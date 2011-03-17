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
        $this->load->library('session');
    }
    
    /**
    * Just for decoration, ha ha.
    * 
    */
    public function decorate() {}
    
    /**
    * Is anyone logged in?
    * 
    */
    public function logged_in($config = array())
    {
        
    }
    
    /**
    * Gets the currently logged in user
    * 
    */
    public function get_user($config = array())
    {
        
    }
    
    /**
    * Force a user to be logged in without a password
    * 
    * @param mixed $username
    */
    public function force_login($username, $config = array())
    {
        
    }
    
    /**
    * Login function
    * 
    * @param mixed $username
    * @param mixed $password
    * @param mixed $remember
    */
    public function login($username, $password, $remember = false, $config = array())
    {
        
    }
    
    /**
    * Restrict access to a function or class method
    * 
    * @param mixed $needle
    * @param mixed $criteria
    */
    public function restrict_access($needle, $criteria = 'role', $config = array())
    {
        
    }
    
    /**
    * Add a new user
    * 
    * @param mixed $data
    */
    public function add_user($data, $config = array())
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

}