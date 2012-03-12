<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * WolfAuth
 *
 * An open source driver based authentication library for Codeigniter
 *
 * @package   WolfAuth
 * @author    Dwayne Charrington
 * @copyright Copyright (c) 2012 Dwayne Charrington.
 * @link      http://ilikekillnerds.com
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html
 * @version   2.0
 */

class Wolfauth_roles extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public function add_role($role_name, $role_slug = '')
    {
        // If no role slug supplied, convert the role name to a slug
        if ($role_slug == '') {
            $role_slug = strtolower(url_title($role_name, 'underscore'));
        }
    }

    /**
     * Has Role
     *
     * Does a user have a particular role?
     *
     * @param $role_slug
     * @return bool
     */
    public function has_role($role_slug)
    {
        $this->db->select('roles.role_name, roles.role_slug')->where('roles.role_slug', $role_slug)->join($this->config->item('table.roles', 'wolfauth'), 'users.role_id = roles.id');

        $this->db->get($this->config->item('table.users', 'wolfauth'));

        return ($this->db->num_rows() == 1) ? TRUE : FALSE;
    }
}