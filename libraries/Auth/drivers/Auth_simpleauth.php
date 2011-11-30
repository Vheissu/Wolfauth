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
        $this->ci->load->library('email');
        
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
        
        $u->where($type,$identity)->get(); 
        
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
            
        $u = new User($user_id);
        
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
        $g = new Group;
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
        // TODO:: we should get the first tow segment only?
        if ($permission == '')
            $permission = trim($this->ci->uri->uri_string(), '/');
        
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
        $r = new Role($role_id);
       
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
        $g = new Group($group_id);
        
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
        $u = new User($user_id);
        
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
        
        $u = new User($user_id);
        
        $assigned_groups = array();
        
        if ( $u->exists() )
        {        
            
            $groups = $u->group->get()->all;
            
            foreach ($groups AS $group)
	        {
            	$assigned_groups[$group->id] = $group->name;
        	}
        }
       
        return $assigned_groups;
        
        /* TODO: why not using the same group function syntax?
         * return ( $u->exists() ) ? $u->group->get()->all : FALSE;
         */
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
        
        $u = new User($user_id);
        
        $assigned_roles = array();
        
        if ( $u->exists() )
        {      
            $roles = $u->role->get()->all;
            
            foreach ($roles AS $r)
            {
                $assigned_roles[$r->id] = $r->name;
            }
        }
        
        return $assigned_roles;      
        
        /* TODO: why not using the same group function syntax?
         * return ( $u->exists() ) ? $u->role->get()->all : FALSE;
         */
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
            
        $u = new User($user_id);
        
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
        
        /* TODO: why not using the same group function syntax?
         * return ( $u->exists() ) ? $u->permission->get()->all : FALSE;
         */
    }
    
    /**
    * Get Group Users
    * Will list all users that belong to a particular group
    * 
    * @param mixed $group_id
    */
    public function get_group_users($group_id)
    {        
        $g = new Group($group_id);
        
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
        $g = new Group($group_id);
        
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
        $g = new Group($group_id);
        
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
        	/* TODO:: why copying the old one?
        	 * can we make it like this?
        	 * $p->permission = $permission;
        	 * $p->save();
        	 */
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
        $p = new Permission($permission_id);
        $r = new User($user_id);
        
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
        $p = new Permission($permission_id);
        $u = new User($user_id);
        
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
        $p = new Permission($permission_id);
        $r = new Role($role_id);
        
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
        $p = new Permission($permission_id);
        $r = new Role($role_id);
        
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
        $p = new Permission($permission_id);
        $g = new Group($group_id);
        
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
        $p = new Permission($permission_id);
        $g = new Group($group_id);
        
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
    	
        if(empty($user_id))
           $user_id = $this->user_id;   
            
        // If we aren't checking a specific permission, auto check for the win!
        if ($permission == '')
        	// TODO:: we should get the first tow segment only?
            $permission = trim($this->ci->uri->uri_string(), '/');  
            
        $u = new User($user_id);
        
        if ( $u->exists() )
        {
        	$by_type = is_int($permission)? 'id':'permission';
        	$u->permission->where($by_type,$permission)->get();
           	if($u->permission->exists())
           	{
           		return TRUE;
           	}
           	else{
           		return FALSE;
           	}
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
        $g = new Group($group_id);
        
        // If we aren't checking a specific permission, auto check for the win!
        if ($permission == '')
            $permission = trim($this->ci->uri->uri_string(), '/');
        
        if ( $g->exists() )
        {
        	$by_type = is_int($permission)? 'id':'permission';
        	$g->permission->where($by_type,$permission)->get();
           	if($g->permission->exists())
           	{
           		return TRUE;
           	}
           	else{
           		return FALSE;
           	}   
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
        $r = new Role($role_id);
        
        // If we aren't checking a specific permission, auto check for the win!
        if ($permission == '')
            $permission = trim($this->ci->uri->uri_string(), '/');
        
    if ( $r->exists() )
        {
        	$by_type = is_int($permission)? 'id':'permission';
        	$r->permission->where($by_type,$permission)->get();
           	if($r->permission->exists())
           	{
           		return TRUE;
           	}
           	else{
           		return FALSE;
           	}   
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
        
        $u = new User($user_id);
        
        if ( $u->exists() )
        {
            $u->group->get();
            return $u->group->exists();
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
        
        $u = new User($user_id);
        
        if ( $u->exists() )
        {
            $u->role->get();
            return $u->role->exists();
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
        $g = new Group($group_id);
        
        if ( $g->exists() )
        {
            $g->role->get();
            return $g->role->exists();
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
        $g->get_by_name($name);
        
        if ( !$g->exists() )
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
        $g = new Group($group_id);
        
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
        $g = new Group($group_id);
        
        if( $g->exists() )
        {
        	return $g->delete();
        }
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
        $u = new User($user_id);
        $g = new Group($group_id);
        
        if( $u->exists() AND $g->exists() )
        {
        	return $u->save($g);
        }
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
        $u = new User($user_id);
        $g = new Group($group_id);
        
    	if( $u->exists() AND $g->exists() )
        {
        	return $u->delete($g);
        }
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
        $g = new Group($group_id);
        $r = new Role($role_id);
        
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
        $g = new Group($group_id);
        $r = new Role($role_id);
        
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

        $a = new Attempt();
        $a->where('ip_address', $this->ip_address)->get();
        
        $login_type = $this->detect_identity($login);
        
        $u = new User();
        $u->where($login_type, $login);
        $user = $u->get();
        
        /* TODO: spammer can make a billion try?
         * should we prevent falier attempts even if the user does not exitsts ?
         */
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
            $user_id = $this->user_id;
        
        $u = new User($user_id);
        
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
        $u->where($type, $identity)->get();
        
        if ( $u->exists() )
        {
            // Only active users can reset passwords
            if ( $u->status == 'active' )
            {
                // Create an activation code
                $code = sha1($this->generate_random() . $identity);
                $u->activation_code = $code;
                $u->save();
                
                // Send user a forgotten password email
                $this->email_forgot_password($u->email, $code);
                
                return TRUE;   
            }
            else
            {
                $this->set_error('Only active users can reset their passwords.');
                return FALSE;
            }
        }
        else
        {
            $this->set_error('User does not exist');
            return FALSE;
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
        $u->where('activation_code', $code)->get();
        
        if ( $u->exists() )
        {
            $salt     = $this->generate_random();
            $new_pass = $this->generate_random();
            
            $password = $this->create_password($new_pass, $salt);
            
            $u->salt = $salt;
            $u->password = $password;
            $u->activation_code = '';
            $u->save();
            
            $this->email_new_password($u->email, $new_pass);
            
            return $new_pass;
        }
        else
        {
            return FALSE;
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
        
        if ( $this->ci->email->send() )
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
            $login_type = $this->detect_identity($login);   
        }
        
        $u = new User();
        $u->where($login_type, $login)->get();
        
        // Make sure user doesn't exist first
        if ( !$u->exists() )
        {
            $salt     = $this->generate_random();
            $password = $this->create_password($password, $salt);
            
            $u->{$login_type} = $login;
            $u->salt          = $salt;
            $u->password      = $password;
            
            if (!empty($fields) )
            {
                $um = new Umeta();
                $um->from_array($fields);
                $u->save($um);
            }
            
            if (!empty($roles))
            {
                $r = new Role();
                $r->where_in('id', $roles)->get();
                return $u->save($r);
            }
            
            if (!empty($groups))
            {
                $g = new Group;
                $g->where_in('id', $groups)->get();
                return $u->save($g);
            }
            
            if(empty($groups) AND empty($roles) AND empty($fields))
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
        $u = new User($user_id);
        
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
        $u = new User($user_id);
        
        if($u->exists())
        {
        	return $u->delete();
        }
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
        
        $u = new User($user_id);
        
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
        $u = new User($user_id);
                 
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
        
        /* 
         * TODO: why you did not update before rederect them?
         */
        if ( $redirect != '' )
            redirect($redirect);
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
    public function get_roles($user_id = 0)
    {
        if (empty($user_id))
            $user_id = $this->user_id;
        
        $u = new User($user_id);
        
        $assigned_roles = array();
        
        foreach ($u->role->get()->all AS $role)
        {
            $assigned_roles[$role->id] = $role->name;
        }
        
        return $assigned_roles;   
        
        /*
         * TODO: why not
         * return ( $u->exists() ) ? $u->role->get()->all : FALSE;
         */
    }
    
    /**
    * Get Role Permissions
    * Gets all permissions assigned to a role
    * 
    * @param mixed $role_id
    */
    public function get_role_permissions($role_id)
    {
        $r = new Role($role_id);
        
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
        $r = new Role($role_id);
        
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
        $u = new User($user_id);
        $r = new Role($role_id);
        
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
        $u = new User($user_id);
        $r = new Role($role_id);
        
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
            $created      = strtotime($a->created);
            
            // Minutes comparison
            $minutes      = floor($current_time - $created / 60);
            
	        // If current time elapsed between creation is greater than the attempts, reset
            if (($current_time - $created) > $this->config['auth.login_attempts_expiry'])
            {
                $this->reset_login_attempts($ip_address);
                // add the first attempt after reset them
                $a = $a->get_copy();
                $a->ip_address = $ip_address;
	            $a->attempts   = 1;
	            $a->save();
            }
            else{
	            // Increment new attempts
	            $a->attempts += 1;
	            $a->save();
            }
        }
        else
        {
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
        
        if(is_int($identity))
        {
        	$login_type = 'id';
        }
        elseif ($login_type == 'auto')
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
    
    public function decorate
    
    /**
    * Auth check
    * check if the current user can access the current uri
    * it check user, user role, user group, and user group role perms
    * all checks done using DataMapper directly
	* user can call this function in the Controller constructor or function
	* it follow the deny all expect allowed concept
    */
	public function auth_check()
    {
		$controller = $this->ci->uri->rsegment(1);
		if ($this->ci->uri->rsegment(2) != '')
		{
			$action = $controller.'/'.$this->ci->uri->rsegment(2);
		}
		else
		{
			$action = $controller.'/index';
		}
		
		$allow = false;
		$user = $this->get_user_info();		
		$u = new User($user['user']['id']);
		
		$p = new Permission();
		
		// check user
		$p->where('permission',$action)->where_related($u)->get();
		if($p->exists())
			return TRUE;
		
		// check user role
		$r = new Role();
		$r->where_related($u)->get();
		$p->where('permission',$action)->where_in_related($r)->get();
		if($p->exists())
			return TRUE;
		
		// check user group
		$g = new Group();
		$g->where_related($u)->get();
		$p->where('permission',$action)->where_in_related($g)->get();
		if($p->exists())
			return TRUE;
		
		// check user group role
		$r->where_in_related($g)->get();
		$p->where('permission',$action)->where_in_related($r)->get();
		if($p->exists())
			return TRUE;
		
		$this->set_error('You have not the permission to do that');
		return FALSE
	}
}