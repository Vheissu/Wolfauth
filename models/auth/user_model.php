<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {
    
    protected $table;
    protected $method;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->load->config('auth');
        $this->load->database();
        
        $this->table   = $this->config->item('users_table');
        $this->method  = $this->config->item('login_method');
    }
    
    /**
    * Get a user from the database by user ID
    * 
    * @param mixed $id
    */
    public function get_user($value)
    {        
        $user     = $this->db->where($this->method, $value)->get($this->table);
        
        return ($user->num_rows() > 0) ? $user->row() : false;
    }
    
}