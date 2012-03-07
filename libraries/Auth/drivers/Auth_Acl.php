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

class Auth_Acl extends CI_Driver {
	
	protected $CI;

    protected $permissions_model;
    protected $roles_model;

	public function __construct()
	{
		// Codeigniter instance
		$this->CI =& get_instance();
		
		// Load our helpers for permissions and roles
		$this->CI->load->helper('auth/auth_permissions');
		$this->CI->load->helper('auth/auth_roles');
		
		$this->CI->load->model('auth/acl_m');
	}

    /**
     * Has Permission
     * Checks if a user has permission to access the current resource
     * @param $permission
     */
	public function has_permission($permission = '')
	{

        // If we have no permission check current URL
        if ($permission == '') {

			// The permission string by default is the whole URL string
            $permission = trim($this->CI->uri->uri_string(), '/');
		}

        return $this->CI->acl_m->has_permission($permission);
	}

    /**
     * Add Permission
     * Adds a permission to a role
     * @param $role_id
     * @param $permission
     */
    public function add_permission($role_id, $permission)
    {
        return $this->CI->acl_m->add_permission($role_id, $permission);
    }

}