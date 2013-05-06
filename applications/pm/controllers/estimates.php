<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Estimates extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('estimates_model', 'estimates');
        if (!$this->app_security_model->check_session()) {
            if (is_ajax()) {
                echo 'Your session has timed out due to inactivity. Please log back in to continue.';
                exit();
            } else {
                redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
            }
        }
    }

    /**
     * Saves estimate/case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    public function save($case_number = '')
    {
        $this->load->model('estimates_model', 'estimates');
        $this->load->model('cases_model', 'cases');

        $this->estimates->save_estimate_prices($case_number);

        $case_data = $this->cases->find_case_by_number($case_number);
        $this->estimates->save_additional_countries($case_data);
        $this->estimates->update_additional_countries();
        $this->cases->save_top_footnote($case_number);
        redirect('/cases/view/' . $case_number);
    }

    function save_locked_official_fee_for_country()
    {
        $user_id = intval($this->uri->segment(3));
        $this->estimates->save_locked_official_fee_for_country($user_id);
    }

    /**
     * Adds country to estimate
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    public function add_country_to_estimate($case_number = '')
    {
        $this->load->model('cases_model', 'cases');
        $this->load->model('estimates_model', 'estimates');
        $this->load->model('associates_model', 'associates');
        $array_of_country_id = explode(',', $_POST['new_estimate_country_id']);
        $this->associates->add_new_associates_to_case($case_number, $array_of_country_id);
        $this->cases->add_country_to_tracker($case_number,$array_of_country_id);

        foreach ($array_of_country_id as $key => $country_id) {
            $_POST['new_estimate_country_id'] = $country_id;
            $this->estimates->add_country_to_estimate($case_number);
            $this->estimates->set_pm_save_time($case_number, NULL);
        }

        redirect('/cases/view/' . $case_number);
    }

    /**
     * Adds country to estimate form.
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    public function add_country_to_estimate_form($case_number = '')
    {

        $this->load->model('cases_model', 'cases');
        $countries = array();
        $case_countries_arr = array();

        if (!is_null($case_countries = $this->cases->get_case_countries($case_number, true))) {

            foreach ($case_countries as $case_country) {
                $case_countries_arr[] = $case_country['id'];
            }
        }
        $countries_by_case_type = $this->cases->get_countries_by_case_type($case_number, true);

        if (check_array($countries_by_case_type)) {
            foreach ($countries_by_case_type as $country) {
                if (!in_array($country['id'], $case_countries_arr)) {
                    $countries[] = array('id' => $country['id'], 'country' => $country['country']);
                }
            }
        }
        $data['case_number'] = $case_number;
        $data['countries'] = $countries;
        $this->load->view('estimates/add_country_to_estimate_form', $data);
    }

    /**
     * Returns estimate table for case.
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */

    public function get_estimate_table($case_number = '')
    {
        $this->load->model('estimates_model', 'estimates');
        $this->estimates->set_pm_save_time($case_number, date('Y-m-d H:i:s'));
        $need_calculations = $this->input->post('need_calculations');
        echo $this->estimates->get_estimate_table($case_number, $need_calculations);
    }

    /**
     * The form to send estimate to BDV
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */

    public function send_estimate_pdf_to_bdb_form($case_number = '')
    {
        $this->load->model('cases_model', 'cases');
        $this->load->model('emails_model', 'emails');
        $this->load->model('estimates_model', 'estimates');
        $this->load->model('customers_model', 'customers');
        // Get case data
        if (!is_null($case = $this->cases->find_case_by_number($case_number))) {
            // Get DBV entry for this case
            if (!is_null($bdv = $this->customers->get_managers($case['sales_manager_id']))) {
                // Get last estimate PDF
                if (!is_null($estimate = $this->estimates->get_last_estimate_pdf($case['id']))) {
                    $pdf_estimate_creation_date = new Datetime($estimate['created_at']);
                    $estimate_saved_by_pm = new Datetime($case['estimate_saved_by_pm']);
                    if ($estimate_saved_by_pm > $pdf_estimate_creation_date) {
                        echo '<p>Please generate a fresh pdf before sending it to client/BDV</p>';
                    } else {
                        $data['estimate'] = $estimate;
                        $data['bdv'] = $bdv;
                        $data['case'] = $case;
                        $email_template = $this->emails->get_ready_email_from_template(18, $case['id']);
                        $email_template['text'] = str_replace('%BDV_REP%', $bdv['firstname'] . ' ' . $bdv['lastname'], $email_template['text']);
                        $data['email_subject'] = $email_template['subject'];
                        $data['email_text'] = $email_template['text'];
                        $this->load->view('estimates/send_estimate_pdf_to_bdv', $data);
                    }
                } else {
                    echo '<p>Please generate a fresh pdf before sending it to client/BDV</p>';
                }
            } else {
                echo '<p>There is no BDV assigned to current case</p>';
            }
        }
    }

    /**
     * The form to send estimate to client
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    public function send_estimate_pdf_to_client_form($case_number = '')
    {

        $this->load->model('cases_model', 'cases');
        $this->load->model('emails_model', 'emails');
        $this->load->model('estimates_model', 'estimates');

        // Get case data
        if (!is_null($case = $this->cases->find_case_by_number($case_number))) {
            // Get client entry
            if (!is_null($client = $this->cases->get_customer_by_case_number($case_number))) {
                // Get last estimate PDF
                if (!is_null($estimate = $this->estimates->get_last_estimate_pdf($case['id']))) {
                    $pdf_estimate_creation_date = strtotime($estimate['created_at']);



                    $estimate_saved_by_pm = strtotime($case['estimate_saved_by_pm']);

                    if ($estimate_saved_by_pm > $pdf_estimate_creation_date) {
                        echo '<p>Please generate a fresh pdf before sending it to client/BDV</p>';
                    } else {
                        $case_manager = '';
                        if ($case['pdf_sent_to_client'] != '0') {
                            $this->db->where('id', $case['pdf_sent_to_client']);
                            $query = $this->db->get('managers');
                            if ($query->num_rows()) {
                                $manager_tmp = $query->row_array();
                                $case_manager = $manager_tmp['firstname'] . ' ' . $manager_tmp['lastname'];
                            }
                        }
                        $data['estimate'] = $estimate;
                        $data['client'] = $client;
                        $data['case'] = $case;
                        $data['case_manager'] = $case_manager;
                        $data['estimate_saved_by_pm'] = date('m-d-Y', $estimate_saved_by_pm);
                        $data['pdf_sent_to_client_date'] = $case['pdf_sent_to_client_date'];
                        $email_template = $this->emails->get_ready_email_from_template(17, $case['id']);
                        $data['email_subject'] = $email_template['subject'];
                        $data['email_text'] = $email_template['text'];
                        $this->load->view('estimates/send_estimate_pdf_to_client', $data);
                    }
                } else {
                    echo '<p>Please generate a fresh pdf before sending it to client/BDV</p>';
                }
            }
        }
    }

    /**
     * Sends estimate to client or bdv
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case id
     * @param    string    desctination: bdb/client
     * @return    void
     * */
    public function send_estimate_pdf($case_id = '', $dest = 'bdv')
    {

        $this->load->library('email');
        $this->load->model('send_emails_model', 'send_emails');
        $this->load->model('estimates_model', 'estimates');
        $this->load->model('cases_model', 'cases');

        // Get case entry
        if (!is_null($case = $this->cases->get_case($case_id, FALSE, TRUE))) {
            $customer = $this->cases->get_customer_by_case_number($case['case_number']);
            // Get last estimate
            if (!is_null($estimate = $this->estimates->get_last_estimate_pdf($case_id))) {
                $case_id = $this->input->post('case_id');
                $to = $this->input->post('to_email');
                if (!empty($to)) {
                    $check_comma = substr($to, -1);
                    if ($check_comma == ',' || $check_comma == ';') {
                        $to = substr($to, 0, -1);
                    }
                    if (strpos($to, ';') === FALSE) {
                        $send_to = explode(',', $to);
                    } else {
                        $send_to = explode(';', $to);
                    }
                }
                $subject = $this->input->post('email_subject');
                $email_content = $this->input->post('email_content');
                $estimate_exists = $this->input->post('estimate_exists');
                $attachments = array();
                $manager_id = $this->session->userdata('manager_user_id');
                if ($dest == 'client') {
                    $this->db->set('pdf_sent_to_client', $manager_id);
                    $this->db->set('pdf_sent_to_client_date', date('Y-m-d H:i:s'));
                    $this->db->where('id', $case['id']);
                    $this->db->update('cases');
                }
                $from = 'case' . $case["case_number"] . $this->config->item('default_email_box');
                if (TEST_MODE) {
                    $send_to = TEST_CLIENT_EMAIL;
                    if ($dest == 'bdv') {
                        $send_to = TEST_BDV_EMAIL;
                    } elseif ($customer['type'] == 'firm') {
                        $send_to = TEST_FIRM_EMAIL;
                    }
                }
                if ($estimate_exists == '1') {
                    if (file_exists($estimate['location'])) {
                        array_push($attachments, $estimate);
                    }
                }
                if ($this->send_emails->send_email($from, $from, $subject, $email_content, $send_to, false, $attachments)) {
                    $this->cases->set_estimate_pdf_sent($case_id, $dest, 1);
                    $this->session->set_flashdata('message', json_encode(array('message' => 'Email has been sent!', 'type' => 'info', 'title' => 'Information')));
                } else {
                    $this->session->set_flashdata('message', json_encode(array('message' => 'Email hasn\'t been sent!', 'type' => 'error', 'title' => 'Information')));
                }
            }
            redirect('/cases/view/' . $case['case_number']);
        }
        $this->session->set_flashdata('message', json_encode(array('message' => 'Email hasn\'t been sent!', 'type' => 'error', 'title' => 'Information')));
    }

    /**
     * Saves filing fee. For AJAX
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    customer id
     * @return    string
     * */
    public function save_filing_fee_for_customer($customer_id)
    {
        $this->load->model('estimates_model', 'estimates');
        echo $this->estimates->save_filing_fee_for_customer($customer_id);

    }

    public function save_translation_fee_for_customer($customer_id)
    {

        echo $this->estimates->save_translation_fee_for_customer($customer_id);
    }

    /**
     * Returns translation fee. For AJAX
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    string
     * */
    public function get_translation_fee()
    {
        $this->load->model('cases_model', 'cases');
        $this->load->model('currencies_model', 'currencies');
        $this->load->model('estimates_model', 'estimates');
        $case_number = $this->input->post('case_number');
        $number_words = $this->input->post('number_words');
        $country_id = $this->input->post('country_id');
        $translation_rate = $this->input->post('translation_rate');
        $estimate_fee_level = $this->input->post('estimate_fee_level');
        $estimate_currency = $this->input->post('estimate_currency');
        $translation_fee = 0;
        $currency_sign = '$';
        if (!is_null($case = $this->cases->find_case_by_number($case_number))) {
            $estimate_countries = $this->estimates->get_estimate_countries($case['id'], $case['user_id'], $estimate_fee_level);
            if (!is_null($customer_fees = $this->estimates->get_customer_fees_by_countries($case['user_id'], $case['case_type_id'], $case['case_number'], $estimate_countries, $case['entity']))) {
                $translation_fee = ceil($translation_rate * $number_words + $customer_fees[$country_id]['translation_rates_for_claims'] * $case['number_words_in_claims']);

                if ($estimate_currency == 'euro') {
                    $currency_sign = '€';
                    $euro_exchange_rate = $this->currencies->get_currency_rate_by_code('EUR');

                    $translation_fee = ceil($translation_fee / $euro_exchange_rate);
                }
            }
        }
        echo $currency_sign . $translation_fee;
    }

    /**
     * Makes available for BDV. For AJAX
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    public function make_available_for_bdv($case_number = '')
    {
        $this->load->model('estimates_model', 'estimates');
        $this->estimates->make_available_for_bdv($case_number);
    }

    /**
     * Makes available for client. For AJAX
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    public function make_available_for_client($case_number = '')
    {
        $this->load->model('estimates_model', 'estimates');
        $this->estimates->make_available_for_client($case_number);
    }

    function delete_sub_country()
    {
        $estimate_country_id = intval($this->uri->segment(3));
        $this->load->model('estimates_model');
        $this->estimates_model->delete_sub_country($estimate_country_id);
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function delete_country_from_estimate($case_number = '')
    {
        $this->load->model('estimates_model', 'estimates');
        $this->load->model('cases_model','cases');
        $this->load->model('associates_model', 'associates');

        $case = $this->cases->find_case_by_number($case_number);

        $this->associates->delete_associates_from_case_by_country_id($case['id'], $this->input->post('country_record_id'));

        $this->estimates->delete_country_from_estimate($case_number);
    }
}

/* End of file estimates.php */
/* Location: ./application/controllers/estimates.php */