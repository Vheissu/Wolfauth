<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// The config for Wolfauth
$config['wolfauth'] = array(

    // Valid WolfAuth drivers
    'allowed_drivers' => array('auth_simpleauth'),

    // Default WolfAuth driver to use
    'default_driver' => 'simpleauth',

	// Password hashing method
	'hash.method' => 'sha256',

	// Hash key
	'hash.key' => 'kjldf983jj0284378383@#',
	
	// The name of the site (used for emails, etc)
	'site.name' => 'Wolfauth Test',
	
	// Which email address should all auth emails come from
	'site.admin_email' => 'do-not-reply@localhost',
	
	// Site status (0 site is down for maintenance mode, 1 site is active)
	'site.status' => 1,

	// How do users login to the site?
	'login.method' => 'username',

	// The amount of failed login attempts before you're banned for a specified amount of time
	'login.max_attempts' => 3,

	// What roles are considered admin
	'roles.admin' => array('admin', 'super_admin'),
	
	// How do users login; via a username or email address? Default: 'username'
	'login.identity' => 'username',

    // The name of the guest role (the slug not display name)
    'role.guest' => 'guest',

    // The human readable spelling of the guest role
    'role.guest.display_name' => 'Guest'
	
);