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

    protected $valid_drivers = array('auth_session');
    protected $_adapter      = "simpleauth"; // Default driver
    
    /**
    * Access sub driver class method or variable
    * 
    * @param mixed $bleh
    */
    public function __get($bleh)
    {
        return $this->{$this->_adapter}->$bleh;
    }
    
}
