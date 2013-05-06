<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Associates_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /*
     * Returns a list of associates
     *
     * @access	public
     * @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return	mixed
     */
    public function get_all_associates($is_replaced)
    {
        $this->db->select('associates.*, countries.country', FALSE);
        $this->db->join('countries', 'countries.id = associates.country_id');
        // if($is_replaced){
        $this->db->where('is_replaced', $is_replaced);
        // }
        $this->db->order_by('countries.country');
        $query = $this->db->get('associates');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    public function get_all_associates_for_search($case_number)
    {
        $this->load->model('associates_model', 'associates');
        $this->load->model('cases_model', 'cases');
        $case = $this->cases->find_case_by_number($case_number);
        $this->db->select('associates.*, countries.country', FALSE);
        $this->db->join('countries', 'countries.id = associates.country_id');
        if ($case['case_type_id'] == '1') {
            $this->db->where('30_months', '1');
            $this->db->or_where('31_months', '1');
        }
        if ($case['case_type_id'] == '2') {
            $this->db->where('ep_validation', '1');
        }
        if ($case['case_type_id'] == '3') {
            $this->db->where('is_direct_case_allowed', '1');
        }

        $this->db->order_by('countries.country');
        $this->db->group_by('associates.id');
        $query = $this->db->get('associates');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Does search on associates list
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string    search string
     * @return    mixed
     * */
    public function search_associates($search_string = '', $is_replaced = '0')
    {
        $this->db->select('associates.*, countries.country', FALSE);
        $this->db->join('countries', 'countries.id = associates.country_id');
        if ($search_string) {
            $this->db->like('countries.country', $search_string);
        }
        $this->db->where('is_replaced', $is_replaced);
        $this->db->order_by('countries.country');
        $query = $this->db->get('associates');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns an associate entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    associate ID
     * @return    mixed
     **/
    public function get_associate($associate_id)
    {
        $this->db->where('id', $associate_id);
        $query = $this->db->get('associates');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    public function get_associates_for_merge($case_id, $associates)
    {

        $associates = explode(",", $associates);
        //var_dump($associates);exit;
        $this->db->select('associates.name,countries.country,cases_associates_data.*');
        $this->db->join('associates', 'associates.id = cases_associates_data.associate_id');
        $this->db->join('countries', 'associates.country_id = countries.id');
        $this->db->where('case_id', $case_id);
        $this->db->where_in("cases_associates_data.associate_id", $associates);
        $this->db->group_by('cases_associates_data.associate_id');
        $query = $this->db->get('cases_associates_data');
        if ($query->num_rows()) {
            $result = $query->result_array();
        }
        return $result;
    }

    public function merge_associates($case_id, $main_associate, $not_active_associates)
    {
        $this->db->set('is_active', '0');
        $this->db->where('case_id', $case_id);
        $this->db->where_in('associate_id', $not_active_associates);
        $this->db->update('cases_associates_data');

        $this->db->select('country_id');
        $this->db->where('case_id', $case_id);
        $this->db->where_in('associate_id', $not_active_associates);
        $query = $this->db->get('cases_associates_data');

        foreach($query->result_array() as $country){
            $new_associate_data = array(
                'associate_id' => $main_associate,
                'case_id' => $case_id,
                'country_id' => $country['country_id'],
                'is_active' => '1'
            );
            $this->db->insert('cases_associates_data', $new_associate_data);
        }

        $this->db->join('associates', 'associates.id = cases_associates_data.associate_id');
        $this->db->where('cases_associates_data.is_active','0');
        $this->db->where('cases_associates_data.case_id',$case_id);
        $this->db->where('associates.is_replaced','1');
        $query = $this->db->get('cases_associates_data');
        foreach($query->result_array() as $associate){
            $this->db->where('cases_associates_data.case_id',$case_id);
            $this->db->where('cases_associates_data.associate_id',$associate['associate_id']);
            $this->db->delete('cases_associates_data');
        }
    }

    /*
     * Updates an associate
     *
     * @access	public
     * @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param 	int	associated ID
     * @param 	string	filename of GSA
     * @param 	string	filetype
     * @return 	bool
     */

    public function update_associate($associate_record, $associate_id = '')
    {
        if ($associate_id) {
            $this->db->where('id', $associate_id);
            $this->db->update('associates', $associate_record);
            return $associate_id;
        } else {
            $this->db->insert('associates', $associate_record);
            return $this->db->insert_id();
        }
    }

    public function get_last_associate_pdf($case_id = '')
    {
        $this->db->where('case_id', $case_id);
        $this->db->where('file_type_id', 17);
        $this->db->limit(1);
        $this->db->order_by('created_at', 'desc');
        $query = $this->db->get('cases_files');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    public function send_email_access_to_associate($name, $username, $password, $email)
    {

        $this->load->model('send_emails_model', 'send_emails');
        $from = 'portal' . $this->config->item('default_email_box');
        $TEMPLATE = $this->db->get_where("zen_emails_templates", array("id" => '37'))->row_array();
        $login_link = '<a href= "https://' . $_SERVER["HTTP_HOST"] . '/login/"> here:</a>';
        $email_link = '<a href="mailto:' . $from . '">' . $from . '</a>';

        /* REPLACEMENT FOR MESSAGE */
        $TEMPLATE["content"] = str_replace("%LOGIN_LINK%", $login_link, $TEMPLATE["content"]);
        $TEMPLATE["content"] = str_replace("%FA_NAME%", $name, $TEMPLATE["content"]);
        $TEMPLATE["content"] = str_replace("%NEW_LOGIN%", $username, $TEMPLATE["content"]);
        $TEMPLATE["content"] = str_replace("%NEW_PASSWORD%", $password, $TEMPLATE["content"]);
        $TEMPLATE["content"] = str_replace("%PORTAL_EMAIL%", $email_link, $TEMPLATE["content"]);

        //temporary we are sending all emails to test fa email
        //$to = $email;
        //if (TEST_MODE) {
        $to = TEST_FA_EMAIL;
        //}
        $this->send_emails->send_email($from, $from, $TEMPLATE["subject"], $TEMPLATE["content"], $to, false, false);


    }


    /*
     * Deletes an associate
     *
     * @access	public
     * @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param 	int	associate ID
     * @return	bool
     */
    public function delete_associate($associate_id)
    {
        $this->db->where('id', $associate_id);
        $this->db->delete('associates');

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Returns associate entry by country ID
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    country ID
     * @return    mixed
     * */
    public function get_associate_by_country_id($country_id = '')
    {
        $this->db->select('associates.*, countries.country', FALSE);
        $this->db->where('associates.country_id', $country_id);
        $this->db->join('countries', 'countries.id = associates.country_id');
        $query = $this->db->get('associates');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Returns an entry of custom associate
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    associate ID
     * @return    void
     * */
    public function delete_gsa($associate_id = '')
    {
        if (!is_null($associate = $this->get_associate($associate_id))) {
            @unlink($associate['path_to_gsa_agreement']);

            $data = array(
                'path_to_gsa_agreement' => '',
                'gsa_filetype' => ''
            );
            $this->db->where('id', $associate_id);
            $this->db->update('associates', $data);
        }
    }

    /**
     * Returns a list of associate references
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    int    associate ID
     * @return    mixed
     */
    public function get_associate_reference_entry($case_id = '', $associate_id = '')
    {
        $this->db->limit(1);
        $this->db->where('case_id', $case_id);
        $this->db->where('associate_id', $associate_id);
        $query = $this->db->get('cases_associates_data');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Returns a list of associate references
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    associate ID
     * @param    int    case ID
     * @param    bool    replace or not
     * @return    bool
     */
    public function insert_associate_reference($associate_id = '', $case_id = '')
    {
        $reference_number = $this->input->post('reference_number');
        $this->db->set('reference_number', $reference_number);
        $this->db->where('associate_id', $associate_id);
        $this->db->where('case_id', $case_id);
        $this->db->update('cases_associates_data');
        return true;
    }

    /**
     * enable associate for current country
     *
     * @access    public
     * @author    Stan Voinov <stan.voinov@gmail.com>
     * @param    int CASE_ID
     * @param    int ASSOC_ID
     * @return    mixed
     */
    function enable_disable_associate($case_id, $associate_id, $active)
    {
        $this->db->set('is_active', $active);
        $this->db->where('case_id', $case_id);
        $this->db->where('associate_id', $associate_id);
        $this->db->update('cases_associates_data');
        return $this->db->affected_rows();
    }

    public function get_all_associates_by_case_type_and_countris_array($countries, $case_type)
    {
        $this->db->where('is_replaced', '0');
        $this->db->where_in('country_id', $countries);
        if ($case_type == '1') {
            $this->db->where("(zen_associates.31_months = '1' OR zen_associates.30_months = '1')", NULL, false);
        }
        if ($case_type == '2') {
            $this->db->where('associates.ep_validation', '1');
        }
        if ($case_type == '3') {
            $this->db->where('associates.is_direct_case_allowed', '1');
        }
        $query = $this->db->get('associates');
        if ($query->num_rows()) {
            return $query->result_array();
        }
    }

    public function add_new_associates_to_case($case_number, $array_of_country_id, $is_approved = '')
    {
        $this->load->model('cases_model', 'cases');
        $case = $this->cases->find_case_by_number($case_number);
        if ($case['is_intake'] == '1' || $case['common_status'] == 'pending-intake' || $case['common_status'] == 'active' || $is_approved == '1') {
            $this->insert_new_associates_to_case_associates_data($case['id'], $array_of_country_id, $case['case_type_id']);
        }
    }

    public function insert_new_associates_to_case_associates_data($case_id, $countries_id, $case_type_id)
    {
        $associates_list = $this->get_all_associates_by_case_type_and_countris_array($countries_id, $case_type_id);
        if ($associates_list) {
            $associate_array = array();
            foreach ($associates_list as $associate) {
                $associate_array[] = array(
                    'case_id' => $case_id,
                    'country_id' => $associate['country_id'],
                    'associate_id' => $associate['id'],
                    'reference_number' => $associate['reference_number'],
                    'is_active' => '1'
                );
            }
            $this->db->insert_batch('cases_associates_data', $associate_array);
        }
    }

    public function new_get_all_case_associates($case_id, $is_active = '')
    {

        $this->db->select("
            countries.country,countries.flag_image, associates.associate, associates.email, associates.fee_currency,
            associates.fee,associates.translation_required, associates.is_replaced ,cases_associates_data.*,
            associates.name, associates.address, associates.address2, associates.firm, associates.phone,
            associates.fax, associates.website , cases_tracker.translation_required as tracker_translation_required
        ");
        $this->db->join('countries', 'countries.id = cases_associates_data.country_id');
        $this->db->join('associates', 'associates.id = cases_associates_data.associate_id');

        $this->db->join('zen_cases_tracker', 'zen_cases_tracker.case_id = zen_cases_associates_data.case_id AND zen_cases_tracker.country_id = zen_cases_associates_data.country_id');

        $this->db->where('cases_associates_data.case_id', $case_id);
        if ($is_active) {
            $this->db->where('cases_associates_data.is_active', $is_active);
        }
        $this->db->order_by('countries.country', 'asc');
        $query = $this->db->get('cases_associates_data');
        //echo $this->db->last_query();exit;
        return $query->result_array();

    }

    public function delete_associates_from_case_by_country_id($case_id, $country_id)
    {
        $this->db->where('case_id', $case_id);
        $this->db->where('country_id', $country_id);
        $this->db->delete('cases_associates_data');
    }

    public function fa_translation_assosiates_for_emails($case_id)
    {
        $this->db->select('countries.country,countries.flag_image, associates.associate, associates.email, associates.fee_currency, associates.fee,associates.translation_required, associates.is_replaced ,cases_associates_data.*');
        $this->db->join('countries', 'countries.id = cases_associates_data.country_id');
        $this->db->join('associates', 'associates.id = cases_associates_data.associate_id');
        $this->db->join('cases_tracker', 'cases_associates_data.country_id = cases_tracker.country_id AND zen_cases_associates_data.case_id = zen_cases_tracker.case_id');
        $this->db->where('cases_associates_data.case_id', $case_id);
        $this->db->where('cases_associates_data.is_active', '1');
        $this->db->where('cases_tracker.translation_required', '1');
        $this->db->order_by('countries.country', 'asc');
        $query = $this->db->get('cases_associates_data');
        return $query->result_array();
    }

    function rand_string($length)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }
}

?>