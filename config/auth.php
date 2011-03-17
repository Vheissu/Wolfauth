<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Default method for authenticating the user.
// Default: 'username'. Valid values: 'username' or 'email'
$config['login_method'] = "username";

// Should we redirect the user after logging in?
$config['redirect_after_login'] = true;

$config['redirect_after_login_location'] = "/";

// The role ID of a guest
$config['guest_role_id'] = 0;

// The role ID's for administrators
$config['admin_role_ids'] = array(4, 5);

// The name of the remember me cookie
$config['cookie_name'] = "wolfauth";

// How long the remember me cookie lasts
$config['cookie_expiry'] = 60;

// Driver configuration stuff
$config['valid_drivers']  = array('auth_session');
$config['default_driver'] = "session";