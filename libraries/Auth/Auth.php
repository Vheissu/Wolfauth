<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 *
 * @package       WolfAuth
 * @author        Dwayne Charrington
 * @copyright     Copyright (c) 2011 Dwayne Charrington.
 * @link          http://ilikekillnerds.com
 */
 
class Auth extends CI_Driver_Library {
    
    protected $ci;
    
    /**
    * Constructor
    */
    public function __construct($adapter = '')
    {
        $this->ci = get_instance();      // Get Codeigniter instance
        $this->ci->load->config('auth'); // Load config file
        
        // Get valid drivers
        foreach ($this->ci->config->item('valid_drivers') AS $driver)
        {
            $this->valid_drivers[] = $driver;
        }
        
        // If we have been supplied a default adapter to this function, load it.
        if ( !is_empty($adapter) )
        {
            $this->_adapter = trim($adapter);
        }
        else
        {
            // No default adapter supplied, so make it 'session'
            $this->_adapter = "session";   
        }
    }
    
    /**
    * If a function isn't found, search our driver for the method or variable
    * 
    * @param mixed $bleh
    */
    public function __get($bleh)
    {
        return $this->{$this->_adapter}->$bleh;
    }
    
    /**
    * Is a user currently logged in?
    * 
    * @param mixed $config
    */
    public function logged_in($config = array())
    {
        return $this->{$this->_adapter}->logged_in($config);
    }
    
    /**
    * Get a user
    * 
    * @param mixed $config
    */
    public function get_this_user($config = array())
    {
        return $this->{$this->_adapter}->get_this_user($config);
    }
    
    /**
    * Get a user by their user ID
    * 
    * @param mixed $id
    * @param mixed $config
    */
    public function get_user_by_id($id, $config = array())
    {
        return $this->{$this->_adapter}->get_user_by_id($id, $config);
    }
    
    /**
    * Get a particular user by username
    * 
    * @param mixed $username
    * @param mixed $config
    */
    public function get_user_by_username($username, $config = array())
    {
        return $this->{$this->_adapter}->get_user_by_username($username, $config);
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
        return $this->{$this->_adapter}->login($username, $password, $remember, $config);   
    }
    
    /**
    * Logout
    */
    public function logout($config = array())
    {
        return $this->{$this->_adapater}->logout($config);
    }
    
    /**
    * Forces a user to be logged in
    * 
    * @param mixed $username
    * @param mixed $config
    */
    public function force_login($username, $config = array())
    {
        $this->{$this->_adapter}->force_login($username, $config);
    }
    
    /**
    * Restrict access to a function or class method
    * 
    * @param mixed $needle
    * @param mixed $criteria can be 'role' or 'username'
    * @param mixed $config
    */
    public function restrict_to($needle, $criteria = 'role', $config = array())
    {
        $this->{$this->_adapter}->restrict_to($needle, $criteria, $config);
    }
    
    /**
    * Add a new user
    * 
    * @param mixed $data
    * @param mixed $config
    */
    public function add_user($data, $config = array())
    {
        $this->{$this->_adapter}->add_user($data, $config);
    } 
       
    /**
    * Edit a user
    * 
    * @param mixed $data
    * @param mixed $config
    */
    public function edit_user($data, $config = array())
    {
        $this->{$this->_adapter}->edit_user($data, $config);
    }
           
    /**
    * Delete a user
    * 
    * @param mixed $id
    * @param mixed $config
    */
    public function delete_user($id, $config = array())
    {
        $this->{$this->_adapter}->delete_user($id, $config);
    }
    
    /**
    * Hashes a password with optional hash
    * 
    * @param mixed $password
    * @param mixed $hash
    * @param mixed $config
    */
    public function hash_password($password, $hash = '', $config = array())
    {
        return $this->{$this->_adapter}->hash_password($password, $hash, $config);
    }
    
    /**
    * Change a password for a particular user ID
    * 
    * @param mixed $id
    * @param mixed $old
    * @param mixed $new
    * @param mixed $config
    */
    public function change_password($id, $old, $new, $config = array())
    {
        return $this->{$this->_adapter}->change_password($password, $hash, $config);
    }
    
    /**
    * Change a user password without knowing the old one
    * 
    * @param mixed $id
    * @param mixed $password
    * @param mixed $config
    */
    public function force_change_password($id, $password, $config = array())
    {
        return $this->{$this->_adapter}->force_change_password($id, $password, $config);
    }
    
}
