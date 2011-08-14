<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Role extends DataMapper {
    
    public $has_many = array("user", "group", "permission");
    public $local_time = TRUE;
    
    public function __construct($id = NULL)
    {
        parent::__construct($id);
    }
    
}
