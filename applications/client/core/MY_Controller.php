<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

	protected function forceHttps()
	{
		if (!$this->input->is_cli_request() && $this->input->server("HTTP_HOST") != 'zenfile.dev' && $this->input->server("SERVER_PORT") != 443)
		{
			$current = str_replace("http://", "https://", current_url());

			redirect($current);
			die();
		}
	}
	
	
	public function __construct()
	{
		parent::__construct();
		
		$this->forceHttps();
	}
	
}