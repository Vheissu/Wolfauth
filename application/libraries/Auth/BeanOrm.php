<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 *
 * @package        WolfAuth
 * @subpackage  Redbean ORM
 * @author            Dwayne Charrington
 * @copyright       2013 Dwayne Charrington.
 * @link                 http://ilikekillnerds.com
 * @license          http://www.apache.org/licenses/LICENSE-2.0.html
 * @version          2.0
 */

class BeanOrm {

    public function __construct()
    {
        // Database config file
        include (APPPATH.'/config/database.php');

        // Redbean main ORM file
        include (APPPATH.'/vendor/rb.php');

        // Database data
        $host  = $db[$active_group]['hostname'];
        $user  = $db[$active_group]['username'];
        $pass = $db[$active_group]['password'];
        $db     = $db[$active_group]['database'];

        // Setup DB connection
        R::setup("mysql:host=$host;dbname=$db", $user, $pass);
    }

}
