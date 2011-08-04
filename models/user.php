<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends DataMapper {
    
    public $has_many = array('question', 'answer', 'group', 'role', 'permission');
    public $has_one  = array('umeta');
    
    public function __construct($id = NULL)
    {
        parent::__construct($id);
    }
    
}