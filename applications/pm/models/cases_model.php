<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cases_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function get_file_types()
    {
        return array(
            1 => array(1, 4, 7, 8, 9, 11, 12, 15, 17, 18, 19, 21, 22), //case files
            2 => array(2, 10, 14), //documents
            3 => array(13, 16), //signed documents
            4 => array(6, 17), //countries
            5 => array(1, 4, 11, 14, 10, 12, 6, 7, 2), //need to assign
            6 => array(1, 2, 4, 14, 6, 10, 11, 12, 13, 9, 16) //need to visible
        );
    }

    public function remove_country_from_case($country_id, $case_number)
    {
        if (!is_null($case = $this->find_case_by_number($case_number))) {
            // 1) Table  "zen_case_countries"

            $this->db->where('country_id', $country_id);
            $this->db->where('case_id', $case['id']);
            $this->db->delete('cases_countries');
            $this->db->flush_cache();
            if (!is_null($files = $this->get_case_files($case['id']))) {
                $files_ids = array();
                foreach ($files as $file) {
                    $files_ids[] = $file['id'];
                }

                $this->db->where('country_id', $country_id);
                $this->db->where_in('file_id', $files_ids);
                $this->db->delete('files_countries');
            }
        }
    }

    /**
     * Returns case entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    int    user ID
     * @param    bool    don't check user_id in SQL query
     * @return    mixed
     * */
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
     * Generates case number for new case (OLD FUNCTION!!!)
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    int
     * */
    public function generate_case_number()
    {
        $this->db->select_max('case_number');
        $query = $this->db->get('cases');
        $case = $query->row_array();

        return ($case['case_number'] + 13);
    }

    /**
     * Returns a list of case types
     *
     * @access    public
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

    public function get_case_files($case_id = '', $types_array = '', $date_created = '')
    {
        $this->db->select('cases_files.*, file_types.name,GROUP_CONCAT(zen_files_countries.country_id) as countries');
        $this->db->join('file_types', 'file_types.id = cases_files.file_type_id', 'left');
        $this->db->join('files_countries', 'cases_files.id = files_countries.file_id', 'LEFT OUTER');
        if ((!empty($types_array)) && (is_array($types_array))) {
            $this->db->where_in('file_type_id', $types_array);
        }
        if ($date_created) {
            $this->db->where('Unix_Timestamp(created_at) <', $date_created);
        }
        $this->db->where('case_id', $case_id);
        $this->db->order_by('created_at', 'asc');
        $this->db->group_by('cases_files.id');
        $query = $this->db->get('cases_files');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    public function get_case_files_with_country_array($case_id = '', $file_types = array(), $country)
    {
        $this->db->select('cases_files.*, file_types.name');
        $this->db->join('file_types', 'file_types.id = cases_files.file_type_id', 'left');
        $this->db->join('files_countries', 'files_countries.file_id = cases_files.id');
        $this->db->where('files_countries.country_id', $country);
        $this->db->where('cases_files.case_id', $case_id);
        $this->db->where_in('cases_files.file_type_id', $file_types);
        $query = $this->db->get('cases_files');
        return $query->result_array();
    }

    /**
     * Returns a list of case files
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    int    country ID
     * @param    array    file types
     * @return    mixed
     * */
    public function get_case_files_by_country($case_id = '-1', $country_id = '-1', $file_types = array(1, 2, 3, 4, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17))
    {
        $country_where = '';
        if ((!empty($country_id)) && (is_numeric($country_id))) {
            $country_where = '(fc.country_id = ' . $country_id . ') AND';
        }

        if (empty($country_where)) {
            $q = 'SELECT *
				  FROM `zen_cases_files` cf
				  WHERE (cf.case_id = ' . $case_id . ') AND
						(cf.file_type_id IN (' . implode(',', $file_types) . '))';
        } else {
            $q = 'SELECT *
				  FROM `zen_cases_files` cf, `zen_files_countries` fc
				  WHERE (cf.case_id = ' . $case_id . ') AND
				  		(fc.file_id = cf.id) AND
						' . $country_where . '
						(cf.file_type_id IN (' . implode(',', $file_types) . '))';
        }

        $query = $this->db->query($q);
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns a list of files and countries for selected case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @return    mixed
     */
    public function get_files_countries($case_id = '') {
        $q = 'SELECT cf.*, fc.file_id, c.country, c.code
			  FROM zen_files_countries fc, zen_cases_files cf, zen_countries c
			  WHERE (cf.case_id = ' . $case_id . ') AND
			  		(fc.file_id = cf.id) AND
					(fc.country_id = c.id)';
        $query = $this->db->query($q);
        if ($query->num_rows()) {
            return $result = $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns a list of files by their ID values
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    array    array of ID values
     * @return    mixed
     * */
    public function get_files_by_id_array($ids = array())
    {
        if (count($ids) > 0) {
            $this->db->where_in('id', $ids);
            $query = $this->db->get('cases_files');
            if ($query->num_rows()) {
                return $query->result_array();
            }
        }
        return NULL;
    }

    function delete_fees_entries_for_case($case_id)
    {
        if (empty($case_id)) {
            return false;
        }

        $this->db->where('case_id', $case_id);
        $this->db->delete('estimates_countries_fees');
        return $this->db->affected_rows();
    }

    /**
     * Updates case entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    mixed
     * */
    public function update_case($case_number = '') {

        $ci = & get_instance();
        $ci->load->model('wipo_model', 'wipo');
        $this->load->model('estimates_model', 'estimates');
        $case_number = $this->input->post('case_number');
        $case = $this->find_case_by_number($case_number);

        if ($case['case_type_id'] != $this->input->post('case_type_id')) {
            $this->delete_fees_entries_for_case($case['id']);
        }

        $parkip_case_number = $this->input->post('parkip_case_number');
        // Default values
        $email_notification = ($this->input->post("email_notification") == ("0" || "1")) ? $this->input->post("email_notification") : "0";
        $list_priorities_number = $this->input->post('list_priorities_number');
        $title = $this->input->post('title');
        $applicant = $this->input->post('applicant');
        $first_priority_date = $this->input->post('first_priority_date');
        if (!empty($first_priority_date)) {
            if (is_valid_date_time($first_priority_date)) {
                $date = new DateTime($first_priority_date);
                $first_priority_date = $date->format('Y-m-d');
            }
        }

        $client_filing_deadline = $this->input->post('client_filing_deadline');
        $filing_deadline = $this->input->post('filing_deadline');
        if (!empty($filing_deadline)) {
            if (is_valid_date_time($filing_deadline)) {
                $date = new DateTime($filing_deadline);
                $filing_deadline = $date->format('Y-m-d');
            }
        }
        if (!empty($filing_deadline)) {
            if (is_valid_date_time($filing_deadline)) {
                $date = new DateTime($filing_deadline);
                $filing_deadline = $date->format('Y-m-d');
            }
        }

        $_12_month_filing_deadline = $this->input->post('12_month_filing_deadline');
        if (!empty($_12_month_filing_deadline)) {
            if (is_valid_date_time($_12_month_filing_deadline)) {
                $date = new DateTime($_12_month_filing_deadline);
                $_12_month_filing_deadline = $date->format('Y-m-d');
            }
        }
        $international_filing_date = $this->input->post('international_filing_date');
        if (!empty($international_filing_date)) {
            if (is_valid_date_time($international_filing_date)) {
                $date = new DateTime($international_filing_date);
                $international_filing_date = $date->format('Y-m-d');
            }
        }
        $publication_date = $this->input->post('publication_date');
        if (!empty($publication_date)) {
            if (is_valid_date_time($publication_date)) {
                $date = new DateTime($publication_date);
                $publication_date = $date->format('Y-m-d');
            }
        }

        $countries_of_case = $this->get_case_countries($case_number);
        foreach ($countries_of_case as $country) {
            $this->db->where('id', $country['primary_id']);
            $this->db->set('reference_number', $_POST['reference_number_for_country_' . $country['primary_id']]);
            $this->db->update('cases_countries');
        }

        $reference_number = $this->input->post('reference_number');
        $application_title = $this->input->post('title');
        $number_priorities_claimed = $this->input->post('number_priorities_claimed');
        $number_claims = $this->input->post('number_claims');
        $number_pages_drawings = $this->input->post('number_pages_drawings');
        $number_pages_claims = $this->input->post('number_pages_claims');
        $number_pages = $this->input->post('number_pages');
        $number_pages_sequence = $this->input->post('number_pages_sequence');
        $number_words = $this->input->post('number_words');
        $number_words_in_claims = $this->input->post('number_words_in_claims');
        $search_location = $this->input->post('search_location');
        $sequence_listing = $this->input->post('sequence_listing');
        $number_reduced_claims = $this->input->post('number_reduced_claims');
        $publication_language = $this->input->post('publication_language');
        if ($this->input->post('30_month_filing_deadline') && trim($this->input->post('30_month_filing_deadline')) != 'N/A') {
            $_30_month_filing_deadline = date('Y-m-d', strtotime($this->input->post('30_month_filing_deadline')));
        } else {
            $_30_month_filing_deadline = NULL;
        }

        if ($this->input->post('31_month_filing_deadline') && trim($this->input->post('31_month_filing_deadline')) != 'N/A') {
            $_31_month_filing_deadline = date('Y-m-d', strtotime($this->input->post('31_month_filing_deadline')));
        } else {
            $_31_month_filing_deadline = NULL;
        }
        $application_number = $this->input->post('application_number');
        $wo_number = $this->input->post('wo_number');
        $wo_number = $this->prepare_number_for_parser($wo_number);

        if (!$wo_number) {
            $wo_number = '';
        }
        // Wanna fuck WIPO?!!
        $parse_wipo = $this->input->post('parse_wipo');
        $get_wipo = $this->input->post('get_wipo');
        if (!empty($get_wipo)) {
            $result = $this->wipo->get_wipo_data($wo_number, $application_number);
            $this->db->where('case_number', $case_number);

            $case_data = array(
               // 'title' => $result['application_title'],
                'application_title' => $result['application_title'],
                'number_priorities_claimed' => $result['number_priorities_claimed'],
                'number_pages_claims' => $result['number_pages_claims'],
                'number_pages' => $result['number_pages'],
                'first_priority_date' => $result['first_priority_date'],
                'search_location' => $result['search_location'],
                'applicant' => $result['applicant'],
                'publication_language' => $result['publication_language'],
                '30_month_filing_deadline' => $result['30_month_filing_deadline'],
                '31_month_filing_deadline' => $result['31_month_filing_deadline'],
                'number_claims' => $result['number_claims'],
                'number_words' => $result['number_words'],
                'number_words_in_claims' => $result['number_words_claims'],
                'sequence_listing' => $result['sequence_listing'],
                'wipo_wo_number' => $result['wo_number'],
                'wipo_pct_number' => $result['pct_number'],
                'application_number' => $result['pct_number'],
                'number_pages_drawings' => $result['number_pages_drawings'],
                'filing_deadline' => $result['30_month_filing_deadline'],
                'international_filing_date' => $result['international_filing_date'],
                'last_update' => date('Y-m-d H:i:s'),
                'publication_date' => $result['publication_date']
            );
            $this->db->update('cases', $case_data);
            return;
        }
        if (!empty($parse_wipo)) {

            $result = $this->parse_wipo_new(urlencode($case_number));
            if (is_array($result)) {
                $this->db->reconnect();
                $ci->wipo->append_wipo_data($result);
                $this->db->where('case_number', $case_number);

                $case_data = array(
                   // 'title' => $result['application_title'],
                    'application_title' => $result['application_title'],
                    'number_priorities_claimed' => $result['number_priorities_claimed'],
                    'number_pages_drawings' => $result['number_pages_drawings'],
                    'number_pages_claims' => $result['number_pages_claims'],
                    'number_pages' => $result['number_pages'],
                    'first_priority_date' => $result['first_priority_date'],
                    'filing_deadline' => $result['filing_deadline'],
                    'search_location' => $result['search_location'],
                    'applicant' => $result['applicant'],
                    'publication_language' => $result['publication_language'],
                    'number_claims' => $result['number_claims'],
                    'number_words' => $result['number_words'],
                    'number_words_in_claims' => $result['number_words_claims'],
                    'sequence_listing' => $result['sequence_listing'],
                    'wipo_wo_number' => $result['wo_number'],
                    'wipo_pct_number' => $result['pct_number'],
                    'application_number' => $result['pct_number'],
                    'international_filing_date' => $result['international_filing_date'],
                    '30_month_filing_deadline' => $result['30_month_filing_deadline'],
                    '31_month_filing_deadline' => $result['31_month_filing_deadline'],
                    'last_update' => date('Y-m-d H:i:s'),
                );
                $this->db->update('cases', $case_data);
                return;
            }
        }


        $case_type_id = $this->input->post('case_type_id');
        if ($case_type_id == '1') {
            $filing_deadline = $_30_month_filing_deadline;
        }
        $sales_manager_id = $this->input->post('sales_manager_id');
        $case_data = array(
            'case_number' => $case_number,
            'parkip_case_number' => $parkip_case_number,
            'application_title' => $application_title,
            'application_number' => $application_number,
            'list_priorities_number' => $list_priorities_number,
           // 'title' => $title,
            'applicant' => $applicant,
            'publication_date' => $publication_date,
            'first_priority_date' => $first_priority_date,
            'filing_deadline' => $filing_deadline,
            'international_filing_date' => $international_filing_date,
            'reference_number' => $reference_number,
            'number_reduced_claims' => $number_reduced_claims,
            'number_priorities_claimed' => $number_priorities_claimed,
            'number_claims' => $number_claims,
            'number_pages_drawings' => $number_pages_drawings,
            'number_pages_claims' => $number_pages_claims,
            'number_pages' => $number_pages,
            'number_pages_sequence' => $number_pages_sequence,
            'number_words' => $number_words,
            'number_words_in_claims' => $number_words_in_claims,
            'sales_manager_id' => $sales_manager_id,
            'search_location' => $search_location,
            'sequence_listing' => $sequence_listing,
            'case_type_id' => $case_type_id,
            'publication_language' => $publication_language,
            '30_month_filing_deadline' => $_30_month_filing_deadline,
            '31_month_filing_deadline' => $_31_month_filing_deadline,
            '12_month_filing_deadline' => $_12_month_filing_deadline,
            'wipo_wo_number' => $wo_number,
            'wipo_pct_number' => $application_number,
            'last_update' => date('Y-m-d H:i:s'),
            'email_notification' => $email_notification,
            'manager_id' => $this->input->post('manager_id')
        );
        $caseCC_raw = $this->input->post("cc");
        $this->update_case_contacts($case['id'], $caseCC_raw);
        // If filing deadline has been changed then "unhighlight" = 1 again!
        if ($case_type_id == '1') {
            $fd_30_orig = $this->input->post('30_month_filing_deadline_orig');
            $fd_31_orig = $this->input->post('31_month_filing_deadline_orig');
            $fd_30_new = $this->input->post('30_month_filing_deadline');
            $fd_31_new = $this->input->post('31_month_filing_deadline');
            if (($fd_30_orig != $fd_30_new) || ($fd_31_orig != $fd_31_new)) {
                $case_data['highlight'] = '1';
            }
        } else {
            $fd_new = $this->input->post('filing_deadline');
            $fd_orig = $this->input->post('filing_deadline_orig');
            if ($fd_new != $fd_orig) {
                $case_data['highlight'] = '1';
            }
        }

        $this->db->where('case_number', $case_number);
        $this->db->update('cases', $case_data);
    }

    function insert_cc_to_case($options = array())
    {
        $this->db->insert('case_contacts', $options);
        return $this->db->insert_id();
    }

    public function update_case_contacts($case_id = '', $cc_string = '')
    {
        $this->db->where('case_id', $case_id);
        $this->db->delete('case_contacts');

        $new_data = array();
        if (!empty($cc_string)) {
            $check_comma = substr($cc_string, -1);
            if ($check_comma == ',' || $check_comma == ';') {
                $cc_string = substr($cc_string, 0, -1);
            }
        }
        if (strpos($cc_string, ';') === FALSE) {
            $cc_array = explode(',', $cc_string);
        } else {
            $cc_array = explode(';', $cc_string);
        }

        foreach ($cc_array as $cc_item) {
            $new_data[] = array(
                'case_id' => $case_id,
                'email' => trim($cc_item)
            );
        }

        if (count($new_data) > 0) {
            $this->db->insert_batch('case_contacts', $new_data);
        }
    }

    /**
     * Returns a list of cases by type
     *
     * @access    public
     * @author    Sergey Koshkarev
     * @param    string    type
     * @return    mixed
     * */
    public function find_cases_by_type($type = '')
    {
        if ($type == 'active') {
            $q = 'SELECT c.*
				  FROM zen_cases c
				  WHERE ((is_intake = 1) OR (is_intake = "")) AND
				  		((manager_id = "") OR (manager_id IS NULL))';
            $query = $this->db->query($q);
            if ($query->num_rows()) {
                return $query->result_array();
            }
        } elseif ($type == 'own') {
            $manager_id = $this->session->userdata('user_id');
            $q = 'SELECT c.*
				  FROM zen_cases c
				  WHERE ((is_intake = 1) OR (is_intake = "")) AND
				  		(manager_id = ' . $manager_id . ')';
            $query = $this->db->query($q);
            if ($query->num_rows()) {
                return $query->result_array();
            }
        }
        return NULL;
    }

    /**
     * Returns a case entry by case number
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    mixed
     * */
    public function find_case_by_number($case_number = '')
    {
        $this->db->select('cases.*, estimate_fee_level, DATE_FORMAT(zen_cases.filing_deadline, "%m/%d/%y") as filing_deadline, DATE_FORMAT(zen_cases.30_month_filing_deadline, "%m/%d/%y") as 30_month_filing_deadline, DATE_FORMAT(zen_cases.12_month_filing_deadline, "%m/%d/%y") as 12_month_filing_deadline, DATE_FORMAT(zen_cases.31_month_filing_deadline, "%m/%d/%y") as 31_month_filing_deadline, DATE_FORMAT(zen_cases.first_priority_date, "%m/%d/%y") as first_priority_date, DATE_FORMAT(zen_cases.30_month_filing_deadline, "%m/%d/%y") as 30_month_filing_deadline, DATE_FORMAT(zen_cases.31_month_filing_deadline, "%m/%d/%y") as 31_month_filing_deadline, DATE_FORMAT(zen_cases.created_at, "%m/%d/%y %r") as created_at, DATE_FORMAT(zen_cases.last_update, "%m/%d/%y %r") as last_update, DATE_FORMAT(zen_cases.publication_date, "%m/%d/%y") as publication_date, DATE_FORMAT(zen_cases.international_filing_date, "%m/%d/%y") as international_filing_date, DATE_FORMAT(zen_cases.filing_deadline, "%m/%d/%y") as case_filing_deadline, case_types.type as case_type', FALSE);
        $this->db->where('case_number = "' . $case_number . '"', FALSE, FALSE);
        $this->db->join('case_types', 'case_types.id = cases.case_type_id');
        $query = $this->db->get('cases');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Returns a list of countries for specific case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    mixed
     * */
    public function get_case_countries($case_number = '', $only_with_fees = false)
    {
        $this->db->distinct();
        $this->db->select('cases_countries.id as primary_id , cases_countries.reference_number , zen_countries.*, cases_countries.extension_needed', FALSE);
        $this->db->join('cases_countries', 'cases_countries.country_id = countries.id');
        $this->db->join('cases', 'cases.id = cases_countries.case_id');
        if ($only_with_fees) {
            $this->db->join('fees', 'fees.country_id = cases_countries.country_id');
        }
        $this->db->where('cases.case_number', $case_number);
        $this->db->order_by('countries.country');
        $query = $this->db->get('countries');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns a list of countries assigned to file
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    file ID
     * @return    mixed
     * */
    public function get_file_countries($file_id)
    {
        $this->db->where('file_id', $file_id);
        $query = $this->db->get('files_countries');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Assigns file to case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    string    filename
     * @param    string    file location
     * @param    int    file type ID
     * @param    int    user ID
     * @return    int
     * */
    public function assign_file_to_case($case_id, $filename, $location, $file_type_id = 1, $user_id)
    {
        $this->db->reconnect();
        $data = array(
            'user_id' => $user_id,
            'case_id' => $case_id,
            'filename' => $filename,
            'filesize' => filesize($location),
            'location' => $location,
            'created_at' => date('Y-m-d H:i:s'),
            'file_type_id' => $file_type_id,
            'owner' => 'manager',
        );
        $this->db->insert('cases_files', $data);

        return $this->db->insert_id();
    }

    /**
     * Assigns a group of files to specific case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    array    files array (ID)
     * @param    int    file type ID
     * @param    int    case ID
     * @return    array
     * */
    public function assign_files_to_case($case_id, $files_array = array(), $file_type_id, $user_id)
    {
        $this->db->reconnect();
        $result = array();
        if (is_array($files_array) && count($files_array) > 0) {
            $time_offset = 0; // Time offset to make "created_at" field values unique (for table sorting)
            foreach ($files_array as $file) {
                $file_data = pathinfo($file);

                $data = array(
                    'user_id' => $user_id,
                    'case_id' => $case_id,
                    'filename' => $file_data['basename'],
                    'filesize' => filesize($file),
                    'location' => $file,
                    'file_type_id' => $file_type_id,
                    'created_at' => date('Y-m-d H:i:s', time() + $time_offset),
                    'owner' => 'manager',
                );

                $time_offset++;
                $this->db->insert('cases_files', $data);
                $result[$this->db->insert_id()] = $file_data['basename'];
            }
        }

        return $result;
    }

    /**
     * Assigns a file to country(ies)
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    file ID
     * @return    void
     * */
    public function assign_file_to_countries($file_id)
    {
        // Delete previous records
        $this->db->where('file_id', $file_id);
        $this->db->delete('files_countries');
        $this->db->flush_cache();

        $records = array();
        $countries = $this->input->post('countries');
        if (check_array($countries)) {
            foreach ($countries as $country) {
                $records[] = array(
                    'file_id' => $file_id,
                    'country_id' => $country
                );
            }
        }
        if (count($records) > 0) {
            $this->db->insert_batch('files_countries', $records);
        }
    }

    /**
     * Returns a file entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    file ID
     * @return    void
     * */
    public function get_file($file_id)
    {
        $this->db->select('cases.common_status, cases.id as case_id, cases.case_number, file_types.name as file_type, cases_files.*', FALSE);
        $this->db->where('cases_files.id', $file_id);
        $this->db->join('cases', 'cases.id = cases_files.case_id', 'left');
        $this->db->join('file_types', 'cases_files.file_type_id = file_types.id', 'left');
        $query = $this->db->get('cases_files');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Removes a file entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    file ID
     * @return    bool
     * */
    public function remove_file($file_id)
    {
        $this->db->where('id', $file_id);
        $this->db->delete('cases_files');
        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Assigns case to manager
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @param    int    manager ID
     * @return    void
     * */
    public function assign_case_to_manager($case_number, $manager_id)
    {
        $data = array(
            'manager_id' => $manager_id,
            'is_active' => '1'
        );

        $this->db->where('case_number', $case_number);
        $this->db->update('cases', $data);
    }

    /**
     * Returns customer entry by case number
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    mixed
     * */
    public function get_customer_by_case_number($case_number = '')
    {

        $q = 'SELECT c.*, DATE_FORMAT(c.last_login, "%m/%d/%Y %r") as last_login
			  FROM zen_customers c, zen_cases cs
			  WHERE (c.id = cs.user_id) AND
			        (cs.case_number = "' . $case_number . '")';
        $query = $this->db->query($q);
        if ($query->num_rows()) {
            $customer = $query->row_array();
            $case = $this->cases->find_case_by_number($case_number);
            $customer['firstname'] = $case['customer_firstname'];
            $customer['lastname'] = $case['customer_lastname'];
            $customer['company_name'] = $case['customer_company_name'];
            $customer['address'] = $case['customer_address'];
            $customer['address2'] = $case['customer_address2'];
            $customer['city'] = $case['customer_city'];
            $customer['state'] = $case['customer_state'];
            $customer['zip_code'] = $case['customer_zip_code'];
            $customer['country'] = $case['customer_country'];
            $customer['phone_number'] = $case['customer_phone_number'];
            $customer['ext'] = $case['customer_ext'];
            $customer['fax'] = $case['customer_fax'];
            return $customer;
        }
        return NULL;
    }

    public function get_replaced_associate_pdf($case_id)
    {
        $this->db->select('custom_associates.*, countries.country, cases_countries.extension_needed, custom_associates.associate_id as assoc_id ');
        $this->db->join('countries', 'custom_associates.country_id = countries.id');
        $this->db->join('cases_countries', 'countries.id = cases_countries.country_id AND zen_cases_countries.case_id = zen_custom_associates.case_id');
        $this->db->where('custom_associates.case_id', $case_id);
        $query = $this->db->get('custom_associates');
        return $query->result_array();
    }

    /**
     * Replaces common associate with custom one
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @param    int    country ID
     * @param    int    associcate ID
     * @return    mixed
     * */
    public function replace_associate($case_number = '', $country_id = '', $associcate_id = "", $new_associate_id)
    {
        if (!is_null($case = $this->find_case_by_number($case_number))) {

            $data = array(
                'is_active' => '0'
            );
            $this->db->where('case_id', $case['id']);
            $this->db->where('associate_id', $associcate_id);
            $this->db->update('cases_associates_data', $data);

            $new_associate_data = array(
                'associate_id' => $new_associate_id,
                'case_id' => $case['id'],
                'country_id' => $country_id,
                'is_active' => '1'
            );
            $this->db->insert('cases_associates_data', $new_associate_data);
        }
        return FALSE;
    }

    public function get_case_number($case_id){
        $this->db->select('case_number');
        $this->db->where('id',$case_id);
        $query = $this->db->get('cases');
        return $query->row_array();
    }

    /**
     * Updates a custom associate entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    custom associate ID
     * @param    int    case number
     * @return    mixed
     * */
    public function update_custom_associate($custom_associate_id, $case_number)
    {
        if (!is_null($case = $this->find_case_by_number($case_number))) {
            $email = $this->input->post('email');
            $contact_name = $this->input->post('contact_name');
            $country_id = $this->input->post('country_id');
            $associate = $this->input->post('associate');
            $fee = $this->input->post('fee');
            $fee_currency = $this->input->post('fee_currency');
            $translation_required = $this->input->post('translation_required');
            $_30_months = $this->input->post('30_months');
            $_31_months = $this->input->post('31_months');
            $ep_validation = $this->input->post('ep_validation');
            $reference_number = $this->input->post('reference_number');

            $data = array(
                'case_id' => $case['id'],
                'country_id' => $country_id,
                'email' => $email,
                'contact_name' => $contact_name,
                'associate' => $associate,
                'fee' => $fee,
                'fee_currency' => $fee_currency,
                'translation_required' => $translation_required,
                '30_months' => $_30_months,
                '31_months' => $_31_months,
                'reference_number' => $reference_number,
                'ep_validation' => $ep_validation,
            );
            $this->db->where('id', $custom_associate_id);
            $this->db->update('custom_associates', $data);
            $data = array(
                'reference_number' => $reference_number,
            );

            $this->db->where('associate_id', $custom_associate_id);
            $this->db->update('cases_associates_data', $data);
            return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
        }
    }

    /**
     * Removes a custom associate entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    custom associate ID
     * @return    bool
     * */
    public function delete_custom_associate($custom_associate_id, $case_id)
    {
        $this->db->where('associate_id', $custom_associate_id);
        $this->db->where('case_id', $case_id);
        $this->db->delete('cases_associates_data');

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Sets file visibility
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    file ID
     * @param    int    visibility
     * @return    bool
     * */
    public function set_file_visibility($file_id, $visibility)
    {
        $this->db->where('id', $file_id);
        $this->db->update('cases_files', array('visibility' => $visibility));

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    public function set_file_fa_visibility($file_id, $visibility)
    {
        $this->db->where('id', $file_id);
        $this->db->update('cases_files', array('visible_to_fa' => $visibility));

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Returns a list of file types
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     * */
    public function file_types($type = NULL)
    {
        $this->db->select('id,name');
        if ($type == '1') {
            $this->db->where('pm_upload_type', '1');
        }
        $this->db->order_by('name');
        $query = $this->db->get('file_types');

        if ($query->num_rows()) {
            return $query->result_array();
        }

        return NULL;
    }

    /**
     * Sets type for file
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    file ID
     * @param    int    file type ID
     * @return    bool
     * */
    public function set_file_type($file_id, $file_type_id)
    {
        $this->db->where('id', $file_id);
        $data = array('file_type_id' => $file_type_id);
        $filetypes = $this->get_file_types();
        if (in_array($file_type_id, $filetypes[6])) {
            $data['visibility'] = '1';
        }
        $this->db->update('cases_files', $data);
        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Returns case entry by file ID
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    file ID
     * @return    mixed
     * */
    public function get_case_by_file_id($file_id = '')
    {
        $this->db->select('cases.*', FALSE);
        $this->db->where('cases_files.id', $file_id);
        $this->db->join('cases', 'cases.id = cases_files.case_id');
        $this->db->limit(1);
        $query = $this->db->get('cases_files');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Sets "extension needed" option for country
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    int    country ID
     * @param    string    value
     * @return    void
     * */
    public function set_extension_needed($case_id, $country_id, $extension_needed)
    {
        $this->db->where('case_id', $case_id);
        $this->db->where('country_id', $country_id);
        $this->db->update('cases_countries', array('extension_needed' => $extension_needed));
    }

    function make_associates_visible_to_client($case_id, $is_visible)
    {

        if (empty($case_id)) {
            return false;
        }

        $this->db->where('id', $case_id);
        $options['is_associates_visible_to_client'] = $is_visible;
        if (empty($options['is_associates_visible_to_client'])) {
            $options['is_associates_visible_to_client'] = '0';
        }
        $this->db->update('cases', $options);
        return $this->db->affected_rows();
    }

    /**
     * Returns countries by type of case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    array
     * */
    public function get_countries_by_case_type($case_number, $get_only_with_fee = false)
    {

        $result = array();
        if (!is_null($case = $this->find_case_by_number($case_number))) {

            $type = '';
            if ($case['case_type_id'] == '1') {
                $type = 'pct';
                if ($get_only_with_fee) {
                    $this->db->join('fees', 'fees.country_id = countries.id');
                    $this->db->where('fees.case_type_id', $case['case_type_id']);
                }


            } elseif ($case['case_type_id'] == '2') {
                $type = 'ep-validation';
            } elseif ($case['case_type_id'] == '3') {
                $type = 'direct-filing';
            }

            if (!empty($type)) {
                $this->db->select('zen_countries.*');
                $this->db->where("$type", "1");
                $query = $this->db->get('countries');
                if ($query->num_rows()) {
                    return $query->result_array();
                }
            }
        }
        return $result;
    }

    /**
     * Returns a list of active cases
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     * */
    public function get_active_cases()
    {
        $q = 'SELECT c.case_number,
					 c.case_type_id,
					 c.id,
					 c.highlight,
					 c.new_email_sign,
					 c.common_status,
					 c.approved_at,
					 c.reestimated_at,
					 c.manager_id,
					 c.30_month_filing_deadline as 30_month_filing_deadline_orig,
					 c.31_month_filing_deadline as 31_month_filing_deadline_orig,
				     DATE_FORMAT(filing_deadline, "%m/%d/%Y") as filing_deadline,
				     DATE_FORMAT(30_month_filing_deadline, "%m/%d/%Y") as 30_month_filing_deadline,
				     DATE_FORMAT(31_month_filing_deadline, "%m/%d/%Y") as 31_month_filing_deadline,
					 DATE_SUB(`filing_deadline`, INTERVAL 7 DAY) as filing_deadline_week_ago,
					 (SELECT CONCAT(cn.note, " ", DATE_FORMAT(created_at, "(%m/%d/%Y %r)")) FROM `zen_cases_notes` cn WHERE cn.case_number = c.case_number ORDER BY created_at DESC LIMIT 1) as last_note,
					 cus.id as client_number, 
					 cus.username as client_name,
					 m.username as manager_name,
					 c.reference_number
			  FROM `zen_cases` c
			  LEFT JOIN `zen_customers` cus
			  ON (c.user_id = cus.id)
			  LEFT JOIN `zen_managers` m
			  ON (c.manager_id = m.id)
			  WHERE (c.case_number != "") AND
					(c.common_status = "active")
					AND (c.is_active = "1")';
        $query = $this->db->query($q);
        if ($query->num_rows()) {
            $cases = $query->result_array();

            foreach ($cases as $key => $case) {

                if ($case['case_type_id'] == '1') {
                    $this->db->select('associates.id');
                    $this->db->join('estimates_countries_fees', 'estimates_countries_fees.country_id = associates.country_id');
                    $this->db->join('cases_tracker', 'cases_tracker.country_id = associates.country_id');
                    $this->db->where('associates.30_months', '1');
                    $this->db->where('estimates_countries_fees.is_approved', '1');
                    $this->db->where('cases_tracker.fr_completed =', '0000-00-00 00:00:00');
                    $this->db->where('cases_tracker.case_id', $case['id']);
                    $query = $this->db->get('associates');
                    if ($query->num_rows()) {
                        $cases[$key]['filing_deadline'] = $case['30_month_filing_deadline'];
                    } else {
                        $cases[$key]['filing_deadline'] = $case['31_month_filing_deadline'];
                    }
                }
                $cases[$key]['last_note'] = is_null($cases[$key]['last_note']) ? '&nbsp;' : $cases[$key]['last_note'];
            }
            return $cases;
        }
        return NULL;
    }

    /**
     * Returns a list of pending cases
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     * */
    public function get_pending_cases()
    {
        $q = 'SELECT c.case_number,
					 c.case_type_id,
					 c.id,
					 c.highlight,
					 c.manager_id,
					 c.new_email_sign,
					 c.common_status,
					 c.reestimated_at,
					 c.approved_at,
					 c.filing_deadline as filing_deadline_orig,
					 c.30_month_filing_deadline as 30_month_filing_deadline_orig,
					 c.31_month_filing_deadline as 31_month_filing_deadline_orig,
				     DATE_FORMAT(filing_deadline, "%m/%d/%Y") as case_filing_deadline,
				     DATE_FORMAT(30_month_filing_deadline, "%m/%d/%Y") as 30_month_filing_deadline,
				     DATE_FORMAT(31_month_filing_deadline, "%m/%d/%Y") as 31_month_filing_deadline,
				     DATE_FORMAT(filing_deadline, "%m/%d/%Y") as filing_deadline,
					 DATE_SUB(`filing_deadline`, INTERVAL 7 DAY) as filing_deadline_week_ago,
					 (SELECT CONCAT(cn.note, " ", DATE_FORMAT(created_at, "(%m/%d/%Y %r)")) FROM `zen_cases_notes` cn WHERE cn.case_number = c.case_number ORDER BY created_at DESC LIMIT 1) as last_note,
					 cus.id as client_number, 
					 cus.username as client_name,
					 m.username as manager_name,
					 c.reference_number
			  FROM `zen_cases` c
			  LEFT JOIN `zen_customers` cus
			  ON (c.user_id = cus.id)
			  LEFT JOIN `zen_managers` m
			  ON (c.manager_id = m.id)
			  WHERE (case_number != "") AND
					(common_status IN ("estimating", "pending-intake", "pending-approval", "estimating-estimate", "estimating-estimate", "estimating-reestimate", "hidden"))
					 AND(is_active = "1")
			  ORDER BY common_status DESC';
        $query = $this->db->query($q);

        if ($query->num_rows()) {
            $cases = $query->result_array();

            foreach ($cases as $key => $case) {

                if ($case['case_type_id'] == '1') {

                    $this->db->select('associates.id');
                    $this->db->join('estimates_countries_fees', 'estimates_countries_fees.country_id = associates.country_id');
                    $this->db->join('cases_tracker', 'cases_tracker.country_id = associates.country_id');
                    $this->db->where('associates.30_months', '1');
                    $this->db->where('estimates_countries_fees.is_approved', '1');
                    $this->db->where('cases_tracker.fr_completed =', '0000-00-00 00:00:00');
                    $this->db->where('cases_tracker.case_id', $case['id']);
                    $this->db->group_by('associates.country_id');
                    $query = $this->db->get('associates');
                    if ($query->num_rows()) {
                        $cases[$key]['filing_deadline'] = $case['30_month_filing_deadline'];
                    } else {
                        $cases[$key]['filing_deadline'] = $case['31_month_filing_deadline'];
                    }
                }
                $cases[$key]['last_note'] = is_null($cases[$key]['last_note']) ? '&nbsp;' : $cases[$key]['last_note'];
            }
            return $cases;
        }
        return NULL;
    }

    /**
     * Returns a list of completed cases
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     * */
    public function get_completed_cases()
    {
        $q = 'SELECT c.case_number,
					 c.case_type_id,
					 c.id,
					 c.highlight,
					 c.manager_id,
					 c.new_email_sign,
					 c.filing_deadline as filing_deadline_orig,
					 c.30_month_filing_deadline as 30_month_filing_deadline_orig,
					 c.31_month_filing_deadline as 31_month_filing_deadline_orig,
				     DATE_FORMAT(filing_deadline, "%m/%d/%Y") as case_filing_deadline,
				     DATE_FORMAT(30_month_filing_deadline, "%m/%d/%Y") as 30_month_filing_deadline,
				     DATE_FORMAT(31_month_filing_deadline, "%m/%d/%Y") as 31_month_filing_deadline,
				     DATE_FORMAT(filing_deadline, "%m/%d/%Y") as filing_deadline,
					 DATE_SUB(`filing_deadline`, INTERVAL 7 DAY) as filing_deadline_week_ago,
					 (SELECT CONCAT(cn.note, " ", DATE_FORMAT(created_at, "(%m/%d/%Y %r)")) FROM `zen_cases_notes` cn WHERE cn.case_number = c.case_number ORDER BY created_at DESC LIMIT 1) as last_note,
					 cus.id as client_number, 
					 cus.username as client_name,
					 m.username as manager_name, 
					 c.reference_number
			  FROM `zen_cases` c
			  LEFT JOIN `zen_customers` cus
			  ON (c.user_id = cus.id)
			  LEFT JOIN `zen_managers` m
			  ON (c.manager_id = m.id)
			  WHERE (case_number != "") AND
					(common_status = "completed") AND
					(is_active = "1")';
        $query = $this->db->query($q);
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns a list of case regions (countries)
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @return    mixed
     * */
    public function get_regions_for_case($case_id = '')
    {
        $this->db->distinct();
        $this->db->select('countries.country, countries.code');
        $this->db->where('cases_countries.case_id', $case_id);
        $this->db->join('countries', 'cases_countries.country_id = countries.id');
        $query = $this->db->get('cases_countries');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns a list of ALL case regions (countries)
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    array
     * */
    public function get_case_regions()
    {
        $result = array();

        $this->db->join('countries', 'cases_countries.country_id = countries.id');
        $this->db->order_by('case_id');
        $this->db->select('case_id, countries.code');
        $query = $this->db->get('cases_countries');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $country) {
                $result[$country['case_id']][] = $country['code'];
            }
        }
        return $result;
    }

    /**
     * Returns a list of ALL approved case regions (countries)
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    array
     * */
    public function get_approved_regions_for_all_cases()
    {
        $result = array();

        $q = 'SELECT c.id, cs.code
			  FROM `zen_cases` c, `zen_countries` cs, `zen_estimates_countries_fees` ecf
			  WHERE (c.id = ecf.case_id) AND
			  		(cs.id = ecf.country_id)';
        $query = $this->db->query($q);
        if ($query->num_rows()) {
            foreach ($query->result_array() as $item) {
                $result[$item['id']][] = $item['code'];
            }
        }
        return $result;
    }

    /**
     * Renames file both in DB and in FTP
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    file ID
     * @return    void
     * */
    public function rename_file($file_id)
    {
        $filename = $this->input->post('filename');
        // Get file entry
        if (!is_null($file = $this->get_file($file_id))) {
            $path_info = pathinfo($file['location']);
            $new_location = $path_info['dirname'] . '/' . $filename . '.' . $path_info['extension'];

            if (rename($file['location'], $new_location)) {
                $data = array(
                    'filename' => $filename . '.' . $path_info['extension'],
                    'location' => $new_location
                );

                $this->db->where('id', $file_id);
                $this->db->update('cases_files', $data);
            }
        }
    }

    /**
     * Returns a list of case notes
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @param    int    user ID
     * @return    mixed
     * */
    public function case_notes($case_number)
    {
        $this->db->select('zen_cases_notes.*, cases_notes.id as id, UNIX_TIMESTAMP(zen_cases_notes.created_at) as created_at_orig, DATE_FORMAT(zen_cases_notes.created_at, "%m/%d/%Y %r") as created_at, managers.username', FALSE);
        $this->db->order_by('client_note', 'desc');
        $this->db->order_by('created_at_orig', 'desc');
        $this->db->join('managers', 'managers.id = cases_notes.user_id');
        $this->db->where('case_number', $case_number);
        $this->db->where('client_note', '0');
        $query = $this->db->get('cases_notes');
        if ($query->num_rows()) {
            return $query->result_array();
        }

        return NULL;
    }

    /**
     * Returns a list of client notes
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    user ID
     * @return    mixed
     * */
    public function client_notes($client_id = '')
    {
        $this->db->select('*, cases_notes.id as id, UNIX_TIMESTAMP(zen_cases_notes.created_at) as created_at_orig, DATE_FORMAT(created_at, "%m/%d/%Y %r") as created_at, managers.username', FALSE);
        $this->db->order_by('created_at_orig', 'desc');
        $this->db->join('managers', 'managers.id = cases_notes.user_id');
        $this->db->where('client_user_id', $client_id);
        $this->db->where('client_note', '1');
        $query = $this->db->get('cases_notes');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    public function fa_case_notes($case_number){

        $this->db->select('zen_cases_notes.*, cases_notes.id as id, UNIX_TIMESTAMP(zen_cases_notes.created_at) as created_at_orig, DATE_FORMAT(zen_cases_notes.created_at, "%m/%d/%Y %r") as created_at, associates.username', FALSE);
        $this->db->order_by('client_note', 'desc');
        $this->db->order_by('created_at_orig', 'desc');
        $this->db->join('associates', 'associates.id = cases_notes.fa_id');
        $this->db->where('client_note', '2');
        $query = $this->db->get('cases_notes');
        if ($query->num_rows()) {
            return $query->result_array();
        }

        return NULL;
    }

    /**
     * Adds a note to case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    string
     * */
    public function add_note_for_case($case_number)
    {
        $manager_id = $this->session->userdata('manager_user_id');
        $client_user_id = $this->input->post('client_user_id');
        $note_text = $this->input->post('note_text');
        $is_client_note = $this->input->post('is_client_note');
        $now = date('Y-m-d H:i:s');
        $now_am_style = date('m/d/Y H:i:s A');
        $data = array(
            'case_number' => $case_number,
            'note' => $note_text,
            'created_at' => $now,
            'user_id' => $manager_id,
            'client_user_id' => $client_user_id,
            'client_note' => "$is_client_note"
        );

        $this->db->insert('cases_notes', $data);

        $data['note_id'] = $this->db->insert_id();
        $data['created_at'] = $now_am_style;
        $data['is_client_note'] = $is_client_note;
        $data['username'] = $this->session->userdata('manager_username');
        $data['delete_link'] = '<a title="Remove" href="javascript:void(0);" id="delete_note_link_' . $data['note_id'] . '" onclick="if(confirm(\'Do you really want to DELETE selected note?\')){ remove_note_from_case(' . $data['note_id'] . ');}"><img src="' . base_url() . 'assets/images/i/delete.png" alt="Remove"/></a>';
        return json_encode($data);
    }

    /**
     * Removes note from case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    note ID
     * @return    void
     * */
    public function remove_note_from_case($note_id)
    {
        $this->db->where('id', $note_id);
        $this->db->delete('cases_notes');
    }

    /**
     * Returns a list of files by email type (for AJAX)
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    array
     * */
    public function get_list_files_by_email_type()
    {
        $this->load->model('associates_model', 'associates');
        $case_number = $this->input->post('case_number');
        $email_type = $this->input->post('email_type');
        $result['zip_hash'] = '';
        $result = array('attached_files' => '', 'countries' => '');
        if (!is_null($case = $this->find_case_by_number($case_number))) {
            switch ($email_type) {
                // Statement of work unsigned, Invoice
                case 'intake-email':
                    $file_types = array(7, 8);
                    break;
                // Application (1), Amendment (4), Sequence Listing (12), other case files (11)
                //
                case 'fa-request':
                    $file_types = array(1, 4, 11, 12);
                    break;
                // POA (2), Assignment Form (14), Associate Acknowledgement Letter (10), ZenFile associates (17)
                case 'document-instruction':
                    $file_types = array(2, 10, 14, 17);
                    break;
                // Filing Receipt, Filing Report
                case 'filing-confirmation':
                    $file_types = array(6, 17);
                    break;
                // All Files
                case 'new-email':
                    $file_types = array(-1);
                    break;
                case 'translation_request':
                    $file_types = array(1);
                    break;
            }
            if ($email_type != 'fa-request') {
                $file_ids = array(-1);

                $this->db->where('case_id', $case['id']);
                $this->db->where_in('file_type_id', $file_types);
                $query = $this->db->get('cases_files');
                if ($query->num_rows()) {
                    foreach ($query->result_array() as $file) {
                        $result['attached_files'] .= '<span id="attached_' . $file['id'] . '">
												<input type="hidden" name="attached_file_' . $file['id'] . '" value="' . $file['id'] . '" />
												<a href="javascript:void(0);" onclick="remove_file_from_email(' . $file['id'] . ')"><img src="' . base_url() . 'assets/images/i/delete.png" title="Remove file" /></a><a title="View file" href="' . base_url() . 'cases/view_file/' . $file['id'] . '">' . $file['filename'] . '</a><br/></span>';
                        $file_ids[] = $file['id'];
                    }
                    $result['zip_hash'] = md5(date('Y-m-d H:i:s'));
                }
            }
            $faresult = $this->associates->new_get_all_case_associates($case['id'], '1');
            if (!empty($faresult)) {
                // Get all sent emails
                $sent_email_result_final = array();
                $manager_id = $this->session->userdata('manager_user_id');
                $this->db->where('manager_id', $manager_id);
                $this->db->where('case_id', $case['id']);
                $this->db->where('email_type', $email_type);
                $sent_email = $this->db->get('sent_emails');
                if ($sent_email->num_rows()) {
                    $sent_email_result = $sent_email->result_array();
                    foreach ($sent_email_result as $item) {
                        $sent_email_result_final[$item['country_id']] = $item;
                    }
                }
                foreach ($faresult as $record) {
                    if ($record['tracker_translation_required'] == 1) {
                        $translation_required = 1;
                    } else {
                        $translation_required = 0;
                    }

                    $country_class = (!empty($sent_email_result_final[$record['country_id']]['created_at'])) ? 'sent' : '';
                    $result['countries'] .= '<div class="country-box ' . $country_class . '">
												<a id="' . $record['country_id'] . '" class="email-country" href="javascript:void(0);">' . $record['country'] . '</a>' .
                        form_hidden('translation_needed_' . $record['country_id'], $translation_required) .
                        form_hidden('country_association_id_' . $record['country_id'], $record["associate_id"]) . '
											</div>';
                }
            }
            // List of countries
        }
        return $result;
    }

    /**
     * Returns text for email. Generates email text by given template (for AJAX)
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    string
     * */
    public function get_associate_by_id($country_association_id)
    {
        $this->db->select('associates.*, countries.country');
        $this->db->join('countries', 'associates.country_id = countries.id');
        $this->db->where('associates.id', $country_association_id);
        $query = $this->db->get('associates');
        return $query->row_array();

    }

    public function get_replaced_associate($country_association_id, $case_id)
    {
        $this->db->select('custom_associates.*, countries.country');
        $this->db->join('countries', 'custom_associates.country_id = countries.id');
        $this->db->where('custom_associates.associate_id', $country_association_id);
        $this->db->where('case_id', $case_id);
        $query = $this->db->get('custom_associates');
        return $query->row_array();
    }

    public function get_email_text()
    {
        $ci = & get_instance();
        $ci->load->model('emails_model', 'emails');
        $ci->load->model('associates_model', 'associates');
        $country_association_id = $this->input->post("country_association_id");
        $translation_needed = $this->input->post('translation_needed');
        $cc = $this->input->post('cc');
        $case_id = $this->input->post('case_id');
        $case_number = $this->input->post('case_number');
        $case_type_id = $this->input->post('case_type_id');
        $country_id = $this->input->post('country_id');
        $email_type = $this->input->post('email_type');
        $files = $this->input->post('files');
        $extension_needed = '0';
        $this->db->select('extension_needed');
        $this->db->where('case_id', $case_id);
        $this->db->where('country_id', $country_id);
        $query = $this->db->get('cases_countries');
        if ($query->num_rows()) {
            $extension_needed = $query->row_array();
            $extension_needed = $extension_needed['extension_needed'];
        }

        $zip_hash = '';
        if ($email_type != 'fa-request') {
            $zip_hash = $this->input->post('zip_hash');
        }
        $files = NULL;
        // FA data
        $fa = $this->get_associate_by_id($country_association_id);

        // Client data
        $customer = $this->get_customer_by_case_number($case_number);


        $text = '';
        switch ($email_type) {
            case 'intake-email':
                if ($case_type_id == 1) {
                    $template_id = 4;
                } elseif ($case_type_id == 2) {
                    $template_id = 3;
                } elseif ($case_type_id == 3) {
                    $template_id = 2;
                }
                $to = $customer['email'];
                $file_types = array(7, 8);
                break;
            case 'fa-request':
                if ($translation_needed == 1) {
                    if ($extension_needed == 1) {
                        if ($case_type_id == 1) {
                            $template_id = 12;
                        } elseif ($case_type_id == 2) {
                            $template_id = 9;
                        } elseif ($case_type_id == 3) {
                            $template_id = 13;
                        }
                    } else {
                        if ($case_type_id == 1) {
                            $template_id = 11;
                        } elseif ($case_type_id == 2) {
                            $template_id = 7;
                        } elseif ($case_type_id == 3) {
                            $template_id = 5;
                        }
                    }
                } else {
                    if ($case_type_id == 1) {
                        $template_id = 10;
                    } elseif ($case_type_id == 2) {
                        $template_id = 8;
                    } elseif ($case_type_id == 3) {
                        $template_id = 6;
                    }
                }
                $file_types = array(1, 4, 11, 12);
                $to = $fa['email'];
                break;
            case 'document-instruction':
                $template_id = 14;
                $file_types = array(2, 10, 14, 17);
                $to = $customer['email'];
                break;
            case 'filing-confirmation':
                if (!empty($country_id)) {
                    $template_id = 19;
                } else {
                    $template_id = 15;
                }
                $file_types = array(6);
                $to = $customer['email'];
                break;
            case 'new-email':
                $template_id = 16;
                $file_types = array(-1);
                $to = '';
                break;
            case 'translation_request':
                $template_id = 25;
                $file_types = array(-1);
                //Brian wants to default the To: Bryan.Millstein@parkip.com Victor He https://park12.teamlab.com/products/projects/tasks.aspx?prjID=342194&id=2307302#task_comments
                $to = 'Bryan.Millstein@parkip.com';
                break;
        }
        $attached_files = '';
        $file_ids = array(-1);
        $this->db->select('cases_files.*');
        $this->db->join('files_countries', 'cases_files.id = files_countries.file_id');
        $this->db->join('cases_tracker', 'files_countries.country_id = cases_tracker.country_id AND zen_cases_files.case_id = zen_cases_tracker.case_id');
        $this->db->where('cases_tracker.country_id', $country_id);
        $this->db->where('cases_tracker.case_id', $case_id);
        $this->db->where_in('cases_files.file_type_id', $file_types);
        $this->db->where('cases_tracker.translation_required >', '0');
        $query = $this->db->get('cases_files');
        $temp_files = array();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $file) {
                if (!in_array($file['id'], $temp_files)) {
                    $temp_files[] = $file['id'];
                    $attached_files .= '<span class="attachtocountry" id="attached_' . $file['id'] . '">
												<input type="hidden" name="attached_file_' . $file['id'] . '" value="' . $file['id'] . '" />
												<a href="javascript:void(0);" onclick="remove_file_from_email(' . $file['id'] . ')"><img src="' . base_url() . 'assets/images/i/delete.png" title="Remove file" /></a><a title="View file" href="' . base_url() . 'cases/view_file/' . $file['id'] . '">' . $file['filename'] . '</a><br/></span>';
                    $file_ids[] = $file['id'];
                }
            }
            $zip_hash = md5(date('Y-m-d H:i:s'));

        }
        $text = $ci->emails->get_ready_email_from_template($template_id, $case_id, $country_id, $files, 777, $email_type, $country_association_id, $zip_hash);
        $text['zip_hash'] = $zip_hash;
        $text['attached_files'] = $attached_files;
        $contacts = $this->get_contacts_of_the_case(array('case_id' => $case_id));
        $data['contacts'] = '';
        foreach ($contacts as $contact) {
            $data['contacts'] .= $contact->email . ', ';
        }
        $data['contacts'] = substr($data['contacts'], 0, -2);
        $text['to'] = $to;
        $text['cc'] = '';
        if ($email_type != 'translation_request') {
            if ($data['contacts']) {
                $text['cc'] = $data['contacts'];
            }
        }
        return json_encode($text);
    }

    public function get_countries_by_file($case, $files = '', $file_types)
    {
        $this->db->select('files_countries.country_id, countries.country, cases_files.filename, cases_files.location, cases_files.id');
        $this->db->join('cases_files', 'cases_files.id = files_countries.file_id');
        $this->db->join('countries', 'countries.id = files_countries.country_id');
        $this->db->where('cases_files.case_id', $case);
        $this->db->where_in('files_countries.file_id', $files);
        $this->db->where_in('cases_files.file_type_id', $file_types);
        $query = $this->db->get('files_countries');
        return $query->result_array();
    }

    public function update_case_manager($case_number)
    {
        $manager_id = $this->session->userdata('manager_user_id');
        $this->db->set('manager_id', $manager_id);
        $this->db->where('case_number', $case_number);
        $this->db->update('cases');
    }

    function recursive_array_search($check, $array)
    {
        foreach ($array as $value) {
            if ($value['id'] == $check) {
                return true;
            }
        }
        return false;
    }

    public function send_email()
    {
        ini_set('memory_limit', '256M');
        $ci = & get_instance();
        $ci->load->model('associates_model', 'associates');
        $ci->load->model('countries_model', 'countries');
        $ci->load->model('files_model', 'files');
        $ci->load->model('send_emails_model', 'send_emails');
        $this->load->library('zip');
        $case_number = $this->input->post('case_number');
        $zip_hash = $this->input->post('zip_hash');
        $to = $this->input->post('to');
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
        $cc = $this->input->post('cc');
        $attachments = '';
        $send_cc = '';
        if (!empty($cc)) {
            $check_comma = substr($cc, -1);
            if ($check_comma == ',' || $check_comma == ';') {
                $cc = substr($cc, 0, -1);
            }
            if (strpos($cc, ';') === FALSE) {
                $send_cc = explode(',', $cc);
            }
            else {
                $send_cc = explode(';', $cc);
            }
        }
        $subject = $this->input->post('subject');
        $text = $this->input->post('text', FALSE);
        $files = $this->input->post('files');
        $case_files = '';
        if ($files){
            $zip_hash = md5(date('Y-m-d H:i:s'));
            $case_files = '<a href="https://' . $_SERVER['SERVER_NAME'] . '/zip/' . $zip_hash . '">files</a>';

        }
        $text = str_replace('%CASE_FILES%', $case_files, $text);
        //var_dump($files);exit;
        $email_type = $this->input->post('email_type');
        $country_id = $this->input->post('country_id');
        if ($email_type == 'filing-confirmation') {
            $file_types_id = array(6);
        } elseif ($email_type == 'document-instruction') {
            $file_types_id = array(2, 10, 14, 17);
        } elseif ($email_type == 'translation_request') {
            $file_types_id = array(1);
        } elseif ($email_type == 'fa-request') {
            $file_types_id = array(1, 3, 4, 11, 12);
        } elseif ($email_type == 'intake-email') {
            $file_types_id = array(7, 8);
        }

        if (!is_null($customer = $this->get_customer_by_case_number($case_number))) {
            if (!is_null($case = $this->find_case_by_number($case_number))) {
                $this->removedir('uploads/tmp/' . $case['case_number'] . '/');
                $this->removedir('uploads/tmp/' . $case['case_number'] . '/attachments/');

                $fa = $ci->associates->get_associate_by_country_id($country_id);

                if (file_exists('uploads/tmp/' . $case['case_number'] . '.zip')) {
                    @unlink('uploads/tmp/' . $case['case_number'] . '.zip');
                }
                @rrmdir('uploads/tmp/' . $case['case_number']);

                if (check_array($files)) {
                    if ($email_type == 'filing-confirmation' || $email_type == 'document-instruction' || $email_type == 'translation_request') {

                        if (mkdir('uploads/tmp/' . $case['case_number'] . '/', 0777)) {

                            if (!is_null($countries = $this->get_countries_by_file($case['id'], $files, $file_types_id))) {
                                if ($email_type != 'translation_request') {
                                    foreach ($countries as $countryfile) {
                                        $country_name = '';
                                        if ($country_name != $countryfile['country']) {
                                            $country_name = $countryfile['country'];
                                            $country_dir = 'uploads/tmp/' . $case['case_number'] . '/' . $country_name . '/';
                                            if ($email_type == 'filing-confirmation') {
                                                $this->save_tracker($case['id'], $countryfile['country_id'], 'fr_sent', date('Y-m-d H:i:s'));
                                                $result['result'] = 'filing-confirmation';
                                                $result['countries'][] = array('id' => $countryfile['country_id'], 'date' => date('m/d/y'));
                                            }
                                        }
                                        if (!file_exists($country_dir)) {
                                            @mkdir($country_dir, 0777);
                                        }
                                        if (!file_exists($country_dir . $countryfile['filename'])) {
                                            @copy('../pm/' . $countryfile['location'], $country_dir . $countryfile['filename']);
                                        }
                                    }
                                    $this->zip->read_dir('uploads/tmp/' . $case['case_number'] . '/', FALSE);
                                }
                            }
                            $temporary_files = $this->get_case_files($case['id'], $file_types = array(20));
                            /// new code
                            if ($email_type != 'translation_request') {
                                foreach ($files as $key => $file) {
                                    if ($this->recursive_array_search($file, $countries)) {
                                        unset($files[$key]);
                                    }
                                }
                            }

                            if (!empty($files)) {
                                $temp_files = $this->get_files_by_id_array($files);
                            }

                            if ($temporary_files) {
                                foreach ($temporary_files as $file) {
                                    $this->db->set('file_type_id', '21');
                                    $this->db->where('id', $file['id']);
                                    $this->db->update('cases_files');
                                }

                            }

                            if (isset($temporary_files) && isset($temp_files)) {
                                $temporary_files = array_merge($temporary_files, $temp_files);
                            } elseif (isset($temp_files)) {
                                $temporary_files = $temp_files;
                            }
                            ///end new code

                            if ($temporary_files) {

                                $path_dir = 'uploads/tmp/' . $case['case_number'] . '/attachments/';

                                if (file_exists($path_dir)) {

                                    @rmdir($path_dir);
                                    @rrmdir($path_dir);
                                }
                                if (mkdir($path_dir, 0777)) {
                                    foreach ($temporary_files as $file) {
                                        @copy('../pm/' . $file['location'], $path_dir . $file['filename']);
                                    }
                                    $this->zip->read_dir('uploads/tmp/' . $case['case_number'] . '/attachments/', FALSE);
                                }
                            }

                            $this->zip->archive('uploads/tmp/' . $case['case_number'] . '.zip');

                            // Create hash for zip
                            $country_id_value = $country_id;
                            if (empty($country_id)) {
                                $country_id_value = 'allcountries';
                            }

                            //log_message('error', 'filing confirmation here');
                            // Move zip file to user's folder
                            if (!is_dir('uploads/' . $case['user_id'] . '/')) {
                                mkdir('uploads/' . $case['user_id'] . '/', 0777);
                            }
                            if (!is_dir('uploads/' . $case['user_id'] . '/' . $case['case_number'] . '/')) {
                                mkdir('uploads/' . $case['user_id'] . '/' . $case['case_number'] . '/', 0777);
                            }
                            $new_path = 'uploads/' . $case['user_id'] . '/' . $case['case_number'] . '/' . $case['case_number'] . '_' . date('YmdHis') . '.zip';

                            if (rename('uploads/tmp/' . $case['case_number'] . '.zip', $new_path)) {
                                $country_list = array();
                                if (!empty($countries)) {
                                    foreach ($countries as $country) {
                                        if (!in_array($country['country_id'], $country_list))
                                            $country_list[] = $country['country_id'];
                                    }
                                }
                                $country_list = implode(',', $country_list);

                                $ci->files->add_zipped_file($case['id'], $new_path, $zip_hash, $email_type, $country_list);

                            }

                            $this->update_translation_request_sent($case['id']);
                        }
                    } elseif ($email_type == 'fa-request') {
                        // Set status "Request sent to FA"
                        if (!empty($country_id)) {
                            $this->save_tracker($case['id'], $country_id, 'fi_requests_sent_fa', 'current_date');
                            $result['result'] = 'fa-request';
                            $result['country_id'] = $country_id;
                            $result['date'] = date('m/d/y');
                        }
                    }
                    if ($email_type == 'intake-email' || $email_type == 'fa-request') {
                        //var_dump($files);exit;
                        foreach($files as $file){
                            $this->db->set('visible_to_fa', '1');
                            $this->db->where('id', $file);
                            $this->db->update('cases_files');
                        }
                        // For document instruction
                        $json_countries = array();
                        $files_countries = $ci->files->get_countries_by_file_types($case['id'], $file_types_id);
                        if (check_array($files_countries)) {
                            foreach ($files_countries as $files_country) {
                                $json_countries[] = $files_country['id'];
                            }
                        }

                        $case_dir = 'uploads/tmp/' . $case['case_number'];
                        if (@mkdir($case_dir . '/')) {

                            // 5,6 - Filing Receipt and Filing Report
                            if (!is_null($case_files = $this->get_case_files($case['id'], $file_types_id))) {
                                foreach ($case_files as $file) {
                                    //log_message('error', 'file in array: '.intval(in_array($file['id'], $files)));
                                    if (in_array($file['id'], $files)) {
                                        @copy('../pm/' . $file['location'], $case_dir . '/' . $file['filename']);
                                    }
                                }
                            }
                            $this->zip->read_dir($case_dir . '/', FALSE);

                            $temporary_files = $this->get_case_files($case['id'], $file_types = array(20));
                            foreach ($files as $key => $file) {
                                if ($this->recursive_array_search($file, $case_files)) {
                                    unset($files[$key]);
                                }
                            }
                            if (!empty($files)) {
                                $temp_files = $this->get_files_by_id_array($files);
                            }
                            if ($temporary_files) {
                                foreach ($temporary_files as $file) {
                                    $this->db->set('file_type_id', '21');
                                    $this->db->where('id', $file['id']);
                                    $this->db->update('cases_files');
                                }
                            }
                            if (isset($temporary_files) && isset($temp_files)) {
                                $temporary_files = array_merge($temporary_files, $temp_files);
                            } elseif (isset($temp_files)) {
                                $temporary_files = $temp_files;
                            }

                            if ($temporary_files) {
                                $path_dir = 'uploads/tmp/' . $case['case_number'] . '/attachments/';

                                if (file_exists($path_dir)) {
                                    @rmdir($path_dir);
                                    @rrmdir($path_dir);
                                }
                                if (@mkdir($path_dir, 0777)) {
                                    foreach ($temporary_files as $file) {
                                        @copy('../pm/' . $file['location'], $path_dir . $file['filename']);
                                    }
                                    $this->zip->read_dir('uploads/tmp/' . $case['case_number'] . '/attachments/', FALSE);
                                }
                            }
                            $this->zip->archive('uploads/tmp/' . $case['case_number'] . '.zip');

                            // Create hash for zip
                            $country_id_value = $country_id;
                            if (empty($country_id)) {
                                $country_id_value = 'allcountries';
                            }
                            // Move zip file to user's folder
                            $new_path = 'uploads/' . $case['user_id'] . '/' . $case['case_number'] . '/' . $case['case_number'] . '_' . date('YmdHis') . '.zip';
                            if (@rename('uploads/tmp/' . $case['case_number'] . '.zip', $new_path)) {
                                $ci->files->add_zipped_file($case['id'], $new_path, $zip_hash, $email_type, $country_id);
                            }
                        }
                    }
                }

                if ($email_type == 'new-email') {
                    $temporary_files = $this->get_case_files($case['id'], $file_types = array(20));
                    if (!empty($files)) {
                        $temp_files = $this->get_files_by_id_array($files);
                    }
                    if ($temporary_files) {
                        foreach ($temporary_files as $key => $file) {
                            $this->db->set('file_type_id', '21');
                            $this->db->where('id', $file['id']);
                            $this->db->update('cases_files');
                            if (isset($temp_files)) {
                                if ($this->recursive_array_search($file['id'], $temp_files)) {
                                    unset($temporary_files[$key]);
                                }
                            }
                        }
                    }
                    if (isset($temporary_files) && isset($temp_files)) {
                        $attachments = array_merge($temporary_files, $temp_files);
                    } elseif (isset($temp_files)) {
                        $attachments = $temp_files;
                    } elseif (isset($temporary_files)) {
                        $attachments = $temporary_files;
                    }
                }
                if (TEST_MODE) {
                    switch ($email_type) {
                        case 'fa-request':
                            $send_to = TEST_FA_EMAIL;
                            break;

                        default:
                            $send_to = TEST_CLIENT_EMAIL;
                            if ($customer['type'] == 'firm') {
                                $send_to = TEST_FIRM_EMAIL;
                            }
                    }
                }
                $from = 'case' . $case['case_number'] . $this->config->item('default_email_box');

                if ($ci->send_emails->send_email($from, $from, $subject, $text, $send_to, $send_cc, $attachments)) {
                    // Insert data about sent email to database
                    $manager_id = $this->session->userdata('manager_user_id');
                    $data = array(
                        'case_id' => $case['id'],
                        'manager_id' => $manager_id,
                        'country_id' => $country_id,
                        'email_type' => $email_type,
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    $this->db->insert('sent_emails', $data);
                    $result['text'] = 'Email has been sent!';
                    return json_encode($result);
                } else {
                    $result['text'] = 'Email hasn\'t been sent!';
                    return json_encode($result);
                }
            }
        }
        $result['text'] = 'Error';
        return json_encode($result);
    }

    // function for updating translation request when sending email triggered (translation required email)
    function update_translation_request_sent($case_id) {
        $this->db->set('translation_request_sent_to_park' , date('Y-m-d H:i:s'));
        $this->db->where('translation_required' , '1');
        $this->db->where('case_id' , $case_id);
        $this->db->update('cases_tracker');
        return $this->db->affected_rows();

    }

    public function removedir($path)
    {
        if (file_exists($path) && is_dir($path)) {
            $dirHandle = opendir($path);
            while (false !== ($file = readdir($dirHandle))) {
                if ($file != '.' && $file != '..') // исключаем папки с назварием '.' и '..'
                {
                    $tmpPath = $path . '/' . $file;
                    chmod($tmpPath, 0777);

                    if (is_dir($tmpPath)) { // если папка
                        $this->removedir($tmpPath);
                    } else {
                        if (file_exists($tmpPath)) {
                            // удаляем файл
                            unlink($tmpPath);
                        }
                    }
                }
            }
            closedir($dirHandle);

            // удаляем текущую папку
            if (file_exists($path)) {
                rmdir($path);
            }
        }
    }

    /**
     * Does search on case list
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @param    string case type
     * @return    mixed
     * */
    public function do_search_by_case_number($case_number = '', $type = '')
    {
        $this->db->select('
        	cases.id, 
        	case_number, 
        	cases.manager_id, 
        	case_type_id, 
        	filing_deadline,
        	DATE_FORMAT(filing_deadline, "%m/%d/%Y") as case_filing_deadline_frm,
        	highlight, new_email_sign, common_status,
        	30_month_filing_deadline as 30_month_filing_deadline_orig, 
        	31_month_filing_deadline as 31_month_filing_deadline_orig, 
        	DATE_FORMAT(30_month_filing_deadline, "%m/%d/%Y") as 30_month_filing_deadline, 
        	DATE_FORMAT(31_month_filing_deadline, "%m/%d/%Y") as 31_month_filing_deadline, 
        	filing_deadline as filing_deadline_orig, 
        	DATE_FORMAT(filing_deadline, "%m/%d/%Y") as filing_deadline, 
        	DATE_SUB(`filing_deadline`, INTERVAL 7 DAY) as filing_deadline_week_ago,
        	(SELECT CONCAT(cn.note, " ", DATE_FORMAT(created_at, "(%m/%d/%Y %r)")) FROM `zen_cases_notes` cn WHERE cn.case_number = case_number ORDER BY created_at DESC LIMIT 1) as last_note,
        	approved_at, 
        	reestimated_at,
        	reference_number,
        	customers.username as client_name,
        	managers.username as manager_name,
        ',
            FALSE
        );
        $this->db->join('customers', 'customers.id = cases.user_id', 'left');
        $this->db->join('managers', 'managers.id = cases.manager_id', 'left');
        if ($type == 'active') {
            $this->db->order_by('new_email_sign', 'desc');
            $this->db->order_by('filing_deadline', 'desc');
            $this->db->order_by('case_number');
            $this->db->where('common_status', 'active');
        } elseif ($type == 'pending') {
            $where = array('estimating', 'pending-intake', 'pending-approval', 'estimating-estimate', 'estimating-reestimate', 'hidden');
            $this->db->where_in('common_status', $where);
            $this->db->order_by('common_status', 'desc');
        } else {
            $this->db->where('common_status', 'completed');
            $this->db->order_by('new_email_sign', 'desc');
        }

        if (!empty($case_number) && ($case_number != 'Case #')) {
            $this->db->like('case_number', $case_number);
        }
        $this->db->where('is_active', '1');
        $query = $this->db->get('cases');

        if ($query->num_rows()) {
            $cases = $query->result_array();
            foreach ($cases as $key => $case) {
                $week_ago_val = strtotime('+1 week', time());
                $cases[$key]['red_box'] = '0';
                if ($cases[$key]['case_type_id'] == '1') {
                    $this->db->select('associates.id');
                    $this->db->join('estimates_countries_fees', 'estimates_countries_fees.country_id = associates.country_id');
                    $this->db->join('cases_tracker', 'cases_tracker.country_id = associates.country_id');
                    $this->db->where('associates.30_months', '1');
                    $this->db->where('estimates_countries_fees.is_approved', '1');
                    $this->db->where('fr_completed =', '0000-00-00 00:00:00');
                    $this->db->where('cases_tracker.case_id', $case['id']);
                    $query = $this->db->get('associates');
                    if ($query->num_rows()) {
                        $cases[$key]['filing_deadline'] = $case['30_month_filing_deadline'];
                    } else {
                        $cases[$key]['filing_deadline'] = $case['31_month_filing_deadline'];
                    }
                }
                $cases[$key]['regions'] = $this->get_regions_for_case($case['id']);
                $cases[$key]['last_note'] = is_null($cases[$key]['last_note']) ? '&nbsp;' : $cases[$key]['last_note'];
            }
            return $cases;
        }
        return 'There is no records matched to your search request';
    }

    public function get_sent_emails($case_id, $countries_arr)
    {
        $sent_emails = array();
        $countries_arr = array();
        if (is_array($countries_arr) && count($countries_arr)) {
            $this->db->where('case_id', $case_id);
            $this->db->where('email_type', 'notification');
            $this->db->where_in('country_id', $countries_arr);
            $query = $this->db->get('sent_emails');
            if ($query->num_rows()) {
                foreach ($query->result_array() as $email) {
                    $sent_emails[] = $email['country_id'];
                }
            }
        }
        return $sent_emails;
    }

    /**
     * Returns a list of BDV
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     * */
    public function get_sales_managers()
    {
        $this->db->where('type', 'sales');
        $query = $this->db->get('managers');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Sets "completed" status for case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    function complete_case($case_number)
    {
        $data = array(
            'common_status' => 'completed'
        );
        $this->db->where('case_number', $case_number);
        $this->db->update('cases', $data);
    }

    /**
     * Sets fee level for case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @return    void
     * */
    public function set_fee_level($case_id = '')
    {
        $fee_level = $this->input->post('fee_level');
        $data = array(
            'estimate_fee_level' => $fee_level
        );
        $this->db->where('id', $case_id);
        $this->db->update('cases', $data);
    }

    public function parse_wipo_new($case_number = '')
    {
        $this->load->model('wipo_scraper_model', 'wipo_scraper');
        $wo_number = $this->input->post('wo_number');
        $wipo_number = !empty($wo_number) ? $this->input->post('wo_number') : $this->input->post('application_number');
        return $this->wipo_scraper->parse_entry($wipo_number);
    }

    /**
     * Sends notification email (from filing confirmation tab)
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    string
     * */
    public function send_notification_email()
    {
        $ci = & get_instance();
        $ci->load->model('files_model', 'files');
        $ci->load->model('associates_model', 'associates');
        $ci->load->model('countries_model', 'countries');
        $ci->load->model('send_emails_model', 'send_emails');
        $this->load->library('zip');
        $result = array('type' => 'error', 'text' => 'some error with sending');

        $case_number = $this->input->post('case_number');
        $to = $this->input->post('to');
        $cc = $this->input->post('cc');
        $subject = $this->input->post('subject');
        $text = $this->input->post('text', FALSE);
       // $zip_hash = $this->input->post('zip_hash');
        $country_id = $this->input->post('country_id');
        $email_type = 'filing-confirmation';
        $case_files = '';
        $zip_hash = md5(date('Y-m-d H:i:s'));
        if (!is_null($customer = $this->get_customer_by_case_number($case_number))) {
            if (!is_null($case = $this->find_case_by_number($case_number))) {
                $from = 'case' . $case['case_number'] . $this->config->item('default_email_box');

                if (TEST_MODE) {
                    $to = TEST_CLIENT_EMAIL;
                    if ($customer['type'] == 'firm') {
                        $to = TEST_FIRM_EMAIL;
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
                    } else {
                        $send_cc = explode(';', $cc);
                    }
                }
                // Attach all files related to current country
                if (!is_null($country = $ci->countries->get_country($country_id))) {
                    if (file_exists('uploads/tmp/' . $country['country'] . '.zip')) {
                        @unlink('uploads/tmp/' . $country['country'] . '.zip');
                    }
                    if (!is_null($country_files = $this->get_case_files_by_country($case['id'], $country_id, array(6)))) {
                        foreach ($country_files as $file) {
                            $this->zip->read_file('../pm/' . $file['location']);
                        }
                            $case_files = '<a href="https://' . $_SERVER['SERVER_NAME'] . '/zip/' . $zip_hash . '">files</a>';
                    }
                    $this->zip->archive('uploads/tmp/' . $country['country'] . '.zip');

                    // Move zip file to user's folder
                    $new_path = 'uploads/' . $case['user_id'] . '/' . $case['case_number'] . '/' . $country['country'] . '_' . date('YmdHis') . '.zip';
                    if (@rename('uploads/tmp/' . $country['country'] . '.zip', $new_path)) {
                        $ci->files->add_zipped_file($case['id'], $new_path, $zip_hash, $email_type, $country_id);
                    }
                }
                if (file_exists('uploads/tmp/' . $country['country'] . '.zip')) {
                }
                // Set status "Filing receipt sent to client"

                $this->save_tracker($case['id'], $country_id, 'fr_sent', date('Y-m-d H:i:s'));
                $text = str_replace('%CASE_FILES%', $case_files, $text);
                if ($ci->send_emails->send_email($from, $from, $subject, $text, $to, $send_cc, false)) {
                    // Insert data about sent email to database
                    $manager_id = $this->session->userdata('manager_user_id');
                    $data = array(
                        'case_id' => $case['id'],
                        'manager_id' => $manager_id,
                        'country_id' => $country_id,
                        'email_type' => 'notification',
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    $this->db->insert('sent_emails', $data);

                    //
                    $status_id = 14;
                    if ($country_id == 'all') {
                        $countries = $this->get_case_countries($case_number);
                        foreach ($countries as $country) {
                            $this->save_tracker($case['id'], $country['id'], 'fr_sent', date('Y-m-d H:i:s', time()));
                        }
                    } else {
                        $this->save_tracker($case['id'], $country_id, 'fr_sent', date('Y-m-d H:i:s'));
                    }
                    $result['type'] = 'information';
                    $result['text'] = 'Email has been sent!';
                } else {
                    $result['type'] = 'error';
                    $result['text'] = 'Email hasn\'t been sent!';
                }
            }
        }
        return json_encode($result);
    }

    /**
     * Returns a list of footnotes for estimate
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @return    mixed
     * */
    public function get_estimate_footnotes($case_id = '')
    {
        $this->db->where('case_id', $case_id);
        $this->db->order_by('country_id');
        $query = $this->db->get('estimates_footnotes');
        if ($query->num_rows()) {
            $result = array();

            foreach ($query->result_array() as $footnote) {
                $result[$footnote['country_id']][] = $footnote;
            }
            return $result;
        }
        return NULL;
    }

    /**
     * Returns a list of countries assigned to user
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    user ID
     * @param    int    case type ID
     * @return    mixed
     * */
    public function get_customer_countries($user_id, $case_type_id = 1)
    {
        $this->db->select('customers_fees.*, countries.country, countries.currency_code', FALSE);
        $this->db->join('countries', 'countries.id = customers_fees.country_id');
        $this->db->where('user_id', $user_id);
        $this->db->where('case_type_id', $case_type_id);
        $this->db->order_by('countries.country');
        $query = $this->db->get('customers_fees');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns a list of countries for case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    int    user ID
     * @return    mixed
     * */
    public function get_list_estimate_countries($case_id, $user_id)
    {
        $this->db->where('case_id', $case_id);
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('estimates_countries_fees');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    function get_contacts_of_the_case($options = array())
    {
        if (isset($options['case_id'])) {
            $this->db->where('case_id', $options['case_id']);
            $query = $this->db->get('case_contacts');
            return $query->result();
        }
    }

    /**
     * Updates customers fees from main fees table
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    user ID
     * @return    bool
     * */
    public function update_customer_fees($user_id)
    {
        $countries = $this->input->post('customer_countries');
        $case_types = $this->input->post('case_types');
        if (check_array($countries)) {
            $filing_fees_level_1 = $this->input->post('filing_fee_level_1');
            $filing_fees_level_2 = $this->input->post('filing_fee_level_2');
            $filing_fees_level_3 = $this->input->post('filing_fee_level_3');
            $translation_rates_level_1 = $this->input->post('translation_rate_level_1');
            $translation_rates_level_2 = $this->input->post('translation_rate_level_2');
            $translation_rates_level_3 = $this->input->post('translation_rate_level_3');
            $var_index = 0;

            foreach ($countries as $country_id) {
                $data = array(
                    'filing_fee_level_1' => $filing_fees_level_1[$var_index],
                    'filing_fee_level_2' => $filing_fees_level_2[$var_index],
                    'filing_fee_level_3' => $filing_fees_level_3[$var_index],
                    'translation_rate_level_1' => $translation_rates_level_1[$var_index],
                    'translation_rate_level_2' => $translation_rates_level_2[$var_index],
                    'translation_rate_level_3' => $translation_rates_level_3[$var_index],
                );
                $this->db->where('case_type_id', $case_types[$var_index]);
                $this->db->where('user_id', $user_id);
                $this->db->where('country_id', $country_id);
                $this->db->update('customers_fees', $data);
                $var_index++;
            }
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Saves top footnote for case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    bool
     * */
    public function save_top_footnote($case_number)
    {
        if (!is_null($case = $this->find_case_by_number($case_number))) {
            $top_footnote = $this->input->post('top_footnote');
            $data = array(
                'top_footnote' => $top_footnote
            );
            $this->db->where('id', $case['id']);
            $this->db->update('cases', $data);
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Returns a language by code and case type ID
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string    language code
     * @param    int    case type ID
     * @return    string
     * */
    public function get_language_by_code_and_case_type($code_language = 'en', $case_type_id = '1')
    {
        if ($case_type_id == '1') {
            $case_type = 'pct';
        } elseif ($case_type_id == '2') {
            $case_type = 'ep';
        } elseif ($case_type_id == '3') {
            $case_type = 'direct';
        }
        $this->db->select($case_type . '_language as language', FALSE);
        $this->db->where('code', strtoupper(trim($code_language)));
        $query = $this->db->get('countries');
        if ($query->num_rows()) {
            $record = $query->row_array();

            return $record['language'];
        }
        return 'English';
    }

    /**
     * Sets the sign that estimate PDF has been sent
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    string    destination: client, bdv
     * @param    int    value (1 - available, 0 - not available)
     * @return    bool
     * */
    public function set_estimate_pdf_sent($case_id, $destination = 'client', $value)
    {
        if (!$destination) {
            $data = array(
                'estimate_sent_to_client' => "$value",
                'estimate_sent_to_bdv' => "$value",
            );
        } elseif ($destination == 'client') {
            $data = array(
                'estimate_sent_to_client' => "$value",
            );
        } elseif ($destination == 'bdv') {
            $data = array(
                'estimate_sent_to_bdv' => "$value",
            );
        }
        $this->db->where('id', $case_id);
        $this->db->update('cases', $data);
    }

    /**
     * Updates customers fees
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    user ID
     * @return    bool
     * */
    public function reload_customer_fees($user_id = '', $case_type_id = false, $country_id = false)
    {

        if ($case_type_id && $country_id) {
            $case_condition = 'zen_customers_fees.case_type_id =' . $case_type_id . ' AND zen_customers_fees.country_id = ' . $country_id;
        } else {
            $case_condition = 'zen_customers_fees.user_id = ' . $user_id;
        }

        $q = 'UPDATE zen_customers_fees, zen_fees
        SET
		zen_customers_fees.filing_fee_level_1 = zen_fees.filing_fee_level_1,
		zen_customers_fees.filing_fee_level_2 = zen_fees.filing_fee_level_2,
		zen_customers_fees.filing_fee_level_3 = zen_fees.filing_fee_level_3,
		zen_customers_fees.translation_rate_level_1 = zen_fees.translation_rate_level_1,
		zen_customers_fees.translation_rate_level_2 = zen_fees.translation_rate_level_2,
		zen_customers_fees.translation_rate_level_3 = zen_fees.translation_rate_level_3,
	  	zen_customers_fees.official_fee_large = zen_fees.official_fee_large,
        zen_customers_fees.official_fee_small = zen_fees.official_fee_small,
        zen_customers_fees.official_fee_individual = zen_fees.official_fee_individual,
	  	zen_customers_fees.sequence_listing_fee = zen_fees.sequence_listing_fee,
		zen_customers_fees.extension_needed_fee = zen_fees.extension_needed_fee,
		zen_customers_fees.request_examination = zen_fees.request_examination,
		zen_customers_fees.number_claims_above_additional_fees = zen_fees.number_claims_above_additional_fees,
	  	zen_customers_fees.fee_additional_claims = zen_fees.fee_additional_claims,
		zen_customers_fees.additional_fee_for_claims = zen_fees.additional_fee_for_claims,
		zen_customers_fees.number_pages_above_additional_fees = zen_fees.number_pages_above_additional_fees,
		zen_customers_fees.fee_additional_pages = zen_fees.fee_additional_pages,
		zen_customers_fees.number_priorities_claimed_with_no_additional_charge = zen_fees.number_priorities_claimed_with_no_additional_charge,
		zen_customers_fees.charge_per_additional_priority_claimed = zen_fees.charge_per_additional_claimed,
		zen_customers_fees.charge_per_additional_claimed = zen_fees.charge_per_additional_claimed,
		zen_customers_fees.number_free_pages_drawing = zen_fees.number_free_pages_drawing,
		zen_customers_fees.charge_per_additional_pages_of_drawing = zen_fees.charge_per_additional_pages_of_drawing,
		zen_customers_fees.claim_number_threshold_for_additional_fee = zen_fees.claim_number_threshold_for_additional_fee,
		zen_customers_fees.page_number_treshold_for_additional_fee = zen_fees.page_number_treshold_for_additional_fee,
		zen_customers_fees.translation_rates_for_claims = zen_fees.translation_rates_for_claims,
		zen_customers_fees.additional_fee_above_treshold = zen_fees.additional_fee_above_treshold
        WHERE (zen_fees.case_type_id = zen_customers_fees.case_type_id) AND (zen_customers_fees.country_id = zen_fees.country_id) AND (' . $case_condition . ')

';

        $this->db->query($q);

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Changes case status
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    string    status
     * @return    bool
     * */
    public function change_case_status($case_id = '', $status = '')
    {
        $data = array(
            'common_status' => $status
        );
        $this->db->where('id', $case_id);
        $this->db->update('cases', $data);

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Marks case to be highlighted
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    bool
     * */
    public function unhighlight($case_number = '')
    {
        $data = array(
            'highlight' => "0"
        );
        $this->db->where('case_number', $case_number);
        $this->db->update('cases', $data);

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Returns a list of approved countries for case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @return    mixed
     * */
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

    public function get_direct_related_cases($case_id = '')
    {
        $q = 'SELECT DISTINCT c.id, c.case_number
		      FROM `zen_cases` c, `zen_related_cases` rc
			  WHERE (c.id = rc.child_case_id) AND
			  		(rc.parent_case_id = ' . (int)$case_id . ') AND c.common_status  NOT IN("draft")';

        $query = $this->db->query($q);
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    public function is_related_case($case_id)
    {
        $this->db->where('child_case_id', $case_id);
        $query = $this->db->get('related_cases');
        return $query->result();
    }

    public function get_my_case_by_number($number)
    {
        $this->db->where('case_number', $number);
        $query = $this->db->get('cases');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return false;
    }

    public function is_exist_case_number($case_number = '')
    {
        $client_id = $this->session->userdata('client_user_id');

        $this->db->select('cases.case_number');
        $this->db->where('cases.case_number', $case_number);

        $query = $this->db->get('cases');
        if ($query->num_rows()) {
            $row = $query->row_array();
            return $row['case_number'];
        }
        return NULL;
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
    public function sendConfEmailToClient($case_number = "", $tpl_num = 0, $country_id = "")
    {
        $this->load->model('send_emails_model', 'send_emails');
        $this->load->model('customers_model', 'customers');
        $CASE = $this->find_case_by_number($case_number, 'noclient');
        $USER = $this->get_customer_by_case_number($case_number); /* return row */

        if (!empty($CASE)) {
            $case_manager = '';
            $this->db->where('id', $CASE["id"]);
            $query = $this->db->get('cases');
            $case_manager_temp = $query->row_array();
            if ($case_manager_temp) {
                $this->db->where('id', $case_manager_temp['manager_id']);
                $query = $this->db->get('managers');
                if ($query->num_rows()) {
                    $temp_data = $query->row_array();
                    $case_manager = $temp_data['firstname'] . ' ' . $temp_data['lastname'];
                }

            }
        }
        if (!empty($USER) && !empty($CASE)) {
            if ($USER['allow_email'] == 'yes') {
                $from = 'case' . $CASE['case_number'] . $this->config->item('default_email_box');
                $CONTACTS = $this->customers->get_case_contacts($CASE["id"]);
                $cc = '';
                if (!empty($CONTACTS)) {
                    foreach ($CONTACTS as $contact) {
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
                /*             * ****************************************** GET EMAIL TEMPLATE **************************************** */
                $TEMPLATE = $this->db->get_where("zen_emails_templates", array("id" => $tpl_num))->row_array();
                if (!empty($TEMPLATE)) {

                    /*                 * ***************************************** GET CASE TYPE ********************************************* */
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

                    /*                 * *************************************** GENERATING CASE COUNTRIES *********************************** */
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
                            foreach ($CASE_COUNTRIES_RAW as $country) {
                                $CASE_COUNTRIES[] = $country["country"];
                            }
                            $CASE_COUNTRIES = implode(",", $CASE_COUNTRIES);
                        }
                    }

                    /*                 * **************************************** GENERATING CASE FILES *************************************** */
                    $CASE_FILES = "";
                    if (
                        (strpos($TEMPLATE["subject"], "%CASE_FILES%") != false)
                        ||
                        (strpos($TEMPLATE["content"], "%CASE_FILES%") != false)
                    ) {
                        $CF = $this->db->get_where("zen_cases_files", array("case_id" => $CASE["id"]))->result_array();
                        if (!empty($CF)) {
                            foreach ($CF as $file) {
                                $CASE_FILES[] = "<a href='" . site_url($file["location"]) . "' alt='" . $file["filename"] . "'>" . $file["filename"] . "</a>";
                            }
                            $CASE_FILES = implode(",", $CASE_FILES);
                        }
                    }

                    /*                 * *************************************************** GET BDV NAME ************************************* */
                    $BDV_NAME = "";
                    if (
                        (strpos($TEMPLATE["subject"], "%BDV_NAME%") != false)
                        ||
                        (strpos($TEMPLATE["content"], "%BDV_NAME%") != false)
                    ) {
                        if (empty($USER["bdv_id"])) {
                            $BDV_NAME = "";
                        } else {
                            $BDV_NAME = $this->db->get_where("zen_managers", array("id" => $USER["bdv_id"]))->row_array();
                            if (!empty($BDV_NAME))
                                $BDV_NAME = $BDV_NAME["firstname"] . " " . $BDV_NAME["lastname"];
                            else
                                $BDV_NAME = "";
                        }
                    }
                    /*                 * ****************************************************************************************************** */

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
                    $TEMPLATE["subject"] = str_replace('%CASE_MANAGER%', $case_manager, $TEMPLATE["subject"]);
                    if ($this->send_emails->send_email($from, $from, $TEMPLATE["subject"], $TEMPLATE["content"], $to, $cc, false)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function save_tracker($case_id, $country_id, $action, $value)
    {
        if ($value == 'current_date')
            $value = date('Y-m-d H:i:s', time());
        $data = array(
            'case_id' => $case_id,
            'country_id' => $country_id,
            $action => $value,
        );

        if ($tracker = $this->get_tracker($case_id, $country_id)) {
            if ($this->update_tracker($data)) {

                if ($value)
                    return $value;
                else
                    return true;
            }
        } else {
            if ($this->create_tracker($data)) {
                if ($value)
                    return $value;
                else
                    return true;
            }
        }

        return false;
    }

    public function get_tracker($case_id, $country_id)
    {
        $this->db->where('case_id', $case_id);
        $this->db->where('country_id', $country_id);
        $query = $this->db->get('cases_tracker');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return false;
    }

    public function create_tracker($data)
    {
        $this->db->where('case_id', $data['case_id']);
        $this->db->where('country_id', $data['country_id']);
        return $this->db->insert('cases_tracker', $data);
    }

    public function update_tracker($data)
    {
        $this->db->where('case_id', $data['case_id']);
        $this->db->where('country_id', $data['country_id']);
        return $this->db->update('cases_tracker', $data);
    }

    public function create_zip($case_number, $country_id = '')
    {

        ini_set('memory_limit', '256M');
        $ci = & get_instance();
        $ci->load->model('files_model', 'files');
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
                            foreach ($country_files as $file) {
                                @copy('../pm/' . $file['location'], $country_dir . $file['filename']);
                            }
                        }
                        $this->zip->read_dir($country_dir, FALSE);
                    }
                } else {
                    if (!is_null($countries = $this->countries->get_case_countries($case_id[0]->id))) {
                        foreach ($countries as $country) {
                            $country_dir = 'uploads/tmp/' . $case_number . '/' . $country['country'] . '/';
                            if (file_exists($country_dir)) {
                                @rmdir($country_dir);
                                @rrmdir($country_dir);
                            }
                            if (@mkdir($country_dir, 0777)) {
                                // 5,6 - Filing Receipt and Filing Report
                                if (!is_null($country_files = $this->get_case_files_with_country_array($case_id[0]->id, $file_types = array(6), $country['id']))) {
                                    foreach ($country_files as $file) {
                                        @copy('../pm/' . $file['location'], $country_dir . $file['filename']);
                                    }
                                }
                                $this->zip->read_dir($country_dir, FALSE);
                            }
                        }
                    }
                }

                $this->zip->archive('uploads/tmp/' . $case_number . '.zip');
                if ($country_id) {
                    return $onecountry[0]->country;
                } else {
                    return 'all countries';
                }
                return TRUE;
            }

        }
        return FALSE;
    }

    function get_match_with_excistant_case_by_pct_wo($case_id, $wipo_wo_number, $wipo_pct_number)
    {

        $this->db->where('wipo_wo_number', $wipo_wo_number);
        $this->db->where('wipo_pct_number', $wipo_pct_number);
        $this->db->order_by('id', 'desc');
        $this->db->where('id <', $case_id);
        $query = $this->db->get('cases');

        return $query->row();

    }

    function get_match_with_excistant_case_by_application_number($case_id, $application_number)
    {

        $this->db->where('application_number', $application_number);
        $this->db->order_by('id', 'desc');
        $this->db->where('id <', $case_id);
        $query = $this->db->get('cases');
        return $query->row();
    }


    function prepare_number_for_parser($number_for_parsing)
    {

        $elements = explode('/', $number_for_parsing);

        if (count($elements) != 3) {

            return false;

        }
        $is_wo = preg_match('/[wW][oO].*/', $number_for_parsing);


        foreach ($elements as $key => $element) {

            $elements[$key] = trim(strtoupper($element));

        }

        if ($is_wo) {

            if (strlen($elements[1]) == 2) {

                $elements[1] = '20' . $elements[1];

            }

        }

        if (strlen($elements[2]) < 6) {

            $zeros_to_add = 6 - strlen($elements[2]);

            $string_to_add = '';

            for ($i = 0; $i < $zeros_to_add; $i++) {

                $string_to_add .= '0';

            }

            $elements[2] = $string_to_add . $elements[2];

        }

        $ready_number = implode('/', $elements);

        return $ready_number;

    }

    function update_case_basic($options = array())
    {
        if (!empty($options['case_id'])) {
            $this->db->where('case_id', $options['case_id']);
        } elseif (!empty($options['case_number'])) {
            $this->db->where('case_number', $options['case_number']);
        } else {
            return false;
        }

        $this->db->update('cases', $options);
        return $this->db->affected_rows();
    }

    /**
     * Complete remove case including all data within database
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    bool
     */

    public function set_case_inactive($case_id)
    {
        // 13. Set draft status for case
        $this->db->set('is_active', '0');
        $this->db->set('last_update', date('Y-m-d H:i:s'));
        $this->db->where('id', $case_id);
        $this->db->update('cases');
    }

    public function assign_client_to_case($case, $user_id)
    {
        $this->load->model('customers_model', 'customers');
        $customer = $this->customers->get_user($user_id, 'customer');
        $this->db->set('manager_id', $customer['manager_id']);
        $this->db->set('sales_manager_id', $customer['bdv_id']);
        $this->db->set('customer_firstname', $customer['firstname']);
        $this->db->set('customer_lastname', $customer['lastname']);
        $this->db->set('customer_email', $customer['email']);
        $this->db->set('customer_company_name', $customer['company_name']);
        $this->db->set('customer_address', $customer['address']);
        $this->db->set('customer_address2', $customer['address2']);
        $this->db->set('customer_city', $customer['city']);
        $this->db->set('customer_state', $customer['state']);
        $this->db->set('customer_zip_code', $customer['zip_code']);
        $this->db->set('customer_country', $customer['country']);
        $this->db->set('customer_phone_number', $customer['phone_number']);
        $this->db->set('customer_ext', $customer['ext']);
        $this->db->set('customer_fax', $customer['fax']);
        $this->db->set('user_id', $user_id);
        $this->db->where('id', $case['id']);
        $this->db->update('cases');
        $this->db->where('case_id', $case['id']);
        $query = $this->db->get('cases_files');
        if ($query->num_rows()) {
            $previous_user_files = $query->result_array();
        }

        if (!file_exists('../pm/uploads/' . $user_id)) {
            mkdir('../pm/uploads/' . $user_id, 0755);
        }
        if (!file_exists('../pm/uploads/' . $user_id . '/' . $case['case_number'])) {
            mkdir('../pm/uploads/' . $user_id . '/' . $case['case_number'], 0755);
        }
        if (isset($previous_user_files)) {
            foreach ($previous_user_files as $file) {
                $this->db->set('user_id', $user_id);
                $this->db->set('location', 'uploads/' . $user_id . '/' . $case['case_number'] . '/' . $file['filename']);
                $this->db->where('case_id', $case['id']);
                $this->db->where('id', $file['id']);
                $this->db->update('cases_files');
                @copy('../pm/' . $file['location'], '../pm/uploads/' . $user_id . '/' . $case['case_number'] . '/' . $file['filename']);
                $temp_path = explode('/', $file['location']);
                if ($temp_path['1'] = 'tmp') {
                    @unlink('../pm/' . $file['location']);
                } else {
                    @rrmdir('../pm/uploads/' . $user_id . '/' . $case['case_number'] . '/');
                }
            }
        }

        $this->db->set('user_id', $user_id);
        $this->db->where('case_id', $case['id']);
        $this->db->update('estimates_countries_fees');

    }


    public function add_country_to_tracker($case_number, $array_of_country_id)
    {
        $case = $this->find_case_by_number($case_number);
        foreach ($array_of_country_id as $country_tracker)
            $new_countries[] = array(
                'case_id' => $case['id'],
                'country_id' => $country_tracker
            );
        $this->db->insert_batch('cases_tracker', $new_countries);
    }
}