<?php

// ===========================================================
// Auth core functions (login, etc)
// ===========================================================

function logged_in()
{
    return auth_instance()->logged_in();  
}

function get_user_info($user_id = 0)
{
    return auth_instance()->get_user_info($user_id);
}

function get_user_by($identity)
{
    return auth_instance()->get_user_by($identity);
}

function login($identity, $password, $remember = FALSE)
{
    return auth_instance()->login($identity, $password, $remember);
}

function logout($redirect = '')
{
    return auth_instance()->logout($redirect); 
}

function register($username, $email, $password, $fields = array())
{
    return auth_instance()->create_user($username, $email, $password, $fields);
}

function update_user($user_id, $fields = array())
{
    return auth_instance()->update_user($user_id, $fields);
}

function update_user_meta($user_id, $fields = array())
{
    return auth_instance()->update_user_meta($user_id, $fields);
}

function delete_user($user_id)
{
    return auth_instance()->delete_user($user_id);
}

// ===========================================================
// End auth core functions (login, etc)
// ===========================================================




// ===========================================================
// Id Functions
// ===========================================================

function get_user_id($username)
{
    return auth_instance()->get_user_id($username); 
}

function get_role_id($role_name)
{
    return auth_instance()->get_role_id($role_name); 
}

function get_permission_id($permission = '')
{
    return auth_instance()->get_permission_id($permission = ''); 
}

// ===========================================================
// End Id Functions
// ===========================================================




// ===========================================================
// Exists functions
// ===========================================================

function role_id_exists($role_id) 
{
    return auth_instance()->role_id_exists($role_id);
}

function role_name_exists($role_name) 
{
    return auth_instance()->role_name_exists($role_name);
}

function user_id_exists($user_id) 
{
    return auth_instance()->user_id_exists($user_id);
}

function username_exists($username) 
{
    return auth_instance()->username_exists($username);
}

function email_exists($email) 
{
    return auth_instance()->email_exists($email);
}

// ===========================================================
// End exists functions
// ===========================================================




// ===========================================================
// List functions functions
// ===========================================================

function list_users($count = 10000, $offset = 0)
{
    return auth_instance()->list_users($count, $offset);
}

function list_roles($count = 10000, $offset = 0)
{
    return auth_instance()->list_roles($count, $offset);
}

function list_permissions($count = 10000, $offset = 0)
{
    return auth_instance()->list_permissions($count, $offset);
}

// ===========================================================
// End list functions functions
// ===========================================================