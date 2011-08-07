<?php
  
class Fb extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('auth');
        $this->auth->set_driver('facebook');
    }
    
    public function index()
    {
        $this->auth->logged_in();
    }
    
}
