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
 * @license       Phil Sturgeon's Don't Be A Dick (DBAD) Licence
 */

class Auth_Simpleauth extends CI_Driver {
    
    // Codeigniter super instance
    protected $_ci;
    
    // Logged in user info
    protected $user_info;
    
    // Store config
    protected $config;
    
    // Are you checking for a username, email address or auto detecting?
    protected $identity_method; 
    
    // Admin role ID's that determine a user to be an admin
    protected $admin_roles;
    
    // Errors and mesages
    protected $errors;
    protected $messages;
    
    /**
    * Constructor
    * 
    */
    public function __construct() 
    {
        $this->_ci = get_instance();
        $this->_ci->load->database();
        $this->_ci->config->load('auth');
        $this->_ci->load->helper('cookie');
        $this->_ci->load->helper('url');
        $this->_ci->lang->load('auth');
        $this->_ci->load->library('session');
        $this->_ci->load->model('auth/user_model');
        
        // Simpleauth config data
        $this->config = (object)$this->_ci->config->item('simpleauth');
        
        // Identity method for checking users against
        $this->identity_method = config_item('identity');
        
        // Get role meta info
        $role = $this->get_role_meta();
        
        // Default role values
        $this->user_info['role_id']       = 0;
        $this->user_info['role_name']     = "guest";
        $this->user_info['role_realname'] = "Guest";
        
        // If we have role meta (only for logged in users)
        if ($role)
        {
            $this->user_info['role_id']       = $role->role_id;
            $this->user_info['role_name']     = $role->slug;
            $this->user_info['role_realname'] = $role->name;
        }
        
        // Store the logged in userinfo in the user array so we can neatly access it
        $this->user_info['user_id']  = ($this->_ci->session->userdata('user_id')) ? $this->_ci->session->userdata('user_id') : 0;
        $this->user_info['username'] = $this->_ci->session->userdata('username');
        $this->user_info['email']    = $this->_ci->session->userdata('email');
        
        // Reset each time this class is constructed, yo!
        $this->errors   = "";
        $this->messages = "";
        
        // If we have some admin roles
        if ( $this->config->admin_roles )
        {
            $this->admin_roles = $this->config->admin_roles;   
        }
        
        // Do we remember the user?
        $this->do_you_remember_me();        
    }
    
    /**
    * Will return role name, role ID, role slug
    * 
    */
    public function get_role_meta($userid = '')
    {
        if ( $userid == '' )
        {
            $userid = $this->_ci->session->userdata('user_id');
        }
        
        return $this->_ci->user_model->get_role($userid);
    }
    
    /**
    * Get user meta from the profile fields column in the user table.
    * 
    * @param mixed $userid
    */
    public function get_user_meta($username = '')
    {
        if ($username == '')
        {
            $username = $this->user_info['username'];
        }
        
        // Get the user
        $user = $this->_ci->user_model->get_user('username', $username);
        
        // Unserialize profile fields
        $fields = @unserialize($user->profile_fields);
        
        // Return the profile fields as an object
        return (!empty($fields)) ? (object)$fields : false;
    }
    
    /**
    * Is anyone logged in? Assumes a logged in user has a user_id higher than 0
    * 
    * @param mixed $config
    */
    public function logged_in()
    {
        return ($this->user_info['user_id'] > 0) ? $this->user_info['user_id'] : false;   
    }
    
    /**
    * Currently logged in user is a user
    * 
    */
    public function is_user()
    {
        $username = $this->user_info['username'];
        return ($username) ? $username : false;
    }
    
    /**
    * Currently logged in user is an administrator?
    * 
    */
    public function is_admin()
    {
        $roleid = $this->user_info['role_id'];
        return (in_array($roleid, $this->admin_roles)) ? true : false;
    }
    
    /**
    * Get currently logged in user data
    * 
    */
    public function get_this_user()
    {   
        if ( $this->logged_in() )
        {
            $username = $this->user_info['username'];
            return $this->_ci->user_model->get_user("username", $username);
        }
        else
        {
            return $this->user_info;
        }
    }
    
    /**
    * Get a user by user ID
    * 
    * @param mixed $id
    */
    public function get_user_by_id($id = 0)
    {
        return $this->_ci->user_model->get_user("id", $id);
    }
    
    /**
    * Forces a user to be logged in without a password
    * 
    * @param mixed $identity
    */
    public function force_login($identity)
    {
        // Determine and set the identity type
        $this->detect_identity($identity);
        
        $user = $this->_ci->user_model->get_user($this->identity_method, $identity);

        $role = $this->get_role_meta($user->id);
        
        $user_data = array(
            'user_id'  => $user->id,
            'username' => $user->username,
            'email'    => $user->email
        );
        
        $this->_ci->session->set_userdata($user_data);
        return true;
    }

    /**
    * Detect Identity
    * Determine what identity we want if set to auto
    *
    * @param mixed $identity
    */
    private function detect_identity($identity)
    {
        if ($this->identity_method == "auto")
        {
            $this->_ci->load->helper('email');

            // If we were supplied a valid email
            if (valid_email($identity))
            {
                $this->identity_method = "email";
            }
            else
            {
                $this->identity_method = "username";
            }
        }
    }

    /**
    * Log a user in
    * 
    * @param mixed $identity
    * @param mixed $password
    * @param mixed $remember
    * @param mixed $redirect_to
    */
    public function login($identity, $password, $remember = false, $redirect_to = false)
    {
        // Trim details
        $identity = trim($identity);
        $password = trim($password);

        // Determine and set the identity type
        $this->detect_identity($identity);

        // Make sure we're not logged in
        if ( $this->user_info['user_id'] == 0 )
        {   
            // Get the user from the database
            $user = $this->_ci->user_model->get_user($this->identity_method, $identity);
            

            // No user? Let's get out of here
            if ( $user === FALSE )
            {
                $this->add_error($this->_ci->lang->line('error_login_details'));
                return false;
            }
            
            // If we have a salt
            if ( !empty($user->salt) )
            {
                // Passwords match
                if ( $user->password == $this->hash_password($password, $user->salt) )
                {
                    // If the user is an activated
                    if ( $user->status == "active" )
                    {
                        // Set remember me
                        if ($remember === true)
                        {
                            $this->set_remember_me($user->id);
                        }
                    
                        // Log the user in using the force login function
                        if ( $this->force_login( $user->{$this->identity_method} ) )
                        {
                            // Update login date/time
                            $this->update_user(array('last_login' => date('Y-m-d H:i:s')), $user->username);
                            
                            // If we are redirecting after logging in
                            if ( $redirect_to !== FALSE )
                            {
                                redirect($redirect_to);
                            }
                            else
                            {
                                return true;
                            }   
                        }
                        else
                        { 
                            $this->add_error($this->_ci->lang->line('error_login'));
                        }
                    }
                    elseif ( $user->status == "banned" )
                    {
                        $this->add_error($this->_ci->lang->line('error_banned'));
                        return false;
                    }
                    elseif ( $user->status == "inactive" )
                    {
                        $this->add_error($this->_ci->lang->line('error_inactive'));
                        return false;
                    }  
                    elseif ( $user->status == "validating" )
                    {
                        $this->add_error($this->_ci->lang->line('error_validating'));
                        return false;
                    }               
                }
                else
                {
                    $this->add_error($this->_ci->lang->line('error_password_mismatch_login'));
                    return false;
                }   
            }
            else
            {
                $this->add_error($this->_ci->lang->line('error_nosalt'));
                return false;
            }
        }
        else
        {
            // We are already logged in, return true
            return true;
        }
    }
    
    /**
     * Logout
     *
     * 07/05/11 - Updated function to clear out database cookie data if needed
     */
    public function logout($redirect_to = false)
    {
        // If we have a user ID, someone is logged in
        if ( $this->user_info['user_id'] > 0 )
        {
            if ($this->do_you_remember_me() == TRUE)
                $this->update_user(array("remember_me" => ""), $this->user_info['username']);

            $user_data = array(
                'user_id'  => 0,
                'role_id'  => 0,
                'username' => '',
                'email'    => '',
            );

            $this->_ci->session->set_userdata($user_data);

            delete_cookie($this->config->cookie_name);
			
            if ($redirect_to !== false)
            {
                redirect($redirect_to);
            }
            else
            {
                return true;
            }
        }
        else
        {
            if ($redirect_to !== false)
            {
                redirect($redirect_to);
            }
            else
            {
                return true;
            }
        }
    }
    
    /**
    * Add a new user
    * 
    * @param mixed $username
    * @param mixed $password
    * @param mixed $email
    * @param mixed $profile_fields
    */
    public function add_user($username, $password, $email, $role_id = 1, $status = 'active', $profile_fields = array())
    {
        if ( empty($username) OR empty($password) OR empty($email) )
        {
           return false;
        }

        if ( !$this->_ci->user_model->is_unique(array("email" => $email, "username" => $username)) )
        {
            return false;
        }
		
        if ( !$this->_ci->user_model->is_unique(array("email" => $email, "username" => $username)) )
        {
            return false;
        }
        
        $salt     = $this->create_salt();
        $password = $this->hash_password($password, $salt);
        
        $user = array(
            'username'       => trim($username),
            'password'       => $password,
            'email'          => trim($email),
            'join_date'      => date('Y-m-d H:i:s'),
            'status'         => $status,
            'salt'           => $salt,
            'profile_fields' => serialize($profile_fields)
        );
        
        $this->_ci->db->insert('users', $user);
        
        if ($this->_ci->db->affected_rows() >= 1)
        {
            $role_meta = array('user_id' => $this->_ci->db->insert_id(),'role_id' => $role_id);
            $this->_ci->db->insert('users_to_roles', $role_meta);
            
            return true;
        }
        else
        {
            $this->add_error($this->_ci->lang->line('error_user_not_added'));
            return false;
        }
        
    }    
    
    /**
    * Update a users information
    * 
    * @param mixed $values
    * @param mixed $username
    */
    public function update_user($values, $username = '')
    {
        $username = ($username) ? $username : $this->user_info['username'];
        $current_values = $this->_ci->user_model->get_user('username', $username);
        
        $update = array();
        
        if ( array_key_exists('username', $values) )
        {
            $this->add_error($this->_ci->lang->line('error_username_change'));
            return false;
        }
        
        if ( array_key_exists('password', $values) )
        {
            // Old password is wrong
            if ( $current_values->password != $this->hash_password($values['old_password'], $current_values->salt) )
            {
                $this->add_error($this->_ci->lang->line('error_username_mismatch'));
                return false;
            }
            
            // If we have a new password
            if ( !empty($values['password']))
            {
                $update['password'] = $this->hash_password($values['password'], $current_values->salt);
            }
            unset($values['password']);
        }
        
        // Old password provided?
        if (array_key_exists('old_password', $values))
        {
            unset($values['old_password']);
        }
		
        // Remembering this user?
        if (array_key_exists('remember_me', $values))
        {
            $update['remember_me'] = $values['remember_me'];
            unset($values['remember_me']);
        }
            
        // If we have an email in our values
        if ( array_key_exists('email', $values) )
        {
            $update['email'] = $values['email'];
            unset($values['email']);
        }
        
        // Update the last login date
        if ( array_key_exists('last_login', $values) )
        {
            $update['last_login'] = $values['last_login'];
            unset($values['last_login']);
        }
        
        // If we have a role ID
        if ( array_key_exists('role_id', $values) )
        {
            if ( is_int($values['role_id']) )
            {
                $update_role['role_id'] = $values['role_id'];
            }
            unset($values['role_id']);
        }
        
        // After setting everything else, if we have any left overs assume profile fields
        if ( !empty($values) )
        {
            $profile_fields = @unserialize($current_values->profile_fields);
            
            foreach ($values as $key => $val)
            {
                if ($val === null)
                {
                    unset($profile_fields[$key]);
                }
                else
                {
                    $profile_fields[$key] = $val;
                }
            }
            $update['profile_fields'] = serialize($profile_fields);
        }
        
        // Update the user
        $this->_ci->db->where('username', $username)->update('users', $update);
        
        // If we're updating the role ID
        if ( $update_role )
        {
            $this->_ci->db->where('user_id', $current_values->user_id)->update('users_to_roles', $update_role);   
        }
        
        // If update was successful.
        if ( $this->_ci->db->affected_rows() == 1 )
        {
            return true;
        }
        else
        {
            $this->add_error($this->_ci->lang->line('error_user_not_updated'));
            return false;
        }   
    }   
    
    /**
    * Delete a user
    * 
    * @param mixed $username
    */
    public function delete_user($username)
    {
        if ( empty($username) )
        {
            $this->add_error($this->_ci->lang->line('empty_username_update'));
            return false;
        }
        else
        {
            $this->_ci->db->where('username', $username)->delete('users');
            
            // If delete was successful.
            if ( $this->_ci->db->affected_rows() == 1 )
            {
                return true;
            }
            else
            {
                $this->add_error($this->_ci->lang->line('error_user_not_deleted'));
                return false;
            }
            
        }
    }
    
    /**
    * Restrict a particular function or controller
    *
    * @param mixed $needles
    * @param mixed $restrict
    * @param mixed $redirect_to
    */
    public function restrict($needles = '', $restrict = 'role', $redirect_to = NULL)
    {
        // Force needles to be array
        $needles = is_array($needles) ? $needles : array($needles);

        if (empty($needles))
        {
            return false;
        }

        // Redirect to base url if no redirect URL supplied
        $redirect_to = ($redirect_to === NULL) ? base_url() : $redirect_to;

        // If we are restricting to role ID's
        if ( $restrict == 'role' )
        {
            $criteria = $this->user_info['role_id'];
        }
        // Are we restricting to usernames
        elseif ( $restrict == 'username' )
        {
            $criteria = $this->user_info['username'];
        }
        else
            return false;


        // If the role is in the allowed roles list
        if ( in_array($criteria, $needles) )
        {
            return true;
        }
        else
        {            
            $this->add_error($this->_ci->lang->line('access_denied'));
            redirect($redirect_to);
        }
    }
    
    /**
    * Does the currently logged in user or specific user
    * have a particular user role.
    * 
    * @param mixed $role_id
    */
    public function has_role($role_ids, $user_id = 0)
    {
        $role_ids = is_array($role_ids) ? $role_ids : array($role_ids);

        if ($user_id == 0)
        {
            $user_id = $this->user_info['user_id'];
        }
        
        $meta = $this->get_role_meta($user_id);
        
        if ($meta AND in_array($meta->role_id, $role_ids))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
    * Creates a password salt
    * 
    */
    public function create_salt()
    {                
        return uniqid(mt_rand(), true);  
    }
    
    /**
    * Hash a password
    * 
    * @param mixed $password
    * @param mixed $salt
    */
    public function hash_password($password, $salt = null)
    {
        if ($salt === null)
        {
            $password = sha1($password);
        }
        else
        {
            $password = hash("sha1", $password.$salt);
        }
        
        return $password;
    }
    
    /**
    * Change a password
    * 
    * @param mixed $old_password
    * @param mixed $new_password
    * @param mixed $username
    */
    public function change_password($old_password, $new_password, $username)
    {
        $update = $this->update_user(array('old_password' => $old_password, 'password' => $new_password), $username);
        
        if ( $update === FALSE )
        {
            $this->add_error($this->_ci->lang->line('error_password_update'));
            return false;
        }
        else
        {
            return true;
        }
    }
    
    /**
    * Sets a user to be remembered
    * 
    * @param mixed $userid
    */
    private function set_remember_me($id)
    {
        $this->_ci->load->library('encrypt');

        $token  = md5(uniqid(rand(), TRUE));
        $expiry = time() + $this->config->cookie_expiry;

        $remember_me = $this->_ci->encrypt->encode(serialize(array($id, $token, $expiry)));

        $cookie = array(
            'name'      => $this->config->cookie_name,
            'value'     => $remember_me,
            'expire'    => $expiry
        );

        // For DB insertion
        $cookie_db_data = array(
            'remember_me' => $remember_me
        );
        
        $user = $this->get_user_by_id($id);

        set_cookie($cookie);
        $this->update_user($cookie_db_data, $user->username);
    }

    /**
    * Checks if we remember a particular user
    * 
    */
    public function do_you_remember_me()
    {
        $this->_ci->load->library('encrypt');
        $error = FALSE;
        $cookie_data = get_cookie($this->config->cookie_name);

        // If we have a cookie stored
        if (!empty($cookie_data))
        {
            $cookie_data = unserialize( $this->_ci->encrypt->decode($cookie_data) );

            if (!empty($cookie_data) && count($cookie_data) == 3)
            {
                list($c_id, $c_token, $c_expiry) = $cookie_data;

                $user = $this->get_user_by_id($c_id);

                // If we got a user
                if (!empty($user))
                {
                    $db_data = unserialize( $this->_ci->encrypt->decode($user->remember_me) );

                    if (!empty($db_data) && count($db_data) == 3)
                    {
                        list($db_id, $db_token, $db_expiry) = $db_data;

                        if ($c_id != $db_id || $c_token != $db_token || $c_expiry != $db_expiry)
                        {
                            // Something isn't right, kick em out
                            delete_cookie($this->config->cookie_name);
                            return FALSE;
                        }
                        else
                        {
                            // All good, I remember ya captain, but is it too late?
                            if ((int) $db_expiry < time())
                            {
                                delete_cookie($this->config->cookie_name);
                                return FALSE;
                            }

                            // Welcome back!
                            $this->force_login($user->username);
                            return TRUE;
                        }
                    }
                    else
                        delete_cookie($this->config->cookie_name);
                        return FALSE;

                }
                else
                {
                    delete_cookie($this->config->cookie_name);
                    return FALSE;
                }

            }
            else
            {
                delete_cookie($this->config->cookie_name);
                return FALSE;
            }
        }
        else
            return FALSE;
        
    }
    
    /**
    * Show error messages
    * 
    * @param mixed $left
    * @param mixed $right
    */
    public function show_errors($return=false, $left = "", $right = "")
    {
        if ( is_array($this->errors) AND !empty($this->errors) )
        {
            $html = "";
            
            foreach ($this->errors AS $error)
            {
                $html .= $left.$error.$right;
            }

            if ($return === FALSE)
                echo $html;
            else
                return $html;
        }
        else
        {
            return false;
        }
    }

    /**
     * Add an error message to the array
     *
     * @param string $message
     */
    public function add_error($message)
    {
        $this->errors[] = $message;

        if ($this->config->flash_errors)
        {
            $this->_ci->session->set_flashdata($this->config->flash_errors_name, $this->errors);
        }
    }
    
    /**
    * Just for decoration, ha ha.
    */
    public function decorate() {}

}
