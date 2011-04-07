<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Facebook_model extends CI_Model {
    
    public function __construct()
    {
        parent::__construct();
        $this->config->load('auth');
    }
    
    /**
    * Get the user from Facebook
    * 
    */
    public function get_user()
    {
        $cookie = $this->get_cookie();
        $user = @json_decode(file_get_contents( 'https://graph.facebook.com/me?access_token=' .    $cookie['access_token']), true);
        return $user;
    }
    
    /**
    * Get the users profile picture from Facebook
    * 
    */
    public function get_picture()
    {
        $cookie = $this->get_cookie();
        $user = @json_decode(file_get_contents( 'https://graph.facebook.com/me/picture?access_token=' .    $cookie['access_token']), true);
        return $user;
    }
    
    /**
    * Get a specific user from Facebook
    * 
    * @param mixed $user_id
    * @return mixed
    */
    function get_specific_user($user_id)
    {
        $cookie = $this->get_cookie();
        $user = @json_decode(file_get_contents( 'https://graph.facebook.com/' . $user_id . '?access_token=' .    $cookie['access_token']), true);
        return $user;        
    }
    
    /**
    * Get Facebook friends (can include or exclude yourself)
    * 
    * @param mixed $include_you
    * @return mixed
    */
    public function get_friends($include_you = TRUE)
    {
        $cookie = $this->get_cookie();
        $friends = @json_decode(file_get_contents('https://graph.facebook.com/me/friends?access_token=' . $cookie['access_token']), true);
        
        if($include_you == TRUE)
            $friends['data'][] = array(    'name'   => 'You', 'id' => $cookie['uid'] );
                
        return $friends['data'];
    }
    
    /**
    * Get Facebook cookie (if it exists)
    * 
    */
    public function get_cookie()
    {
        // Get Facebook config options and typecastttt to an object
        $config = (object)$this->config->item('facebook');
        
        // Get our application ID
        $app_id = $config->api_key;
        
        // Get secret key
        $secret = $config->secret;
        
        // If we have a cookie, parse, etc.
        if ( isset($_COOKIE['fbs_' . $app_id]) )
        {
              $args = array();
              parse_str(trim($_COOKIE['fbs_' . $app_id], '\\"'), $args);
              ksort($args);
              
              $payload = '';
              foreach ($args as $key => $value) 
              {
                    if ($key != 'sig') 
                    {
                      $payload .= $key . '=' . $value;
                    }
              }
              
              if (md5($payload . $secret) != $args['sig']) 
              {
                    return null;
              }
              return $args;
          }
          else
          {
            return null;
          }
    }
    
}