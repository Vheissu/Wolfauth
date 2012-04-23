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
		$this->CI->load->library('session');
		$this->CI->load->model('simpleauth_model');
		$this->CI->load->helper('cookie');

        // Load the auth config file
        $this->CI->config->load('auth');

        // Get and store Wolfauth configuration values
        $this->_config = config_item('wolfauth');

        // Get the current user (returns guest if no user is logged in)
		$user = $this->get_user();

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

	/**
	 * Return current user info
	 *
	 * @return object if user logged in or false if logged out
	 *
	 */
	public function get_user()
	{
		// If there is a logged in user, then return their info as an object
		if ($this->logged_in())
		{
            // Get the user by ID
		    $user = $this->CI->simpleauth_model->_get_user($this->user_id(), 'id');
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

	/**
	 * Logs a user in, you guessed it!
	 *
	 * @param $identity
	 * @param $password
	 * @return mixed (user ID on success or false on failure)
	 *
	 */
	public function login($identity, $password)
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
				if ($this->CI->input->post('remember_me') == 'yes')
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

        return $this->simpleauth_model->insert_user();
    }

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

	/**
	 * Perform a hmac hash, using the configured method.
	 *
	 * @param   string  string to hash
	 * @return  string
	 */
	public function hash($str)
	{
		return hash_hmac($this->_config['hash.method'], $str, $this->_config['hash.key']);
	}

    /**
     * Sets an error message
     *
     * @param $error
     */
    public function set_error($error)
    {
        $this->_errors[] = $error;
    }

    /**
     * Set a message
     *
     * @param $message
     */
    public function set_message($message)
    {
        $this->_messages[] = $message;
    }

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


}