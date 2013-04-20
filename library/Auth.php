<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth {

	protected $CI;

	public function __construct()
	{
		$this->CI = &get_instance();

		$this->CI->config->load('auth');

		$this->CI->load->database();
		$this->CI->load->library('session');
		$this->CI->load->library('encrypt');

		$this->CI->load->model('user_model');
		$this->CI->load->helper('cookie');

		$this->do_you_remember_me();
	}

	public function is_logged()
	{
		return ($this->CI->session->userdata('user_id') !== FALSE) ? TRUE : FALSE;
	}

	public function user_id()
	{
		return $this->CI->session->userdata('user_id');
	}

	public function is_role($role = 'admin')
	{
		$role_name = $this->CI->session->userdata('role_name');

		return ($role_name == $role) ? TRUE : FALSE;
	}

	public function login($email, $password)
	{
		$user = $this->CI->user_model->get($email);

		if ($user)
		{
			if ($this->CI->user_model->hash_password($password) == $user->password)
			{
				$role_name	= $user->role_name;
				$user_id	= $user->id;

				$this->CI->session->set_userdata(array(
					'user_id'	=> $user_id,
					'role_name'	=> $group_id,
					'email'		=> $user->email
				));

				// Remember me?
				if ($this->CI->input->post('remember_me') == 'yes')
				{
					$this->set_remember_me($user_id);
				}

				return $user_id;
			}
		}

		return FALSE;
	}

	public function logout()
	{
		$user_id = $this->CI->session->userdata('user_id');

		$this->CI->session->sess_destroy();
		delete_cookie('rememberme');

		$user_data = array(
			'id' => $this->CI->session->userdata('user_id'),
			'remember_me' => ''
		);

		$this->CI->user_model->update($user_data);
	}

	private function set_remember_me($user_id)
	{
		$token = md5(uniqid(rand(), TRUE));
		$timeout = 60 * 60 * 24 * 7; // One week

		$remember_me = $this->CI->encrypt->encode($user_id.':'.$token.':'.(time() + $timeout));

		// Set the cookie and database
		$cookie = array(
			'name'		=> 'rememberme',
			'value'		=> $remember_me,
			'expire'	=> $timeout
		);

		set_cookie($cookie);
		$this->CI->user_model->update(array('id' => $user_id, 'remember_me' => $remember_me));
	}

	public function do_you_remember_me()
	{
		if( $cookie_data = get_cookie('rememberme') )
		{
			$user_id = '';
			$token   = '';
			$timeout = '';

			$cookie_data = $this->CI->encrypt->decode($cookie_data);
			
			if (strpos($cookie_data, ':') !== FALSE)
			{
				$cookie_data = explode(':', $cookie_data);
				
				if (count($cookie_data) == 3)
				{
					list($user_id, $token, $timeout) = $cookie_data;
				}
			}

			if ( (int) $timeout < time() )
			{
				return FALSE;
			}

			if ( $data = $this->CI->user_model->get_user_by_id($user_id) )
			{
				// Fill the session and renew the remember me cookie
				$this->CI->session->set_userdata(array(
					'user_id'	=> $user_id,
					'role_name'	=> $data->role_name
					'email'     => $data->email
				));

				$this->set_remember_me($user_id);

				return TRUE;
			}

			delete_cookie('rememberme');
		}

		return FALSE;
	}



}