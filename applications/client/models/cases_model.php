<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cases_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_last_user_fee_level($user_id)
    {

        $this->db->where('user_id', $user_id);
        $this->db->where("id = (SELECT MAX(id) FROM zen_cases WHERE user_id = {$user_id})");
        $query = $this->db->get('cases');
        $result = $query->row();

        if ($result) {
            return $result->estimate_fee_level;
        } else {
            // return default fee level
            return 1;
        }
    }

    public function create_case($case_number = '', $addArray = "")
    {
        $this->load->model('estimates_model', 'estimates');
        $this->load->model('profile_model', 'profile');
        $this->load->model('associates_model', 'associates');
        $user_id = $this->session->userdata('client_user_id');
        $application_type = $this->input->post('application_type');
        $notification_each_time = ($this->input->post('notification_each_time') == "yes") ? "1" : "0";
        $additional = $this->input->post('additional');
        $deadline = $this->input->post('deadline');
        $priorityDate = $additional['first_priority_date'];
        if ($application_type == "pct" && empty($deadline) && isset($priorityDate) && !empty($priorityDate)) {
            $date = new DateTime($priorityDate);
            $year = $this->estimates->addYears($date, '2');
            $date = new DateTime($year);
            $filing_deadline = $this->estimates->addMonths($date, '6');
        }
        else
        {
            $filing_deadline = $deadline;
        }
        $is_intake = $this->input->post('is_intake');
        $additional_contacts = $this->input->post('addtional_contacts');
        $application_number = $this->input->post('application_number');
        $application_number = strtoupper(clearString($application_number));
        $parts = explode('/', $application_number);
        if ($parts[0] === 'PCT') {
            $tmp = substr($parts[1], 2);
            if (strlen($tmp) == 2)
                $parts[1] = substr_replace($parts[1], '20', 2, 0);
            $parts[2] = sprintf('%06d', $parts[2]);
            $application_number = implode('/', $parts);
        }

        $common_status = 'estimating-estimate';
        if ($is_intake == '1') {
            $common_status = 'pending-intake';
        }

        $case_type_id = '1';
        if ($application_type == 'direct') {
            $case_type_id = '3';
        } elseif ($application_type == 'ep')
        {
            $case_type_id = '2';
        }
        $bdv = $this->profile->get_bdv();
        $case_data = array(
            'case_number' => $case_number,
            'case_type_id' => $case_type_id,
            'application_number' => $application_number,
            'application_title' => $this->input->post('application_title'),
            'applicant' => $this->input->post('applicant'),
            'filing_deadline' => $filing_deadline,
            'reference_number' => $this->input->post('reference_number'),
            'additional' => $this->input->post('special_instructions'),
            'list_priorities_number' => $this->input->post('list_priorities_number'),
            'last_update' => date('Y-m-d H:i:s'),
            'submitted_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'is_active' => '1',
            'user_id' => $user_id,
            'is_intake' => $is_intake,
            'common_status' => $common_status,
            'sales_manager_id' => $bdv["id"],
            'manager_id' => $this->session->userdata('client_manager_id'),
            'email_notification' => $notification_each_time
        );

        if (check_array($additional)) {
            $case_data['30_month_filing_deadline'] = isset($additional['30_month_filing_deadline']) ? $additional['30_month_filing_deadline'] : '';
            $case_data['31_month_filing_deadline'] = isset($additional['31_month_filing_deadline']) ? $additional['31_month_filing_deadline'] : '';
            $case_data['applicant'] = isset($additional['applicant']) ? $additional['applicant'] : '';
            $case_data['filing_deadline'] = isset($additional['filing_deadline']) ? $additional['filing_deadline'] : '';
            $case_data['first_priority_date'] = isset($additional['first_priority_date']) ? $additional['first_priority_date'] : '';
            $case_data['number_claims'] = isset($additional['number_claims']) ? $additional['number_claims'] : '';
            $case_data['number_pages'] = isset($additional['number_pages']) ? $additional['number_pages'] : '';
            $case_data['number_pages_claims'] = isset($additional['number_pages_claims']) ? $additional['number_pages_claims'] : '';
            $case_data['number_pages_drawings'] = isset($additional['number_pages_drawings']) ? $additional['number_pages_drawings'] : '';
            $case_data['number_priorities_claimed'] = isset($additional['number_priorities_claimed']) ? $additional['number_priorities_claimed'] : '';
            $case_data['number_words'] = isset($additional['number_words']) ? $additional['number_words'] : '';
            $case_data['number_words_in_claims'] = isset($additional['number_words_in_claims']) ? $additional['number_words_in_claims'] : '';
            $case_data['publication_language'] = isset($additional['publication_language']) ? $additional['publication_language'] : '';
            $case_data['search_location'] = isset($additional['search_location']) ? $additional['search_location'] : '';
            $case_data['sequence_listing'] = isset($additional['sequence_listing']) ? $additional['sequence_listing'] : '';
            $case_data['title'] = isset($additional['title']) ? $additional['title'] : '';
            $case_data['wipo_pct_number'] = isset($additional['pct_number']) ? $additional['pct_number'] : '';
            $case_data['wipo_wo_number'] = isset($additional['wo_number']) ? $additional['wo_number'] : '';
        }

        if (isset($addArray) && !empty($addArray)) {
            foreach ($addArray as $key => $value)
            {
                $case_data[$key] = $value;
            }
        }

        $this->db->insert('cases', $case_data);
        $case_id = $this->db->insert_id();
        /* FILL WIPO TABLE */
        $WIPO_DATA = array(
            'title' => isset($case_data["application_title"]) ? $case_data["application_title"] : "",
            'number_priorities_claimed' => isset($case_data['number_priorities_claimed']) ? $case_data['number_priorities_claimed'] : '',
            'number_pages_drawings' => isset($case_data['number_pages_drawings']) ? $case_data['number_pages_drawings'] : '',
            'number_pages_claims' => isset($case_data['number_pages_claims']) ? $case_data['number_pages_claims'] : '',
            'number_pages' => isset($case_data['number_pages']) ? $case_data['number_pages'] : '',
            'first_priority_date' => isset($case_data['first_priority_date']) ? $case_data['first_priority_date'] : '',
            'international_filing_date' => isset($addArray['international_filing_date']) ? $addArray['international_filing_date'] : "",
            'search_location' => isset($case_data['search_location']) ? $case_data['search_location'] : '',
            'applicant' => isset($case_data['applicant']) ? $case_data['applicant'] : '',
            'publication_language' => isset($case_data['publication_language']) ? $case_data['publication_language'] : '',
            '30_month_filing_deadline' => isset($case_data['30_month_filing_deadline']) ? $case_data['30_month_filing_deadline'] : '',
            '31_month_filing_deadline' => isset($case_data['31_month_filing_deadline']) ? $case_data['31_month_filing_deadline'] : '',
            'number_claims' => isset($case_data['number_claims']) ? $case_data['number_claims'] : '',
            'number_words' => isset($case_data['number_words']) ? $case_data['number_words'] : '',
            'number_words_in_claims' => isset($case_data['number_words_in_claims']) ? $case_data['number_words_in_claims'] : '',
            'number_words_in_application' => isset($addArray['number_words_in_application']) ? $addArray['number_words_in_application'] : "",
            'sequence_listing' => isset($case_data['sequence_listing']) ? $case_data['sequence_listing'] : '',
            'wo_number' => isset($case_data["application_number"]) ? $case_data["application_number"] : '',
            'pct_number' => isset($case_data["application_number"]) ? $case_data["application_number"] : '',
        );
        if (!empty($WIPO_DATA)) {
            $this->load->model('wipo_model', 'wipo');
            $WIPO_EXIST = $this->wipo->get_entry($WIPO_DATA["wo_number"]);
            if (empty($WIPO_EXIST)) {
                $this->wipo->append_wipo_data_entry($WIPO_DATA);
            }
        }
        // Additional contacts (to be CC'ed)
        if (check_array($additional_contacts)) {
            $add_contacts_array = array();
            foreach ($additional_contacts as $contact)
            {
                if (!empty($contact)) {
                    $add_contacts_array[] = array(
                        'case_id' => $case_id,
                        'email' => $contact,
                    );
                }
            }

            if (check_array($add_contacts_array)) {
                $this->db->insert_batch('case_contacts', $add_contacts_array);
            }
        }
        // Case countries
        $countries = $this->input->post('countries');
        if (check_array($countries)) {
            $countries_array = array();
            foreach ($countries as $country_id)
            {
                $countries_array[] = array(
                    'case_id' => $case_id,
                    'country_id' => $country_id,
                    'reference_number' => $_POST{'reference_number_for_country_' . $country_id}
                );
            }
            $this->db->insert_batch('cases_countries', $countries_array);
        }
        //insert intake countries to the tracker
        if ($is_intake == '1') {
            if (isset($countries_array)) {
                $this->db->insert_batch('cases_tracker', $countries_array);
            }
            $this->associates->insert_new_associates_to_case_associates_data($case_id, $countries, $case_type_id);

        }
        // Files stuff
        // 1. Move uploaded files to needed folder
        // 2. Assign categories
        $attachments = $this->input->post('attachments');

        if (check_array($attachments)) {
            //var_dump($attachments);exit;
            $attachments_categories = $this->input->post('attachments_categories');
            $random = $this->input->post('random');

            $user_path = $this->config->item('path_upload') . 'pm/uploads/' . $user_id . '/';
            if ($_SERVER['HTTP_HOST'] == 'zenfile.local')
                $user_path = $this->config->item('http_path_upload') . 'pm/uploads/' . $user_id . '/';
            $case_path = $user_path . $case_number . '/';

            if (!is_dir($user_path)) {
                mkdir($user_path, 0775);
            }
            if (!is_dir($case_path)) {
                mkdir($case_path);
            }
            $case_files = array();
            foreach ($attachments as $index => $attachment)
            {
                $pinfo = pathinfo($attachment);
                $file_path = $case_path . $pinfo['basename'];
                $file_type_id = (isset($attachments_categories[$index])) ? $attachments_categories[$index] : NULL;

                if (copy($attachment, $file_path)) {
                    if ($_SERVER['HTTP_HOST'] == 'zenfile.local') {
                        $finfo = finfo_open(FILEINFO_MIME, '/usr/share/misc/magic.mgc');
                    } elseif ($_SERVER['HTTP_HOST'] == 'zen' || $_SERVER['HTTP_HOST'] == 'lastzenfile') {
                        $finfo = finfo_open(FILEINFO_MIME);
                    } else {
                        $finfo = finfo_open(FILEINFO_MIME, '/usr/share/misc/magic');
                    }


                    $mime_type = finfo_file($finfo, $file_path);
                    finfo_close($finfo);
                    $case_files[] = array(
                        'case_id' => $case_id,
                        'user_id' => $user_id,
                        'filename' => $pinfo['basename'],
                        'location' => str_replace($this->config->item('path_upload') . 'pm/', '', $file_path),
                        'mime_type' => $mime_type,
                        'filesize' => filesize($file_path),
                        'created_at' => date('Y-m-d H:i:s'),
                        'owner' => 'customer',
                        'visibility' => '1',
                        'file_type_id' => $file_type_id,
                    );
                    $file = APPPATH . '../../client/uploads/' . $pinfo['basename'];
                    if (file_exists($file))
                        unlink($file);
                }
            }
            if (check_array($case_files)) {
                $this->db->insert_batch('cases_files', $case_files);
            }
        }

        $this->load->model('emails_model', 'emails');
        $this->emails->create_email_account_for_case($case_number);
        /* Sending welcome message to client */
        if ($is_intake == "1")
            $this->sendConfEmailToClient($case_number, '1');
        else
            $this->sendConfEmailToClient($case_number, '20');
        /* end sendign welcome message to client */
        $this->sendConfEmailToManager($case_number, '26');
        $this->sendConfEmailToManager($case_number, '36');
        return TRUE;
    }

    /**
     * Send confirmation of creating estimate to Client
     *
     * @access     private
     * @author    Stan Voinov <stan.voinov@gmail.com>
     * @param string $type type of given value (case_number,case_id)
     * @param    string    case number
     * @param    int    email template id
     * @return    bool
     */
    public function sendConfEmailToClient($case_number = "", $tpl_num = '0', $country_id = "")
    {

        $this->load->model('send_emails_model', 'send_emails');
        $CASE = $this->find_case_by_number($case_number);
        $USER = $this->get_customer_by_case_number($case_number);
        if (!empty($CASE)) {
            $case_manager = '';
            $this->db->where('id', $CASE["id"]);
            $query = $this->db->get('cases');
            $case_manager_temp = $query->row_array();
            if ($case_manager_temp) {
                $this->db->where('id', $case_manager_temp['manager_id']);
                $query = $this->db->get('managers');
                $temp_data = $query->row_array();
                $case_manager = (isset($temp_data['firstname']) ? $temp_data['firstname'] : '') . ' ' . (isset($temp_data['lastname']) ? $temp_data['lastname'] : '');
            }
        }
        if ($USER && $CASE) {
            if ($this->session->userdata('client_allow_email') == 'yes') {
                $case_user = $USER["firstname"] . ' ' . $USER["lastname"];
                $cc = '';
                $from = 'case' . $CASE['case_number'] . $this->config->item('default_email_box');
                $CONTACTS = $this->get_case_contacts($CASE["id"]);
                if (!empty($CONTACTS)) {
                    foreach ($CONTACTS as $contact)
                    {
                        if (!empty($contact['email'])) {
                            $cc[] = $contact["email"];
                        }
                    }
                }
                $to = $USER["email"];
                if (TEST_MODE) {
                    $to = TEST_CLIENT_EMAIL;
                    if ($USER['type'] == 'firm') {
                        $to = TEST_FIRM_EMAIL;
                    }
                }

                /******************************************** GET EMAIL TEMPLATE *****************************************/
                $TEMPLATE = $this->db->get_where("zen_emails_templates", array("id" => $tpl_num))->row_array();

                if (!empty($TEMPLATE)) {

                    /******************************************* GET CASE TYPE **********************************************/
                    $CASE_TYPE = "";
                    if (
                        (strpos($TEMPLATE["subject"], "%CASE_TYPE%") != false)
                        ||
                        (strpos($TEMPLATE["content"], "%CASE_TYPE%") != false)
                    ) {
                        $CASE_TYPE = $this->db->get_where("zen_case_types", array("id" => $CASE["case_type_id"]))->row_array();
                        if (!empty($CASE_TYPE))
                            $CASE_TYPE = $CASE_TYPE["type"];
                        else
                            $CASE_TYPE = "";
                    }

                    /***************************************** GENERATING CASE COUNTRIES ************************************/
                    $CASE_COUNTRIES = "";
                    if (
                        (strpos($TEMPLATE["subject"], "%CASE_COUNTRIES%") != false)
                        ||
                        (strpos($TEMPLATE["content"], "%CASE_COUNTRIES%") != false)
                    ) {
                        $CASE_COUNTRIES_RAW = $this->db->query("SELECT DISTINCT zen_countries.country
                                            FROM zen_countries
                                            LEFT JOIN zen_cases_countries ON zen_countries.id = zen_cases_countries.country_id
                                            LEFT JOIN zen_cases ON zen_cases_countries.case_id = zen_cases.id
                                            WHERE zen_cases.id =" . $CASE["id"])->result_array();
                        if (!empty($CASE_COUNTRIES_RAW)) {
                            foreach ($CASE_COUNTRIES_RAW as $country)
                            {
                                $CASE_COUNTRIES[] = $country["country"];
                            }
                            $CASE_COUNTRIES = implode(", ", $CASE_COUNTRIES);
                        }
                    }
                    /****************************************** GENERATING CASE FILES ****************************************/
                    $CASE_FILES = "";
                    if (
                        (strpos($TEMPLATE["subject"], "%CASE_FILES%") != false)
                        ||
                        (strpos($TEMPLATE["content"], "%CASE_FILES%") != false)
                    ) {
                        $CF = $this->db->get_where("zen_cases_files", array("case_id" => $CASE["id"]))->result_array();
                        if (!empty($CF)) {
                            foreach ($CF as $file)
                            {
                                $CASE_FILES[] = "<a href='" . site_url($file["location"]) . "' alt='" . $file["filename"] . "'>" . $file["filename"] . "</a>";
                            }
                            $CASE_FILES = implode(",", $CASE_FILES);
                        }
                    }
                    /***************************************************** GET BDV NAME **************************************/
                    $BDV_NAME = "";
                    if (
                        (strpos($TEMPLATE["subject"], "%BDV_NAME%") != false)
                        ||
                        (strpos($TEMPLATE["content"], "%BDV_NAME%") != false)
                    ) {
                        if (empty($USER["bdv_id"])) {
                            $BDV_NAME = "";
                        }
                        else
                        {
                            $BDV_NAME = $this->db->get_where("zen_managers", array("id" => $USER["bdv_id"]))->row_array();
                            if (!empty($BDV_NAME)) {
                                $BDV_EMAIL = $BDV_NAME["email"];
                                $BDV_NAME = $BDV_NAME["firstname"] . " " . $BDV_NAME["lastname"];

                            } else
                                $BDV_NAME = "";
                        }
                    }
                    /*********************************************************************************************************/

                    /* REPLACEMENT FOR SUBJECT */
                    $TEMPLATE["subject"] = str_replace("%CLIENT_REFERENCE_NUMBER%", $CASE["reference_number"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CLIENT_FIRSTNAME%", $USER["firstname"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CLIENT_LASTNAME%", $USER["lastname"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CLIENT_EMAIL%", $USER["email"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CLIENT_PHONE%", $USER["phone_number"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CLIENT_ADDRESS2%", $USER["address2"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CLIENT_ADDRESS%", $USER["address"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CLIENT_COMPANY_NAME%", $USER["company_name"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CLIENT_CITY%", $USER["city"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CLIENT_STATE%", $USER["state"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CLIENT_ZIP_CODE%", $USER["zip_code"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CLIENT_COUNTRY%", $USER["country"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CLIENT_FAX%", $USER["fax"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CASE_LINK%", site_url("cases/view/" + $CASE["case_number"]), $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CASE_COUNTRIES%", $CASE_COUNTRIES, $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CASE_NUMBER%", $CASE["case_number"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%PARKIP_CASE_NUMBER%", $CASE["parkip_case_number"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CASE_TYPE%", $CASE_TYPE, $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CASE_APPLICATION_NUMBER%", $CASE["application_number"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CASE_APPLICANT%", $CASE["applicant"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CASE_APPLICATION_TITLE%", $CASE["application_title"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CASE_FILING_DEADLINE%", $CASE["filing_deadline"], $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%CASE_FILES%", $CASE_FILES, $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%FA_FEE%", "", $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%FA_NAME%", "", $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%FA_COUNTRY%", "", $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%FA_FILING_DEADLINE_TYPE%", "", $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace("%BDV_NAME%", $BDV_NAME, $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace('%CASE_MANAGER%', $case_manager, $TEMPLATE["subject"]);
                    $TEMPLATE["subject"] = str_replace('%CASE_USER%', $case_user, $TEMPLATE["subject"]);

                    /* REPLACEMENT FOR MESSAGE */
                    $TEMPLATE["content"] = str_replace("%CLIENT_REFERENCE_NUMBER%", $CASE["reference_number"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CLIENT_FIRSTNAME%", $USER["firstname"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CLIENT_LASTNAME%", $USER["lastname"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CLIENT_EMAIL%", $USER["email"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CLIENT_PHONE%", $USER["phone_number"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CLIENT_ADDRESS2%", $USER["address2"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CLIENT_ADDRESS%", $USER["address"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CLIENT_COMPANY_NAME%", $USER["company_name"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CLIENT_CITY%", $USER["city"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CLIENT_STATE%", $USER["state"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CLIENT_ZIP_CODE%", $USER["zip_code"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CLIENT_COUNTRY%", $USER["country"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CLIENT_FAX%", $USER["fax"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CASE_LINK%", site_url("cases/view/" + $CASE["case_number"]), $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CASE_COUNTRIES%", $CASE_COUNTRIES, $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CASE_NUMBER%", $CASE["case_number"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%PARKIP_CASE_NUMBER%", $CASE["parkip_case_number"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CASE_TYPE%", $CASE_TYPE, $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CASE_APPLICATION_NUMBER%", $CASE["application_number"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CASE_APPLICANT%", $CASE["applicant"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CASE_APPLICATION_TITLE%", $CASE["application_title"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CASE_FILING_DEADLINE%", $CASE["filing_deadline"], $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%CASE_FILES%", $CASE_FILES, $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%FA_FEE%", "", $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%FA_NAME%", "", $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%FA_COUNTRY%", "", $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%FA_FILING_DEADLINE_TYPE%", "", $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace("%BDV_NAME%", $BDV_NAME, $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace('%CASE_MANAGER%', $case_manager, $TEMPLATE["content"]);
                    $TEMPLATE["content"] = str_replace('%CASE_USER%', $case_user, $TEMPLATE["content"]);

                    if ($this->send_emails->send_email($from, $from, $TEMPLATE["subject"], $TEMPLATE["content"], $to, $cc, false)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function sendConfEmailToManager($case_number = "", $tpl_num = 0, $reestimate = '', $country_filing_deadline ='')
    {
        $this->load->model('send_emails_model', 'send_emails');
        $this->load->model('customers_model', 'customers');
        $CASE = $this->find_case_by_number($case_number);
        $USER = $this->get_customer_by_case_number($case_number);
        if (!empty($CASE)) {
            $case_tmp_type = 'estimate';
            if ($CASE['is_intake'] == '1') {
                $case_tmp_type = 'intake';
            }
            $case_manager = '';
            $this->db->where('id', $CASE["id"]);
            $query = $this->db->get('cases');
            $case_manager_temp = $query->row_array();
            if ($case_manager_temp) {
                $this->db->where('id', $case_manager_temp['manager_id']);
                $query = $this->db->get('managers');
                $temp_data = $query->row_array();
                $case_manager = (isset($temp_data['firstname']) ? $temp_data['firstname'] : '') . ' ' . (isset($temp_data['lastname']) ? $temp_data['lastname'] : '');
            }
            $from = 'case' . $CASE['case_number'] . $this->config->item('default_email_box');
            $case_user = $USER["firstname"] . ' ' . $USER["lastname"];

            /******************************************** GET EMAIL TEMPLATE *****************************************/
            $TEMPLATE = $this->db->get_where("zen_emails_templates", array("id" => $tpl_num))->row_array();
            if (!empty($TEMPLATE)) {
                /******************************************* GET CASE TYPE **********************************************/
                $CASE_TYPE = "";
                if (
                    (strpos($TEMPLATE["subject"], "%CASE_TYPE%") != false)
                    ||
                    (strpos($TEMPLATE["content"], "%CASE_TYPE%") != false)
                ) {
                    $CASE_TYPE = $this->db->get_where("zen_case_types", array("id" => $CASE["case_type_id"]))->row_array();
                    if (!empty($CASE_TYPE))
                        $CASE_TYPE = $CASE_TYPE["type"];
                    else
                        $CASE_TYPE = "";
                }
                /***************************************** GENERATING CASE COUNTRIES ************************************/
                $CASE_COUNTRIES = "";
                if (
                    (strpos($TEMPLATE["subject"], "%CASE_COUNTRIES%") != false)
                    ||
                    (strpos($TEMPLATE["content"], "%CASE_COUNTRIES%") != false)
                ) {
                    $CASE_COUNTRIES_RAW = $this->db->query("SELECT DISTINCT zen_countries.country
                                            FROM zen_countries
                                            LEFT JOIN zen_cases_countries ON zen_countries.id = zen_cases_countries.country_id
                                            LEFT JOIN zen_cases ON zen_cases_countries.case_id = zen_cases.id
                                            WHERE zen_cases.id =" . $CASE["id"])->result_array();
                    if ($reestimate == '1') {
                        $this->db->select('countries.country');
                        $this->db->join('estimates_countries_fees', 'countries.id = estimates_countries_fees.country_id', 'left');
                        $this->db->where('estimates_countries_fees.case_id', $CASE["id"]);
                        $this->db->where('estimates_countries_fees.is_approved', '1');
                        $this->db->where('estimates_countries_fees.parent_id', 0);
                        $query = $this->db->get('countries');
                        $CASE_COUNTRIES_RAW = $query->result_array();
                    }

                    if (!empty($CASE_COUNTRIES_RAW)) {
                        foreach ($CASE_COUNTRIES_RAW as $country)
                        {
                            $CASE_COUNTRIES[] = $country["country"];
                        }
                        $CASE_COUNTRIES = implode(", ", $CASE_COUNTRIES);
                    }
                }
                /****************************************** GENERATING CASE FILES ****************************************/
                $CASE_FILES = "";
                if (
                    (strpos($TEMPLATE["subject"], "%CASE_FILES%") != false)
                    ||
                    (strpos($TEMPLATE["content"], "%CASE_FILES%") != false)
                ) {
                    $CF = $this->db->get_where("zen_cases_files", array("case_id" => $CASE["id"]))->result_array();
                    if (!empty($CF)) {
                        foreach ($CF as $file)
                        {
                            $CASE_FILES[] = "<a href='" . site_url($file["location"]) . "' alt='" . $file["filename"] . "'>" . $file["filename"] . "</a>";
                        }
                        $CASE_FILES = implode(", ", $CASE_FILES);
                    }
                }
                /***************************************************** GET BDV NAME **************************************/
                $BDV_NAME = "";
                $BDV_EMAIL = '';
                if (
                    (strpos($TEMPLATE["subject"], "%BDV_NAME%") != false)
                    ||
                    (strpos($TEMPLATE["content"], "%BDV_NAME%") != false)
                ) {
                    if (empty($USER["bdv_id"])) {
                        $BDV_NAME = "";
                    }
                    else
                    {
                        $BDV_NAME = $this->db->get_where("zen_managers", array("id" => $USER["bdv_id"]))->row_array();
                        if (!empty($BDV_NAME)) {
                            $BDV_EMAIL = $BDV_NAME["email"];
                            $BDV_NAME = $BDV_NAME["firstname"] . " " . $BDV_NAME["lastname"];
                        } else
                            $BDV_NAME = "";
                    }
                }
                /*********************************************************************************************************/

                /* REPLACEMENT FOR SUBJECT */
                $TEMPLATE["subject"] = str_replace("%approve_by_deadline%", $country_filing_deadline, $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%CLIENT_REFERENCE_NUMBER%", $CASE["reference_number"], $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%CLIENT_FIRSTNAME%", $USER["firstname"], $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%CLIENT_LASTNAME%", $USER["lastname"], $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%CASE_LINK%", '<a href="https://' . $_SERVER["HTTP_HOST"] . '/pm/cases/view/' . $CASE["case_number"] . '">Case</a>', $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%CASE_COUNTRIES%", $CASE_COUNTRIES, $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%CASE_NUMBER%", $CASE["case_number"], $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%PARKIP_CASE_NUMBER%", $CASE["parkip_case_number"], $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%CASE_TYPE%", $case_tmp_type . ' ' . $CASE_TYPE, $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%CASE_APPLICATION_NUMBER%", $CASE["application_number"], $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%CASE_APPLICANT%", $CASE["applicant"], $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%CASE_APPLICATION_TITLE%", $CASE["application_title"], $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%CASE_FILING_DEADLINE%", $CASE["filing_deadline"], $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%CASE_FILES%", $CASE_FILES, $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%FA_FEE%", "", $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%FA_NAME%", "", $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%FA_COUNTRY%", "", $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%FA_FILING_DEADLINE_TYPE%", "", $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace('%CASE_USER%', $case_user, $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace("%BDV_NAME%", $BDV_NAME, $TEMPLATE["subject"]);
                $TEMPLATE["subject"] = str_replace('%CASE_MANAGER%', $case_manager, $TEMPLATE["subject"]);


                /* REPLACEMENT FOR MESSAGE */
                $TEMPLATE["content"] = str_replace("%approve_by_deadline%", $country_filing_deadline, $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%CLIENT_REFERENCE_NUMBER%", $CASE["reference_number"], $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%CLIENT_FIRSTNAME%", $USER["firstname"], $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%CLIENT_LASTNAME%", $USER["lastname"], $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%CASE_LINK%", '<a href="https://' . $_SERVER["HTTP_HOST"] . '/pm/cases/view/' . $CASE["case_number"] . '">Case</a>', $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%CASE_COUNTRIES%", $CASE_COUNTRIES, $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%CASE_NUMBER%", $CASE["case_number"], $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%PARKIP_CASE_NUMBER%", $CASE["parkip_case_number"], $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%CASE_TYPE%", $case_tmp_type . ' ' . $CASE_TYPE, $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%CASE_APPLICATION_NUMBER%", $CASE["application_number"], $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%CASE_APPLICANT%", $CASE["applicant"], $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%CASE_APPLICATION_TITLE%", $CASE["application_title"], $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%CASE_FILING_DEADLINE%", $CASE["filing_deadline"], $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%CASE_FILES%", $CASE_FILES, $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%FA_FEE%", "", $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%FA_NAME%", "", $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%FA_COUNTRY%", "", $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%FA_FILING_DEADLINE_TYPE%", "", $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace('%CASE_USER%', $case_user, $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace("%BDV_NAME%", $BDV_NAME, $TEMPLATE["content"]);
                $TEMPLATE["content"] = str_replace('%CASE_MANAGER%', $case_manager, $TEMPLATE["content"]);
                $cc = '';

                if (TEST_MODE) {
                    $TEMPLATE["subject"] = str_replace('%PM_FIRSTNAME%', 'TEST MODE PM FIRSTNAME', $TEMPLATE["subject"]);
                    $TEMPLATE["content"] = str_replace('%PM_FIRSTNAME%', 'TEST MODE PM FIRSTNAME', $TEMPLATE["content"]);
                    $to = TEST_PM_EMAIL;
                    if ($tpl_num == '32'|| $tpl_num == '29') {
                        $cc[] = TEST_BDV_EMAIL;

                    }
                    if ($tpl_num == '36') {
                        $to = TEST_BDV_EMAIL;
                    }

                    if ($this->send_emails->send_email($from, $from, $TEMPLATE["subject"], $TEMPLATE["content"], $to, $cc, false)) {
                        return true;
                    }

                }
                else
                {
                    if ($tpl_num == '36') {
                        $to = $BDV_EMAIL;
                        $this->send_emails->send_email($from, $from, $TEMPLATE["subject"], $TEMPLATE["content"], $to, $cc, false);
                        return true;
                    }
                    if ($CASE['manager_id']) {
                        $manager = $this->customers->get_managers($CASE['manager_id']);
                        if ($manager) {
                            $TEMPLATE["subject"] = str_replace('%PM_FIRSTNAME%', $manager['firstname'], $TEMPLATE["subject"]);
                            $TEMPLATE["content"] = str_replace('%PM_FIRSTNAME%', $manager['firstname'], $TEMPLATE["content"]);
                            $to = $manager["email"];
                        } else {
                            return false;
                        }

                        if ($tpl_num == '32'|| $tpl_num == '29') {
                            $cc = $BDV_EMAIL;
                        }

                        if ($this->send_emails->send_email($from, $from, $TEMPLATE["subject"], $TEMPLATE["content"], $to, $cc, false)) {
                            return true;
                        }
                    }
                    $managers = $this->customers->get_managers();
                    if ($managers) {
                        foreach ($managers as $manager)
                        {
                            $temp = $this->replace_template_manager_first_name($TEMPLATE, $manager['firstname']);
                            $to = $manager["email"];
                            $this->send_emails->send_email($from, $from, $temp["subject"], $temp["content"], $to, $cc, false);

                        }

                        return true;
                    }

            }
        }
        }
        return false;
    }

    public function replace_template_manager_first_name($TEMPLATE, $manager)
    {
        $tmp["subject"] = str_replace('%PM_FIRSTNAME%', $manager, $TEMPLATE["subject"]);
        $tmp["content"] = str_replace('%PM_FIRSTNAME%', $manager, $TEMPLATE["content"]);
        return $tmp;
    }

    /**
     * get Contacts for Case by case number
     *
     * @access     private
     * @author    Stan Voinov <stan.voinov@gmail.com>
     * @param    mixed    where
     * @return    array
     */
    public function get_case_contacts($case_id)
    {
        $query = $this->db
            ->select('*')
            ->from('case_contacts')
            ->where('case_id', $case_id)
            ->get();

        if ($query->num_rows()) {
            return $query->result_array();
        }
        return null;
    }

    public function get_active_cases($sort = "ASC")
    {
        $user_id = $this->session->userdata('client_user_id');
        $firm = $this->check_firm($user_id);
        if ($firm) {
            $insert_query = '( user_id in(' . $firm . ',' . $user_id . '))';
            $join = "LEFT JOIN `zen_customers` cus
			  ON (c.user_id = cus.id)";
            $select = ",cus.firstname, cus.lastname, cus.username";
        } else {
            $insert_query = '(user_id = ' . $user_id . ')';
            $join = '';
            $select = '';
        }
        $q = 'SELECT c.*,
				     DATE_FORMAT(filing_deadline, "%m/%d/%Y") as filing_deadline,
				     DATE_FORMAT(30_month_filing_deadline, "%m/%d/%Y") as 30_month_filing_deadline,
				     DATE_FORMAT(31_month_filing_deadline, "%m/%d/%Y") as 31_month_filing_deadline,
					 DATE_SUB(`filing_deadline`, INTERVAL 7 DAY) as filing_deadline_week_ago' . $select . '
			  FROM `zen_cases` c
			  ' . $join . '
			  WHERE (c.common_status = "active") AND
			  (c.is_active = "1")AND
			  		' . $insert_query . '
			  ORDER BY c.case_number ' . $sort;
        $query = $this->db->query($q);
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns a list of pending cases
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param $sort - "ASC","DESC"
     * @return    mixed
     * */
    public function get_pending_cases($sort = "ASC")
    {
        $user_id = $this->session->userdata('client_user_id');
        $firm = $this->check_firm($user_id);
        if ($firm) {
            $insert_query = '( user_id in(' . $firm . ',' . $user_id . '))';
            $join = "LEFT JOIN `zen_customers` cus
			  ON (c.user_id = cus.id)";
            $select = ",cus.firstname, cus.lastname, cus.username";
        } else {
            $insert_query = '(user_id = ' . $user_id . ')';
            $join = '';
            $select = '';
        }

        $q = 'SELECT c.*, (SELECT MIN(country_filing_deadline) FROM `zen_estimates_countries_fees` WHERE case_id = c.id) as approve_by ,
				     DATE_FORMAT(filing_deadline, "%m/%d/%Y") as filing_deadline,
				     DATE_FORMAT(30_month_filing_deadline, "%m/%d/%Y") as 30_month_filing_deadline,
				     DATE_FORMAT(31_month_filing_deadline, "%m/%d/%Y") as 31_month_filing_deadline,
					 DATE_SUB(`filing_deadline`, INTERVAL 7 DAY) as filing_deadline_week_ago' . $select . '
			  FROM `zen_cases` c
			  ' . $join . '
			  WHERE (common_status IN ("estimating", "pending-intake", "pending-approval", "estimating-estimate", "estimating-reestimate")) AND
			  (is_active = "1") AND
			  		' . $insert_query . '
			  ORDER BY c.case_number ' . $sort;
        $query = $this->db->query($q);
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    public function check_firm($user_id)
    {
        $childs_users = '';
        $this->db->select('id');
        $this->db->where('parent_firm_id', $user_id);
        $query = $this->db->get('customers');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $firm) {
                $childs_users .= $firm['id'] . ',';
            }
            $childs_users = substr($childs_users, 0, -1);
        }
        return $childs_users;
    }

    /**
     * Returns a list of user cases (completed cases)
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    user ID
     * @param   $sort - "ASC","DESC"
     * @return    mixed
     */
    public function get_completed_cases($sort = "ASC")
    {
        $user_id = $this->session->userdata('client_user_id');
        $firm = $this->check_firm($user_id);
        if ($firm) {
            $insert_query = '( user_id in(' . $firm . ',' . $user_id . '))';
            $join = "LEFT JOIN `zen_customers` cus
			  ON (c.user_id = cus.id)";
            $select = ",cus.firstname, cus.lastname, cus.username";
        } else {
            $insert_query = '(user_id = ' . $user_id . ')';
            $join = '';
            $select = '';
        }
        $q = 'SELECT c.*,
				     DATE_FORMAT(filing_deadline, "%m/%d/%Y") as filing_deadline,
				     DATE_FORMAT(30_month_filing_deadline, "%m/%d/%Y") as 30_month_filing_deadline,
				     DATE_FORMAT(31_month_filing_deadline, "%m/%d/%Y") as 31_month_filing_deadline,
					 DATE_SUB(`filing_deadline`, INTERVAL 7 DAY) as filing_deadline_week_ago' . $select . '
			  FROM `zen_cases` c
			  ' . $join . '
			  WHERE (common_status = "completed") AND
			  (is_active = "1")AND
			  		' . $insert_query . '
			  ORDER BY c.case_number ' . $sort;
        $query = $this->db->query($q);
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns a case entry
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    user ID
     * @return    mixed
     */
    public function get_case($case_id = '', $user_id = '', $skip_user_id = FALSE)
    {
        $user_id = (empty($user_id)) ? $this->session->userdata('client_user_id') : $user_id;
        $this->db->select('cases.*, DATE_FORMAT(zen_cases.filing_deadline, "%m/%d/%y") as filing_deadline, DATE_FORMAT(zen_cases.30_month_filing_deadline, "%m/%d/%y") as 30_month_filing_deadline, DATE_FORMAT(zen_cases.12_month_filing_deadline, "%m/%d/%y") as 12_month_filing_deadline, DATE_FORMAT(zen_cases.31_month_filing_deadline, "%m/%d/%y") as 31_month_filing_deadline, DATE_FORMAT(zen_cases.first_priority_date, "%m/%d/%y") as first_priority_date, DATE_FORMAT(zen_cases.30_month_filing_deadline, "%m/%d/%y") as 30_month_filing_deadline, DATE_FORMAT(zen_cases.31_month_filing_deadline, "%m/%d/%y") as 31_month_filing_deadline, DATE_FORMAT(zen_cases.created_at, "%m/%d/%y %r") as created_at, DATE_FORMAT(zen_cases.last_update, "%m/%d/%y %r") as last_update, DATE_FORMAT(zen_cases.publication_date, "%m/%d/%y") as publication_date, DATE_FORMAT(zen_cases.international_filing_date, "%m/%d/%y") as international_filing_date, DATE_FORMAT(zen_cases.filing_deadline, "%m/%d/%y") as case_filing_deadline, case_types.type as case_type', FALSE);
        if (!$skip_user_id) {
            $this->db->where('user_id', $user_id);
        }
        $this->db->where('cases.id', $case_id);
        $this->db->join('case_types', 'case_types.id = cases.case_type_id');
        $query = $this->db->get('cases');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Returns case number for new case
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    int
     */
    public function generate_case_number()
    {
        $q = 'SELECT MAX(`case_number`) as last_case_number FROM `zen_cases`';
        $query = $this->db->query($q);
        $last_case_number = $query->row_array();
        return intval($last_case_number['last_case_number'] + 1);
    }

    public function generate_case_number_for_related_case($case_number = '', $case_id = '')
    {
        $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // 1. Get depth of case
        $q = 'SELECT COUNT(id) as depth
			  FROM `zen_related_cases` rc
			  WHERE (rc.child_case_id = ' . (int)$case_id . ') OR
			  		(rc.parent_case_id = ' . (int)$case_id . ')';
        $query = $this->db->query($q);
        $result = $query->row_array();

        $depth = $result['depth'] > 0 ? $result['depth'] : 0;
        return $case_number . $letters[$depth];
    }

    /**
     * Returns a list of case types
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     */
    public function get_case_types()
    {
        $query = $this->db->get('case_types');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns a list of files that uploaded for selected case
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    int user ID
     * @return    mixed
     */
    public function get_case_files($case_id = '', $types_array = '', $date_created = '')
    {
        $this->db->select('cases_files.*, file_types.name');
        $this->db->join('file_types', 'file_types.id = cases_files.file_type_id', 'left');
        if ((!empty($types_array)) && (is_array($types_array))) {
            $this->db->where_in('file_type_id', $types_array);
        }
        if ($date_created) {
            $this->db->where('Unix_Timestamp(created_at) <', $date_created);
        }
        $this->db->where('case_id', $case_id);
        $this->db->where('visibility', '1');
        $this->db->order_by('created_at', 'asc');
        $query = $this->db->get('cases_files');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    public function get_fa_case_files($case_id, $country_id,$types_array = ''){
        $this->db->select('cases_files.*,cases_files.id as file_id_link, cases_files_data.*, file_types.name');
        $this->db->join('file_types', 'file_types.id = cases_files.file_type_id', 'left');
        $this->db->join('cases_files_data', 'cases_files_data.file_id = cases_files.id', 'left');
        $this->db->join('files_countries', 'files_countries.file_id = cases_files.id', 'left');
        $this->db->where('case_id', $case_id);
        $this->db->where('country_id', $country_id);
        $this->db->where('visible_to_fa', '1');
        $this->db->where_in('file_type_id', $types_array);
        $this->db->order_by('created_at', 'asc');
        $query = $this->db->get('cases_files');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    public function check_document_required_for_country($case_id,$country_id){
        $this->db->select('doc_required');
        $this->db->where('case_id', $case_id);
        $this->db->where('country_id', $country_id);
        $query = $this->db->get('cases_tracker');
        if($query->num_rows()){
            return $query->row_array();
        }
    }

    public function get_parent_case_files($case_id = '')
    {
        $this->db->select('cases_files.*, file_types.name');
        $this->db->join('file_types', 'file_types.id = cases_files.file_type_id', 'left');
        $this->db->where('case_id', $case_id);
        $this->db->order_by('created_at', 'asc');
        $query = $this->db->get('cases_files');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    public function save_document_requirements($document_data){
        $this->db->where('file_id',$document_data['file_id']);
        $this->db->where('fa_id', $document_data['fa_id']);
        $query = $this->db->get('cases_files_data');
        if ($query->num_rows()) {
            $tmp = $query->row_array();
            $this->db->where('id',$tmp['id']);
            $this->db->update('cases_files_data', $document_data);
        }else{
            $this->db->insert('cases_files_data', $document_data);
        }
        echo $this->db->last_query();exit;
    }

    public function get_case_files_with_country_array($case_id = '', $file_types = array(), $country)
    {

        $this->db->select('cases_files.*, file_types.name');
        $this->db->join('file_types', 'file_types.id = cases_files.file_type_id', 'left');
        $this->db->join('files_countries', 'files_countries.file_id = cases_files.id');
        $this->db->where('files_countries.country_id', $country);
        $this->db->where('cases_files.case_id', $case_id);
        $this->db->where('cases_files.visibility', '1');
        $this->db->where_in('cases_files.file_type_id', $file_types);
        $query = $this->db->get('cases_files');

        return $query->result_array();

    }

    public function create_zip($case_number, $country_id = '')
    {

        ini_set('memory_limit', '256M');
        $ci = & get_instance();
        $this->load->model('countries_model', 'countries');
        $this->load->library('zip');
        $this->db->select('id')
            ->where('case_number', $case_number);
        $query = $this->db->get('cases');
        $case_id = $query->result();
        $files = $this->get_case_files($case_id[0]->id, $file_types = array(6));

        if (check_array($files)) {
            if (file_exists('uploads/tmp/' . $case_number . '.zip')) {
                @unlink('uploads/tmp/' . $case_number . '.zip');
            }
            if (file_exists('uploads/tmp/' . $case_number . '/')) {
                @rrmdir('uploads/tmp/' . $case_number . '/');
            }
            if (is_dir('uploads/tmp/' . $case_number . '/') || @mkdir('uploads/tmp/' . $case_number . '/', 0777)) {
                if ($country_id) {
                    $this->db->select('country');
                    $this->db->where('id', $country_id);
                    $query = $this->db->get('countries');
                    $onecountry = $query->result();
                    $country_dir = 'uploads/tmp/' . $case_number . '/' . $onecountry[0]->country . '/';
                    if (file_exists($country_dir)) {
                        @rmdir($country_dir);
                        @rrmdir($country_dir);
                    }
                    if (@mkdir($country_dir, 0777)) {
                        // 5,6 - Filing Receipt and Filing Report
                        if (!is_null($country_files = $this->get_case_files_with_country_array($case_id[0]->id, $file_types = array(6), $country_id))) {
                            foreach ($country_files as $file)
                            {
                                @copy('../pm/' . $file['location'], $country_dir . $file['filename']);
                            }
                        }
                        $this->zip->read_dir($country_dir, FALSE);
                    }
                } else {
                    if (!is_null($countries = $this->countries->get_case_countries($case_id[0]->id))) {
                        foreach ($countries as $country)
                        {
                            $country_dir = 'uploads/tmp/' . $case_number . '/' . $country['country'] . '/';
                            if (file_exists($country_dir)) {
                                @rmdir($country_dir);
                                @rrmdir($country_dir);
                            }
                            if (@mkdir($country_dir, 0777)) {
                                // 5,6 - Filing Receipt and Filing Report
                                if (!is_null($country_files = $this->get_case_files_with_country_array($case_id[0]->id, $file_types = array(6), $country['id']))) {
                                    foreach ($country_files as $file)
                                    {
                                        @copy('../pm/' . $file['location'], $country_dir . $file['filename']);
                                    }
                                }
                                $this->zip->read_dir($country_dir, FALSE);
                            }
                        }
                    }

                }


                $this->zip->archive('uploads/tmp/' . $case_number . '.zip');
            }
            if ($country_id) {
                return $onecountry[0]->country;
            } else {
                return 'all countries';
            }
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Returns a list of files that uploaded for selected case by given file types
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    int    user ID
     * @param    array file types
     * @return    mixed
     */
    public function get_case_files_by_type($case_id = '', $user_id = '', $files_types = array())
    {
        $user_id = (empty($user_id)) ? $this->session->userdata('client_user_id') : $user_id;

        $this->db->select('*, cases_files.id as file_id, file_types.name as file_type_name', FALSE);
        $this->db->where('user_id', $user_id);
        $this->db->where('case_id', $case_id);
        $this->db->where_in('file_type_id', $files_types);
        $this->db->where('visibility', '1');
        $this->db->order_by('file_type_id');
        $this->db->join('file_types', 'file_types.id = cases_files.file_type_id');
        $query = $this->db->get('cases_files');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Updates case info
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @return    bool
     */
    public function update_case($case_id)
    {
        $this->load->model('estimates_model', 'estimates');
        $this->load->model('associates_model', 'associates');
        $this->load->model('emails_model', 'emails');
        $application_number = $this->input->post('application_number');
        $title = $this->input->post('title');
        $applicant = $this->input->post('applicant');
        $filing_deadline = $this->input->post('filing_deadline');
        $parent_case = $this->input->post('parent_case');
        $is_intake = trim($this->input->post("is_intake"));

        switch ($is_intake)
        {
            case "1":
                $is_intake = "1";
                $common_status = "hidden";
                $estimate_available_for_client = "0";
                break;

            default:
                $is_intake = "0";
                $common_status = "hidden";
                $estimate_available_for_client = "0";
                break;
        }
        if (!empty($filing_deadline)) {
            $date = new DateTime($filing_deadline);
            $filing_deadline = $date->format('Y-m-d');
        } else
        {
            $filing_deadline = NULL;
        }
        $reference_number = $this->input->post('reference_number');
        $list_priorities_number = $this->input->post('list_priorities_number');
        $cc = $this->input->post('cc');
        $countries = $this->input->post('countries');

        if (check_array($countries)) {
            $countries_array = array();
            foreach ($countries as $country_id)
            {
                if (strpos($country_id, ',') !== FALSE) {
                    $temp_array = explode(',', $country_id);
                    if (check_array($temp_array)) {
                        foreach ($temp_array as $item)
                        {
                            $countries_array[] = array(
                                'case_id' => $case_id,
                                'country_id' => $item,
                                'reference_number' => $_POST{'reference_number_for_country_' . $country_id}
                            );
                            $CASE_where[] = sprintf("id='%s'", $item);
                        }
                    }
                } else
                {
                    $countries_array[] = array(
                        'case_id' => $case_id,
                        'country_id' => $country_id,
                        'reference_number' => $_POST{'reference_number_for_country_' . $country_id}
                    );
                    $CASE_where[] = sprintf("id='%s'", $country_id);
                }
            }
            // Delete all related records firstly
            $this->db->where('case_id', $case_id);
            $this->db->delete('cases_countries');

            $this->db->flush_cache();

            $this->db->insert_batch('cases_countries', $countries_array);

            $now = new DateTime();
            if (isset($CASE_where) && !empty($CASE_where)) {
                $CASE_where = implode(" OR ", $CASE_where);
                $this->db->select_min("country_filing_deadline");
                $this->db->where($CASE_where, NULL, FALSE);
                $countriesDEADLINE = $this->db->get("countries")->row_array();
                if (!empty($countriesDEADLINE) && isset($countriesDEADLINE["country_filing_deadline"]) && !empty($countriesDEADLINE["country_filing_deadline"])) {
                    $now->modify("+" . $countriesDEADLINE['country_filing_deadline'] . " month");
                    $case_filing_deadline = $now->format('Y-m-d');
                }
                else
                {
                    //                    $date=new DateTime($now);
                    $year = $this->estimates->addYears($now, '2');
                    $date = new DateTime($year);
                    $case_filing_deadline = $this->estimates->addMonths($date, '6');
                }
            }
            else {
                //                $date=new DateTime($now);
                $year = $this->estimates->addYears($now, '2');
                $date = new DateTime($year);
                $case_filing_deadline = $this->estimates->addMonths($date, '6');
            }
            /* END STAN */
        }
        $parent_case = $this->get_case($this->input->post('parent_case'));
        if ($parent_case['case_type_id'] == 2 || $parent_case['case_type_id'] == 3) {
            $case_filing_deadline = date('Y-m-d', strtotime($parent_case['filing_deadline']));
        }
        $case_data = array(
            'reference_number' => $reference_number,
            'additional' => $this->input->post('special_instructions'),
            'is_intake' => $is_intake,
            'common_status' => $common_status,
            'estimate_available_for_client' => $estimate_available_for_client,
            "filing_deadline" => $case_filing_deadline
        );

        $this->db->where('id', $case_id);
        $this->db->update('cases', $case_data);

        if ($is_intake == '1') {
            $this->db->select('case_type_id');
            $this->db->where('id', $case_id);
            $query = $this->db->get('cases');
            $case_tmp = $query->row_array();
            $case_type_id = $case_tmp['case_type_id'];
            $this->associates->insert_new_associates_to_case_associates_data($case_id, $countries, $case_type_id);
            $this->add_country_to_tracker($case_id,$countries);
        }

        if ($this->db->affected_rows() > 0) {
            if (!empty($parent_case)) {

                $this->db->select("case_number");
                $INSERTED_CASE = $this->db->get_where("cases", array("id" => $case_id))->row_array();
                $this->emails->create_email_account_for_case($INSERTED_CASE["case_number"]);
                $this->sendConfEmailToClient($INSERTED_CASE["case_number"], '21');
            }
            return TRUE;
        }
        return FALSE;
    }

    public function add_country_to_tracker($case_id,$countries){
        foreach ($countries as $country_tracker){
            $new_countries[] = array(
                'case_id' => $case_id,
                'country_id' => $country_tracker
            );
        }
        $this->db->insert_batch('cases_tracker', $new_countries);
    }

    public function set_file_type($file_id, $file_type_id)
    {
        $this->db->where('id', $file_id);
        $this->db->update('cases_files', array('file_type_id' => $file_type_id));

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Assigns file to case
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    user ID
     * @param    int    case ID
     * @param    string    filename
     * @param    string    filepath
     * @param    string    unique string or hash
     * @return    mixed
     */
    public function assign_file_to_case($user_id, $case_id, $filename, $location, $unique_id = '')
    {
        $this->db->select('id');
        $this->db->where('case_number', $case_id);
        $query = $this->db->get('cases');
        $case = $query->result();


        $data = array(
            'user_id' => $user_id,
            'case_id' => $case[0]->id,
            'filename' => $filename,
            'filesize' => filesize($location),
            'location' => $location,
            'owner' => 'customer',
            'visibility' => '1',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $data['location'] = str_replace('../pm/', '', $location);
        $this->db->insert('cases_files', $data);

        return ($this->db->affected_rows() > 0) ? $this->db->insert_id() : FALSE;
    }

    /**
     * Returns a user entry by case number
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    mixed
     */
    public function get_customer_by_case_number($case_number = '')
    {
        $q = 'SELECT c.*
			  FROM zen_customers c, zen_cases cs
			  WHERE (c.id = cs.user_id) AND
			        (cs.case_number = "' . $case_number . '")';
        $query = $this->db->query($q);
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Returns a case entry
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    mixed
     */
    public function find_case_by_number($case_number = '')
    {
        $user_id = $this->session->userdata('client_user_id');
        $firm = $this->check_firm($user_id);
        $this->db->select('cases.*, DATE_FORMAT(zen_cases.filing_deadline, "%m/%d/%y") as filing_deadline, DATE_FORMAT(zen_cases.first_priority_date, "%m/%d/%y") as first_priority_date, DATE_FORMAT(filing_deadline, "%m/%d/%Y") as case_filing_deadline, case_types.type as case_type', FALSE);
        $this->db->where('case_number', $case_number);
        $this->db->where('user_id', $user_id);
        if ($firm) {
            $this->db->or_where('cases.case_number = "' . $case_number . '" AND zen_cases.user_id IN (' . $firm . ')', NULL, FALSE);
        }
        $this->db->join('case_types', 'case_types.id = cases.case_type_id');
        $query = $this->db->get('cases');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    public function fa_case_fees($case_number = '')
    {
        $this->db->select('cases.*, DATE_FORMAT(zen_cases.filing_deadline, "%m/%d/%y") as filing_deadline, DATE_FORMAT(zen_cases.first_priority_date, "%m/%d/%y") as first_priority_date, DATE_FORMAT(filing_deadline, "%m/%d/%Y") as case_filing_deadline, case_types.type as case_type', FALSE);
        $this->db->where('case_number', $case_number);
        $this->db->join('case_types', 'case_types.id = cases.case_type_id');
        $query = $this->db->get('cases');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Returns a list of countries assigned to case
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    mixed
     */
    public function get_case_countries($case_number = '')
    {
        $this->db->select('zen_countries.*', FALSE);
        $this->db->join('cases_countries', 'cases_countries.country_id = countries.id');
        $this->db->join('cases', 'cases.id = cases_countries.case_id');
        $this->db->where('cases.case_number', $case_number);
        $query = $this->db->get('countries');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }
    
    /**
     * 
     * Returns a country assigned to case
     * 
     * @author Semyon Babushkin
     * @param $case_number
     * @param $country_id
     * @return mixed
     */
    
	public function get_case_country($case_id,$country_id)
    {
        $this->db->select('*', FALSE);
        $this->db->join('cases_countries', 'cases_countries.country_id = countries.id');
        $this->db->where('cases_countries.case_id', $case_id);
        $this->db->where('cases_countries.country_id', $country_id);
        $query = $this->db->get('countries');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    public function getFileTypes($type)
    {
        $this->db->select('id,name');
        if ($type == 'client_upload_type') {
            $this->db->where($type, '1');
        }
        if ($type == 'client_upload_active_case') {
            $this->db->where($type, '1');
        }
        $query = $this->db->get('file_types');

        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Approves countries in estimate
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int case number
     * @return    void
     */
    public function approve_estimate_countries($case_number)
    {

        $this->load->model('estimates_model', 'estimates');
        $reestimate = $this->input->post('reestimate');
        if (!is_null($case = $this->find_case_by_number($case_number))) {
            $this->db->where('case_id', $case['id']);
            $data = array('is_approved' => '0', 'is_disabled_by_client' => '1');
            $this->db->update('estimates_countries_fees', $data);
            $approved_countries = $this->input->post('approved_countries');
            $approved_countries = array_unique($approved_countries);
            if (check_array($approved_countries)) {

                $countries_ids = array();
                foreach ($approved_countries as $country)
                {
                    $countries_ids[] = $country;
                }
                if (count($countries_ids) > 0) {

                    $this->db->where_in('country_id', $countries_ids);
                    $this->db->where('case_id', $case['id']);
                    $data = array('is_disabled_by_client' => '0');

                    if ($this->input->post('by_user') == '1') {
                        $data = array('is_approved' => '1', 'is_disabled_by_client' => '0');
                    }

                    $this->db->update('estimates_countries_fees', $data);
                    //add aproved countries to the tracker
                    if ($this->input->post('by_user') == '1') {
                        foreach ($countries_ids as $country_tracker)
                            $new_countries[] = array(
                                'case_id' => $case['id'],
                                'country_id' => $country_tracker
                            );
                        $this->db->insert_batch('cases_tracker', $new_countries);
                    }
                    // If we have added countries
                    if (!is_null($case_countries = $this->get_case_countries($case['case_number']))) {
                        $case_countries_arr = array();
                        foreach ($case_countries as $case_country)
                        {
                            $case_countries_arr[] = $case_country['id'];
                        }
                        $arr_diff = array_diff($countries_ids, $case_countries_arr);

                        if (check_array($arr_diff)) {
                            $new_estimate_entries = array();
                            $new_countries = array();
                            foreach ($arr_diff as $arr_item)
                            {
                                $new_countries[] = array(
                                    'case_id' => $case['id'],
                                    'country_id' => $arr_item
                                );

                                $new_estimate_entries[] = array(
                                    'user_id' => $case['user_id'],
                                    'case_id' => $case['id'],
                                    'country_id' => $arr_item,
                                    'added_by_client' => '1',
                                );
                            }
                            if (check_array($new_countries)) {
                                $this->db->insert_batch('cases_countries', $new_countries);
                            }

                            if (check_array($new_estimate_entries)) {
                                $this->estimates->add_fees_entries($case['user_id'], $case_number, $case['fee_level'], true);
                            }
                        }
                    }
                }

                if (!empty($reestimate)) {
                    $new_status = 'estimating-reestimate';
                } else
                {
                    $new_status = 'pending-intake';
                }

                $data = array(
                    'approved_at' => date('Y-m-d H:i:s'),
                    'estimate_available_for_client' => '0',
                    'highlight' => '1',
                    'common_status' => "$new_status"
                );
                $this->db->where('id', $case['id']);
                $this->db->update('cases', $data);
                // Remove unapproved countries if client approves the case
                if (empty($_POST['by_user']) && empty($reestimate)) {

                    $this->db->where('user_id', $case['user_id']);
                    $this->db->where('case_id', $case['id']);
                    $this->db->where('is_approved', '0');
                    $query = $this->db->get('estimates_countries_fees');
                    $to_delete = array();
                    if ($query->num_rows()) {
                        foreach ($query->result_array() as $res_item)
                        {
                            $to_delete[] = $res_item['country_id'];
                        }
                    }

                    $this->db->where('user_id', $case['user_id']);
                    $this->db->where('case_id', $case['id']);
                    $this->db->where('is_approved', '0');
                    $this->db->delete('estimates_countries_fees');

                    if (count($to_delete) > 0) {
                        $this->db->where('case_id', $case['id']);
                        $this->db->where_in('country_id', $to_delete);
                        $this->db->delete('cases_countries');
                    }
                }
            }

            $unapproved_countries_id = $this->get_unapproved_countries_ids($case['id'], $countries_ids);
            if ($unapproved_countries_id) {
                $this->disable_countries_by_client($unapproved_countries_id);
            }

        }
    }

    function disable_countries_by_client($ids_to_disable)
    {
        $options['is_disabled_by_client'] = '1';
        $options['is_approved'] = '0';
        $this->db->where_in('id', $ids_to_disable);
        $this->db->update('zen_estimates_countries_fees', $options);
    }

    // function for getting countries what needs to be disabled after the reestimate
    function get_unapproved_countries_ids($case_id, $approved_countries)
    {

        $this->db->select('id');
        $this->db->where_not_in('country_id', $approved_countries);
        $this->db->where('case_id', $case_id);
        $query = $this->db->get('zen_estimates_countries_fees');
        $ids = array();
        foreach ($query->result() as $key => $value) {
            $ids[] = $value->id;
        }
        return $ids;
    }

    /**
     * Finds the last estimate PDF
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int case ID
     * @return    mixed
     */
    public function find_pdf_estimate($case_id)
    {
        $user_id = $this->session->userdata('client_user_id');
        $this->db->limit(1);
        $this->db->order_by('created_at', 'desc');
        $this->db->where('case_id', $case_id);
        $this->db->where('user_id', $user_id);
        $this->db->where('file_type_id', '15');
        $query = $this->db->get('cases_files');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    function is_estimate_available_for_client($case_number)
    {

        $this->db->where('case_number', $case_number);
        $this->db->where('estimate_available_for_client', '1');
        $query = $this->db->get('cases');

        if ($query->num_rows()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Inserts a case note
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int case number
     * @param    string case number
     * @return    mixed
     */
    public function add_case_note($case_number = '', $note = '', $user_id = 0, $client_user_id = 0)
    {
        if (!empty($note)) {
            /* user id means manager id */
            $data = array(
                'case_number' => $case_number,
                'note' => $note,
                'user_id' => $user_id,
                'client_user_id' => $client_user_id,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->db->insert('cases_notes', $data);
            return ($this->db->affected_rows() > 0) ? $this->db->insert_id() : FALSE;
        }
    }

    /**
     * Return a list of approved countries from estimate
     *
     * @access     public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int case ID
     * @return    mixed
     */
    public function get_approved_countries($case_id = '')
    {
        $this->db->where('is_approved', '1');
        $this->db->where('case_id', $case_id);
        $query = $this->db->get('estimates_countries_fees');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Creates a new related case
     *
     * @access    public
     * @param    int    case ID
     * @return     bool
     * */
    public function create_related_case($parent_case_number = '')
    {
        $result = array(
            'case_number' => '',
            'case_type' => '',
        );

        $this->db->where('case_number', $parent_case_number);
        $query = $this->db->get('cases');
        if ($query->num_rows()) {
            // Case data
            $case_data = $query->row_array();
            $case_data['last_update'] = NULL;
            $case_data['created_at'] = date('Y-m-d H:i:s');
            $case_data['related_hidden'] = '1';
            $case_data['common_status'] = 'draft';
            $case_id = $case_data['id'];
            unset($case_data['id']); // Remove primary key value
            $this->db->insert('cases', $case_data);
            $new_case_id = $this->db->insert_id();
            $this->db->select('email');
            $this->db->where('case_id', $case_id);
            $query = $this->db->get('case_contacts');
            if ($query->num_rows()) {
                $tmpcontacts = $query->result_array();
                foreach ($tmpcontacts as $contact) {
                    $insert[] = array(
                        'case_id' => $new_case_id,
                        'email' => $contact['email']
                    );
                }
                $this->db->insert_batch('case_contacts', $insert);
            }

            $case_number = $this->generate_case_number_for_related_case($case_data['case_number'], $case_id);
            $this->db->where('id', $new_case_id);
            $this->db->update('cases', array('case_number' => $case_number));
            $data = array(
                'child_case_id' => $new_case_id,
                'parent_case_id' => $case_id,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->db->insert('related_cases', $data);
            // Case type
            $case_type = 'case';
            $estimates_statuses = array('estimating', 'pending-approval');
            if (in_array($case_data['common_status'], $estimates_statuses)) {
                $case_type = 'estimate';
            }

            $result['case_number'] = $case_number;
            $result['case_type'] = $case_type;
            $result['case_id'] = $new_case_id;
            $new_attachments = $this->get_parent_case_files($case_id, $file_types = array(1, 2, 3, 4, 7, 8, 9, 10, 11, 12, 14, 13, 15, 16, 17, 18));
            $user_id = $this->session->userdata('client_user_id');

            if (check_array($new_attachments)) {

                $user_path = $this->config->item('path_upload') . 'pm/uploads/' . $user_id . '/';
                if ($_SERVER['HTTP_HOST'] == 'zenfile.local')
                    $user_path = $this->config->item('http_path_upload') . 'pm/uploads/' . $user_id . '/';
                $case_path = $user_path . $case_number . '/';

                if (!is_dir($user_path)) {
                    mkdir($user_path, 0775);
                }
                if (!is_dir($case_path)) {
                    mkdir($case_path);
                }
                $case_files = array();
                foreach ($new_attachments as $attachments)
                {
                    $file_path = $case_path . $attachments['filename'];
                    @$copy = copy($this->config->item('path_upload') . 'pm/' . $attachments['location'], $file_path);
                    if ($copy) {
                        if ($_SERVER['HTTP_HOST'] == 'zenfile.local') {
                            $finfo = finfo_open(FILEINFO_MIME, '/usr/share/misc/magic.mgc');
                        } elseif ($_SERVER['HTTP_HOST'] == 'zen' || $_SERVER['HTTP_HOST'] == 'lastzenfile') {
                            $finfo = finfo_open(FILEINFO_MIME);
                        } else {
                            $finfo = finfo_open(FILEINFO_MIME, '/usr/share/misc/magic');
                        }
                        finfo_close($finfo);

                        $case_files[] = array(
                            'case_id' => $new_case_id,
                            'user_id' => $user_id,
                            'filename' => $attachments['filename'],
                            'location' => str_replace($this->config->item('path_upload') . 'pm/', '', $file_path),
                            'mime_type' => $attachments['mime_type'],
                            'filesize' => $attachments['filesize'],
                            'created_at' => date('Y-m-d H:i:s'),
                            'owner' => 'customer',
                            'visibility' => $attachments['visibility'],
                            'file_type_id' => $attachments['file_type_id'],
                        );
                    }
                }
                if (check_array($case_files)) {
                    $this->db->insert_batch('cases_files', $case_files);
                }
            }
        }

        return $result;
    }

    public function get_direct_related_cases($case_id = '')
    {
        $q = 'SELECT DISTINCT c.id, c.case_number
		      FROM `zen_cases` c, `zen_related_cases` rc
			  WHERE (c.id = rc.child_case_id) AND
			  		(rc.parent_case_id = ' . (int)$case_id . ') AND c.common_status  NOT IN("hidden","draft")';

        $query = $this->db->query($q);
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    public function getParentCaseId($case_id = '')
    {
        $result = null;
        $query = $this->db
            ->select('parent_case_id, created_at')
            ->from('related_cases')
            ->where('child_case_id', $case_id)
            ->get();
        $result = $query->result();

        return $result;
    }

    public function fa_active_cases($fa_id, $params='')
    {
        $common_statuses = array('hidden', 'draft', 'completed');
        $this->db->select('cases.*,countries.country,countries.country_filing_deadline, cases_associates_data.fa_reference_number');
        $this->db->join('cases', 'cases_associates_data.case_id = cases.id');
        $this->db->join('countries', 'cases_associates_data.country_id = countries.id');
        $this->db->join('cases_tracker', 'zen_cases_tracker.country_id = zen_cases_associates_data.country_id AND zen_cases_associates_data.case_id = zen_cases_tracker.case_id');
        $this->db->where('cases_associates_data.associate_id', $fa_id);
        $this->db->where('cases_tracker.fi_requests_sent_fa >', '0000-00-00 00:00:00');
        $this->db->where('cases_associates_data.is_active','1');
        $this->db->where('cases.is_active','1');
        $this->db->where_not_in('cases.common_status',$common_statuses);
        if($params){
            $this->db->where("(zen_cases.application_number LIKE '%{$params}%' OR zen_cases.application_number LIKE '%{$params}%' OR zen_cases.reference_number LIKE '%{$params}%' OR zen_cases.case_number LIKE '%{$params}%' OR zen_cases.applicant LIKE '%{$params}%')");
        }
        $query = $this->db->get('cases_associates_data');
        return $query->result_array();

    }

    public function fa_completed_cases($fa_id, $params='')
    {
        $this->db->select('cases.*,countries.country,cases_associates_data.fa_reference_number');
        $this->db->join('cases', 'cases_associates_data.case_id = cases.id');
        $this->db->join('countries', 'cases_associates_data.country_id = countries.id');
        $this->db->join('cases_tracker', 'zen_cases_tracker.country_id = zen_cases_associates_data.country_id AND zen_cases_associates_data.case_id = zen_cases_tracker.case_id');
        $this->db->where('cases_associates_data.associate_id', $fa_id);
        $this->db->where('cases_tracker.fi_requests_sent_fa >', '0000-00-00 00:00:00');
        $this->db->where('cases_associates_data.is_active','1');
        $this->db->where('cases.is_active','1');
        $this->db->where('cases.common_status', 'completed');
        if($params){
            $this->db->where("(zen_cases.application_number LIKE '%{$params}%' OR zen_cases.application_number LIKE '%{$params}%' OR zen_cases.reference_number LIKE '%{$params}%' OR zen_cases.case_number LIKE '%{$params}%' OR zen_cases.applicant LIKE '%{$params}%')");
        }
        $query = $this->db->get('cases_associates_data');
        return $query->result_array();

    }

    public function fa_case_countries($case_id,$fa_id){
        $this->db->join('cases_associates_data','cases_associates_data.country_id = countries.id');
        $this->db->join('cases_countries',' zen_cases_countries.case_id = cases_associates_data.case_id AND zen_cases_countries.country_id = zen_cases_associates_data.country_id');
        $this->db->join('cases_tracker', 'zen_cases_tracker.country_id = zen_cases_associates_data.country_id AND zen_cases_associates_data.case_id = zen_cases_tracker.case_id');
        $this->db->where('cases_tracker.fi_requests_sent_fa >', '0000-00-00 00:00:00');
        $this->db->where('cases_associates_data.case_id', $case_id);
        $this->db->where('cases_associates_data.associate_id', $fa_id);
        $this->db->where('cases_associates_data.is_active','1');
        $query = $this->db->get('countries');
        return $query->result_array();
    }
}

?>