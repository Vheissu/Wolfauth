<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* @name WolfAuth
* @category Library
* @package WolfAuth
* @author Dwayne Charrington
* @copyright 2011
* @link http://ilikekillnerds.com
*/

class WolfAuth {
    
    private $CI;
    
    protected $guest_role;
    protected $admin_roles;
    
    protected $user_id;
    protected $role_id;
    
    /**
    * Constructor function
    * 
    */
    public function __construct()
    {
        $this->CI =& get_instance();
        
        $this->CI->database();
        $this->CI->load->config('wolf_auth');
        $this->CI->load->library('session');
        $this->CI->load->library('email');
        $this->CI->load->model('wolfauth_model');
        $this->CI->load->helper('cookie');
        
        // Set some default role stuff
        $this->guest_role  = $this->CI->config->item('guest_role');
        $this->admin_roles = $this->CI->config->item('admin_roles');
        
        // Set some important IDs
        $this->role_id = $this->CI->session->userdata('role_id');
        
    }
    
    /**
    * Fetch the access role of a particular user
    * or from the currently logged in user.
    * 
    * @param mixed $userid
    */
    public function get_role($userid = 0)
    {
        
        // No ID supplied to this function?
        // Get the role of the current user
        // regardless of being logged in or
        // not.
        if ( $userid == 0 )
        {
            // If we don't have a user ID set, then return the guest role ID
            if ( !$this->role_id >= 0 )
            {
                return $this->guest_role;
            }
            // We have a logged in role to return!
            else
            {
                return $this->role_id; 
            }   
        }
        else
        {
            // Fetch the user ID of the specific user supplied to this function
            $this->CI->wolfauth_model->get_userinfo('role', $userid);
        }
        
    }
    
}