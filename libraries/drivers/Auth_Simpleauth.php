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
	
	protected $user_model;
	protected $email_model;
	
	public function __construct()
	{
		$this->CI =& get_instance();
		
		// Clear any messages
		$this->clear_messages();
		
		$this->CI->load->library('session');
		$this->CI->load->database();
		$this->CI->load->helper('cookie');
		$this->ci->load->helper('string');
		
		$this->user_model      = $this->CI->config->item('model.user', 'wolfauth');
		$this->email_model     = $this->CI->config->item('model.email', 'wolfauth');
		$this->attempts_model  = $this->CI->config->item('model.attempts', 'wolfauth');
		$this->identity_method = $this->CI->config->item('identity_method', 'wolfauth');
		
		// Load our ACL driver
		$this->CI->load->driver('wolfauth_acl');
		
		// Load needed models and stuff
		$this->CI->load->model($this->user_model);
		
		$this->do_you_remember_me();
	}
	
	public function clear_messages()
	{
		$this->errors   = array();
		$this->messages = array();
	}
	
	public function login($identity, $password, $remember = FALSE)
	{
		$user = $this->CI->{$this->user_model}->get($identity, $this->determine_identity($identity));
		
		if ( $user ) {

			if ( $user['activated'] ) {
				if ( $this->check_password($password, $user['password']) ) {
					unset($user['password']);
					
					$this->CI->session->set_userdata(array(
						'user'     => $user,
						'logged_in' => TRUE 
					));
					
					if ( $remember ) {
						$this->lets_remember_you($user['id']);
					}
					return TRUE;
				}
			}
		}	
	}

    public function register($username, $email, $password, $fields = array())
    {

    }
	
    public function logout() 
    {
		$user = $this->CI->session->userdata('user');
		
        $this->CI->session->sess_destroy();
        
		$this->CI->load->helper('cookie');
		delete_cookie($this->config->item('cookie.name', 'wolfauth'));
		
		$this->CI->{$this->user_model}->update_user(array('remember_me' => ''), $user['id']);
		
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
        return $user['id'];
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
        $this->attempts_model->reset_login_attempts($ip_address);
    }
	
	private function lets_remember_you($user_id)
	{
		$this->CI->load->library('encrypt');

		$token = md5(uniqid(rand(), TRUE));
		$timeout = $this->config->item('cookie.expiry', 'wolfauth'); // One week

		$remember_me = $this->CI->encrypt->encode($user_id.'//'.$token.'//'.(time() + $timeout));

		$cookie = array(
			'name'		=> $this->config->item('cookie.name', 'wolfauth'),
			'value'		=> $remember_me,
			'expire'	=> $timeout
		);

		set_cookie($cookie);
		$this->CI->{$this->user_model}->update_user(array('remember_me' => $remember_me), $user_id);
	}
	
	private function do_you_remember_me()
	{
		$this->CI->load->library('encrypt');
		
		if ( $cookie = get_cookie($this->config->item('cookie.name', 'wolfauth')) ) {
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
				
				if ( $user = $this->CI->{$this->user_model}->get($user_id, 'id') ) {
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