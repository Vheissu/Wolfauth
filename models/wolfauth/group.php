<?php
  
class Group extends DataMapper {
    
    public $has_many = array('user', 'role', 'permission');
    
    public function __construct($id = NULL)
    {
        parent::__construct($id);
    }
    
}
