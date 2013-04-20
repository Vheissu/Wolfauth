<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {

	/**
	 * Get Roles
	 *
	 * Get all roles from the database that exist
	 *
	 */
	public function get_roles()
	{
		$roles = $this->db->get('roles');

		return ($roles->num_rows() >= 1) ? $roles->result() : FALSE;
	}

	/**
	 * Insert Role
	 *
	 * Insert a new user assignable role into the database
	 *
	 */
	public function insert_role($role_name, $role_display_name)
	{
		$role_data = array(
			'role_name'         => $role_name,
			'role_display_name' => $role_display_name
		);

		return ( $this->db->insert('roles', $role_data) ) ? $this->db->insert_id() : FALSE;
	}

	/**
	 * Update Role
	 *
	 * Update a role in the database
	 *
	 */
	public function update_role($role_data = array())
	{
		$this->db->where('role_name', $role_data['role_name']);

		return ( ! $this->db->update('roles', $role_data)) ? FALSE : TRUE;
	}

	/**
	 * Delete Role
	 *
	 * Deletes a role from the database
	 *
	 */
	public function delete_role($role_name)
	{
		$this->db->where('role_name', $role_name);
		$this->db->delete('roles');

		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

	/**
	 * Count All Users
	 *
	 * Count all users in the database regardless of status
	 *
	 */
	public function count_all_users()
	{
		return $this->db->count_all('users');
	}


	/**
	 * Get Users
	 *
	 * Query the database for users and return their appropriate bits of info.
	 * Support for pagination limiting and offsets as well.
	 *
	 */
	public function get_users($limit = 10, $offset = 0)
	{
		if ($limit != '*')
		{
			$this->db->limit($limit, $offset);
		}

		$this->db->select('users.id AS user_id, users.email, users.password, roles.role_name');
		$this->db->join('roles', 'users.role_id = roles.id');

		$users = $this->db->get('users');

		return ($users->num_rows() >= 1) ? $users->result() : FALSE;
	}

	/**
	 * Get
	 *
	 * Gets a user via email address
	 *
	 */
	public function get($email)
	{
		return $this->_get_user($email);
	}


	/**
	 * Get User By ID
	 *
	 * Gets a user via their ID
	 *
	 */
	public function get_user_by_id($id)
	{
		return $this->_get_user($id, 'id');
	}


	/**
	 * Get Password Reset
	 *
	 * Get info of a user based on the password reset
	 * information supplied in the form of user ID and authkey
	 *
	 */
	public function get_password_reset($user_id, $authkey = '')
	{
		$this->db->where('id', $user_id);
		$this->db->where('authkey', $authkey);

		$user = $this->db->get('users');

		return ($user->num_rows() == 1) ? $user->row() : FALSE;
	}


	/**
	 * Insert
	 *
	 * Insert a new user into the users table
	 *
	 */
	public function insert($user_data = array())
	{
		if ( isset($user_data['password']) )
		{
			$user_data['password'] = $this->hash_password($user_data['password']);
		}

		return ( $this->db->insert('users', $user_data) ) ? $this->db->insert_id() : FALSE;
	}


	/**
	 * Update
	 *
	 * Update a user row in the database
	 *
	 */
	public function update($user_data = array())
	{
		$this->db->where('id', $user_data['id']);

		if ( isset($user_data['password']) )
		{
			$user_data['password'] = $this->hash_password($user_data['password']);
		}

		return ( ! $this->db->update('users', $user_data)) ? FALSE : TRUE;
	}


	/**
	 * Delete
	 *
	 * Deletes a user from the database
	 *
	 */
	public function delete($user_id = '')
	{
		$this->db->where('id', $user_id);
		$this->db->delete('users');

		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}


	/**
	 * Get User
	 *
	 * Utility function for getting users based on various criteria
	 *
	 * @access protected
	 *
	 */
	protected function _get_user($needle, $haystack = 'email')
	{
		$this->db->where($haystack, $needle);
		$this->db->limit(1, 0);

		$user = $this->db->get('users');

		return ($user->num_rows() == 1) ? $user->row() : FALSE;
	}


	/**
	 * Hash Password
	 *
	 * Securely hash a password
	 *
	 */
	public function hash_password($password)
	{
		return hash_hmac('sha512', $password, config_item('auth_encryption_key'));
	}

}