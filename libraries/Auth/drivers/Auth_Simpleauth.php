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
	
	public function __construct()
	{
		$this->CI =& get_instance();
		
		// Clear any messages
		$this->clear_messages();
		
		$this->CI->load->library('session');
		$this->CI->load->database();
		$this->CI->load->helper('auth/auth');
		$this->CI->load->helper('cookie');
		$this->CI->load->helper('string');

		$this->identity_method = $this->CI->config->item('identity_method', 'wolfauth');
		
		// Load needed models and stuff
		$this->CI->load->model($this->CI->config->item('model.user', 'wolfauth'));
		$this->CI->load->model($this->CI->config->item('model.email', 'wolfauth'));

		$this->do_you_remember_me();
	}
	
	public function clear_messages()
	{
		$this->errors   = array();
		$this->messages = array();
	}
	
	public function login($identity, $password, $remember = FALSE)
	{
		$user = $this->CI->wolfauth_users->get($identity, $this->determine_identity($identity));
		
		if ( $user ) {
			if ( $user->activated == 'yes' ) {
				if ( $this->check_password($password, $user->password) ) {
					unset($user->password);
					
					$this->CI->session->set_userdata(array(
						'user'     => $user,
						'logged_in' => TRUE 
					));
					
					if ( $remember ) {
						$this->lets_remember_you($user->id);
					}
					return TRUE;
				}
			} else {
				$this->set_message('Your account is not activated');
				return false;
			}
		} else {
			$this->set_message('A user matching that username or password could not be found');
			return false;
		}		
	}

    public function register($fields, $extra_fields = array())
    {
    	// Check the user doesn't exist already
		if ( ! $this->CI->wolfauth_users->user_exists($fields['username']) )
		{
            // Insert the user
            return $this->CI->wolfauth_users->insert_user($fields, $extra_fields);
		}
		
		$this->set_message('A user with that username already exists');
		
		return FALSE;
    }
	
    public function logout() 
    {
		$user = $this->CI->session->userdata('user');
		
        $this->CI->session->sess_destroy();
        
		$this->CI->load->helper('cookie');
		delete_cookie($this->config->item('cookie.name', 'wolfauth'));
		
		$this->CI->wolfauth_users->update_user(array('remember_me' => ''), $user->id);
		
        $this->CI->session->set_userdata('logged_in', FALSE);
        $this->CI->session->set_userdata('user', FALSE);
    }
    
    public function logged_in() 
    {
        return $this->CI->session->userdata('logged_in');
    }
    
    public function get_user_id() 
    {
        $user = $this->CI->session->userdata('user');
        return $user->id;
    }	
	
	public function determine_identity($identity)
	{
		$this->CI->load->helper('email');
		
		if ( $identity == 'auto' ) {
			
			if ( valid_email($identity) ) {
				$identity = "email";
			} else {
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
        $this->{attempts_model}->reset_login_attempts($ip_address);
    }
	
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
		$this->CI->wolfauth_users->update_user(array('remember_me' => $remember_me), $user_id);
	}
	
	private function do_you_remember_me()
	{
		$this->CI->load->library('encrypt');
		
		if ( $cookie = get_cookie($this->CI->config->item('cookie.name', 'wolfauth')) ) {
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
				
				if ( $user = $this->CI->wolfauth_users->get($user_id, 'id') ) {
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
    */
    public function auth_errors()
    {
        return (!empty($this->errors)) ? $this->errors : FALSE;
    }
	
   /**
    * Auth Messages
    * Show any messages relating to the auth class
    * 
    */
    public function auth_messages()
    {
        return (!empty($this->messages)) ? $this->messages : FALSE;
    }
}