<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Errors extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
	}
	/**
	* Shows error page in case javascript is disabled in client's browser
	* 
	* @access	public
	* @author	Sergey Koshkarev <koshkarev.ss@gmail.com> 
	* @return	void
	*/
	public function noscript()
	{	
		$this -> load -> view('errors/noscript');
	}
}

/* End of file errors.php */
/* Location: ./application/controllers/errors.php */