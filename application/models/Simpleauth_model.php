<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Simpleauth_model extends CI_Model {

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
		$fields = 'users.id, users.username, users.email, users.password, users.role_id, roles.role, roles.display_name AS role_name';

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
	 * Returns all user information based on username or email
	 *
	 * @param	string $email
	 * @return	mixed
	 */
	public function get_user($identity)
	{
		// This will be populated with the user info
		$user = '';
		
		// If the default login method is username
		if ($this->auth->_config['login.method'] == 'username')
		{
			$user = $this->_get_user($identity);
		}
		else
		{
			$this->_get_user($identity, 'email');
		}

		// Return the user
		return $user;
	}

	/**
	 * Get User By Id
	 *
	 * Returns all user information based on user id
	 *
	 * @param	int $user_id - User ID
	 * @return	mixed
	 */
	public function get_user_by_id($user_id)
	{
		return $this->_get_user($user_id, 'id');
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
	 * Delete a user
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
	 * Adds a capability to the database
	 *
	 * @param	integer $user_id
	 * @return	bool
	 */
	public function add_capability($name)
	{
		// Insert the capability
		$data['capability'] = $name;

		// Insert the new capability
		$this->db->insert('capabilities', $data);

		// Was the capability added in
		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

	/**
	 * Deletes a capability from the database
	 *
	 * @param	string $name
	 * @param	bool $delete_relationships - Should all relationships be severed as well?
	 * @return	bool
	 */
	public function delete_capability($name, $delete_relationships = TRUE)
	{
		// Find the capability
		$this->db->where('capability', $name);

		// Delete the capability
		$this->db->delete('capabilities');

		if ($delete_relationships === TRUE)
		{
			// Delete the relationships
			$this->delete_capability_relationships($name);
		}

		// Was the capability deleted?
		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

	/**
	 * Deletes role <> capability relationships
	 *
	 * @param	string $name
	 * @return	bool
	 */
	public function delete_capability_relationships($name)
	{
		// Get the ID of our capability
		$capability_id = $this->get_capability_id($name);

		$this->db->where('capability_id', $capability_id);

		// Delete the capability
		$this->db->delete('roles_capabilities');

		// Was the capability relationship deleted?
		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

	/**
	 * Get a list of capabilities for a particular role
	 *
	 * @param string $role - The role name to get capabilities from
	 *
	 */
	public function get_capabilities($role)
	{
		// Get the role meta via the $role slug
		$role_info = $this->get_role($role, 'role');

		$this->db->select('capabilities.id, capabilities.capability');
		$this->db->where('role', $role_info->role);
		$this->db->join('roles_capabilities', 'capabilities.id = roles_capabilities.capability_id');

		// Get the results
		$result = $this->db->get('roles');

		// Return the results if capabilities were found
		return ($result->num_rows() > 0) ? $result->result() : FALSE;
	}

	/**
	 * Get the ID of a capability based on its name
	 *
	 * @param string $name - The capability name
	 *
	 */
	public function get_capability_id($name)
	{
		$this->db->select('id');
		$this->db->where('capability', $name);

		// Get the capabilities
		$result = $this->db->get('capabilities');

		// Return the row
		return ($result->num_rows() == 1) ? $result->row() : FALSE;
	}

	/**
	 * Get a role based on criteria
	 *
	 * @param string|int $needle - The value to find
	 * @param string $haystack - The type of value we're searching
	 * @return object on Success or false on Failure
	 *
	 */
	public function get_role($needle, $haystack = 'id')
	{
		// Fetch
		$this->db->where($haystack, $needle);

		// Search the roles table
		$result = $this->db->get('roles');

		// Return the database row if successful or FALSE on failure
		return ( $result->num_rows() == 1 ) ? $result->row() : FALSE;
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