<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* @name WolfAuth
* @category Model
* @package WolfAuth
* @author Dwayne Charrington
* @copyright 2011
* @link http://ilikekillnerds.com
*/

class WolfAuth_model extends CI_Model {
    
    protected $_tables;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->load->config('wolfauth', TRUE);
        $this->lang->load('wolfauth');
        
        // Get the array of tables from the config file
        $this->_tables = $this->config->item('tables', 'wolfauth');
        
    }
    
    /**
    * Get a user based on identity criteria
    * 
    * @param mixed $needle
    * @param mixed $haystack
    */
    public function get_user($needle = '', $haystack = '')
    {
        $this->db->select(''. $this->_tables['users'] .'.*, '. $this->_tables['roles'] .'.name AS role_name, '. $this->_tables['roles'] .'.description AS role_description');
        
        $this->db->where($haystack, $needle);
        
        // Join the user roles 
        $this->db->join($this->_tables['roles'], $this->_tables['roles'].'.actual_role_id = '.$this->_tables['users'].'.role_id');

        $user = $this->db->get($this->_tables['users']);
        
        return ($user->num_rows() == 1) ? $user->row() : FALSE;
    }
    
    /**
    * Get a users details based on their user ID
    * 
    * @param mixed $id
    */
    public function get_user_by_id($id = '')
    {    
        return $this->get_user($id, 'id');
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
        if ($username = '' OR $old_password = '' OR $new_password = '')
        {
            return FALSE;
        }
        
        // If the user exists
        if ( $user = $this->get_user_by_username($username) )
        {
            $arr['id']       = $user->id;
            $arr['password'] = $this->hash_password($new_password);

            $this->update_user($arr);
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
    public function activation_check($userid = '', $authkey = '')
    {
        $user = $this->db->where('id', $userid)->where('activation_code', $authkey)->get($this->_tables['users']);

        if ( $user->num_rows() == 1 )
        {
            $arr['activation_code'] = '';
            $arr['id'] = $user->id;
            $arr['status'] = 'active';

            return $this->update_user($arr);
        }
        else
        {
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
    * @return string
    */
    protected function hash_password($password = '')
    {
        $this->load->helper('security');

        return do_hash($password);
    }
    
    /**
    * Generates a random password based on the length defined
    * in the WolfAuth config file.
    * 
    * This function returns an object with the values hashed
    * and unhashed.
    * 
    * @param mixed $length
    */
    public function generate_password($length = '')
    {
        $this->load->helper('string');
        
        $length = ($length != '') ? $length : $this->config->item('password_length', 'wolfauth');
        
        return random_string('alnum', $length);
        
    }
    
}