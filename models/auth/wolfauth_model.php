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

class Wolfauth_model extends CI_Model {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('email');
		$this->load->library('email');		
	}

    /**
     * Logout
     *
     * Logs a user out
     */
    public function logout()
    {
        $user = $this->get_user();

        $this->session->sess_destroy();

        $this->load->helper('cookie');
        delete_cookie($this->config->item('cookie.name', 'wolfauth'));

        $this->update_user(array('remember_me' => ''), $user->id);

        $this->session->set_userdata('logged_in', FALSE);
        $this->session->set_userdata('user', FALSE);
    }
	
	public function get_user_password_reset($id = '', $passkey = '')
	{
		$this->db->where('id', $id);
		$this->db->where('auth_code', $passkey);

		$user = $this->db->get($this->config->item('table.users', 'wolfauth'));
		
		return ($user->num_rows() == 1) ? $user : FALSE;
	}

    /**
     * User Exists
     *
     * Returns true or false if the user already exists
     *
     * @param $username
     * @return bool
     */
    public function user_exists($username)
    {
        return ( $this->get_user_by_username($username) ) ? TRUE : FALSE;
    }

    /**
     * Email Exists
     *
     * Does an email address exist in the database?
     *
     * @param $email
     * @return bool
     */
    public function email_exists($email)
    {
        return ( $this->get_user_by_email($email) ) ? TRUE : FALSE;
    }

    /**
     * Count Users
     *
     * Count the number of users in the database
     *
     * @return mixed
     */
    public function count_users()
    {
        return $this->db->count_all($this->config->item('table.users', 'wolfauth'));
    }

    /**
     * Get User By Username
     *
     * Gets a user from the database via their username
     *
     * @param string $username
     * @return mixed
     */
    public function get_user_by_username($username = '')
    {
        return $this->_get_user($username, 'username');
    }

    /**
     * Get User By ID
     *
     * Gets a user from the database via their user ID
     *
     * @param string $id
     * @return mixed
     */
    public function get_user_by_id($id = '')
    {
        return $this->_get_user($id, 'id');
    }

    /**
     * Get User By Email
     *
     * Get a user via their email
     *
     * @param string $email
     * @return mixed
     */
    public function get_user_by_email($email = '')
    {
        return $this->_get_user($email, 'email');
    }

    /**
     * Get Users
     *
     * Returns all users
     *
     * @param	$limit mixed
     * @param	$offset int
     * @return	object
     */
    public function get_users($limit = 10, $offset = 0)
    {
        $fields = 'users.id, users.email, users.first_name, users.last_name, users.activated, users.joined, roles.id AS role_id, roles.role_name, roles.role_slug';

        if ($limit != '*')
        {
            $this->db->limit($limit, $offset);
        }

        $this->db->select($fields);
        $this->db->join($this->config->item('table.roles', 'wolfauth'), 'users.role_id = roles.id');

        return $this->db->get($this->config->item('table.users', 'wolfauth'));
    }

    /**
     * Force Login
     *
     * Forces a user to be logged in without a password
     * @param $identity (username or email)
     * @return bool
     *
     */
    public function force_login($identity)
    {
        // Detect if we have an email or username
        $identity = $this->determine_identity($identity);
        $user     = '';

        if ($identity == 'email')
        {
            // Fetch the user by email address
            $user = $this->get_user_by_email($identity);
        }
        else
        {
            // Fetch the user by username
            $user = $this->get_user_by_username($identity);
        }

        // Always will return true
        return $this->set_login($user);
    }

    /**
     * Insert User
     *
     * Inserts a user and any meta into the database
     *
     * @param $fields
     * @return bool
     */
	public function insert_user($fields)
	{
		if ( isset($fields['password']) )
		{
            $fields['password'] = $this->generate_password($fields['password']);
		}

		return ($this->db->insert($this->config->item('table.users', 'wolfauth'), $fields)) ? $this->db->insert_id() : FALSE;
	}

    /**
     * Update User
     *
     * Update a users details
     *
     * @param array $fields
     * @param $user_id
     * @return bool
     */
	public function update_user($fields = array(), $user_id)
	{
		// Find the user ID
		$this->db->where('id', $user_id);
		
		// If we have a password to update!
		if ($fields['password'])
        {
			$fields['password'] = $this->generate_password($fields['password']);
		}
		
		return ($this->db->update($this->config->item('table.users', 'wolfauth'), $fields)) ? TRUE : FALSE; 
	}

    /**
     * Delete User
     *
     * Deletes a user
     *
     * @param string $user_id
     * @return bool
     */
	public function delete_user($user_id = '')
	{
		$this->db->where('id', $user_id);

		$this->db->delete($this->config->item('table.users', 'wolfauth'));

		return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
	}
	
    /**
     * Update Login Attempts
     *
     * Used by the login function when a user attempts to login
     * unsuccessfully.
     *
     * @param mixed $ip_address
     * @return mixed
     */
    public function update_login_attempts($ip_address = NULL)
    {
        if (is_null($ip_address))
        {
            $ip_address = $this->ip_address;
        }
            
        $exists = $this->db->get_where($this->config->item('table.attempts', 'wolfauth'), array('ip_address' => $ip_address));
        
        if ( $exists->num_rows() >= 1 )
        {
            $exists = $exists->row();
            $current_time = time();
            $created      = strtotime($exists->created);
            
            // Minutes comparison
            $minutes      = floor($current_time - $created / 60);
            
	        // If current time elapsed between creation is greater than the attempts, reset
            if (($current_time - $created) > $this->config->item('attempts.expiry', 'wolfauth'))
            {
                $this->reset_login_attempts($ip_address);

                // add the first attempt after reset them
                $insert = $this->db->insert($this->config->item('attempts.expiry', 'wolfauth'), array('ip_address' => $ip_address, 'attempts' => 1));

                return $insert->affected_rows();
            }
            else
            {
	            // Increment new attempts
                $this->db->set('attempts', 'attempts + 1', FALSE);
                $this->db->set('ip_address', $ip_address);
                $insert = $this->db->update($this->config->item('attempts.expiry', 'wolfauth'));
            }
        }
        else
        {
            $insert = $this->db->insert($this->config->item('attempts.expiry', 'wolfauth'), array('ip_address' => $ip_address, 'attempts' => 1));
            return $insert->affected_rows();
        }
    }

    /**
     * Set Login
     *
     * Sets login session info
     *
     * @param $user
     * @param bool $remember
     * @return bool
     */
    public function set_login($user, $remember = false)
    {
        unset($user->password);

        $this->session->set_userdata(array(
            'user'     => $user,
            'logged_in' => TRUE
        ));

        if ($remember)
        {
            $this->set_remember_me($user->id);
        }
        return TRUE;
    }
	
    /**
     * Reset Login Attempts
     *
     * Resets login attempts increment value in the database
     * for a particular IP address.
     *
     * @param mixed $ip_address
     */
    public function reset_login_attempts($ip_address)
    {
		$this->db->where('ip_address', $ip_address);
		$this->db->delete($this->config->item('table.attempts', 'wolfauth'));
    }

    /**
     * Has Role
     *
     * Does a user have a particular role?
     *
     * @param $role_slug
     * @return bool
     */
    public function has_role($role_slug)
    {
        $this->db->select('roles.role_name, roles.role_slug')->where('roles.role_slug', $role_slug)->join($this->config->item('table.roles', 'wolfauth'), 'users.role_id = roles.id');

        $this->db->get($this->config->item('table.users', 'wolfauth'));

        return ($this->db->num_rows() == 1) ? TRUE : FALSE;
    }

    /**
     * Has Permission
     *
     * Has the current user got permission to access this resource?
     *
     * @param $permission
     * @return bool
     */
    public function has_permission($permission = '')
    {
        // If we have no permission check current URL
        if ($permission == '')
        {
            // The permission string by default is the whole URL string
            $permission = trim($this->uri->uri_string(), '/');
        }

        $user = $this->get_user();

        $this->db->select('permissions.permission')->where('role_id', $user->role_id)->get($this->config->item('table.permissions', 'wolfauth'));

        return ($this->db->num_rows() == 1) ? TRUE : FALSE;
    }

    /**
      * Add Permission
     *
      * Adds a permission to a role
      *
      * @param $role_id
      * @param $permission
      * @return bool
      */
    public function add_permission($role_id, $permission)
    {
        $data['role_id'] = $role_id;
        $data['permission'] = $permission;
        $this->db->insert($this->CI->config->item('table.permissions', 'wolfauth'), $data);

        return ($this->db->affected_rows() == 1) ? TRUE : FALSE;
    }

    /**
     * Edit Permission
     *
     * Allows you to edit a permission via the admin area
     *
     * @param int $permission_id
     * @param $data
     * @return bool
     */
    public function edit_permission($permission_id, $data)
    {
        // Make sure we have some data
        if (isset($data['id']) AND isset($data['permission']))
        {
            $this->db->where('id', $data['id']);
            $this->db->update($this->CI->config->item('table.permissions', 'wolfauth'), array('permission' => $data['permission']));

            return ($this->db->affected_rows() == 1) ? TRUE : FALSE;
        }
    }

    /**
     * Delete Permission
     *
     * Delete a permission from the permissions table
     *
     * @param int $permission_id
     * @return bool
     */
    public function delete_permission($permission_id)
    {
        $this->db->where('id', $permission_id);
        $this->db->delete($this->CI->config->item('table.permissions', 'wolfauth'));

        return ($this->db->affected_rows() == 1) ? TRUE : FALSE;
    }

	
    /**
    * Email Forgot Password
     *
    * If a user forgets their password, they can send themselves
    * an email to reset their password.
    * 
    * @param mixed $email
    * @param mixed $code
    * @return bool
    */
    public function email_forgot_password($email, $code)
    {
        $data['email']       = $email;
        $data['forgot_code'] = $code;
        $data['reset_link']  = $this->config->item('reset_password_link', 'wolfauth');
        
        $message = $this->load->view('auth/emails/reset_password', $data, true);
        
        $this->email->clear();
        $this->email->set_newline("\r\n");
        $this->email->from($this->config->item('admin_email', 'wolfauth'), $this->config->item('site_name', 'wolfauth'));
        $this->email->to($email);
        $this->email->subject($this->config->item('site_name', 'wolfauth') . ' - Forgotten Password Verification');
        $this->email->message($message);
		
		return ( $this->email->send() ) ? TRUE : FALSE;  
    }
	
    /**
    * Email New Password
     *
    * Send a newly generated password to the user
    * 
    * @param mixed $email
    * @param mixed $password
    * @return bool
    */
    public function email_new_password($email, $password)
    {
        $data['email']    = $email;
        $data['password'] = $password;
        
        $message = $this->load->view('auth/emails/new_password', $data, true);
        
        $this->ci->email->clear();
        $this->ci->email->set_newline("\r\n");
        $this->ci->email->from($this->config->item('admin_email', 'wolfauth'), $this->config->item('site_name', 'wolfauth'));
        $this->ci->email->to($email);
        $this->ci->email->subject($this->config->item('site_name', 'wolfauth') . ' - Forgotten Password Request');
        $this->ci->email->message($message);
		
		return ( $this->ci->email->send() ) ? TRUE : FALSE; 
    }

    /**
     * Check Password
     *
     * Checks a password and salt to ensure it matches
     *
     * @param $password
     * @param $old_password
     * @param $salt
     * @return bool
     */
    public function check_password($password, $old_password, $salt)
    {
        $salted = $this->generate_password($password.$salt);

        return ($salted == $old_password) ? TRUE : FALSE;
    }

    /**
     * Generate Salt
     *
     * Generate a password salt value
     *
     * @param int $length
     * @return mixed
     */
    public function generate_salt($length = 8)
    {
        $this->load->helper('string');

        return random_string('alnum', $length);
    }

    /**
     * Generate Password
     *
     * Generates a random password or hashes one
     *
     * @param string $password
     * @return mixed
     */
	public function generate_password($password = '')
	{
		$this->load->helper('security');
		$this->load->helper('string');

		if ($password == '')
		{
			// Generate a password 8 characters long
			$password = random_string('alnum', 8);
		}

		return do_hash($password);
	}

    /**
     * Logged In
     *
     * Is a user logged in?
     *
     * @return mixed
     */
    public function logged_in()
    {
        return $this->session->userdata('logged_in');
    }

    /**
     * Get User
     *
     * Get the current user session info
     *
     * @return mixed
     */
    public function get_user()
    {
        return $this->session->userdata('user');
    }

    /**
     * Get User ID
     *
     * Get the user ID of the currently logged in user
     *
     * @return bool
     */
    public function get_user_id()
    {
        $user = $this->get_user();

        return ($user) ? $user->id : FALSE;
    }

    /**
     * Determine Identity
     *
     * Is the supplied value an email address or username
     *
     * @param $identity
     * @return string
     */
    public function determine_identity($identity)
    {
        $this->load->helper('email');

        if ( $identity == 'auto' )
        {
            if ( valid_email($identity) )
            {
                $identity = "email";
            }
            else
            {
                $identity = "username";
            }
        }

        return $identity;
    }

    public function get_remember_me()
    {
        $this->load->library('encrypt');

        if ( $cookie = get_cookie($this->config->item('cookie.name', 'wolfauth')) )
        {
            $user_id = '';
            $token = '';
            $timeout = '';

            $cookie_data = $this->encrypt->decode($cookie);

            if (strpos($cookie_data, '//') !== FALSE)
            {
                $cookie_data = explode('//', $cookie_data);

                if (count($cookie_data) == 3)
                {
                    list($user_id, $token, $timeout) = $cookie_data;
                }

                if ( (int) $timeout < time() )
                {
                    return FALSE;
                }

                if ( $user = $this->get_user_by_id($user_id) )
                {
                    // Fill the session and renew the remember me cookie
                    $this->session->set_userdata(array(
                        'user'		=> $user,
                        'logged_in'	=> true
                    ));

                    $this->set_remember_me($user_id);

                    return TRUE;
                }

                delete_cookie($this->config->item('cookie.name', 'wolfauth'));
            }

            return FALSE;
        }
    }

    /**
     * Set Remember Me
     *
     * Sets a remember me cookie and stores data
     *
     * @param $user_id
     */
    public function set_remember_me($user_id)
    {
        $this->load->library('encrypt');

        $token = md5(uniqid(rand(), TRUE));
        $timeout = $this->config->item('cookie.expiry', 'wolfauth');

        $remember_me = $this->encrypt->encode($user_id.'//'.$token.'//'.(time() + $timeout));

        $cookie = array(
            'name'		=> $this->config->item('cookie.name', 'wolfauth'),
            'value'		=> $remember_me,
            'expire'	=> $timeout
        );

        set_cookie($cookie);
        $this->update_user(array('remember_me' => $remember_me), $user_id);
    }

    /**
     * _Get User
     *
     * Protected utility function for getting user info
     *
     * @param string $needle
     * @param string $haystack
     * @return mixed
     */
    protected function _get_user($needle = '', $haystack = 'username')
    {
        $this->db->where($haystack, $needle);

        $user = $this->db->get($this->config->item('table.users', 'wolfauth'));

        return ($user->num_rows() == 1) ? $user : FALSE;
    }

}