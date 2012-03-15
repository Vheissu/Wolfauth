<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// Include user defined roles
include 'wolfauth_roles.php';

// File defined permissions for accessing things
include 'wolfauth_permissions.php';

// The config for Wolfauth
$config['wolfauth'] = array(
	
	// The name of the site (used for emails, etc)
	'site.name' => 'Wolfauth Test',
	
	// Which email address should all auth emails come from
	'site.admin_email' => 'do-not-reply@localhost',
	
	// Site status (0 site is down for maintenance mode, 1 site is active)
	'site.status' => 1,

	// The amount of failed login attempts before you're banned for a specified amount of time
	'login.max_attempts' => 3,
	
	// How do users login; via a username or email address? Default: 'username'
	'login.identity' => 'username',
	
	// To add new default roles, please edit the file wolfauth_roles.php in the same directory as this file
	'roles.user'   => $user_roles,
	'roles.guest'  => $guest_roles,
	'roles.editor' => $editor_roles,
	'roles.admin'  = $admin_roles
	
);