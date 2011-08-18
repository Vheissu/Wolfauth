<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 * 
 * This driver is a simple login/logout and create user driver that uses
 * Codeigniter sessions. It is very basic and needs more methods for it
 * to be an all in one class solution.
 *
 * @package       WolfAuth
 * @subpackage    Simpleauth
 * @author        Dwayne Charrington
 * @copyright     Copyright (c) 2011 Dwayne Charrington.
 * @link          http://ilikekillnerds.com
 * @license       http://www.apache.org/licenses/LICENSE-2.0.html
 */

class Auth_Simpleauth extends CI_Driver {
    
    public $errors = array();
    public $messages = array();
    public $login_destination = '/';

    private $user_id = 0;
    private $ip_address;
    
    protected $config;
    
    private $ci;
    
    //--------------------------------------------------------------------
    
    public function __construct() 
    {
        log_message('debug', 'Auth class initialized.');
            
        $this->ci = get_instance();
        
        $this->ip_address  = $this->ci->input->ip_address();
        
        $this->ci->config->load('auth');
        $this->ci->load->helper('cookie');
        $this->ci->load->helper('email');
        $this->ci->load->helper('string');
        $this->ci->load->library('encrypt');
        $this->ci->load->library('session');
        $this->ci->load->library('datamapper');
        
        $this->config = config_item('simpleauth');
        
        if ($this->user_id != $this->ci->session->userdata('userid') OR $this->ci->session->userdata('userid') !== FALSE)
            $this->user_id = $this->ci->session->userdata('userid');
        
        // Check if we have a remember me cookie
        $this->_check_remember_me();
    }
    
    /**
    * Logged In
    * Check if a user is currently logged in
    * 
    */
    public function logged_in()
    {        
        return $this->ci->session->userdata('logged_in');
    }
    
    /**
    * Get a user by whatever (id, username or email)
    * 
    * @param mixed $identity
    */
    public function get_user_by($identity)
    {
        // Detect what kind of identity we're working with here
        $type = $this->detect_identity($identity);
        
        $u = new User;
        
        if ($type == 'email')
        {
            $u->get_user_by_email($identity);
        }
        elseif (is_int($identity))
        {
           $u->get_user_by_id($identity); 
        }
        else
        {
           $u->get_user_by_username($identity); 
        }
        
        return ( $u->exists() ) ? $this->get_user_info($u->id) : FALSE;
    }
    
    /**
    * Get User Info
    * Will return an array of user info.
    * 
    * @param mixed $user_id
    */
    public function get_user_info($user_id = 0)
    {
        if ($user_id == 0)
            $user_id = $this->user_id;
            
        $u = new User;
        $u->get_by_id($user_id);
        
        foreach ($u->role->get()->all AS $role)
        {
            $user['roles'][$role->id] = $role->name;
        }
        
        foreach ($u->group->get()->all AS $group)
        {
            $user['groups'][$group->id] = $group->name;
        }
        
        $user['user'] = $u->to_array();
        
        // We don't want the user meta ID
        unset($user['user']['umeta_id']);
        
        $user['meta'] = $this->get_user_meta($user_id);
        
        return (count($user) > 0) ? $user : FALSE;    
    }
    
    /**
    * Get User ID From Username
    * Self explanatory ^^
    * 
    * @param mixed $username
    */
    public function get_user_id($username)
    {
        $u = new User;
        $u->get_by_username($username);
        
        return ( $u->exists() ) ? $u->id : FALSE;
    }
    
    /**
    * Get Group ID From Group Name
    *     
    * @param mixed $group_name
    */
    public function get_group_id($group_name)
    {
        $g = new User;
        $g->get_by_name($group_name);
        
        return ( $g->exists() ) ? $g->id : FALSE;
    }
    
    /**
    * Get Role ID From Role Name
    * 
    * @param mixed $role_name
    */
    public function get_role_id($role_name)
    {
        $r = new Role;
        $r->get_by_name($role_name);
        
        return ( $r->exists() ) ? $r->id : FALSE;
    }
    
    /**
    * Get Permission ID
    * Gets a permission ID based on a permissiong string
    * 
    * @param mixed $role_id
    */
    public function get_permission_id($permission = '')
    {
        $p = new Permission;
        
        // If we aren't checking a specific permission, auto check for the win!
        if ($permission == '')
            $permission = $this->ci->uri->rsegment(1) . "/" . $this->ci->uri->rsegment(2);
        
        $p->get_by_permission($permission);
        
        return ( $p->exists() ) ? $p->id : FALSE;
    }
    
    /**
    * Role ID Exists
    * Check if a role ID exists
    * 
    * @param mixed $role_id
    */
    public function role_id_exists($role_id)
    {
        $r = new Role;
        $r->get_by_id($role_id);
        
        return ( $r->exists() ) ? TRUE : FALSE;
    }
    
    /**
    * Group ID Exists
    * Check if a group ID exists
    * 
    * @param mixed $group_id
    */
    public function group_id_exists($group_id)
    {
        $g = new Group;
        $g->get_by_id($group_id);
        
        return ( $g->exists() ) ? TRUE : FALSE;
    }
    
    /**
    * User ID Exists
    * Check if a user ID exists
    * 
    * @param mixed $user_id
    */
    public function user_id_exists($user_id)
    {
        $u = new User;
        $u->get_by_id($user_id);
        
        return ( $u->exists() ) ? TRUE : FALSE;
    }
    
    /**
    * Username Exists
    * Check if a username exists
    * 
    * @param mixed $username
    */
    public function username_exists($username)
    {
        $u = new User;
        $u->get_by_username($username);
        
        return ( $u->exists() ) ? TRUE : FALSE;
    }
    
    /**
    * Email Exists
    * Check if an email address exists
    * 
    * @param mixed $email
    */
    public function email_exists($email)
    {
        $u = new User;
        $u->get_by_email($email);
        
        return ( $u->exists() ) ? TRUE : FALSE;
    }
    
    /**
    * Get User Groups
    * Get all groups a user belongs too
    * 
    * @param mixed $user_id
    */
    public function get_user_groups($user_id = 0)
    {
        if ($user_id == 0)
            $user_id = $this->user_id;
        
        $u = new User();
        $u->get_by_id($user_id);
        
        $groups = $u->group->get()->all;
        
        $assigned_groups = array();
        
        foreach ($groups AS $group)
        {
            $assigned_groups[$group->id] = $group->name;
        }
        
        return $assigned_groups;  
    }
    
    /**
    * Get User Roles
    * Get all roles assigned to a user
    * 
    * @param mixed $user_id
    */
    public function get_user_roles($user_id = 0)
    {
        if ($user_id == 0)
        {
            $user_id = $this->user_id;
        }
        $u = new User;
        $u->get_by_id($user_id);
        
        if ( $u->exists() )
        {        
            $assigned_roles = array();
            
            $roles = $u->role->get()->all;
            
            foreach ($roles AS $r)
            {
                $assigned_roles[$r->id] = $r->name;
            }
            
            return $assigned_roles;
        }
        else
        {
            return FALSE;
        }  
    }
    
    /**
    * Get User Permissions
    * Get all user permissions assigned to a user
    * 
    * @param mixed $user_id
    */
    public function get_user_permissions($user_id = 0)
    {
        if ($user_id == 0)
            $user_id = $this->user_id;
            
        $u = new User;
        $u->get_by_id($user_id);
        
        $assigned_permissions = array();
        
        if ( $u->exists() )
        {                    
            $permissions = $u->permission->get()->all;
            
            foreach ($permissions AS $p)
            {
                $assigned_permissions[$p->id] = $p->name;
            }
        }
        
        return $assigned_permissions;
    }
    
    /**
    * Get Group Users
    * Will list all users that belong to a particular group
    * 
    * @param mixed $group_id
    */
    public function get_group_users($group_id)
    {        
        $g = new Group;
        $g->get_by_id($group_id);
        
        return ( $g->exists() ) ? $g->user->get()->all : FALSE; 
    }
    
    /**
    * Get Group Roles
    * Will list all roles assigned to a particular group
    * 
    * @param mixed $group_id
    */
    public function get_group_roles($group_id)
    {
        $g = new Group;
        $g->get_by_id($group_id);
        
        return ( $g->exists() ) ? $g->role->get()->all : FALSE;
    }
    
    /**
    * Get Group Permissions
    * Get all permissions assigned to a group
    * 
    * @param mixed $group_id
    */
    public function get_group_permissions($group_id)
    {
        $g = new Group;
        $g->get_by_id($group_id);
        
        return ( $g->exists() ) ? $g->permission->get()->all : FALSE;
    }
    
    /**
    * Add Permission
    * Adds a new permission to the database
    * 
    * @param mixed $permission
    */
    public function add_permission($permission)
    {
        $p  = new Permission;
        $p->get_by_permission($permission);
        
        if ( !$p->exists() )
        {
            $pp = $p->get_copy();
            $pp->permission = $permission;
            $pp->save();
        }  
        else
        {
            return FALSE;
        }
    }
    
    /**
    * Remove Permission
    * Remove a permission from the database
    * 
    * @param mixed $permission
    */
    public function remove_permission($permission)
    {
        $p  = new Permission;
        $p->get_by_permission($permission);
        
        if ( !$p->exists() )
        {
            $p->delete();
        }  
    }

    /**
    * Add Permission To User
    * Adds a permission to a user
    * 
    * @param mixed $permission_id
    * @param mixed $user_id
    */
    public function add_permission_to_user($permission_id, $user_id)
    {
        $p = new Permission;
        $p->get_by_id($permission_id);
        
        $r = new User;
        $r->get_by_id($user_id);
        
        if ( $p->exists() AND $u->exists() )
        {
            return $u->save($p);
        }
    }
    
    /**
    * Remove Permission From User
    * Removes a permission from a user
    * 
    * @param mixed $permission_id
    * @param mixed $user_id
    */
    public function remove_permission_from_user($permission_id, $user_id)
    {
        $p = new Permission;
        $p->get_by_id($permission_id);
        
        $u = new User;
        $u->get_by_id($user_id);
        
        if ( $p->exists() AND $u->exists() )
        {
            return $u->delete($p);
        }
    }
    
    /**
    * Add Permission To Role
    * Adds a permission to a role
    * 
    * @param mixed $permission_id
    * @param mixed $role_id
    */
    public function add_permission_to_role($permission_id, $role_id)
    {
        $p = new Permission;
        $p->get_by_id($permission_id);
        
        $r = new Role;
        $r->get_by_id($role_id);
        
        if ( $p->exists() AND $r->exists() )
        {
            return $r->save($p);
        }
    }
    
    /**
    * Remove Permission From Role
    * Removes a permission from a role
    * 
    * @param mixed $permission_id
    * @param mixed $role_id
    */
    public function remove_permission_from_role($permission_id, $role_id)
    {
        $p = new Permission;
        $p->get_by_id($permission_id);
        
        $r = new Role;
        $r->get_by_id($role_id);
        
        if ( $p->exists() AND $r->exists() )
        {
            return $r->delete($p);
        }
    }    
    
    /**
    * Add Permission To Group
    * Adds a permission to a group
    * 
    * @param mixed $permission_id
    * @param mixed $group_id
    */
    public function add_permission_to_group($permission_id, $group_id)
    {
        $p = new Permission;
        $p->get_by_id($permission_id);
        
        $g = new Group;
        $g->get_by_id($group_id);
        
        if ( $p->exists() AND $g->exists() )
        {
            return $g->save($p);
        }
    }
    
    /**
    * Remove Permission From Group
    * Removes a permission assigned to a group
    * 
    * @param mixed $permission_id
    * @param mixed $group_id
    * @return bool
    */
    public function remove_permission_from_group($permission_id, $group_id)
    {
        $p = new Permission;
        $p->get_by_id($permission_id);
        
        $g = new Group;
        $g->get_by_id($group_id);
        
        if ( $p->exists() AND $g->exists() )
        {
            return $g->delete($p);
        }
    }
    
    /**
    * User Has Permission
    * Checks if the currently logged in or out user has permission
    * 
    * @param mixed $permission
    */
    public function user_has_permission($user_id = 0, $permission = '')
    {
        if ($user_id == 0 OR $user_id == '')
           $user_id = $this->user_id;   
            
        // If we aren't checking a specific permission, auto check for the win!
        if ($permission == '')
            $permission = $this->ci->uri->rsegment(1) . "/" . $this->ci->uri->rsegment(2);   
            
        $u = new User;
        $u->get_by_id($user_id);
        
        if ( $u->exists() )
        {
            $found = false;
            
            $permissions = $u->permission->get()->all;
            
            foreach ($permissions AS $p)
            {
                if ( is_int($permission) )
                {
                    if ($p->id == $permission)
                    {
                        $found = true;
                    }
                }
                else
                {
                    if ($p->permission == $permission)
                    {
                        $found = true;
                    }
                }
            }
            
            return $found;
        }
        else
        {
            return FALSE;
        }
    }
    
    /**
    * Group Has Permission
    * Check if a group has a particular permission
    * 
    * @param mixed $group_id
    * @param mixed $permission (can be ID or permission string)
    */
    public function group_has_permission($group_id, $permission = '')
    {
        $g = new Group;
        $g->get_by_id($group_id);
        
        // If we aren't checking a specific permission, auto check for the win!
        if ($permission == '')
            $permission = $this->ci->uri->rsegment(1) . "/" . $this->ci->uri->rsegment(2);
        
        if ( $g->exists() )
        {
            $found = false;
            
            $permissions = $g->permission->get()->all;
            
            foreach($permissions AS $p)
            {
                if ( is_int($permission) )
                {
                    if ($p->id == $permission)
                    {
                        $found = true;
                    }
                }
                else
                {
                    if ($p->permission == $permission)
                    {
                        $found = true;
                    }
                }
            }
            
            return $found;   
        }
        else
        {
            return false;
        }
    }
    
    /**
    * Role Has Permission
    * Check if a role has a particular permission
    * 
    * @param mixed $role_id
    * @param mixed $permission
    */
    public function role_has_permission($role_id, $permission = '')
    {
        $r = new Role;
        $r->get_by_id($$role_id);
        
        // If we aren't checking a specific permission, auto check for the win!
        if ($permission == '')
            $permission = $this->ci->uri->rsegment(1) . "/" . $this->ci->uri->rsegment(2);
        
        if ( $r->exists() )
        {
            $found = false;
            
            $permissions = $r->permission->get()->all;
            foreach($permissions AS $p)
            {
                if ( is_int($permission) )
                {
                    if ($p->id == $permission)
                    {
                        $found = true;
                    }
                }
                else
                {
                    if ($p->permission == $permission)
                    {
                        $found = true;
                    }
                }
            }
            
            return $found;   
        }
        else
        {
            return false;
        }
    }
    
    /**
    * List Users
    * Will get all users from the database
    * 
    * @param mixed $count
    * @param mixed $offset
    * 
    */
    public function list_users($count = 10000, $offset = 0)
    {
        $u = new User;
        return $u->get($count, $offset);
    }
    
    /**
    * List Groups
    * Will get all groups from the database
    * 
    * @param mixed $count
    * @param mixed $offset
    * 
    */
    public function list_groups($count = 10000, $offset = 0)
    {
        $g = new Group;
        return $g->get($count, $offset);
    }
    
    /**
    * List Roles
    * Will get all roles from the database
    * 
    * @param mixed $count
    * @param mixed $offset
    * 
    */
    public function list_roles($count = 10000, $offset = 0)
    {
        $r = new Role;
        return $r->get($count, $offset);
    }
    
    /**
    * List Permissions
    * Will get all permissions from the database
    * 
    * @param mixed $count
    * @param mixed $offset
    * 
    */
    public function list_permissions($count = 10000, $offset = 0)
    {
        $p = new Permission;
        return $p->get($count, $offset);
    }
    
    /**
    * User has group
    * Check if a user belongs to a particular group
    * 
    * @param mixed $user_id
    * @param mixed $group_id
    */
    public function user_has_group($user_id = 0, $group_id)
    {
        if ($user_id == 0)
            $user_id = $this->user_id;
        
        $u = new User;
        $u->get_by_id($user_id);
        
        if ( $u->exists() )
        {
            $groups = $u->group->get()->all_to_array();
        } 
    }
    
    /**
    * User has role
    * Check if a user has a particular role
    * 
    * @param mixed $user_id
    * @param mixed $role_id
    */
    public function user_has_role($user_id = 0, $role_id)
    {
        if ($user_id == 0)
            $user_id = $this->user_id;
        
        $u = new User;
        $u->get_by_id($user_id);
        
        if ( $u->exists() )
        {
            $roles = $u->role->get()->all_to_array();
        } 
    }
    
    /**
    * Group Has Role
    * Check if a group has a particular role
    * 
    * @param mixed $group_id
    * @param mixed $role_id
    */
    public function group_has_role($group_id, $role_id)
    {
        $g = new Group;
        $g->get_by_id($group_id);
        
        if ( $g->exists() )
        {
            $roles = $g->role->get()->all;
            $found = false;
            
            foreach ($roles AS $role)
            {
                if ($role->id == $role_id)
                {
                    $found = true;
                }
            }
            return $found;
        }
    }
    
    
    
    /**
    * Add Group
    * Add a new group to the database
    * 
    * @param mixed $name
    * @param mixed $description
    * @return bool
    */
    public function add_group($name, $description = '')
    {
        $g  = new Group;
        
        $gg = new Group;
        $gg->get_by_name($name);
        
        if ( !$gg->exists() )
        {        
            $g->name        = $name;
            $g->description = $description;
        
            return $g->save();
        }
        else
        {
            return FALSE;
        }
    }
    
    /**
    * Update Group
    * Update a group name or description
    * 
    * @param mixed $group_id
    * @param mixed $name
    * @param mixed $description
    */
    public function update_group($group_id, $name = '', $description = '')
    {
        $g = new Group;
        $g->get_by_id($group_id);
        
        if ( $g->exists() )
        {
            if ($name != '')
            {
                $g->name = $name;
            }
            
            if ($description !== '')
            {
                $g->description = $description;
            }
            
            if ($name != '' OR $description !== '')
            {
                $g->save();   
            }
        }
    }
    
    /**
    * Delete Group
    * Deletes a group
    * 
    * @param mixed $group_id
    * @return bool
    */
    public function delete_group($group_id)
    {
        $g = new Group;
        $g->get_by_id($group_id);
        
        return $g->delete();
    }
    
    /**
    * Add User To Group
    * Adds a new user to a group
    * 
    * @param mixed $user_id
    * @param mixed $group
    */
    public function add_user_to_group($user_id, $group_id)
    {
        $u = new User;
        $u->get_by_id($user_id);
        
        $g = new Group;
        $g->get_by_id($group_id);
        
        return $u->save($g);
    }
    
    /**
    * Remove User From Group
    * Removes a user from a group
    * 
    * @param mixed $user_id
    * @param mixed $group_id
    * @return bool
    */
    public function remove_user_from_group($user_id, $group_id)
    {
        $u = new User;
        $u->get_by_id($user_id);
        
        $g = new Group;
        $g->get_by_id($group_id);
        
        return $u->delete($g);
    }
    
    /**
    * Add Role To Group
    * Adds a role to a group
    * 
    * @param mixed $group_id
    * @param mixed $role_id
    */
    public function add_role_to_group($group_id, $role_id)
    {
        $g = new Group;
        $g->get_by_id($group_id);
        
        $r = new Role;
        $r->get_by_id($role_id);
        
        if ( $g->exists() AND $r->exists() )
        {
            $g->save($r);
        }
    }
    
    /**
    * Remove Role From Group
    * Removes a role from a group
    * 
    * @param mixed $group_id
    * @param mixed $role_id
    */
    public function remove_role_from_group($group_id, $role_id)
    {
        $g = new Group;
        $g->get_by_id($group_id);
        
        $r = new Role;
        $r->get_by_id($role_id);
        
        if ( $g->exists() AND $r->exists() )
        {
            $g->delete($r);
        }
    }
    
    
    
    /**
    * Login
    * Logs a user in
    * 
    * @param mixed $login
    * @param mixed $password
    * @param mixed $remember
    */
    public function login($login = '', $password = '', $remember = FALSE)
    {    
        if ( empty($login) OR empty($password) )
        {
            return FALSE;
        }
        
        $login_type = $this->detect_identity($login);

        $u = new User();
        
        $a = new Attempt();
        $a->where('ip_address', $this->ip_address)->get();
        
        $u->where($login_type, $login);
        $user = $u->get();
        
        if ( $user->exists() AND $a->attempts < $this->config['auth.max_login_attempts'] )
        {                     
            $salted_password = $this->create_password($password, $user->salt);
                        
            if ( $salted_password == $user->password )
            {
                $user_id = $user->id;                
                $this->force_login($user_id, $remember);
                
                return TRUE;                
            }
            else
            {
                $this->update_login_attempts();
                return FALSE;
            }
        }
        else
        {
            if ($a->attempts >= $this->config['auth.max_login_attempts'])
            {
                $this->set_error("You have reached the maximum allowed login attempts, please try again later.");
            }
            else
            {
                $this->set_error("Could not log user in, incorrect details were supplied.");   
            }
            return FALSE;
        }
    }
    
    /**
    * Force Login
    * Will populate all session variables and force a 
    * user to be logged in.
    * 
    * @param mixed $user_id
    * @param mixed $remember
    */
    public function force_login($user_id, $remember = FALSE)
    {
        $this->ci->session->set_userdata(array('userid' => $user_id, 'logged_in' => 'true'));
        $this->update_user($user_id, array('last_login' => time(), 'last_ip' => $this->ip_address));
        $this->reset_login_attempts();
        
        if ($remember === TRUE)
        {
            $this->_set_remember_me($user_id);
        }
    }
    
    /**
    * Activate
    * Activates a user
    * 
    * @param mixed $user_id
    * @param mixed $code
    */
    public function activate($user_id = 0, $code = '')
    {
        if ($user_id == 0)
        {
            $user_id = $this->user_id;
        }
        
        $u = new User;
        $u->get_by_id($user_id);
        
        if ( $u->exists() )
        {
            // If codes match
            if ($u->activation_code == $code)
            {
                $u->activation_code = '';
                $u->status = 'active';
                return $u->save();
            }
            else
            {
                $this->set_error("The activation code supplied is not correct.");
                return false;
            }
        }
        else
        {
            $this->set_error("Cannot activate a user that does not exist.");
            return false;
        }
    }
    
    /**
    * Forgotten Password
    * Helps a user reset their password if they forgot it
    * 
    * @param mixed $identity
    */
    public function forgotten_password($identity)
    {
        $type = $this->detect_identity($identity);
        
        $u = new User;
        $u->get_by_{$type}($identity);
        
        if ( $u->exists() )
        {
            // Only active users can reset passwords
            if ( $u->status == 'active' )
            {
                // Create an activation code
                $code = $this->generate_random() .$identity;
                $u->activation_code = $code;
                $u->save();
                
                // Send user a forgotten password email
                $this->email_forgot_password($u->email, $code);   
            }
            else
            {
                $this->set_error('Only active users can reset their passwords.');
            }
        } 
    }
    
    /**
    * Complete Forgotten Password
    * 
    * @param mixed $code
    */
    public function complete_forgotten_password($code)
    {
        $u = new User;
        $u->get_by_activation_code($code);
        
        if ( $u->exists() )
        {
            $salt     = $this->generate_random();
            $new_pass = $this->generate_random();
            
            $password = $this->create_password($new_pass, $salt);
            
            $this->email_new_password($u->email, $new_pass);
            
            return $password;
        }
    }
    
    /**
    * Email Forgot Password
    * If a user forgets their password, they can send themselves
    * an email to reset their password.
    * 
    * @param mixed $email
    */
    public function email_forgot_password($email, $code)
    {
        $data['email']       = $email;
        $data['forgot_code'] = $code;
        $data['reset_link']  = config_item('wolfauth.reset_password_link');
        
        $message = $this->ci->load->view('emails/reset_password', $data, true);
        
        $this->ci->email->clear();
        $this->ci->email->set_newline("\r\n");
        $this->ci->email->from(config_item('wolfauth.admin_email'), config_item('wolfauth.site_name'));
        $this->ci->email->to($email);
        $this->ci->email->subject(config_item('wolfauth.site_name') . ' - Forgotten Password Verification');
        $this->ci->email->message($message);
        
        if ($this->ci->email->send())
        {
            $this->set_message('Successfully sent forgot password email');
            return TRUE;
        }
        else
        {
            $this->set_error('There was a problem whilst trying to the forgot password email');
            return FALSE;
        }  
    }
    
    /**
    * Email New Password
    * Send a newly generated password to the user
    * 
    * @param mixed $email
    */
    public function email_new_password($email, $password)
    {
        $data['email']    = $email;
        $data['password'] = $password;
        
        $message = $this->ci->load->view('emails/new_password', $data, true);
        
        $this->ci->email->clear();
        $this->ci->email->set_newline("\r\n");
        $this->ci->email->from(config_item('wolfauth.admin_email'), config_item('wolfauth.site_name'));
        $this->ci->email->to($email);
        $this->ci->email->subject(config_item('wolfauth.site_name') . ' - Forgotten Password Request');
        $this->ci->email->message($message);
        
        if ($this->ci->email->send())
        {
            $this->set_message('Successfully sent forgot password email');
            return TRUE;
        }
        else
        {
            $this->set_error('There was a problem whilst trying to the forgot password email');
            return FALSE;
        }  
    }
    
    /**
    * Create User
    * Creates a new user account and allows for multiple roles
    * 
    * @param mixed $login
    * @param mixed $password
    * @param mixed $fields
    * @param mixed $roles
    * @param mixed $groups
    */
    public function create_user($login, $password, $fields = array(), $roles = array(), $groups = array())
    {    
        if (empty($login) OR empty($password))
        {
            $this->set_error("You must supply a username and or password.");
            return FALSE;
        }
                
        $login_type = $this->config['auth.login_type'];
        
        if ($login_type == 'auto')
        {
            if ( !valid_email($this->config['auth.login_type']) )
            {
                $login_type = 'username';
            }
            else
            {
                $login_type = 'email';
            }   
        }
        
        $u = new User();
        $u->where($login_type, $login);
        $u->get();
        
        // Make sure user doesn't exist first
        if ( !$u->exists() )
        {
            $u = $u->get_copy();
            
            $salt     = $this->generate_random();
            $password = $this->create_password($this->generate_random(), $salt);
            
            $u->{$login_type} = $login;
            $u->salt          = $salt;
            $u->password      = $password;
            
            if ( !empty($fields) )
            {
                $um = new Umeta();
                foreach ($fields AS $field => $value)
                {
                    $um->{$field} = $value;
                }
                $u->save($um);
            }
            
            if (!empty($roles))
            {
                $r = new Role();
                $r->where_in('id', $roles)->get();
                
                return $u->save($r);
            }
            elseif (!empty($groups))
            {
                $g = new Group;
                $g->where_in('id', $groups)->get();
                return $u->save($g);
            }
            else
            {
                return $u->save();
            }            
        }
        else
        {
            $this->set_error("That user already exists.");
            return FALSE;
        }
    }
    
    /**
    * Update User
    * Updates a user in the database
    * 
    * @param mixed $user_id
    * @param mixed $fields
    */
    public function update_user($user_id, $fields = array())
    {
        $u = new User;
        $u->get_by_id($user_id);
        
        if ( isset($fields['password']) )
        {
            $fields['password'] = $this->create_password($fields['password'], $u->salt);
            
            if ( isset($fields['password2']) )            
                unset($fields['password2']);
        }
        
        return ( $u->exists() ) ? $u->update($fields) : FALSE;                 
    }
    
    /**
    * Delete User
    * Deletes a user from the database
    * 
    * @param mixed $user_id
    */
    public function delete_user($user_id)
    {
        $u = new User();
        $u->get_by_id($user_id);
        
        return $u->delete();
    }
    
    /**
    * Get User Meta
    * Gets user meta fields and info
    * 
    * @param mixed $user_id
    */
    public function get_user_meta($user_id = 0)
    {
        if ($user_id == 0)
            $user_id = $this->user_id;
        
        $u = new User;
        $u->get_by_id($user_id);
        
        if ( $u->exists() )
        {
            $umeta = $u->umeta->get();
            $fields = $u->umeta->fields;
            
            $metas = array();
            
            foreach ($umeta->all AS $meta)
            {
                foreach ($fields AS $k => $field)
                {
                    if ($field != 'id')
                    {
                        $metas[$field] = $meta->{$field};
                    }
                }
            }
            
            return $metas;
        }
        else
        {
            return array();
        }
    }
    
    /**
    * Update User Meta
    * Updates meta values of a user
    * 
    * @param mixed $user_id
    * @param mixed $fields
    */
    public function update_user_meta($user_id, $fields = array())
    {
        $u = new User;
        $u->get_by_id($user_id);
                 
        $um = new Umeta;
        foreach ($fields AS $field => $value)
        {
            $um->{$field} = $value;
        }

        
        return ( $u->exists() ) ? $u->save($um) : FALSE;
    }
    
    /**
    * Logout
    * Logs a user out and destroys session
    * 
    */
    public function logout($redirect = '')
    {
        $user_id = $this->ci->session->userdata('userid');

        $this->ci->session->sess_destroy();

        delete_cookie('rememberme');
        
        $u = new User;
        $u->where('id', $user_id);
        
        if ( $redirect != '' )
            redirect($login);
        else
            return $this->update_user($user_id, array('remember_me' => ''));
    }
    
    
    
    /**
    * Get Roles
    * Will return an array of roles for
    * the particular user ID supplied
    * 
    * @param mixed $user_id
    */
    public function get_roles($user_id = null)
    {
        if (!is_null($user_id))
            $user_id = $this->user_id;
        
        $u = new User();
        $u->get_by_id($user_id);
        
        $roles = $u->role->get()->all;
        
        $assigned_roles = array();
        
        foreach ($roles AS $role)
        {
            $assigned_roles[$role->id] = $role->name;
        }
        
        return $assigned_roles;   
    }
    
    /**
    * Get Role Permissions
    * Gets all permissions assigned to a role
    * 
    * @param mixed $role_id
    */
    public function get_role_permissions($role_id)
    {
        $r = new Role;
        $r->get_by_id($role_id);
        
        return ( $r->exists() ) ? $r->permission->get()->all : FALSE;
    }
    
    /**
    * Add Role
    * Adds a new role to the database
    * 
    * @param mixed $name
    * @param mixed $description
    * @return bool
    */
    public function add_role($name, $description = '')
    {
        $r = new Role;
        $r->get_by_name($name);
        
        if ( !$r->exists() )
        {
            $r->name = $name;
            $r->description = $description;
        
            return $r->save();   
        }
        else
        {
            $this->set_message('That role already exists');
            return FALSE;
        }
    }
    
    /**
    * Update Role
    * Updates a role name and or description
    * 
    * @param mixed $role_id
    * @param mixed $new_name
    * @param mixed $description
    */
    public function update_role($role_id, $name = '', $description = '')
    {
        $r = new Role;
        $r->get_by_id($role_id);
        
        if ( $r->exists() )
        {
            if ($name != '')
            {
                $r->name = $name;
            }
            
            if ($description !== '')
            {
                $r->description = $description;
            }
            
            if ($name !== '' OR $description !== '')
            {
                $r->save();   
            }
        }
    }
    
    /**
    * Delete Role
    * Deletes a role from the database
    * 
    * @param mixed $name
    * @return bool
    */
    public function delete_role($name)
    {
        $r = new Role;
        $r->get_by_name($name);
        
        if ( $r->exists() )
        {
            return $r->delete();
        }
    }
    
    /**
    * Add Role To User
    * Adds a role to a user
    * 
    * @param mixed $user_id
    * @param mixed $role_id
    */
    public function add_role_to_user($user_id, $role_id)
    {
        $u = new User;
        $u->get_by_id($user_id);
        
        $r = new Role;
        $r->get_by_id($role_id);
        
        if ( $u->exists() AND $r->exists() )
        {
            $u->save($r);
        }
    }
    
    /**
    * Remove Role From User
    * Removes a role from a user
    * 
    * @param mixed $user_id
    * @param mixed $role_id
    */
    public function remove_role_from_user($user_id, $role_id)
    {
        $u = new User;
        $u->get_by_id($user_id);
        
        $r = new Role;
        $r->get_by_id($role_id);
        
        if ( $u->exists() AND $r->exists() )
        {
            $u->delete($r);
        } 
    }  
    
    /**
    * Update Login Attempts
    * Used by the login function when a user attempts to login
    * unsuccessfully.
    * 
    * @param mixed $ip_address
    */
    public function update_login_attempts($ip_address = NULL)
    {
        if (is_null($ip_address))
            $ip_address = $this->ip_address;
            
        $a = new Attempt();
        $a->where('ip_address', $ip_address)->get();
        
        if ( $a->exists() )
        {
            $current_time = time();
            $attempts     = $a->attempts;
            $created      = strtotime($a->created);
            
            // Minutes comparison
            $minutes      = floor($current_time - $created / 60);
            
            // Increment new attempts
            $new_attempts = $attempts + 1;
            
            // If current time elapsed between creation is greater than the attempts, reset
            if (($current_time - $created) > $this->config['auth.login_attempts_expiry'])
            {
                $this->reset_login_attempts($ip_address);
            }   
        }
        else
        {
            $a = $a->get_copy();
            $a->ip_address = $ip_address;
            $a->attempts   = 1;
            $a->save();
        }
    }
    
    /**
    * Reset Login Attempts
    * Resets login attempts increment value
    * in the database for a particular IP address.
    * 
    * @param mixed $ip_address
    */
    public function reset_login_attempts($ip_address = NULL)
    {
        if (is_null($ip_address))
            $ip_address = $this->ip_address;
            
        $a = new Attempt();
        $a->where('ip_address', $ip_address)->get();
        
        if ( $a->exists() )
        {
            $a->delete();
        }
    }
    
    /**
    * Set Remember Me
    * Sets a remember me cookie
    * 
    * @param mixed $user_id
    */
    private function _set_remember_me($user_id)
    {
        $token = md5(uniqid(rand(), TRUE));
        $timeout = 60 * 60 * 24 * 7; // One week

        $remember_me = $this->ci->encrypt->encode($user_id.':'.$token.':'.(time() + $timeout));

        $cookie = array(
            'name'   => 'rememberme',
            'value'  => $remember_me,
            'expire' => $timeout
        );

        set_cookie($cookie);
        
        $u = new User();
        $u->where('id', $user_id);
        
        return $this->update_user($user_id, array('remember_me' => $remember_me));      
    }
    
    /**
    * Check Remember Me
    * Checks if a user is logged in
    * 
    */
    private function _check_remember_me()
    {
        if($cookie_data = get_cookie('rememberme'))
        {
            $user_id = '';
            $token = '';
            $timeout = '';

            $cookie_data = $this->ci->encrypt->decode($cookie_data);
            
            if (strpos($cookie_data, ':') !== FALSE)
            {
                $cookie_data = explode(':', $cookie_data);
                
                if (count($cookie_data) == 3)
                {
                    list($user_id, $token, $timeout) = $cookie_data;
                }
            }

            // Expired cookie?
            if ( (int) $timeout < time() )
            {
                return FALSE;
            }
            
            $u = new User();
            $u->get_by_id($user_id);

            if ( $u->exists() )
            {
                $this->ci->session->set_userdata('userid', $user_id);

                $this->_set_remember_me($user_id);

                return TRUE;
            }

            delete_cookie('rememberme');
        }

        return FALSE;
    }
    
    /**
    * Detect Identity Type
    * Determines whethere or not a user is logging
    * in via email or username.
    * 
    * @param mixed $login
    */
    private function detect_identity($identity)
    {
        $login_type = $this->config['auth.login_type'];
        
        if ($login_type == 'auto')
        {
            if ( !valid_email($identity) )
            {
                $login_type = 'username';
            }
            else
            {
                $login_type = 'email';
            }   
        }
        
        return $login_type;
    }
    
    /**
    * Set Error
    * Sets an error message
    * 
    * @param mixed $error
    */
    public function set_error($error)
    {
        $this->errors[] = $error;
        
        return $error;
    }
    
    /**
    * Set Message
    * Sets a message
    * 
    * @param mixed $message
    */
    public function set_message($message)
    {
        $this->messages[] = $message;

        return $message;
    }
    
    /**
    * Create Password
    * Creates a password provided it's given a salt
    * 
    * @param mixed $password
    * @param mixed $salt
    */
    public function create_password($password, $salt)
    {
        return sha1( $salt . $password );
    }
    
    /**
    * Generate Random
    * Generates a random string
    * 
    */
    public function generate_random()
    {
        return random_string('alpha', 8);
    }
    
    /**
    * Auth Errors
    * Show any error messages relating to the auth class
    * 
    */
    public function auth_errors()
    {
        return (!empty($this->errors)) ? $this->errors : FALSE;
    }
    
    public function decorate() {}


}
