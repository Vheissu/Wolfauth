<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 *
 * @package   WolfAuth
 * @author    Dwayne Charrington
 * @copyright Copyright (c) 2012 Dwayne Charrington.
 * @link      http://ilikekillnerds.com
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html
 * @version   2.0
 */

function role_has_permission($role_id, $permission = '')
{
    return auth_instance()->role_has_permission($role_id, $permission);
}

function has_permission($permission)
{
	return auth_instance()->has_permission($permission);
}

function add_permission($permission)
{
    return auth_instance()->add_permission($permission);
}

function remove_permission($permission)
{
    return auth_instance()->remove_permission($permission);
}

function add_permission_to_role($permission_id, $role_id)
{
    return auth_instance()->add_permission_to_role($permission_id, $role_id);
}

function remove_permission_from_role($permission_id, $role_id)
{
    return auth_instance()->remove_permission_from_role($permission_id, $role_id);
}