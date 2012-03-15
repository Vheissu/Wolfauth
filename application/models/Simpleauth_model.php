<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {

	/**
	 * Get Role Name
	 *
	 * Gets a role name
	 *
	 * @param	int
	 * @return	mixed (string on success, FALSE on fail)
	 */
	public function get_role_name($role_id)
	{
		$this->db->select('role_name');
		$this->db->where('id', $role_id);
		$role = $this->db->get('roles');

		return ($role->num_rows == 1) ? $role->row('role_name') : FALSE;
	}

	/**
	 * Insert Role
	 *
	 * Inserts a new role
	 *
	 * @param	string $role_name
	 * @param   string $role_slug (optional) 
	 * @return	mixed (int on success, FALSE on fail)
	 */
	public function insert_role($role_name = '', $role_slug = '')
	{
		$this->db->set('role_name', $role_name);

		// If no role slug supplied, create one
		if ($role_slug == '') {
			$role_slug = url_title($role_name, 'underscore');
		}

		$this->db->set('role_slug', $role_slug);

		return ($this->db->insert('roles')) ? $this->db->insert_id() : FALSE;
	}

	/**
	 * Get Users
	 *
	 * Returns all users, allows for pagination
	 *
	 * @param	int $limit  - The amount of results to limit to
	 * @param	int $offset - Pagination offset
	 * @return	object
	 */
	public function get_users($limit = 10, $offset = 0)
	{
		$fields = 'users.id, users.username, users.email, users.password, users.role_id, roles.role_name, roles.role_slug';

		// If we don't have a return all users value, use the limit and offset values
		if ($limit != '*')
		{
			$this->db->limit($limit, $offset);
		}

		$this->db->select($fields);
		$this->db->join('roles', 'users.role_id = roles.id');
		return $this->db->get('users');
	}

	/**
	 * Count All Users
	 *
	 * Returns a count of all users
	 *
	 * @return	int
	 */
	public function count_all_users()
	{
		return $this->db->count_all('users');
	}

	/**
	 * Get User
	 *
	 * Returns all user information based on username
	 *
	 * @param	string
	 * @return	mixed
	 */
	public function get_user($username)
	{
		return $this->_get_user($username);
	}

	/**
	 * Get User By Id
	 *
	 * Returns all user information based on user id
	 *
	 * @param	int $id - User ID
	 * @return	mixed
	 */
	public function get_user_by_id($id)
	{
		return $this->_get_user($id, 'id');
	}

	/**
	 * Get User Password Reset
	 *
	 * Returns member information for password reset
	 *
	 * @param	int
	 * @param	string
	 * @return	mixed
	 */
	public function get_user_password_reset($id = '', $passkey = '')
	{
		$this->db->where('id', $id);
		$this->db->where('auth_code', $passkey);

		$user = $this->db->get('users');

		// If the user was found, return the user object
		return ($user->num_rows() == 1) ? $user : FALSE;
	}

	/**
	 * Get User
	 *
	 * Returns all information about any one member
	 *
	 * @param	string $needle   - The value to query by
	 * @param	string $haystack - The field to query by
	 * @return	mixed
	 */
	protected function _get_user($needle, $haystack = 'username')
	{
		$this->db->where($haystack, $needle);

		$user = $this->db->get('users');
		
		return ($user->num_rows() == 1) ? $user : FALSE;
	}

	/**
	 * Insert User
	 *
	 * Inserts a user
	 *
	 * @param	array $user_data
	 * @return	mixed (INT on success, BOOL on fail)
	 */
	public function insert_user($user_data)
	{
		if (isset($user_data['password']))
		{
			$user_data['password'] = $this->generate_password($user_data['password']);
		}

		return ($this->db->insert('users', $user_data)) ? $this->db->insert_id() : FALSE;
	}

	/**
	 * Update User
	 *
	 * Updates a user
	 *
	 * @param	array $user_data
	 * @return	bool
	 */
	public function update_user($user_data)
	{
		$this->db->where('id', $user_data['id']);

		if (isset($user_data['password']))
		{
			$user_data['password'] = $this->generate_password($user_data['password']);
		}

		return ( ! $this->db->update('users', $user_data)) ? FALSE : TRUE;
	}

	/**
	 * Delete User
	 *
	 * Deletes a user
	 *
	 * @param	integer $user_id
	 * @return	bool
	 */
	public function delete_user($user_id)
	{
		$this->db->where('id', $user_id);

		$this->db->delete('users');

		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

	/**
	 * Has Permission
	 *
	 * Does the user have a particular permission
	 *
	 * @param  int   $user_id
	 * @param  mixed $permission
	 * @return bool
	 *
	 *
	 */
	public function has_permission($user_id, $permission)
	{
		$this->db->select('users.id, users.username, users_permissions.id, permissions.permission_string');
		$this->db->where('users.id', $user_id);
		
		// Join our joining table and permissions
		$this->db->join('users_permissions', 'users_permissions.user_id = users.id');
		$this->db->join('permissions', 'users_permissions.user_id = users.id');

		// Find out if the user has the position
		$this->db->where('permissions.permission_string', $permission);
		$this->db->get('users');

		// Does the user have permission? 
		return ($this->db->num_rows() == 1) ? TRUE : FALSE;
	}

	/**
	 * Generate Password
	 *
	 * @param	string	password
	 * @return	string
	 */
	public function generate_password($password)
	{
		// Return sha256 encrypted password
		return hash_hmac('sha256', $password, NULL);
	}

}