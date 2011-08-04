<?php

$ci = get_instance();
$ci->load->driver('auth');

function has_access($roleid)
{    
    return $ci->auth->simpleacl->has_access($roleid);
}

function login($login, $password, $remember = FALSE)
{
    return $ci->auth->login($login, $password, $remember);
}

function logout()
{
    return $ci->auth->logout(); 
}

function create_user($login, $password, $fields = array(), $roles = array())
{
    return $ci->auth->create_user($login, $password, $fields = array(), $roles = array());
}

function update_user($user_id, $fields = array())
{
    return $ci->auth->update_user($user_id, $fields = array());
}

function delete_user($user_id)
{
    return $ci->auth->delete_user($user_id);
}
