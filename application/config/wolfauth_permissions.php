<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// Permissions

/**
 * How to add in new permissions
 *
 * If you're familiar with how the Wordpress roles and permissions system works,
 * this how the WolfAuth permissions system works.
 *
 * The structure of permissions follows the below simple structure:
 * $permission['permission_name'] = array("roles.groupname"); 
 *
 * The value in the brackets and single quotes 'permission_name' is the value you will
 * use when checking if a user has permission based on the role group that they belong
 * to using the function: $this->auth->has_permission('permission_name');
 *
 * See below for an example permission for allowing a user to upload an image
 * $permission['upload_image'] = array("roles.user");
 * 
 * Then you would check if a user has permission using the following example
 * $this->auth->has_permission('upload_image');
 *
 * The above function will return TRUE if a user has permission or FALSE if they
 * don't have that particular permission.
 *
 * Assign multiple role groups to a permission
 * A permission something generic say for example of being able to reply to a comment
 * on a blog post would not only just be for users, but also editors and administrators
 * as well.
 *
 * To assign multiple roles to a permission, it's simple. See the below example:
 * $permission['reply_to_single_post_comment'] = array('roles.user', 'roles.admin', 'roles.editor'); 
 *
 * The above example is saying that all members of the user role group, admin role group and
 * editor role group have permission to reply to post a comment on a single blog post.
 *
 *
 */

$permission['access_admin'] = array("roles.admin");
$permission['add_user']     = array("roles.admin");
$permission['edit_user']    = array("roles.admin");
$permission['delete_user']  = array("roles.admin");