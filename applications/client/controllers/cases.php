<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cases extends CI_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->app_security_model->check_session()) {
            if (is_ajax()) {
                echo 'Your session has timed out due to inactivity. Please log back in to continue.';
                exit();
            } else {
                redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
            }
        }

        $applicationFormScripts = array(
            'fileuploader.js' => 'fileuploader.js',
            'swfupload.js' => 'swfupload.js',
            'swfupload.cookies.js' => 'swfupload.cookies.js',
            'handlers.swfupload.js' => 'handlers.swfupload.js',
            'uploaders.zenfile.js' => 'uploaders.zenfile.js',
            'application_form.zenfile.js' => 'application_form.zenfile.js',
        );
        add_assets($applicationFormScripts, 'page');
    }

    function ajax_get_user_managers($user_id = 0)
    {

        $this->load->model('customers_model', 'customers');
        $result = array(
            'pm' => 'Unknown',
            'bdv' => 'Unknown'
        );
        if ($data = $this->customers->get_user_manager($user_id)) {
            $result['pm'] = $data['firstname'] . ' ' . $data['lastname'];
        }
        if ($data = $this->customers->get_sales_manager($user_id)) {
            $result['bdv'] = $data['firstname'] . ' ' . $data['lastname'];
        }

        die(json_encode($result));
    }

    public function ajax_send_support_email()
    {
        $this->load->model('customers_model', 'customers');
        $this->load->model('send_emails_model', 'send_emails');
        $user_id = $this->session->userdata('client_user_id');
        $text = htmlspecialchars($this->input->post('message'));
        $customer = $this->customers->get_user($user_id, 'customer');
        $level = $this->input->post('level');
        if (TEST_MODE) {
            $to[] = TEST_PM_EMAIL;
            $to[] = TEST_BDV_EMAIL;
        } else {
            if ($pm = $this->customers->get_user_manager($user_id)) {
                if ($pm['email']) $to[] = $pm['email'];
            }
            if ($bdv = $this->customers->get_sales_manager($user_id)) {
                if ($bdv['email']) $to[] = $bdv['email'];
            }
        }

        if (!count($to)) {
            $result = array('result' => 'fail');
        } else {
            $subject = "Support email. Urgency level - " . $level;

            if ($this->send_emails->send_email(false, $customer['email'], $subject, $text, $to, false, false)) {
                $result = array('result' => 'ok');
            } else {
                $result = array('result' => 'fail');
            }
        }
        die(json_encode($result));
    }

    /**
     * Create case form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string    case type
     * @return    void
     */
    public function create($type = '')
    {

        $this->load->model('countries_model', 'countries');
        $this->load->model('cases_model', 'cases');
        $this->session->set_userdata('current_case_id', '');
        $this->session->set_userdata('current_case_number', '');
        $this->session->set_userdata('current_case_type', $type);

        // Case Data
        $data['countries'] = $countries = $this->countries->get_countries_list_by_type($type, 1);
        $additional_countries = $this->countries->get_countries_list_by_type($type, 0);
        $data['case_countries'] = NULL;

        $countries_by_case_type_output = array();
        if (check_array($additional_countries)) {
            foreach ($additional_countries as $country) {
                $countries_by_case_type_output[] = array('id' => $country['id'], 'country' => $country['country']);
            }
        }
        $data['countries_by_case_type_output'] = $countries_by_case_type_output;

        $case_url = '/cases/create/' . $type;
        $this->session->set_userdata('current_case_url', $case_url);

        $allowed_types = array('pct', 'ep-validation', 'direct-filing');
        if (!in_array($type, $allowed_types)) {
            redirect('/dashboard/');
        } else {
            $data['files'] = NULL;
            $case_types_full = array(
                'pct' => 'PCT National Phase',
                'ep-validation' => 'EP Validation',
                'direct-filing' => 'Direct Filing'
            );
            $header['page_name'] = 'Create a Case';
            $header['breadcrumb'] = array(
                anchor('/dashboard/', 'Dashboard'),
                'Create a case',
                $case_types_full[$type]
            );
            $this->load->view('parts/header', $header);
            $this->load->view('cases/create/' . $type, $data);
            $this->load->view('parts/footer');
        }
    }

    public function set_file_type()
    {
        $this->load->model('cases_model', 'cases');
        $file_type_id = $this->input->post('file_type_id');
        $file_id = $this->input->post('file_id');
        $this->cases->set_file_type($file_id, $file_type_id);
    }


    public function upload($case_id = null)
    {
        $this->load->model('cases_model', 'cases');
        $userpath = '../pm/uploads/' . $this->session->userdata('client_user_id');
        if (!is_dir($userpath)) {
            mkdir($userpath, 0775);
        }
        $usercasepath = $userpath . '/' . $case_id;
        if (!is_dir($usercasepath)) {
            mkdir($usercasepath, 0775);
        }
        $pathToUpload = $usercasepath;
        $config = array();
        $config['upload_path'] = $pathToUpload;
        $config['allowed_types'] = '*';
        $config['max_size'] = (1024 * 1024 * 100);
        $config['encrypt_name'] = false;

        if ($this->input->get_post('uploader') == 'stream')
            $config['stream_upload'] = true;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('qqfile')) {
            $response = array(
                'error' => $this->upload->display_errors(),
                'success' => false,
                'attachment' => array()
            );
        } else {
            $data = $this->upload->data();
            if ($data['file_ext'] == '.zip') {
                if ($files = $this->unzip->extract($pathToUpload . '/' . $data['file_name'])) {
                    @unlink($pathToUpload . '/' . $data['file_name']);
                    $response = array(
                        'error' => '',
                        'success' => true,
                        'files' => array()
                    );
                    foreach ($files as $filepath) {
                        $fdata = pathinfo($filepath);
                        $file_id = $this->cases->assign_file_to_case(
                            $this->session->userdata('client_user_id'),
                            $case_id,
                            $fdata['basename'],
                            $filepath
                        );
                        $response['files'][] = array(
                            'name' => $fdata['filename'],
                            'ext' => $fdata['extension'],
                            'id' => $file_id,
                        );
                    }
                } else {
                    $response = array(
                        'error' => 'Unpack error',
                        'success' => false,
                        'attachment' => array()
                    );
                }
            } else {
                $last_insert = $this->cases->assign_file_to_case($this->session->userdata('client_user_id'), $case_id, $data['file_name'], $pathToUpload . '/' . $data['file_name']);
                $response = array(
                    'error' => '',
                    'success' => true,
                    'lastinsert' => $last_insert,
                    'attachment' => array(
                        'name' => $data['raw_name'],
                        'ext' => str_replace('.', '', $data['file_ext']),
                        'file' => $last_insert,
                    )
                );
            }
        }
        $case = $this->cases->find_case_by_number($case_id);

        if (isset($case)) {
            $this->cases->sendConfEmailToManager($case_id, '28');

        }

        echo json_encode($response);
        die();
    }


    public function remove_uploaded()
    {
        $lastinsert = $this->input->post('file');
        $this->db->select('*');
        $this->db->where('id', $lastinsert);
        $query = $this->db->get('cases_files');
        $fileinfo = $query->result();
        $filepath = $this->config->item('path_upload') . 'pm/' . $fileinfo['0']->location;
        $this->db->where('id', $lastinsert);
        $this->db->delete('cases_files');
        if (file_exists($filepath))
            unlink($filepath);
    }

    /**
     * Shows file
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string file hash from ID
     * @return    void
     */
    public function view_file($file_id)
    {
        $this->load->model('files_model', 'files');
        if (!is_null($file = $this->files->get_file_by_id($file_id))) {
            header('Content-type: ' . $file['mime_type']);
            header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
            readfile('../pm/' . $file['location']);
        }
    }

    public function create_zip($case_id)
    {
        $this->load->model('cases_model', 'cases');
        $success = $this->cases->create_zip($case_id, $this->uri->segment(4));
        if ($success) {
            header('Content-type: application/zip');
            header('Content-Disposition: attachment; filename="' . $case_id . '.zip"');
            readfile('uploads/tmp/' . $case_id . '.zip');
        } else {
            redirect('/cases/view/' . $case_id);
        }
    }

    /**
     * View case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     */


    public function view($case_number = '')
    {
        $this->load->model('cases_model', 'cases');
        $this->load->model('countries_model', 'countries');
        $this->load->model('associates_model', 'associates');
        $this->load->model('estimates_model');
        $this->load->model('files_model');

        $this->session->set_userdata('current_case_id', '');
        $this->session->set_userdata('current_case_number', '');
        $this->session->set_userdata('current_case_type', '');
        $this->session->set_userdata('current_case_url', '');

        $user_id = $this->session->userdata('client_user_id');
        $data['case_id'] = $case_number;

        // file types
        $data['fileTypes'] = $this->cases->getFileTypes($file_type = 'client_upload_active_case');

        if (!is_null($data['case'] = $case = $this->cases->find_case_by_number($case_number)) && ($data['case']["common_status"] != "hidden")) {

            $data['client_files'] = $this->cases->get_case_files($case['id'], $file_types = array(1, 3, 4, 7, 8, 9, 11, 12, 13, 15, 16, 18, 22, 17));
            $data['document_files'] = $this->cases->get_case_files($case['id'], $file_types = array(2, 10, 14));
            $data['filing_files'] = $this->cases->get_case_files($case['id'], $file_types = array(6));

            if (isset($data['parent_client_files']) && !is_null($data['client_files'])) {
                $data['client_files'] = array_merge($data['parent_client_files'], $data['client_files']);
            } else if (isset($data['parent_client_files'])) {
                $data['client_files'] = $data['parent_client_files'];
            }
            if (isset($data['parent_document_files']) && !is_null($data['document_files'])) {
                $data['document_files'] = array_merge($data['parent_document_files'], $data['document_files']);
            } else if (isset($data['parent_document_files'])) {
                $data['document_files'] = $data['parent_document_files'];
            }
            $case_types = array(
                '1' => 'pct',
                '2' => 'ep-validation',
                '3' => 'direct-filing'
            );
            $case_types_full = array(
                'pct' => 'PCT Intake',
                'ep-validation' => 'EP Validation',
                'direct-filing' => 'Direct Filing'
            );
            $header['page_name'] = 'Case ' . $case_number;
            $header['breadcrumb'] = array(
                anchor('/dashboard/', 'Dashboard'),
                'Cases',
                'Edit',
                $case['case_type'],
                $case_number
            );
            $header['subheader_message'] = 'Case ' . $case_number;

            $case_url = '/cases/view/' . $case['case_number'];
            $this->session->set_userdata('current_case_url', $case_url);
            $this->session->set_userdata('current_case_id', $case['id']);
            $this->session->set_userdata('current_case_number', $case['case_number']);
            $this->session->set_userdata('view_mode', TRUE);
            $type = $case_types[$case['case_type_id']];
            $data['type_label'] = $case_types_full[$type];
            $this->session->set_userdata('current_case_type', $type);

            // Case Data
            $data['countries'] = $countries = $this->countries->get_countries_list_by_type($type, 1);

            $additional_countries = $this->countries->get_countries_list_by_type($type, 0);
            // switching to common calculation

            $data['case_countries'] = $this->countries->get_case_countries($case['id'], false);
            if (!empty($data['case_countries'])) {
                foreach ($data['case_countries'] as $key => $country)
                {
                    $data['case_countries'][$key]['files'] = $this->cases->get_case_files_with_country_array($case['id'], $file_types = array(6), $country['id']);
                }
            }

            $data['countries_fees'] = $this->estimates_model->get_calculation_results($case_number, false, false);

            $countries_by_case_type_output = array();
            if (check_array($additional_countries)) {
                foreach ($additional_countries as $country) {
                    $countries_by_case_type_output[] = array('id' => $country['id'], 'country' => $country['country']);
                }
            }

            $data['is_have_files'] = $this->files_model->is_have_filing_report_files_with_assigned_countries($case['id']);





            $associates = $this->associates->new_get_all_case_associates($case['id'], '1');
            $data['case_associates'] = $associates;
            $data['related_cases'] = $this->cases->get_direct_related_cases($case['id']);



            $parent_case_data = $this->cases->getParentCaseId($case['id']);

            if ($parent_case_data) {
                $data['parent_case'] = $parent_case_data[0]->parent_case_id;
            } else {
                $data['parent_case'] = '';
            }
                add_assets(array(
                    site_url('countries/json_static_countries') => 'application_form.zenfile.js',
                ), 'raw');
            $data['common_countries'] = $this->countries->get_common_countries($type , $data['parent_case']);

            if ($data['parent_case']) {
                // it's done if parent case should not have button "create related case" , so related cases should not have this button too
                $id_for_search = $data['parent_case'];
            } else {
                $id_for_search = $case['id'];
            }

            $data['countries_for_related'] = $this->countries->get_common_countries($type , $id_for_search);



            $this->load->view('parts/header', $header);
            $this->load->view('cases/edit', $data);

            $this->load->view('parts/footer');
        } else {
            redirect("dashboard");
        }
    }

    function is_estimate_available_for_client_ajax()
    {

        $this->load->model('cases_model', 'cases');
        $case_number = $this->uri->segment(3);
        $data['is_available'] = $this->cases->is_estimate_available_for_client($case_number);
        echo json_encode($data['is_available']);

    }


    function ajax_send_notification_email()
    {

        $this->load->model('estimates_model', 'estimates');
        $this->load->model('cases_model', 'cases');
        $this->estimates->update_estimates_countries_fees(array('id' => $this->input->post('id'), 'past_deadline_notification_sent' => 1));
        $this->db->select('case_id,country_filing_deadline');
        $this->db->where('id', $this->input->post('id'));
        $query = $this->db->get('estimates_countries_fees');

        if ($query->num_rows()) {
            $case_id = $query->row_array();
            $case = $this->cases->get_case($case_id['case_id']);
            $this->cases->sendConfEmailToManager($case['case_number'], '32', false, $case_id['country_filing_deadline']);
        }
    }


    /**
     * Submits "approve estimate" form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     */
    public function approve_estimate_form_submit($case_number = '')
    {
        $this->load->model('cases_model', 'cases');
        $this->load->model('associates_model', 'associates');
        $countries = $this->input->post('approved_countries');
        $this->cases->approve_estimate_countries($case_number);

        // Send email to PM
        if ($this->input->post('reestimate') == '1') {
            $template = '30';
        } else {
            $template = '29';
            $countries = array_unique($countries);
            $this->associates->add_new_associates_to_case($case_number, $countries, '1');
        }

        $this->cases->sendConfEmailToManager($case_number, $template, '1');

    }

    function test_parsing() {
        $this->db->truncate('zen_wipo_data');
        $this->load->model('wipo_model');

        $data = $this->wipo_model->light_parser('PCT/US2011/053015');

    }

}

/* End of file dashboard.php */
/* Location: ./application/controllers/dashboard.php */
