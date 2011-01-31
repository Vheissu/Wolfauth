<?php

abstract class WolfAuth_Core {
    
    abstract protected function login();
    abstract protected function logout();
    abstract protected function get_role();
    
}