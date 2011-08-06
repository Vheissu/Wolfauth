<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 * 
 * This driver allows user accounts to be created, login users by Facebook
 * and a whole lot more Facebook related goodness.
 *
 * @package       WolfAuth
 * @subpackage    Facebook
 * @author        Dwayne Charrington
 * @copyright     Copyright (c) 2011 Dwayne Charrington.
 * @link          http://ilikekillnerds.com
 * @license       http://www.apache.org/licenses/LICENSE-2.0.html
 */

class Auth_Facebook extends CI_Driver {
    
    protected $auth;
    protected $ci;
    protected $config;

    public function __construct()
    {
        $this->auth = auth_instance();
        $this->ci   = get_instance();
        
        $this->ci->config->load('auth');
        
        $this->config = get_item('facebook');
    }
    
    public function getInfoID($id) 
    {
        try {
            $request = $this->request('/' . $id, 'GET', NULL, false);
        } catch (Exception $e){ $request = $e; }
        
        return $request;    
   }
    
    public function get_access_token()
    {
        $parameter = array('client_id'   => $this->config['facebook.api_key'],
                        'client_secret' => $this->config['facebook.api_secret'],
                        'grant_type'    => 'client_credentials');
        try{
            $request = $this->request('/oauth/access_token', 'GET', $parameter, true, false);
            parse_str($request);
        } catch (Exception $e){ die($e); }
            
        $request = $request ? $access_token : NULL;    
        return $request;                         
    }
    
    public function revoke_auth($uid)
    {
        try{
            $request = $this->request('/' . $uid . '/permissions', 'POST' ,
            array('method'=>'delete','access_token'=>$this->app_access_token));
        } catch (Exception $e){ $request = false; }
        
        return $request;    
    }
    
    protected function request($path, $method = "POST", $args = array(), $ssl = true, $json_decode = true)
    {
        $ch = curl_init();
        $method = strtoupper($method);

        $url = $ssl ? "https://".$this->config['facebook.api_url'].$path : "http://".$this->config['facebook.api_url'].$path;

        if ( $method == 'POST' )
        { 
            curl_setopt($ch, CURLOPT_POST, true); 
        }
        elseif ( $method == 'GET' )
        {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }
        elseif( $method == 'DELETE' )
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        if ( $args && $method == 'POST' )
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args, null, '&'));
        }
        elseif ( $args && $method == 'GET' )
        {
            $url .= '?'.http_build_query($args, null, '&');
        }     

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_URL, $url);

        $result = curl_exec($ch);

        if ( $result === false ) 
        {
            curl_close($ch);
            return curl_error($ch); 
        }

        curl_close($ch);

        return $json_decode ? json_decode($result, true) : $result;
    }
    
}