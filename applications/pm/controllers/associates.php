<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Associates extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->app_security_model->check_session()) {
            redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
        }
    }

    /**
     * Main associates list
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function index($action = false)
    {
        $this->load->library('table');
        $this->load->model('associates_model', 'associates');

        // If we do search
        $data['search_string'] = $search_string = $this->input->post('search_string');
        if (!empty($search_string)) {
            $data['associates'] = $this->associates->search_associates($search_string);
            $data['replaced_associates'] = $this->associates->search_associates($search_string, true);
        }
        else
        {
            $data['associates'] = $this->associates->get_all_associates('0');
            $data['replaced_associates'] = $this->associates->get_all_associates('1');
        }

        $header['selected_menu'] = 'associates';
        $header['page_name'] = 'Associates';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Associates'
        );
        if (!empty($action) && $action == 'success') {
            $data['message'] = 'New Associate Successfully Added';
        }
        $this->load->view('parts/header', $header);
        $this->load->view('associates/list', $data);
        $this->load->view('footer');
    }

    /**
     * Removes associate by his ID
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    associate ID
     * @return    void
     * */
    public function delete($associate_id = '')
    {
        $this->load->model('associates_model', 'associates');
        $this->associates->delete_associate($associate_id);
        redirect('/associates/');
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

    public function ajax_username_check()
    {
        $this->load->model('customers_model', 'customers');
        if ($this->customers->check_username($this->input->get('username'))) {
            $output = true;
        } else {
            $output = false;
        }
        echo json_encode($output);
    }

    /**
     * Updates associate entry.
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    associate ID
     * @return    void
     * */
    public function edit($associate_id = '')
    {
        $this->load->library('form_validation');
        $this->load->library('table');
        $this->load->model('countries_model', 'countries');
        $this->load->model('send_emails_model', 'send_emails');
        $this->load->model('associates_model', 'associates');
        $this->form_validation->set_rules('country_id', 'Country', 'trim|required');
        $this->form_validation->set_rules('fee', 'Fee', 'trim|required');
        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('address', 'Address', 'trim|required');
        $this->form_validation->set_rules('phone', 'Phone', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_emails');
        if (empty($associate_id)) {
            // $this->form_validation->set_rules('email', 'Email', 'required|valid_email|callback__email_check');
            $this->form_validation->set_rules('username', 'Username', 'required|callback__username_check');
        }
        $this->form_validation->set_rules('contact_name', 'Contact Name', 'required');
        $this->form_validation->set_error_delimiters('<div class="red-notice">', '</div>');

        if ($this->form_validation->run() == TRUE) {
            $config['upload_path'] = './uploads/associates/';
            $config['allowed_types'] = 'pdf';
            $config['max_size'] = '10000';
            $this->load->library('upload', $config);

            $filetype = '';
            $path_to_gsa_agreement = '';

            if ($this->upload->do_upload('agreement')) {
                $upload_data = $this->upload->data();
                $path_to_gsa_agreement = 'uploads/associates/' . $upload_data['file_name'];
                $filetype = $upload_data['file_type'];
            } else {

            }
            $password = $this->associates->rand_string('6');
            $associate_record = array(
                'country_id' => trim($this->input->post('country_id')),
                'associate' => $this->input->post('associate'),
                'email' => $this->input->post('email'),
                'firm' => $this->input->post('firm'),
                'name' => $this->input->post('name'),
                'username' => $this->input->post('username'),
                'phone' => $this->input->post('phone'),
                'address' => $this->input->post('address'),
                'address2' => $this->input->post('address2'),
                'fa_country_id' => $this->input->post('fa_country_id'),
                'city' => $this->input->post('city'),
                'zip_code' => $this->input->post('zip_code'),
                'fax' => $this->input->post('fax'),
                'website' => $this->input->post('website'),
                'fee' => $this->input->post('fee'),
                'fee_currency' => $this->input->post('fee_currency'),
                'translation_required' => $this->input->post('translation_required'),
                'contact_name' => $this->input->post('contact_name'),
                '30_months' => $this->input->post('30_months'),
                '31_months' => $this->input->post('31_months'),
                'ep_validation' => $this->input->post('ep_validation'),
                'is_direct_case_allowed' => $this->input->post('is_direct_case_allowed'),
                'gsa_filetype' => $filetype
            );

            if ($path_to_gsa_agreement) {
                $associate_record['path_to_gsa_agreement'] = $path_to_gsa_agreement;
            }

            $data['message'] = 'Associate Updated';
            if (empty($associate_id)) {
                $this->associates->send_email_access_to_associate($this->input->post('name'), $this->input->post('username'), $password, $this->input->post('email'));
                $associate_record['password'] = md5(sha1(($password)));
                $data['message'] = 'Associate Created';
            }

            $associate_id = $this->associates->update_associate($associate_record, $associate_id);
        }

        $data['associate'] = $this->associates->get_associate($associate_id);
        $path_to_gsa_agreement = $data['associate']['path_to_gsa_agreement'];
        if (!empty($_POST)) {
            $data['associate'] = $_POST;
            $data['associate']['path_to_gsa_agreement'] = $path_to_gsa_agreement;
        }
        if (!empty($associate_id)) {
            $data['associate']['id'] = $associate_id;
        }
        $data['countries'] = $this->countries->get_all_countries();
        $header['selected_menu'] = 'associates';
        $header['page_name'] = 'Edit Associate';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            anchor('/associates/', 'Associates'),
            'Edit Associate'
        );

        $this->load->view('parts/header', $header);
        $this->load->view('associates/edit', $data);
        $this->load->view('footer');

    }

    public function search_replaced_associates($case_number, $country_id, $associate_id)
    {

        $this->load->model('associates_model', 'associates');
        $associates = $this->associates->get_all_associates_for_search($case_number);
        $associates_array = array();
        if (($associates)) {
            foreach ($associates as $associate) {
                if ($associate['name']) {
                    $associates_array[] = array('id' => $associate['id'], 'name' => $associate['name'].' ('.$associate['country'].')');

                }
            }
        }
        // var_dump($associates_array);exit;
        $data['case_number'] = $case_number;
        $data['country_id'] = $country_id;
        $data['associate_id'] = $associate_id;
        $data['associates'] = $associates_array;
        $this->load->view('associates/search_replaced_associate', $data);
    }


    /**
     * Removes GSA from associate entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    associate ID
     * @return    void
     * */
    public function delete_gsa($associate_id = '')
    {
        $this->load->model('associates_model', 'associates');

        $this->associates->delete_gsa($associate_id);

        redirect('/associates/edit/' . $associate_id);
    }

    /**
     * View GSA from associate entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    associate ID
     * @return    void
     * */
    public function view_gsa($associate_id)
    {
        $this->load->model('associates_model', 'associates');
        if (!is_null($associate = $this->associates->get_associate($associate_id))) {
            header('Content-type: ' . $associate['gsa_filetype']);
            header('Content-Disposition: attachment; filename="gsa_agreement.pdf"');
            readfile($associate['path_to_gsa_agreement']);
        }
    }
}

/* End of file associates.php */
/* Location: ./application/controllers/associates.php */