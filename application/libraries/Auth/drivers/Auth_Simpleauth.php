<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth_simpleauth extends CI_Driver {

	// Codeigniter instance
	public $CI;

    protected $_errors   = array();
    protected $_messages = array();

    // Empty config array
    protected $_config = array();

	// Where our user role is stored
	protected $role = array();

    // The current user ID
    protected $user_id;

	// Currently logged in user capabilities
	protected $capabilities = array();
	
	public function __construct()
	{
		// Store reference to the Codeigniter super object
		$this->CI =& get_instance();

		// Load needed Codeigniter Goodness
		$this->CI->load->database();
		$this->CI->load->library('email');
		$this->CI->load->library('session');
		$this->CI->load->model('simpleauth_model');
		$this->CI->load->helper('cookie');

        // Load the auth config file
        $this->CI->config->load('auth');

        // Get and store Wolfauth configuration values
        $this->_config = config_item('wolfauth');

        // Get the current user as an object (returns guest if no user is logged in)
		$user = $this->get_user(true);

		// Store the current user role and display name
		$this->role['role'] = $user->role;
		$this->role['display_name'] = $user->role_display_name;

        // Store the user ID
        $this->user_id = $user->id;

		// Get capabilities for this role
		$this->capabilities = $this->CI->simpleauth_model->get_capabilities($user->role);

		// Check for a rememberme me cookie
		$this->_check_remember_me();
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Will return TRUE or FALSE if the user if logged in
	 *
	 * @return bool (TRUE if logged in, FALSE if not logged in)
	 *
	 */
	public function logged_in()
	{
		return $this->user_id();
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Returns user ID of currently logged in user
	 *
	 * @return mixed (user ID on success or false on failure)
	 *
	 */
	public function user_id()
	{
		return $this->CI->session->userdata('user_id');
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Is Role
	 *
	 * @return bool (true if a user has this role false if not)
	 *
	 */
	public function is_role($slug)
	{
		$slug = trim($slug);
		if ($slug == $this->role['name'])
		{
			return TRUE;
		}

		return FALSE;
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Return current user info
	 *
	 * @param bool $as_object - Should we return user data as an object?
	 * @return object if user logged in or false if logged out
	 *
	 */
	public function get_user($as_object = FALSE)
	{
		// If there is a logged in user, then return their info as an object
		if ($this->logged_in())
		{
            // Get the user by ID
		    $user = $this->CI->simpleauth_model->get_user_by_id($this->user_id(), 'id');

		    // Are we returning the user as an object and we have a valid user
		    if ($as_object == TRUE && $user !== FALSE)
		    {
			    $tmp = new stdClass;

			    foreach ($user->result_array() AS $arr)
			    {
			    	foreach ($arr AS $k => $v)
			    	{
			    		$tmp->{$k} = $v;
			    	}
			    }

			    $user = $tmp;
		    }

        }
        // There is no user logged in, they're a guest
		else
		{
            // Create an empty class, because expected output is always an object
            $user = new stdClass;

            // Guests don't get a user ID because they're not special enough
            $user->id = 0;

            // Set the user to have a guest role as defined in the config file
			$user->role = $this->_config['role.guest'];
			$user->role_display_name = $this->_config['role.guest.display_name'];
		}

		// Return the user
		return $user;
	}

    /**
     * Does the user have a particular capability to do something?
     *
     * @param $capability
     * @param int $user_id
     */
    public function user_can($capability, $user_id = 0)
    {
        // No user, then default to the current one
        if ($user_id == 0)
        {
            // Get the current user ID
            $user_id = $this->user_id;
        }
    }

    // -------------------------------------------------------------------------------------

	/**
	 * Logs a user in, you guessed it!
	 *
	 * @param $identity
	 * @param $password
	 * @param $remember
	 * @return mixed (user ID on success or false on failure)
	 *
	 */
	public function login($identity, $password, $remember = FALSE)
	{
		// Get the user from the database
		$user = $this->CI->simpleauth_model->get_user($identity);

		if ($user)
		{
			// Compare the user and pass
			if ($this->hash($password) == $user->row('password'))
			{
				$user_id  = $user->row('id');
				$username = $user->row('username');
				$email    = $user->row('email');
				$role     = $user->row('role');

				$this->CI->session->set_userdata(array(
					'user_id'  => $user_id,
					'username' => $username,
					'email'	   => $email,
					'role'     => $role
				));

				// Do we rememberme them?
				if ($this->CI->input->post('remember_me') == 'yes' OR $remember == TRUE)
				{
					$this->_set_remember_me($user_id);
				}

				return $user_id;
			}
            else
            {
                $this->set_error('Username and or password was incorrect');
            }
		}

		// Looks like the user doesn't exist
		return FALSE;
	}

	// -------------------------------------------------------------------------------------

    /**
     * Register a new user
     *
     * @param $username
     * @param $email
     * @param $password
     * @param int $role
     * @return mixed
     */
    public function register($username, $email, $password, $role = 2)
    {
        $data['username'] = $username;
        $data['email']    = $email;
        $data['password'] = $password;
        $data['role_id']  = $role;

        return $this->simpleauth_model->insert_user($data);
    }

    // -------------------------------------------------------------------------------------

	/**
	 * OMG, logging out like it's 1999
	 *
	 * @return	void
	 */
	public function logout()
	{
		$user_id = $this->CI->session->userdata('user_id');

		$this->CI->session->sess_destroy();

		$this->CI->load->helper('cookie');
		delete_cookie('rememberme');

		$user_data = array(
			'user_id'     => $this->CI->session->userdata('user_id'),
			'remember_me' => ''
		);

		$this->CI->simpleauth_model->update_user($user_data);
	}

	// -------------------------------------------------------------------------------------

    /**
     * Activates a user
     * 
     * @param mixed $user_id
     * @param mixed $code
     */
    public function activate($user_id = 0, $code = '')
    {
        if ($user_id == 0)
        {
            $user_id = $this->user_id;
        }
        
        // Get the user via their ID
        $user = $this->simpleauth_model->_get_user($user_id, 'id');
        
        // If the user exists and they aren't already active or banned
        if ( $user && $user->status !== 'active' && $user->status !== 'banned' )
        {
            // If codes match
            if ($u->activation_code == $code)
            {
                // Activate the user
                $data['id'] = $user_id;
                $data['activation_code'] = '';
                $data['status'] = 'active';
                
                return $this->simpleauth_model->update_user($data);
            }
            else
            {
                $this->set_error("The activation code supplied is not correct.");
                return false;
            }
        }
        elseif (!empty($user))
        {
            $this->set_error("Cannot activate a user that does not exist.");
            return false;
        }
        elseif ($user->status !== 'active' && $user->status == 'banned')
        {
        	$this->set_error('Banned users cannot be activated');
        }
    }

	// -------------------------------------------------------------------------------------

	/**
	 * Adds a capability into the capabilities table
	 *
	 * @param string $capability - The capability name
	 *
	 */
	public function add_capability($capability)
	{
		return $this->simpleauth_model->add_capability(trim($capability));
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Deletes a capability from the database
	 *
	 * @param	string $name
	 * @param	bool $delete_relationships - Should all relationships be severed as well?
	 * @return	bool
	 */
	public function delete_capability($name, $delete_relationships = TRUE)
	{
		return $this->simpleauth_model->delete_capability($name, $delete_relationships);
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Adds a capability to a user role to allow access to thing
	 *
	 * @param string $role - The role slug name
	 * @param string $capability - The name of the capability we're adding
	 * @return bool TRUE on success and FALSE on failure
	 */
	public function add_capability_to_role($role, $capability)
	{
		return $this->simpleauth_model->add_capability_to_role($role, $capability);
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Deletes role <> capability relationships
	 *
	 * @param	string $name
	 * @return	bool
	 */
	public function delete_capability_relationships($name)
	{
		return $this->simpleauth_model->delete_capability_relationships($name);
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Get a list of capabilities for a particular role
	 *
	 * @param string $role - The role name to get capabilities from
	 * @return array on success and FALSE on failure
	 */
	public function get_capabilities($role)
	{
		return $this->simpleauth_model->get_capabilities($role);
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Get a role based on criteria
	 *
	 * @param string|int $needle - The value to find
	 * @param string $haystack - The type of value we're searching
	 * @return object on Success or false on Failure
	 *
	 */
	public function get_role($needle, $haystack = 'id')
	{
		return $this->simpleauth_model->get_role($needle, $haystack);
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Adds a new role to the roles database
	 *
	 * @param string $role - The role slug to add
	 * @param string $display_name - Human readable name of the role
	 * @return bool - True if the role was added, False if it wasn't
	 *
	 */
	public function add_role($role, $display_name)
	{
		return $this->simpleauth_model->add_role($role, $display_name);
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Updates an already existent role in the roles table
	 *
	 * @param string $role - The role slug to identify by
	 * @param array $data - The array of new data values to add
	 * @return bool - Returns True if update was success of False if it wasn't
	 *
	 */
	public function update_role($role, $data = array())
	{
		return $this->simpleauth_model->update_role($role, $data);
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Deletes a role from the database
	 *
	 * @param string $role - The role slug we're removing
	 * @param bool $delete_relationships - Delete all role > capability relationships too?
	 *
	 */
	public function delete_role($role, $delete_relationships = TRUE)
	{
		return $this->simpleauth_model->delete_role($role, $delete_relationships);
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Updates the remember me cookie and database information
	 *
	 * @param	string unique identifier
	 * @access  private
	 * @return	void
	 */
	private function _set_remember_me($user_id)
	{
		$this->CI->load->library('encrypt');

		$token = md5(uniqid(rand(), TRUE));
		$timeout = 60 * 60 * 24 * 7; // One week

		$remember_me = $this->CI->encrypt->encode($user_id.':'.$token.':'.(time() + $timeout));

		// Set the cookie and database
		$cookie = array(
			'name'		=> 'rememberme',
			'value'		=> $remember_me,
			'expire'	=> $timeout
		);

		set_cookie($cookie);
		$this->CI->simpleauth_model->update_user(array('id' => $user_id, 'remember_me' => $remember_me));
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Checks if a user is logged in and remembered
	 *
	 * @access	private
	 * @return	bool
	 */
	private function _check_remember_me()
	{
		$this->CI->load->library('encrypt');

		// Is there a cookie to eat?
		if($cookie_data = get_cookie('rememberme'))
		{
			$user_id = '';
			$token = '';
			$timeout = '';

			$cookie_data = $this->CI->encrypt->decode($cookie_data);
			
			if (strpos($cookie_data, ':') !== FALSE)
			{
				$cookie_data = explode(':', $cookie_data);
				
				if (count($cookie_data) == 3)
				{
					list($user_id, $token, $timeout) = $cookie_data;
				}
			}

			// Cookie expired
			if ((int) $timeout < time())
			{
				return FALSE;
			}

			if ($data = $this->CI->simpleauth_model->get_user_by_id($user_id))
			{
				// Set session values
				$this->CI->session->set_userdata(array(
					'user_id'   => $user_id,
					'role_name' => $data->row('role_name'),
					'username'	=> $data->row('username')
				));

				$this->_set_rememberme_me($user_id);

				return TRUE;
			}

			delete_cookie('rememberme');
		}

		return FALSE;
	}

	// -------------------------------------------------------------------------------------

	/**
	 * Perform a hmac hash, using the configured method.
	 *
	 * @param   string $str  - string  string to hash
	 * @return  string
	 */
	public function hash($str)
	{
		return hash_hmac($this->_config['hash.method'], $str, $this->_config['hash.key']);
	}

	// -------------------------------------------------------------------------------------

    /**
     * Sets an error message
     *
     * @param $error
     */
    public function set_error($error)
    {
        $this->_errors[] = $error;
    }

    // -------------------------------------------------------------------------------------

    /**
     * Set a message
     *
     * @param $message
     */
    public function set_message($message)
    {
        $this->_messages[] = $message;
    }

    // -------------------------------------------------------------------------------------

    /**
     * Returns the array of auth errors (if any)
     *
     * @return string
     */
    public function auth_errors()
    {
        $output = '';
        foreach($this->_errors AS $error)
        {
            $output .= "<p class='error-msg'>".$error."</p>";
        }

        return $output;
    }

    // -------------------------------------------------------------------------------------

}