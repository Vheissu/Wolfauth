<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 *
 * @package   WolfAuth
 * @author    Dwayne Charrington
 * @copyright Copyright (c) 2013 Dwayne Charrington.
 * @link      http://ilikekillnerds.com
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html
 * @version   2.0
 */

 /*
 | -------------------------------------------------------------------
 | EMAIL CONFING
 | -------------------------------------------------------------------
 | Configuration of outgoing mail server.
 | */ 
 $config['protocol'] = 'smtp';
 $config['smtp_host'] = 'ssl://smtp.googlemail.com';
 $config['smtp_port'] =' 465';
 $config['smtp_timeout'] = '30';
 $config['smtp_user'] = '';
 $config['smtp_pass'] = '';
 $config['charset'] = 'utf-8';
 $config['newline'] = "\r\n";
 $config['mailtype'] = "html";
 
 /* End of file email.php */
 /* Location: ./system/application/config/email.php */
 