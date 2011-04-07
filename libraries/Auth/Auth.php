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
 * @license       Phil Sturgeon's Don't Be A Dick (DBAD) Licence
 */
 
class Auth extends CI_Driver_Library {
    
    // Default driver
    protected $_adapter;
    
    public function __construct()
    {
        $ci = get_instance();
        $ci->config->load('auth');
        
        // If we have a list of valid drivers
        if ( $ci->config->item('valid_drivers') )
        {
            $this->valid_drivers = $ci->config->item('valid_drivers');
        }
        
        // If we have a default driver set
        if ( $ci->config->item('default_driver') )
        {
            $this->_adapter = $ci->config->item('default_driver');
        }
                
    } 
    
    /**
    * Redirect all method calls not in this class to the child class
    * set in the variable _adapter which is the default class.
    * 
    * @param mixed $child
    * @param mixed $arguments
    * @return mixed
    */
    public function __call($child, $arguments)
    {
        return call_user_func_array(array($this->{$this->_adapter}, $child), $arguments);
    }
    
}
