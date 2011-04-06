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
    protected $_adapter = 'simpleauth';
    
    // Valid drivers
    protected $valid_drivers = array('auth_simpleauth'); 
    
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
