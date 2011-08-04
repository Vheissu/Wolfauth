<?php
  
class Umeta extends DataMapper {
    
    public $table = "umeta";
    public $has_many = array('user');
    
    public function __construct($id = null)
    {
        parent::__construct($id);
    }
    
} 