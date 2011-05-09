<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 * 
 * This driver is a simple ACL implementation which also interfaces
 * with the Simple Auth driver to allow you to restrict access to
 * certain parts of your site.
 *
 * @package       WolfAuth
 * @subpackage    Simpleacl
 * @author        Dwayne Charrington
 * @copyright     Copyright (c) 2011 Dwayne Charrington.
 * @link          http://ilikekillnerds.com
 * @license       Do What You Want, As Long As You Attribute Me (DWYWALAYAM) licence
 */

class Auth_simpleacl extends CI_Driver {
    
    protected $_ci; // Codeigniter instance
    protected $_sa; // Simpleauth instance
    protected $user_data = array();
    protected $_hash = '';
    protected $_cookie_name = 'simpleacl';
    
    public function __construct()
    {
        $this->_ci = get_instance();
        $this->_sa = $this->_ci->auth->simpleauth;
        
        log_message('debug', "Simpleacl Class Initialized");
        
        $this->_ci->load->library('session');
        $this->_ci->load->helper('cookie');
        $this->_ci->load->model('acl_model');
        
        // Get user data
        $this->user_data = $this->_sa->get_this_user();
    }
    
    /**
    * Check Permission
    * Do we have permission to access the current resource?
    * 
    * @param mixed $resource
    */
    public function check_permission($resource = NULL)
    {
        // If we are an administrator, we always have access baby
        if ( $this->_sa->is_admin() )
        {
            return true;
        }
        
        // If no permission supplied, try and work it out
        if ( is_null($resource) )
        {
            $resource = '';
            
            // Get the URL segments
            $url_string = $this->_ci->uri->uri_string();
            
            // If we don't just have a slash in the URL
            if ($url_string !== "/")
            {
                $resource = ltrim($url_string, "/");   
            }
        }        
        
        // Return whether or not we're allowed access
        return $this->_ci->simpleacl_model->check_resource($resource);
    }
    
    /**
    * Add Permission
    * Allows you to add permission to a particular
    * set of resources.
    * 
    * @param mixed $role_name
    * @param mixed $permissions
    */
    public function add_permission($role_name, $permissions)
    {
        $this->_ci->simpleacl_model->add_permission($role_name, $permissions);
    }
    
}