<?php
  
  class WolfAuth_Acl {
      
    var $perms = array();
    var $user_id;
    var $user_roles = array();
    var $ci;
    
    function __construct($config=array()) 
    {
        $this->ci = CI_BASE::get_instance();

        $this->userID = floatval($config['userID']);
        $this->userRoles = $this->getUserRoles();
        $this->buildACL();
    }

    function buildACL() {
    //first, get the rules for the user's role
    if (count($this->userRoles) > 0)
    {
    $this->perms = array_merge($this->perms,$this->getRolePerms($this->userRoles));
    }
    //then, get the individual user permissions
    $this->perms = array_merge($this->perms,$this->getUserPerms($this->userID));
    }

    function getPermKeyFromID($permID) {
    //$strSQL = "SELECT `permKey` FROM `".DB_PREFIX."permissions` WHERE `ID` = " . floatval($permID) . " LIMIT 1";
    $this->ci->db->select('permKey');
    $this->ci->db->where('id',floatval($permID));
    $sql = $this->ci->db->get('perm_data',1);
    $data = $sql->result();
    return $data[0]->permKey;
    }

    function getPermNameFromID($permID) {
    //$strSQL = "SELECT `permName` FROM `".DB_PREFIX."permissions` WHERE `ID` = " . floatval($permID) . " LIMIT 1";
    $this->ci->db->select('permName');
    $this->ci->db->where('id',floatval($permID));
    $sql = $this->ci->db->get('perm_data',1);
    $data = $sql->result();
    return $data[0]->permName;
    }

    function getRoleNameFromID($roleID) {
    //$strSQL = "SELECT `roleName` FROM `".DB_PREFIX."roles` WHERE `ID` = " . floatval($roleID) . " LIMIT 1";
    $this->ci->db->select('roleName');
    $this->ci->db->where('id',floatval($roleID),1);
    $sql = $this->ci->db->get('role_data');
    $data = $sql->result();
    return $data[0]->roleName;
    }

    function getUserRoles() {
    //$strSQL = "SELECT * FROM `".DB_PREFIX."user_roles` WHERE `userID` = " . floatval($this->userID) . " ORDER BY `addDate` ASC";

    $this->ci->db->where(array('userID'=>floatval($this->userID)));
    $this->ci->db->order_by('addDate','asc');
    $sql = $this->ci->db->get('user_roles');
    $data = $sql->result();

    $resp = array();
    foreach( $data as $row )
    {
    $resp[] = $row->roleID;
    }
    return $resp;
    }

    function getAllRoles($format='ids') {
    $format = strtolower($format);
    //$strSQL = "SELECT * FROM `".DB_PREFIX."roles` ORDER BY `roleName` ASC";
    $this->ci->db->order_by('roleName','asc');
    $sql = $this->ci->db->get('role_data');
    $data = $sql->result();

    $resp = array();
    foreach( $data as $row )
    {
    if ($format == 'full')
    {
    $resp[] = array("id" => $row->ID,"name" => $row->roleName);
    } else {
    $resp[] = $row->ID;
    }
    }
    return $resp;
    }

    function getAllPerms($format='ids') {
    $format = strtolower($format);
    //$strSQL = "SELECT * FROM `".DB_PREFIX."permissions` ORDER BY `permKey` ASC";

    $this->ci->db->order_by('permKey','asc');
    $sql = $this->ci->db->get('perm_data');
    $data = $sql->result();

    $resp = array();
    foreach( $data as $row )
    {
    if ($format == 'full')
    {
    $resp[$row->permKey] = array('id' => $row->ID, 'name' => $row->permName, 'key' => $row->permKey);
    } else {
    $resp[] = $row->ID;
    }
    }
    return $resp;
    }

    function getRolePerms($role) {
    if (is_array($role))
    {
    //$roleSQL = "SELECT * FROM `".DB_PREFIX."role_perms` WHERE `roleID` IN (" . implode(",",$role) . ") ORDER BY `ID` ASC";
    $this->ci->db->where_in('roleID',$role);
    } else {
    //$roleSQL = "SELECT * FROM `".DB_PREFIX."role_perms` WHERE `roleID` = " . floatval($role) . " ORDER BY `ID` ASC";
    $this->ci->db->where(array('roleID'=>floatval($role)));

    }
    $this->ci->db->order_by('id','asc');
    $sql = $this->ci->db->get('role_perms'); //$this->db->select($roleSQL);
    $data = $sql->result();
    $perms = array();
    foreach( $data as $row )
    {
    $pK = strtolower($this->getPermKeyFromID($row->permID));
    if ($pK == '') { continue; }
    if ($row->value === '1') {
    $hP = true;
    } else {
    $hP = false;
    }
    $perms[$pK] = array('perm' => $pK,'inheritted' => true,'value' => $hP,'name' => $this->getPermNameFromID($row->permID),'id' => $row->permID);
    }
    return $perms;
    }

    function getUserPerms($userID) {
    //$strSQL = "SELECT * FROM `".DB_PREFIX."user_perms` WHERE `userID` = " . floatval($userID) . " ORDER BY `addDate` ASC";

    $this->ci->db->where('userID',floatval($userID));
    $this->ci->db->order_by('addDate','asc');
    $sql = $this->ci->db->get('user_perms');
    $data = $sql->result();

    $perms = array();
    foreach( $data as $row )
    {
    $pK = strtolower($this->getPermKeyFromID($row->permID));
    if ($pK == '') { continue; }
    if ($row->value == '1') {
    $hP = true;
    } else {
    $hP = false;
    }
    $perms[$pK] = array('perm' => $pK,'inheritted' => false,'value' => $hP,'name' => $this->getPermNameFromID($row->permID),'id' => $row->permID);
    }
    return $perms;
    }

    function hasRole($roleID) {
    foreach($this->userRoles as $k => $v)
    {
    if (floatval($v) === floatval($roleID))
    {
    return true;
    }
    }
    return false;
    }

    function hasPermission($permKey) {
    $permKey = strtolower($permKey);
    if (array_key_exists($permKey,$this->perms))
    {
    if ($this->perms[$permKey]['value'] === '1' || $this->perms[$permKey]['value'] === true)
    {
    return true;
    } else {
    return false;
    }
    } else {
    return false;
    }
    }
      
  }
  
?>
