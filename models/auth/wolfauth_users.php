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

class Wolfauth_users extends CI_Model {
	
	public function __construct()
	{
		parent::__construct();	
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
	
	public function insert_user($fields, $extra_fields)
	{
		if ( isset($fields['password']) )
		{
            $fields['password'] = $this->generate_password($fields['password']);
		}

        // Merge the arrays
        $fields = array_merge($fields, $extra_fields);

		return ($this->db->insert($this->config->item('table.users', 'wolfauth'), $fields)) ? $this->db->insert_id() : FALSE;
	}
	
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
	
	public function delete_user($user_id = '')
	{
		$this->db->where('id', $user_id);

		$this->db->delete($this->config->item('table.users', 'wolfauth'));

		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}
	
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