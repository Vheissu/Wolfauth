<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 *
 * @package       WolfAuth
 * @subpackage    Session
 * @author        Dwayne Charrington
 * @copyright     Copyright (c) 2011 Dwayne Charrington.
 * @link          http://ilikekillnerds.com
 */

class Auth_Facebook extends CI_Driver {
    
    protected $fb;
    protected $user;
    
    protected $app_id = '';
    protected $secret = '';
    protected $cookie = true;

    /**
    * Constructor
    * 
    */
    public function __construct() 
    {        
        
        if ( !$this->app_id OR !$this->secret )
        {
            echo 'Visit '.anchor('http://www.facebook.com/developers/createapp.php', 'http://www.facebook.com/developers/createapp.php').' to create your app.'.
            '<br />The config file is located at "system\application\modules\account\config\facebook.php"';
            exit();
        }
        
        // Create the Facebook object
        $this->fb = new Facebook(array('app_id' => $this->app_id, 'secret' => $this->secret, 'cookie' => $this->cookie));
        
        // Check for Facebook session
        if ($this->fb->getSession()) 
        {
            try
            {
                $this->user = $this->fb->api('/me');
            } 
            catch (FacebookApiException $e) 
            {
                error_log($e);
            }
        }                
    }
}
