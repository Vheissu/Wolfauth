<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 *
 * @package   WolfAuth
 * @author    Dwayne Charrington
 * @copyright Copyright (c) 2012 Dwayne Charrington.
 * @link      http://ilikekillnerds.com
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html
 * @version   2.0
 */

// Valid drivers for Wolfauth, add in 3rd party driver names here
$config['drivers'] = array('auth_acl', 'auth_simpleauth');

// Default driver to use
$config['default_driver'] = "auth_simpleauth";

// Identity to valid by; 'username', 'email' or 'auto' - auto will determine if a username or email
$config['identity_method'] = "auto";

// The name of the site
$config['site_name'] = "WolfAuth";

// The email of the site administrator
$config['admin_email'] = "test@domain.com";

// The location of the password reset link
$config['reset_password_link'] = "testauth/resetpassword";

// Name of our auth cookie
$config['cookie.name'] = "wolfauth";

// How long in seconds this cookie will last until expiry
// Default is 7 days
$config['cookie.expiry'] = 604800;

// Wolfauth models
$config['model.user']     = "wolfauth/wolfauth_users";
$config['model.email']    = "wolfauth/wolfauth_email";
$config['model.attempts'] = "wolfauth/wolfauth_attempts";
$config['model.acl']      = "wolfauth/wolfauth_acl";

// Wolfauth table names
$config['table.users']       = "users";
$config['table.roles']       = "roles";
$config['table.attempts']    = "attempts";
$config['table.permissions'] = "permissions";
$config['table.sessions']    = "sessions";

// Admin roles by default
$config['roles.admin'] = array('administrator');

// Roles which identify a guest (should usually only be one)
$config['roles.guest'] = array('guest');

// Require users to activate their accounts via an email link
$config['require_activation'] = false;

?>