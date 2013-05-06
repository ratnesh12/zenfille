<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Wipo_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Adds patent data to DB
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    array    wipo data
     * @return    void
     * */
    public function append_wipo_data($result = NULL)
    {
        log_message('error', json_encode($result));

        $this->db->where('wo_number', $result['wo_number']);
        $this->db->where('pct_number', $result['pct_number']);
        $query = $this->db->get('wipo_data');

        if (check_array($result)) {
            // To avoid error "MySQL has gone away" error we shuld reconnect to database
            $this->db->reconnect();
            $data = array(
                'application_title' => $result['application_title'],
                'number_priorities_claimed' => $result['number_priorities_claimed'],
                'number_pages_drawings' => $result['number_pages_drawings'],
                'number_pages_claims' => $result['number_pages_claims'],
                'number_pages' => $result['number_pages'],
                'first_priority_date' => $result['first_priority_date'],
                'international_filing_date' => $result['international_filing_date'],
                'search_location' => $result['search_location'],
                'applicant' => $result['applicant'],
                'publication_language' => $result['publication_language'],
                '30_month_filing_deadline' => $result['30_month_filing_deadline'],
                '31_month_filing_deadline' => $result['31_month_filing_deadline'],
                'number_claims' => $result['number_claims'],
                'number_words' => $result['number_words'],
                'number_words_claims' => $result['number_words_claims'],
                'number_words_in_application' => $result['number_words_in_application'],
                'sequence_listing' => $result['sequence_listing'],
                'wo_number' => $result['wo_number'],
                'pct_number' => $result['pct_number'],
                'created_at' => date('Y-m-d H:i:s')
            );
            if ($query->num_rows() > 0) {
                $this->db->where('wo_number', $result['wo_number']);
                $this->db->where('pct_number', $result['pct_number']);
                $this->db->update('wipo_data', $data);
            } else {
                $this->db->insert('wipo_data', $data);
            }

        }
    }

    /**
     * Returns patent data entry by WO or PCT number
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string    WO number
     * @param    string    PCT number
     * @return    mixed
     * */
    public function get_wipo_data($wo_number = '', $pct_number = '')
    {
        $this->db->where('wo_number', $wo_number);
        $this->db->or_where('pct_number', $pct_number);

        $query = $this->db->get('wipo_data');
        if ($query->num_rows()) {
            $wipo_data = $query->row_array();
            if ((!empty($wipo_data['applicant'])) || (!empty($wipo_data['application_title']))) {
                return $wipo_data;
            }
        }
        return NULL;
    }
}
