<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* @name WolfAuth
* @category Library
* @package WolfAuth
* @author Dwayne Charrington
* @copyright 2011
* @link http://ilikekillnerds.com
*/

class WolfAuth {
    
    protected $CI;
    
    protected $guest_role;
    protected $admin_roles;
    protected $identity_criteria;

    protected $user_id;
    
    // An array of errors
    protected $error_array = array();
    
    // An array of messages
    protected $message_array = array();
    
    /**
    * Constructor function
    * 
    */
    public function __construct()
    {
        $this->CI =& get_instance();
        
        /**
        * We load all of this in-case the end user doesn't autoload
        * anything.
        * 
        * @var WolfAuth
        */
        $this->CI->load->database();
        $this->CI->load->config('wolfauth', TRUE);
        $this->CI->lang->load('wolfauth');
        $this->CI->lang->load('wolfauth_email');
        $this->CI->load->library('session');
        $this->CI->load->library('email');
        $this->CI->load->model('wolfauth_model');
        $this->CI->load->helper('cookie');
        
        // Sets some defaults for roles, etc.
        $this->set_defaults();
        
        // Check if we remember this user and if we do, log them in
        $this->do_you_remember_me(); 
    }
    
    /**
    * Set up some default values for WolfAuth like session data and user roles
    * 
    */
    protected function set_defaults()
    {
        // Set the default guest role which by default is '0'
        $this->guest_role        = $this->CI->config->item('guest_role', 'wolfauth');
        
        // Set the default admin role(s) which by default are '4' and '5'
        $this->admin_roles       = $this->CI->config->item('admin_roles', 'wolfauth');
        
        // Set the default identity criteria (default is username)
        $this->identity_criteria = $this->CI->config->item('identity_criteria', 'wolfauth');
        
        // Store the current user ID in this class for easier reference
        $this->user_id = $this->CI->session->userdata('user_id');

    }
    
    /**
    * Is the currently logged in user or a particular an admin?
    * Uses the array of admin ID's in the wolf_auth config file.
	*/
    public function is_admin($userid = 0)
    {   
        // Conditional to return TRUE or FALSE if the user has an admin ID
        return (in_array($this->get_role($userid), $this->admin_roles)) ? TRUE : FALSE;
    }
    
    /**
    * Is the current user a user or are they merely a guest?
    * A value of '0' means that they are not logged in and are
    * treated as guests. A value of 1 or higher means that they
    * are users regardless of priveleges.
    * 
    * @param mixed $userid
    */
    public function is_user($userid = 0)
    {
        // If user role is greater thsn 0, return true        
        return ($this->get_role($userid) > 0) ? TRUE : FALSE;
    }
    
    /**
    * Is there a logged in user
    * 
    */
    public function is_logged_in()
    {
        return $this->user_id ? TRUE : FALSE;
    }
    
    /**
    * Fetches the user role for the currently logged in user
    * if no userid is supplied, else if a user ID is supplied
    * that particular user's role ID will be returned.
    * 
    * @param mixed $userid
    */
    public function get_role($userid = 0)
    {
        // Get the currently logged in users role ID 
        if ( $userid == 0 )
        {
            // Return the guest role if no valid user or return the user ID if a valid user is logged in 
            return ($this->CI->session->userdata('user_role') >= 0) ? $this->CI->session->userdata('user_role') : $this->guest_role;
        }
        else
        {
            // Fetch the user ID of the specific user supplied to this function
            $user = $this->CI->wolfauth_model->get_user_by_id($userid);
            
            // If we found the user
            if ($user)
            {
                return $user->role_id;
            }
            else
            {
                return FALSE;
            }   
        }
        
        // Looks like we're doomed
        // We should never have arrived here
        return FALSE;
    }
    
    /**
    * Fetches a user by their user ID
    * 
    * @param mixed $userid
    */
    public function get_user_by_id($userid = 0)
    {
        $user = $this->CI->wolfauth_model->get_user_by_id($userid);   
        return ($user) ? $user : FALSE;
    }
    
    /**
    * Get this user will get the currently logged in user
    * and then return an object of user data for them.
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
    public function restrict($needles = array(), $restrict = "roleid", $redirect_to = '')
    {
        $redirect_to = ($redirect_to == '') ? $this->CI->config->item('base_url') : $redirect_to;
        
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
        else
        {
            show_error($this->CI->lang->line('access_denied'));
        }
    }
    
    /**
    * Activate a user based on the provided auth key for
    * activating a user as defined in the users table.
    * 
    * @param mixed $userid
    * @param mixed $authkey
    */
    public function activate_user($userid = '', $authkey = '')
    {
        // Activate the user
        $action = $this->CI->wolfauth_model->activate_user($userid, $authkey);

        // If the user was activated
        if ($action)
        {
            // Are we sending an email after activating?
            if ( $this->CI->config->item('send_email_after_activation') )
            {
                $user = $this->get_user_by_id($userid);
                
                $body = $this->load->view('wolfauth/emails/account_activated', '', TRUE);
                
                $this->_send_email($user->email, $this->CI->lang->line('user_activated_subject'), $body);
            }
            else
            {
                $this->message_array[] = $this->CI->lang->line('account_activated');
            }
        }
        else
        {
            $this->error_array[] = $this->CI->lang->line('account_activation_error');
        }

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
        if ( $needle == '' OR $password = '' )
        {
            $this->error_array[] = $this->CI->lang->line('missing_login_credentials');
            return FALSE;
        }
        
        // Looks like we are already logged in
        if ( $this->user_id > 0 OR $this->CI->session->userdata('user_role') > 0 )
        {
            
            if ($redirect != '')
            {
                redirect($redirect);
            }
            
            return TRUE;
        }
        
        // Fetch user information
        $user = $this->CI->wolfauth_model->get_user($needle, $this->identity_criteria);
        
        // If we have a user
        if ($user)
        {
            // If passwords match
            if ($this->CI->wolfauth_model->hash_password($password) == $user->password)
            {
                $user_id = $user->id;
                $this->force_login($needle);
                
                if ($this->CI->input->post('remember_me') == 'yes')
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
                $this->error_array[] = $this->CI->lang->line('password_incorrect');
                
                return FALSE;
            }
        }
        
        $this->error_array[] = $this->CI->lang->line('account_not_found');
        
        // All hope is lost...
        return FALSE;
        
    }
    
    /**
    * I wonder what this function does? I think it logs a user out, but I 
    * can't be sure. I've had a few drinks.
    * 
    */
    public function logout($redirect = '')
    {
        $this->CI->session->sess_destroy();

        $this->CI->load->helper('cookie');
        delete_cookie('wolfauth');

        $user_data = array(
            'id' => $this->user_id,
            'remember_me' => ''
        );
        
        // Remove remember me data, yo.
        $this->CI->wolfauth_model->update_user($user_data);
        
        // Default redirect
        if (!$redirect)
        {
            $this->CI->load->helper('url');
            $redirect = base_url();
        }
        
        // Redirect the user to oblivion
        redirect($redirect); 
    }
    
    /**
    * Forces a user to be logged in via the criteria set in the config file.
    * Can log in a user without needing a password or anything of that kind!
    * 
    * @param mixed $needle
    */
    public function force_login($needle = '')
    {
        if ( $needle == '' )
        {
            return FALSE;
        }
        
        // Get the user to make sure they exist
        $user = $this->CI->wolfauth_model->get_user($needle, $this->identity_criteria);
        
        if ( $user )
        {
            $this->CI->session->set_userdata(array(
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
    * Check if a username already exists in the database and if it does
    * return true.
    * 
    * @param mixed $username
    */
    public function username_exists($username)
    {
        return $this->wolfauth_model->get_user_by_username($username) ? TRUE : FALSE;
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
            $prefix = $this->CI->config->item('error_prefix', 'wolfauth');
        }
        
        if ($suffix == '')
        {
            $suffix = $this->CI->config->item('error_suffix', 'wolfauth');
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
            $prefix = $this->CI->config->item('message_prefix', 'wolfauth');
        }
        
        if ($suffix == '')
        {
            $suffix = $this->CI->config->item('message_suffix', 'wolfauth');
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
        $this->CI->load->helper('cookie');
        $this->CI->load->library('encrypt');

        $token  = md5(uniqid(rand(), TRUE));
        $expiry = $this->CI->config->item('cookie_expiry', 'wolfauth');

        $remember_me = $this->CI->encrypt->encode(serialize(array($userid, $token, $expiry)));

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
        $this->CI->wolfauth_model->update_user($cookie_db_data);
    }
    
    /**
    * Checks if a user is remembered or not
    * 
    */
    private function do_you_remember_me()
    {
        $this->CI->load->helper('cookie');
        $this->CI->load->library('encrypt');

        $cookie_data = get_cookie('wolfauth');
        
        // Cookie Monster: Me want cookie. Me want to know, cookie exist?
        if ( $cookie_data )
        {
            // Set up some default empty variables
            $userid = '';
            $token = '';
            $timeout = '';
            
            // Unencrypt and unserialize the cookie
            $cookie_data = $this->CI->encrypt->encode(unserialize($cookie_data));
            
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
            $data = $this->CI->wolfauth_model->get_user_by_id($userid);
            
            // If the user obviously exists
            if ( $data )
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
        $this->CI->load->library('email');
        
        // Email behind the scenes settings like character sets and mailtypes
        $config['mailtype']  = $this->CI->config->item('email_format', 'wolfauth');
        $config['charset']   = $this->CI->config->item('email_charset', 'wolfauth');
        $config['wordwrap']  = $this->CI->config->item('email_wordwrap', 'wolfauth');
        $config['useragent'] = $this->CI->config->item('email_useragent', 'wolfauth');
        
        // Set up our email settings
        $this->CI->email->initialize($config);
        
        $from_address  = $this->CI->config->item('email_from_address','wolfauth');
        $from_name     = $this->CI->config->item('email_from_name','wolfauth');
        
        // Set the email parameters     
        $this->CI->email->from($from_address, $from_name);
        $this->CI->email->to($to);
        $this->CI->email->subject($subject);
        $this->CI->email->message($body);
        
        // Some helpful debugging methods
        log_message('debug','Sending email with subject: '.$subject.' to :'.$to);
        log_message('debug','Email body contents:'.$body);
        
        // Return the result
        return $this->CI->email->send();     
       
    }
    
}
