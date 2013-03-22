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

// Text for form labels and buttons
$lang['label_username'] = "Username";
$lang['label_password'] = "Password";
$lang['button_login']   = "Login";

// Field names and ID's
$lang['field_username'] = "username";
$lang['field_password'] = "password";
$lang['field_submit']   = "submit";

// Error messages
$lang['error_no_login_redirect']       = "You did not supply a redirection url in the config file, so there is no where to redirect too.";
$lang['error_login_details']           = "The login details that you have supplied are incorrect.";
$lang['error_username_change']         = "Changing the username is not allowed.";
$lang['error_password_mismatch']       = "The old password provided was incorrect.";
$lang['error_password_mismatch_login'] = "The password supplied was incorrect.";
$lang['error_password_update']         = "The password was not changed, something went wrong.";
$lang['error_login']                   = "There was an error logging you in, I don't know what else to say really.";
$lang['error_banned']                  = "Epic fail, your account was banned. You probably know the reason. If this was a mistake, accept our apologies and contact us.";
$lang['error_inactive']                = "Your account is inactive. Probably because you didn't sign in for a while or something.";
$lang['error_validating']              = "You must activate your account before you can use it. Check your email inbox, dude or dudette.";
$lang['error_user_not_added']          = "Looks like there was a problem whilst trying to add a new user, oops.";
$lang['error_user_not_updated']        = "Looks like there was a problem whilst trying to update that user account, oops.";
$lang['error_user_not_deleted']        = "Looks like there was a problem whilst trying to delete that user account, oops.";

// Empty messages
$lang['empty_username']        = "Please enter a username";
$lang['empty_password']        = "You must enter a valid password";
$lang['empty_email']           = "Enter a valid email address, at once!";
$lang['empty_username_update'] = "A username is required to delete a user";

// Success messages
$lang['success_register']  = "Your account has been successfully created.";
$lang['success_activated'] = "Your account has been successfully activated.";
$lang['standard_redirect'] = "Please wait while you are redirected...";

$lang['access_denied']     = "You are not allowed to access this resource. Your attempt has been logged and will be investigated.";

// Activation
$lang['incorrect_activation_code'] = "The activation code supplied is not correct.";
$lang['activate_user_not_exist']   = "Cannot activate a user that does not exist.";
$lang['cannot_activated_banned']   = "Banned users cannot be activated";