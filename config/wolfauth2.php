<?php

$config['wolfauth'] = array(


	// Valid drivers for the Auth class
	'drivers' => array('auth_simpleauth'),
	
	// The default auth driver to use
	'driver'   => 'simpleauth',

	// Require users to activate their accounts
	'site.require_activation' => false,

	// The default login identity method to use (username or email)
	'identity' => 'username',

	// The name of your site used for emails
	'site.name' => 'WolfAuth',

	// The email address of the site administrator
	'site.admin_email' => 'test@domain.com',

	// The password reset link sans the http://www part
	'reset_password_link' => 'testauth/resetpassword',

	// The name to use for session cookies
	'cookie.name' => 'wolfauth',

	// Expiry of site cookies. Default is 7 days
	'cookie.expiry' => 604800,



	// Table names

	'table.users'       => 'table.users',
	'table.roles'       => 'roles',
	'table.attempts'    => 'attempts',
	'table.permissions' => 'permissions',
	'table.sessions'    => 'ci_sessions',



	// Default roles

	// Define admin roles here
	'roles.admin' => array('admin'),
	'roles.guest' => array('guest'),



	// Attempts

	// Time between max allowed tries (15 minutes)
	'attempts.expiry'  => 900,

	// Number of tries allowed before blocked for above time
	'attempts.maximum' => 3

);