<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Permission_model extends CI_Model {

	/**
	 * Get Permissions
	 *
	 * Get all valid permissions from the database
	 *
	 */
	public function get_permissions()
	{
		$permissions = $this->db->get('roles');

		return ($permissions->num_rows() >= 1) ? $permissions->result() : FALSE;
	}


	/**
	 * Get Role Permissions
	 *
	 * Get all permissions for a particular role
	 *
	 *
	 */
	public function get_role_permissions($role_id)
	{
		$role = $this->db->get_where('roles', array('id' => $role_id), 1, 0);

		if ($role->num_rows() == 1)
		{
			$this->db->select('permissions.permission');
			$this->db->where('permissions_roles.role_id', $role->row('id'));
			$this->db->join('permissions', 'permissions.id = permissions_roles.permission_id');

			$result = $this->db->get('permissions_roles');

			return ($result->num_rows() > 0) ? $result->result_array() : FALSE;
		}
	}

}