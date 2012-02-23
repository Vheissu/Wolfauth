<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 *
 * @package   WolfAuth
 * @author    Dwayne Charrington
 * @copyright Copyright (c) 2012 Dwayne Charrington.
 * @link      http://ilikekillnerds.com
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html
 * @version   2.0
 */

class Auth_Acl extends CI_Driver {
	
	protected $CI;
	
	public function __construct()
	{
		// Codeigniter instance
		$this->CI =& get_instance();
		
		// Load our helpers for permissions and roles
		$this->CI->load->helper('wolfauth/auth_permissions');
		$this->CI->load->helper('wolfauth/auth_roles');
		
		$this->CI->load->model('wolfauth/wolfauth_permissions');
		$this->CI->load->model('wolfauth/wolfauth_roles');
	}
	
	public function has_permission($permission)
	{
        if ($permission == '') {
			// The permission string by default is the whole URL string
            $permission = trim($this->ci->uri->uri_string(), '/');
		}
	}

}