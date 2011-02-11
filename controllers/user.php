<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();
        
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('auth');
        $this->load->model('auth');
    }
    
    public function index()
    {
        redirect('user/login');
    }
    
    public function restricted_function()
    {
        // Restrict this function to user roles 3, 4 and 5. If a user isn't any of those roles, send them to Google.
        restrict(array(3,4,5), 'http://www.google.com');
    }
    
    public function login()
    {
        // Posted variables
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        
        // Login using the helper function
        if ( login($username, $password) )
        {
            // We are logged in baby
        }
        else
        {
            $this->load->view('wolfauth/login_form');
        }
        
    }
    
    public function generate_password($length = '')
    {
        echo generate_password($length);
    }
    
}   