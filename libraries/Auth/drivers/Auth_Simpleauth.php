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


	public function clear_messages()
	{
		$this->errors   = array();
		$this->messages = array();
	}

    /**
     * Activate
     * Activates a user
     *
     * @param $user_id
     * @param $code
     * @return bool
     */
    public function activate($user_id, $code)
    {
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
     * Logs a user into our beautiful auth system
     *
     * @param $identity
     * @param $password
     * @param bool $remember
     * @return bool
     */
	public function login($identity, $password, $remember = FALSE)
	{
		$user = $this->wolfauth_model->get_user($identity, $this->determine_identity($identity));

        // If we have a user
		if ( $user )
        {
			if ( $user->activated == 'yes' )
            {
				if ( $this->check_password($password, $user->password) )
                {
					unset($user->password);
					
					$this->CI->session->set_userdata(array(
						'user'     => $user,
						'logged_in' => TRUE 
					));
					
					if ( $remember )
                    {
						$this->lets_remember_you($user->id);
					}
					return TRUE;
				}
			}
            else
            {
				$this->set_message('Your account is not activated');
				return false;
			}
		}
        else
        {
			$this->set_message('A user matching that username or password could not be found');
			return false;
		}		
	}

    /**
     * Register
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
     * Changes a users password
     *
     * @param $identity
     * @param $old_password
     * @param $new_password
     * @return bool
     */
    public function change_password($identity, $old_password, $new_password)
    {
        if ($this->CI->wolfauth_model->change_password($identity, $old_password, $new_password))
        {

            $this->set_message('password_change_successful');

            return TRUE;

        }

        $this->set_error('password_change_unsuccessful');

        return FALSE;
    }
	
    public function logout() 
    {
		$user = $this->CI->session->userdata('user');
		
        $this->CI->session->sess_destroy();
        
		$this->CI->load->helper('cookie');
		delete_cookie($this->config->item('cookie.name', 'wolfauth'));
		
		$this->CI->wolfauth_model->update_user(array('remember_me' => ''), $user->id);
		
        $this->CI->session->set_userdata('logged_in', FALSE);
        $this->CI->session->set_userdata('user', FALSE);
    }

    /**
     * Logged In
     * Is someone logged in?
     *
     * @return bool
     */
    public function logged_in() 
    {
        return $this->CI->session->userdata('logged_in');
    }

    /**
     * Get User ID
     * Get the user ID of the currently logged in user
     *
     * @return mixed
     */
    public function get_user_id() 
    {
        $user = $this->CI->session->userdata('user');
        return $user->id;
    }

    /**
     * Get User
     * Gets the current logged in user and returns the info
     *
     * @return mixed
     */
    public function get_user()
    {
        return $this->CI->session->userdata('user');
    }

    /**
     * Determine Identity
     * Determine whether or not a user has supplied a username or email
     *
     * @param $identity
     * @return string
     */
	public function determine_identity($identity)
	{
		$this->CI->load->helper('email');
		
		if ( $identity == 'auto' )
        {
			if ( valid_email($identity) )
            {
				$identity = "email";
			}
            else
            {
				$identity = "username";
			}
		}
		
		return $identity;					
	}
	
    /**
    * Reset Login Attempts
    * Resets login attempts increment value
    * in the database for a particular IP address.
    * 
    * @param mixed $ip_address
    */
    public function reset_login_attempts($ip_address = NULL)
    {
        $this->wolfauth_model->reset_login_attempts($ip_address);
    }
	
    /**
     * Has Permission
     * Checks if a user has permission to access the current resource
     *
     * @param $permission
     * @return mixed
     */
	public function has_permission($permission = '')
	{
        // If we have no permission check current URL
        if ($permission == '')
        {
			// The permission string by default is the whole URL string
            $permission = trim($this->CI->uri->uri_string(), '/');
		}

        return $this->CI->wolfauth_model->has_permission($permission);
	}

    /**
     * Add Permission
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
     * Lets Remember You
     * Sets remember me info
     *
     * @param $user_id
     */
	private function lets_remember_you($user_id)
	{
		$this->CI->load->library('encrypt');

		$token = md5(uniqid(rand(), TRUE));
		$timeout = $this->config->item('cookie.expiry', 'wolfauth');

		$remember_me = $this->CI->encrypt->encode($user_id.'//'.$token.'//'.(time() + $timeout));

		$cookie = array(
			'name'		=> $this->config->item('cookie.name', 'wolfauth'),
			'value'		=> $remember_me,
			'expire'	=> $timeout
		);

		set_cookie($cookie);
		$this->CI->wolfauth_model->update_user(array('remember_me' => $remember_me), $user_id);
	}

    /**
     * Do You Remember Me
     * Sets a remember me cookie if the user is remembered
     *
     * @return bool
     */
	private function do_you_remember_me()
	{
		$this->CI->load->library('encrypt');
		
		if ( $cookie = get_cookie($this->CI->config->item('cookie.name', 'wolfauth')) )
        {
			$user_id = '';
			$token = '';
			$timeout = '';
			
			$cookie_data = $this->CI->encrypt->decode($cookie);
			
			if (strpos($cookie_data, '//') !== FALSE)
			{
				$cookie_data = explode('//', $cookie_data);
				
				if (count($cookie_data) == 3)
				{
					list($user_id, $token, $timeout) = $cookie_data;
				}
				
				if ( (int) $timeout < time() )
				{
					return FALSE;
				}
				
				if ( $user = $this->CI->wolfauth_model->get($user_id, 'id') )
                {
					// Fill the session and renew the remember me cookie
					$this->CI->session->set_userdata(array(
						'user'		=> $user,
						'logged_in'	=> true
					));
					
					$this->lets_remember_you($user_id);
					
					return TRUE;
				}

				delete_cookie($this->config->item('cookie.name', 'wolfauth'));				
			}
			
			return FALSE;		
		}
	}
	
    /**
    * Set Error
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