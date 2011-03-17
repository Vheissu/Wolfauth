<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {
    
    protected $table;
    protected $method;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->load->config('auth');
        
        $this->table   = $this->config->item('users_table');
        $this->method  = $this->config->item('login_method');
    }
    
    /**
    * Get a user from the database
    * 
    * @param mixed $where
    * @param mixed $value
    */
    public function get_user($where, $value)
    {        
        $user = $this->db->where($where, $value)->get($this->table);
        
        return ($user->num_rows() > 0) ? $user->row() : false;
        $user->free_result();
    }
    
    /**
    * Add a new user
    * 
    * @param mixed $data
    */
    public function add_user($data)
    {
        // Make sure data being provided is an array
        if ( is_array($data) AND count($data) > 1 )
        {
            // Make sure we have a username
            if ( isset($data['username']) )
            {
                $query = $this->db->insert($this->table, $data);
                return ($query) ? true : false;
                $query->free_result();
            }
        }
        else
        {
            return false;
        }
    }
    
    /**
    * Edit a users information in the database
    * 
    * @param mixed $data
    */
    public function edit_user($data)
    {
        // Make sure we have an array
        if ( is_array($data) )
        {
            // Make sure we have been supplied a user ID
            if ( isset($data['id']) )
            {
                $query = $this->db->where('id', $data['id'])->update($this->table, $data);
                return ($query) ? true : false;
                $query->free_result();
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    
    /**
    * Delete a user from the database
    * 
    * @param mixed $id
    */
    public function delete_user($id)
    {
        if ( is_int($id) )
        {
            $query = $this->db->where('id', $id)->delete($this->table);
            return ($query) ? $true : false;
            $query->free_result();
        }
        else
        {
            return false;
        }
    }
    
}