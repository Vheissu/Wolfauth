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
    
    protected $error_array = array();
    
    /**
    * Constructor function
    * 
    */
    public function __construct()
    {
        $this->CI =& get_instance();
        
        $this->CI->load->database();
        $this->CI->load->config('wolfauth', TRUE);
        $this->CI->lang->load('wolfauth');
        $this->CI->load->library('session');
        $this->CI->load->library('email');
        $this->CI->load->model('wolfauth_model');
        $this->CI->load->helper('cookie');
        
        // Set some default role stuff
        $this->guest_role        = $this->CI->config->item('guest_role', 'wolfauth');
        $this->admin_roles       = $this->CI->config->item('admin_roles', 'wolfauth');
        $this->identity_criteria = $this->CI->config->item('identity_criteria', 'wolfauth');
        
        // Do you remember meeee?
        $this->do_you_remember_me(); 
        
    }
    
    /**
    * Is the currently logged in user or a particular an admin?
    * Uses the array of admin ID's in the wolf_auth config file.
	*/
    public function is_admin($userid = 0)
    {
    	$role_id = $this->get_role($userid);
        
        return (in_array($role_id, $this->admin_roles)) ? TRUE : FALSE;
    }
    
    /**
    * Does the user have basic logged in user rights?
    * Because a role of 0 means not logged in, anything
    * above 1 means a user is a user regardless of role.
    * 
    * @param mixed $userid
    */
    public function is_user($userid = 0)
    {
        $role_id = $this->get_role($userid);
        
        return ($role_id > 0) ? TRUE : FALSE;
    }
    
    /**
    * Is a user activated or not?
    * 
    */
    public function is_activated($userid = 0)
    {
        
    }
    
    /**
    * Fetch the access role of a particular user
    * or from the currently logged in user.
    * 
    * @param mixed $userid
    */
    public function get_role($userid = 0)
    {
        // No ID supplied to this function?
        // Get the role of the current user
        // regardless of being logged in or
        // not.
        if ( $userid == 0 )
        {
            // If we don't have a user ID set, then return the guest role ID
            if ( !$this->CI->session->userdata('user_role') >= 0 )
            {
                return $this->guest_role;
            }
            // We have a logged in role to return!
            else
            {
                return $this->CI->session->userdata('user_role'); 
            }   
        }
        else
        {
            // Fetch the user ID of the specific user supplied to this function
            $user = $this->CI->wolfauth_model->get_user_by_id($userid);
            
            // if we found the user
            if ($user)
            {
                return $user->role_id;
            }
            
        }
        
        // Looks like we're doomed
        // We should never have arrived here
        return FALSE;
    }
    
    /**
    * Fetches a user by ID
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
    * and then return an object of user data
    * 
    */
    public function get_this_user()
    {        
        return $this->get_user_by_id($this->CI->session->userdata('user_id'));
    }
    
    /**
    * Activate a usser based on the provided auth key for
    * activating a user as defined in the users table.
    * 
    * @param mixed $needle
    * @param mixed $authkey
    */
    public function activate_user($needle = '', $authkey = '')
    {
        //$this->CI->wolfauth_model;
    }
    
    /**
    * Log a user in to the site. Also allows you to redirect
    * somewhere if the user is successfully logged in.
    * 
    * @param mixed $criteria
    * @param mixed $password
    * @param bool  $redirect
    */
    public function login($needle = '', $password = '')
    {
        
        // Make sure no one fools us with whitespace
        $needle   = trim($needle);
        $password = trim($password);
        
        if ( $needle == '' OR $password = '' )
        {
            $this->error_array[] = $this->CI->lang->line('missing_login_credentials');
            return FALSE;
        }
        
        // Looks like we are already logged in
        if ( $this->CI->session->userdata('user_id') > 0 OR $this->CI->session->userdata('user_role') > 0 )
        {
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
                // Get the user ID from the database
                $user_id = $user->id;
                
                // Creates a logged in session
                $this->force_login($needle);
                
                if ($this->CI->input->post('remember_me') == 'yes')
                {
                    $this->_set_remember_me($user_id);
                }

                return TRUE;
            }
            else
            {
                $this->error_array[] = $this->CI->lang->line('account_not_found');
                
                return FALSE;
            }
        }
        
        $this->error_array[] = $this->CI->lang->line('account_not_found');
        
        // All hope is lost...
        return FALSE;
        
    }
    
    /**
    * I wonder what this function does?
    * I think it logs a user out, but I can't
    * be sure. I've had a few drinks.
    * 
    */
    public function logout($redirect = '')
    {
        $user_id = $this->CI->session->userdata('user_id');

        $this->CI->session->sess_destroy();

        $this->CI->load->helper('cookie');
        delete_cookie('wolfauth');

        $user_data = array(
            'id' => $user_id,
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
        }
        
        return FALSE;
    }
    
    /**
    * Generates a random password
    * 
    */
    public function generate_password($length = '')
    {
        return $this->CI->wolfauth_model->generate_password($length);
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
    * Sets a remember me cookie
    * 
    * @param mixed $userid
    */
    private function set_remember_me($userid)
    {
        $this->CI->load->helper('cookie');
        $this->CI->load->library('encrypt');

        $token  = md5(uniqid(rand(), TRUE));
        $expiry = 60 * 60 * 24 * 7; // One week

        $remember_me = $this->CI->encrypt->encode(serialize(array($userid, $token, $expiry)));

        $cookie = array(
            'name'      => 'wolfauth',
            'value'     => $remember_me,
            'expire'    => $expiry
        );

        set_cookie($cookie);
        $this->CI->wolfauth_model->update_user(array('id'=>$userid, 'remember_me'=>$remember_me));
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
        if($cookie_data)
        {
            // Set up some default empty variables
            $userid = '';
            $token = '';
            $timeout = '';
            
            // Unencrypt and unserialize the cookie
            $cookie_data = $this->CI->encrypt->encode(unserialize($cookie_data));
            
            // If we have cookie data
            if (!empty($cookie_data))
            {   
                // Make sure we have 3 values in our cookie array
                if (count($cookie_data) == 3)
                {
                    // Create variables from array values
                    list($userid, $token, $expiry) = $cookie_data;
                }
            }
            
            // Cookie Monster: Me not eat EXPIRED COOKIEEEE!
            if ((int) $expiry < time())
            {
                return FALSE;
            }
            
            // Make sure the user exists by fetching info by their ID
            $data = $this->CI->wolfauth_model->get_user_by_id($userid);
            
            // If the user obviously exists
            if ($data)
            {
                $this->force_login($data->username);
                $this->set_remember_me($userid);

                return TRUE;
            }

            delete_cookie('wolfauth');
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
        $this->CI->email->from( $from_address, $from_name);
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