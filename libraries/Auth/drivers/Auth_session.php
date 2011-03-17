<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 *
 * @package       WolfAuth
 * @subpackage    Session
 * @author        Dwayne Charrington
 * @copyright     Copyright (c) 2011 Dwayne Charrington.
 * @link          http://ilikekillnerds.com
 */

class Auth_Session extends CI_Driver {
    
    // Logged in user info
    protected $user_id;
    protected $role_id;  
    protected $username;
    protected $email; 
    
    /**
    * Gets Codeigniter super instance
    * 
    * @param mixed $blah
    */
    public function __get($blah)
    {
        $ci = get_instance();
        return $ci->$blah;
    }
    
    /**
    * Constructor
    * 
    */
    public function __construct() 
    {
        $this->load->database();
        $this->load->config('auth');
        $this->load->library('session');
        $this->load->model('auth/user_model.php');
        
        $this->user_id  = ($this->session->userdata('user_id')) ? $this->session->userdata('user_id') : 0;
        $this->role_id  = ($this->session->userdata('role_id')) ? $this->session->userdata('role_id') : $this->auth_config->guest_role_id;
        $this->username = $this->session->userdata('username');
        $this->email    = $this->session->userdata('email');        
    }
    
    /**
    * Just for decoration, ha ha.
    * 
    */
    public function decorate() {}
    
    /**
    * Is anyone logged in? Assumes a logged in user has a user_id higher than 0
    * 
    * @param mixed $config
    */
    public function logged_in($config = array())
    {
        return ($this->user_id > 0) ? $this->user_id : false;   
    }
    
    /**
    * Get currently logged in user data
    * 
    * @param mixed $config
    */
    public function get_user($config = array())
    {
        // Get user based on defined criteria
        if ( $this->config->item('login_method') == 'username' )
        {
            $value = $this->username;   
        }
        elseif ( $this->config->item('login_method') == 'email' )
        {
            $value = $this->email;   
        }
        
        return $this->user_model->get_user($value); 
    }
    
    /**
    * Forces a user to be logged in without a password
    * 
    * @param mixed $username
    * @param mixed $config
    */
    public function force_login($username, $config = array())
    {
       
    }
    
    /**
    * Log a user in
    * 
    * @param mixed $username
    * @param mixed $password
    * @param mixed $remember
    * @param mixed $config
    */
    public function login($username, $password, $remember = false, $config = array())
    {
        // Make sure we're not logged in
        if ( $this->user_id == 0 )
        {   
            // Get the user
            $user = $this->user_model->get_user($username);
            
            // Passwords match
            if ( $user->password == $this->hash_password($password) )
            {
                $user_data = array(
                    'user_id'  => $user->id,
                    'role_id'  => $user->role_id,
                    'username' => $user->username,
                    'email'    => $user->email
                );
                $this->session->set_userdata($user_data);
            }
        }
    }
    
    /**
    * Restrict access to something
    * 
    * @param mixed $needle
    * @param mixed $criteria
    * @param mixed $config
    */
    public function restrict_to($needle, $criteria = 'role', $config = array())
    {
        
    }
    
    /**
    * Add a new user
    * 
    * @param mixed $data
    * @param mixed $config
    */
    public function add_user($data, $config = array())
    {
        
    }    
    
    /**
    * Edit a user
    * 
    * @param mixed $data
    * @param mixed $config
    */
    public function edit_user($data, $config = array())
    {
        
    }    
    
    /**
    * Delete a user
    * 
    * @param mixed $id
    * @param mixed $config
    */
    public function delete_user($id, $config = array())
    {
        
    }
    
    /**
    * Hash a password
    * 
    * @param mixed $password
    */
    public function hash_password($password, $salt = '', $config = array())
    {
        if ($salt === FALSE)
        {
            $password = sha1($password);
        }
        else
        {
            $password = sha1($password.$salt);
        }
        
        return $password;
    }
    
    /**
    * Change a password
    * 
    * @param mixed $id
    * @param mixed $old
    * @param mixed $new
    * @param mixed $config
    */
    public function change_password($id, $old, $new, $config = array())
    {
        
    }

}
