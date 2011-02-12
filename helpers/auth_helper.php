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
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->get_role($userid);
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
    $CI =& get_instance();
    $CI->load->model('auth');

    return $CI->auth->restrict($needles, 'roleid', $redirect_to);
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
    $CI =& get_instance();
    $CI->load->model('auth');

    return $CI->auth->restrict($needles, 'username', $redirect_to);
}

/**
* Will determine if we have any POST data
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
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->is_user($userid);
}

/**
* Check if a user is an administrator. Calling this function by itself
* without arguments will check the current user.
*
* @param mixed $userid
*/
function is_admin($userid = 0)
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->is_admin($userid);
}

/**
* Is there someone currently logged in?
*
*/
function is_logged_in()
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->is_logged_in();
}

/**
* Check if a username exists in the database
*
* @param mixed $username
*/
function username_exists($username = '')
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->username_exists($username);
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
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->login($needle, $password, $redirect);
}

/**
* Forcefully log a user in without a password. Good for testing.
*
* @param mixed $needle
*/
function force_login($needle = '')
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->force_login($needle);
}

/**
* I'm not sure what this function does. Oh, that's right
* it logs users out. Derrrr, me smart.
*
* @param mixed $redirect
*/
function logout($redirect = '')
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->logout($redirect);
}

// User info retrieving functions

/**
* Returns an object of information about the current user.
* @return object
*
*/
function get_this_user()
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->get_this_user();
}

/**
* Get user data via an ID.
*
* @param mixed $userid
*/
function get_user_by_id($userid = 0)
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->get_user_by_id($userid);
}

/**
* Get user data via an email address
*
* @param mixed $email
*/
function get_user_by_email($email = '')
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->get_user_by_email($email);
}

/**
* Get a user via username
*
* @param mixed $username
*/
function get_user_by_username($username = '')
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->get_user_by_username($username);
}

/**
* Gets specific meta data from the database
*
* @param mixed $key
*/
function get_user_meta($key = '')
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->get_user_meta($key);
}

/**
* Get all user meta data from the database for
* a particular user or the currently logged in
* user.
*
* @param mixed $userid
*/
function get_all_user_meta($userid = 0)
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->get_all_user_meta($userid);
}

/**
* Get all users from the database
*
* @param mixed $limit
* @param mixed $offset
*/
function get_users($limit = '', $offset = 0)
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->get_users($limit, $offset);
}

function count_users()
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->count_users();
}

// Updating helper functions

function add_user($user_data = array())
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->add_user($user_data);
}

function add_user_meta($meta_key = '', $meta_value = '', $userid = 0)
{
    $CI =& get_instance();
    
    $CI->load->model('auth');
    return $CI->auth->add_user_meta($meta_key, $meta_value, $userid = 0);
}

function update_user($user_data = array())
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->update_user($user_data);
}

function update_password($username = '', $old_password = '', $new_password = '')
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->update_password($username, $old_password, $new_password);
}

function activate_user($userid = '', $authkey = '')
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->activate_user($userid, $authkey);
}

// Deletion / Removal helper functions

function delete_user($userid = '')
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->delete_user($userid);
}

/**
* Generates a password based on specific length or length
* defined in the config file which by default is 8.
*
* @param mixed $length
*/
function generate_password($length = '')
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->generate_password($length);
}

/**
* Creates a hashed password
*
* @param mixed $password
*/
function hash_password($password = '')
{
    $CI =& get_instance();

    $CI->load->model('auth');
    return $CI->auth->hash_password($password);
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
    $CI =& get_instance();

    return $CI->auth->get_errors($prefix, $suffix);
}

function auth_messages($prefix = '', $suffix = '')
{
    $CI =& get_instance();

    return $CI->auth->get_messages($prefix, $suffix);
}
