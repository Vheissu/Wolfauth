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
 * @license       Do What You Want As Long As You Attribute Me (DWYWALAYAM) licence
 */

class Auth_simpleacl extends CI_Driver {
    
    protected $_ci;
    protected $user_data = array();
    protected $_hash = '';
    protected $_cookie_name = 'simpleacl';
    
    public function __construct()
    {
        $this->_ci = get_instance();
        log_message('debug', "Simpleacl Class Initialized");
        
        $this->_ci->load->library('session');
        $this->_ci->load->helper('cookie');
        
        // Are we logged in?
        if ( $this->_ci->auth->simpleauth->logged_in() )
        {
            // Get user data
            $this->user_data = $this->_ci->auth->simpleauth->get_this_user();
        }
        else
        {
            // set guest info
        }
    }
    
    public function check_role($role = NULL)
    {
        
    }
    
}