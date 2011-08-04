<?php
  
class Testauth extends MY_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('auth');
    }
    
    public function index()
    {
        
        if (logged_in())
        {
            echo "Logged in!";
        }
        else
        {
            echo "Not logged baby";
        }
    }
    
    public function login()
    {
        $this->auth->login('admin', 'password');
    }
    
    public function add_group()
    {
        $this->auth->add_group('Users', 'Just your standard users group');
    }
    
    public function add_role_group()
    {
        $this->auth->add_role_to_group(1, 4);
    }
    
    public function add_user_group()
    {
        $this->auth->add_user_to_group(1, 2);
    }
    
    public function user_info()
    {
        $info = $this->auth->get_user_info(1);
    }
    
}
