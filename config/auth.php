<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Default method for authenticating the user.
// Default: 'username'. Valid values: 'username' or 'email'
$config['login_method'] = "username";

// The role ID of a guest
$config['guest_role_id'] = 0;

// Driver configuration stuff
$config['valid_drivers']  = array('auth_session');
$config['default_driver'] = "session";