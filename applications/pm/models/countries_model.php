<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Countries_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns a list of US states
     *
     * @access    public
     * @return    mixed
     * */
    public function get_us_states()
    {
        $this->db->order_by('state');
        $query = $this->db->get('us_states');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /*
    * Returns a list of available countries
    *
    * @access	public
    * @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
    * @return	mixed
    */
    public function get_all_countries()
    {
        $this->db->order_by('country');
        $query = $this->db->get('countries');
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
     * @param    string    case type
     * @param    int    case ID
     * @return    mixed
     */
    public function get_countries_list($type = '', $case_id = '')
    {
        $this->db->select('countries.*, cases_countries.is_enabled, (CASE WHEN zen_cases_countries.country_id > 0 THEN "1" ELSE "0" END) as selected', FALSE);
        $this->db->join('cases_countries', 'cases_countries.country_id = countries.id', 'left');
        $this->db->where('countries.' . $type, 1);
        $this->db->where('cases_countries.case_id', $case_id);
        $this->db->order_by('countries.country');
        $query = $this->db->get('countries');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns a list of countries by case type
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string    case type
     * @param    bool    witout favourite countries or not
     * @param    int    if need common countries then 1, otherwise - 0
     * @param    bool    use previous parameter or not
     * @param    bool    use statement for "primary"  field
     * @return    mixed
     */
    public function get_countries_list_by_type($type = 'pct', $common = 0)
    {

        $common_statement_where = '(c.`common-' . $type . '` = "' . $common . '")';
        $q = 'SELECT c.*
				  FROM `zen_countries` c
				  WHERE (c.`' . $type . '` = "1") AND
				  		' . $common_statement_where . '
				  ORDER BY c.country';

        $query = $this->db->query($q);

        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns a list of countries by their IDs
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    array    array of IDs
     * @return    mixed
     */
    public function get_countries_by_id($ids_array = array())
    {
        $this->db->where_in('id', $ids_array);
        $query = $this->db->get('countries');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    public function get_case_countries($case_id, $get_cases_deleted_by_user = true)
    {
        $this->db->select('*, countries.country, countries.code', FALSE);

        if (!$get_cases_deleted_by_user) {
            $this->db->join('estimates_countries_fees', 'estimates_countries_fees.country_id = cases_countries.country_id');
            $this->db->where('estimates_countries_fees.is_disabled_by_client', '0');
            $this->db->where('estimates_countries_fees.case_id', $case_id);
        }
        $this->db->join('countries', 'countries.id = cases_countries.country_id');
        $this->db->group_by('cases_countries.country_id');
        $this->db->order_by("countries.country", "asc");
        $query = $this->db->get('cases_countries');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns a country entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    country ID
     * @return    mixed
     */
    public function get_country($country_id)
    {
        $this->db->where('id', $country_id);
        $query = $this->db->get('countries');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Returns a list of common countries
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string    case type
     * @param    int    user ID
     * @return    mixed
     */

    public function get_common_countries($type = 'pct', $parent_case_id = "")
    {
        if (!empty($parent_case_id)) {
            $q = "SELECT *
                FROM `zen_countries`  as c
                WHERE `c`.`id` NOT IN
                    (
                        SELECT DISTINCT `zen_countries`.`id`
                        FROM `zen_countries` LEFT JOIN `zen_cases_countries`
                        ON `zen_countries`.`id` = `zen_cases_countries`.`country_id`
                        WHERE
                        `zen_cases_countries`.`case_id` = $parent_case_id || case_id IN (
                            SELECT child_case_id as id
                            FROM zen_related_cases
                            JOIN zen_cases ON `zen_cases`.`id` = `zen_related_cases`.`child_case_id`
                            WHERE zen_cases.common_status != 'hidden' AND related_hidden = 1 AND parent_case_id = {$parent_case_id}
                        )
                    )


                 AND `c`.`" . $type . "`='1' AND `c`.`common-" . $type . "`='1' ORDER BY c.country ASC ";
        }
        else
        {
            $q = 'SELECT c.*
			  FROM `zen_countries` c
			  WHERE (c.`' . $type . '` = "1") AND
			  		(c.`common-' . $type . '` = "1")
			  ORDER BY c.country ASC';
        }
        $query = $this->db->query($q);

        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns a currency rate by currency code
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string currency code
     * @return    float
     */
    public function get_currency_rate_by_code($code = 'EUR')
    {
        $this->db->where('code', $code);
        $query = $this->db->get('currencies_rates');
        if ($query->num_rows()) {
            $record = $query->row_array();
            return $record['rate'];
        }
        return '1';
    }


    public function json_search_countries($search_string = '', $case_type = '')
    {
        $result = array();
        $this->db
            ->select('id, flag_image, country, LOWER(code) as code');
        $this->db->from('countries');
        $this->db->where("country LIKE '" . $search_string . "%'");
        if ($case_type == 'ep') {
            $this->db->where("(`ep-validation` = '1' OR `common-ep-validation` = '1')");
        }
        elseif ($case_type == 'pct') {
            $this->db->where("(`common-pct` = '1' OR `pct` = '1')");
        }
        elseif ($case_type == 'direct') {
            $this->db->where("(`common-direct-filing` = '1' OR `direct-filing` = '1')");
        }

        $this->db->order_by('country');

        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $key => $country)
            {
                $result[$key]['value'] = $country['country'];
                $result[$key]['flag'] = $country['flag_image'];
                $result[$key]['id'] = $country['id'];
                $result[$key]['cssClass'] = strtolower($country['code']);
            }
        }

        return $result;
    }


    public function json_search_countries_for_related($parent_case_id = '', $type = '')
    {
        $q = "SELECT *
                FROM `zen_countries`  as c
                WHERE (`c`.`" . $type . "`='1' OR `c`.`common-" . $type . "`='1')
                AND `c`.`id` NOT IN
                    (
                        SELECT DISTINCT `zen_countries`.`id`
                        FROM `zen_countries` LEFT JOIN `zen_cases_countries`
                        ON `zen_countries`.`id` = `zen_cases_countries`.`country_id`
                        WHERE
                        `zen_cases_countries`.`case_id` = $parent_case_id
                    )
                   ORDER BY c.country ASC ";
        $query = $this->db->query($q);
        if ($query->num_rows()) {
            foreach ($query->result_array() as $key => $country)
            {
                $result[$key]['value'] = $country['country'];
                $result[$key]['flag'] = $country['flag_image'];
                $result[$key]['id'] = $country['id'];
                $result[$key]['cssClass'] = strtolower($country['code']);
            }
        }

        return $result;
    }

    public function get_iso_data($term = '')
    {
        $result = array();

        $this->db->like('printable_name', $term);
        $query = $this->db->get('all_countries');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $entry)
            {
                $result[] = array(
                    'value' => $entry['printable_name'],
                    'id' => $entry['iso']
                );
            }
        }

        return $result;
    }

    /*
    * Inserts a country
    *
    * @access	public
    * @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
    * @return	void
    */
    public function insert_country()
    {
        $country = $this->input->post('country');
        $pct_language = $this->input->post('pct_language');
        $ep_language = $this->input->post('ep_language');
        $direct_language = $this->input->post('direct_language');
        $code = $this->input->post('code');
        $currency_code = $this->input->post('currency_code');
        $deadline = $this->input->post('filling-deadline');

        $pct_intake_val = '0';
        $pct_intake = $this->input->post('pct');
        if (!empty($pct_intake)) {
            $pct_intake_val = '1';
        }

        $ep_validation_val = '0';
        $ep_validation = $this->input->post('ep-validation');
        if (!empty($ep_validation)) {
            $ep_validation_val = '1';
        }

        $direct_filing_val = '0';
        $direct_filing = $this->input->post('direct-filing');
        if (!empty($direct_filing)) {
            $direct_filing_val = '1';
        }

        $data = array(
            'country' => $country,
            'code' => $code,
            'country_filing_deadline' => $deadline,
            'pct_language' => $pct_language,
            'ep_language' => $ep_language,
            'direct_language' => $direct_language,
            'currency_code' => $currency_code,
            'direct-filing' => $direct_filing_val,
            'ep-validation' => $ep_validation_val,
            'pct' => $pct_intake_val,
            'pct_language' => $pct_language,
            'ep_language' => $ep_language,
            'direct_language' => $direct_language,
            'target_language' => $this->input->post('target_language'),
        );

        $this->db->insert('countries', $data);
        return $this->db->insert_id();
    }

    /*
     * Updates country entry
     *
     * @access	public
     * @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param	int	country ID
     * @param	string	path to country flag
     * @return	void
     */
    public function update_country($country_id = '', $flag_image = '')
    {
        $code = $this->input->post('code');
        $currency_code = $this->input->post('currency_code');
        $deadline = $this->input->post('filling-deadline');

        $pct_intake_val = '0';
        $pct_intake = $this->input->post('pct');
        if (!empty($pct_intake)) {
            $pct_intake_val = '1';
        }

        $ep_validation_val = '0';
        $ep_validation = $this->input->post('ep-validation');
        if (!empty($ep_validation)) {
            $ep_validation_val = '1';
        }

        $direct_filing_val = '0';
        $direct_filing = $this->input->post('direct-filing');
        if (!empty($direct_filing)) {
            $direct_filing_val = '1';
        }

        $common_pct_intake_val = '0';
        $common_pct_intake = $this->input->post('common-pct');
        if (!empty($common_pct_intake)) {
            $common_pct_intake_val = '1';
        }

        $common_ep_validation_val = '0';
        $common_ep_validation = $this->input->post('common-ep-validation');
        if (!empty($common_ep_validation)) {
            $common_ep_validation_val = '1';
        }

        $common_direct_filing_val = '0';
        $common_direct_filing = $this->input->post('common-direct-filing');
        if (!empty($common_direct_filing)) {
            $common_direct_filing_val = '1';
        }

        $pct_language = $this->input->post('pct_language');
        $ep_language = $this->input->post('ep_language');
        $direct_language = $this->input->post('direct_language');

        $data = array(
            'code' => $code,
            'currency_code' => $currency_code,

            'common-pct' => $common_pct_intake_val,
            'common-ep-validation' => $common_ep_validation_val,
            'common-direct-filing' => $common_direct_filing_val,

            'direct-filing' => $direct_filing_val,
            'ep-validation' => $ep_validation_val,
            'pct' => $pct_intake_val,
            'pct_language' => $pct_language,
            'ep_language' => $ep_language,
            'direct_language' => $direct_language,
            'target_language' => $this->input->post('target_language'),
            'country_filing_deadline' => $deadline,
        );
        if ($flag_image) {
            $data['flag_image'] = $flag_image;
        }
        $this->db->where('id', $country_id);
        $this->db->update('countries', $data);
    }

}
