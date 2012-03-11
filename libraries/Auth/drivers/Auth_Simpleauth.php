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

class Auth_Simpleauth extends CI_Driver {
	
	protected $CI;
	
	protected $errors   = array();
	protected $messages = array();
	
	protected $login_destination = '/';
	
	protected $identity_method;

    public $user;
	
	public function __construct()
	{
		$this->CI =& get_instance();
		
		// Clear any messages
		$this->clear_messages();
		
        // Load needed Codeigniter libraries, helpers and models.
		$this->CI->load->library('session');
		$this->CI->load->database();
		$this->CI->load->helper('auth/auth');
		$this->CI->load->helper('auth/auth_permissions');
		$this->CI->load->helper('auth/auth_roles');
		$this->CI->load->helper('cookie');
		$this->CI->load->helper('string');
        $this->CI->load->model('auth/wolfauth_model');

        // The identity method is the means to validate a users login by
		$this->identity_method = $this->CI->config->item('identity_method', 'wolfauth');

        // Are we logged in already?
        if ($this->logged_in())
        {
            // Get the current user
            $this->user = $this->get_user();
        }

        // Check if the user has a remember me cookie set
		$this->do_you_remember_me();
	}

    /**
     * Acts as a shortcut to calling Wolfauth model functions
     *
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $arguments)
    {
        if ( ! method_exists( $this->CI->wolfauth_model, $method) )
        {
            throw new Exception('Undefined method ' . $method . '() called');
        }

        return call_user_func_array( array($this->CI->wolfauth_model, $method), $arguments);
    }


    /**
     * Clear Messages
     *
     * Clear all errors and messages
     *
     *
     */
	public function clear_messages()
	{
        // Set error and message arrays to be empty
		$this->errors   = array();
		$this->messages = array();

        return TRUE;
	}

    /**
     * Activate
     *
     * Activates a user
     *
     * @param $user_id
     * @param $code
     * @return bool
     */
    public function activate($user_id, $code)
    {
        // If activation was successful
        if ($this->wolfauth_model->activate($user_id, $code))
        {
            $this->set_message('activate_successful');

            return TRUE;
        }

        $this->set_error('activate_unsuccessful');

        return FALSE;
    }

    /**
     * Login
     *
     * Logs a user into our beautiful auth system
     *
     * @param $identity
     * @param $password
     * @param bool $remember
     * @return bool
     */
	public function login($identity, $password, $remember = FALSE)
	{
        // Determine if we have an email or username
        $identity_type = $this->determine_identity($identity);

        // Get the user by their identity type
		$user = $this->wolfauth_model->get_user_by_{$identity_type}($identity);

        // If we have a user
		if ($user)
        {
            // Is the user activated?
			if ( $user->activated == 'yes' )
            {
                // If the passwords match, log the user in
                if ($this->wolfauth_model->check_password($password, $user->password, $user->salt))
                {
                    $this->set_message('user_logged_in');
                    return $this->wolfauth_model->set_login($user->id, $remember);
                }
                // Login details incorrect
                else
                {
                    $this->set_error('password_incorrect');
                    return FALSE;
                }
			}
            else
            {
				$this->set_error('account_inactive');
				return FALSE;
			}
		}

        $this->set_error('user_not_found');
        return FALSE;
	}

    /**
     * Force Login
     *
     * Forces a user to be logged in without a password
     *
     * @param $identity (username or email)
     * @return bool
     */
    public function force_login($identity)
    {
        return $this->CI->wolfauth_model->force_login();
    }

    /**
     * Register
     *
     * Register a user with option for extra fields
     *
     * @param $fields
     * @return bool
     */
    public function register($fields)
    {
    	// Check the user doesn't exist already
		if (!$this->CI->wolfauth_model->user_exists($fields['username']))
		{
            // Insert the user
            return $this->CI->wolfauth_model->insert_user($fields);
		}
		
		$this->set_message('username_exists');
		
		return FALSE;
    }

    /**
     * Change Password
     *
     * Changes a users password
     *
     * @param $identity
     * @param $old_password
     * @param $new_password
     * @return bool
     */
    public function change_password($identity, $old_password, $new_password)
    {
        // If the old password matches the current password, allow the change
        if ($this->CI->wolfauth_model->change_password($identity, $old_password, $new_password))
        {
            $this->set_message('password_change_successful');

            return TRUE;
        }

        $this->set_error('password_change_unsuccessful');

        return FALSE;
    }

    /**
     * Logout
     *
     * Logs a currently logged in user out
     *
     * @return mixed
     */
    public function logout() 
    {
        return $this->CI->wolfauth_model->logout();
    }

    /**
     * Logged In
     *
     * Is someone logged in?
     *
     * @return bool
     */
    public function logged_in() 
    {
        return $this->CI->wolfauth_model->logged_in();
    }

    /**
     * Get User ID
     *
     * Get the user ID of the currently logged in user
     *
     * @return mixed
     */
    public function get_user_id() 
    {
        return $this->CI->wolfauth_model->get_user_id();
    }

    /**
     * Get User
     *
     * Gets the current logged in user and returns the info
     *
     * @return mixed
     */
    public function get_user()
    {
        return $this->CI->wolfauth_model->get_user();
    }

    /**
     * Determine Identity
     *
     * Determine whether or not a user has supplied a username or email
     *
     * @param $identity
     * @return string
     */
	public function determine_identity($identity)
	{
        return $this->CI->wolfauth_model->determine_identity($identity);
	}
	
    /**
    * Reset Login Attempts
    *
    * Resets login attempts increment value
    * in the database for a particular IP address.
    * 
    * @param mixed $ip_address
    */
    public function reset_login_attempts($ip_address = NULL)
    {
        return $this->wolfauth_model->reset_login_attempts($ip_address);
    }
	
    /**
     * Has Permission
     *
     * Checks if a user has permission to access the current resource
     *
     * @param $permission
     * @return mixed
     */
	public function has_permission($permission = '')
	{
        return $this->CI->wolfauth_model->has_permission($permission);
	}

    /**
     * Add Permission
     *
     * Adds a permission to a role
     * 
     * @param $role_id
     * @param $permission
     * @return bool
     */
    public function add_permission($role_id, $permission)
    {
        return $this->CI->wolfauth_model->add_permission($role_id, $permission);
    }

    /**
     * Do You Remember Me
     *
     * Sets a remember me cookie if the user is remembered
     *
     * @param $user_id
     * @return bool
     */
	private function lets_remember_you($user_id)
	{
		return $this->CI->wolfauth_model->set_remember_me($user_id);
	}

    /**
     * Do You Remember Me
     *
     * Sets a remember me cookie if the user is remembered
     *
     * @return bool
     */
	private function do_you_remember_me()
	{
		return $this->CI->wolfauth_model->get_remember_me();
	}
	
    /**
    * Set Error
    *
    * Sets an error message
    * 
    * @param mixed $error
    * @return string
    */
    public function set_error($error)
    {
        $this->errors[] = $error;
        
        return $error;
    }
    
    /**
    * Set Message
    *
    * Sets a message
    * 
    * @param mixed $message
    * @return string
    */
    public function set_message($message)
    {
        $this->messages[] = $message;

        return $message;
    }
	
   /**
    * Auth Errors
    *
    * Show any error messages relating to the auth class
    *
    * @return mixed
    * 
    */
    public function auth_errors()
    {
        return (!empty($this->errors)) ? $this->errors : FALSE;
    }
	
   /**
    * Auth Messages
    *
    * Show any messages relating to the auth class
    *
    * @return mixed
    * 
    */
    public function auth_messages()
    {
        return (!empty($this->messages)) ? $this->messages : FALSE;
    }
}