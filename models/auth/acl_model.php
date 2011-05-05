<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 * 
 * This driver is a simple ACL implementation which also interfaces
 * with the Simple Auth driver to allow you to restrict access to
 * certain parts of your site.
 *
 * @package       WolfAuth
 * @subpackage    Acl Model
 * @author        Dwayne Charrington
 * @copyright     Copyright (c) 2011 Dwayne Charrington.
 * @link          http://ilikekillnerds.com
 * @license       Do What You Want, As Long As You Attribute Me (DWYWALAYAM) licence
 */
 
class Acl_model extends CI_Model {
    
    public function __construct()
    {
        parent::__construct();
    }
    
}