<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
   
class Auth extends CI_Driver_Library {
    
    protected $ci;
    
    /**
    * Constructor
    * 
    */
    public function __construct()
    {
        
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
    
    /**
    * Return an instance of this class
    * 
    */
    public static function instance()
    {
        static $instance;

        empty($instance) and $instance = new Auth();

        return $instance;
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
    */
    public function login($username, $password, $remember = false, $config = array())
    {
        $this->{$this->_adapter}->login($username, $password, $remember, $config);   
    }
    
    /**
    * Forces a user to be logged in
    * 
    * @param mixed $username
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
    */
    public function restrict_access($needle, $criteria = 'role', $config = array())
    {
        $this->{$this->_adapter}->restrict_access($needle, $criteria, $config);
    }
    
    /**
    * Adds a new user in the form of an array of info for full extensibility.
    * 
    * @param mixed $data
    */
    public function add_user($data)
    {
        $this->{$this->_adapter}->add_user($data);
    }
    
    /**
    * Hashes a password with optional hash
    * 
    * @param mixed $password
    * @param mixed $hash
    */
    public function hash_password($password, $hash = '', $config = array())
    {
        return $this->{$this->_adapter}->hash_password($password, $hash, $config);
    }
    
}