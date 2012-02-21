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

class Wolfauth_email extends CI_Model {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->ci->load->helper('email');
		$this->ci->load->library('email');
	}
	
    /**
    * Email Forgot Password
    * If a user forgets their password, they can send themselves
    * an email to reset their password.
    * 
    * @param mixed $email
    */
    public function email_forgot_password($email, $code)
    {
        $data['email']       = $email;
        $data['forgot_code'] = $code;
        $data['reset_link']  = $this->config->item('reset_password_link', 'wolfauth');
        
        $message = $this->load->view('wolfauth/emails/reset_password', $data, true);
        
        $this->email->clear();
        $this->email->set_newline("\r\n");
        $this->email->from($this->config->item('admin_email', 'wolfauth'), $this->config->item('site_name', 'wolfauth'));
        $this->email->to($email);
        $this->email->subject($this->config->item('site_name', 'wolfauth') . ' - Forgotten Password Verification');
        $this->email->message($message);
		
		return ( $this->email->send() ) ? TRUE : FALSE;  
    }
	
    /**
    * Email New Password
    * Send a newly generated password to the user
    * 
    * @param mixed $email
    */
    public function email_new_password($email, $password)
    {
        $data['email']    = $email;
        $data['password'] = $password;
        
        $message = $this->load->view('wolfauth/emails/new_password', $data, true);
        
        $this->ci->email->clear();
        $this->ci->email->set_newline("\r\n");
        $this->ci->email->from($this->config->item('admin_email', 'wolfauth'), $this->config->item('site_name', 'wolfauth'));
        $this->ci->email->to($email);
        $this->ci->email->subject($this->config->item('site_name', 'wolfauth') . ' - Forgotten Password Request');
        $this->ci->email->message($message);
		
		return ( $this->ci->email->send() ) ? TRUE : FALSE; 
    }
	
}