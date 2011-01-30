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