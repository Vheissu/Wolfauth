<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 * 
 * This model is used by the SimpleACL driver to do resource checking
 * and other ACL associated tasks.
 *
 * @package       WolfAuth
 * @subpackage    Acl Model
 * @author        Dwayne Charrington
 * @copyright     Copyright (c) 2011 Dwayne Charrington.
 * @link          http://ilikekillnerds.com
 * @license       Do What You Want, As Long As You Attribute Me (DWYWALAYAM) licence
 */
 
class Acl_model extends CI_Model {
    
    protected $_user_info = array();
    
    public function __construct()
    {
        parent::__construct();
        $this->_user_info = $this->auth->simpleauth->get_this_user();
        $this->load->config('auth');
    }
    
    /**
    * Check Resource
    * Does a user have permission to access this resource?
    * 
    * @param mixed $resource
    */
    public function check_resource($resource)
    {
        // Get this current guest or users details
        $role_name = $this->_user_info['role_name'];
        
        // Lowercase in-case someone puts capitals in the URL
        $resource = strtolower($resource);
        
        // Get our config stuff for simpleacl
        $simpleacl = config_item('simpleacl');
        
        // Check if this user can access this resource
        $dbquery = $this->db->where('resource_slug', $resource)->where('role_name', $role_name)->get('acl_permissions');
        
        // Deniable means if it's not on the list, deny access
        if ($simpleacl['method'] == "DENIABLE")
        {
            // If this resource has been assigned permission
            return ($dbquery->num_rows() == 1) ? true : false;   
        }
        // Return true if on the list, return true if not
        else
        {
            return ($dbquery->num_rows() == 1) ? true : true;
        }
    }
    
    /**
    * Add Resource
    * Add a new ACL resource
    * 
    * @param mixed $role_name
    * @param mixed $resource
    */
    public function add_resource($role_name, $resource)
    {
        
    }
    
}