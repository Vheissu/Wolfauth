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