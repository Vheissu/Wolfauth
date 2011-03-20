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

class Auth_Facebook extends CI_Driver {
    
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
        $this->load->helper('cookie');
        $this->load->helper('url');
        $this->lang->load('auth');
        $this->load->library('session');
        $this->load->model('auth/user_model.php');
        
        $this->user_id  = ($this->session->userdata('user_id')) ? $this->session->userdata('user_id') : 0;
        $this->role_id  = ($this->session->userdata('role_id')) ? $this->session->userdata('role_id') : $this->auth_config->guest_role_id;
        $this->username = $this->session->userdata('username');
        $this->email    = $this->session->userdata('email');
        
        // Do we remember the user?
        $this->do_you_remember_me();
                
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
            $where = "username";
            $value = $this->username;   
        }
        elseif ( $this->config->item('login_method') == 'email' )
        {
            $where = "email";
            $value = $this->email;   
        }
        
        return $this->user_model->get_user($where, $value); 
    }
    
    /**
    * Forces a user to be logged in without a password
    * 
    * @param mixed $username
    * @param mixed $config
    */
    public function force_login($username, $config = array())
    {
        $user = $this->user_model->get_user($username);
        
        $user_data = array(
            'user_id'  => $user->id,
            'role_id'  => $user->role_id,
            'username' => $user->username,
            'email'    => $user->email
        );
        $this->session->set_userdata($user_data);
        return true;
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
                // Set remember me
                if ($remember === true)
                {
                    $this->_set_remember_me($user->id);
                }
                
                // Log the user in
                if ( $this->force_login($user->username) )
                {
                    // If we are redirecting after logging in
                    if ( $this->config->item('redirect_after_login') === TRUE )
                    {
                        // If we have a location to redirect too (and we should)
                        if ( $this->config->item('redirect_after_login_location') )
                        {
                            redirect($this->config->item('redirect_after_login_location'));
                        }
                        else
                        {
                            // There is nowhere to redirect, bail out!
                            show_error($this->lang->line('no_login_redirect'));
                        }
                    }   
                }                
            }
        }
        else
        {
            // We are already logged in, redirect
            redirect($this->config->item('default_redirection_url'));
        }
    }
    
    /**
    * Logout
    */
    public function logout($config = array())
    {
        // If we have a user ID, someone is logged in
        if ( $this->user_id > 0 )
        {
            $user_data = array(
                'user_id'  => 0,
                'role_id'  => 0,
                'username' => '',
                'email'    => '',
            );
            $this->session->set_userdata($user_data);
        }
        else
        {
            redirect($this->config->item('default_redirection_url'));
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
    
    /**
    * Sets a user to be remembered
    * 
    * @param mixed $userid
    */
    private function set_remember_me($id)
    {
        $this->load->library('encrypt');

        $token  = md5(uniqid(rand(), TRUE));
        $expiry = $this->config->item('cookie_expiry');

        $remember_me = $this->encrypt->encode(serialize(array($id, $token, $expiry)));

        $cookie = array(
            'name'      => $this->config->item('cookie_name'),
            'value'     => $remember_me,
            'expire'    => $expiry
        );

        // For DB insertion
        $cookie_db_data = array(
            'id' => $id,
            'remember_me' => $remember_me
        );

        set_cookie($cookie);
        $this->update_user($cookie_db_data);
    }
    
    /**
    * Checks if we remember a particular user
    * 
    */
    private function do_you_remember_me()
    {
        $this->load->library('encrypt');

        $cookie_data = get_cookie($this->config->item('cookie_name'));

        // Cookie Monster: Me want cookie. Me want to know, cookie exist?
        if ($cookie_data)
        {
            // Set up some default empty variables
            $id = '';
            $token = '';
            $timeout = '';

            // Unencrypt and unserialize the cookie
            $cookie_data = $this->encrypt->encode(unserialize($cookie_data));

            // If we have cookie data
            if ( !empty($cookie_data) )
            {
                // Make sure we have 3 values in our cookie array
                if ( count($cookie_data) == 3 )
                {
                    // Create variables from array values
                    list($id, $token, $expiry) = $cookie_data;
                }
            }

            // Cookie Monster: Me not eat, EXPIRED COOKIEEEE!
            if ( (int) $expiry < time() )
            {
                delete_cookie($this->config->item('cookie_name'));
                return FALSE;
            }

            // Make sure the user exists by fetching info by their ID
            $data = $this->get_user_by_id($id);

            // If the user obviously exists
            if ($data)
            {
                $this->force_login($data->username);
                $this->set_remember_me($id);

                return TRUE;
            }

        }

        // Cookie Monster: ME NOT FIND COOKIE! ME WANT COOOKIEEE!!!
        return FALSE;
    }

}
