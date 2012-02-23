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

class Wolfauth_attempts extends CI_Model {
	
	public function __construct()
	{
		parent::__construct();
	}
	
    /**
    * Update Login Attempts
    * Used by the login function when a user attempts to login
    * unsuccessfully.
    * 
    * @param mixed $ip_address
    */
    public function update_login_attempts($ip_address = NULL)
    {
        if (is_null($ip_address))
            $ip_address = $this->ip_address;
            
        $a = new Attempt();
        $a->where('ip_address', $ip_address)->get();
        
        if ( $a->exists() )
        {
            $current_time = time();
            $created      = strtotime($a->created);
            
            // Minutes comparison
            $minutes      = floor($current_time - $created / 60);
            
	        // If current time elapsed between creation is greater than the attempts, reset
            if (($current_time - $created) > $this->config['auth.login_attempts_expiry'])
            {
                $this->reset_login_attempts($ip_address);
                // add the first attempt after reset them
                $a = $a->get_copy();
                $a->ip_address = $ip_address;
	            $a->attempts   = 1;
	            $a->save();
            }
            else{
	            // Increment new attempts
	            $a->attempts += 1;
	            $a->save();
            }
        }
        else
        {
            $a->ip_address = $ip_address;
            $a->attempts   = 1;
            $a->save();
        }
    }
	
    /**
    * Reset Login Attempts
    * Resets login attempts increment value
    * in the database for a particular IP address.
    * 
    * @param mixed $ip_address
    */
    public function reset_login_attempts($ip_address)
    {
		$this->db->where('ip_address', $ip_address);
		$this->db->delete($this->config->item('table.attempts', 'wolfauth'));
    }
	
}