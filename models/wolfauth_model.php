<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* @name WolfAuth
* @category Model
* @package WolfAuth
* @author Dwayne Charrington
* @copyright 2011
* @link http://ilikekillnerds.com
*/

class WolfAuth_model extends CI_Model {
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
    * Gets information about a particular user
    * Criteria can be any value in the user meta
    * or users table.
    * 
    * @param mixed $criteria
    * @param mixed $userid
    */
    public function get_userinfo($criteria, $userid)
    {
        switch ($criteria)
        {
            case 'all':
            break;
            
            case 'username':
            break;
            
            case 'password':
            break;
            
            case 'email':
            break;
            
            case 'user_id':
            break;
            
            case 'role_id':
            break;
            
        }
    }
    
    /**
    * Logs a user in based on particular criteria
    * usually an email address or username.
    * 
    * Default criteria is 'username'
    * 
    * @param mixed $criteria
    * @param mixed $password
    */
    public function login($criteria, $password)
    {
        
    }
    
}