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
* Get a user role for a particular user or for the currently logged in user.
* 
* Calling this function without a user ID will return the currently logged 
* in role ID or the default role Id for logged out users if no user is 
* logged in.
* 
*/
function get_role($userid = 0)
{
    $CI = &get_instance();
    
    $CI->load->model('auth_model');
    return $CI->auth_model->get_role($userid); 
}

/**
* Retrict access to a particular controller or page to people with certain role ID's
* 
* @param mixed $allowed_roles
* @param mixed $redirect_to
* @return bool
*/
function restrict($needles = '', $redirect_to = '')
{
    $CI = &get_instance();
    $CI->load->model('auth_model');
    
    return $CI->auth_model->restrict($needles, 'roleid', $redirect_to);
}

/**
* Retrict access to a particular controller or page to people with particular usernames
* 
* @param mixed $allowed_usernames
* @param mixed $redirect_to
* @return bool
*/
function restrict_usernames($needles = '', $redirect_to = '')
{
    $CI = &get_instance();
    $CI->load->model('auth_model');
    
    return $CI->auth_model->restrict($needles, 'username', $redirect_to);
}

/**
* If there is post data then there was a post request. This simple helper is 
* a conditional check and is the same as going:
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
* Check if a user is a standard user. Calling this function by itself
* without arguments will check the current user.
* 
* @param mixed $userid
*/
function is_user($userid = 0)
{
    $CI = &get_instance();
    
    $CI->load->model('auth_model');
    return $CI->auth_model->is_user($userid); 
}

/**
* Check if a user is an administrator. Calling this function by itself
* without arguments will check the current user.
* 
* @param mixed $userid
*/
function is_admin($userid = 0)
{
    $CI = &get_instance();
    
    $CI->load->model('auth_model');
    return $CI->auth_model->is_admin($userid); 
}

/**
* Log a user into the site by supplying the needle and password. 
* The needle is the value being sent from your login form by 
* default it is a username, but you can change this in the 
* wolfauth_config.php file
* 
* @param mixed $needle
* @param mixed $password
*/
function login($needle = '', $password = '', $redirect = '')
{
    $CI = &get_instance();
    
    $CI->load->model('auth_model');
    return $CI->auth_model->login($needle, $password, $redirect); 
}

/**
* WolfAuth errors helper function which will retrieve any errors in 
* the WolfAuth function and then display them when this function is 
* called.
* 
* @param mixed $prefix
* @param mixed $suffix
*/
function auth_errors($prefix = '', $suffix = '')
{
    if (FALSE === ($WA = &get_auth_class()))
    {
        return '';
    }

    return $WA->get_errors($prefix, $suffix);
}

/**
* Return the WolfAuth library class by reference so we can access the 
* errors array variable populated inside of the library.
* 
*/
function &get_auth_class()
{
    $CI =& get_instance();

    $return = FALSE;

    if ( ! isset($CI->load->_ci_classes) OR  ! isset($CI->load->_ci_classes['auth_model']))
    {
        return $return;
    }
    
    $object = $CI->load->_ci_classes['auth_model'];

    if ( ! isset($CI->$object) OR ! is_object($CI->$object) )
    {
        return $return;
    }

    return $CI->$object;
}