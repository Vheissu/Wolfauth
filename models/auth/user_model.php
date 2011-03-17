<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {
    
    protected $_users;
    protected $_usermeta;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->load->config('auth');
        $this->load->database();
        
        $this->table = $this->config->item('users_table');
    }
    
    /**
    * Get a user from the database by ID
    * 
    * @param mixed $id
    */
    public function get_user($id)
    {
        $object = new stdClass;
        
        $user     = $this->db->where('id', $id)->get($this->_users);
        $usermeta = $this->db->where('user_id', $id)->get($this->_usermeta);
        
        if ($user->num_rows() > 0)
        {
            
        }
        
        return ($object) ? $object : false;
    }
    
}