<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* @name WolfAuth
* @category Config
* @package WolfAuth
* @author Dwayne Charrington
* @copyright 2011
* @link http://ilikekillnerds.com
*/

/**
* Define the tables that WolfAuth needs for database shiz
* 
* 'users' is the user table
* 'user_meta' is the user meta table
* 'roles' is the site roles table
* 
*/
$config['tables'] = array(
    'users'     => 'users',
    'user_meta' => 'user_meta',
    'roles'     => 'roles',
);

/**
* How are we going to validate this user at login?
* Default criteria is username, but you can also use
* email as well.
* 
* @var mixed
*/
$config['identity_criteria'] = 'username';

/**
* The email address of the website administrator
* 
*/
$config['admin_email'] = "admin@website.com";

/**
* An array of IDs that have admin priveleges
* 
*/
$config['admin_roles'] = array(6,7);

/**
* All guest users will be assigned this ID
* 
* @var mixed
*/
$config['guest_role'] = 0;