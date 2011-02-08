<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();
        
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('wolfauth');
        
        $this->load->library('wolfauth');
    }
    
    public function index()
    {
        redirect('user/login');
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
        echo $this->wolfauth->generate_password($length);
    }
    
}   