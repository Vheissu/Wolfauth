<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 * 
 * The config file for all session methods like valid drivers and all
 * that kind of stuff.
 *
 * @package       WolfAuth
 * @subpackage    Config
 * @author        Dwayne Charrington
 * @copyright     Copyright (c) 2011 Dwayne Charrington.
 * @link          http://ilikekillnerds.com
 * @license       Phil Sturgeon's Don't Be A Dick (DBAD) Licence
 */
 
// Valid auth drivers
$config['valid_drivers'] = array('auth_simpleauth', 'auth_facebook');

// Default auth driver to use without the prefixing auth_
$config['default_driver'] = "simpleauth";


/**
* When a user logs in what do they need to supply?
* A username, email address or do we autodetect?
* Valid values; "email", "username" and "auto"
* 
* @var mixed
*/
$config['identity'] = "auto";

// Simpleauth driver config options
$config['simpleauth'] = array(
    "admin_roles"   => array(3,4),  // Admin role IDs
    "cookie_name"   => "wolfauth",  // The name of the remember me cookie
    "cookie_expiry" => 3600         // 1 hour (you should probably make this longer)
);

$config['simpleacl'] = array(
    "method" => "DENIABLE" // Can be DENIABLE (will only allow defined allowed resources) or ALLOWABLE will allow everyone except those on the list.
);

// Facebook driver config options
$config['facebook'] = array(
    "api_key" => "", // Your Facebook API key,
    "secret"  => "", // Secret key
    "generate_session_secret" => FALSE,
    "xbml_src" => "http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/en_US"
);