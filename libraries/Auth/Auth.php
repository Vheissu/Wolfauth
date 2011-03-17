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
    public function __construct()
    {
        // Get Codeigniter instance
        $this->ci = get_instance();
        
        // Load config file
        $this->ci->load->config('auth');
        
        // Get valid drivers
        foreach ($this->ci->config->item('valid_drivers') AS $driver)
        {
            $this->valid_drivers[] = $driver;
        }
        
        // Set default driver
        $this->_adapter = $this->ci->config->item('default_driver');
    }
    
    public function logged_in($config = array())
    {
        $this->{$this->_adapter}->logged_in($config);
    }
    
    public function get_user($config = array())
    {
        $this->{$this->_adapter}->get_user();
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
        $this->{$this->_adapter}->login($username, $password, $remember, $config);   
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
    
}
