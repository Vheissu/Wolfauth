<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* @name auth
* @category Model
* @package auth
* @author Dwayne Charrington
* @copyright 2011
* @link http://ilikekillnerds.com
*/

class Auth extends CI_Model {
    
    protected $_tables;
    
    protected $guest_role;
    protected $admin_roles;
    protected $identity_criteria;

    protected $user_id = NULL;

    protected $error_array = array();

    protected $message_array = array();
    
    public function __construct()
    {
        parent::__construct();
        
        /**
        * We load all of this in-case the end user doesn't autoload
        * anything.
        *
        * @var auth
        */
        $this->load->database();
        $this->load->config('auth', TRUE);
        $this->load->helper('cookie');
        $this->load->helper('url');
        $this->lang->load('auth');
        $this->lang->load('auth_email');
        $this->load->library('session');
        $this->load->library('email');

        $this->guest_role        = $this->config->item('guest_role', 'auth');
        $this->admin_roles       = $this->config->item('admin_roles', 'auth');
        $this->identity_criteria = $this->config->item('identity_criteria', 'auth');
        $this->user_id           = $this->session->userdata('user_id');

        // Check if we remember this user and if we do, log them in
        $this->do_you_remember_me();
        
        // Get the array of tables from the config file
        $this->_tables = $this->config->item('tables', 'auth');
    }
    
    /**
    * Log a user in to the site. Also allows you to redirect
    * somewhere if the user is successfully logged in.
    *
    * @param mixed $criteria
    * @param mixed $password
    * @param bool  $redirect
    */
    public function login($needle = '', $password = '', $redirect = '')
    {
        // We need a needle and password
        if ( $needle == '' OR $password = '' )
        {
            $this->error_array[] = $this->lang->line('missing_login_credentials');
            return FALSE;
        }
        
        // We are already logged in
        if ( $this->user_id > 0 OR $this->session->userdata('user_role') > $this->guest_role )
        {
            if ($redirect != '')
            {
                redirect($redirect);
            }
            return TRUE;
        }

        $user = $this->get_user($needle, $this->identity_criteria);

        if ($user)
        {
            if ($this->hash_password($password) == $user->password)
            {
                $user_id = $user->id;
                $this->force_login($needle);

                if ($this->input->post('remember_me') == 'yes')
                {
                    $this->_set_remember_me($user_id);
                }

                // If we are redirecting after logging in
                if ($redirect != '')
                {
                    redirect($redirect);
                }
                else
                {
                    return TRUE;
                }
            }
            else
            {
                $this->error_array[] = $this->lang->line('password_incorrect');

                return FALSE;
            }
        }

        $this->error_array[] = $this->lang->line('account_not_found');

        // All hope is lost...
        return FALSE;
    }

    /**
    * Forces a user to be logged in via the criteria set in the config file.
    * Can log in a user without needing a password or anything of that kind!
    *
    * @param mixed $needle
    */
    public function force_login($needle = '')
    {
        if ($needle == '')
        {
            return FALSE;
        }

        // Get the user to make sure they exist
        $user = $this->get_user($needle, $this->identity_criteria);

        if ( $user )
        {
            $this->session->set_userdata(array(
                'user_id'    => $user->id,
                'username'   => $user->username,
                'role_id'    => $user->role_id,
                'email'      => $user->email
            ));

            return TRUE;
        }

        return FALSE;
    }
    
    /**
    * I wonder what this function does? I think it logs a user out, but I
    * can't be sure. I've had a few drinks.
    *
    */
    public function logout($redirect = '')
    {
        $this->session->sess_destroy();

        $this->load->helper('cookie');
        delete_cookie('wolfauth');

        $user_data = array(
            'id' => $this->user_id,
            'remember_me' => ''
        );

        // Remove remember me data, yo.
        $this->update_user($user_data);
        
        $redirect = ($redirect == '') ? base_url() : $redirect; 

        // Redirect the user to oblivion
        redirect($redirect);
    }
    
    /**
    * Determines if the current user or a specific user is an administrator
    * 
    * @param mixed $userid
    * @return bool
    */
    public function is_admin($userid = 0)
    {
        // Conditional to return TRUE or FALSE if the user has an admin ID
        return (in_array($this->get_role($userid), $this->admin_roles)) ? TRUE : FALSE;
    }
    
    /**
    * Determines if the current user or a specific user is just a standard user.
    * 
    * @param mixed $userid
    */
    public function is_user($userid = 0)
    {
        // If user role is greater thsn 0, return true
        return ($this->get_role($userid) > $this->guest_role) ? TRUE : FALSE;
    }
    
    /**
    * Returns true or false if a user is logged in
    * 
    */
    public function is_logged_in()
    {
        return $this->user_id ? TRUE : FALSE;
    }
    
    /**
    * Does a username alrady exist in the database?
    * 
    * @param mixed $username
    */
    public function username_exists($username = '')
    {
        return $this->get_user_by_username($username) ? TRUE : FALSE;
    }
    
    /**
    * Returns the user role ID of the currently logged in user
    * or the role ID of a specfic user
    * 
    * @param mixed $userid
    * @returns current role ID if user logged in or role ID of
    *          specific user.
    */
    public function get_role($userid = 0)
    {
        if ( $userid == 0 )
        {
            return ($this->session->userdata('user_role') > $this->guest_role) ? $this->session->userdata('user_role') : $this->guest_role;
        }
        else
        {
            $user = $this->get_user_by_id($userid);
            
            return $user ? $user->role_id : FALSE;
        }
        return FALSE;
    }
    
    /**
    * Get a users details based on their user ID
    * 
    * @param mixed $id
    */
    public function get_user_by_id($userid = '')
    {    
        return $this->get_user($userid, 'id');
    }
    
    /**
    * Get a users details based on their email address
    * 
    * @param mixed $email
    */
    public function get_user_by_email($email = '')
    {
        return $this->get_user($email, 'email');
    }
    
    /**
    * Get a users details based on their username
    * 
    * @param mixed $username
    */
    public function get_user_by_username($username = '')
    {
        return $this->get_user($username, 'username');   
    }
    
    /**
    * Get user meta
    * 
    * @param mixed $key
    * @param mixed $value
    */
    public function get_user_meta($key = '')
    {
        $this->db->where('key', $key);
        
        $meta = $this->db->get($this->_tables['user_meta']);
        
        return ($meta->num_rows() == 1) ? $meta->row('value') : FALSE;
    }
    
    /**
    * Get all user meta for a specific user or if no
    * userid is supplied, for the current user.
    * 
    */
    public function get_all_user_meta($userid = 0)
    {
        if ($userid == 0)
        {
            $userid = $this->user_id;
        }
        
        // Get all user meta for the current user
        $result = $this->db->select('key, value')->where('user_id', $userid)->get($this->_tables['user_meta'])->result_array();
        
        $metas = '';
        foreach ($result AS $key => $val)
        {
        }
        
        //die(print_r($metas));
    }
    
    /**
    * Gets info about the currently logged in user
    * 
    * If a user is logged in, a data object is returned.
    * If no user is logged in, FALSE is returned.
    * 
    */
    public function get_this_user()
    {
        return $this->get_user_by_id($this->user_id);
    }
    
    /**
    * Restrict a particular function or controller to particular user ID's
    *
    * @param mixed $allowed_roles
    * @param mixed $redirect_to
    */
    public function restrict($needles = '', $restrict = "roleid", $redirect_to = '')
    {
        $redirect_to = ($redirect_to == '') ? $this->config->item('base_url') : $redirect_to;

        // If we are restricting to role ID's
        if ( $restrict == "roleid" )
        {
            $criteria = $this->get_role();
        }
        // Are we restricting to usernames
        elseif ( $restrict == 'username' )
        {
            $user = $this->get_this_user();
            $criteria = $user->username;
        }

        // If we have allowed roles defined
        if (!empty($needles))
        {
            // If multiple needles are supplied as an array
            if (is_array($needles))
            {
                // If the role is in the allowed roles list
                if (in_array($criteria, $needles))
                {
                    return TRUE;
                }
                else
                {
                    redirect($redirect_to);
                }   
            }
            // If only a single value is provided
            else
            {
                if ($criteria == $needles)
                {
                    return TRUE;
                }
                else
                {
                    redirect($redirect_to);
                }
            }
        }
        else
        {
            show_error($this->lang->line('access_denied'));
        }
    }
    
    /**
    * Inserts a new user into the database. This function expects 
    * the incoming data to match the field names defined in the 
    * users table.
    * 
    * @param mixed $member_data
    */
    public function add_user($user_data = array())
    {
        if (isset($user_data['password']))
        {
            $user_data['password'] = $this->hash_password($user_data['password']);
        }

        return ($this->db->insert($this->_tables['users'], $user_data)) ? $this->db->insert_id() : FALSE;
    }
    
    /**
    * Updates a user in the database and returns true or false
    * depending on whether or not the update was successful.
    * 
    * @param string $user_data
    * @return mixed
    */
    public function update_user($user_data = array())
    {
        $this->db->where('id', $user_data['id']);

        if (isset($user_data['password']))
        {
            $user_data['password'] = $this->hash_password($user_data['password']);
        }

        return ($this->db->update($this->_tables['users'], $user_data)) ? TRUE : FALSE;
    }
    
    /**
    * Change a user password
    * 
    * @param mixed $old_password
    * @param mixed $new_password
    */
    public function update_password($username = '', $old_password = '', $new_password = '')
    {
        // Make sure we have an old and new password
        if ($username == '' OR $old_password == '' OR $new_password == '')
        {
            return FALSE;
        }
        
        // If the user exists
        if ( $user = $this->get_user_by_username($username) )
        {
            $arr['id']       = $user->id;
            $arr['password'] = $this->hash_password($new_password);

            $this->update_user($arr);
            
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
    
    /**
    * Delete a user from the database
    * 
    * @param mixed $userid
    */
    public function delete_user($userid = '')
    {
        $this->db->where('id', $userid);

        $this->db->delete($this->_tables['users']);

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }
    
    /**
    * Check an activation code sent to confirm a users email
    * If found, then activate the user.
    * 
    * @param mixed $id
    * @param mixed $authkey
    */
    public function activate_user($userid = '', $authkey = '')
    {
        
        if ($userid == '' OR $authkey == '')
        {
            return FALSE;
        }
        
        // Fetch the user based on the activation code supplied
        $user = $this->db->where('id', $userid)->where('activation_code', $authkey)->get($this->_tables['users']);

        // If the user was found
        if ($user->num_rows() == 1)
        {
            // Activate the user
            $arr['activation_code'] = '';
            $arr['id'] = $user->id;
            $arr['status'] = 'active';
            
            // Update the user
            $update =  $this->update_user($arr);
            
            // If user successfully updated
            if ($update)
            {
                // Are we sending an email after activating?
                if ( $this->config->item('send_email_after_activation') )
                {
                    $body = $this->load->view('auth/emails/account_activated', '', TRUE);

                    $this->_send_email($user->email, $this->lang->line('user_activated_subject'), $body);
                }
                
                // Store the success message
                $this->message_array[] = $this->lang->line('account_activated');
                return TRUE;
            }
            else
            {
                return FALSE;
            }            
        }
        else
        {
            $this->error_array[] = $this->lang->line('account_activation_error');
            return FALSE;
        }
    }
    
    /**
    * Fetch all users from the database
    * Limit and offsets can be supplied
    * 
    * @param mixed $limit
    * @param mixed $offset
    */
    public function get_users($limit = '', $offset = 0)
    {
        if ($limit != '')
        {
            $this->db->limit($limit, $offset);
        }
        
        $this->db->select('

            '. $this->_tables['users'] .'.*, 
            '. $this->_tables['user_meta'] .'.*, 
            '. $this->_tables['roles'] .'.name AS role_name, 
            '. $this->_tables['roles'] .'.description AS role_description');
        
        $this->db->join($this->_tables['user_meta'], $this->_tables['user_meta'].'.user_id = '.$this->_tables['users'].'.id');
        $this->db->join($this->_tables['roles'], $this->_tables['roles'].'.actual_role_id = '.$this->_tables['users'].'.role_id');
        
        return $this->db->get($this->_tables['users']);
    }
    
    /**
    * Count all users in the database
    * 
    */
    public function count_users()
    {
        return $this->db->count_all($this->_tables['users']);
    }
    
    /**
    * Create a password hash
    * 
    * @param int $password
    * @access private
    * @return string
    */
    protected function hash_password($password = '')
    {
        $this->load->helper('security');

        return do_hash($password);
    }
    
    /**
    * Generates a random password based on the length defined
    * in the auth config file.
    * 
    * This function returns an object with the values hashed
    * and unhashed.
    * 
    * @param mixed $length
    */
    public function generate_password($length = '')
    {
        $this->load->helper('string');
        
        $length = ($length != '') ? $length : $this->config->item('password_length', 'auth');
        
        return random_string('alnum', $length);
        
    }
    
    /**
    * Get a user based on identity criteria. This is the heart of the auth
    * library. Nearly every function relies on this function.
    * 
    * @param mixed $needle
    * @param mixed $haystack
    */
    public function get_user($needle = '', $haystack = '')
    {
        $this->db->select(''. $this->_tables['users'] .'.*, '. $this->_tables['roles'] .'.name AS role_name, '. $this->_tables['roles'] .'.description AS role_description');
        
        $this->db->where($this->_tables['users'].".".$haystack, $needle);
        
        // Join the user roles 
        $this->db->join($this->_tables['roles'], $this->_tables['roles'].'.actual_role_id = '.$this->_tables['users'].'.role_id');

        $user = $this->db->get($this->_tables['users']);
        
        return ($user->num_rows() == 1) ? $user->row() : FALSE;
    }
    
    /**
    * Gets errors from errors array and wraps them in delimiters
    * Parts of this function are using bits of code from the
    * Codeigniter form_validation library function error_string.
    *
    */
    public function get_errors($prefix = '', $suffix = '')
    {
        if (count($this->error_array) === 0)
        {
            return '';
        }

        if ($prefix == '')
        {
            $prefix = $this->config->item('error_prefix', 'auth');
        }

        if ($suffix == '')
        {
            $suffix = $this->config->item('error_suffix', 'auth');
        }

        $str = '';
        foreach ($this->error_array as $val)
        {
            if ($val != '')
            {
                $str .= $prefix.$val.$suffix."\n";
            }
        }

        return $str;
    }

    /**
    * Gets message(s) from messages array and wraps them in delimiters
    * Parts of this function are using bits of code from the
    * Codeigniter form_validation library function error_string.
    *
    */
    public function get_messages($prefix = '', $suffix = '')
    {
        if (count($this->messages_array) === 0)
        {
            return '';
        }

        if ($prefix == '')
        {
            $prefix = $this->config->item('message_prefix', 'wolfauth');
        }

        if ($suffix == '')
        {
            $suffix = $this->config->item('message_suffix', 'wolfauth');
        }

        $str = '';
        foreach ($this->messages_array as $val)
        {
            if ($val != '')
            {
                $str .= $prefix.$val.$suffix."\n";
            }
        }

        return $str;
    }

    /**
    * Sets a remember me cookie
    *
    * @param mixed $userid
    */
    private function set_remember_me($userid)
    {
        $this->load->library('encrypt');

        $token  = md5(uniqid(rand(), TRUE));
        $expiry = $this->config->item('cookie_expiry', 'auth');

        $remember_me = $this->encrypt->encode(serialize(array($userid, $token, $expiry)));

        $cookie = array(
            'name'      => 'wolfauth',
            'value'     => $remember_me,
            'expire'    => $expiry
        );

        // For DB insertion
        $cookie_db_data = array(
            'id' => $userid,
            'remember_me' => $remember_me
        );

        set_cookie($cookie);
        $this->update_user($cookie_db_data);
    }

    /**
    * Checks if a user is remembered or not
    *
    */
    private function do_you_remember_me()
    {
        $this->load->library('encrypt');

        $cookie_data = get_cookie('wolfauth');

        // Cookie Monster: Me want cookie. Me want to know, cookie exist?
        if ($cookie_data)
        {
            // Set up some default empty variables
            $userid = '';
            $token = '';
            $timeout = '';

            // Unencrypt and unserialize the cookie
            $cookie_data = $this->encrypt->encode(unserialize($cookie_data));

            // If we have cookie data
            if ( !empty($cookie_data) )
            {
                // Make sure we have 3 values in our cookie array
                if ( count($cookie_data) == 3 )
                {
                    // Create variables from array values
                    list($userid, $token, $expiry) = $cookie_data;
                }
            }

            // Cookie Monster: Me not eat, EXPIRED COOKIEEEE!
            if ( (int) $expiry < time() )
            {
                delete_cookie('wolfauth');
                return FALSE;
            }

            // Make sure the user exists by fetching info by their ID
            $data = $this->get_user_by_id($userid);

            // If the user obviously exists
            if ($data)
            {
                $this->force_login($data->username);
                $this->set_remember_me($userid);

                return TRUE;
            }

        }

        // Cookie Monster: ME NOT FIND COOKIE! ME WANT COOOKIEEE!!!
        return FALSE;
    }

    /**
    * Sends an email
    *
    * @param mixed $to
    * @param mixed $subject
    * @param mixed $body
    */
    private function _send_email($to, $subject, $body)
    {
        // Load the email library
        $this->load->library('email');

        // Email behind the scenes settings like character sets and mailtypes
        $config['mailtype']  = $this->config->item('email_format', 'wolfauth');
        $config['charset']   = $this->config->item('email_charset', 'wolfauth');
        $config['wordwrap']  = $this->config->item('email_wordwrap', 'wolfauth');
        $config['useragent'] = $this->config->item('email_useragent', 'wolfauth');

        // Set up our email settings
        $this->email->initialize($config);

        $from_address  = $this->config->item('email_from_address','wolfauth');
        $from_name     = $this->config->item('email_from_name','wolfauth');

        // Set the email parameters
        $this->email->from($from_address, $from_name);
        $this->email->to($to);
        $this->email->subject($subject);
        $this->email->message($body);

        // Some helpful debugging methods
        log_message('debug','Sending email with subject: '.$subject.' to :'.$to);
        log_message('debug','Email body contents:'.$body);

        // Return the result
        return $this->email->send();
    }
    
}