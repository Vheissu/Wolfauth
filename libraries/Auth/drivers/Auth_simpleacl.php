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
        $this->_ci->load->model('simpleacl_model');
        
        // Are we logged in?
        if ( $this->_sa->logged_in() )
        {
            // Get user data
            $this->user_data = $this->_sa->get_this_user();
        }
        else
        {
            // set guest info
        }
    }
    
    /**
    * Check Permission
    * Do we have permission to access the current resource?
    * 
    * @param mixed $permission
    */
    public function check_permission($permission = NULL)
    {
        // If we are an administrator, we always have access baby
        if ( $this->_sa->is_admin() )
        {
            return true;
        }
        
        // If no permission supplied, try and work it out
        if ( is_null($permission) )
        {
            // Get the segments from the URL
            $url_string = $this->_ci->uri->rsegment_array();
        }        
    }
    
}