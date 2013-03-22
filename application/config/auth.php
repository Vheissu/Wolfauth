<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 *
 * @package   WolfAuth
 * @author    Dwayne Charrington
 * @copyright Copyright (c) 2013 Dwayne Charrington.
 * @link      http://ilikekillnerds.com
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html
 * @version   2.0
 */

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
	
);