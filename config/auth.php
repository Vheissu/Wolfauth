<?php

$config['admin_email'] = "admin@localhost";
$config['site_name'] = "Test Auth";

$config['reset_password_link'] = "user/reset_password/";

$config['default_driver'] = "simpleauth";
$config['valid_drivers']  = array('auth_simpleauth');

/**
* Simpleauth driver config values
* 
* @var mixed
*/
$config['simpleauth'] = array(

    'auth.login_type'            => 'auto',
    'auth.min_password_length'   => 8,
    'auth.max_password_length'   => 30,
    'auth.max_login_attempts'    => 4,
    'auth.login_attempts_expiry' => 900 // 15 minutes
    
);

$config['facebook'] = array(

    'facebook.api_url'    => 'graph.facebook.com',
    'facebook.api_key'    => '',
    'facebook.api_secret' => '',

);
