<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 *
 * @package   WolfAuth
 * @author    Dwayne Charrington
 * @copyright Copyright (c) 2013 Dwayne Charrington.
 * @link      http://ilikekillnerds.com
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html
 * @version   2.0
 */

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
		$fields = 'users.user_id, users.username, users.email, users.salt, users.password, users.role_id, roles.role, roles.display_name AS role_name';

		// If we don't have a return all users value, use the limit and offset values
		if ($limit != '*')
		{
			$this->db->limit($limit, $offset);
		}

		$this->db->select($fields);
		$this->db->join('roles', 'users.role_id = roles.role_id');
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
	 * @param	string $identity
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
			$user = $this->_get_user($identity, 'email');
		}

		// Return the user
		return $user;
	}

    /**
     * Gets the number of login attempts for a particular user
     * @param $identity
     */
    public function get_login_attempts($identity)
    {

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
		return $this->_get_user($user_id, 'user_id');
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
		$this->db->where('user_id', $id);
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
        $this->db->select('users.*, roles.role_title, roles.role_description');
		$this->db->where('users.'.$haystack, $needle);
        $this->db->join('roles', 'roles.role_id = users.role_id');
        $this->db->limit(1, 0);

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
			$user_data['password'] = $this->auth->hash($user_data['password']);
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
		$this->db->where('user_id', $user_data['user_id']);

		if (isset($user_data['password']))
		{
			$user_data['password'] = $this->auth->hash($user_data['password']);
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
		$this->db->where('user_id', $user_id);
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

		// Should relationships with roles be deleted
		if ($delete_relationships === TRUE)
		{
			// Delete the relationships
			$this->delete_capability_relationships($name);
		}

		// Was the capability deleted?
		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

	/**
	 * Adds a capability to a user role to allow access to thing
	 *
	 * @param string $role - The role slug name
	 * @param string $capability - The name of the capability we're adding
	 * @return bool TRUE on success and FALSE on failure
	 */
	public function add_capability_to_role($role, $capability)
	{
		// Get a role by its role slug
		$role_info = $this->get_role($role, 'role_title');

		// If we have info about this role, it exists and continue
		if ($role_info)
		{
			// The role ID
			$data['role_id'] = $role_info->role_id;

			// The capability ID
			$data['capability_id'] = $this->get_capability_id($capability);

			// Store the info in the database
			$this->db->insert('roles_capabilities', $data);

			// Was the capability added to the role?
			return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
		}
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
	 * @param string $role - The role name to get capabilities from the DB
	 *
	 */
	public function get_capabilities($role)
	{
		// Get the role meta via the $role slug
		$role_info = $this->get_role($role, 'role_title');

		if (isset($role_info->role_id))
		{
			$this->db->select('capabilities.capability_id, capabilities.capability');
			$this->db->where('role_id', $role_info->role_id);
			$this->db->join('capabilities', 'capabilities.capability_id = roles_capabilities.capability_id');

			// Get the results
			$result = $this->db->get('roles_capabilities');

			// Return the results if capabilities were found
			return ($result->num_rows() > 0) ? $result->result() : FALSE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Get the ID of a capability based on its name
	 *
	 * @param string $name - The capability name
     * @return object on success or bool FALSE on failure
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
		return ( $result->num_rows() == 1 ) ? $result->row() : new StdClass;
	}

	/**
	 * Adds a new role to the roles database
	 *
	 * @param string $role - The role name (lowercased)
	 * @param string $description - Human readable name of the role
	 * @return bool - True if the role was added, False if it wasn't
	 *
	 */
	public function add_role($role, $description = '')
	{
		// Prep the role before inserting into the database
		$data['role_title']       = strtolower(trim($role));
		$data['role_description'] = trim($description);

		// Insert the role into the database
		$this->db->insert('roles', $data);

		// Was the role added into the database?
		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

	/**
	 * Updates an already existent role in the roles table
	 *
	 * @param string $role - The role slug to identify by
	 * @param array $data - The array of new data values to add
	 * @return bool - Returns True if update was success of False if it wasn't
	 *
	 */
	public function update_role($role, $data = array())
	{
		// Make sure we have data to actually update in the database
		if (!empty($data))
		{
			// Look for the role we're updating
			$this->db->where('role_title', $role);

			// Return True if the update was successfull or False if unsuccessful
			return ( ! $this->db->update('roles', $data)) ? FALSE : TRUE;	
		}
	}

	/**
	 * Deletes a role from the database
	 *
	 * @param string $role - The role slug we're removing
	 * @param bool $delete_relationships - Delete all role > capability relationships too?
	 *
	 */
	public function delete_role($role, $delete_relationships = TRUE)
	{
		// Find this role...
		$this->db->where('role_title', $role);

		// Delete the role, yeowww
		$this->db->delete('roles');

		// If we're deleting the role relationships in our mapping table
		if ($delete_relationships === TRUE)
		{
			$this->delete_role_relationships($role);
		}

		// If we have deleted a row, return True otherwise return False
		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

	/**
	 * Deletes role <> capability relationships
	 *
	 * @param	string $name
	 * @return	bool
	 */
	public function delete_role_relationships($name)
	{
		// Get the role info
		$role_info = $this->get_role($name, 'role_title');

		// Find the mapping entries in the database
		$this->db->where('role_id', $role_info->role_id);

		// Delete the capability
		$this->db->delete('roles_capabilities');

		// Was the capability relationship deleted?
		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}

}