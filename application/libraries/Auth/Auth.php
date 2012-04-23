<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 *
 * @package   WolfAuth
 * @author    Dwayne Charrington
 * @copyright Copyright (c) 2012 Dwayne Charrington.
 * @link      http://ilikekillnerds.com
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html
 * @version   2.0
 */
 
class Auth extends CI_Driver_Library {
	
	// Codeigniter instance
	public $CI;
	
	// An array of configuration values from config/wolfauth.php
	public $_config = array();
	
	// The currently in use driver
	public $_driver;
	
	public function __construct()
	{
		$this->CI =& get_instance();
		
		// Load needed helpers
		$this->CI->load->helper('form');
		$this->CI->load->helper('url');
		
		// Load the auth config file
		$this->CI->config->load('auth');
		
		// Get and store Wolfauth configuration values
		$this->_config = config_item('wolfauth');
		
		// Set the drivers defined in the auth config file
		$this->valid_drivers = $this->_config['allowed_drivers'];
		
		// Set the default driver
		$this->_driver = $this->_config['default_driver'];
	}

    /*
     * Set Driver
     *
     * Sets a driver to use
     *
     * @param $name
     */
	public function set_driver($name)
	{
		// Set the driver name, trim whitespace
		$this->_driver = trim($name);
	}
	
	/*
	 * Login
	 *
	 * Base function for logging in a user
	 *
	 * @param $email
	 * @param $password
	 * @param (bool) $remember
	 * @return int
	 */
	public function login($email, $password, $remember = false)
	{
		// Call the child driver login method
		return $this->{$this->_driver}->login($email, $password, $remember);
	}

	/*
	 * Force Login
	 *
	 * Force login for a particular username or email
	 *
	 * @param $identity (username or email)
	 * @return bool
	 *
	 */
	public function force_login($email)
	{
		return $this->{$this->_driver}->force_login();
	}
	
    /*
     * Logout
     *
     * Logs a user out
     *
     * @return bool
     */
	public function logout()
	{
		return $this->{$this->_driver}->logout();
	}
	
	/*
	 * Logged In
	 *
	 * Is a user currently logged in
	 *
	 * @return bool
	 *
	 */
	public function logged_in()
	{
		return $this->{$this->_driver}->logged_in();
	}

    /**
     * Get User
     *
     * Get the currently logged in user info
     *
     * @return mixed
     */
    public function get_user()
    {
        return $this->{$this->_driver}->get_user();
    }

    /*
     * Register
     *
     * Register a user account
     *
     * @param $fields
     * @return mixed
     */
	public function register($fields)
	{
		// Call the child register function
		return $this->{$this->_driver}->register($fields);
	}
	
	/**
	* __call magic function
	*
	* Redirect all method calls not in this class to the child class
	* set in the variable _adapter which is the default class.
	*
	* @param mixed $child
	* @param mixed $arguments
	* @return mixed
	*/
    public function __call($child, $arguments)
    {
        return call_user_func_array(array($this->{$this->_driver}, $child), $arguments);
    }
	
    /*
    * Auth Instance
    *
    * Static function wrapper for auth drivers
    *
    * @return object
    * 
    */
    public static function auth_instance()
    {
        static $ci;
        $ci = get_instance();
        
        return $ci->auth;
    }

}

/*
 * Auth Instance
 *
 * Function shortcut to the proper auth instance
 *
 * @return object
 *
 */
function auth_instance()
{
    return Auth::auth_instance();
}