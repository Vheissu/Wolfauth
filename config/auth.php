<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* @name WolfAuth
* @category Config
* @package WolfAuth
* @author Dwayne Charrington
* @copyright 2011
* @link http://ilikekillnerds.com
*/


/**
* Define the tables that WolfAuth needs for database shiz
*
* 'users' is the user table
* 'user_meta' is the user meta table
* 'roles' is the site roles table
*
*/
$config['tables'] = array
(
    'users'     => 'users',
    'user_meta' => 'usermeta',
    'roles'     => 'roles',
);

$config['site_name'] = "WolfAuth";

/**
* Send an email to a user after they activate their account
*
* @var mixed
*/
$config['send_email_after_activation'] = "TRUE";

/**
* Require activation of user accounts?
* Default: true
*
* @var mixed
*/
$config['require_activation'] = "TRUE";

/**
* Activation Method
* How is the user to be activated?
*
* Supported values: auto and manual.
* Default: auto
*
* AUTO means that an email is sent to the user
* asking them to activate their account and MANUAL
* means that a site administrator must activate
* the account.
*
* @var mixed
*/
$config['activation_method'] = "AUTO";

/**
* Meta fields are values in the usermeta table that will be used
* for adding user / editing user functions.
*
* @var mixed
*/
$config['meta_fields'] = array
(
    'first_name',
    'last_name',
    'dob'
);

/**
* The URL users are sent to, to activate their accounts.
* The base url will be prepended before this so, you only
* need to provide the controller and function handling
* the activation. See the default below.
*
* @var mixed
*/
$config['activation_url'] = "user/activate_account/";

/**
* When generating a random password this is the
* length the generated password will be.
*
* @var mixed
*/
$config['password_length'] = 8;

/**
* How are we going to validate this user at login?
* Default criteria is username, but you can also use
* email as well.
*
* @var mixed
*/
$config['identity_criteria'] = 'username';

/**
* The email address of the website administrator
*
*/
$config['admin_email'] = "admin@website.com";

/**
* An array of IDs that have admin priveleges
*
*/
$config['admin_roles'] = array(4,5);

/**
* All guest users will be assigned this ID
*
* @var mixed
*/
$config['guest_role'] = "0";

/**
* How long remembet me cookies last for?
* The default is one week.
*
* @var mixed
*/
$config['cookie_expiry'] = "60 * 60 * 24 * 7"; // One week expiry

/**
* Passwords are salted with this code. If you change this, any accounts created
* using this salt value will not work any more. Leave it the following value
* if you can.
*
* @var mixed
*/
$config['password_hash'] = "kjgkds09gs8d09g8s092523523758237";

// =========================
// Error Settings
// =========================

/**
* What is placed before the error message?
*
* @var mixed
*/
$config['error_prefix'] = "<p class='wolfauth_error'>";

/**
* What is placed after the error message (at the end)?
*
* @var mixed
*/
$config['error_suffix'] = "</p>";

// =========================
// Message Settings
// =========================

/**
* What is placed before the message?
*
* @var mixed
*/
$config['message_prefix'] = "<p class='wolfauth_message'>";

/**
* What is placed at the end of the message?
*
* @var mixed
*/
$config['message_suffic'] = "</p>";

// =========================
// Email Settings
// =========================

$config['email']['protocol']  = "smtp";
$config['email']['smtp_host'] = "ssl://smtp.googlemail.com";
$config['email']['smtp_port'] = "465";
$config['email']['smtp_user'] = "";
$config['email']['smtp_pass'] = "";
$config['email']['mailpath']  = "";
$config['email']['newline']   = "\r\n";


$config['email']['email_from_address'] = "";
$config['email']['email_from_name'] = "Wolf from Websiteapp.com";

$config['email']['mailtype']  = "html";
$config['email']['charset']   = "utf-8";
$config['email']['wordwrap']  = FALSE;
$config['email']['useragent'] = "WolfAuth";
