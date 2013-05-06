<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->app_security_model->check_session()) {
            redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
        }
        if ($this->session->userdata('type') != 'admin') {
            redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
        }
    }

    /**
     * Dashboard. Some content goes here
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     */

    public function index()
    {
        $this->load->library('table');
        $header['page_name'] = 'Admin Dashboard';
        $header['breadcrumb'] = array(
            'Dashboard'
        );
        $header['subheader_message'] = 'Welcome to the Zenfile Client Portal';
        $this->load->view('parts/header', $header);
        $this->load->view('cases/admin/list');
        $this->load->view('parts/footer');
    }

    /**
     * Removes case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     */

    public function draft_case()
    {
        $this->load->model('cases_model', 'cases');

        $start_case_number = $this->input->post('start_case_number');
        $end_case_number = $this->input->post('end_case_number');

        if (empty($start_case_number) && empty($end_case_number)) {
            $this->session->set_flashdata('message', 'No cases entered!');
            redirect('/admin/');
        }

        if (!empty($start_case_number)) {
            $this->db->where('case_number >= ' . $start_case_number, FALSE, FALSE);
        }
        if (!empty($end_case_number)) {
            $this->db->where('case_number <= ' . $end_case_number, FALSE, FALSE);
        }
        $query = $this->db->get('cases');

        if ($query->num_rows()) {
            foreach ($query->result_array() as $case)
            {
                $this->cases->set_case_inactive($case['id']);
            }
            $this->session->set_flashdata('message', 'The case has been removed!');
            redirect('/admin/');
        }
    }

    public function delete_user_cases()
    {
        $this->load->model('cases_model', 'cases');
        $user_id = $this->input->post('user_id');
        $this->db->where('user_id',$user_id);
        $query = $this->db->get('cases');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $case)
            {
                $this->cases->set_case_inactive($case['id']);
            }
            $this->session->set_flashdata('message', 'Cases have been removed!');
            redirect('/admin/');
        }
        $this->session->set_flashdata('message', 'The user is not exist or has no cases!');
        redirect('/admin/');
    }

    public function case_number_check($case_number)
    {
        $result = true;
        $this->load->model('cases_model', 'cases');
        if (strlen($case_number) < 1 || !is_numeric($case_number)) {
            $this->form_validation->set_message('case_number_check', 'The field "Case number" is required and must be a digit');
            $result = false;
        }
        if ($this->cases->is_exist_case_number($case_number)) {
            $this->form_validation->set_message('case_number_check', 'The case number "' . $case_number . '" already exists');
            $result = false;
        }
        return $result;
    }

    public function user_id_check($user_id)
    {
        $result = true;
        $this->load->model('customers_model', 'customers');
        if (strlen($user_id) < 1 || !is_numeric($user_id)) {
            $this->form_validation->set_message('user_id_check', 'The field "User Id" is required and must be a digit');
            $result = false;
        } elseif (!$this->customers->get_user($user_id, 'customer')) {
            $this->form_validation->set_message('user_id_check', 'The user with "' . $user_id . '" id is not exists');
            $result = false;
        }
        return $result;
    }

    public function create_case()
    {
        $header['page_name'] = 'Create case';
        $this->load->model('emails_model', 'emails');
        $this->load->model('customers_model', 'customers');
        $this->form_validation->set_rules('case_number', 'Case number', 'callback_case_number_check');
        $this->form_validation->set_rules('user_id', 'User ID', 'callback_user_id_check');
        if ($this->form_validation->run()) {
            $user_info = $this->customers->get_user($this->input->post('user_id'), 'customer');
            $data = array(
                'is_intake' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'is_active' => '1',
                'common_status' => 'pending-intake',
                'case_type_id' => 3,
                'case_number' => set_value('case_number'),
                'user_id' => set_value('user_id'),
                'sales_manager_id' => $user_info['bdv_id'],
                'manager_id' => $user_info['manager_id']
            );
            $this->db->insert('cases', $data);
            $case_id = $this->db->insert_id();
            $this->emails->create_email_account_for_case($data['case_number']);
            $this->session->set_flashdata('message', 'The case(id=' . $case_id . ') has been created!');
            redirect(base_url() . 'admin/');
        }

        $this->load->view('parts/header', $header);
        $this->load->view('cases/admin/create_case');
        $this->load->view('parts/footer');
    }

    public function assign_client_to_case()
    {
        $this->load->model('cases_model', 'cases');
        $case_number = $this->input->post('case_number');
        $user_id = (int)$this->input->post('user_id');
        $this->db->where('id', $user_id);
        $query = $this->db->get('customers');
        if ($query->num_rows()) {
            $user = $query->row_array();

            $this->db->where('case_number', $case_number);
            $query = $this->db->get('cases');
            if ($query->num_rows()) {
                $case = $query->row_array();
                $this->cases->assign_client_to_case($case, $user_id);
                $this->db->select('child_case_id');
                $this->db->where('parent_case_id', $case['id']);
                $query = $this->db->get('related_cases');
                if ($query->num_rows()) {
                    $related_cases = $query->result_array();
                    $related_cases_id = array();
                    foreach ($related_cases as $temp) {
                        $related_cases_id[] = $temp['child_case_id'];
                        $this->db->where('case_id', $temp['child_case_id']);
                        $this->db->delete('case_contacts');
                    }
                    $related_cases_id = implode(', ', $related_cases_id);
                    $this->db->where('id IN (', $related_cases_id . ')', false);
                    $query = $this->db->get('cases');
                    if ($query->num_rows()) {
                        $related_cases = $query->result_array();
                        foreach ($related_cases as $related_case) {
                            $this->cases->assign_client_to_case($related_case, $user_id);
                        }
                    }
                }
                $this->db->where('case_id', $case['id']);
                $this->db->delete('case_contacts');
            } else {
                $this->session->set_flashdata('message', 'This case is not exist yet!');
                redirect(base_url() . 'admin/');
            }
        }
        else {
            $this->session->set_flashdata('message', 'This user does not exist');
            redirect(base_url() . 'admin/');
        }

        $this->session->set_flashdata('message', 'The case ' . $case_number . ' has been reassign to client ' . $user["firstname"] . ' ' . $user["lastname"] . '!');
        redirect(base_url() . 'admin/');

    }

    public function get_case(){
        $this->load->model('cron_model', 'cron');
        $this->cron->clear_deleted_cases();
        exit;
    }
}
