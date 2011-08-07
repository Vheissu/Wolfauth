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
 * @license       http://www.apache.org/licenses/LICENSE-2.0.html
 */
 
class Auth extends CI_Driver_Library {
    
    // Default driver
    protected $_adapter;
    
    protected $ci;
    
    public function __construct()
    {
        $this->ci = get_instance();
        $this->ci->config->load('auth');
        
        // If we have a list of valid drivers
        if ( $this->ci->config->item('valid_drivers') )
        {
            $this->valid_drivers = $this->ci->config->item('valid_drivers');
        }
        
        // If we have a default driver set
        if ( $this->ci->config->item('default_driver') )
        {
            $this->_adapter = $this->ci->config->item('default_driver');
        }
                
    }
    
    /**
    * Set Driver
    * Set what driver we are using
    * 
    * @param string $name
    */
    public function set_driver($name)
    {
        $name = trim($name);

        if ( $this->_adapter === $name )
        {
            return TRUE;
        }
        else
        {
            $this->_adapter = $name;
            return TRUE;
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
    
    /**
    * Auth Instance
    * Static function wrapper for auth drivers
    * 
    */
    public static function auth_instance()
    {
        $ci = get_instance();
        return $ci->auth;
    }
    
}

/**
* Auth Instance
* Function shortcut to the proper auth instance
* 
*/
function auth_instance()
{
    return Auth::auth_instance();
}
