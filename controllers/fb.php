<?php
  
class Fb extends MY_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('auth');
        $this->auth->set_driver('auth_facebook');
    }
    
    public function index()
    {
    
    }
    
}
