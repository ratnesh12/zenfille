<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Estimates_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns a list of commont footnotes
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixedd
     * */
    public function get_common_footnotes()
    {
        $query = $this->db->get('common_footnotes');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    public function get_common_footnote($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('common_footnotes');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    public function delete_footnote($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('common_footnotes');
    }

    public function update_footnote($action)
    {

        $this->db->set('text', trim($this->input->post('text')));
        if ($action == 'Edit') {
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('common_footnotes');
        } else {
            $this->db->insert('common_footnotes');
        }
    }

    /**
     * Returns a list of estimate footnotes
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @return    mixed
     * */
    public function get_estimate_footnotes($case_id = '')
    {
        $this->db->order_by('country_id');
        $this->db->where('case_id', $case_id);
        $query = $this->db->get('estimates_footnotes');
        if ($query->num_rows()) {
            $result = array();
            foreach ($query->result_array() as $record) {
                $result[$record['country_id'] . '-' . $record['fee_type']] = $record;
            }
            return $result;
        }
        return NULL;
    }

    /*
	* Returns an estimate data by case number
	*
	* @access	public
	* @param	int
	* @return 	mixed
	*/
    public function find_estimate_by_case_number($case_number = '')
    {
        $this->db->select('cases.*, case_types.type as case_type', FALSE);
        $this->db->join('case_types', 'case_types.id = cases.case_type_id');
        $this->db->where('is_intake', '0');
        $this->db->where('case_number', $case_number);
        $query = $this->db->get('cases');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    function update_case_countries($options = array()) {
        if (!empty($options['id'])) {
            $this->db->where('id' , $options['id']);
        } elseif (!empty($options['case_id']) && !empty($options['country_id'])) {
            $this->db->where('case_id' , $options['case_id']);
            $this->db->where('country_id' , $options['country_id']);
        } else {
            return false;
        }

        $this->db->update('case_countries' , $options);
    }

    function update_associates_data($options = array()) {


        if(empty($options['id'])) {
            return false;
        }

        $this->db->where('id' , $options['id']);

        $this->db->update('cases_associates_data' , $options);
        return $this->db->affected_rows();
    }

    function delete_cases_associates_data($options = array()) {
        if (empty($options['additional_fee_id'])) {
            return false;
        }
        $this->db->where('additional_fee_id' , $options['additional_fee_id']);
        $this->db->delete('case_country_additional_fees_for_invoice');
    }

    function get_cases_associates_data($options = array()) {
        $this->db->select("
            cases_associates_data.* , filename , location , fee
        ");
        if (isset($options['country_id'])) {
            $this->db->where('cases_associates_data.country_id' , $options['country_id']);
        }
        if (isset($options['case_id'])) {
            $this->db->where('cases_associates_data.case_id' , $options['case_id']);
        }

        if (isset($options['associate_id'])) {
            $this->db->where('cases_associates_data.associate_id' , $options['associate_id']);
        }

        if (isset($options['id'])) {
            $this->db->where('cases_associates_data.id' , $options['id']);
        }

        $this->db->join('cases_files' , 'cases_files.id = cases_associates_data.fa_invoice_file_id' , 'left');
        $this->db->join('associates' , 'associates.id = cases_associates_data.associate_id');

        $query = $this->db->get('zen_cases_associates_data');

        if ((isset($options['country_id']) && isset($options['case_id']) && isset($options['associate_id'])) || isset($options['id'])) {
            return $query->row();
        } else {
            return $query->result();
        }
    }

    function insert_case_country_additional_fees_for_invoice($options = array()) {
        $this->db->insert('zen_case_country_additional_fees_for_invoice' , $options);
        return $this->db->insert_id();
    }

    function update_case_country_additional_fees_for_invoice($options = array()) {
        if (empty($options['additional_fee_id'])) {
            return false;
        }
        $this->db->where('additional_fee_id' , $options['additional_fee_id']);
        $this->db->update('case_country_additional_fees_for_invoice' , $options);
        return $this->db->affected_rows();
    }

    function get_case_country_additional_fees_for_invoice($options = array()) {
        if(isset($options['cases_associates_data_id'])) {
            $this->db->where('cases_associates_data_id' , $options['cases_associates_data_id']);
        }

        if(isset($options['additional_fee_id'])) {
            $this->db->where('additional_fee_id' , $options['additional_fee_id']);
        }

        $query = $this->db->get('case_country_additional_fees_for_invoice');
        if (isset($options['additional_fee_id'])) {
            return $query->row();
        }
        return $query->result();
    }


    // function needed for refreshing fees of case
    function delete_estimate_country_records_of_case($case_id) {

        $this->db->where('case_id' , $case_id);
        $this->db->delete('estimates_countries_fees');

    }

    /**
     * Saves estimate prices:
     * - saves case data
     * - saves footnotes
     * - transllation fees, filing fee, official fee fees
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    public function save_estimate_prices($case_number)
    {
        $ci = &get_instance();
        $ci->load->model('cases_model', 'cases');
        $ci->load->model('currencies_model', 'currencies');

        // Get case data
        if (!is_null($case = $ci->cases->find_case_by_number($case_number))) {
            // 0. Save data for case record
            $estimate_fee_level = $this->input->post('estimate_fee_level');
            $inflate_official_fee = $this->input->post('inflate_official_fee');
            $claims = $this->input->post('claims');
            $pages = $this->input->post('pages');
            $number_words = $this->input->post('number_words');
            $number_words_in_claims = $this->input->post('number_words_in_claims');
            $pages_sequence_listing = $this->input->post('pages_sequence_listing');
            $search_report_location = $this->input->post('search_report_location');
            $estimate_currency = $this->input->post('estimate_currency');
            $entity = $this->input->post('entity');
            $number_priorities_claimed = $this->input->post('number_priorities_claimed');
            $number_pages_drawings = $this->input->post('number_pages_drawings');

            $data = array(
                'estimate_fee_level' => $estimate_fee_level,
                'estimate_inflate_official_fee' => $inflate_official_fee,
                'number_claims' => $claims,
                'number_pages' => $pages,
                'number_words' => $number_words,
                'number_words_in_claims' => $number_words_in_claims,
                'number_pages_sequence' => $pages_sequence_listing,
                'search_location' => $search_report_location,
                'entity' => $entity,
                'case_currency' => $estimate_currency,
                'number_priorities_claimed' => $number_priorities_claimed,
                'number_pages_drawings' => $number_pages_drawings,
                'estimate_saved_by_pm' => date('Y-m-d H:i:s')
            );
            $this->db->where('id', $case['id']);
            $this->db->update('cases', $data);

            // 1. Delete all previous records by case id and by fee level

            // 1.1. Save "is_approved" values in temp array
            $approved_values = array();
            $this->db->where('case_id', $case['id']);
            $query = $this->db->get('estimates_countries_fees');
            if ($query->num_rows()) {
                foreach ($query->result_array() as $row) {
                    $approved_values[$row['country_id']] = $row['is_approved'];
                }
            }
//var_dump($approved_values);exit;
            // 2. Save footnotes
            // 2.1. Delete previous footnotes related to current case
            $this->db->where('case_id', $case['id']);
            $this->db->delete('estimates_footnotes');

            $footnotes = $this->input->post('footnotes');
            if (check_array($footnotes)) {
                $country_id = $this->input->post('country_id');
                $fee_type = $this->input->post('fee_type');
                $data = array();
                $order = 1;

                foreach ($footnotes as $key => $footnote) {
                    $data[] = array(
                        'case_id' => $case['id'],
                        'footnote' => $footnote,
                        'fee_type' => $fee_type[$key],
                        'country_id' => $country_id[$key],
                        'order' => $order
                    );
                    $order++;
                }

                if (count($data) > 0) {
                    $this->db->insert_batch('estimates_footnotes', $data);
                }
                unset($data);
            }
            // 3. Fees
            $country_filing_deadline = $this->input->post('country_filing_deadline');
            $countries = $this->input->post('country_estimate_id');
            $additional_charges = $this->input->post('additional_charges');
            $initial_official_fees = $this->input->post('initial_official_fees');

            // disabling this statement
            if (check_array($countries)) {
                $index = 0;
                $level = $this->input->post('estimate_fee_level');
                $data = array();
                $fee_types = array('official', 'translation', 'filing');
                foreach ($countries as $country) {

                    foreach ($fee_types as $fee_type) {
                        $is_locked = $this->input->post('locked_' . $country . '_' . $fee_type);
                        $var_name = 'locked_' . $fee_type;
                        $$var_name = "0";
                        if (!empty($is_locked) && ($is_locked != '0')) {
                            $$var_name = "1";
                        }
                    }
                    $filing_fee = $this->input->post('filing_fee_' . $country);
                    $official_fee = $this->input->post('official_fee_' . $country);
                    $translation_fee = $this->input->post('translation_fee_' . $country);
                    if ($estimate_currency == 'euro') {
                        $euro_exchange_rate = $ci->currencies->get_currency_rate_by_code('EUR');
                        $filing_fee = ceil($filing_fee / $euro_exchange_rate);
                        $official_fee = ceil($official_fee / $euro_exchange_rate);
                        $translation_fee = ceil($translation_fee / $euro_exchange_rate);
                    }
                    $is_approved = (isset($approved_values[$country])) ? $approved_values[$country] : '0';
                    $country_filing_deadline_val = NULL;
                    $raw_country_filing_deadline = $country_filing_deadline[$index];

                    if (!empty($raw_country_filing_deadline)) {
                        $fd_temp = new DateTime($raw_country_filing_deadline);
                        $country_filing_deadline_val = $fd_temp->format('Y-m-d');
                    }

                    $data = array(
//                        'user_id' => $case['user_id'],
//                        'case_id' => $case['id'],
//                        'country_id' => $country,
//                        'filing_fee' => $filing_fee,
//                        'official_fee_' . $entity => floatval($initial_official_fees[$index]),
//                        'translation_fee' => $translation_fee,
//                        'level' => "$level",
//                        'is_approved' => "$is_approved",
//                        'is_approved_by_pm' => '1',
//                        'additional_charges' => floatval($additional_charges[$index]),
//                        'initial_official_fee_' . $entity => floatval($initial_official_fees[$index]),
                        'country_filing_deadline' => $country_filing_deadline_val
                    );

                    $this->db->where('id', $country);
                    $this->db->where('parent_id' , 0);
                    $this->db->update('estimates_countries_fees', $data);
//                    echo $this->db->last_query() . '<br/>';
                    $index++;
                }
//                exit;
            }
        }
    }

    public function delete_country_from_estimate($case_number)
    {
        $ci = &get_instance();
        $ci->load->model('cases_model', 'cases');

        if (!is_null($case = $ci->cases->find_case_by_number($case_number))) {
            // Country ID here!
            $record_id = $this->input->post('country_record_id');

            $this->db->where('case_id', $case['id']);
            $this->db->where('user_id', $case['user_id']);
            $this->db->where('country_id', $record_id);
            $this->db->delete('estimates_countries_fees');

            $this->db->flush_cache();

            $ci->cases->remove_country_from_case($record_id, $case_number);
        }
    }

    /**
     * Adds country to estimate
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @param    int    country ID
     * @return    void
     * */
    public function add_country_to_estimate($case_number = '', $newest_estimate_country_id = '')
    {
        $ci = &get_instance();
        $ci->load->model('cases_model', 'cases');


        // Get case data
        if (!is_null($case = $ci->cases->find_case_by_number($case_number))) {
            // Case countries
            $case_countries_arr = array();
            $case_countries = $ci->cases->get_case_countries($case_number);
            if (check_array($case_countries)) {
                foreach ($case_countries as $item) {
                    $case_countries_arr[] = $item['id'];
                }
                unset($item);
            }
            $translation_level = $filing_level = $case['estimate_fee_level'];
            if (empty($case['estimate_fee_level'])) {
                $translation_level = $filing_level = 1;
                $case['estimate_fee_level'] = 1;
            }
            // 1. Add country to case countries
            // If there are no records related to new ones then add them
            $new_estimate_country_id = $this->input->post('new_estimate_country_id');
            if (empty($new_estimate_country_id)) {
                $new_estimate_country_id = $newest_estimate_country_id;
            }
            $data = array();
            if (strpos($new_estimate_country_id, ',') !== FALSE) {
                $ids_array = explode(',', $new_estimate_country_id);
                foreach ($ids_array as $item) {

                    $this->db->where('case_id', $case['id']);
                    $this->db->where('country_id', $item);
                    $query = $this->db->get('cases_countries');
                    if (!$query->num_rows()) {
                        if (!in_array($item, $case_countries_arr)) {
                            $data[] = array(
                                'case_id' => $case['id'],
                                'added_by_pm' => '1',
                                'country_id' => $item
                            );
                        }
                    }
                }
                if (check_array($data)) {
                    $this->db->insert_batch('cases_countries', $data);
                }
            } else {
                $this->db->where('case_id', $case['id']);
                $this->db->where('country_id', $new_estimate_country_id);
                $query = $this->db->get('cases_countries');
                if (!$query->num_rows()) {
                    $data = array(
                        'case_id' => $case['id'],
                        'added_by_pm' => '1',
                        'country_id' => $new_estimate_country_id
                    );

                    $this->db->insert('cases_countries', $data);
                }
            }

            // 2. Add country to estimate table
            if (strpos($new_estimate_country_id, ',') !== FALSE) {
                $temp_array = explode(',', $new_estimate_country_id);
                $this->db->where_in('country_id', $temp_array);
            } else {
                $this->db->where('country_id', $new_estimate_country_id);
            }
            $this->db->where('user_id', $case['user_id']);
            $this->db->where('case_type_id', $case['case_type_id']);
            $query = $this->db->get('customers_fees');

                // Get the record from master fee schedule table (zen_fees)
                if (strpos($new_estimate_country_id, ',') !== FALSE) {
                    $temp_array = explode(',', $new_estimate_country_id);
                    $this->db->where_in('country_id', $temp_array);
                } else {
                    $this->db->where('country_id', $new_estimate_country_id);
                }
                $this->db->where('case_type_id', $case['case_type_id']);
                $query = $this->db->get('fees');
                if ($query->num_rows()) {
                    $master_fee_record = $query->row_array();

                        $data = array(
                            'user_id' => $case['user_id'],
                            'country_id' => $new_estimate_country_id,
                            'case_type_id' => $master_fee_record['case_type_id'],
                            'filing_fee_level_1' => $master_fee_record['filing_fee_level_1'],
                            'filing_fee_level_2' => $master_fee_record['filing_fee_level_2'],
                            'filing_fee_level_3' => $master_fee_record['filing_fee_level_3'],
                            'translation_rate_level_1' => $master_fee_record['translation_rate_level_1'],
                            'translation_rate_level_2' => $master_fee_record['translation_rate_level_2'],
                            'translation_rate_level_3' => $master_fee_record['translation_rate_level_3'],
                            'official_fee_large' => $master_fee_record['official_fee_large'],
                            'official_fee_small' => $master_fee_record['official_fee_small'],
                            'official_fee_individual' => $master_fee_record['official_fee_individual'],
                            'sequence_listing_fee' => $master_fee_record['sequence_listing_fee'],
                            'extension_needed_fee' => $master_fee_record['extension_needed_fee'],
                            'request_examination' => $master_fee_record['request_examination'],
                            'number_claims_above_additional_fees' => $master_fee_record['number_claims_above_additional_fees'],
                            'fee_additional_claims' => $master_fee_record['fee_additional_claims'],
                            'additional_fee_for_claims' => $master_fee_record['additional_fee_for_claims'],
                            'number_pages_above_additional_fees' => $master_fee_record['number_pages_above_additional_fees'],
                            'fee_additional_pages' => $master_fee_record['fee_additional_pages'],
                            'number_priorities_claimed_with_no_additional_charge' => $master_fee_record['number_priorities_claimed_with_no_additional_charge'],
                            'charge_per_additional_claimed' => $master_fee_record['charge_per_additional_claimed'],
                            'number_free_pages_drawing' => $master_fee_record['number_free_pages_drawing'],
                            'charge_per_additional_pages_of_drawing' => $master_fee_record['charge_per_additional_pages_of_drawing'],
                            'claim_number_threshold_for_additional_fee' => $master_fee_record['claim_number_threshold_for_additional_fee'],
                            'page_number_treshold_for_additional_fee' => $master_fee_record['page_number_treshold_for_additional_fee'],
                            'additional_fee_above_treshold' => $master_fee_record['additional_fee_above_treshold'],
                        );
                        $this->db->where('user_id', $case['user_id']);
                        $this->db->where('country_id', $new_estimate_country_id);
                        $this->db->where('case_type_id', $master_fee_record['case_type_id']);
                        $query = $this->db->get('customers_fees');
                        if (!$query->num_rows()) {
                            $this->db->insert('customers_fees', $data);
                        }

                    $this->db->flush_cache();

                    // Add values to estimates table
                }

            $this->add_fees_entries($case['user_id'], $case_number, $case['estimate_fee_level']);
        }
    }

    public function add_fees_entries($user_id, $case_number, $fee_level, $added_by_client = false)
    {
        $ci = & get_instance();
        $ci->load->model('currencies_model', 'currencies');
        // If there are no countries in the table then insert them

        $ci = &get_instance();
        $ci->load->model('cases_model', 'cases');
        $case = $ci->cases->find_case_by_number($case_number);

        if (!is_null($case)) {
            $countries = $ci->cases->get_case_countries($case_number);

            if (!is_null($countries)) {
                if (check_array($countries)) {
                    foreach ($countries as $country) {

                        $data = array(
                            'user_id' => $user_id,
                            'case_id' => $case['id'],
                            'country_id' => $country['id'],
                            'translation_fee' => '0.00',
                            'filing_fee' => '0.00',
                            'official_fee_large' => '0.00',
                            'official_fee_small' => '0.00',
                            'official_fee_individual' => '0.00',
                            'added_originally' => '1',
                        );

                        $case_country_data = array(
                            'user_id' => $user_id,
                            'country_id' => $country['id'],
                            'case_type_id' => $case['case_type_id']
                        );
                        $fee_record = NULL;
                        // Check is there this record in DB or not
                        $query = $this->db->get_where('customers_fees', $case_country_data);

                        if (!$query->num_rows()) {
                            // If there is no related record in "customer_fees" table then get them from "fees" table
                            $this->db->flush_cache();
                            $this->db->where('country_id', $country['id']);
                            $this->db->where('case_type_id', $case['case_type_id']);
                            $query = $this->db->get('fees');

                            if ($query->num_rows()) {
                                $fee_record = $query->row_array();
                            }
                        } else {
                            $fee_record = $query->row_array();
                        }

                        $this->db->flush_cache();

                        if (!is_null($fee_record)) {
                            if ($fee_level == '4') {
                                $data['translation_fee'] = $fee_record['translation_rate_level_3'];
                            } else {
                                $data['translation_fee'] = $fee_record['translation_rate_level_' . $fee_level];
                            }

                            if ($fee_level == '4') {
                                $data['filing_fee'] = $fee_record['filing_fee_level_2'];
                            } else {
                                $data['filing_fee'] = $fee_record['filing_fee_level_' . $fee_level];
                            }

                            // currency exchange rate

                            $currency_exchange_rate = $ci->currencies->get_currency_rate_by_code($country['currency_code']);
                            $global_fees_of_country = $this->get_global_fees_of_country($country['id'], $case['case_type_id']);
                            $data['official_fee_large'] = ceil($global_fees_of_country['official_fee_large'] / $currency_exchange_rate);
                            $data['official_fee_small'] = ceil($global_fees_of_country['official_fee_small'] / $currency_exchange_rate);
                            $data['official_fee_individual'] = ceil($global_fees_of_country['official_fee_individual'] / $currency_exchange_rate);
                            $data['initial_official_fee_large'] = $global_fees_of_country['official_fee_large'];
                            $data['initial_official_fee_small'] = $global_fees_of_country['official_fee_small'];
                            $data['initial_official_fee_individual'] = $global_fees_of_country['official_fee_individual'];
                            $data['filing_fee_level_1'] = $fee_record['filing_fee_level_1'];
                            $data['filing_fee_level_2'] = $fee_record['filing_fee_level_2'];
                            $data['filing_fee_level_3'] = $fee_record['filing_fee_level_3'];
                            $data['sequence_listing_fee'] = $global_fees_of_country['sequence_listing_fee'];
                            $data['extension_needed_fee'] = $global_fees_of_country['extension_needed_fee'];
                            $data['request_examination'] = $global_fees_of_country['request_examination'];
                            $data['number_claims_above_additional_fees'] = $global_fees_of_country['number_claims_above_additional_fees'];
                            $data['fee_additional_claims'] = $global_fees_of_country['fee_additional_claims'];
                            $data['additional_fee_for_claims'] = $global_fees_of_country['additional_fee_for_claims'];
                            $data['number_pages_above_additional_fees'] = $global_fees_of_country['number_pages_above_additional_fees'];
                            $data['fee_additional_pages'] = $global_fees_of_country['fee_additional_pages'];
                            $data['number_priorities_claimed_with_no_additional_charge'] = $global_fees_of_country['number_priorities_claimed_with_no_additional_charge'];
                            $data['charge_per_additional_claimed'] = $global_fees_of_country['charge_per_additional_claimed'];
                            $data['number_free_pages_drawing'] = $global_fees_of_country['number_free_pages_drawing'];
                            $data['charge_per_additional_pages_of_drawing'] = $global_fees_of_country['charge_per_additional_pages_of_drawing'];
                            $data['claim_number_threshold_for_additional_fee'] = $global_fees_of_country['claim_number_threshold_for_additional_fee'];
                            $data['page_number_treshold_for_additional_fee'] = $global_fees_of_country['page_number_treshold_for_additional_fee'];
                            $data['translation_rates_for_claims'] = $global_fees_of_country['translation_rates_for_claims'];
                            $data['additional_fee_above_treshold'] = $global_fees_of_country['additional_fee_above_treshold'];
                            $data['translation_rate_level_1'] = $fee_record['translation_rate_level_1'];
                            $data['translation_rate_level_2'] = $fee_record['translation_rate_level_2'];
                            $data['translation_rate_level_3'] = $fee_record['translation_rate_level_3'];

                            $fee_record['id'] = null;
                            $fee_record['user_id'] = $user_id;
                            $fee_record['case_type_id'] = $case['case_type_id'];
                            $this->db->where('country_id', $fee_record['country_id']);
                            $this->db->where('user_id', $user_id);
                            $this->db->where('case_type_id', $case['case_type_id']);
                            $tquery = $this->db->get('customers_fees');

                            if (!$tquery->num_rows()) {
                                $this->db->insert('customers_fees', $fee_record);
                            }
                        }
                        // If there is no records in "estimates_countries_fees"
                        $this->db->where('user_id', $user_id);
                        $this->db->where('case_id', $case['id']);
                        $this->db->where('country_id', $country['id']);
                        $query = $this->db->get('estimates_countries_fees');

                        if (!$query->num_rows()) {
                            if ($case['is_intake'] == '1') {
                                $data['is_approved'] = '1';

                            } else {
                                $data['is_approved'] = '0';
                            }
                            if ($case['common_status'] == 'pending-intake' || $case['common_status'] == 'active') {
                                $data['pm_approved_after_client'] = '1';
                                $data['is_approved'] = '1';
                            }
                            if ($added_by_client) {
                                $data['added_by_client'] = '1';
                                $data['added_originally'] = '0';
                            } else {
                                $data['added_by_client'] = '0';
                            }
                            $this->db->insert('estimates_countries_fees', $data);
                        }
                    }
                }
            }
        }
    }

    /**
     * Calculates official fee using local currency rate
     *
     * @access    public
     * @author    Sergey Koshkarev
     * @param    int    country ID
     * @param    float
     * @return    float
     * */
    public function calculate_official_fee_by_local_currency($country_id = '', $fee_value = '')
    {
        return ceil($fee_value);
    }

    /**
     * Calculates translation fee by translation rate, country and case
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @param    int    country ID
     * @param    float    translation rate
     * @return    float
     * */
    public function calculate_translation_fee($case_number = '', $country_id = '', $translation_rate = '')
    {
        $ci = &get_instance();
        $ci->load->model('cases_model', 'cases');

        $translation_fee = 0;
        $this->db->where('case_number', $case_number);
        $query = $this->db->get('cases');
        if ($query->num_rows()) {
            $case = $query->row_array();

            $case_type_id = $case['case_type_id'];
            $number_words = $case['number_words'];
            if (!is_null($customer_fees = $this->get_customer_fee_value_by_country($case['user_id'], $case['case_type_id'], $case['case_number'], $country_id))) {
                // Translation Fees
                if (($case_type_id == '1') || ($case_type_id == '3')) {
                    $translation_fee = ceil($translation_rate * $number_words);
                } // EP Data
                elseif ($case_type_id == '2') {
                    // Translation Fees
                    $translation_fee = ceil($translation_rate * $number_words + $customer_fees[$country_id]['translation_rates_for_claims'] * $case['number_words_in_claims']);
                }
            }
        }

        return $translation_fee;
    }

    /**
     * Returns estimate table: see estimate tab
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    mixed
     * */
    public function get_estimate_table($case_number, $need_calculations = false)
    {
        $countries_for_output = $this->get_calculation_results($case_number, false, $need_calculations);
        // endloop of country
        $this->table->add_row('&nbsp;',
            '&nbsp;',
            '&nbsp;',
            '&nbsp;',
            '&nbsp;',
            '&nbsp;');
        $this->table->add_row(array('colspan' => 7, 'data' => '<span id="all_total">Total:&nbsp;&nbsp;&nbsp;' . $countries_for_output['bottom_currency_sign'] . number_format($countries_for_output['bottom_all_total'], 0, '.', ' ') . '</span>', 'align' => 'right'));
        if (is_ajax()) {
            echo $this->table->generate();
        } else {
            return $this->table->generate();
        }
        $this->table->clear();


    }

    /**
     * Returns a list of customers fees by selected countries
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    user ID
     * @param    int    case type ID
     * @param    int    case number
     * @param    array    a list of countries
     * @return    mixed
     * */
    public function get_customer_fees_by_countries($user_id, $case_type_id = 1, $case_number, $estimate_countries = array(), $entity)
    {
        $countries_ids = array();
        if (check_array($estimate_countries)) {
            foreach ($estimate_countries as $country) {
                $countries_ids[] = $country['country_id'];
            }
        }

        $temp_array = array();
        if (!check_array($countries_ids)) {
            $countries_ids = array('-1');
        }
        $this->db->where_in('country_id', $countries_ids);
        $this->db->where('user_id', $user_id);
        $this->db->where('case_type_id', $case_type_id);
        $query = $this->db->get('customers_fees');
        if ($query->num_rows()) {

            $result = array();
            foreach ($query->result_array() as $record) {

                $result[$record['country_id']] = $record;
                $result[$record['country_id']]['official_fee'] = $this->calculate_official_fee_by_local_currency($record['country_id'], $record['official_fee_' . $entity]);
                $temp_array[] = $record['country_id'];
            }

            if (count($result) < $estimate_countries) {
                $new_countries_array = array_diff($countries_ids, $temp_array);
                if (check_array($new_countries_array)) {
                    foreach ($new_countries_array as $item) {
                        $this->add_country_to_estimate($case_number, $item);
                    }

                    $this->db->where_in('country_id', $countries_ids);
                    $this->db->where('user_id', $user_id);
                    $this->db->where('case_type_id', $case_type_id);
                    $query = $this->db->get('customers_fees');
                    if ($query->num_rows()) {
                        $result = array();
                        foreach ($query->result_array() as $record) {
                            $result[$record['country_id']] = $record;
                            $result[$record['country_id']]['official_fee'] = $this->calculate_official_fee_by_local_currency($record['country_id'], $record['official_fee']);
                        }

                        return $result;
                    }
                }
            }
            return $result;
        }
        return NULL;
    }

    /**
     * Returns customer fee entry by case type, country
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    user ID
     * @param    int    case type ID
     * @param    int    case number
     * @param    int    country ID
     * @return    mixed
     */
    public function get_customer_fee_value_by_country($user_id, $case_type_id, $case_number, $country_id = '')
    {
        $this->db->where('country_id', $country_id);
        $this->db->where('user_id', $user_id);
        $this->db->where('case_type_id', $case_type_id);
        $query = $this->db->get('customers_fees');
        if ($query->num_rows()) {
            $result = array();
            $record = $query->row_array();

            $result[$record['country_id']] = $record;
            $result[$record['country_id']]['official_fee_large'] = $this->calculate_official_fee_by_local_currency($record['country_id'], $record['official_fee_large']);
            return $result;
        }
        return NULL;
    }

    /**
     * Returns the last estimate PDF
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @return    mixed
     * */
    public function get_last_estimate_pdf($case_id = '')
    {
        $this->db->where('case_id', $case_id);
        $this->db->where('file_type_id', 15);
        $this->db->limit(1);
        $this->db->order_by('created_at', 'desc');
        $query = $this->db->get('cases_files');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Saves filing fee (for AJAX saving)
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    customer ID
     * @return    bool
     * */
    public function save_filing_fee_for_customer($customer_id = '')
    {
        $country_id = $this->input->post('country_id');
        $fee_value = $this->input->post('fee_value');
        $case_id = $this->input->post('case_id');
        $estimate_country_id = $this->input->post('estimate_country_id');
        if (!empty($_POST['unlock'])) {
            // unlock this
            $lock = '0';
        } else {
            $lock = '1';
        }

        $this->db->where('case_id', $case_id);
        $this->db->where('user_id', $customer_id);
        $this->db->where('estimates_countries_fees.id', $estimate_country_id);

        $data = array(
            'filing_fee' => $fee_value,
            'filing_fee_locked' => $lock,
        );
        $this->db->update('estimates_countries_fees', $data);

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    function save_locked_official_fee_for_country($customer_id)
    {
        $country_id = $this->input->post('country_id');
        $fee_value = $this->input->post('fee_value');
        $case_id = $this->input->post('case_id');
        $estimate_country_id = $this->input->post('estimate_country_id');
        if ($this->input->post('unlock')) {
            // unlock this
            $lock = '0';
        } else {
            $lock = '1';
        }

        $this->db->where('case_id', $case_id);
        $this->db->where('user_id', $customer_id);
        $this->db->where('id', $estimate_country_id);


        $data = array(
            'official_fee_locked_value' => $fee_value,
            'official_fee_locked' => $lock ,

        );

        $this->db->update('estimates_countries_fees', $data);

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;

    }

    /**
     * Makes estimate available for BDV
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    public function make_available_for_bdv($case_number = '')
    {
        $available = $this->input->post('available');

        $data = array(
            'estimate_available_for_bdv' => "$available"
        );
        $this->db->where('case_number', $case_number);
        $this->db->update('cases', $data);
    }

    /**
     * Makes estimate available for client
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    public function make_available_for_client($case_number = '')
    {
        $available = $this->input->post('available');

        $data = array(
            'estimate_available_for_client' => "$available"
        );
        $this->db->where('case_number', $case_number);
        $this->db->update('cases', $data);
    }

    /**
     * Sets the time when estimate saved by PM
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @param    string    date of saving
     * @return    void
     * */
    public function set_pm_save_time($case_number = '', $dt_save = '')
    {
        $dt_save_value = $dt_save;
        if (!is_null($dt_save)) {
            $dt_save_value = "$dt_save";
        }
        $this->db->where('case_number', $case_number);
        $this->db->update('cases', array('estimate_saved_by_pm' => $dt_save_value));

    }

    public function save_translation_fee_for_customer($customer_id = '')
    {
        $country_id = $this->input->post('country_id');
        $fee_value = $this->input->post('fee_value');
        $case_id = $this->input->post('case_id');
        $estimate_country_id = $this->input->post('estimate_country_id');

        if ($this->input->post('unlock')) {
            // unlock this
            $lock = '0';
        } else {
            $lock = '1';
        }

        $this->db->where('case_id', $case_id);
        $this->db->where('user_id', $customer_id);
        $this->db->where('id', $estimate_country_id);

        $data = array(
            'translation_fee' => $fee_value,
            'translation_fee_locked' => $lock
        );

        $this->db->update('estimates_countries_fees', $data);

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    function get_sub_lines($country_id)
    {
        $this->db->where('parent_id', $country_id);
        $query = $this->db->get('estimates_countries_fees');
        return $query->result_array();
    }

    function get_global_fees_of_country($country_id, $case_type_id)
    {
        $this->db->where('case_type_id', $case_type_id);
        $this->db->where('country_id', $country_id);
        $query = $this->db->get('fees');
        return $query->row_array();
    }

    public function get_calculation_results($case_number, $for_pdf = false, $is_manager = false, $for_one_country_id = false , $ignore_rows = false)
    {

        $ci = &get_instance();
        $ci->load->library('table');
        $ci->load->model('cases_model', 'cases');
        $ci->load->model('currencies_model', 'currencies');
        $case = $ci->cases->find_case_by_number($case_number);

        if ($is_manager && !$for_pdf) {
            $need_ajax = true;
        } else {
            $need_ajax = false;
        }

        if (!is_null($case)) {
            $currencies_rates_array = array();
            if (!empty($_POST['estimate_fee_level'])) {
                $estimate_fee_level = $_POST['estimate_fee_level'];
            } else {
                $estimate_fee_level = $case['estimate_fee_level'];
            }

            $entity = $this->input->post('entity');
            if (empty($entity))
                $entity = $case['entity'];

            if ($for_one_country_id) {
                $estimate_countries = $this->get_estimate_countries($case['id'], $case['user_id'], $estimate_fee_level, FALSE, $for_one_country_id);
            } else {
                $estimate_countries = $this->get_estimate_countries($case['id'], $case['user_id'], $estimate_fee_level, FALSE);
            }



            $customer_fees = $this->get_customer_fees_by_countries($case['user_id'], $case['case_type_id'], $case['case_number'], $estimate_countries, $entity);
            $new_array = array();
            if (empty($estimate_countries)) {
                $estimate_countries = array();
            }
            foreach ($estimate_countries as $country_key => $country_value) {
                if ($country_value['parent_id'] != 0) {
                    continue;
                }
                $sublines = $this->get_sub_lines($country_value['id']);
                $new_array[] = $country_value;
                if ($sublines) {
                    foreach ($sublines as $subline_key => $subline) {
                        $new_array[$subline_key . '_' . $country_value['country_id']] = $subline;
                    }
                }
            }
            $estimate_countries = $new_array;

            if (!is_null($customer_fees)) {

                if (!empty($_POST['entity'])) {
                    $ajax_inflate_official_fee = $inflate_official_fee = $this->input->post('inflate_official_fee');
                    $number_claims = $this->input->post('claims');
                    $number_pages = floatval($this->input->post('pages'));
                    $pages_sequence_listing = floatval($this->input->post('pages_sequence_listing'));
                    $search_report_location = $this->input->post('search_report_location');
                    $estimate_currency = $this->input->post('estimate_currency');
                    $entity = $this->input->post('entity');
                    $number_words = floatval($this->input->post('number_words'));
                    $number_words_in_claims = $this->input->post('number_words_in_claims');
                    $number_priorities_claimed = floatval($this->input->post('number_priorities_claimed'));
                    $number_pages_drawings = $this->input->post('number_pages_drawings');

                    // Save case data
                    $data = array(
                        'estimate_fee_level' => $estimate_fee_level,
                        'estimate_inflate_official_fee' => $inflate_official_fee,
                        'number_claims' => $number_claims,
                        'number_pages' => $number_pages,
                        'number_words' => $number_words,
                        'number_words_in_claims' => $number_words_in_claims,
                        'number_pages_sequence' => $pages_sequence_listing,
                        'search_location' => $search_report_location,
                        'entity' => $entity,
                        'case_currency' => $estimate_currency,
                        'number_priorities_claimed' => $number_priorities_claimed,
                        'number_pages_drawings' => $number_pages_drawings,
                    );
                    $this->db->where('id', $case['id']);

                    $this->db->update('cases', $data);

                } else {
                    $inflate_official_fee = $case['estimate_inflate_official_fee'];
                    $number_claims = ($case['number_claims']);
                    $number_pages = $case['number_pages'];
                    $pages_sequence_listing = $case['number_pages_sequence'];
                    $search_report_location = $case['search_location'];
                    $estimate_currency = $case['case_currency'];
                    $entity = $case['entity'];
                    $number_words = $case['number_words'];
                    $number_words_in_claims = $case['number_words_in_claims'];
                    $number_priorities_claimed = $case['number_priorities_claimed'];
                    $number_pages_drawings = $case['number_pages_drawings'];
                }

                $inflate_official_fee = floatval(1 + ($inflate_official_fee / 100));
                $estimate_footnotes = $this->get_estimate_footnotes($case['id']);


                $tmpl = array(
                    'table_open' => '<table border="0" cellpadding="2" cellspacing="0" width="100%" id="fees-table" class="data-table">'
                );

                $this->table->set_template($tmpl);
                $this->table->set_heading('', 'Approve by', 'Ext.', 'Country', 'Filing Fee', 'Official Fee', 'Translation Fee', 'Total');
                $all_total = 0;
                $number_footnotes = 1;
                $currency_sign = '$';

                $not_take_part_in_calculation = array();

                //startloop
                foreach ($estimate_countries as $country) {

                    if (is_ajax() && $need_ajax && $country['parent_id'] == 0) {
                        echo '<div class="calculate_row" style="float:left;">';
                    }

                    if ($country['parent_id'] == 0) {
                        $real_country = true;
                    } else {
                        $real_country = false;
                    }


                    if ($real_country) {

                        $country['official_fee_' . $entity] = $country['initial_official_fee_' . $entity];

                        $filing_footnote = '';
                        $official_footnote = '';
                        $translation_footnote = '';
                        $filing_locked = '';
                        $official_locked = '';
                        $translation_locked = '';

                        $locked_types = array('translation', 'filing', 'official');
                        foreach ($locked_types as $locked_type) {
                            if ($country[$locked_type . '_fee_locked'] == '1') {
                                $locked_var_name = $locked_type . '_locked';

                                $$locked_var_name = '<span class="locked-value">
													<img src="' . base_url() . 'assets/images/i/lock.png">
												</span>
												<input class="locked" type="hidden" value="' . $country[$locked_type . '_fee_locked'] . '" name="locked_' . $country['country_id'] . '_' . $locked_type . '">';
                            }

                            // Looking for footnote for current country

                            if (isset($estimate_footnotes[$country['country_id'] . '-' . $locked_type])) {
                                $var_name = $estimate_footnotes[$country['country_id'] . '-' . $locked_type]['fee_type'] . '_footnote';
                                $$var_name = '<sup id="' . $country['country_id'] . '-' . $estimate_footnotes[$country['country_id'] . '-' . $locked_type]['fee_type'] . '">' . $number_footnotes . '</sup>';
                                $number_footnotes++;
                            }
                        }


                        $country_input = form_hidden('countries[]', $country['country_id']);

                        if (array_key_exists($country['currency_code'], $currencies_rates_array)) {
                        } else {
                            $currencies_rates_array[$country['currency_code']] = $local_currency_rate = $ci->currencies->get_currency_rate_by_code($country['currency_code']);

                        }


                        // Additional charges
                        $additional_charges = 0.0;
                        if (($case['case_type_id'] == '1') || ($case['case_type_id'] == '3')) {
                            // Additional charges
                            // ====================================================================
                            // 1. "fee charged for excess claims"
                            $fee_charged_for_excess_claims = 0;

                            if ($case['entity'] == 'small') {
                                if (($country['country_id'] == '56') || ($country['country_id'] == '147')) {

                                    if (($number_claims - $country['number_claims_above_additional_fees']) > 0) {

                                        $fee_charged_for_excess_claims = ($number_claims - $country['number_claims_above_additional_fees']) * ($country['fee_additional_claims'] / $currencies_rates_array[$country['currency_code']]);
                                    }
                                } else {
                                    if (($number_claims - $country['number_claims_above_additional_fees']) > 0) {
                                        $fee_charged_for_excess_claims = ($number_claims - $country['number_claims_above_additional_fees']) * ($country['fee_additional_claims'] / $currencies_rates_array[$country['currency_code']]);
                                    }
                                }

                            } elseif ($case['entity'] == 'large') {
                                if (($number_claims >= $country['number_claims_above_additional_fees']) && (!empty($country['number_claims_above_additional_fees']))) {
                                    $fee_charged_for_excess_claims = ($number_claims - $country['number_claims_above_additional_fees']) * ($country['fee_additional_claims']);
                                }
                            } elseif ($case['entity'] == 'individual') {
                                if (($country['country_id'] == '56') || ($country['country_id'] == '147') || ($country['country_id'] == '85')) {
                                    if ($number_claims > $country['number_claims_above_additional_fees']) {
                                        $fee_charged_for_excess_claims = ($number_claims - $country['number_claims_above_additional_fees']) * ($country['fee_additional_claims'] / $currencies_rates_array[$country['currency_code']]);
                                    }
                                } else {

                                    if ($number_claims > $country['number_claims_above_additional_fees']) {
                                        $fee_charged_for_excess_claims = ($number_claims - $country['number_claims_above_additional_fees']) * ($country['fee_additional_claims'] / $currencies_rates_array[$country['currency_code']]);
                                    }
                                }
                            }

                            // ====================================================================
                            // 1. 2nd threshhold claims

                            $second_threshold_claims = 0;

                            //todo working marker

                            if (($number_claims >= $country['claim_number_threshold_for_additional_fee']) && (!empty($country['claim_number_threshold_for_additional_fee'])) && ($country['claim_number_threshold_for_additional_fee'] != '0')) {
                                if ($case['entity'] == 'small') {

                                    if (($country['country_id'] == '56') || ($country['country_id'] == '147')) {
                                        $second_threshold_claims = ceil(($number_claims - $country['claim_number_threshold_for_additional_fee']) * $country['additional_fee_for_claims']);
                                    } else {
                                        $second_threshold_claims = ceil(($number_claims - $country['claim_number_threshold_for_additional_fee']) * $country['additional_fee_for_claims']);
                                    }
                                } elseif ($case['entity'] == 'large') {
                                    $second_threshold_claims = ceil(($number_claims - $country['claim_number_threshold_for_additional_fee']) * $country['additional_fee_for_claims']);
                                } elseif ($case['entity'] == 'individual') {
                                    if (($country['country_id'] == '56') || ($country['country_id'] == '147') || ($country['country_id'] == '85')) {
                                        $second_threshold_claims = ceil(($number_claims - $country['claim_number_threshold_for_additional_fee']) * $country['additional_fee_for_claims']);
                                    } else {
                                        $second_threshold_claims = ceil(($number_claims - $country['claim_number_threshold_for_additional_fee']) * $country['additional_fee_for_claims'], -1);
                                    }
                                }
                            }

                            // ====================================================================
                            // 3. Fee for excess pages

                            $fee_for_excess_pages = 0;

                            if ($country['number_pages_above_additional_fees'] <= ($number_pages + $pages_sequence_listing)) {
                                if ($case['entity'] == 'small') {
                                    if (($country['country_id'] == '56') || ($country['country_id'] == '147')) {
                                        $fee_for_excess_pages = ceil(($number_pages + $pages_sequence_listing - $country['number_pages_above_additional_fees']) * $country['fee_additional_pages'] / $currencies_rates_array[$country['currency_code']]);
                                    } else {
                                        $fee_for_excess_pages = ceil(($number_pages + $pages_sequence_listing - $country['number_pages_above_additional_fees']) * $country['fee_additional_pages'] / $currencies_rates_array[$country['currency_code']]);
                                    }
                                } elseif ($case['entity'] == 'large') {
                                    $fee_for_excess_pages = ceil(($number_pages + $pages_sequence_listing - $country['number_pages_above_additional_fees']) * $country['fee_additional_pages'] / $currencies_rates_array[$country['currency_code']]);
                                } elseif ($case['entity'] == 'individual') {
                                    if (($country['country_id'] == '56') || ($country['country_id'] == '147') || ($country['country_id'] == '85')) {
                                        $fee_for_excess_pages = ceil(($number_pages + $pages_sequence_listing - $country['number_pages_above_additional_fees']) * $country['fee_additional_pages'] / $currencies_rates_array[$country['currency_code']]);
                                    } else {
                                        $fee_for_excess_pages = ceil(($number_pages + $pages_sequence_listing - $country['number_pages_above_additional_fees']) * $country['fee_additional_pages'] / $currencies_rates_array[$country['currency_code']]);
                                    }
                                }
                            }

                            // ====================================================================
                            // 4. 2nd threshhold pages
                            $second_treshold_pages = 0;

                            if (($country['page_number_treshold_for_additional_fee'] != '0') && (!empty($country['page_number_treshold_for_additional_fee'])) && ($country['page_number_treshold_for_additional_fee'] < ($number_pages + $pages_sequence_listing))) {
                                if ($case['entity'] == 'small') {
                                    if (($country['country_id'] == '56') || ($country['country_id'] == '147')) {
                                        $second_treshold_pages = ($number_pages + $pages_sequence_listing - $country['page_number_treshold_for_additional_fee']) * $country['additional_fee_above_treshold'];
                                    } else {
                                        $second_treshold_pages = ($number_pages + $pages_sequence_listing - $country['page_number_treshold_for_additional_fee']) * $country['additional_fee_above_treshold'];
                                    }
                                } elseif ($case['entity'] == 'large') {
                                    $second_treshold_pages = ($number_pages + $pages_sequence_listing - $country['page_number_treshold_for_additional_fee']) * $country['additional_fee_above_treshold'];
                                } elseif ($case['entity'] == 'individual') {
                                    if (($country['country_id'] == '56') || ($country['country_id'] == '147') || ($country['country_id'] == '85')) {
                                        $second_treshold_pages = ($number_pages + $pages_sequence_listing - $country['page_number_treshold_for_additional_fee']) * $country['additional_fee_above_treshold'];
                                    } else {
                                        $second_treshold_pages = ($number_pages + $pages_sequence_listing - $country['page_number_treshold_for_additional_fee']) * $country['additional_fee_above_treshold'];
                                    }
                                }

                            }

                            // ====================================================================
                            // 5. Fee per excess priorities claimed
                            $fee_per_excess_priorities_claimed = 0;


                            if ($country['number_priorities_claimed_with_no_additional_charge'] < $number_priorities_claimed) {
                                if ($case['entity'] == 'small') {

                                    if (($country['country_id'] == '56') || ($country['country_id'] == '147')) {
                                        $fee_per_excess_priorities_claimed = ceil(($number_priorities_claimed - $country['number_priorities_claimed_with_no_additional_charge']) * ($country['charge_per_additional_claimed'] / $currencies_rates_array[$country['currency_code']]));
                                    } else {
                                        $fee_per_excess_priorities_claimed = ceil(($number_priorities_claimed - $country['number_priorities_claimed_with_no_additional_charge']) * ($country['charge_per_additional_claimed'] / $currencies_rates_array[$country['currency_code']]));
                                    }
                                } elseif ($case['entity'] == 'large') {
                                    $fee_per_excess_priorities_claimed = ceil(($number_priorities_claimed - $country['number_priorities_claimed_with_no_additional_charge']) * ($country['charge_per_additional_claimed']));
                                } elseif ($case['entity'] == 'individual') {
                                    if (($country['country_id'] == '56') || ($country['country_id'] == '147') || ($country['country_id'] == '85')) {
                                        $fee_per_excess_priorities_claimed = ceil(($number_priorities_claimed - $country['number_priorities_claimed_with_no_additional_charge']) * ($country['charge_per_additional_claimed'] / $currencies_rates_array[$country['currency_code']]));
                                    } else {
                                        $fee_per_excess_priorities_claimed = ceil(($number_priorities_claimed - $country['number_priorities_claimed_with_no_additional_charge']) * ($country['charge_per_additional_claimed'] / $currencies_rates_array[$country['currency_code']]));
                                    }
                                }
                            }

                            // ====================================================================
                            // 6. Fee for extra drawings
                            $fee_for_extra_drawing = 0;

                            if ($number_pages_drawings >= $country['number_free_pages_drawing']) {
                                if ($case['entity'] == 'small') {
                                    if (($country['country_id'] == '56') || ($country['country_id'] == '147')) {
                                        $fee_for_extra_drawing = ceil(($number_pages_drawings - $country['number_free_pages_drawing']) * $country['charge_per_additional_pages_of_drawing']);
                                    } else {
                                        $fee_for_extra_drawing = ceil(($number_pages_drawings - $country['number_free_pages_drawing']) * $country['charge_per_additional_pages_of_drawing']);
                                    }
                                } elseif ($case['entity'] == 'large') {
                                    $fee_for_extra_drawing = ceil(($number_pages_drawings - $country['number_free_pages_drawing']) * $country['charge_per_additional_pages_of_drawing']);
                                } elseif ($case['entity'] == 'individual') {
                                    if (($country['country_id'] == '56') || ($country['country_id'] == '147') || ($country['country_id'] == '85')) {
                                        $fee_for_extra_drawing = ceil(($number_pages_drawings - $country['number_free_pages_drawing']) * $country['charge_per_additional_pages_of_drawing']);
                                    } else {
                                        $fee_for_extra_drawing = ceil(($number_pages_drawings - $country['number_free_pages_drawing']) * $country['charge_per_additional_pages_of_drawing']);
                                    }
                                }
                            }

                            // ====================================================================
                            // 7. Search charge
                            $search_charge = 0;
                            $euro_rate = $ci->currencies->get_currency_rate_by_code();
                            // Europe
                            if ($country['country_id'] == '74') {
                                if ($search_report_location == 'ep') {
                                    $search_charge = 0;
                                } elseif (($search_report_location == 'us') || ($search_report_location == 'jp_ru_au_cn_kr')) {
                                    $search_charge = 1281;
                                } else {

                                    $search_charge = 1050 / $euro_rate;
                                }
                            } // USA
                            elseif ($country['country_id'] == '147') {

                                if ($search_report_location == 'us') {

                                    $search_charge = 350;
                                } else {

                                    $search_charge = 780;
                                }
                            }

                            $additional_charges = $fee_charged_for_excess_claims + $second_threshold_claims + $fee_for_excess_pages + $second_treshold_pages + $fee_per_excess_priorities_claimed + $fee_for_extra_drawing + $search_charge;

                            if (is_ajax() && $need_ajax) {
                                if ($estimate_currency == 'euro') {
                                    $euro_exchange_rate = $ci->currencies->get_currency_rate_by_code('EUR');
                                    $official_fee_ajax = ceil($country['official_fee_' . $entity] * $euro_exchange_rate);
                                } else {
                                    $official_fee_ajax = $country['official_fee_' . $entity];
                                }

                                echo '</br>';
                                echo '=================================';
                                echo '</br>';
                                echo $country['country'] . $country['country_id'];
                                echo '</br>';
                                echo '<br/>Official fee: ' . $official_fee_ajax;
                                echo '<br/>$fee_charged_for_excess_claims = ' . $fee_charged_for_excess_claims;
                                echo '<br/>$second_threshold_claim = ' . $second_threshold_claims;
                                echo '<br/>$fee_for_excess_pages = ' . $fee_for_excess_pages;
                                echo '<br/>$second_treshold_pages = ' . $second_treshold_pages;
                                echo '<br/>$fee_per_excess_priorities_claimed = ' . $fee_per_excess_priorities_claimed;
                                echo '<br/>$fee_for_extra_drawing = ' . $fee_for_extra_drawing;
                                echo '<br/>$search_charge = ' . $search_charge;
                                echo '</br>';
                                echo '=================================';
                            }
                        } elseif ($case['case_type_id'] == '2') {
                            // "fee charged for excess claims"
                            $fee_charged_for_excess_claims = 0;
                            if (($country['number_claims_above_additional_fees'] == 0) || (empty($country['number_claims_above_additional_fees']))) {
                                $fee_charged_for_excess_claims = 0;
                            } elseif ($country['number_claims_above_additional_fees'] < $number_claims) {

                                $fee_charged_for_excess_claims = ($number_claims - $country['number_claims_above_additional_fees']) * $country['fee_additional_claims'];
                            }

                            // ====================================================================
                            // 2. 2nd threshhold claims

                            $second_threshold_claims = 0;

                            if (($number_claims > $country['number_pages_above_additional_fees']) && (!empty($country['number_pages_above_additional_fees']))) {
                                if (($country['number_claims_above_additional_fees'] != 0) && (!empty($country['number_claims_above_additional_fees']))) {
                                    $second_threshold_claims = ($number_claims - $country['number_claims_above_additional_fees']) * $country['fee_additional_claims'];
                                }
                            }

                            // ====================================================================
                            // 3. Fee for excess pages
                            $fee_for_excess_pages = 0;

                            if ($country['number_pages_above_additional_fees'] < $number_pages) {
                                // Austria
                                if ($country['country_id'] == '46') {

                                    $fee_for_excess_pages = ceil((($number_pages - $country['page_number_treshold_for_additional_fee']) / $country['page_number_treshold_for_additional_fee'])) * $country['additional_fee_above_treshold'];
                                } else {
                                    $fee_for_excess_pages = ceil(($number_pages - $country['number_pages_above_additional_fees']) * $country['fee_additional_pages']);
                                }
                            }

                            // Fee for extra drawings
                            $fee_for_extra_drawing = 0;
                            if ($case['number_pages_drawings'] > $country['number_free_pages_drawing']) {
                                $fee_for_extra_drawing = ceil(($case['number_pages_drawings'] - $country['number_free_pages_drawing']) * $country['charge_per_additional_pages_of_drawing'] / $currencies_rates_array[$country['currency_code']]);
                            }

                            $additional_charges = ceil($fee_charged_for_excess_claims + $second_threshold_claims + $fee_for_excess_pages + $fee_for_extra_drawing);

                            if (is_ajax() && $need_ajax) {
                                echo '</br>';
                                echo '=================================';
                                echo '</br>';
                                echo $country['country'] . $country['country_id'];
                                echo '</br>';
                                echo '<br/>Official fee: ' . $country['official_fee_' . $entity];
                                echo '<br/>$fee_charged_for_excess_claims = ' . $fee_charged_for_excess_claims;
                                echo '<br/>$second_threshold_claims = ' . $second_threshold_claims;
                                echo '<br/>$fee_for_excess_pages = ' . $fee_for_excess_pages;
                                echo '<br/>$fee_for_extra_drawing = ' . $fee_for_extra_drawing;

                                echo '</br>';
                                echo '=================================';
                            }
                        }

                        if ($additional_charges < 0) {
                            $additional_charges = 0;
                        }

                        if (($case['case_type_id'] == '1') || ($case['case_type_id'] == '3')) {

                            $filing_fee = 0;

                            if ($estimate_fee_level == 4) {
                                $filing_fee = $country['filing_fee_level_2'];

                            } else {
                                if ($estimate_fee_level) {
                                    $filing_fee = $country['filing_fee_level_' . $estimate_fee_level];

                                }

                            }

                            if ($case['sequence_listing'] == '1') {
                                $filing_fee = $filing_fee + $country['sequence_listing_fee'];
                            }

                            if ($case['entity'] == 'small') {

                                if (($country['country_id'] == '56') || ($country['country_id'] == '147')) {

                                } else {

                                }
                            } elseif ($case['entity'] == 'large') {

                            } elseif ($case['entity'] == 'individual') {

                                if (($country['country_id'] == '56') || ($country['country_id'] == '147') || ($country['country_id'] == '85')) {

                                } else {

                                }
                            }

                            // Translation Fees
                            $translation_fee = 0;
                            if ($estimate_fee_level == 4) {
                                $translation_fee = round_up($country['translation_rate_level_3'] * $number_words);
                            } else {
                                if ($estimate_fee_level) {
                                    $translation_fee = round_up($country['translation_rate_level_' . $estimate_fee_level] * $number_words);
                                }
                            }

                        } // EP Data
                        elseif ($case['case_type_id'] == '2') {
                            // here calculation
                            // Filing Fee
                            if ($estimate_fee_level == 4) {
                                $filing_fee = $country['filing_fee_level_2'];
                            } else {
                                $filing_fee = $country['filing_fee_level_' . $estimate_fee_level];

                            }

                            // Translation Fees

                            if ($estimate_fee_level == 4) {
                                $translation_fee = round_up($country['translation_rate_level_3'] * $number_words + $country['translation_rates_for_claims'] * $number_words_in_claims);
                            } else {
                                $translation_fee = round_up($country['translation_rate_level_' . $estimate_fee_level] * $number_words + $country['translation_rates_for_claims'] * $number_words_in_claims);
                            }

                        }
                        // Additional charges
                        if (is_ajax() && $need_ajax) {
                            // Official fee for EP validation
                            if ($case['case_type_id'] == '2') {

                                $official_fee = round_up($inflate_official_fee * ($country['official_fee_' . $entity] + $additional_charges));

                            } else {

                                $official_fee = round_up($inflate_official_fee * round_up($country['official_fee_' . $entity] + $additional_charges));
                            }
                        } else {

                            if ($case['case_type_id'] == '2') {

                                $official_fee = round_up($inflate_official_fee * ($country['official_fee_' . $entity] + $additional_charges));

                            } else {

                                $official_fee = round_up($inflate_official_fee * round_up($country['official_fee_' . $entity] + $additional_charges));


                            }
                        }

                        // If filing fee is locked
                        if (!empty($filing_locked)) {
                            $filing_fee = $country['filing_fee'];

                            if ($case['sequence_listing'] == '1') {
                                $sequence_listing_fee = $country['translation_rates_for_claims'];
                                $filing_fee = $filing_fee + $sequence_listing_fee;
                            }
                        }

                        // If official fee is locked
                        if (!empty($official_locked)) {
                            $official_fee = $country['official_fee_locked_value'];
                        }
                        // If translation fee is locked
                        if (!empty($translation_locked)) {
                            $translation_fee = $country['translation_fee'];
                        }

                        $currency_sign = '$';

                        // If estimate currency is EURO
                        if ($estimate_currency == 'euro') {
                            $currency_sign = '€';

                            if (array_key_exists('EUR', $currencies_rates_array)) {
                                $euro_exchange_rate = $currencies_rates_array['EUR'];
                            } else {
                                $currencies_rates_array['EUR'] = $euro_exchange_rate = $ci->currencies->get_currency_rate_by_code('EUR');

                            }
                            $filing_fee = round_up($filing_fee * $euro_exchange_rate);
                            $official_fee = round_up($official_fee * $euro_exchange_rate);
                            $translation_fee = round_up($translation_fee * $euro_exchange_rate);

                        }

                        if (($translation_fee < 150) && ($translation_fee > 0)) {
                            $translation_fee = 150;
                        }

                        $total = $filing_fee + $official_fee + $translation_fee;
                        if ($country['is_disabled_by_client'] == '0') {
                            if (!empty($country['country_filing_deadline']) && strtotime($country['country_filing_deadline']) < time()) {
                                $not_take_part_in_calculation[] = $country['id'];
                            } else {

                                $all_total += $total;
                            }



                        }

                        $row_estimate_class = ''; // CSS class to highlight estimate rows

                        // Added by client during estimate
                        if ($country['added_by_client'] == '1' && $country['added_originally'] == '0') {
                            $row_estimate_class = 'added-estimate-row';
                        }

                        // Added by PM
                        if ($country['added_by_pm'] == '1') {
                            $row_estimate_class = 'added-by-pm';
                        }

                        if (($country['is_approved'] == '0') && ($country['is_disabled_by_client'] == '1')) {
                            $row_estimate_class = 'disabled-estimate-row';
                        }

                        // Approved by client
                        if ($country['is_approved'] == '1') {
                            $row_estimate_class = 'approved-estimate-row';
                        }

                        // Added originally
                        if (empty($row_estimate_class) && ($country['added_originally'] == '1') && ($country['neutral'] == '1') && ($country['added_by_client'] == '0') && !$case['is_intake']) {
                            $row_estimate_class = '';
                        }

                        if (!empty($country['country_filing_deadline'])) {
                            $temp_date = new Datetime($country['country_filing_deadline']);
                            $current_time = new Datetime(date('Y-m-d'));
                            if ($current_time >= $temp_date) {
                                $row_estimate_class = 'past-approval';
                            }
                        }
                        $country_delete_link = '<a href="javascript:void(0);" rel="' . $country['id'] . '" class="estimate_delete_country" id="' . $country['country_id'] . '"><img src="' . base_url() . 'assets/images/i/delete.png" title="Remove country from estimate" class="tiptip"/></a>&nbsp;';

                        $plus = '<span class="plus"><a rel="' . $country['country_id'] . '" class="add_sub_line" href="' . $country['id'] . '"><img width="20px" src="' . base_url() . 'assets/img/plus_64.png"></a></span>';

                        // Disabled by client
                    } else {

                        $country['extension_needed'] = false;
                        $country['country'] = $country['custom_text'];
                        $translation_fee = $country['translation_fee'];
                        $official_fee = $country['official_fee_locked_value'];
                        $filing_fee = $country['filing_fee'];
                        $country['translation_rate'] = 0;

                        $total = $official_fee + $filing_fee + $translation_fee;

                        if (!$country['is_disabled_by_client'] && !in_array($country['parent_id'] , $not_take_part_in_calculation)) {
                            $all_total += $total;
                        }

                        $plus = '<span class="plus"><a rel="' . $country['country_id'] . '" class="edit_sub_line" href="' . $country['id'] . '"><img width="20px" src="' . base_url() . 'assets/img/edit.png"></a></span>';
                        $country_delete_link = '<a href="' . base_url() . 'estimates/delete_sub_country/' . $country['id'] . '" class="delete_sub_country" rel="' . $country['id'] . '"><img src="' . base_url() . 'assets/images/i/delete.png" title="Remove country from estimate" class="tiptip"/></a>&nbsp;';
                    }


                    $this->table->add_row(
                        array('data' => $plus),
                        array('data' => form_input('country_filing_deadline[]', $country['country_filing_deadline'], 'class="date small"') . '<input type="hidden" name="country_estimate_id[]" value="' . $country['id'] . '">' , 'class' => $row_estimate_class) ,
                        array('data' => form_checkbox('extension_needed', $country['country_id'], $country['extension_needed'], 'id="' . $country['country_id'] . '" class="extension_needed"') . form_hidden('additional_charges[]', $additional_charges) . form_hidden('parent_id', $country['parent_id']) . form_hidden('custom_text', $country['custom_text']) . form_hidden('initial_official_fees[]', $country['initial_official_fee_' . $entity]), 'class' => $row_estimate_class),
                        array('data' => $country_delete_link . $country['country'] . $country_input, 'class' => $row_estimate_class),
                        array('data' => '<span id="filing" class="fee">' . $currency_sign . $filing_fee . '</span>' . $filing_footnote . form_hidden('filing_fee_' . $country['country_id'], $filing_fee) . $filing_locked, 'class' => $row_estimate_class),
                        array('data' => '<span id="official" class="fee">' . $currency_sign . $official_fee . '</span>' . $official_footnote . form_hidden('official_fee_' . $country['country_id'], $official_fee) . $official_locked, 'class' => $row_estimate_class),
                        array('data' => '<span id="translation" class="fee">' . $currency_sign . $translation_fee . '</span>' . $translation_footnote . form_hidden('translation_fee_' . $country['country_id'], $translation_fee) . form_hidden('translation_rate_' . $country['country_id'], $country['translation_rate']) . $translation_locked, 'class' => $row_estimate_class),
                        array('data' => '<span class="estimate-total">' . $currency_sign . $total . '</span>', 'class' => $row_estimate_class));
                    if ($ignore_rows) {
                        $this->table->clear();
                    }
                    if ($country['is_disabled_by_client'] == '1') {
                        unset($country);
                        continue;
                    }

                    if (!empty($country['country_filing_deadline']) && strtotime($country['country_filing_deadline']) < time() && $for_pdf) {
                        unset($country);
                        continue;
                    }

                    $country['result_official_fee'] = $official_fee;
                    $country['result_filing_fee'] = $filing_fee;
                    $country['result_translation_fee'] = $translation_fee;
                    $country['result_currency_sign'] = $currency_sign;
                    $country['result_total'] = $total;
                    $country['result_country_id'] = $country['country_id'];
                    $countries_array['countries'][] = $country;
                    if (is_ajax() && $need_ajax && $country['parent_id'] == 0) {
                        echo '</div>';
                    }
                }

                if (is_ajax() && $need_ajax) {
                    echo '<div style="clear:both;"></div>';
                }

                $countries_array['bottom_currency_sign'] = $currency_sign;
                $countries_array['bottom_all_total'] = $all_total;



                return $countries_array;
            }
        }
    }

    function save_additional_countries($case_data)
    {
        if (empty($_POST['sub_custom_text'])) {
            return false;
        }

        foreach ($_POST['sub_custom_text'] as $key => $value) {
            $this->db->where('id',$_POST['sub_parent_id'][$key]);
            $query = $this->db->get('estimates_countries_fees');
            $parents_data = $query->row_array();

            $options['is_approved'] = $parents_data['is_approved'];
            $options['is_approved_by_pm'] = $parents_data['is_approved_by_pm'];
            $options['pm_approved_after_client'] = $parents_data['pm_approved_after_client'];

            $options['custom_text'] = $_POST['sub_custom_text'][$key];
            $options['official_fee_locked_value'] = $_POST['sub_official_fee'][$key];
            $options['filing_fee'] = $_POST['sub_filing_fee'][$key];
            $options['translation_fee'] = $_POST['sub_translation_fee'][$key];
            $options['case_id'] = $case_data['id'];
            $options['user_id'] = $case_data['user_id'];
            $options['parent_id'] = $_POST['sub_parent_id'][$key];
            $options['country_id'] = $_POST['sub_country_id'][$key];
            $this->insert_sublines($options);

        }

    }

    function update_additional_countries()
    {
        if (empty($_POST['update_custom_text'])) {
            return false;
        }
        foreach ($_POST['update_custom_text'] as $key => $value) {
            $options['custom_text'] = $_POST['update_custom_text'][$key];
            $options['official_fee_locked_value'] = $_POST['update_official_fee'][$key];
            $options['filing_fee'] = $_POST['update_filing_fee'][$key];
            $options['translation_fee'] = $_POST['update_translation_fee'][$key];
            $options['id'] = $_POST['update_estimate_country_id'][$key];
            $this->update_estimates_countries_fees($options);
        }
    }

    function update_estimates_countries_fees($options = array())
    {
        if (empty($options['id'])) {
            return false;
        }

        $this->db->where('id', $options['id']);
        $this->db->update('estimates_countries_fees', $options);
    }

    function get_countries_of_estimate($case_id)
    {
        $this->db->select('zen_estimates_countries_fees.* , currency_code');
        $this->db->where('case_id', $case_id);
        $this->db->where('parent_id', 0);
        $this->db->join('countries', 'countries.id = estimates_countries_fees.country_id');
        $query = $this->db->get('estimates_countries_fees');

        return $query->result_array();
    }

    function update_fees_of_case($case_number)
    {

        $ci = & get_instance();
        $ci->load->model('currencies_model', 'currencies');
        // If there are no countries in the table then insert them

        $ci = &get_instance();
        $ci->load->model('cases_model', 'cases');
        $case = $ci->cases->find_case_by_number($case_number);
        $user_id = $case['user_id'];
        $countries = $this->get_countries_of_estimate($case['id']);
        foreach ($countries as $country) {

            $data = array(
                'user_id' => $user_id,
                'case_id' => $case['id'],
                'country_id' => $country['country_id'],
            );

            $case_country_data = array(
                'user_id' => $user_id,
                'country_id' => $country['country_id'],
                'case_type_id' => $case['case_type_id']
            );

            // Check is there this record in DB or not
            $query = $this->db->get_where('customers_fees', $case_country_data);
            $fee_record = $query->row_array();
            // currency exchange rate

            $currency_exchange_rate = $ci->currencies->get_currency_rate_by_code($country['currency_code']);

            $global_fees_of_country = $this->get_global_fees_of_country($country['country_id'], $case['case_type_id']);

            $data['official_fee_large'] = ceil($global_fees_of_country['official_fee_large'] / $currency_exchange_rate);
            $data['official_fee_small'] = ceil($global_fees_of_country['official_fee_small'] / $currency_exchange_rate);
            $data['official_fee_individual'] = ceil($global_fees_of_country['official_fee_individual'] / $currency_exchange_rate);

            $data['initial_official_fee_large'] = $global_fees_of_country['official_fee_large'];
            $data['initial_official_fee_small'] = $global_fees_of_country['official_fee_small'];
            $data['initial_official_fee_individual'] = $global_fees_of_country['official_fee_individual'];

            $data['filing_fee_level_1'] = $fee_record['filing_fee_level_1'];
            $data['filing_fee_level_2'] = $fee_record['filing_fee_level_2'];
            $data['filing_fee_level_3'] = $fee_record['filing_fee_level_3'];

            $data['sequence_listing_fee'] = $global_fees_of_country['sequence_listing_fee'];
            $data['extension_needed_fee'] = $global_fees_of_country['extension_needed_fee'];
            $data['request_examination'] = $global_fees_of_country['request_examination'];
            $data['number_claims_above_additional_fees'] = $global_fees_of_country['number_claims_above_additional_fees'];
            $data['fee_additional_claims'] = $global_fees_of_country['fee_additional_claims'];
            $data['additional_fee_for_claims'] = $global_fees_of_country['additional_fee_for_claims'];
            $data['number_pages_above_additional_fees'] = $global_fees_of_country['number_pages_above_additional_fees'];
            $data['fee_additional_pages'] = $global_fees_of_country['fee_additional_pages'];
            $data['number_priorities_claimed_with_no_additional_charge'] = $global_fees_of_country['number_priorities_claimed_with_no_additional_charge'];
            $data['charge_per_additional_claimed'] = $global_fees_of_country['charge_per_additional_claimed'];
            $data['number_free_pages_drawing'] = $global_fees_of_country['number_free_pages_drawing'];
            $data['charge_per_additional_pages_of_drawing'] = $global_fees_of_country['charge_per_additional_pages_of_drawing'];
            $data['claim_number_threshold_for_additional_fee'] = $global_fees_of_country['claim_number_threshold_for_additional_fee'];
            $data['page_number_treshold_for_additional_fee'] = $global_fees_of_country['page_number_treshold_for_additional_fee'];

            $data['translation_rates_for_claims'] = $global_fees_of_country['translation_rates_for_claims'];
            $data['additional_fee_above_treshold'] = $global_fees_of_country['additional_fee_above_treshold'];

            $data['translation_rate_level_1'] = $fee_record['translation_rate_level_1'];
            $data['translation_rate_level_2'] = $fee_record['translation_rate_level_2'];
            $data['translation_rate_level_3'] = $fee_record['translation_rate_level_3'];
            $this->db->where('id', $country['id']);
            $this->db->update('estimates_countries_fees', $data);
            unset($data);
        }

    }

    function insert_sublines($options = array())
    {

        $this->db->insert('estimates_countries_fees', $options);
        return $this->db->insert_id();
    }

    function delete_sub_country($estimate_country_id)
    {
        if (empty($estimate_country_id)) {
            return false;
        }

        $this->db->where('id', $estimate_country_id);
        $this->db->delete('estimates_countries_fees');
    }

    function save_customer_data_for_case($case_id)
    {

        $this->db->select('customers.*');
        $this->db->where('cases.id', $case_id);
        $this->db->where('is_customer_data_loaded', 0);
        $this->db->join('customers', 'customers.id = cases.user_id');
        $query = $this->db->get('cases');

        if (!$query->num_rows()) {
            return false;
        }

        $case_data = $query->row();

        $this->db->where('id', $case_id);
        $options['customer_firstname'] = $case_data->firstname;
        $options['customer_email'] = $case_data->email;
        $options['customer_lastname'] = $case_data->lastname;
        $options['customer_company_name'] = $case_data->company_name;
        $options['customer_address'] = $case_data->address;
        $options['customer_address2'] = $case_data->address2;
        $options['customer_city'] = $case_data->city;
        $options['customer_state'] = $case_data->state;
        $options['customer_zip_code'] = $case_data->zip_code;
        $options['customer_country'] = $case_data->country;
        $options['customer_phone_number'] = $case_data->phone_number;
        $options['customer_ext'] = $case_data->ext;
        $options['customer_fax'] = $case_data->fax;
        $options['is_customer_data_loaded'] = 1;
        $this->db->update('cases', $options);

    }

    public function get_estimate_countries($case_id, $user_id, $fee_level = FALSE, $take_origin_official_fee = FALSE, $country_id = false)
    {
        $this->load->model('cases_model', 'cases');
        $ci = &get_instance();
        $ci->load->model('currencies_model', 'currencies');
        if (!is_null($case = $this->cases->get_case($case_id, FALSE, TRUE))) {
            $fee_level = ($fee_level) ? $fee_level : $case['estimate_fee_level'];
            if (empty($fee_level)) {
                $fee_level = '1';
            }
            if ($fee_level == '4') {
                $translation_fee_level = 3;
            } else
            {
                $translation_fee_level = $fee_level;
            }
            if ($country_id) {
                $where_for_country_id = ' cc.country_id =' . $country_id . ' AND';
            } else
            {
                $where_for_country_id = '';
            }
            $q = 'SELECT DISTINCT ecf.*,
								  c.country,
								  c.currency_code,
								  cc.extension_needed,
								  cc.is_enabled,
								  cc.added_by_pm,
								  cc.estimate_added,
								  c.id as country_id,
								  cf.official_fee_large as origin_official_fee_large,
                                  cf.official_fee_small as origin_official_fee_small,
                                  cf.official_fee_individual as origin_official_fee_individual,
								  ecf.initial_official_fee_large,
                                  ecf.initial_official_fee_small,
                                  ecf.initial_official_fee_individual,
								  DATE_FORMAT(ecf.country_filing_deadline, "%m/%d/%y") as country_filing_deadline,
								  cf.translation_rate_level_' . $translation_fee_level . ' as translation_rate,
								  c.pct_language,
								  c.ep_language,
								  c.direct_language
				  FROM `zen_estimates_countries_fees` ecf, `zen_countries` c, `zen_cases_countries` cc, `zen_customers_fees` cf
				  WHERE (ecf.case_id = ' . $case_id . ') AND
				  		(c.id = ecf.country_id) AND
				  		(c.id = cc.country_id) AND
				  		(cc.case_id = ' . $case_id . ') AND' .
                $where_for_country_id
                . '(cc.country_id = ecf.country_id) AND
						(cf.country_id = cc.country_id) AND
						(cf.user_id = ' . $case['user_id'] . ') AND
						(cf.case_type_id = ' . $case['case_type_id'] . ')
				  ORDER BY c.country';
            $query = $this->db->query($q);
            $result = array();
            if ($ecf_count = $query->num_rows()) {
                foreach ($query->result_array() as $row)
                {
                    $currency_exchange_rate = 1;
                    if ($take_origin_official_fee) {
                        if ($row['currency_code'] != 'USD') {
                            $currency_exchange_rate = 1;
                        }
                        $row['official_fee_large'] = ceil($row['initial_official_fee_large'] / $currency_exchange_rate);
                        $row['official_fee_small'] = ceil($row['initial_official_fee_small'] / $currency_exchange_rate);
                        $row['official_fee_individual'] = ceil($row['initial_official_fee_individual'] / $currency_exchange_rate);
                    } else
                    {
                        $row['official_fee_large'] = ceil($row['official_fee_large'] / $currency_exchange_rate);
                        $row['official_fee_small'] = ceil($row['official_fee_small'] / $currency_exchange_rate);
                        $row['official_fee_individual'] = ceil($row['official_fee_individual'] / $currency_exchange_rate);
                    }
                    $additional_charges = 0;
                    $row['official_fee_large'] = $row['official_fee_large'] + $additional_charges;
                    $row['official_fee_small'] = $row['official_fee_small'] + $additional_charges;
                    $row['official_fee_individual'] = $row['official_fee_individual'] + $additional_charges;
                    $result[] = $row;
                    $case_countries_ids[] = $row['country_id'];
                }
                return $result;
            } else
            {
                $q = 'SELECT DISTINCT ecf.*,
								  c.country,
								  c.currency_code,
								  cc.extension_needed,
								  cc.is_enabled,
								  cc.added_by_pm,
								  cc.estimate_added,
								  c.id as country_id,
								  cf.official_fee_large as origin_official_fee_large,
                                  cf.official_fee_small as origin_official_fee_small,
                                  cf.official_fee_individual as origin_official_fee_individual,
								  ecf.initial_official_fee_large,
                                  ecf.initial_official_fee_small,
                                  ecf.initial_official_fee_individual,
								  cf.translation_rate_level_' . $translation_fee_level . ' as translation_rate,
								  c.pct_language,
								  c.ep_language,
								  c.direct_language
				  FROM `zen_estimates_countries_fees` ecf, `zen_countries` c, `zen_cases_countries` cc, `zen_customers_fees` cf
				  WHERE (ecf.case_id = ' . $case_id . ') AND
				  		(c.id = ecf.country_id) AND
				  		(c.id = cc.country_id) AND
				  		(cc.case_id = ' . $case_id . ') AND'
                    . $where_for_country_id .
                    '(cc.country_id = ecf.country_id) AND
						(cf.country_id = cc.country_id) AND
						(cf.user_id = ' . $case['user_id'] . ') AND
						(cf.case_type_id = ' . $case['case_type_id'] . ')
				  ORDER BY c.country';
                $query = $this->db->query($q);
                if ($query->num_rows()) {
                    foreach ($query->result_array() as $row)
                    {
                        $currency_exchange_rate = 1;

                        if ($take_origin_official_fee) {
                            $row['official_fee'] = ceil($row['initial_official_fee'] / $currency_exchange_rate);
                        } else
                        {
                            $row['official_fee'] = ceil($row['official_fee'] / $currency_exchange_rate);
                        }
                        $result[] = $row;
                    }
                    return $result;
                } else
                {
                    $this->add_fees_entries($user_id, $case['case_number'], $fee_level);
                }
            }
            $this->db->flush_cache();
            $query = $this->db->query($q);
            if ($query->num_rows()) {
                foreach ($query->result_array() as $row)
                {
                    $currency_exchange_rate = 1;
                    if ($take_origin_official_fee) {
                        $currency_exchange_rate = 1;
                        $row['official_fee_large'] = ceil($row['initial_official_fee'] / $currency_exchange_rate);
                        $row['official_fee_small'] = ceil($row['initial_official_fee'] / $currency_exchange_rate);
                        $row['official_fee_individual'] = ceil($row['initial_official_fee'] / $currency_exchange_rate);
                    } else
                    {
                        $row['official_fee_large'] = ceil($row['official_fee_large'] / $currency_exchange_rate);
                        $row['official_fee_small'] = ceil($row['official_fee_small'] / $currency_exchange_rate);
                        $row['official_fee_individual'] = ceil($row['official_fee_individual'] / $currency_exchange_rate);
                    }
                    $result[] = $row;
                }
                return $result;
            }
        }
        return NULL;
    }

    public function addMonths($date, $months)
    {

        $init = clone $date;
        $modifier = $months . ' months';
        $back_modifier = -$months . ' months';

        $date->modify($modifier);
        $back_to_init = clone $date;
        $back_to_init->modify($back_modifier);

        while ($init->format('m') != $back_to_init->format('m')) {
            $date->modify('-1 day');
            $back_to_init = clone $date;
            $back_to_init->modify($back_modifier);
        }
        return $date->format('Y-m-d');
    }

    public function addYears($date, $years)
    {
        $init = clone $date;
        $modifier = $years . ' years';
        $date->modify($modifier);

        while ($date->format('m') != $init->format('m')) {
            $date->modify('-1 day');
        }

        return $date->format('Y-m-d');
    }

    function get_assigned_associates_invoices($case_id) {

        $this->db->select('zen_cases_associates_data.* , filename , location , zen_countries.country , fee , fee_currency');
        $this->db->join('zen_cases_associates_data' , 'zen_cases_associates_data.case_id = zen_cases_countries.case_id AND zen_cases_associates_data.country_id = zen_cases_countries.country_id');
        $this->db->join('zen_cases_files' , 'zen_cases_files.id = zen_cases_associates_data.fa_invoice_file_id' , 'left');
        $this->db->join('zen_countries' , 'zen_countries.id = zen_cases_associates_data.country_id');
        $this->db->join('zen_associates' , 'zen_associates.id = zen_cases_associates_data.associate_id');

        $this->db->where('zen_cases_associates_data.is_active' , '1');
        $this->db->where('cases_countries.case_id' , $case_id);

        $this->db->where('zen_cases_associates_data.fa_invoice_status IS NOT NULL' , null , null , false);

        $query = $this->db->get('cases_countries');
        $invoices = $query->result();

        foreach($invoices as $key => $invoice) {
            $invoices[$key]->additional_fees = $this->get_case_country_additional_fees_for_invoice(array(
                'cases_associates_data_id' => $invoice->id
            ));
        }
        return $invoices;

    }

    function is_deadline_passed_for_country($case_id , $country_id) {
        $this->db->where('country_id' , $country_id);
        $this->db->where('case_id' , $case_id);
        $query = $this->db->get('zen_cases_tracker');
        $tracker_row = $query->row();

        $date = strtotime($tracker_row->fi_requests_sent_fa);
        $unix_final_date = strtotime($tracker_row->fi_requests_sent_fa . ' + 60 seconds');

        if ($unix_final_date < time()) {
            return true;
        } else {
            return false;
        }
    }
}