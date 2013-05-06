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
            }
            else
            {
              //  redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
            }
        }
    }

    public function send_notification_to_supervisor($case_number = '')
    {
        if ($this->input->post('message_type') == 'escalate_esimate') {
            $action = 'Change Estimate';
        } elseif ($this->input->post('message_type') == 'escalate_case_info') {
            $action = 'Change Case Info';
        } else {
            $action = 'Try to approve/deny';
        }
        $this->load->model('customers_model', 'customers');
        $this->load->model('send_emails_model', 'send_emails');
        $manager_id = $this->session->userdata('manager_user_id');
        $manager = $this->customers->get_user($manager_id, 'manager');
        $back['result'] = 'no';
        $supervisor = $this->customers->get_user($manager['supervisor_id'], 'manager');
        $from = 'case' . $case_number . $this->config->item('default_email_box');
        $TEMPLATE = $this->db->get_where("zen_emails_templates", array("id" => '31'))->row_array();
        if ($TEMPLATE) {
            $TEMPLATE["subject"] = str_replace("%CASE_NUMBER%", $case_number, $TEMPLATE["subject"]);
            $TEMPLATE["content"] = str_replace("%CASE_NUMBER%", $case_number, $TEMPLATE["content"]);
            $TEMPLATE["subject"] = str_replace("%PM_ACTION%", $action, $TEMPLATE["subject"]);
            $TEMPLATE["content"] = str_replace("%PM_ACTION%", $action, $TEMPLATE["content"]);
            if ($manager && $supervisor) {
                $TEMPLATE["subject"] = str_replace('%PM_FULLNAME%', $manager['firstname'] . " " . $manager['lastname'], $TEMPLATE["subject"]);
                $TEMPLATE["content"] = str_replace('%PM_FULLNAME%', $manager['firstname'] . " " . $manager['lastname'], $TEMPLATE["content"]);
                $TEMPLATE["subject"] = str_replace('%SUPERVISOR_FULLNAME%', $supervisor['firstname'] . " " . $supervisor['lastname'], $TEMPLATE["subject"]);
                $TEMPLATE["content"] = str_replace('%SUPERVISOR_FULLNAME%', $supervisor['firstname'] . " " . $supervisor['lastname'], $TEMPLATE["content"]);
                $to = $supervisor["email"];
                $cc[] = $manager["email"];
                if (TEST_MODE) {
                    $to = TEST_SUPERVISOR_EMAIL;
                    $cc[] = TEST_PM_EMAIL;
                }
            }
            if ($this->send_emails->send_email($from, $from, $TEMPLATE["subject"], $TEMPLATE["content"], $to, $cc, false)) {
                $back['result'] = 'ok';
            }
        }
        echo json_encode($back);
    }

    /**
     * Updates case entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    public function update($case_number = '')
    {

        $this->load->model('cases_model', 'cases');
        $this->cases->update_case($case_number);
        redirect('/cases/view/' . $case_number);
    }

    function ajax_update_fees_for_all_users()
    {
        if (!$this->input->is_ajax_request()) {
            return false;
        }
        $this->load->model('cases_model', 'cases');
        $case_type_id = $this->input->post('case_type_id');
        $country_id = $this->input->post('country_id');
        $this->cases->reload_customer_fees(false, $case_type_id, $country_id);
    }

    function ajax_is_associates_visible_to_client()
    {
        $this->load->model('cases_model', 'cases');
        $case_id = intval($this->uri->segment(3));
        $is_visible = $this->input->post('is_associates_visible_to_client');
        $this->cases->make_associates_visible_to_client($case_id, $is_visible);
    }

    public function fc_send_email($case_id = "", $country_id = "")
    {
        $this->load->library('table');
        $this->load->model('cases_model', 'cases');
        $this->load->model('customers_model', 'customers');
        $this->load->model('countries_model', 'countries');
        $customer = $this->cases->get_customer_by_case_number($case_id);
        $case = $this->cases->find_case_by_number($case_id);
        $country = $this->countries->get_country($country_id);
        $contact_list = $this->cases->get_contacts_of_the_case(array('case_id' => $case['id']));
        $contacts = '';
        foreach ($contact_list as $contact) {
            if (!empty($contact->email)) {
                $contacts .= $contact->email . ', ';
            }
        }
        if (!empty($contacts)) {
            $contacts = substr($contacts, 0, -2);
        }
        $this->db->where('case_id', $case['id']);
        $this->db->where('country_id', $country_id);
        $query = $this->db->get('cases_tracker');
        if ($query->num_rows()) {
            $result = $query->row_array();
            $translation = $result['translation_required'];
        } else {
            $translation = '';
        }
        $country_files = $this->cases->get_case_files_by_country($case_id, $country_id, array(6));
        $data = array(
            'customer' => $customer,
            'country' => $country,
            'country_files' => $country_files,
            'case' => $case,
            'contacts' => $contacts,
            'translation' => $translation
        );
        $this->load->view('cases/fc_send_email', $data);
    }

    /**
     * View case entry. Huge function!
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    voidw
     * */

    public function view($case_number = '')
    {
        $this->load->library('table');
        $this->load->model('cases_model', 'cases');
        $this->load->model('customers_model', 'customers');
        $this->load->model('currencies_model', 'currencies');
        $this->load->model('fees_model', 'fees');
        $this->load->model('estimates_model', 'estimates');
        $this->load->model('emails_model', 'emails');
        $this->load->model('associates_model', 'associates');
        $this->load->helper('common_helper');
        $header['page_name'] = 'Case ' . $case_number;
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Cases',
            $case_number
        );
        $header['subheader_message'] = 'Case ' . $case_number;
        $case = $this->cases->find_case_by_number($case_number);
        $this->estimates->save_customer_data_for_case($case['id']);
        $customer = $this->cases->get_customer_by_case_number($case_number);

        if (!empty($customer)) {
            $customer_bdv_id = $this->customers->get_bdv_for_customer($customer['id']);
            $customer['count_estimates'] = $this->customers->get_cases_count($customer['id'], TRUE);
            $customer['count_intakes'] = $this->customers->get_cases_count($customer['id'], FALSE);
            $contacts = $this->cases->get_contacts_of_the_case(array('case_id' => $case['id']));

            $data['contacts'] = '';
            foreach ($contacts as $contact) {
                $data['contacts'] .= $contact->email . ', ';
            }
            if ($case['common_status'] == 'estimating' || $case['common_status'] == 'estimating-estimate' || $case['common_status'] == 'estimating-reestimate' || $case['common_status'] == 'pending-intake' || $case['common_status'] == 'hidden') {
                //                $this->estimates->update_fees_of_case($case_number);
            }
            if ($case['case_type_id'] == 1) {
                if (!empty($case_number['wipo_wo_number']) && !empty($case['wipo_pct_number'])) {
                    $data['match_with_existant_case'] = $this->cases->get_match_with_excistant_case_by_pct_wo($case['id'], $case['wipo_wo_number'], $case['wipo_pct_number']);
                } else {
                    $data['match_with_existant_case'] = false;
                }
            } else {
                $data['match_with_existant_case'] = $this->cases->get_match_with_excistant_case_by_application_number($case['id'], $case['application_number']);
            }

            if ($this->input->post('invoice_edit')) {
                $this->_edit_invoice();
            }

            $data['contacts'] = substr($data['contacts'], 0, -2);
            $files_countries = $this->cases->get_files_countries($case['id']);
            $common_footnotes = $this->estimates->get_common_footnotes();
            $estimate_footnotes = $this->estimates->get_estimate_footnotes($case['id']);
            $files_countries_output = array();
            if (check_array($files_countries)) {
                foreach ($files_countries as $key => $record)
                {
                    $files_countries_output[$record['file_id']][] = trim($record['code']);
                }
            }
            $case_types = $this->cases->get_case_types();
            $case_types_output = array();
            if (check_array($case_types)) {
                foreach ($case_types as $key => $record)
                {
                    if ($record["id"] != "4") /* Hide for now case_status -  Translate Only */
                        $case_types_output[$record['id']] = trim($record['type']);
                }
            }
            $countries_by_case_type_output = array();
            $countries_by_case_type = $this->cases->get_countries_by_case_type($case_number);
            if (check_array($countries_by_case_type)) {
                foreach ($countries_by_case_type as $country)
                {
                    $countries_by_case_type_output[] = array('id' => $country['id'], 'country' => $country['country']);
                }
            }

            $countries = $this->cases->get_case_countries($case_number);
            $fees = $this->fees->get_fees_by_countries($countries);
            $euro_exchange_rate = $this->currencies->get_currency_rate_by_code('EUR');
            $associates = $this->associates->new_get_all_case_associates($case['id']);
            // var_dump($associates);exit;

            $file_types = $this->cases->file_types($file_types = '1');
            $case_notes = $this->cases->case_notes($case_number);
            $client_notes = $this->cases->client_notes($case['user_id']);
            $sales_managers = $this->cases->get_sales_managers();
            $customer_countries = $this->cases->get_customer_countries($case['user_id'], $case['case_type_id']);
            // References number for case's associates

            $estimate_table = $this->estimates->get_estimate_table($case_number);
            $sales_managers_output = array('' => '');
            if (check_array($sales_managers)) {
                foreach ($sales_managers as $sale_manager)
                {
                    $sales_managers_output[$sale_manager['id']] = $sale_manager['firstname'] . ' ' . $sale_manager['lastname'];
                }
            }
            $managers = $this->customers->get_managers();
            $manager_id = array('0' => 'Select Manager');
            foreach ($managers as $manager) {
                $manager_id[$manager['id']] = $manager['firstname'] . ' ' . $manager['lastname'];
            }
            $data['managers'] = $manager_id;

            // Direct Link to WIPO site
            $wipo_direct_link = '';
            $wipo_direct_link_title = $case['application_number'];
            if (!empty($case['wipo_wo_number'])) {
                $wipo_direct_link_url = 'http://www.wipo.int/patentscope/search/en/' . str_replace('/', '', $case['wipo_wo_number']);
                $wipo_direct_link = anchor_popup($wipo_direct_link_url, $wipo_direct_link_title);
            }
            elseif (!empty($case['wipo_pct_number']))
            {
                $wipo_direct_link_url = 'http://www.wipo.int/patentscope/search/en/' . str_replace('/', '', $case['wipo_pct_number']);
                $wipo_direct_link = anchor_popup($wipo_direct_link_url, $wipo_direct_link_title);
            }

            if (!is_null($bdv = $this->customers->get_managers($case['sales_manager_id']))) {
                $estimate = $this->estimates->get_last_estimate_pdf($case['id']);
                $data['estimate'] = $estimate;
                $data['bdv'] = $bdv;
                $data['case'] = $case;
                $email_template = $this->emails->get_ready_email_from_template(18, $case['id']);
                $data['email_subject'] = $email_template['subject'];
                $data['email_text'] = $email_template['text'];
            }
            $data['list_estimate_countries'] = $this->cases->get_list_estimate_countries($case['id'], $case['user_id']);
            $data['estimate_table'] = $estimate_table;
            $data['filing_countries'] = $this->cases->get_case_countries($case['case_number']);
            $data['list_estimate_countries'] = $this->cases->get_list_estimate_countries($case['id'], $case['user_id']);
            $case_file_types = $this->cases->get_file_types();
            $data['files'] = $this->cases->get_case_files($case['id'], array_merge($case_file_types[1], $case_file_types[4]));
            $data['document_files'] = $this->cases->get_case_files($case['id'], $case_file_types[2]);
            $data['signed_document_files'] = $this->cases->get_case_files($case['id'], $case_file_types[3]);

            //print_r($data['files']);die;
            if ($data['filing_countries']) {
                foreach ($data['filing_countries'] as $key => $country) {
                    $res = $this->cases->get_tracker($case['id'], $country['id']);
                    $tracker[$country['id']] = $res[0];

                    $data['filing_countries'][$key]['files'] = $this->cases->get_case_files_with_country_array($case['id'], $case_file_types[4], $country['id']);
                }
                $data['tracker'] = $tracker;
            }

            $countries_fees = $this->estimates_model->get_calculation_results($case['case_number'] , false , true , false , true);

            foreach($countries_fees['countries'] as $country_fee) {
                if(!isset($result[$country_fee['country_id']]['additional_summ'])) {
                    $data['filing_cost_result'][$country_fee['country_id']]['additional_summ'] = 0;
                }

                if($country_fee['parent_id'] != 0) {
                    $data['filing_cost_result'][$country_fee['country_id']]['additional_summ'] += $country_fee['result_official_fee'] + $country_fee['result_filing_fee'];
                    continue;
                }

                $data['filing_cost_result'][$country_fee['country_id']]['result_official_fee'] = $country_fee['result_official_fee'];
                $data['filing_cost_result'][$country_fee['country_id']]['result_filing_fee'] = $country_fee['result_filing_fee'];
            }



            $data['wipo_direct_link'] = $wipo_direct_link;
            $data['euro_exchange_rate'] = $euro_exchange_rate;
            $data['fees'] = $fees;
            $data['sales_managers_output'] = $sales_managers_output;
            $data['case_notes'] = $case_notes;
            $data['client_notes'] = $client_notes;
            $data['fa_notes'] = $this->cases->fa_case_notes($case_number);
            //var_dump($data['fa_notes']);exit;
            $data['customer'] = $customer;
            $data['customer_bdv_id'] = $customer_bdv_id;
            $data['case'] = $case;
            $data['common_footnotes'] = $common_footnotes;
            $data['estimate_footnotes'] = $estimate_footnotes;
            $data['case_types_output'] = $case_types_output;
            $data['countries_by_case_type_output'] = $countries_by_case_type_output;
            $data['files_countries_output'] = $files_countries_output;
            $data['countries'] = $countries;
            $data['estimate_countries'] = $estimate_countries = NULL;
            $data['customer_countries'] = $customer_countries;
            $data['invoices'] = $this->estimates_model->get_assigned_associates_invoices($case['id']);
            $data['associates'] = $associates;
            $data['file_types'] = $file_types;
            $data['related_cases'] = $this->cases->get_direct_related_cases($case['id']);
            $data['is_related'] = $this->cases->is_related_case($case['id']);
            $this->benchmark->mark('before_output');
            $this->load->view('parts/header', $header);
            $this->load->view('cases/edit', $data);
            $this->load->view('parts/footer');
        }
        else {
            redirect("dashboard");
        }
    }

    /**
     * Upload file. For AJAX
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @return    void
     * */
    public function upload_file($case_id) {

        ini_set('max_execution_time', '120');
        ini_set('memory_limit', '1024M');
        ini_set('post_max_size', '100M');
        ini_set('upload_max_filesize', '100M');
        $this->load->helper('qq_uploader');
        $this->load->model('customers_model', 'customers');
        $this->load->model('cases_model', 'cases');
        $qq_file = $this->input->get('qqFile');
        $user_id = $this->input->get('customer_id');
        $file_type_id = $this->input->get('file_type_id');

        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'txt', 'zip', 'pdf', 'xls', 'xlsx', 'ppt', 'pptx', 'doc', 'docx', 'msg', 'rtf');
        // max file size in bytes
        $size_limit = 100 * 1024 * 1024;
        $uploader = new qqFileUploader($allowed_extensions, $size_limit, $qq_file);

        $case_number = $this->input->get('case_number');
        $dir = 'uploads/' . $user_id . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0775);
        }
        $dir = 'uploads/' . $user_id . '/' . $case_number . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0775);
        }
        $result = $uploader->handleUpload($dir);

        $result['case_number'] = $case_number;
        $result['multiple'] = '0';
        if (isset($result['success'])) {
            if (isset($result['zip'])) {
                $files_indexes = $this->cases->assign_files_to_case($case_id, $result['files'], $file_type_id, $user_id);
                $result['multiple'] = '1';
            }
            else
            {
                $result['file_id'] = $this->cases->assign_file_to_case($case_id, $result['file'], $result['filepath'], $file_type_id, $user_id);
            }
        }
        $file_types = $this->cases->file_types();
        $file_types_dd = array('' => '');
        if (check_array($file_types)) {
            foreach ($file_types as $file_type)
            {
                $file_types_dd[$file_type['id']] = $file_type['name'];
            }
        }
        unset($file_types_dd['20']);
        $result['class_hash'] = uniqid();
        if ($result['multiple'] == '1') {
            foreach ($files_indexes as $file_id => $filename)
            {
                $fdata = pathinfo($filename);
                $new_item = array();
                $new_item['file_id'] = $file_id;
                $new_item['visibility'] = form_checkbox('visibility', '1', 0, 'class="file_visibility" id="' . $file_id . '"');
                $new_item['file'] = '<div class="file_control">
										<input type="checkbox" name="case_files[]" value="' . $file_id . '">
									</div>
									<div class="dl_icon"><a href="' . base_url() . 'cases/view_file/' . $file_id . '"></a></div>
									<div class="fname">
										<span id="' . $file_id . '" class="filename">' . $filename . '</span><input id="inp' . $file_id . '" class="filename_input" type="text" style="display: none;" value="' . $fdata['filename'] . '" name="filename">' . form_hidden('ext' . $file_id, $fdata['extension']) . '
										<a id="rename_' . $file_id . '" class="rename_link_ok" style="display: none;" href="javascript:void(0);">OK</a>
										<a id="cancel_' . $file_id . '" class="rename_link_cancel" style="display: none;" href="javascript:void(0);">Cancel</a>
									</div>';
                $new_item['view_link'] = anchor('/cases/view_file/' . $file_id, '<img src="' . base_url() . 'assets/images/i/eye-14-14.png" alt="View"/>');
                $new_item['delete_link'] = '<a href="javascript:void(0);" id="delete_link_' . $file_id . '" onclick="if(confirm(\'Do you really want to delete selected file?\')){ remove_file(' . $file_id . ');}"><img src="' . base_url() . 'assets/images/i/delete.png" alt="Remove"/></a>';
                $new_item['assign_to_countries_link'] = '<a href="' . base_url() . 'cases/assign_file_to_countries_form/' . $file_id . '/' . $case_number . '" class="' . $result['class_hash'] . ' popup">Assign to countries</a>';
                $new_item['file_type_dropdown'] = form_dropdown('file_type', $file_types_dd, $file_type_id, 'class="file_type" id="ft' . $file_id . '" autocomplete="off"');
                $new_item['send_file_link'] = '';
                $result['rows'][] = $new_item;
            }
            $result['indexes'] = $files_indexes;
            $result['count'] = count($files_indexes);
        }
        else
        {
            $fdata = pathinfo($result['file']);
            $result['file'] = '<div class="file_control">
									<input type="checkbox" name="case_files[]" value="' . $result['file_id'] . '">
								</div>
								<div class="dl_icon"><a href="' . base_url() . 'cases/view_file/' . $result['file_id'] . '"></a></div>
								<div class="fname">
									<span id="' . $result['file_id'] . '" class="filename">' . $result['file'] . '</span><input id="inp' . $result['file_id'] . '" class="filename_input" type="text" style="display: none;" value="' . $fdata['filename'] . '" name="filename">' . form_hidden('ext' . $result['file_id'], $fdata['extension']) . '
									<a id="rename_' . $result['file_id'] . '" class="rename_link_ok" style="display: none;" href="javascript:void(0);">OK</a>
									<a id="cancel_' . $result['file_id'] . '" class="rename_link_cancel" style="display: none;" href="javascript:void(0);">Cancel</a>
								</div>';
            $result['visibility'] = form_checkbox('visibility', '1', 0, 'class="file_visibility" id="' . $result['file_id'] . '"');
            $result['view_link'] = anchor('/cases/view_file/' . $result['file_id'], '<img src="' . base_url() . 'assets/images/i/eye-14-14.png" alt="View"/>');
            $result['delete_link'] = '<a href="javascript:void(0);" id="delete_link_' . $result['file_id'] . '" onclick="if(confirm(\'Do you really want to delete selected file?\')){ remove_file(' . $result['file_id'] . '); }"><img src="' . base_url() . 'assets/images/i/delete.png" alt="Remove"/></a>';
            $result['assign_to_countries_link'] = '<a href="' . base_url() . 'cases/assign_file_to_countries_form/' . $result['file_id'] . '/' . $case_number . '" class="' . $result['class_hash'] . ' popup">Assign to countries</a>';
            $result['file_type_dropdown'] = form_dropdown('file_type', $file_types_dd, $file_type_id, 'class="file_type" id="ft' . $result['file_id'] . '" autocomplete="off"');
            $result['send_file_link'] = '';
        }
        // to pass data through iframe you will need to encode all html tags
        header("content-type: application/json; charset=utf8");
        echo json_encode($result);
    }

    /**
     * Views file
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    file ID
     * @return    void
     * */
    public function view_file($file_id)
    {
        $this->load->model('cases_model', 'cases');
        if (!is_null($file = $this->cases->get_file($file_id))) {
            header('Content-type: ' . $file['mime_type']);
            header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
            readfile($file['location']);
        }
    }

    /**
     * Removes file
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    file ID
     * @param    int    case number
     * @return    void
     * */
    public function remove_file($file_id = '', $case_number = '')
    {
        $this->load->model('cases_model', 'cases');

        if ($this->input->is_ajax_request()) {
            $file_id = $this->input->post('file_id');
        }
        if (!is_null($file = $this->cases->get_file($file_id))) {
            if ($this->cases->remove_file($file_id)) {
                @unlink($file['location']);
            }
        }

        if (!$this->input->is_ajax_request()) {
            redirect('/cases/view/' . $case_number);
        }
    }

    /**
     * Intake form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    public function intake($case_number = '')
    {
        $this->load->library('table');
        $this->load->model('emails_model', 'emails');
        $this->load->model('customers_model', 'customers');
        $this->load->model('cases_model', 'cases');
        $this->load->model('files_model', 'files');
        // Send email to client
        if (!is_null($case = $this->cases->find_case_by_number($case_number))) {
            // Customer data
            if (!is_null($customer = $this->cases->get_customer_by_case_number($case_number))) {
                $template_id = 0;
                // PCT National Phase
                if ($case['case_type_id'] == '1') {
                    $template_id = 4;
                }
                // EP Validation
                elseif ($case['case_type_id'] == '2')
                {
                $template_id = 3;
            }
                // Direct Filing
                elseif ($case['case_type_id'] == '3')
                {
                    $template_id = 2;
                }
                $email_content = $this->emails->get_ready_email_from_template($template_id, $case['id']);

                $data['cc'] = '';
                $cc = array();
                if ($contacts = $this->customers->get_case_contacts($case['id'])) {
                    foreach ($contacts as $contact) {
                        $cc[] = $contact['email'];
                    }
                }
                if (count($cc)) {
                    $data['cc'] = implode(', ', $cc);
                }
                $data['case'] = $case;
                $data['email_content'] = $email_content;
                $data['customer'] = $customer;
                $data['sow'] = $this->files->get_file_by_type($case['id'], FALSE, 8, TRUE);
                $header['page_name'] = 'Cases';
                $header['breadcrumb'] = array(
                    anchor('/dashboard/', 'Dashboard'),
                    'Cases',
                    'Intake a case',
                    $case['case_number']
                );
                $this->load->view('parts/header', $header);
                $this->load->view('cases/intake_form', $data);
                $this->load->view('parts/footer');
            }
        }
    }

    /**
     * Submits intake form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    public function submit_intake($case_number = '')
    {
        $this->load->model('cases_model', 'cases');
        $this->load->model('files_model', 'files');
        $this->load->model('countries_model', 'countries');
        $this->load->model('send_emails_model', 'send_emails');

        if (!is_null($case = $this->cases->find_case_by_number($case_number))) {
            $to = $this->input->post('to');
            if (!empty($to)) {
                $check_comma = substr($to, -1);
                if ($check_comma == ',' || $check_comma == ';') {
                    $to = substr($to, 0, -1);
                }
                if (strpos($to, ';') === FALSE) {
                    $send_to = explode(',', $to);
                } else
                {
                    $send_to = explode(';', $to);
                }
            }
            $cc = $this->input->post('cc');
            $subject = $this->input->post('subject');
            $email_content = $this->input->post('template_content');
            $manager_id = $this->session->userdata('manager_user_id');
            $customer = $this->cases->get_customer_by_case_number($case_number);
            $this->cases->assign_case_to_manager($case_number, $manager_id);
            $this->cases->change_case_status($case['id'], 'active');

            // Set favourite countries
            if (!is_null($case_countries = $this->cases->get_case_countries($case_number))) {
                $current_case_countries = array();
                foreach ($case_countries as $case_country)
                {
                    $current_case_countries[] = $case_country['id'];
                }
            }
            $from = 'case' . $case_number . $this->config->item('default_email_box');
            if (TEST_MODE) {
                $send_to = TEST_CLIENT_EMAIL;
                if ($customer['type'] == 'firm') {
                    $send_to = TEST_FIRM_EMAIL;
                }
            }
            $send_cc = '';
            if (!empty($cc)) {
                $check_comma = substr($cc, -1);
                if ($check_comma == ',' || $check_comma == ';') {
                    $cc = substr($cc, 0, -1);
                }
                if (strpos($cc, ';') === FALSE) {
                    $send_cc = explode(',', $cc);
                } else
                {
                    $send_cc = explode(';', $cc);
                }
            }
            $attachments = array();
            $temporary_files = $this->cases->get_case_files($case['id'], $file_types = array(20));
            if ($temporary_files) {
                $attachments = $temporary_files;
                foreach ($temporary_files as $file)
                {
                    $this->db->set('file_type_id', '21');
                    $this->db->where('id', $file['id']);
                    $this->db->update('cases_files');
                }
            }
            if ($attached_files = $this->input->post('attached_files')) {
                $files_data = $this->files->get_file_by_id($attached_files);
            }
            if (!empty($attachments) && !empty($files_data)) {
                $attachments = array_merge($files_data, $attachments);
            } elseif (!empty($files_data)) {
                $attachments = $files_data;
            }
            $file_id = $this->input->post('file_id');
            if (!is_null($sow = $this->files->get_file_by_id($file_id))) {
                array_push($attachments, $sow);
            }
            $this->send_emails->send_email($from, $from, $subject, $email_content, $send_to, $send_cc, $attachments);
            redirect('/cases/view/' . $case_number);
        }
    }

    /**
     * Assigns file to countries form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    file ID
     * @param    int    case number
     * @return    void
     * */
    public function assign_file_to_countries_form($file_id, $case_number)
    {
        $this->load->library('table');
        $this->load->model('cases_model', 'cases');
        $data['case_number'] = $case_number;
        $data['file'] = $this->cases->get_file($file_id);
        $data['file_countries'] = $this->cases->get_file_countries($file_id);
        $data['countries'] = $this->cases->get_case_countries($case_number);
        if (!is_null($case = $this->cases->find_case_by_number($case_number))) {
            $data['list_estimate_countries'] = $this->cases->get_list_estimate_countries($case['id'], $case['user_id']);
        }
        $this->load->view('cases/assign_file_to_countries', $data);
    }

    public function assign_files_to_countries_form($case_number)
    {
        $this->load->library('table');
        $this->load->model('cases_model', 'cases');
        $file_ids = $this->input->get('fids');
        $data['case_number'] = $case_number;
        $file_ids_array = explode(',', $file_ids);
        foreach ($file_ids_array as $file_id) {
            $data['files'][] = $this->cases->get_file($file_id);
        }
        $data['fids'] = $file_ids;
        $data['countries'] = $this->cases->get_case_countries($case_number);
        if (!is_null($case = $this->cases->find_case_by_number($case_number))) {
            $data['list_estimate_countries'] = $this->cases->get_list_estimate_countries($case['id'], $case['user_id']);
        }
        $this->load->view('cases/assign_files_to_countries', $data);
    }

    public function merge_associates_form($case_id){

        $associates = $this->input->get('associates');
        $this->load->model('associates_model', 'associates');
        $data['case_id'] = $case_id;
        $data['not_active_associates'] = $associates;
        $data['merge_associates'] = $this->associates->get_associates_for_merge($case_id, $associates);
        $this->load->view('associates/merge_associates_form', $data);

    }

    public function merge_associates($case_id){

        $this->load->model('associates_model', 'associates');
        $this->load->model('cases_model', 'cases');
        $main_associate = $this->input->post('main_associate');
        $not_active_associates =array_diff(explode(',', $this->input->post('not_active_associates')), array($main_associate));
        $this->associates->merge_associates($case_id, $main_associate, $not_active_associates);
        $case_number = $this->cases->get_case_number($case_id);
        redirect('/cases/view/' . $case_number['case_number']);
    }

    public function attach_files_from_case($case_id)
    {
        $this->load->model('cases_model', 'cases');
        $data['files'] = $this->cases->get_case_files($case_id);
        $data['file_types'] = $this->cases->file_types($file_types = '1');
        $this->load->view('cases/attach_files_from_case', $data);
    }

    public function assign_files_to_type_form($case_number)
    {
        $this->load->library('table');
        $this->load->model('cases_model', 'cases');
        $file_ids = $this->input->get('fids');
        $data['file_types'] = $this->cases->file_types($file_types = '1');
        $data['case_number'] = $case_number;
        $file_ids_array = explode(',', $file_ids);
        foreach ($file_ids_array as $file_id) {
            $data['files'][] = $this->cases->get_file($file_id);
        }
        $data['fids'] = $file_ids;
        $this->load->view('cases/assign_files_to_type', $data);
    }

    /**
     * Assigns file to countries (action)
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    file ID
     * @param    int    case number
     * @return    void
     * */
    public function assign_file_to_countries($file_id, $case_number)
    {
        $this->load->model('cases_model', 'cases');

        $this->cases->assign_file_to_countries($file_id);

        redirect('/cases/view/' . $case_number);
    }

    public function assign_files_to_countries()
    {
        $this->load->model('cases_model', 'cases');
        $file_ids = $this->input->post('fids');
        $file_ids_array = explode(',', $file_ids);
        foreach ($file_ids_array as $file_id) {
            $this->cases->assign_file_to_countries($file_id);
        }
        die();
    }

    public function get_files_table($case_number = '')
    {
        $this->load->model('cases_model', 'cases');
        $case = $this->cases->find_case_by_number($case_number);
        $files = $this->cases->get_case_files($case['id'], NULL, NULL);
        $files_countries = $this->cases->get_files_countries($case['id']);
        $file_types = $this->cases->file_types($file_types = '1');
        $files_countries_output = array();
        if (check_array($files_countries)) {
            foreach ($files_countries as $key => $record) {
                $files_countries_output[$record['file_id']][] = trim($record['code']);
            }
        }
        $case_types = $this->cases->get_case_types();
        $data = array(
            'files' => $files,
            'case_types' => $case_types,
            'file_types' => $file_types,
            'files_countries_output' => $files_countries_output,
            'case' => $case
        );
        $case_file_types = $this->cases->get_file_types();

        $data['filing_countries'] = $this->cases->get_case_countries($case['case_number']);
        if ($data['filing_countries']) {
            foreach ($data['filing_countries'] as $key => $country) {
                $data['filing_countries'][$key]['files'] = $this->cases->get_case_files_with_country_array($case['id'], $case_file_types[4], $country['id']);
            }
        }
        $data['list_estimate_countries'] = $this->cases->get_list_estimate_countries($case['id'], $case['user_id']);
        $data['files'] = $this->cases->get_case_files($case['id'], array_merge($case_file_types[1], $case_file_types[4]));
        $data['document_files'] = $this->cases->get_case_files($case['id'], $case_file_types[2]);
        $data['signed_document_files'] = $this->cases->get_case_files($case['id'], $case_file_types[3]);
        $this->load->view('/cases/case_files', $data);
    }

    /**
     * Form to replace associate
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @param    int    country ID
     * @return    void
     * */
    public function replace_associate_form()
    {
        $this->load->library('table');
        $this->load->model('countries_model', 'countries');
        $data['case_number'] = $this->input->post('case_number');
        $data['country_id'] = $this->input->post('country_id');
        $data["associcate_id"] = $this->input->post('associate_id');
        $data["is_replaced"] = $this->input->post('is_replaced');
        $data["is_edit"] = '0';
        $data['countries'] = $this->countries->get_all_countries();
        $this->load->view('/associates/create_replace_associate', $data);
    }

    public function create_associate_form($case_number, $country_id)
    {
        $this->load->library('table');
        $this->load->model('countries_model', 'countries');
        $data['case_number'] = $case_number;
        $data['country_id'] = $country_id;
        $data["associcate_id"] = '0';
        $data["is_replaced"] = '1';
        $data["is_edit"] = '0';
        $data['countries'] = $this->countries->get_all_countries();
        $this->load->view('/associates/create_replace_associate', $data);
    }

    /**
     * Replaces associate
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @param    int    country ID
     * @return    void
     * */
    public function create_replace_associate($case_number)
    {
        $this->load->model('cases_model', 'cases');
        $this->load->model('associates_model', 'associates');
        $case = $this->cases->find_case_by_number($case_number);
        $password = $this->associates->rand_string('6');
        $country_id = $this->input->post('country_id');
        $associcate_id = $this->input->post('associcate_id');
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
            'password' => md5(sha1(($password))),
            'is_replaced' => $this->input->post('is_replaced')
        );

        $is_edit = '';
        if($this->input->post('is_edit') == '1'){
        $is_edit =  $associcate_id;
        }else{
            if($case['case_type_id'] =='1'){
            $associate_record['30_months'] = '1';
            $associate_record['31_months'] = '1';
            }
            if($case['case_type_id'] =='2'){
            $associate_record['ep_validation'] = '1';
            }
            if($case['case_type_id']=='3'){
            $associate_record['is_direct_case_allowed'] = '1';
            }
        }
       // $this->associates->send_email_access_to_associate($this->input->post('name'), $this->input->post('username'), $password, $this->input->post('email'));
        $new_associate_id = $this->associates->update_associate($associate_record, $is_edit);
        $this->cases->replace_associate($case_number, $country_id, $associcate_id, $new_associate_id);

        redirect('/cases/view/' . $case_number);
    }

    /**
     * Removes custom associate
     *
     * @access    public
     * */
    public function replace_associate($case_number)
    {
        $this->load->model('cases_model', 'cases');
        $country_id = $this->input->post('country_id');
        $associcate_id = $this->input->post('associate_id');
        $new_associate_id = $this->input->post('new_associate_id');
        $this->cases->replace_associate($case_number, $country_id, $associcate_id, $new_associate_id);
        redirect('/cases/view/' . $case_number);
    }

    /**
     * Removes custom associate
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    custom associate ID
     * @param    int    case number
     * @return    void
     * */
    public function delete_custom_associate($custom_associate_id, $case_id, $case_number)
    {
        $this->load->model('cases_model', 'cases');
        $this->cases->delete_custom_associate($custom_associate_id, $case_id);
        redirect('/cases/view/' . $case_number);
    }

    /**
     * Edit custom associate form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    custom associate ID
     * @param    int    case number
     * @return    void
     * */
    public function edit_replaced_associate($associate_id, $case_number, $country_id)
    {
        $this->load->library('table');
        $this->load->model('cases_model', 'cases');
        $this->load->model('countries_model', 'countries');
        $data['countries'] = $this->countries->get_all_countries();
        if (!is_null($case = $this->cases->find_case_by_number($case_number))) {

            $this->db->where('case_id', $case['id']);
            $this->db->where('associate_id', $associate_id);
            $this->db->delete('cases_associates_data');
            $data['case_number'] = $case_number;
            $data['country_id'] = $country_id;
            $data["associcate_id"] = $associate_id;
            $data["is_replaced"] = '1';
            $data["is_edit"] = '1';
            $data['associate'] = $this->cases->get_associate_by_id($associate_id);
            $this->load->view('/associates/create_replace_associate', $data);
        }
    }

    /**
     * Updates custom associate
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    custom associate ID
     * @param    int    case number
     * @return    void
     * */
    public function update_custom_associate($custom_associate_id, $case_number)
    {
        $this->load->model('cases_model', 'cases');
        $this->cases->update_custom_associate($custom_associate_id, $case_number);
        redirect('/cases/view/' . $case_number);
    }

    /**
     * Sets file visibility. For AJAX
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function set_file_visibility()
    {
        $this->load->model('cases_model', 'cases');
        $visibility = $this->input->post('visibility');
        $file_id = $this->input->post('file_id');
        $this->cases->set_file_visibility($file_id, $visibility);
    }

    public function set_file_fa_visibility()
    {
        $this->load->model('cases_model', 'cases');
        $visibility = $this->input->post('visibility');
        $file_id = $this->input->post('file_id');
        $this->cases->set_file_fa_visibility($file_id, $visibility);
    }

    /**
     * Sets file type. For AJAX
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function set_file_type()
    {
        $this->load->model('cases_model', 'cases');
        $this->load->model('files_model', 'files');
        $file_type_id = $this->input->post('file_type_id');
        $file_id = $this->input->post('file_id');
        $has_countries = false;
        if (!is_array($file_id)) {
            $has_countries = $this->files->get_file_countries(substr($file_id, 2));
            $file_id = array(substr($file_id, 2));
        }
        foreach ($file_id as $file_id_item) {
            $this->cases->set_file_type($file_id_item, $file_type_id);
        }
        $parent_table = $this->input->post('parent_table');
        $block_num = $this->get_file_type_group($file_type_id);
        switch ($block_num) {
            case 1;
                $block_title = 'files_table';
                break;
            case 2;
                $block_title = 'documents';
                break;
            case 3;
                $block_title = 'signed_documents';
                break;
            case 4;
                if ($has_countries) {
                    $block_title = 'filling_confirmation_tbl';
                } else {
                    $block_title = $parent_table;
                }
                break;
            default:
                $block_title = false;
        }
        $data['file_id'] = str_replace('ft', '', $file_id);
        $file_types = $this->cases->get_file_types();
        if (in_array($file_type_id, $file_types[5])) {
            $data['need_to_assign'] = true;
        }
        if ($block_title == false) {
            $data['block_title'] = false;
        } else {
            $data['block_title'] = $block_title;
        }
        echo(json_encode($data));
    }

    /**
     * Find the number of file types block
     * @param int file_type_id
     * @access    public
     * @author    Semyon babshkin <next15@mail.ru>
     * @return    int - number of block
     * */
    public function get_file_type_group($file_type_id)
    {
        $case_file_types = $this->cases->get_file_types();
        foreach ($case_file_types as $num => $group) {
            if (in_array($file_type_id, $group)) {
                return $num;
            }
        }
        return false;
    }

    /**
     * Sets "estension needed" option
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function set_extension_needed()
    {
        $this->load->model('cases_model', 'cases');
        $case_id = $this->input->post('case_id');
        $country_id = $this->input->post('country_id');
        $extension_needed = $this->input->post('extension_needed');
        $this->cases->set_extension_needed($case_id, $country_id, $extension_needed);
    }

    /**
     * Renames file
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    file ID
     * @return    void
     * */
    public function rename_file($file_id = '')
    {
        $this->load->model('cases_model', 'cases');
        $this->cases->rename_file($file_id);
    }

    /**
     * Adds note for case. For AJAX
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    string
     * */
    public function add_note_for_case($case_number)
    {
        $this->load->model('cases_model', 'cases');
        echo $this->cases->add_note_for_case($case_number);
    }

    /**
     * Removes note from case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function remove_note_from_case()
    {
        $this->load->model('cases_model', 'cases');
        $note_id = $this->input->post('note_id');
        $this->cases->remove_note_from_case($note_id);
    }

    /**
     * Returns a list of files by email type. For AJAX
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    string
     * */
    public function get_list_files_by_email_type()
    {

        $this->load->model('cases_model', 'cases');
        $case = $this->cases->get_my_case_by_number($this->input->post('case_number'));
        if ($case['common_status'] == 'completed') {
            echo json_encode(array("attached_files" => "", "countries" => ""));
            exit;
        }
        echo json_encode($this->cases->get_list_files_by_email_type());

    }

    /**
     *  Returns email text. For AJAX
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    string
     * */
    public function get_email_text()
    {
        $this->load->model('cases_model', 'cases');
        $case = $this->cases->get_my_case_by_number($this->input->post('case_number'));
        if ($case['common_status'] == 'completed') {
            return false;
            exit;
        } else {
            echo $this->cases->get_email_text();
        }
    }

    /**
     *  Sends email. For AJAX
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    string
     * */
    public function send_email()
    {
        $this->load->model('cases_model', 'cases');
        echo $this->cases->send_email();
    }

    /**
     * Completes case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    public function complete($case_number)
    {
        $this->load->model('cases_model', 'cases');

        $this->cases->complete_case($case_number);

        redirect('/cases/view/' . $case_number);
    }

    /**
     * Sends notification email
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function send_notification_email()
    {
        $this->load->model('cases_model', 'cases');
        echo $this->cases->send_notification_email();
    }

    /**
     * Generates estimate PDF
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    string
     * */
    public function generate_estimate_pdf($case_number = '')
    {
        $this->load->helper('mpdf');
        $this->load->model('cases_model', 'cases');
        $this->load->model('estimates_model');
        // Get case entry by case number
        if (!is_null($case = $this->cases->find_case_by_number($case_number))) {
            // 1. Patent Details
            $data['case'] = $case;

            // 2. Countries/Fees table
            $data['countries_fees'] = $this->estimates_model->get_calculation_results($case_number, true, true);

            // 3. Estimates Footnotes
            $data['footnotes'] = $this->cases->get_estimate_footnotes($case['id']);
            $data['publication_language'] = $this->cases->get_language_by_code_and_case_type($case['publication_language'], $case['case_type_id']);
            $pdf = $this->load->view('estimates/generate_pdf', $data, TRUE);


            $timestamp = date('m_d_Y_H_i_s');
            $filename = str_replace('/', '-', $case['application_number']) . '_estimate_' . $timestamp . '.pdf';
            if (!file_exists('uploads/' . $case['user_id'])) {
                mkdir('uploads/' . $case['user_id'], 0755);
            }
            if (!file_exists('uploads/' . $case['user_id'] . '/' . $case_number)) {
                mkdir('uploads/' . $case['user_id'] . '/' . $case_number, 0755);
            }
            $location = 'uploads/' . $case['user_id'] . '/' . $case_number . '/' . $filename;
            $css = 'assets/css/estimate_pdf.css';
            pdf_create($pdf, $location, $css, FALSE, TRUE);
            $this->cases->assign_file_to_case($case['id'], $filename, $location, 15, $case['user_id']);

            $this->cases->set_estimate_pdf_sent($case['id'], FALSE, 0);
            echo json_encode(array('result' => '1'));
        }
        else
        {
            echo json_encode(array('result' => '0'));
        }
    }

    /**
     * Returns estimate PDF
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    string
     * */
    public function get_estimate_pdf()
    {
        $this->load->model('estimates_model', 'estimates');
        $this->load->model('files_model');

        $case_id = $this->input->post('case_id');
        $result = array(
            'result' => '',
            'file_id' => '',
        );

        if ($estimate = $this->estimates->get_last_estimate_pdf($case_id)) {
            $result['file_id'] = $estimate['id'];
            $result['result'] = 'ok';
            $result['file_data'] = $this->files_model->get_file_by_id($result['file_id']);
        }

        echo json_encode($result);
    }

    public function get_associate_pdf()
    {
        $this->load->model('associates_model', 'associates');
        $case_id = $this->input->post('case_id');
        $result = array(
            'result' => '',
            'file_id' => '',
        );

        if ($associate = $this->associates->get_last_associate_pdf($case_id)) {
            $result['file_id'] = $associate['id'];
            $result['result'] = 'ok';
        }

        echo json_encode($result);
    }

    /**
     * Reloads customer fees. Sync values with main fees table
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case id
     * @param    int    case number
     * @return    void
     */
    public function reload_customer_fees($user_id = '', $case_number = '')
    {
        $this->load->model('cases_model', 'cases');
        $this->cases->reload_customer_fees($user_id);
        redirect('/cases/view/' . $case_number);
    }

    /**
     * Associate reference form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case id
     * @param    int    case number
     * @param    int    associate id
     * @param    int    1 - this is custom associate, 0 - not custom
     * @return    void
     */
    public function associate_reference_form($case_id, $case_number, $associate_id)
    {
        $this->load->model('associates_model', 'associates');
        $data['case_id'] = $case_id;
        $data['case_number'] = $case_number;
        $data['associate_id'] = $associate_id;
        $data['reference_number'] = $this->associates->get_associate_reference_entry($case_id, $associate_id);
        $this->load->view('cases/associate_reference_form', $data);
    }

    /**
     * Submit associate reference
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case id
     * @param    int    case number
     * @param    int    associate id
     * @return    void
     */
    public function associate_reference_form_submit($case_id, $case_number, $associate_id)
    {
        $this->load->model('associates_model', 'associates');
        $this->associates->insert_associate_reference($associate_id, $case_id, TRUE);
        redirect('/cases/view/' . $case_number);
    }

    /**
     * Creates associate list PDF
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    string
     */
    public function create_pdf_associates_list($case_number = '')
    {
        $this->load->helper('mpdf');
        $this->load->library('table');
        $this->load->model('cases_model', 'cases');
        $this->load->model('associates_model', 'associates');
        $result = array();

        if (!is_null($case = $this->cases->find_case_by_number($case_number))) {
            $data['associates'] = $this->associates->new_get_all_case_associates($case['id'], '1');
            $pdf = $this->load->view('associates/generate_list_pdf', $data, TRUE);
            $timestamp = date('m_d_Y_H_i_s');
            $filename = 'associates_list_' . $timestamp . '.pdf';
            if (!file_exists('uploads/' . $case['user_id'])) {
                mkdir('uploads/' . $case['user_id'], 0755);
            }
            if (!file_exists('uploads/' . $case['user_id'] . '/' . $case_number)) {
                mkdir('uploads/' . $case['user_id'] . '/' . $case_number, 0755);
            }
            $location = 'uploads/' . $case['user_id'] . '/' . $case_number . '/' . $filename;
            $css = 'assets/css/associates_list_pdf.css';
            $margins = array(
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 20,
                'margin_bottom' => 15,
                'margin_header' => 0,
                'margin_footer' => 0,
            );
            pdf_create($pdf, $location, $css, FALSE, TRUE, 'CONFIDENTIAL', $margins);
            $file_type_id = 17;
            $file_id = $this->cases->assign_file_to_case($case['id'], $filename, $location, $file_type_id, $case['user_id']);
            // Variables for dynamic show
            $file_types = $this->cases->file_types();
            $file_types_dd = array('' => '');
            if (check_array($file_types)) {
                foreach ($file_types as $file_type)
                {
                    $file_types_dd[$file_type['id']] = $file_type['name'];
                }
            }
            $result['class_hash'] = uniqid();
            $result['visibility'] = form_checkbox('visibility', '1', 0, 'class="file_visibility" id="' . $file_id . '"');
            $result['file'] = '<span id="' . $file_id . '" class="filename">' . $filename . '</span><input id="inp' . $file_id . '" class="filename_input" type="text" style="display: none;" value="' . $filename . '" name="filename">
                <a id="rename_' . $file_id . '" class="rename_link_ok" style="display: none;" href="javascript:void(0);">OK</a>
                <a id="cancel_' . $file_id . '" class="rename_link_cancel" style="display: none;" href="javascript:void(0);">Cancel</a>';
            $result['view_link'] = anchor('/cases/view_file/' . $file_id, '<img src="' . base_url() . 'assets/images/i/eye-14-14.png" alt="View"/>');
            $result['delete_link'] = '<a href="javascript:void(0);" id="delete_link_' . $file_id . '" onclick="if(confirm(\'Do you really want to delete selected file?\')){ remove_file(' . $file_id . ');}"><img src="' . base_url() . 'assets/images/i/delete.png" alt="Remove"/></a>';
            $result['assign_to_countries_link'] = '<a href="' . base_url() . 'cases/assign_file_to_countries_form/' . $file_id . '/' . $case_number . '" class="' . $result['class_hash'] . ' popup">Assign to countries</a>';
            $result['file_type_dropdown'] = form_dropdown('file_type', $file_types_dd, $file_type_id, 'class="file_type" id="ft' . $file_id . '"');
            $result['send_file_link'] = '';
            $result['result'] = '1';
            echo json_encode($result);
        }
    }

    /**
     * Unhighlights case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     */
    public function unhighlight($case_number = '')
    {
        $this->load->model('cases_model', 'cases');
        $this->cases->unhighlight($case_number);
        redirect('/cases/view/' . $case_number);
    }

    public function change_related_hidden()
    {

        $this->load->model('cases_model', 'cases');
        if ($this->input->post('status') == '1') {
            $this->cases->sendConfEmailToClient($this->input->post('case_id'), '24');
            $this->db->where('case_number', $this->input->post('case_id'));
            $this->db->delete('cases');
            echo json_encode(array('type' => 'error', 'text' => 'You successfully removed case countries!'));
        }
        else
        {
            $this->db->where('case_number', $this->input->post('case_id'));
            $query = $this->db->get('cases');
            if (!$query->num_rows()) {
                echo json_encode(array('type' => 'error', 'text' => 'Something is wrong with this case, please contact administrator for support'));
            }
            $case_managers = $query->row_array();
            if (empty($case_managers['manager_id'])) {
                echo json_encode(array('type' => 'error', 'text' => 'You need to assign Project Manager to this case'));
                return false;
            }
            if (empty($case_managers['sales_manager_id'])) {
                echo json_encode(array('type' => 'error', 'text' => 'You need to assign Sales Manager to this case'));
                return false;
            }
            if ($this->input->post('is_intake') == '1') {
                $temp = '23';
            } else {
                $temp = '22';
            }
            $this->db->set('common_status', $this->input->post('type'));
            $this->db->where('case_number', $this->input->post('case_id'));
            $this->db->update('cases');
            $this->cases->sendConfEmailToClient($this->input->post('case_id'), $temp);
            echo json_encode(array('type' => 'information', 'text' => 'You successfully approved case countries!'));
        }
    }

    public function save_tracker()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $this->load->model('cases_model', 'cases');
        $case_id = $this->input->post('case_id');
        $country_id = $this->input->post('country_id');
        $value = $this->input->post('value');
        $action = $this->input->post('action');
        echo $this->cases->save_tracker($case_id, $country_id, $action, $value);
    }

    public function enable_disable_assoc()
    {
        $action = $this->input->post("ACTION");
        $case_id = $this->input->post("CASE_ID");
        $associate_id = $this->input->post("ASSOC_ID");
        $output = array(
            "error" => true,
            "error_text" => "",
            "data" => array()
        );
        $this->load->model('associates_model', 'associates');
        if ($action == 'ENABLE') {
            $action = '1';
        } elseif ($action == 'DISABLE') {
            $action = '0';
        }
        $output["error"] = false;
        $output["data"]["ASSOC_ID"] = $associate_id;
        $output["data"]["CASE_ID"] = $case_id;
        $output["data"]["ACTION"] = $action;
        $result = $this->associates->enable_disable_associate($case_id, $associate_id, $action);
        if (empty($result)) {
            $output["error"] = true;
            $output["error_text"] = "Something is wrong. Please try again later.";
        } else {
            $output["data"]["state"] = true;
        }

        echo json_encode($output);
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

    function ajax_get_data_for_case()
    {
        $this->load->model('cases_model', 'cases');
        $case_number = $this->input->post('case_number');
        $data['case'] = $this->cases->find_case_by_number($case_number);
        foreach ($data['case'] as $key => $value) {
            if (empty($value) || $value == '00/00/00') {
                $data['case'][$key] = 'N/A';
            }
        }
        echo json_encode($data);
    }

    function ajax_update_customer_data_for_case() {
        $this->cases_model->update_case_manager($_POST['customer_data']['case_number']);
        $options = $this->input->post('customer_data');
        $this->cases_model->update_case_basic($options);
    }

    public function file_view_more($file_id)
    {
        $this->load->model('files_model', 'files');
        $this->load->view('/cases/file_view_more');
    }

    public function generate_sow_pdf($case_naumber)
    {
        $this->load->helper('mpdf');
        $this->load->model('cases_model', 'cases');
        $data = array();
        $data['result'] = $case_naumber;
        die(json_encode($data));
    }

    public function update_customer_fees($user_id)
    {
        $this->load->model('cases_model', 'cases');
        $this->load->model('estimates_model');

        if ($this->cases->update_customer_fees($user_id)) {
            echo json_encode(array('type' => 'information', 'text' => 'Fee schedule has been saved!'));
        } else {
            echo json_encode(array('type' => 'error', 'text' => 'Fee schedule hasn\'t been saved!'));
        }
        $this->estimates_model->update_fees_of_case($this->input->post('case_number'));
    }
    // function for saving invoice (by PM)
    function _edit_invoice() {

        $options['id'] = $this->input->post('associate_data_id');

        switch ($this->input->post('fa_invoice_status')) {
            // if clicked on approved button
            case 'approved':
                // if approved - let's save everything
                $options['fa_invoice_status'] = 'approved';
                $options['fa_corrected_invoice_official_fee'] = $_POST['fa_corrected_invoice_official_fee'];
                $options['estimated_by_pm_filing_fee'] = $_POST['estimated_by_pm_filing_fee'];
                $options['estimated_by_pm_official_fee'] = $_POST['estimated_by_pm_official_fee'];
                $options['estimated_by_pm_additional_fee'] = $_POST['estimated_by_pm_additional_fee'];
                foreach($_POST['additional_fee_id'] as $key => $value) {
                    $this->estimates_model->update_case_country_additional_fees_for_invoice(array(
                        'additional_fee_id' => $_POST['additional_fee_id'][$key] ,
                        'cases_associates_data_id' => $options['id'] ,
                        'additional_fee_corrected_by_pm' => $_POST['additional_fee_corrected_by_pm'][$key] ,
                    ));
                }
                break;
            // rejected button
            case 'rejected':
                $options['fa_invoice_status'] = 'rejected';
                break;
            // pending unlock button
            case 'pending-unlock':
                $options['fa_invoice_status'] = 'pending-unlock';
                break;
            default:
                break;
        }
        // if "not approved" let's clean what was saved so far
        if ($this->input->post('fa_invoice_status') != 'approved') {
            foreach($_POST['additional_fee_id'] as $key => $value) {
                $this->estimates_model->update_case_country_additional_fees_for_invoice(array(
                    'additional_fee_id' => $_POST['additional_fee_id'][$key] ,
                    'cases_associates_data_id' => $options['id'] ,
                    'additional_fee_corrected_by_pm' => NULL ,
                ));
                $options['fa_corrected_invoice_official_fee'] = NULL;
                $options['estimated_by_pm_filing_fee'] = NULL;
                $options['estimated_by_pm_official_fee'] = NULL;
                $options['estimated_by_pm_additional_fee'] = NULL;
            }
        }

        $this->estimates_model->update_associates_data($options);

    }

    function download_invoice() {
        $this->load->model('estimates_model');
        $this->load->helper('download');
        $case_id = $this->uri->segment(4);
        $country_id = $this->uri->segment(3);
        $associate_data = $this->estimates_model->get_cases_associates_data(array(
            'case_id' => $case_id,
            'associate_id' => $this->session->userdata('fa_user_id') ,
            'country_id' => $country_id
        ));
        $data = file_get_contents('../pm/' . $associate_data->location); // Read the file's contents

        force_download($associate_data->filename , $data);

    }

}