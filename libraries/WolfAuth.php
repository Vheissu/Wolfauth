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
    
    private $CI;
    
    protected $guest_role;
    protected $admin_roles;
    protected $identity_criteria;
    
    protected $user_id;
    protected $role_id;
    
    /**
    * Constructor function
    * 
    */
    public function __construct()
    {
        $this->CI =& get_instance();
        
        $this->CI->database();
        $this->CI->load->config('wolf_auth');
        $this->CI->load->library('session');
        $this->CI->load->library('email');
        $this->CI->load->model('wolfauth_model');
        $this->CI->load->helper('cookie');
        
        // Set some default role stuff
        $this->guest_role        = $this->CI->config->item('guest_role');
        $this->admin_roles       = $this->CI->config->item('admin_roles');
        $this->identity_criteria = $this->CI->config->item('identity_criteria');
        
        // Set some important IDs
        $this->role_id = $this->CI->session->userdata('role_id');
        
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
    * 
    * @param mixed $userid
    */
    public function is_user($userid = 0)
    {
        $role_id = $this->get_role($userid);
        
        return ($role_id > 0) ? TRUE : FALSE;
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
            if ( !$this->role_id >= 0 )
            {
                return $this->guest_role;
            }
            // We have a logged in role to return!
            else
            {
                return $this->role_id; 
            }   
        }
        else
        {
            // Fetch the user ID of the specific user supplied to this function
            $user = $this->CI->wolfauth_model->get_user_by_id($userid);
            
            // if we found the user
            if ($user)
            {
                return $user->row('role_id');
            }
            
        }
        
        // Looks like we're doomed
        // We should never have arrive here
        return FALSE;
        
    }
    
    /**
    * Log a user in to the site
    * 
    * @param mixed $criteria
    * @param mixed $password
    */
    public function login($needle = '', $password = '')
    {
        if ( $needle == '' OR $password = '' )
        {
            return FALSE;
        }
        
        // Fetch user information
        $user = $this->CI->wolfauth_model->get_user($needle, $this->identity_criteria);
        
        // If we have a user
        if ($user)
        {
            // If passwords match
            if ($this->CI->wolfauth_model->hash_password($password) == $user->row('password'))
            {
                $role_id = $user->row('role_id');
                $user_id = $user->row('id');
                
                $this->CI->session->set_userdata(array(
                    'user_id'   => $user_id,
                    'role_id'   => $role_id,
                    'email'     => $member->row('email')
                ));
                
                if ($this->CI->input->post('remember_me') == 'yes')
                {
                    $this->_set_remember_me($user_id);
                }

                return $user_id;
            }
        }
        
        // All hope is lost...
        return FALSE;
        
    }
    
    /**
    * I wonder what this function does?
    * I think it logs a user out, but I can't
    * be sure. I've had a few drinks.
    * 
    */
    public function logout()
    {
        $user_id = $this->CI->session->userdata('user_id');

        $this->CI->session->sess_destroy();

        $this->CI->load->helper('cookie');
        delete_cookie('rememberme');

        $user_data = array(
            'user_id' => $this->CI->session->userdata('user_id'),
            'remember_me' => ''
        );

        $this->CI->wolfauth_model->update_user($user_data);
    }
    
    /**
    * Sets a remember me cookie
    * 
    * @param mixed $userid
    */
    private function set_remember_me($userid)
    {
        $this->CI->load->library('encrypt');

        $token  = md5(uniqid(rand(), TRUE));
        $expiry = 60 * 60 * 24 * 7; // One week

        $remember_me = $this->CI->encrypt->encode($userid.':'.$token.':'.(time() + $expiry));

        $cookie = array(
            'name'      => 'rememberme',
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
        $this->CI->load->library('encrypt');

        $cookie_data = get_cookie('rememberme');
        
        // The cookie exist?
        if($cookie_data)
        {
            $userid = '';
            $token = '';
            $timeout = '';

            $cookie_data = $this->CI->encrypt->decode($cookie_data);
            
            if (strpos($cookie_data, ':') !== FALSE)
            {
                $cookie_data = explode(':', $cookie_data);
                
                if (count($cookie_data) == 3)
                {
                    list($userid, $token, $timeout) = $cookie_data;
                }
            }

            if ((int) $timeout < time())
            {
                return FALSE;
            }
            
            $data = $this->CI->wolfauth_model->get_user_by_id($userid);

            if ($data)
            {
                $this->CI->session->set_userdata(array(
                    'user_id'     => $user_id,
                    'role_id'    => $data->row('role_id')
                ));

                $this->set_remember_me($user_id);

                return TRUE;
            }

            delete_cookie('rememberme');
        }

        return FALSE;
    }
    
}