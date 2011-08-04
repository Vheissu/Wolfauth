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

function login($login, $password, $remember = FALSE)
{
    return auth_instance()->login($login, $password, $remember);
}

function logout()
{
    return auth_instance()->logout(); 
}

function create_user($login, $password, $fields = array(), $roles = array(), $groups = array())
{
    return auth_instance()->create_user($login, $password, $fields, $roles, $groups);
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
// Has permission helper functions
// ===========================================================

function user_has_permission($user_id, $permission = '')
{
    return auth_instance()->user_has_permission($user_id, $permission);
}

function group_has_permission($group_id, $permission = '')
{
    return auth_instance()->group_has_permission($group_id, $permission);
}

function role_has_permission($role_id, $permission = '')
{
    return auth_instance()->role_has_permission($role_id, $permission);
}

// ===========================================================
// End has permission helper functions
// ===========================================================




// ===========================================================
// Id Functions
// ===========================================================

function get_user_id($username)
{
    return auth_instance()->get_user_id($username); 
}

function get_group_id($group_name)
{
    return auth_instance()->get_group_id($group_name); 
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

function group_id_exists($group_id) 
{
    return auth_instance()->group_id_exists($group_id);
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
// Role functions
// ===========================================================

function add_role_to_group($group_id, $role_id)
{
    return auth_instance()->add_role_to_group($group_id, $role_id);
}

function remove_role_from_group($group_id, $role_id)
{
    return auth_instance()->remove_role_from_group($group_id, $role_id);
}

function get_user_roles($user_id = 0)
{
    return auth_instance()->get_user_roles($user_id);
}

function get_group_roles($group_id)
{
    return auth_instance()->get_group_roles($group_id);
}

// ===========================================================
// End role functions
// ===========================================================



// ===========================================================
// Group functions
// ===========================================================

function add_user_to_group($user_id, $group_id)
{
   return auth_instance()->add_user_to_group($user_id, $group_id); 


function remove_user_from_group($user_id, $group_id)
{
   return auth_instance()->remove_user_from_group($user_id, $group_id); 
}

// ===========================================================
// End group functions
// ===========================================================




// ===========================================================
// Permission functions
// ===========================================================

function add_permission($permission)
{
    return auth_instance()->add_permission($permission);
}

function remove_permission($permission)
{
    return auth_instance()->remove_permission($permission);
}

function add_permission_to_user($permission_id, $user_id)
{
    return auth_instance()->add_permission_to_user($permission_id, $user_id);
}

function remove_permission_from_user($permission_id, $user_id)
{
    return auth_instance()->remove_permission_from_user($permission_id, $user_id);
}

function add_permission_to_role($permission_id, $role_id)
{
    return auth_instance()->add_permission_to_role($permission_id, $role_id);
}

function remove_permission_from_role($permission_id, $role_id)
{
    return auth_instance()->remove_permission_from_role($permission_id, $role_id);
}

function add_permission_to_group($permission_id, $group_id)
{
    return auth_instance()->add_permission_to_group($permission_id, $group_id);
}

function remove_permission_from_group($permission_id, $group_id)
{
    return auth_instance()->remove_permission_from_group($permission_id, $group_id);
}

// ===========================================================
// End permission functions
// ===========================================================




// ===========================================================
// List functions functions
// ===========================================================

function list_users($count = 10000, $offset = 0)
{
    return auth_instance()->list_users($count, $offset);
}

function list_groups($count = 10000, $offset = 0)
{
    return auth_instance()->list_groups($count, $offset);
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