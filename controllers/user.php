<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->library('wolfauth');
    }
    
    public function index()
    {
        redirect('user/login');
    }
    
    public function login()
    {
        
    }
    
}   