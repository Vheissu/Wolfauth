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
    * Generate a secure password
    * 
    * @param int $password
    * @return string
    */
    public function generate_password($password = '')
    {
        $this->load->helper('security');

        if ($password == '')
        {
            $password = rand();
        }

        return do_hash($password);
    }
    
}