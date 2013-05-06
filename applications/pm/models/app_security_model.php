<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class App_security_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Login to system. Generic function
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     */
    public function do_login()
	
	
    {
		
		//$admin=md5(admin);
        //  echo $admin; die;
			$username = strtolower($this->input->post('username'));
        	$password = $this->input->post('password');
		
        if (!is_null($data = $this->check_auth($username, $password))){ 
		   	 return $data;
	    }else{	
		   	return NULL;
        }
	}

    public function check_user($username, $password, $table){
		
        $this->db->where('username', $username);
        $this->db->where('password', md5(sha1(($password))));
        $query = $this->db->get($table);	    
		
		// echo $this->db->last_query() . '<br/>';
        if ($query->num_rows()) {
            $result = $query->row_array();
            
			if ($result['blocked'] == '0') {
                // Clear login attemplts to 0 and set last login time
                $this->set_last_login($result['id'], $table);
                return $result;
            } else {

            }
        } else {
			
            $this->increase_login_attempts($username, $table);

        }

        if ($this->check_login_attempts($username, $table)) {
            return true;
        }else{
            $this->input->set_cookie('login_error', 'Wrong username/password', time() + 3600, '.' . $this->input->server('HTTP_HOST') . '', '/', 'zen_', FALSE);
        }

        return false;
    }

    /**
     * Checks auth data
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string    username
     * @param    string    password
     * @return    mixed
     */
    public function check_auth($username, $password)
    {
        setcookie('login_error', '', time() - 3600, '.' . $this->input->server('HTTP_HOST') . '', '/', 'zen_', FALSE);
        
        if ($data = $this->check_user($username, $password, 'customers')) {

            $session_array = array(
                'client_user_id' => $data['id'],
                'client_username' => $data['username'],
                'client_firstname' => $data['firstname'],
                'client_lastname' => $data['lastname'],
                'client_email' => $data['email'],
                'client_bdv_id' => $data['bdv_id'],
                'client_allow_email' => $data['allow_email'],
                'client_type' => $data['type'],
                'client_tooltips_disable' => $data['is_disable_tooltips'],
                'parent_firm_id' => '',
                'client_manager_id' => '',
                'client_logged_in' => TRUE
            );
            if ($data['parent_firm_id']) {
                $session_array['parent_firm_id'] = $data['parent_firm_id'];
            }
            if ($data['manager_id']) {
                $session_array['client_manager_id'] = $data['manager_id'];
            }

            $this->session->set_userdata($session_array);
            $usertype = 'client';
            return $usertype;

        } elseif ($data = $this->check_user($username, $password, 'managers')) {
			
		        $session_array = array(
                'manager_user_id' => $data['id'],
                'manager_username' => $data['username'],
                'manager_firstname' => $data['firstname'],
                'manager_email' => $data['email'],
                'manager_lastname' => $data['lastname'],
                'type' => $data['type'],
                'manager_logged_in' => TRUE
            );
		 
              $this->session->set_userdata($session_array);
             $usertype = $data['type'];     
			      
			return $usertype;

        } elseif ($data = $this->check_user($username, $password, 'associates')) {

            $session_array = array(
                'fa_user_id' => $data['id'],
                'fa_name' => $data['name'],
                'fa_username' => $data['username'],
                'fa_email' => $data['email'],
                'fa_type' => TRUE
            );

            $this->session->set_userdata($session_array);
            $usertype = 'fa';
            return $usertype;
        }
        return FALSE;
    }


    public function check_login_attempts($username, $table)
    {
        $this->load->model('send_emails_model', 'send_emails');
        $this->db->where('username', $username);
        $query = $this->db->get($table);
        if ($query->num_rows() > 0) {
            $data = $query->row_array();
            $remain_login_attempts = 5 - $data['login_attempts'];
            if ($remain_login_attempts < 1) {
                $this->block_user($username, $table);
                $from = 'portal' . $this->config->item('default_email_box');
                if (TEST_MODE) {
                    $to = TEST_PM_EMAIL;
                }
                else
                {
                    $to = $this->config->item('zenfile_pm_email');
                }
                $subject = 'The user "' . $username . '" has been blocked';
                $content = 'The user "' . $username . '" has been blocked due to too many unsuccessful login attempts';
                $this->send_emails->send_email($from, $from, $subject, $content, $to, false, false);

                $this->input->set_cookie('login_error', 'You have made too many unsuccessful login attempts and your IP address (' . $this->input->ip_address() . ') has now been blocked. Your login is now temporarily blocked. This temporary delay helps prevent someone else from guessing your password. Your project manager will contact you about reinstating your account.', time() + 3600, '.' . $this->input->server('HTTP_HOST') . '', '/', 'zen_', FALSE);
                return true;
            }
            else
            {
                $this->input->set_cookie('login_error', 'The user name or password you entered is incorrect. Please try again. Remaining attempts: ' . $remain_login_attempts, time() + 3600, '.' . $this->input->server('HTTP_HOST') . '', '/', 'zen_', FALSE);
                return true;
            }
        }
        return FALSE;
    }

    /**
     * Sets the time of last user login
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    client ID
     * @return    void
     */
    public function set_last_login($id, $table)
    {
        $data = array(
            'last_login' => date('Y-m-d H:i:s'),
            'login_attempts' => 0
        );
        $this->db->where('id', $id);
        $this->db->update($table, $data);
    }

    /**
     * Increase count of login attempts
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string    username
     * @return    void
     */
    public function increase_login_attempts($username, $table)
    {
       
		   
		$this->db->where('username', $username);
        $query = $this->db->get($table);
		
        if ($query->num_rows()) {
            $customer = $query->row_array();
            // Increase a quantity of login attempts
            $login_attempts = $customer['login_attempts'] + 1;
            $data = array(
                'login_attempts' => $login_attempts
            );
            $this->db->where('username', $username);
            $this->db->update($table, $data);
        }   
		   
		   
		
    }

    /**
     * Checks current user's session
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    bool
     */
    public function check_session()
    {
 
        if ($this->session->userdata('client_user_id') > 0) {

            $id = $this->session->userdata('client_user_id');
            $username = $this->session->userdata('client_username');
            $this->db->where('username', $username);
            $query = $this->db->get('customers');
		 
            if ($query->num_rows()) {
                $this->input->set_cookie('login_error', '', time() + 3600, '.' . $this->input->server('HTTP_HOST') . '', '/', 'zen_', FALSE);
                $user = $query->row_array();

                if ($user['id'] == $id) {
                    return TRUE;
                }
            }

        } elseif ($this->session->userdata('manager_user_id') > 0)
        {

            $id = $this->session->userdata('manager_user_id');
            $username = $this->session->userdata('manager_username');
            $this->db->where('username', $username);
            $query = $this->db->get('managers');


            if ($query->num_rows()) {
                $user = $query->row_array();
                if ($user['id'] == $id) {
                    if ($this->check_ip()) {
                        return TRUE;
                    }
                }
            }

        }elseif ($this->session->userdata('fa_user_id') > 0)
        {

            $id = $this->session->userdata('fa_user_id');
            $username = $this->session->userdata('fa_username');
            $this->db->where('username', $username);
            $query = $this->db->get('associates');

            if ($query->num_rows()) {
                $user = $query->row_array();
               // var_dump($user);exit;
                if ($user['id'] == $id) {
                        return TRUE;
                }
            }
        }

        $current_url = $this->uri->uri_string();
        $this->input->set_cookie('previous_url', $current_url, 86500);
        return FALSE;
    }

    /**
     * Logout function. Clear current session
     *
     * @access public
     * @return void
     */

    public function logout()
    {
        $this->session->sess_destroy();

        redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
    }

    public function block_user($username, $table)
    {
        $data = array(
            'blocked' => "1"
        );
        $this->db->where('username', $username);
        $this->db->update($table, $data);
        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    public function check_ip($ip_address = '')
    {
        if (empty($ip_address)) {
            $ip_address = $this->input->ip_address();
        }
        $this->db->where('ip_address', $ip_address);
        $this->db->where('is_active', '1');
        $query = $this->db->get('allowed_ip');

        if ($query->num_rows()) {
            return TRUE;
        }
        return FALSE;
    }
}

?>