<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 * 
 * Let users login with Facebook on your site. Store information about
 * them in the database and dance around lolly flavoured rainbows.
 *
 * @package       WolfAuth
 * @subpackage    Facebook
 * @author        Dwayne Charrington
 * @copyright     Copyright (c) 2011 Dwayne Charrington.
 * @link          http://ilikekillnerds.com
 * @license       Phil Sturgeon's Don't Be A Dick (DBAD) Licence
 */
 
class Auth_facebook extends CI_Driver {
    
    protected $_ci;
    protected $config;
    
    public function __construct()
    {
        // Load some things
        $this->_ci = get_instance();
        $this->_ci->load->database();
        $this->_ci->config->load('auth');
        $this->_ci->load->helper('cookie');
        $this->_ci->load->helper('url');
        $this->_ci->lang->load('auth');
        $this->_ci->load->library('session');
        $this->_ci->load->model('auth/user_model');
        
        // Get the config values for this driver defined in the config/auth.php file
        $this->config = (object)$this->_ci->config->item('facebook');        
    }
    
}