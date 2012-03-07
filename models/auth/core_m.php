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

class Core_m extends CI_Model {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('email');
		$this->load->library('email');		
	}
	
	public function get_user_password_reset($id = '', $passkey = '')
	{
		$this->db->where('id', $id);
		$this->db->where('auth_code', $passkey);

		$user = $this->db->get($this->config->item('table.users', 'wolfauth'));
		
		return ($user->num_rows() == 1) ? $user : FALSE;
	}

    /**
     * User Exists
     * Returns true or false if the user already exists
     *
     * @param $username
     * @return bool
     */
    public function user_exists($username)
    {
        return ( $this->get($username, 'username') ) ? TRUE : FALSE;
    }

    /**
     * Email Exists
     * Does an email address exist in the database?
     *
     * @param $email
     * @return bool
     */
    public function email_exists($email)
    {
        return ( $this->get($email, 'email') ) ? TRUE : FALSE;
    }

    /**
     * Get
     * Gets a user object
     *
     * @param string $needle
     * @param string $haystack
     * @return bool
     */
	public function get($needle = '', $haystack = 'email')
	{
		$this->db->where($haystack, $needle);

		$user = $this->db->get($this->config->item('table.users', 'wolfauth'));

        if ( $user->num_rows() == 1 ) {
            return $user->row();
        } elseif ( $user->num_rows() > 1 ) {
            return $user->result();
        } else {
            return FALSE;
        }

	}

    /**
     * Insert User
     * Inserts a user and any meta into the database
     *
     * @param $fields
     * @param $extra_fields
     * @return bool
     */
	public function insert_user($fields, $extra_fields)
	{
		if ( isset($fields['password']) )
		{
            $fields['password'] = $this->generate_password($fields['password']);
		}

		$insert = $this->db->insert($this->config->item('table.users', 'wolfauth'), $fields)->db->insert_id();

        // If the user inserted okay and no extra fields to add
        if ($insert AND empty($extra_fields))
        {
            // Return user ID
            return $insert;
        }

        // User inserted okay and we have extra fields to add
        if ($insert AND ! empty($extra_fields))
        {
            return $this->insert_usermeta($insert, $extra_fields);
        }

        return FALSE;
	}

    /**
     * Insert Usermeta
     * Inserts metadata for a user
     *
     * @param $user_id
     * @param $usermeta
     * @return bool
     */
    public function insert_usermeta($user_id, $usermeta)
    {
        return ($this->db->insert($this->config->item('table.usermeta', 'wolfauth'), $usermeta)) ? TRUE : FALSE;
    }

    /**
     * Update User
     * Update a users details
     *
     * @param array $fields
     * @param $user_id
     * @return bool
     */
	public function update_user($fields = array(), $user_id)
	{
		// Find the user ID
		$this->db->where('id', $user_id);
		
		// If we have a password to update!
		if ($fields['password']) {
			$fields['password'] = $this->generate_password($fields['password']);
		}
		
		return ($this->db->update($this->config->item('table.users', 'wolfauth'), $fields)) ? TRUE : FALSE; 
	}

    /**
     * Update Usermeta
     * Update a users meta details
     *
     * @param array $fields
     * @param $user_id
     * @return bool
     */
	public function update_usermeta($fields = array(), $user_id)
	{
		// Find the user ID
		$this->db->where('user_id', $user_id);

		return ($this->db->update($this->config->item('table.usermeta', 'wolfauth'), $fields)) ? TRUE : FALSE;
	}

    /**
     * Delete User
     * Deletes a user
     *
     * @param string $user_id
     * @return bool
     */
	public function delete_user($user_id = '')
	{
		$this->db->where('id', $user_id);

		$this->db->delete($this->config->item('table.users', 'wolfauth'));

		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}
	
    /**
    * Update Login Attempts
    * Used by the login function when a user attempts to login
    * unsuccessfully.
    * 
    * @param mixed $ip_address
    */
    public function update_login_attempts($ip_address = NULL)
    {
        if (is_null($ip_address)) {
            $ip_address = $this->ip_address;
        }
            
        $exists = $this->db->get_where($this->config->item('table.attempts', 'wolfauth'), array('ip_address' => $ip_address));
        
        if ( $exists->num_rows() >= 1 ) {
            $exists = $exists->row();
            $current_time = time();
            $created      = strtotime($exists->created);
            
            // Minutes comparison
            $minutes      = floor($current_time - $created / 60);
            
	        // If current time elapsed between creation is greater than the attempts, reset
            if (($current_time - $created) > $this->config->item('attempts.expiry', 'wolfauth')) {
                $this->reset_login_attempts($ip_address);

                // add the first attempt after reset them
                $insert = $this->db->insert($this->config->item('attempts.expiry', 'wolfauth'), array('ip_address' => $ip_address, 'attempts' => 1));

                return $insert->affected_rows();
            } else {
	            // Increment new attempts
                $this->db->set('attempts', 'attempts + 1', FALSE);
                $this->db->set('ip_address', $ip_address);
                $insert = $this->db->update($this->config->item('attempts.expiry', 'wolfauth'));
            }
        } else {
            $insert = $this->db->insert($this->config->item('attempts.expiry', 'wolfauth'), array('ip_address' => $ip_address, 'attempts' => 1));
            return $insert->affected_rows();
        }
    }
	
    /**
    * Reset Login Attempts
    * Resets login attempts increment value
    * in the database for a particular IP address.
    * 
    * @param mixed $ip_address
    */
    public function reset_login_attempts($ip_address)
    {
		$this->db->where('ip_address', $ip_address);
		$this->db->delete($this->config->item('table.attempts', 'wolfauth'));
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
        
        $message = $this->load->view('auth/emails/reset_password', $data, true);
        
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
        
        $message = $this->load->view('auth/emails/new_password', $data, true);
        
        $this->ci->email->clear();
        $this->ci->email->set_newline("\r\n");
        $this->ci->email->from($this->config->item('admin_email', 'wolfauth'), $this->config->item('site_name', 'wolfauth'));
        $this->ci->email->to($email);
        $this->ci->email->subject($this->config->item('site_name', 'wolfauth') . ' - Forgotten Password Request');
        $this->ci->email->message($message);
		
		return ( $this->ci->email->send() ) ? TRUE : FALSE; 
    }

    /**
     * Generate Password
     * Generates a password
     *
     * @param string $password
     * @return mixed
     */
	public function generate_password($password = '')
	{
		$this->load->helper('security');
		$this->load->helper('string');

		if ($password == '')
		{
			// Generate a password 8 characters long
			$password = random_string('alnum', 8);
		}

		return do_hash($password);
	}

}