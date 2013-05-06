<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Fees_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns all fee entries
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     */
    public function get_all_records()
    {
        $this->db->select('fees.*, countries.country', FALSE);
        $this->db->join('countries', 'countries.id = fees.country_id');
        $this->db->group_by('country_id');
        $this->db->order_by('countries.country');
        $query = $this->db->get('fees');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Does search on fees
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     */
    public function search_fees($search_string = '')
    {
        $this->db->select('fees.*, countries.country', FALSE);
        $this->db->join('countries', 'countries.id = fees.country_id');
        $this->db->group_by('country_id');
        $this->db->order_by('countries.country');
        $this->db->like('countries.country', $search_string);
        $query = $this->db->get('fees');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns a fee entry by ID
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    fee entry ID
     * @return    mixed
     */
    public function get_fee($fee_id)
    {
        $this->db->select('fees.*, countries.country', FALSE);
        $this->db->join('countries', 'countries.id = fees.country_id');
        $this->db->where('fees.id', $fee_id);
        $query = $this->db->get('fees');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Returns a fee entry by country ID
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    country ID
     * @return    mixed
     */
    public function get_fee_by_country_id($country_id)
    {
        $this->db->where('country_id', $country_id);
        $this->db->order_by('case_type_id');
        $query = $this->db->get('fees');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Returns a fee entry by country ID
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    country ID
     * @param    int    case type ID
     * @return    mixed
     */
    public function get_fee_by_country_id_and_case_type_id($country_id, $case_type_id = 1)
    {
        $this->db->where('country_id', $country_id);
        $this->db->where('case_type_id', $case_type_id);
        $this->db->order_by('case_type_id');
        $query = $this->db->get('fees');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Inserts a new fee for country
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    bool
     */
    public function insert_fee()
    {
        $country_id = $this->input->post('country_id');
        if (is_null($this->get_fee_by_country_id($country_id))) {
            $data = array();
            $fee_types = array(
                'pct' => 1,
                'ep' => 2,
                'direct' => 3
            );
            foreach ($fee_types as $key => $case_type_id)
            {
                $filing_fee_level_1 = $this->input->post($key . '_filing_fee_level_1');
                $filing_fee_level_2 = $this->input->post($key . '_filing_fee_level_2');
                $filing_fee_level_3 = $this->input->post($key . '_filing_fee_level_3');
                $translation_rate_level_1 = $this->input->post($key . '_translation_rate_level_1');
                $translation_rate_level_2 = $this->input->post($key . '_translation_rate_level_2');
                $translation_rate_level_3 = $this->input->post($key . '_translation_rate_level_3');
                $official_fee_large = $this->input->post($key . '_official_fee_large');
                $official_fee_small = $this->input->post($key . '_official_fee_small');
                $official_fee_individual = $this->input->post($key . '_official_fee_individual');

                $translation_rates_for_claims = $this->input->post($key . '_translation_rates_for_claims');
                $request_examination = $this->input->post($key . '_request_examination');
                $number_claims_above_additional_fees = $this->input->post($key . '_number_claims_above_additional_fees');
                $fee_additional_claims = $this->input->post($key . '_fee_additional_claims');
                $additional_fee_for_claims = $this->input->post($key . '_additional_fee_for_claims');
                $number_pages_above_additional_fees = $this->input->post($key . '_number_pages_above_additional_fees');
                $fee_additional_pages = $this->input->post($key . '_fee_additional_pages');
                $number_priorities_claimed_with_no_additional_charge = $this->input->post($key . '_number_priorities_claimed_with_no_additional_charge');
                $charge_per_additional_claimed = $this->input->post($key . '_charge_per_additional_claimed');
                $number_free_pages_drawing = $this->input->post($key . '_number_free_pages_drawing');
                $claim_number_threshold_for_additional_fee = $this->input->post($key . '_claim_number_threshold_for_additional_fee');
                $charge_per_additional_pages_of_drawing = $this->input->post($key . '_charge_per_additional_pages_of_drawing');
                $page_number_treshold_for_additional_fee = $this->input->post($key . '_page_number_treshold_for_additional_fee');
                $extension_needed_fee = $this->input->post($key . '_extension_needed_fee');
                $sequence_listing_fee = $this->input->post($key . '_sequence_listing_fee');
                $additional_fee_above_treshold = $this->input->post($key . '_additional_fee_above_treshold');

                $data[] = array(
                    'country_id' => $country_id,
                    'case_type_id' => $case_type_id,
                    'filing_fee_level_1' => $filing_fee_level_1,
                    'filing_fee_level_2' => $filing_fee_level_2,
                    'filing_fee_level_3' => $filing_fee_level_3,
                    'translation_rate_level_1' => $translation_rate_level_1,
                    'translation_rate_level_2' => $translation_rate_level_2,
                    'translation_rate_level_3' => $translation_rate_level_3,

                    'official_fee_large' => $official_fee_large,
                    'official_fee_small' => $official_fee_small,
                    'official_fee_individual' => $official_fee_individual,

                    'translation_rates_for_claims' => $translation_rates_for_claims,
                    'sequence_listing_fee' => $sequence_listing_fee,
                    'request_examination' => $request_examination,
                    'number_claims_above_additional_fees' => $number_claims_above_additional_fees,
                    'fee_additional_claims' => $fee_additional_claims,
                    'number_priorities_claimed_with_no_additional_charge' => $number_priorities_claimed_with_no_additional_charge,
                    'charge_per_additional_claimed' => $charge_per_additional_claimed,
                    'charge_per_additional_pages_of_drawing' => $charge_per_additional_pages_of_drawing,
                    'number_free_pages_drawing' => $number_free_pages_drawing,
                    'claim_number_threshold_for_additional_fee' => $claim_number_threshold_for_additional_fee,
                    'page_number_treshold_for_additional_fee' => $page_number_treshold_for_additional_fee,
                    'extension_needed_fee' => $extension_needed_fee,
                    'sequence_listing_fee' => $sequence_listing_fee,
                    'additional_fee_for_claims' => $additional_fee_for_claims,
                    'fee_additional_pages' => $fee_additional_pages,
                    'number_pages_above_additional_fees' => $number_pages_above_additional_fees,
                    'additional_fee_above_treshold' => $additional_fee_above_treshold,
                );
            }

            $this->db->insert_batch('fees', $data);

            return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
        }
        return FALSE;
    }

    /*
     * Updates fee entry
     *
     * @access	public
     * @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param	int	country ID
     * @return	bool
     */
    public function update_fee($country_id)
    {
        $data = array();
        $fee_types = array(
            'pct' => 1,
            'ep' => 2,
            'direct' => 3
        );
        foreach ($fee_types as $key => $case_type_id)
        {
            $filing_fee_level_1 = $this->input->post($key . '_filing_fee_level_1');
            $filing_fee_level_2 = $this->input->post($key . '_filing_fee_level_2');
            $filing_fee_level_3 = $this->input->post($key . '_filing_fee_level_3');
            $translation_rate_level_1 = $this->input->post($key . '_translation_rate_level_1');
            $translation_rate_level_2 = $this->input->post($key . '_translation_rate_level_2');
            $translation_rate_level_3 = $this->input->post($key . '_translation_rate_level_3');
            $official_fee_large = $this->input->post($key . '_official_fee_large');
            $official_fee_small = $this->input->post($key . '_official_fee_small');
            $official_fee_individual = $this->input->post($key . '_official_fee_individual');
            $translation_rates_for_claims = $this->input->post($key . '_translation_rates_for_claims');
            $exchange_rate = $this->input->post($key . '_exchange_rate');

            $sequence_listing_fee = $this->input->post($key . '_sequence_listing_fee');
            $request_examination = $this->input->post($key . '_request_examination');
            $number_claims_above_additional_fees = $this->input->post($key . '_number_claims_above_additional_fees');
            $fee_additional_claims = $this->input->post($key . '_fee_additional_claims');
            $additional_fee_for_claims = $this->input->post($key . '_additional_fee_for_claims');
            $number_pages_above_additional_fees = $this->input->post($key . '_number_pages_above_additional_fees');
            $fee_additional_pages = $this->input->post($key . '_fee_additional_pages');
            $number_priorities_claimed_with_no_additional_charge = $this->input->post($key . '_number_priorities_claimed_with_no_additional_charge');
            $charge_per_additional_claimed = $this->input->post($key . '_charge_per_additional_claimed');
            $number_free_pages_drawing = $this->input->post($key . '_number_free_pages_drawing');
            $claim_number_threshold_for_additional_fee = $this->input->post($key . '_claim_number_threshold_for_additional_fee');
            $charge_per_additional_pages_of_drawing = $this->input->post($key . '_charge_per_additional_pages_of_drawing');
            $page_number_treshold_for_additional_fee = $this->input->post($key . '_page_number_treshold_for_additional_fee');
            $extension_needed_fee = $this->input->post($key . '_extension_needed_fee');
            $additional_fee_above_treshold = $this->input->post($key . '_additional_fee_above_treshold');
            $sequence_listing_fee = $this->input->post($key . '_sequence_listing_fee');

            $data = array(
                'country_id' => $country_id,
                'case_type_id' => $case_type_id,
                'filing_fee_level_1' => $filing_fee_level_1,
                'filing_fee_level_2' => $filing_fee_level_2,
                'filing_fee_level_3' => $filing_fee_level_3,
                'translation_rate_level_1' => $translation_rate_level_1,
                'translation_rate_level_2' => $translation_rate_level_2,
                'translation_rate_level_3' => $translation_rate_level_3,
                'official_fee_large' => $official_fee_large,
                'official_fee_small' => $official_fee_small,
                'official_fee_individual' => $official_fee_individual,
                'translation_rates_for_claims' => $translation_rates_for_claims,
                'sequence_listing_fee' => $sequence_listing_fee,
                'request_examination' => $request_examination,
                'number_claims_above_additional_fees' => $number_claims_above_additional_fees,
                'fee_additional_claims' => $fee_additional_claims,
                'number_priorities_claimed_with_no_additional_charge' => $number_priorities_claimed_with_no_additional_charge,
                'charge_per_additional_claimed' => $charge_per_additional_claimed,
                'charge_per_additional_pages_of_drawing' => $charge_per_additional_pages_of_drawing,
                'number_free_pages_drawing' => $number_free_pages_drawing,
                'claim_number_threshold_for_additional_fee' => $claim_number_threshold_for_additional_fee,
                'page_number_treshold_for_additional_fee' => $page_number_treshold_for_additional_fee,
                'extension_needed_fee' => $extension_needed_fee,
                'additional_fee_for_claims' => $additional_fee_for_claims,
                'fee_additional_pages' => $fee_additional_pages,
                'number_pages_above_additional_fees' => $number_pages_above_additional_fees,
                'additional_fee_above_treshold' => $additional_fee_above_treshold
            );
            $this->db->where('case_type_id', $case_type_id);
            $this->db->where('country_id', $country_id);
            $this->db->update('fees', $data);
        }
        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /*
     * Deletes a fee entry
     *
     * @access	public
     * @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param	int	country ID
     * @return	bool
     */
    public function delete_fee($country_id)
    {
        $this->db->where('country_id', $country_id);
        $this->db->delete('fees');

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Returns a list of fees sorted by countries
     *
     * The result should be the next array:
    array(
    <country_id> = array('filing_fee' 		=> <value>,
    'official_fee' 	=> <value>,
    'translation_fee' 	=> <value>)
    );
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     */
    public function get_fees_by_countries($countries = array(), $level = 1)
    {
        $result = array();
        $countries_ids = array(-1);
        $this->db->select('*, filing_fee_level_' . $level . ' as filing_fee, translation_rate_level_' . $level . ' as translation_fee', FALSE);

        if (check_array($countries)) {
            foreach ($countries as $country)
            {
                $countries_ids[] = $country['id'];
                $result[$country['id']] = array(
                    'filing_fee' => 0,
                    'official_fee' => 0,
                    'translation_fee' => 0,
                );
            }
        }
        asort($countries_ids);
        $this->db->where_in('country_id', $countries_ids);
        $query = $this->db->get('fees');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $item)
            {
                $result[$item['country_id']] = $item;
            }
            return $result;
        }
        return NULL;
    }
}

?>