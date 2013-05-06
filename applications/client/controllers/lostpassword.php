<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lostpassword extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
	}

	public function index()
	{			
		$this -> load -> view('lostpassword/main');
	}
	
	public function submit()
	{
        $this->load->model('send_emails_model', 'send_emails');
        $this->form_validation->set_rules('log', 'Username', 'required|callback__usercheck');
		$username = $this -> input -> post('log');

		if (($this->form_validation->run()))
		{
			$customer = $this->user_exist_check($username);
			$new_password = create_password(7, TRUE, TRUE, TRUE);
            $from = 'portal'.$this->config->item('default_email_box');
            $TEMPLATE = $this->db->get_where("zen_emails_templates", array("id" => '34'))->row_array();
            $login_link = '<a href= "https://'.$_SERVER["HTTP_HOST"].'/client/"> here:</a>';
            $email_link = '<a href="mailto:'.$from.'">'.$from.'</a>';

            /* REPLACEMENT FOR MESSAGE */
            $TEMPLATE["content"] = str_replace("%LOGIN_LINK%", $login_link, $TEMPLATE["content"]);
            $TEMPLATE["content"] = str_replace("%NEW_PASSWORD%", $new_password, $TEMPLATE["content"]);
            $TEMPLATE["content"] = str_replace("%PORTAL_EMAIL%", $email_link, $TEMPLATE["content"]);
            $TEMPLATE["content"] = str_replace("%CASE_USER%", $customer['firstname'].' '.$customer['lastname'], $TEMPLATE["content"]);
            $to = $customer['email'];
            $this->send_emails->send_email($from, $from, $TEMPLATE["subject"], $TEMPLATE["content"], $to, false, false);
            $this->db->set('password', md5(sha1(($new_password))));
            $this->db->where('id', $customer['id']);
            $table = 'managers';
            if(isset($customer['manager_id'])){
                $table = 'customers';
            }
            $this->db->update($table);
            redirect('/lostpassword/thanks');
		}
        $this -> load -> view('lostpassword/main');
	}

    function _usercheck($username){
        if ($this->user_exist_check($username))
        {
            $result = true;
        }else{
            $this->form_validation->set_message('_usercheck', 'The user "'.$username.'" is not exists');
            $result = false;
        }
        return $result;

    }

    public function thanks(){
        $this -> load -> view('lostpassword/thanks');
    }

    public function user_exist_check($username){
        $this -> db -> where('username', $username);
        $query = $this -> db -> get('customers');
        if ($query -> num_rows())
        {
            return $query->row_array();
        }
        $this -> db -> where('username', $username);
        $query = $this -> db -> get('managers');
        if ($query -> num_rows())
        {
            return $query->row_array();
        }
        return false;
    }
}

/* End of file lostpassword.php */
/* Location: ./application/controllers/lostpassword.php */