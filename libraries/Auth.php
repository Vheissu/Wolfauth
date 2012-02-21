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
	
	// Valid drivers to use with Wolfauth (defined in config/wolfauth.php)
	public $valid_drivers = array();
	
	// Codeigniter instance
	public $CI;
	
	// The currently in use driver
	public $_driver;
	
	public function __construct( $params = array() )
	{
		$this->CI =& get_instance();
		$this->CI->config->load('wolfauth', TRUE);
		
		$this->valid_drivers = $this->CI->config->item('drivers', 'wolfauth');
	}
	
	public function set_driver($name)
	{
		// Set the driver name, trim whitespace
		$this->_driver = trim($name);
	}
	
	/**
	 * Login
	 * Base function for logging in a user
	 * @param $identity (username or email)
	 * @param $password
	 * @param (bool) $remeber
	 */
	public function login($identity, $password, $remember = false)
	{
		// Call the child driver login method
		return $this->{$this->_driver}->login($identity, $password, $remember);
	}
	
	/**
	 * Logout
	 * Destroys the session completely and logs us out
	 *
	 */
	public function logout()
	{
		return $this->{$this->_driver}->logout();
	}
	
	/**
	 * Logged In
	 * Is a user currently logged in
	 *
	 */
	public function logged_in()
	{
		return $this->{$this->_driver}->logged_in();
	}
	
	/**
	 * Get User ID
	 * Get current user ID
	 *
	 */
	public function get_user_id()
	{
		return $this->{$this->_driver}->get_user_id();
	}
	
	public function register($username, $email, $password, $fields = array())
	{
		// Call the child register function
		return $this->{$this->_driver}->register($username, $email, $password, $fields);
	}
	
    /**
    * Auth Instance
    * Static function wrapper for auth drivers
    * 
    */
    public static function auth_instance()
    {
        static $ci;
        $ci = get_instance();
        
        return $ci->auth;
    }

}

/**
* Auth Instance
* Function shortcut to the proper auth instance
* 
*/
function auth_instance()
{
    return Auth::auth_instance();
}
