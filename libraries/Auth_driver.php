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
    
    public function __construct()
    {
        $this->valid_drivers = array('auth_driver_facebook');
    }
    
}