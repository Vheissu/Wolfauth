<?php

function add_role($role_id, $role_name)
{
    return auth_instance()->add_role($role_id, $role_name);
}

function update_role($role_id, $role_name)
{
    return auth_instance()->update_role($role_id, $role_name);
}

function remove_role($role_name)
{
    return auth_instance()->remove_role($role_name);
}

function user_role($role_id, $user_id)
{
	return auth_instance()->user_role($role_id, $user_id);
}