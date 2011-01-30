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
    
    private $_tables;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->load->config('wolf_auth');
        
        // Get the array of tables from the config file
        $this->_tables = $this->config->item('tables');
        
    }
    
    /**
    * Get a user based on identity criteria
    * 
    * @param mixed $needle
    * @param mixed $haystack
    */
    public function get_user($needle = '', $haystack = '')
    {
        $this->db->where($haystack, $needle);

        $user = $this->db->get($this->_tables['users']);
        
        return ($user->num_rows() == 1) ? $user : FALSE;
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
    * Get a users details based on their email address
    * 
    * @param mixed $username
    */
    public function get_user_by_username($username = '')
    {
        return $this->get_user($email, 'username');   
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
    * Inserts a new user into the database
    * This function expects the incoming data to match the field
    * names defined in the users table.
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

        return (!$this->db->update($this->_tables['users'], $user_data)) ? FALSE : TRUE;
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
    * Update a users status from active, inactive or banned.
    * 
    * @param mixed $userid
    * @param mixed $status
    * @return mixed
    */
    public function update_user_status($userid = '', $status = '')
    {
        $data = array(
            'id'     => $userid,
            'status' => $status
        );
        
        return $this->update_user($data);
    }
    
    /**
    * Check an activation code sent to confirm a users email
    * 
    * @param mixed $id
    * @param mixed $authkey
    */
    public function activation_check($userid = '', $authkey = '')
    {
        $this->db->where('id', $userid);
        $this->db->where('activation_code', $authkey);

        $user = $this->db->get($this->_tables['users']);

        if ($user->num_rows() == 1)
        {
            return $user;
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
        
        $this->db->select('users.*, user_meta.*, roles.name AS role_name, roles.description AS role_description');
        
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
    public function hash_password($password = '')
    {
        $this->load->helper('security');

        return do_hash($password);
    }
    
}