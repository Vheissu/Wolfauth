<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {
    
    protected $__table;
    protected $__method;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->__table   = "users";
    }
	
    /*
     * Check whether not a user is unique based on properties passed in
     *
     * @param array $properties
     */
    public function is_unique($properties)
    {
        foreach ($properties as $prop => $value)
        {
            $user = $this->get_user($prop, $value);

            if ( !empty($user) )
            {
                return false;
                break;
            }
        }

        return true;
    }
    
    /**
    * Get a user from the database
    * 
    * @param mixed $where
    * @param mixed $value
    */
    public function get_user($where, $value)
    {        
        $user = $this->db->where($where, $value)->get($this->__table);
        
        return ($user->num_rows() > 0) ? $user->row() : false;
        $user->free_result();
    }
    
    /**
    * Get role information for a user ID
    * 
    * @param mixed $userid
    */
    public function get_role($userid)
    {
        $roles = $this->db->
                          select('users_to_roles.role_id, roles.name, roles.slug, roles.description')
                          ->where('users_to_roles.user_id', $userid)
                          ->join('roles', 'roles.id = users_to_roles.role_id')
                          ->get('users_to_roles');
        
        return ($roles->num_rows() > 0) ? $roles->row() : false;
        $roles->free_result();   
    }
    
    /**
    * Get user meta from the database and unserialize it.
    * 
    * @param mixed $userid
    * @return mixed
    */
    public function get_user_meta($userid)
    {
        $user = $this->get_user('id', $userid);
        $meta = @unserialize($user->profile_fields);
        
        settype($meta, "object");
        
        return ($meta) ? $meta : false;
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
                $query = $this->db->insert($this->__table, $data);
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
            $query = $this->db->where('id', $id)->delete($this->__table);
            return ($query) ? $true : false;
            $query->free_result();
        }
        else
        {
            return false;
        }
    }
    
}