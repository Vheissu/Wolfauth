<?php

class Auth_simpleauth extends CI_Driver {
	
	// Codeigniter instance
	public $_ci;

	// Codeigniter session
	public $_session;

	// User object
	public $_user;

	// Config values array
	public $_config;

	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{
		// Get the Codeigniter instance
		$this->_ci =& get_instance();

		// Load the basic auth config file
		$this->_ci->load->config('wolfauth');

		// Get the basic config
		$this->_config = config_item('wolfauth');

		// Load needed libraries, models and helpers
		$this->_ci->load->library('session');		
		$this->_ci->load->model('wolfauth_users');
		$this->_ci->load->model('wolfauth_roles');
		$this->_ci->load->model('wolfauth_permissions');	
	}

	/**
	 * Login
	 *
	 * Logs a user in if their login details are correct
	 *
	 * @param $identity - Username or email address
	 * @param $password - Password to validate with
	 * @param $remember - True or false to remember user
	 * @return bool
	 *
	 */
	public function login($identity, $password, $remember = FALSE)
	{
		// Get the identity field type from the config
		$identity_field = $this->config['identity'];
	}

	/**
	 * Force Login
	 *
	 * Log a user in via their username or email without a password
	 *
	 * @param $identity - Username or email address
	 * @return int - The user ID
	 *
	 */
	public function force_login($identity)
	{

	}

	/**
	 * Logout
	 *
	 * Logs a user out and then checks to make sure the session was destroyed
	 *
	 * @return bool
	 *
	 */
	public function logout()
	{
		// Destroy the session
		$this->_ci->session->session_destroy();

		// Check we're definitely not logged in now
		return !$this->logged_in();
	}

	/**
	 * Logged In
	 *
	 * Will return true or false if a user is logged in or out
	 *
	 * @return bool (true if logged in, false if not logged in)
	 *
	 */
	public function logged_in()
	{
		return ($this->get_user() !== NULL);
	}

	/**
	 * Get User
	 *
	 * Get a users session values
	 *
	 * @return mixed (will return session object if user exists, otherwise NULL)
	 *
	 */
	public function get_user()
	{

	}

	/**
	 * Hash Password
	 *
	 * Hashes a password using the hash function sha256 by default
	 *
	 * @param $password - The password to hash
	 * @return string
	 *
	 */
	public function hash_password($password)
	{
		return $this->hash($password);
	}

	/**
	 * Hash
	 *
	 * Will hash a string using sha256 by default and then return it
	 *
	 * @param $string - The string to be hashed
	 * @return string
	 *
	 */
	public function hash($string)
	{
		return hash_hmac('sha256', $string, NULL);
	}


}