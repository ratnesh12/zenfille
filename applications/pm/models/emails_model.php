<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Emails_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    public function create_email_account_for_case($case_number = '')
    {
        if (empty($case_number)) {
            return FALSE;
        }
        $cpanel_username = $this->config->item('cpanel_username');
        $cpanel_domain = $this->config->item('cpanel_domain');
        $case_email = 'case' . $case_number;
        $case_email_password = $this->config->item('zenfile_default_email_password');

        $hash = '78af47c56d4c042b74639d18feb535ba5aafb0f29c9e6a3c29a24da47b6992dc2a6696e318533a40219876c9f50d3c045331464a7cea62f56050081ec027efa5806bd33695205234b59ee7d63addf8751a355710f6c3ed1f1c3cc4849034d939f1f9b6159248630572ce7ad23769fbc1146b0f7eb632b1bad3437fc9197c6d74010c1cfb163d4d644b0128a7f874aaefe37eba660c7832e3a2000302f618bda723a42240df0f41bbe8147ff897db7d64ee38090c08d27c4f727c69f56f2b39477d76af73ca25997f44d56ad585db059215422962bd30f77b7083cfc4c31c7e9d4c5cafa3bdf7082f40f147c9734e1a818d5525345fcdc817a223bb1d965945491bfbffa47c62792b73504a1cb97f887a80b700cd24ba602f9a6ed0dcee5f1b26273eebf75af1263df0ecc686e3b476f804ccbe536a285144cc0f6f3d5dc595a65d1dd019fd3780f8e140b46e4ba4b8a3e8597aaf67a0da9cee6582723027116eb62ede5ca1418e70bf667f66c50b4a3e833a416f8d37b7cc6c74656467c45ffde2f3373a28c3d3b5c1e0d9f36941bc7b25ad04e4060a8eebb031931b546f065b3946308c3b706a496891a15a6d0ba9a85c3725788663b84f4e09a3db6b32ff8c5d4c2bb954718be545f2ffb788c98644';
        $query = 'https://108.167.175.233:2087/json-api/cpanel?cpanel_jsonapi_user=' . $cpanel_username . '&cpanel_jsonapi_module=Email&cpanel_jsonapi_func=addpop&cpanel_jsonapi_apiversion=1&arg-0=' . $case_email . '&arg-1=' . $case_email_password . '&arg-2=0&arg-3=' . $cpanel_domain;

        $curl = curl_init();
        $header[0] = "Authorization: WHM root:" . preg_replace("'(\r|\n)'", "", $hash);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        # Create Curl Object
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        # Allow self-signed certs
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        # Allow certs that do not match the hostname
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        # Do not include header in output
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        # Return contents of transfer on curl_exec

        # set the username and password
        curl_setopt($curl, CURLOPT_URL, $query);
        # execute the query
        $result = curl_exec($curl);
        curl_close($curl);
        if ($result == FALSE) {
            error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
            return FALSE;
            # log error if curl exec fails
        }
        else
        {
            $result_array = json_decode($result);
            if (isset($result_array->data)) {
                if (is_object($result_array->data)) {
                    if (strpos($result_array->data->result, 'exists') === FALSE) {
                        return TRUE;
                    }
                }
            }
            return FALSE;
        }
    }
    /**
     * Returns a list of available emails templates
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     */
    public function get_all_templates()
    {
        $query = $this->db->get('emails_templates');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns an email template
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param     int    template ID
     * @return    mixed
     */
    public function get_template($template_id)
    {
        $this->db->where('id', $template_id);
        $query = $this->db->get('emails_templates');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Inserts a new email template
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    bool
     */
    public function insert_template()
    {
        $title = $this->input->post('title');
        $content = $this->input->post('content');
        $subject = $this->input->post('subject');
        $description = $this->input->post('description');

        $template = array(
            'title' => $title,
            'content' => $content,
            'description' => $description,
            'subject' => $subject,
        );

        $this->db->insert('emails_templates', $template);

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Updates an email template
     *
     * @access    public
     * @param    int    template ID
     * @return    bool
     */
    public function update_template($template_id)
    {
        $title = $this->input->post('title');
        $content = $this->input->post('content');
        $description = $this->input->post('description');
        $subject = $this->input->post('subject');

        $template = array(
            'title' => $title,
            'content' => $content,
            'description' => $description,
            'subject' => $subject,
        );

        $this->db->where('id', $template_id);
        $this->db->update('emails_templates', $template);

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Deletes an email template
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param     int    template ID
     * @return    bool
     */
    public function delete_template($template_id)
    {
        $this->db->where('id', $template_id);
        $this->db->delete('emails_templates');
        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Generates email text from template
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param     int    template ID
     * @param     int    case ID
     * @param     int    country ID
     * @param     array    files
     * @param     int    status ID
     * @param     string    email type
     * @return    array
     */
    public function get_ready_email_from_template($template_id = '', $case_id = '', $country_id = '', $files = array(), $status_id = -1, $email_type = '', $country_association_id = "", $zip_hash = '')
    {
        $ci = & get_instance();
        $ci->load->model('cases_model', 'cases');
        $ci->load->model('associates_model', 'associates');
        $ci->load->model('countries_model', 'countries');
        $this->load->model('customers_model', 'customers');

        $result = array(
            'text' => '',
            'subject' => ''
        );

        if (empty($email_type)) {
            $email_type = $this->input->post('email_type');
        }
        $case_manager = '';
        $this->db->where('id', $case_id);
        $query = $this->db->get('cases');
        $case_manager_temp = $query->row_array();
        if ($case_manager_temp) {
            $this->db->where('id', $case_manager_temp['manager_id']);
            $query = $this->db->get('managers');
            $temp_data = $query->row_array();
            $case_manager = (isset($temp_data['firstname']) ? $temp_data['firstname'] : '') . ' ' . (isset($temp_data['lastname']) ? $temp_data['lastname'] : '');
        }

        $client_reference_number = '';
        $client_firstname = '';
        $client_lastname = '';
        $client_email = '';
        $client_phone = '';
        $client_address = '';
        $client_company_name = '';
        $client_city = '';
        $client_state = '';
        $client_zip_code = '';
        $client_fax = '';

        $case_link = '';
        $case_application_title = '';
        $case_application_number = '';
        $case_filing_deadline = '';
        $case_applicant = '';
        $case_countries = '';
        $case_type = '';
        $case_cc = '';
        $case_files = '';
        $country_reference_number = 'N/A';

        $fa_fee = 0;
        $fa_name = '';
        $fa_country = '';
        $fa_filing_deadline_type = '';

        $bdv_name = '';
        $trans_ass_data = '';

        if (!is_null($template = $this->get_template($template_id))) {

            $content = $template['content'];

            if (!is_null($case = $ci->cases->get_case($case_id, FALSE, TRUE))) {

                $case_link = 'http://' . $_SERVER['HTTP_HOST'] . '/client/cases/view/' . $case['case_number'];
                $case_applicant = $case['applicant'];
                $case_application_title = $case['application_title'];
                $case_application_number = $case['application_number'];

                if ($case['filing_deadline'] == '0000-00-00' || $case['filing_deadline'] == '00/00/00') {
                    $case_filing_deadline = 'N/A';
                } else {
                    $case_filing_deadline = new Datetime($case['filing_deadline']);
                    $case_filing_deadline = $case_filing_deadline->format('F d, Y');
                }

                if ($case['case_type_id'] == '1') {
                    if (!is_null($country = $ci->countries->get_country($country_id))) {

                        if (!empty($country['country_filing_deadline']) && !empty($case['30_month_filing_deadline']) && !empty($case['31_month_filing_deadline'])) {
                            if ($country['country_filing_deadline'] == '30') {
                                $case_filing_deadline = new Datetime($case['30_month_filing_deadline']);
                                $case_filing_deadline = $case_filing_deadline->format('F d, Y');
                            } elseif ($country['country_filing_deadline'] == '31')
                            {
                                $case_filing_deadline = new Datetime($case['31_month_filing_deadline']);
                                $case_filing_deadline = $case_filing_deadline->format('F d, Y');
                            }

                        } else
                        {
                            $case_filing_deadline = 'N/A';
                        }
                    } else
                    {
                        $_30_fd_unix = strtotime($case['30_month_filing_deadline']);
                        if ($_30_fd_unix < time()) {
                            if ($case['31_month_filing_deadline'] == '00/00/00' || empty($case['31_month_filing_deadline'])) {
                                $case_filing_deadline = 'N/A';
                            } else
                            {
                                $case_filing_deadline = new Datetime($case['31_month_filing_deadline']);
                                $case_filing_deadline = $case_filing_deadline->format('F d, Y');
                            }
                        } else
                        {
                            if ($case['30_month_filing_deadline'] == '00/00/00' || empty($case['30_month_filing_deadline'])) {
                                $case_filing_deadline = 'N/A';
                            } else
                            {
                                $case_filing_deadline = new Datetime($case['30_month_filing_deadline']);
                                $case_filing_deadline = $case_filing_deadline->format('F d, Y');
                            }
                        }
                    }
                }

                if (!is_null($country = $ci->countries->get_country($country_id))) {
                    if (!empty($country['country_filing_deadline'])) {

                    }
                }
                $this->db->select('email');
                $this->db->where('case_id', $case['id']);
                $query = $this->db->get('case_contacts');
                if ($query->num_rows()) {
                    $arraycc = '';
                    foreach ($query->result_array() as $array) {
                        $arraycc[] .= $array['email'];
                    }
                    $case_cc = implode('; ', $arraycc);
                }
                $parkip_case_number = $case['parkip_case_number'];

                if (!is_null($customer = $ci->cases->get_customer_by_case_number($case['case_number']))) {
                    $client_reference_number = !empty($case['reference_number']) ? $case['reference_number'] : 'N/A';
                    $client_firstname = $customer['firstname'];
                    $client_lastname = $customer['lastname'];
                    $client_email = $customer['email'];
                    $client_phone = $customer['phone_number'];
                    $client_address2 = $customer['address2'];
                    $client_address = $customer['address'];
                    $client_company_name = $customer['company_name'];
                    $client_city = $customer['city'];
                    $client_state = $customer['state'];
                    $client_zip_code = $customer['zip_code'];
                    $client_country = $customer['country'];
                    $client_fax = $customer['fax'];
                }

                // country filing deadline

                if(!empty($client_reference_number)){
                    $country_reference_number = $client_reference_number;
                }
                if($country_id){

                    $this->db->where('case_id', $case['id']);
                    $this->db->where('country_id', $country_id);
                    $query = $this->db->get('cases_countries');
                    if($query->num_rows()){
                        $tmp = $query->row_array();
                        if(!empty($tmp['reference_number'])){
                        $country_reference_number = $tmp['reference_number'];
                        }
                    }

                }

                //end country filing deadline

                // A list of countries assigned to current case
                $fa_country = $country['country'];
                if (!is_null($countries_array = $ci->cases->get_case_countries($case['case_number']))) {
                    $approved_countries_array = array();
                    if (!is_null($approved_countries = $ci->cases->get_approved_countries($case['id']))) {
                        foreach ($approved_countries as $approved_country)
                        {
                            $approved_countries_array[] = $approved_country['country_id'];
                        }
                    }
                    $temp_array = array();
                    foreach ($countries_array as $country)
                    {
                        if (in_array($country['id'], $approved_countries_array)) {
                            $temp_array[] = $country['country'];
                        }
                    }
                    $case_countries = implode(', ', $temp_array);
                }
                if($template_id == '5' ||$template_id == '6' ||$template_id == '7' ||$template_id == '8' ||$template_id == '9' ||$template_id == '10' ||$template_id == '11' ||$template_id == '12' ||$template_id == '13'){
                // FA data
                    $fa = $ci->cases->get_associate_by_id($country_association_id);
                if($fa){
                    $fa_currency_sign = '$';
                    if ($fa['fee_currency'] == 'euro') {
                        $fa_currency_sign = '€';
                    }
                    $fa_country = $fa['country'];
                    $fa_name = $fa['contact_name'];
                    $fa_fee = $fa_currency_sign . $fa['fee'];
                    if ($fa['30_months'] == 1) {
                        $fa_filing_deadline_type = '30 months';
                    } elseif ($fa['31_months'] == 1)
                    {
                        $fa_filing_deadline_type = '31 months';
                    } elseif ($fa['ep_validation'] == 1)
                    {
                        $fa_filing_deadline_type = 'EP Validation';
                    }
                }
                }

                // A list of attached file
                // If we have sent an array of files
                $country_id_value = $country_id;
                if (empty($country_id)) {
                    $country_id_value = 'allcountries';
                }
                $custom_email_files_id = array();
                if (is_array($files) && (count($files) > 0)) {
                    $custom_email_files = $this->cases->get_files_by_id_array($files);
                    if (check_array($custom_email_files)) {
                        foreach ($custom_email_files as $item)
                        {
                            $custom_email_files_id[] = $item['id'];
                        }
                    }
                }

                $file_types_id = array(1, 2, 3, 4, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17);
                if ($email_type == 'fa-request') {
                    $file_types_id = array(1, 3, 4, 11, 12);
                } elseif ($email_type == 'filing-confirmation')
                {
                    $file_types_id = array(6, 17);
                } elseif ($email_type == 'document-instruction')
                {
                    $file_types_id = array(2, 10, 14, 17);
                } elseif ($email_type == 'intake-email')
                {
                    $file_types_id = array(7, 8);
                } elseif ($email_type == 'translation_request') {
                    $file_types_id = array(1);
                    $temp_mailto = array();
                    $translation_associates = $ci->associates->fa_translation_assosiates_for_emails($case_id);
                    if ($translation_associates) {
                        $temp_country_array = array();
                        foreach ($translation_associates as $ass_data) {
                            $temp_mails = explode(',', $ass_data['email']);
                            foreach ($temp_mails as $temp_mail) {
                                $temp_mailto[] = '<a href="mailto:' . $temp_mail . '">' . $temp_mail . '</a>';
                            }
                            $mailto_associate = implode(', ', $temp_mailto);
                            unset($temp_mailto);

                            $trans_ass_data .= $ass_data['country'] . ' - ' . $mailto_associate . '</br>';
                            $temp_country_array[] = $ass_data['country'];
                            $case_countries = implode(', ', $temp_country_array);
                        }
                    } else {
                        return false;
                    }
                }

                $email_files = $this->cases->get_case_files_by_country($case['id'], $country_id_value, $file_types_id);

                if (!is_null($email_files) && is_array($email_files) && (count($email_files) > 0)) {
                    $string = base_convert(md5($country_id_value . $email_type . $case['id'] . $case['case_number']), 16, 10);
//                    $case_files .= '<a href="https://' . $_SERVER['SERVER_NAME'] . '/zip/' . $zip_hash . '">files</a>';
                }
                $bdv = $this->db->get_where("zen_managers", array("id" => $case['sales_manager_id']))->row_array();
                if (!empty($bdv)) {
                    $bdv_name = $bdv['firstname'] . ' ' . $bdv['lastname'];
                }
            }

            // ========================================================================
            // Email content
            // ========================================================================
            // Client data %COUNTRY_REFERENCE_NUMBER% $country_reference_number
            $content = str_replace('%COUNTRY_REFERENCE_NUMBER%', $country_reference_number, $content);
            $content = str_replace('%CLIENT_REFERENCE_NUMBER%', $client_reference_number, $content);
            $content = str_replace('%CLIENT_FIRSTNAME%', trim($client_firstname), $content);
            $content = str_replace('%CLIENT_LASTNAME%', trim($client_lastname), $content);
            $content = str_replace('%CLIENT_EMAIL%', $client_email, $content);
            $content = str_replace('%CLIENT_PHONE%', $client_phone, $content);
            $content = str_replace('%CLIENT_ADDRESS2%', $client_address2, $content);
            $content = str_replace('%CLIENT_ADDRESS%', $client_address, $content);
            $content = str_replace('%CLIENT_COMPANY_NAME%', $client_company_name, $content);
            $content = str_replace('%CLIENT_CITY%', $client_city, $content);
            $content = str_replace('%CLIENT_STATE%', $client_state, $content);
            $content = str_replace('%CLIENT_ZIP_CODE%', $client_zip_code, $content);
            $content = str_replace('%CLIENT_COUNTRY%', $client_country, $content);
            $content = str_replace('%CLIENT_FAX%', $client_fax, $content);
            // Case data
            $content = str_replace('%PARKIP_CASE_NUMBER%', $parkip_case_number, $content);
            $content = str_replace('%CASE_LINK%', $case_link, $content);
            $content = str_replace('%CASE_COUNTRIES%', $case_countries, $content);
            $content = str_replace('%CASE_NUMBER%', $case['case_number'], $content);
            $content = str_replace('%CASE_TYPE%', $case_type, $content);
            $content = str_replace('%CASE_APPLICATION_NUMBER%', $case_application_number, $content);
            $content = str_replace('%CASE_APPLICANT%', $case_applicant, $content);
            $content = str_replace('%CASE_APPLICATION_TITLE%', $case_application_title, $content);
            $content = str_replace('%CASE_FILING_DEADLINE%', $case_filing_deadline, $content);
            $content = str_replace('%CASE_CLIENT_REFERENCE%', $client_reference_number, $content);
            $content = str_replace('%CASE_CC%', $case_cc, $content);
            //$content = str_replace('%CASE_FILES%', $case_files, $content);
            $content = str_replace('%CASE_MANAGER%', $case_manager, $content);
            $content = str_replace('%case_countries_translation-associates_email%', $trans_ass_data, $content);

            $content = str_replace('%ZENFILE_REFERENCE%', $case['case_number'], $content);
            // FA data
            $content = str_replace('%FA_COUNTRY%', $fa_country, $content);
            $content = str_replace('%FA_FEE%', $fa_fee, $content);
            $content = str_replace('%FA_NAME%', $fa_name, $content);
            $content = str_replace('%FA_FILING_DEADLINE_TYPE%', $fa_filing_deadline_type, $content);
            // BDV data
            $content = str_replace('%BDV_NAME%', $bdv_name, $content);


            // ========================================================================
            // Email subject
            // ========================================================================
            // Client data
            $template['subject'] = str_replace('%COUNTRY_REFERENCE_NUMBER%', $country_reference_number, $template['subject']);
            $template['subject'] = str_replace('%CLIENT_REFERENCE_NUMBER%', $client_reference_number, $template['subject']);
            $template['subject'] = str_replace('%CLIENT_FIRSTNAME%', $client_firstname, $template['subject']);
            $template['subject'] = str_replace('%CLIENT_LASTNAME%', $client_lastname, $template['subject']);
            $template['subject'] = str_replace('%CLIENT_EMAIL%', $client_email, $template['subject']);
            $template['subject'] = str_replace('%CLIENT_PHONE%', $client_phone, $template['subject']);
            $template['subject'] = str_replace('%CLIENT_ADDRESS2%', $client_address2, $template['subject']);
            $template['subject'] = str_replace('%CLIENT_ADDRESS%', $client_address, $template['subject']);
            $template['subject'] = str_replace('%CLIENT_COMPANY_NAME%', $client_company_name, $template['subject']);
            $template['subject'] = str_replace('%CLIENT_CITY%', $client_city, $template['subject']);
            $template['subject'] = str_replace('%CLIENT_STATE%', $client_state, $template['subject']);
            $template['subject'] = str_replace('%CLIENT_ZIP_CODE%', $client_zip_code, $template['subject']);
            $template['subject'] = str_replace('%CLIENT_COUNTRY%', $client_country, $template['subject']);
            $template['subject'] = str_replace('%CLIENT_FAX%', $client_fax, $template['subject']);
            // Case data
            $template['subject'] = str_replace('%PARKIP_CASE_NUMBER%', $parkip_case_number, $template['subject']);
            $template['subject'] = str_replace('%CASE_COUNTRIES%', $case_countries, $template['subject']);
            $template['subject'] = str_replace('%CASE_NUMBER%', $case['case_number'], $template['subject']);
            $template['subject'] = str_replace('%CASE_TYPE%', $case_type, $template['subject']);
            $template['subject'] = str_replace('%CASE_APPLICATION_NUMBER%', $case_application_number, $template['subject']);
            $template['subject'] = str_replace('%CASE_APPLICANT%', $case_applicant, $template['subject']);
            $template['subject'] = str_replace('%CASE_APPLICATION_TITLE%', $case_application_title, $template['subject']);
            $template['subject'] = str_replace('%CASE_FILING_DEADLINE%', $case_filing_deadline, $template['subject']);
            $template['subject'] = str_replace('%CASE_CLIENT_REFERENCE%', $client_reference_number, $template['subject']);
            $template['subject'] = str_replace('%CASE_CC%', $case_cc, $template['subject']);
            $template['subject'] = str_replace('%CASE_FILES%', $case_files, $template['subject']);
            $template['subject'] = str_replace('%CASE_MANAGER%', $case_manager, $template['subject']);
            $template['subject'] = str_replace('%ZENFILE_REFERENCE%', $case['case_number'], $template['subject']);
            // FA data
            $template['subject'] = str_replace('%FA_COUNTRY%', $fa_country, $template['subject']);
            $template['subject'] = str_replace('%FA_FEE%', $fa_fee, $template['subject']);
            $template['subject'] = str_replace('%FA_NAME%', $fa_name, $template['subject']);
            $template['subject'] = str_replace('%FA_FILING_DEADLINE_TYPE%', $fa_filing_deadline_type, $template['subject']);
            // BDV data
            $template['subject'] = str_replace('%BDV_NAME%', $bdv_name, $template['subject']);
            $result['text'] = htmlspecialchars_decode($content);
            $result['subject'] = $template['subject'];
        }
        return $result;
    }

    /**
     * Returns a list of files related to case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    mixed
     */
    public function get_related_files($case_number = '')
    {
        $q = 'SELECT cf.*
			  FROM zen_cases_files cf, zen_cases c
			  WHERE (c.case_number = ' . $case_number . ')';
        $query = $this->db->query($q);
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Sets a mark of new email
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @param    int    value: 1 - new email
     * */
    public function set_new_email_sign($case_number, $value = 1)
    {
        $this->db->where('case_number', $case_number);
        $this->db->update('cases', array('new_email_sign' => "$value"));
    }
}

?>