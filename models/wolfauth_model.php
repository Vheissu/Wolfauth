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
    * Get user meta
    * 
    * @param mixed $key
    * @param mixed $value
    */
    public function get_user_meta($key = '')
    {
        //$this->db->join($this->_tables['user_meta'], $this->_tables['user_meta'].'.user_id = '.$this->_tables['users'].'.id');
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