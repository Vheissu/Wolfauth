<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 * 
 * This driver allows user accounts to be created, login users by Facebook
 * and a whole lot more Facebook related goodness.
 *
 * @package       WolfAuth
 * @subpackage    Facebook
 * @author        Dwayne Charrington
 * @copyright     Copyright (c) 2011 Dwayne Charrington.
 * @link          http://ilikekillnerds.com
 * @license       http://www.apache.org/licenses/LICENSE-2.0.html
 */
 
require_once APPPATH . 'third_party/Facebook/facebook.php';

class Auth_Facebook extends CI_Driver {
    
    protected $auth;
    protected $ci;
    protected $config;
    
    protected $fb;

    public function __construct()
    {
        $this->auth = auth_instance();
        $this->ci   = get_instance();
        
        $this->ci->config->load('auth');
        
        $this->config = config_item('facebook');
        
        $this->fb = new Facebook(array(  
            'appId'  => $this->config['facebook.app_id'],  
            'secret' => $this->config['facebook.api_secret'],  
            'cookie' => true  
        ));         
    }
    
    /**
    * Logged In
    * Check if a Facebook session exists
    * 
    */
    public function logged_in()
    {
        // Get User ID
        $user = $this->fb->getUser();
        
        if ($user) 
        {
            try 
            {
                // Proceed knowing you have a logged in user who's authenticated.
                $user_profile = $this->fb->api('/me');
                
                print_r($user_profile);
                die;
            } 
            catch (FacebookApiException $e) 
            {
                error_log($e);
                $user = null;
            }
            
            return TRUE;
        }

        // Login or logout url will be needed depending on current user state.
        if ($user) 
        {
          $logoutUrl = $this->fb->getLogoutUrl();
        } 
        else 
        {
          $loginUrl = $this->fb->getLoginUrl();
        }  
    }
    
    public function login()
    {
        
    }
    
}