<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* @name Main Auth Driver
* @category Driver
* @package WolfAuth
* @author Dwayne Charrington
* @copyright 2011
* @link http://ilikekillnerds.com
*/

class Auth_driver extends CI_Driver_Library {
    
    protected $_session_data;
    protected $_user_data;
    
    public function __construct()
    {
        // Set the valid drivers
        $this->valid_drivers = array('auth_driver_facebook');
        
        // Don't overwrite user data
        if (empty($this->_user_data))
        {
            $this->_user_data = array();
        }
    }
    
    /**
    * Logs a user in depending on criteria
    * 
    */
    public function login()
    {
        
    }
    
    /**
    * Log a user out depending on criteria
    * 
    */
    public function logout()
    {
        
    }
    
    /**
    * Allows child classes (drivers) to create sessions
    * 
    */
    protected function _set_session()
    {
        
    }
    
    /**
    * Allows child classes (drivers) to store user data
    * 
    */
    protected function _set_user_data()
    {
        
    }
    
}