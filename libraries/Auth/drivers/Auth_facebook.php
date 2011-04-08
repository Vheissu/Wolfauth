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
    
    protected $user_info;
    
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
        $this->_ci->load->model('auth/facebook_model');
        
        // Get the config values for this driver defined in the config/auth.php file
        $this->config = (object)$this->_ci->config->item('facebook');        
    }
    
    /**
    * Login with your Facebook account, sir.
    * 
    */
    public function login()
    {
        // Get the Facebook user
        $fb_user = $this->_ci->facebook_model->get_user();
        
        // Looks like we have ourselves a Facebook user here
        if ( $fb_user )
        {
            // Store the Facebook user ID
            $this->_ci->session->set_userdata('facebook_id', $fb_user['id']);
            
            // Look through the database to see if this user has Facebooked before
            $user = $this->_ci->user_model->get_user("facebook_id", $fb_user['id']);
            
            // User was found aiiiighhttt
            if ( $user )
            {
                    
            }
            // Looks like this user has Facebook, but no account created
            else
            {
                $details = array();
                
                // User addition was successful
                if ( $this->_ci->user_model->add_user($details) )
                {
                    return true;
                }
                else
                {
                    show_error($this->_ci->lang->line('error_user_not_added'));
                }
            }
        }        
    }
    
    /**
    * Destroy any Facebooksession currently running
    * 
    */
    public function destroy_session()
    {
        
    }
    
}