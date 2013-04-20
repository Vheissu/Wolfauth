<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function is_logged()
{
	$CI =& get_instance();
	return $CI->auth->is_logged();
}

function user_id()
{
	$CI =& get_instance();
	return $CI->auth->user_id();
}

function is_role($role)
{
	$CI =& get_instance();
	return $CI->auth->is_role($role);
}

function login($email, $password)
{
	$CI =& get_instance();
	return $CI->auth->login($email, $password);	
}

function logout()
{
	$CI =& get_instance();
	return $CI->auth->logout();	
}