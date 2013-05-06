<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Request extends CI_Controller
{

    protected function getCommonCountries($type, $parent_case_id = '')
    {
        $this->load->model('countries_model', 'countries');
        $out = array();
        $common_countries = $this->countries->get_common_countries($type, $parent_case_id);

        if (check_array($common_countries)) {
            foreach ($common_countries as $country)
            {
                $out[] = array(
                    'country_id' => $country['id'],
                    'country' => $country['country'],
                    'flag_image' => $country['flag_image'],
                    'code' => strtolower($country['code']),
                );
            }
        }
        return $out;
    }

    function __construct()
    {
        parent::__construct();

        if (!$this->app_security_model->check_session()) {
            redirect('/');
        }
        // assets
        $applicationFormStyles = array(
            // zenfile css
            'application_form.zenfile.css' => 'application_form.zenfile.css'
        );
        add_assets($applicationFormStyles, 'page');

        $applicationFormScripts = array(
            'fileuploader.js' => 'fileuploader.js',
            'swfupload.js' => 'swfupload.js',
            'swfupload.cookies.js' => 'swfupload.cookies.js',
            'handlers.swfupload.js' => 'handlers.swfupload.js',
            'application_form.zenfile.js' => 'application_form.zenfile.js',
        );
        add_assets($applicationFormScripts, 'page');
    }

    public function index($case_type = 'case', $case_number = '')
    {
        $this->load->model('cases_model', 'cases');
        $this->load->model('countries_model');
        // file types
        $data['fileTypes'] = $this->cases->getFileTypes($file_type = 'client_upload_type');
        // case types
        $data['caseTypes'] = $this->cases->get_case_types();
        $data['case'] = $case = $this->cases->find_case_by_number($case_number);
        $data['isEstimate'] = 0;
        $estimates_statuses = array('estimating', 'pending-approval');
        if (in_array($data['case']['common_status'], $estimates_statuses) || $case_type == 'estimate') {
            $case_type = 'estimate';
            $data['isEstimate'] = 1;
        }

        $data['all_countries'] = $this->countries_model->get_all_countries();
//        var_dump($data['all_countries']);

        $data['case_type'] = isset($case['case_type_id']) ? $case['case_type_id'] : '';
        $data['application_number'] = isset($case['application_number']) ? $case['application_number'] : '';
        $data['application_title'] = isset($case['application_title']) ? $case['application_title'] : '';
        $data['applicant'] = isset($case['applicant']) ? $case['applicant'] : '';
        $data['filing_deadline'] = isset($case['filing_deadline']) ? $case['filing_deadline'] : '';
        $data['reference_number'] = isset($case['reference_number']) ? $case['reference_number'] : '';
        $data['notification_each_time'] = isset($case['notification_each_time']) ? $case['notification_each_time'] : '';
        $data['additional_contacts'] = $this->cases->get_case_contacts($case['id']);
        $data['filing_deadline_30'] = isset($case['30_month_filing_deadline']) ? $case['30_month_filing_deadline'] : '';
        $data['special_instructions'] = '';

        $parent_case_data = $this->cases->getParentCaseId($case['id']);
        if ($parent_case_data) {
            $data['parent_case'] = $parent_case_data[0]->parent_case_id;
        } else {
            $data['parent_case'] = '';
        }
        // intake or estimate
        if (isset($case['is_intake']))
            $data['isIntake'] = $case['is_intake'];
        else
        {
            if ($case_type == 'case')
                $data['isIntake'] = 1;
            elseif ($case_type == 'estimate')
                $data['isIntake'] = 0;
        }
        $data['direct_filing_checked'] = '';
        $data['ep_checked'] = '';
        $data['pct_checked'] = '';

        if ($data['parent_case']) {
            switch ($case['case_type_id'])
            {
                case '1':
                    $parent_type = 'pct';
                    break;
                case '2':
                    $parent_type = 'ep-validation';
                    break;
                case '3':
                    $parent_type = 'direct-filing';
                    break;
                default:
                    $parent_type = 'pct';
                    break;
            }
            $RELATED_COUNTRIES = $this->getCommonCountries($parent_type, $data['parent_case']);

            $data['common_countries'] = array(
                'direct' => $RELATED_COUNTRIES,
                'ep' => $RELATED_COUNTRIES,
                'pct' => $RELATED_COUNTRIES
            );
        }
        else {
            $data['common_countries'] = array(
                'direct' => $this->getCommonCountries('direct-filing'),
                'ep' => $this->getCommonCountries('ep-validation'),
                'pct' => $this->getCommonCountries('pct')
            );
        }

        $data['selected_countries'] = array();

        switch ($case['case_type_id'])
        {
            case '1':
                $data['pct_checked'] = 'checked';
                break;
            case '2':
                $data['ep_checked'] = 'checked';
                break;
            case '3':
                $data['direct_filing_checked'] = 'checked';
                break;
        }
            add_assets(array(
                site_url('countries/json_static_countries') => 'application_form.zenfile.js',
            ), 'raw');

        if ($case_type == 'case') {
            if ($data['parent_case']) {
                $data['_TEMPLATE']['title'] = 'Related Case';
                $data['page_name'] = 'Related Case';
            }
            else
            {
                $data['_TEMPLATE']['title'] = 'New Case';
                $data['page_name'] = 'New Case';
            }
        }
        elseif ($case_type == 'estimate')
        {
            if ($data['parent_case']) {
                $data['_TEMPLATE']['title'] = 'Related Estimate';
                $data['page_name'] = 'Related Estimate';
            }
            else
            {
                $data['_TEMPLATE']['title'] = 'New Estimate';
                $data['page_name'] = 'New Estimate';

            }
        }

        $this->load->vars($data);
        $this->load->view('parts/header');
        if ($data['parent_case'])
            $this->load->view('request/related');
        else
            $this->load->view('request/case');
        $this->load->view('parts/footer');
    }

    public function create_related_case($case_number = '')
    {
        $this->load->model('cases_model', 'cases');
        $data = $this->cases->create_related_case($case_number);
        redirect(base_url('/request/' . $data['case_type'] . '/' . $data['case_number']));
    }

    public function get_common_countries()
    {
        $type = $this->input->post('type');
        $result['common_countries'] = $this->getCommonCountries($type);
        echo json_encode($result);
    }

    public function save()
    {
        $user_id = $this->session->userdata('client_user_id');
        $this->load->model('cases_model', 'cases');
        $this->load->model('estimates_model', 'estimates');
        $app_num = trim($this->input->post('application_number'));
        if (!empty($app_num)) {
            $this->load->model('wipo_model', 'wipo');
            $result = $this->wipo->get_entry($app_num);
        }

        $addArray = array();
        $deadline = $this->input->post('deadline');
        if (!empty($deadline) && $deadline != 'N/A') {
            $addArray['filing_deadline'] = date('Y-m-d', strtotime($deadline));
            $addArray['30_month_filing_deadline'] = date('Y-m-d', strtotime($deadline));
            $date = new DateTime($deadline);
            $addArray['31_month_filing_deadline'] = $this->estimates->addMonths($date, '1');
        } else {
            $addArray['filing_deadline'] = NULL;
            $addArray['30_month_filing_deadline'] = NULL;
            $addArray['31_month_filing_deadline'] = NULL;
        }

        if (isset($result) && !empty($result)) {
            $addArray['first_priority_date'] = $result['first_priority_date'];
            $addArray['wipo_pct_number'] = $result['pct_number'];
            $addArray['wipo_wo_number'] = $result['wo_number'];
            $addArray['international_filing_date'] = $result['international_filing_date'];
            $addArray['number_priorities_claimed'] = $result['number_priorities_claimed'];
            $addArray['number_claims'] = $result['number_claims'];
            $addArray['number_pages_drawings'] = $result['number_pages_drawings'];
            $addArray['number_pages_claims'] = $result['number_pages_claims'];
            $addArray['number_pages'] = $result['number_pages'];
            $addArray['number_words'] = $result['number_words'];
            $addArray['number_words_in_claims'] = $result['number_words_in_claims'];
            $addArray['sequence_listing'] = $result['sequence_listing'];
            $addArray['search_location'] = $result['search_location'];
            $addArray['publication_language'] = $result['publication_language'];

        }
        $addArray['estimate_fee_level'] = $this->cases->get_last_user_fee_level($user_id);

        $parent_case = $this->input->post('parent_case');
        $success = FALSE;

        if (empty($parent_case)) {

            $case_number = $this->cases->generate_case_number();
            if ($this->cases->create_case($case_number, $addArray)) {

                $success = TRUE;
            }
        } else
        { /* RELATED CASE */
            $case_number = $this->input->post('case_number');
            $case_id = $this->input->post('case_id');

            if ($this->cases->update_case($case_id)) {
                $success = TRUE;
            }

        }
        if ($success) {
            if (!empty($parent_case)) {
                $this->cases->sendConfEmailToManager($case_number, '27');
            }
            $this->notify->setData(array('case_number' => $case_number));
            $this->notify->returnSuccess('Success');
        } else
        {
            $this->notify->returnError('There was an error');
        }
    }


    public function upload()
    {
        $config = array();
        $config['upload_path'] = APPPATH . '../../client/uploads/';
//        $config['allowed_types'] = 'zip|pdf|png|jpeg|jpg|doc|docx|xls|xlsx|tiff|txt|wpd|ppt|pptx|msg';
        $config['allowed_types'] = '*';
        $config['max_size'] = (1024 * 1024 * 100);
        $config['encrypt_name'] = FALSE;
        $config['stream_upload'] = true;
        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('qqfile')) {
            $response = array(
                'error' => $this->upload->display_errors(),
                'success' => FALSE,
                'attachment' => array()
            );
        } else {
            $data = $this->upload->data();
            if ($data['file_ext'] == '.zip') {
                if ($files = $this->unzip->extract($config['upload_path'] . '/' . $data['file_name'])) {
                    @unlink(realpath($config['upload_path']) . '/' . $data['file_name']);
                    $response = array(
                        'error' => '',
                        'success' => true,
                        'files' => array()
                    );
                    foreach ($files as $filepath) {
                        $fdata = pathinfo($filepath);
                        $response['files'][] = array(
                            'name' => $fdata['filename'],
                            'ext' => $fdata['extension'],
                            'file' => realpath($filepath),
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
                $response = array(
                    'error' => '',
                    'success' => true,
                    'attachment' => array(
                        'name' => $data['raw_name'],
                        'ext' => str_replace('.', '', $data['file_ext']),
                        'file' => $data['full_path'],
                    )
                );
            }
        }

        echo json_encode($response);
        die();
    }

    public function remove_uploaded() {
        $file = $this->input->post('file');
        $file = APPPATH . '../../client/uploads/' . pathinfo($file, PATHINFO_BASENAME);
        if (file_exists($file))
            unlink($file);
    }
}