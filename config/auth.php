<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Default method for authenticating the user.
// Default: 'username'. Valid values: 'username' or 'email'
$config['login_method'] = "username";

// Should we redirect the user after logging in?
$config['redirect_after_login'] = true;

// Location to redirect user after logging in
$config['redirect_after_login_location'] = "/";

// The role ID of a guest
$config['guest_role_id'] = 0;

// The role ID's for administrators
$config['admin_role_ids'] = array(4, 5);

// Cookie stuff
$config['cookie_name'] = "wolfauth";           // Name of the remember me cookie
$config['cookie_expiry'] = "60 * 60 * 24 * 7"; // One week expiry

// Password stuff
$config['password_length'] = 8; // Randomly generated passwords are this long

// Driver configuration stuff
$config['valid_drivers']  = array('auth_session');
$config['default_driver'] = "session";

// Default location to redirect too if redirection is required
// This will not be used in places a redirection location is
// using its own config value
$config['default_redirection_url'] = "/";