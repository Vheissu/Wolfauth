<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth_Simpleauth extends CI_Driver {

	public $CI;

	// Role slugs for administrators
	protected $admin_roles     = array('admin', 'super_admin');

	// Role slugs for editors
	protected $editor_roles    = array('editor', 'super_editor');

	// Role slugs for standard users
	protected $user_roles      = array('user');

	// Roles slugs for guests (should usually only be the one slug)
	protected $guest_roles     = array('guest');
	
	public function __construct()
	{
		// Store reference to the Codeigniter super object
		$this->CI =& get_instance();

		// Load needed Codeigniter Goodness
		$this->CI->load->database();
		$this->CI->load->library('session');
		$this->CI->load->model('user_model');
		$this->CI->load->helper('cookie');

		// Check for a rememberme me cookie
		$this->_check_remember_me();
	}

	/**
	 * Is Admin
	 *
	 * Is the currently logged in user an administrator?
	 *
	 * @return bool
	 *
	 */
	public function is_admin()
	{
		return (in_array($this->CI->session->userdata('role_slug'), $this->admin_roles)) ? TRUE : FALSE;
	}

	/**
	 * Is Editor
	 *
	 * Is the currently logged in user an editor?
	 *
	 * @return bool
	 *
	 */
	public function is_editor()
	{
		return (in_array($this->CI->session->userdata('role_slug'), $this->editor_roles)) ? TRUE : FALSE;
	}

	/**
	 * Is User
	 *
	 * Is the currently logged in user a user?
	 *
	 * @return bool
	 *
	 */
	public function is_user()
	{
		return (in_array($this->CI->session->userdata('role_slug'), $this->user_roles)) ? TRUE : FALSE;
	}

	/**
	 * Is Guest
	 *
	 * Is the current user a guest?
	 *
	 * @return bool
	 *
	 */
	public function is_guest()
	{
		return (in_array($this->CI->session->userdata('role_slug'), $this->guest_roles)) ? TRUE : FALSE;
	}

	/**
	 * Logged In
	 *
	 * Will return TRUE or FALSE if the user if logged in
	 *
	 * @return bool (TRUE if logged in, FALSE if not logged in)
	 *
	 */
	public function logged_in()
	{
		return ($this->CI->session->userdata('user_id')) ? TRUE : FALSE;
	}

	/**
	 * User ID
	 *
	 * Returns user ID of currently logged in user
	 *
	 * @return mixed (user ID on success or false on failure)
	 *
	 */
	public function user_id()
	{
		return ($this->CI->session->userdata('user_id')) ? $this->CI->session->userdata('user_id') : FALSE;
	}

	/**
	 * Login
	 *
	 * Logs a user in, you guessed it!
	 *
	 * @param $username
	 * @param $password
	 * @return mixed (user ID on success or false on failure)
	 *
	 */
	public function login($username, $password)
	{
		// Get the user from the database
		$user = $this->CI->user_model->get_user($username);

		if ($user)
		{
			// Compare the user and pass
			if ($this->CI->user_model->generate_password($password) == $user->row('password'))
			{
				$role_id   = $user->row('role_id');
				$role_name = $user->row('role_name');
				$role_slug = $user->row('role_slug');
				$user_id   = $user->row('id');
				$user_name = $user->row('username');

				$this->CI->session->set_userdata(array(
					'user_id'	=> $user_id,
					'role_id'	=> $role_id,
					'role_name' => $role_name,
					'role_slug' => $role_slug,
					'username'	=> $user_name
				));

				// Do we rememberme them?
				if ($this->CI->input->post('remember_me') == 'yes')
				{
					$this->_set_remember_me($user_id);
				}

				return $user_id;
			}
		}

		// Looks like the user doesn't exist
		return FALSE;
	}

	/**
	 * Logout
	 *
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

		$this->CI->user_model->update_user($user_data);
	}

	/**
	 * Has Permission
	 *
	 * Does the current user have permission to access this resource?
	 *
	 * @param mixed $permission
	 * @return bool (TRUE if user is allowed to access, FALSE if not allowed access)
	 *
	 *
	 */
	public function has_permission($permission = '')
	{
		// If no permission supplied
		if ($permission == '')
		{
			// Permission is the first URL segment
			$permission = $this->CI->uri->segment(1); 
		}

		// Get the current user ID
		$user_id = $this->user_id();

		// Return boolean value of TRUE or FALSE
		return $this->user_model->has_permission($user_id, $permission);


	}

	/**
	 * Set remember Me
	 *
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
		$this->CI->user_model->update_user(array('id' => $user_id, 'remember_me' => $remember_me));
	}

	/**
	 * Check remember Me
	 *
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

			if ($data = $this->CI->user_model->get_user_by_id($user_id))
			{
				// Set session values
				$this->CI->session->set_userdata(array(
					'user_id'   => $user_id,
					'role_id'	=> $data->row('role_id'),
					'role_name' => $data->row('role_name'),
					'role_slug' => $data->row('role_slug'),
					'username'	=> $data->row('username')
				));

				$this->_set_rememberme_me($user_id);

				return TRUE;
			}

			delete_cookie('rememberme');
		}

		return FALSE;
	}

}