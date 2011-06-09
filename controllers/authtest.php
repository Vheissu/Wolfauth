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
            $password = $this->input->post('password');
            
            // Remember me
            $remember = false;
            
            // Remember me?
            if ( $this->input->post('remember') == 1 )
            {
                $remember = true;
            }

			// Redirecting the user after login? Where? 
			$redirect = false;
			
            // Log on in
            $this->auth->login($username, $password, $remember, $redirect);
        }
           
    }
    
}