<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth_Simpleauth extends CI_Driver {

	// Codeigniter instance
	public $CI;

	// Group ID for current user - 0 means guest
	protected $group_id = 0;
	
	public function __construct()
	{
		// Store reference to the Codeigniter super object
		$this->CI =& get_instance();

		// Load needed Codeigniter Goodness
		$this->CI->load->database();
		$this->CI->load->library('session');
		$this->CI->load->model('simpleauth_model');
		$this->CI->load->helper('cookie');

		// Store the group ID for easier reference
		$this->group_id = $this->CI->session->userdata('group_id');

		// Check for a rememberme me cookie
		$this->_check_remember_me();
	}


	/**
	 * Is Super Admin
	 *
	 * Is the currently logged in user a super administrator?
	 *
	 * @return bool
	 *
	 */	
	public function is_super_admin()
	{
		return (in_array($this->group_id, $this->_config['roles.sadmin'])) ? TRUE : FALSE;
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
		return (in_array($this->group_id, $this->_config['roles.admin']) OR in_array($this->group_id, $this->_config['roles.sadmin'])) ? TRUE : FALSE;
	}

	public function is_user()
	{
		return (in_array($this->group_id, $this->_config['roles.user'])) ? TRUE : FALSE;
	}

	/**
	 * Is Group
	 *
	 * Does the currently logged in user belong to a
	 * particular group?
	 *
	 * @param int $id
	 * @return bool - true if yes, false if no	 
	 *
	 */
	public function is_group($id)
	{
		return ($id == $this->group_id) ? TRUE : FALSE;
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

	public function group()
	{
		return $this->group_id;
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

		// The user was found
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
				$email     = $user->row('email');

				$this->CI->session->set_userdata(array(
					'user_id'	=> $user_id,
					'role_id'	=> $role_id,
					'role_name' => $role_name,
					'role_slug' => $role_slug,
					'username'	=> $user_name,
					'email'     => $email
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