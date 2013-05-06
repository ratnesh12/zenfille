<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Users extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->app_security_model->check_session()) {
            redirect('http://' . $_SERVER["HTTP_HOST"] . '/new_project/login/');
        }
        if ($this->session->userdata('type') != 'admin') {
            redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
        }
    }

    public function index()
    {
        $this->load->library('table');
        $this->load->model('users_model', 'users');
        $data['users'] = $this->users->get_all_users();
        $header['page_name'] = 'Dashboard';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Users'
        );

        $this->load->view('parts/header', $header);
        $this->load->view('users/list', $data);
        $this->load->view('parts/footer');
    }

    public function create()
    {
        $this->load->library('table');
        $this->load->model('users_model', 'users');
        $data['supervisors'] = $this->users->get_supervisors();
        $data['firms'] = $this->users->get_firms();
        $data['sales'] = $this->users->get_sales();
        $data['managers'] = $this->users->get_managers();


        $header['page_name'] = 'Create User';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'New User'
        );

        $this->load->view('parts/header', $header);
        $this->load->view('users/create', $data);
        $this->load->view('parts/footer');
    }

    public function edit($user_id = '', $type = 'customer')
    {
        $this->load->library('form_validation');
        $this->load->library('table');
        $this->load->model('users_model', 'users');
        $this->load->model('customers_model', 'customers');
        $data['user'] = $this->customers->get_user($user_id, $type);
        $data['supervisors'] = $this->users->get_supervisors();
        $data['firms'] = $this->users->get_firms();
        $header['page_name'] = 'Edit User ' . $user_id;
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'User ' . $user_id
        );

        $this->load->view('parts/header', $header);
        $this->load->view('users/edit', $data);
        $this->load->view('parts/footer');
    }

    public function update($user_id = '', $type = 'customer')
    {
        $this->load->library('form_validation');
        $this->load->model('users_model', 'users');
        $this->form_validation->set_rules('firstname', 'Firstname', 'required');
        $this->form_validation->set_rules('lastname', 'Lastname', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        if ($this->form_validation->run() == FALSE) {
            $this->edit($user_id);
        }
        else
        {
            $type = $this->input->post('type');
            $this->users->update_user($user_id, $type);
            $this->session->set_flashdata('message', 'Information has been saved');
            redirect('/users/edit/' . $user_id . '/' . $type);
        }
    }

    public function insert()
    {
        $this->load->library('form_validation');
        $this->load->model('users_model', 'users');
        $this->form_validation->set_rules('username', 'Username', 'required|callback__username_check');
        $this->form_validation->set_rules('firstname', 'Firstname', 'required');
        $this->form_validation->set_rules('lastname', 'Lastname', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');
        $this->form_validation->set_rules('confirm_password', 'Password Confirmation', 'required|matches[password]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');

        if ($this->form_validation->run() == FALSE) {
            $this->create();
        }
        else
        {
            $type = $this->input->post('type');
            $this->users->insert_user($type);
            redirect('/users/');
        }
    }

    public function delete($user_id, $type)
    {
        $this->load->model('users_model', 'users');
        $this->users->delete_user($user_id, $type);
        redirect('/users/');
    }

    function _username_check()
    {

        $this->load->model('customers_model', 'customers');
        if ($this->customers->check_username($this->input->post('username'))) {
            return true;
        }
        $this->form_validation->set_message('_username_check', 'Username ' . $this->input->post("username") . ' is already exist');
        return false;

    }
}

/* End of file users.php */
/* Location: ./application/controllers/users.php */