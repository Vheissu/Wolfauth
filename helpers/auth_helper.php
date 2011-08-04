<?php

// ===========================================================
// Auth core functions (login, etc)
// ===========================================================

function logged_in()
{
    $ci = get_instance();
    return $ci->auth->logged_in();  
}

function get_user_info($user_id = 0)
{
    $ci = get_instance();
    return $ci->auth->get_user_info($user_id);
}

function get_user_by($identity)
{
    $ci = get_instance();
    return $ci->auth->get_user_by($identity);
}

function login($login, $password, $remember = FALSE)
{
    $ci = get_instance();
    return $ci->auth->login($login, $password, $remember);
}

function logout()
{
    $ci = get_instance();
    return $ci->auth->logout(); 
}

function create_user($login, $password, $fields = array(), $roles = array(), $groups = array())
{
    $ci = get_instance();
    return $ci->auth->create_user($login, $password, $fields, $roles, $groups);
}

function update_user($user_id, $fields = array())
{
    $ci = get_instance();
    return $ci->auth->update_user($user_id, $fields);
}

function update_user_meta($user_id, $fields = array())
{
    $ci = get_instance();
    return $ci->auth->update_user_meta($user_id, $fields);
}

function delete_user($user_id)
{
    $ci = get_instance();
    return $ci->auth->delete_user($user_id);
}

// ===========================================================
// End auth core functions (login, etc)
// ===========================================================




// ===========================================================
// Has permission helper functions
// ===========================================================

function user_has_permission($user_id, $permission = '')
{
    $ci = get_instance();    
    return $ci->auth->user_has_permission($user_id, $permission);
}

function group_has_permission($group_id, $permission = '')
{
    $ci = get_instance();   
    return $ci->auth->group_has_permission($group_id, $permission);
}

function role_has_permission($role_id, $permission = '')
{
    $ci = get_instance();    
    return $ci->auth->role_has_permission($role_id, $permission);
}

// ===========================================================
// End has permission helper functions
// ===========================================================




// ===========================================================
// Id Functions
// ===========================================================

function get_user_id($username)
{
    $ci = get_instance();
    return $ci->auth->get_user_id($username); 
}

function get_group_id($group_name)
{
    $ci = get_instance();
    return $ci->auth->get_group_id($group_name); 
}

function get_role_id($role_name)
{
    $ci = get_instance();
    return $ci->auth->get_role_id($role_name); 
}

function get_permission_id($permission = '')
{
    $ci = get_instance();
    return $ci->auth->get_permission_id($permission = ''); 
}

// ===========================================================
// End Id Functions
// ===========================================================




// ===========================================================
// Exists functions
// ===========================================================

function role_id_exists($role_id) 
{
    $ci = get_instance();
    return $ci->auth->role_id_exists($role_id);
}

function group_id_exists($group_id) 
{
    $ci = get_instance();
    return $ci->auth->group_id_exists($group_id);
}

function user_id_exists($user_id) 
{
    $ci = get_instance();
    return $ci->auth->user_id_exists($user_id);
}

function username_exists($username) 
{
    $ci = get_instance();
    return $ci->auth->username_exists($username);
}

function email_exists($email) 
{
    $ci = get_instance();
    return $ci->auth->email_exists($email);
}

// ===========================================================
// End exists functions
// ===========================================================




// ===========================================================
// Permission functions
// ===========================================================

function add_permission($permission)
{
    return $ci->auth->add_permission($permission);
}

function remove_permission($permission)
{
    return $ci->auth->remove_permission($permission);
}

function add_permission_to_user($permission_id, $user_id)
{
    return $ci->auth->add_permission_to_user($permission_id, $user_id);
}

function remove_permission_from_user($permission_id, $user_id)
{
    return $ci->auth->remove_permission_from_user($permission_id, $user_id);
}

function add_permission_to_role($permission_id, $role_id)
{
    return $ci->auth->add_permission_to_role($permission_id, $role_id);
}

function remove_permission_from_role($permission_id, $role_id)
{
    return $ci->auth->remove_permission_from_role($permission_id, $role_id);
}

function add_permission_to_group($permission_id, $group_id)
{
    return $ci->auth->add_permission_to_group($permission_id, $group_id);
}

function remove_permission_from_group($permission_id, $group_id)
{
    return $ci->auth->remove_permission_from_group($permission_id, $group_id);
}

// ===========================================================
// End permission functions
// ===========================================================




// ===========================================================
// List functions functions
// ===========================================================

function list_users($count = 10000, $offset = 0)
{
    return $ci->auth->list_users($count, $offset);
}

function list_groups($count = 10000, $offset = 0)
{
    return $ci->auth->list_groups($count, $offset);
}

function list_roles($count = 10000, $offset = 0)
{
    return $ci->auth->list_roles($count, $offset);
}

function list_permissions($count = 10000, $offset = 0)
{
    return $ci->auth->list_permissions($count, $offset);
}

// ===========================================================
// End list functions functions
// ===========================================================