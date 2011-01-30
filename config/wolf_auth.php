<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
* The email address of the website administrator
* 
*/
$config['admin_email'] = "admin@website.com";

/**
* An array of IDs that have admin priveleges
* 
*/
$config['admin_roles'] = array(6,7);