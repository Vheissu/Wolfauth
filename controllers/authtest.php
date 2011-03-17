<?php
    
class Authtest extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('auth');
    }
    
    public function index()
    {
        
    }
    
    public function login()
    {
        // If data was posted
        if ( $this->input->post() )
        {
            // Posted details
            $username = $this->input->post('username');
            $password = $this->input->post('username');
            
            // Remember me?
            if ( $this->input->post('remember') == TRUE )
            {
                $remember = $this->input->post('remember');
            }
            else
            {
                $remember = false;
            }
            
            // Array of extra information you can pass to your functions
            $config = array();
            
            // Log on in
            $this->auth->login($username, $password, $remember, $config);
        }
           
    }
    
}