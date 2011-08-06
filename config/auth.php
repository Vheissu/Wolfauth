<?php

$config['default_driver'] = "simpleauth";

$config['valid_drivers']  = array('auth_simpleauth', 'auth_facebook');

$config['admin_email'] = "admin@localhost";
$config['site_name'] = "Test Auth";

$config['reset_password_link'] = "user/reset_password/";

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

    'facebook.authorise_url' => 'https://graph.facebook.com/oauth/authorize',
    'facebook.token_url'     => 'https://graph.facebook.com/oauth/access_token',
    'facebook.profile_url'   => 'https://graph.facebook.com/me',
    'facebook.app_id'        => '',
    'facebook.api_key'       => '',
    'facebook.api_secret'    => '',

);
