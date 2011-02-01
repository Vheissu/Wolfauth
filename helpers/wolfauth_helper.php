<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* @name WolfAuth
* @category Helper
* @package WolfAuth
* @author Dwayne Charrington
* @copyright 2011
* @link http://ilikekillnerds.com
*/

/**
* Get a user role for a particular user
* or for the currently logged in user.
* 
* Calling this function without a user ID
* will return the currently logged in role ID
* or the default role Id for logged out users if
* no user is logged in.
* 
*/
function get_role($userid = 0)
{
    $CI = &get_instance();
    
    $CI->load->library('wolfauth');
    $CI->wolfauth->get_role($userid); 
}

/**
* If there is post data then there was a post request
* This simple helper is a conditional check and is the same
* as going:
*  
* if (!empty($_POST)) 
* { 
*   return TRUE; 
* } 
* else 
* { 
*   return FALSE; 
* }
* 
*/
function is_posted()
{
    return (!empty($_POST)) ? TRUE : FALSE;
}

/**
* Log a user into the site by supplying
* the needle and password. The needle is
* the value being sent from your login form
* by default it is a username, but you can
* change this in the wolfauth_config.php file
* 
* @param mixed $needle
* @param mixed $password
*/
function login($needle = '', $password = '')
{
    $CI = &get_instance();
    
    $CI->load->library('wolfauth');
    return $CI->wolfauth->login($needle, $password); 
}