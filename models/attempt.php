<?php
  
class Attempt extends DataMapper {
    
    public $table = "login_attempts";
    
    public function __construct($id = null)
    {
        parent::__construct($id);
    }
    
} 