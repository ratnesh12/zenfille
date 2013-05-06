<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Customers_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /*
     * Returns a list of customers
     *
     * @access	public
     * @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return	mixed
     */
    public function get_all_customers()
    {
        $this->db->select('customers.*, CONCAT(zen_managers.firstname, " ", zen_managers.lastname) as bdv', FALSE);
        $this->db->join('managers', 'managers.id = customers.bdv_id', 'left');
        $this->db->where('customers.is_deleted', '0');
        $query = $this->db->get('customers');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Does search on customers
     *
     * @access    public
     * @author    Sergey Koshkarev  <koshkarev.ss@gmail.com>
     * @param    string    company name
     * @return    mixed
     */
    public function search_customers($search_string = '')
    {
        $this->db->select('customers.*, CONCAT(zen_managers.firstname, " ", zen_managers.lastname) as bdv', FALSE);
        $this->db->join('managers', 'managers.id = customers.bdv_id', 'left');
        $this->db->like('customers.company_name', $search_string);
        $this->db->where('customers.is_deleted', '0');
        $query = $this->db->get('customers');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /*
     * Inserts a new customer
     *
     * @access	public
     * @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return	bool
     */
    public function insert_customer()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $email = $this->input->post('email');
        $firstname = $this->input->post('firstname');
        $lastname = $this->input->post('lastname');
        $company_name = $this->input->post('company_name');
        $address = $this->input->post('address');
        $address2 = $this->input->post('address2');
        $city = $this->input->post('city');
        $state = $this->input->post('state');
        $zip_code = $this->input->post('zip_code');
        $country = $this->input->post('country');
        $phone_number = $this->input->post('phone_number');
        $ext = $this->input->post('ext');
        $fax = $this->input->post('fax');
        $bdv = $this->input->post('bdv');

        $customer = array(
            'username' => $username,
            'password' => md5(sha1($password)),
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'company_name' => $company_name,
            'address' => $address,
            'address2' => $address2,
            'city' => $city,
            'state' => $state,
            'zip_code' => $zip_code,
            'country' => $country,
            'phone_number' => $phone_number,
            'phone_country_code' => $this->input->post('phone_country'),
            'ext' => $ext,
            'fax' => $fax,
            'bdv_id' => $bdv,
            'allow_email' => $this->input->post('allow_email'),
            'type' => $this->input->post('type'),
            'parent_firm_id' => $this->input->post('parent_firm_id'),
            'manager_id' => $this->input->post('manager_id')
        );

        $this->db->insert('customers', $customer);

        $user_id = $this->db->insert_id();
        // Insert countries fees
        $query = $this->db->get('fees');
        $data = array();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $master_fee_record)
            {
                $data[] = array(
                    'user_id' => $user_id,
                    'country_id' => $master_fee_record['country_id'],
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
                    'translation_rates_for_claims' => $master_fee_record['translation_rates_for_claims'],
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
            }
        }

        if (count($data) > 0) {
            $this->db->insert_batch('customers_fees', $data);
        }

        return ($this->db->affected_rows() > 0) ? $user_id : FALSE;
    }

    /*
     * Updates a customer entry
     *
     * @access	public
     * @param	int	customer ID
     * @return	bool
     */
    public function update_customer($customer_id)
    {



        $customer = array(
            'username' => $this->input->post('username'),
            'is_disable_tooltips' => $this->input->post('is_disable_tooltips'),
            'email' => $this->input->post('email'),
            'firstname' => $this->input->post('firstname'),
            'lastname' => $this->input->post('lastname'),
            'company_name' => $this->input->post('company_name'),
            'address' => $this->input->post('address'),
            'address2' => $this->input->post('address2'),
            'city' => $this->input->post('city'),
            'state' => $this->input->post('state'),
            'zip_code' => $this->input->post('zip_code'),
            'country' => $this->input->post('country'),
            'phone_number' => $this->input->post('phone_number'),
            'phone_country_code' => $this->input->post('phone_country_code'),
            'ext' => $this->input->post('ext'),
            'fax' => $this->input->post('fax'),
            'bdv_id' => $this->input->post('bdv'),
            'allow_email' => $this->input->post('allow_email'),
            'type' => $this->input->post('type'),
            'parent_firm_id' => $this->input->post('parent_firm_id'),
            'manager_id' => $this->input->post('manager_id')
        );
        if ($this->input->post('password')) {
            $customer['password'] = md5(sha1($this->input->post('password')));
        }
        if ($this->input->post('blocked')) {
            $customer['blocked'] = $this->input->post('blocked');
        } else {
            $customer['blocked'] = "0";
        }

        if ($this->input->post('blocked') != '1') {
            $customer['login_attempts'] = 0;
        }


        $this->db->where('id', $customer_id);
        $this->db->update('customers', $customer);

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    public function set_deleted($customer_id)
    {
        $this->db->where('id', $customer_id);
        $this->db->set('is_deleted', '1');
        $this->db->set('blocked', '1');
        $this->db->update('customers');
    }

    public function get_managers($manager_id = '')
    {
        if ($manager_id) {
            $this->db->where('id', $manager_id);
        } else {
            $this->db->where('type', 'pm');
            $this->db->or_where('type', 'supervisor');
        }
        $query = $this->db->get('managers');
        if ($manager_id) {
            return $query->row_array();
        }
        return $query->result_array();
    }

    public function get_firms()
    {
        $this->db->where('type', 'firm');
        $query = $this->db->get('customers');
        return $query->result_array();
    }

    public function get_user($user_id, $type = 'customer')
    {
        $this->db->where('id', $user_id);
        if ($type == 'customer' || $type == 'firm') {
            $query = $this->db->get('customers');
        }
        else
        {
            $query = $this->db->get('managers');
        }
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Does search on customers by case number
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    mixed
     * */
    public function find_customer_by_case_number($case_number = '')
    {
        $this->db->select('customers.*', FALSE);
        $this->db->join('cases', 'cases.user_id = customers.id');
        $this->db->where('cases.case_number', $case_number);
        $this->db->limit('1');
        $query = $this->db->get('customers');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Gets count cases
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    bool    count estimate or not
     */
    public function get_cases_count($customer_id, $estimates = TRUE, $active = false)
    {
        if ($estimates) {
            $this->db->where('is_intake', '0');
        }
        else
        {
            $this->db->where('is_intake', '1');
        }
        if ($active == true)
            $this->db->where('is_active', '1');

        $this->db->where('user_id', $customer_id);
        $this->db->from('cases');
        return $this->db->count_all_results();
    }

    /**
     * Returns BDV entry for client
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    customer ID
     * @return    mixed
     */
    public function get_bdv_for_customer($customer_id = '')
    {
        $this->db->where('id', $customer_id);
        $query = $this->db->get('customers');
        if ($query->num_rows()) {
            $record = $query->row_array();
            return (!empty($record['bdv_id'])) ? $record['bdv_id'] : NULL;
        }
        return NULL;
    }

    /**
     * Returns a list of customers and companies names
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    exclude customer ID from SQL
     * @return    array
     * */
    public function get_client_companies_array($excluding_id = '')
    {
        $result = array();

        $this->db->select('id, firstname, lastname, company_name');
        if (!empty($excluding_id)) {
            $this->db->where('id != ' . $excluding_id, FALSE, FALSE);
        }
        $query = $this->db->get('customers');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $customer)
            {
                $result[] = array(
                    'id' => $customer['id'],
                    'company_name' => $customer['company_name']);
            }
        }

        return $result;
    }

    /**
     * Returns a list of case contacts by given case id
     *
     * @access    public
     * @param    int
     * @return    array|null
     * */
    public function get_case_contacts($case_id = '')
    {
        $this->db->where('case_id', $case_id);
        $query = $this->db->get('case_contacts');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    function create_customer($data)
    {

        $parent_firm_id = $this->session->userdata('client_user_id');
        $parent_data = $this->get_user($parent_firm_id, 'customer');
        $customer = array(
            'username' => $data['username'],
            'password' => md5(sha1($data['password'])),
            'email' => $data['email'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'company_name' => $parent_data['company_name'],
            'address' => $parent_data['address'],
            'address2' => $parent_data['address2'],
            'city' => $parent_data['city'],
            'state' => $parent_data['state'],
            'zip_code' => $parent_data['zip_code'],
            'country' => $parent_data['country'],
            'phone_number' => $parent_data['phone_number'],
            'phone_country_code' => $parent_data['phone_country_code'],
            'ext' => $parent_data['ext'],
            'fax' => $parent_data['fax'],
            'bdv_id' => $parent_data['bdv_id'],
            'allow_email' => $parent_data['allow_email'],
            'type' => 'customer',
            'parent_firm_id' => $parent_firm_id,
            'manager_id' => $parent_data['manager_id']
        );
        $this->db->insert('customers', $customer);

    }

    function check_username($username)
    {

        $this->db->where('username', $username);
        $query = $this->db->get('customers');
        if ($query->num_rows()) {
            return false;
        }
        $this->db->where('username', $username);
        $query = $this->db->get('managers');
        if ($query->num_rows()) {
            return false;
        }

        $this->db->where('username', $username);
        $query = $this->db->get('associates');
        if ($query->num_rows()) {
            return false;
        }
        return true;
    }

    function check_email($email)
    {

        $this->db->where('email', $email);
        $query = $this->db->get('customers');
        if ($query->num_rows()) {
            return false;
        }
        $this->db->where('email', $email);
        $query = $this->db->get('managers');
        if ($query->num_rows()) {
            return false;
        }
        $this->db->like('email', $email, 'both');
        $query = $this->db->get('associates');
        if ($query->num_rows()) {
            return false;
        }
        return true;
    }

    function get_sales_manager($user_id)
    {
        $this->db->select('managers.*');
        $this->db->join('managers', 'managers.id = customers.bdv_id');
        $this->db->where('customers.id', $user_id);
        $query = $this->db->get('customers');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    function get_user_manager($user_id)
    {
        $this->db->select('managers.*');
        $this->db->join('managers', 'managers.id = customers.manager_id');
        $this->db->where('customers.id', $user_id);
        $query = $this->db->get('customers');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }
}

?>