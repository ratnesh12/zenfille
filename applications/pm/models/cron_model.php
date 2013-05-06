<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cron_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Updates last case number via cron task
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    value to increase case number
     * @return    bool
     */
    public function update_last_case_number($increase_value = 13)
    {
        $q = 'SELECT MAX(`case_number`) as last_case_number FROM `zen_cases`';
        $query = $this->db->query($q);
        $last_case_number = $query->row_array();
        $case_number = $last_case_number["last_case_number"] + $increase_value;
        $insert_draft_case = array(
            'is_active' => '0',
            'case_number' => $case_number,
        );
        $this->db->insert('cases', $insert_draft_case);

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    public function cron_removedir($path)
    {
        if (file_exists($path) && is_dir($path)) {
            $dirHandle = opendir($path);
            while (false !== ($file = readdir($dirHandle)))
            {
                $tmpPath = $path . '/' . $file;
                chmod($tmpPath, 0755);
                if (file_exists($tmpPath)) {
                    @unlink($tmpPath);
                }
            }
            closedir($dirHandle);
        }
    }

    public function clear_deleted_cases(){
        $this->db->where('is_active', '0');
        $this->db->where('DATE_ADD(`last_update`, INTERVAL 30 DAY) < NOW()');
        $this->db->delete('cases');
        if($this->db->affected_rows() > 0){
            $this->clear_database_and_files_after_case_deleted();
        }
    }
    function clear_database_and_files_after_case_deleted()
    {
        $this->load->model('cases_model', 'cases');
        $this->db->join('cases', 'cases.id = cases_files.case_id', 'left');
        $this->db->where('cases.id is NULL');
        $query = $this->db->get('zen_cases_files');
        $files = $query->result();

        foreach ($files as $file) {

            $this->cases->removedir($file->location);
        }

        $this->db->where('case_id is NULL');
        $this->db->delete('cases_files');

        $this->db->where('email = ""');
        $this->db->delete('case_contacts');

        $this->db->where('file_id is NULL');
        $this->db->delete('files_countries');

        $this->db->join('cases', 'cases.id = cases_countries.case_id', 'left');
        $this->db->where('cases.id is NULL');
        $query = $this->db->get('cases_countries');
        $countries_for_delete = $query->result_array();

        foreach ($countries_for_delete as $country) {

            $this->db->where('case_id', $country['case_id']);
            $this->db->delete('cases_countries');
        }

        $this->db->join('cases_files', 'files_countries.file_id = cases_files.id', 'left');
        $this->db->where('cases_files.id is NULL');
        $query = $this->db->get('files_countries');
        $countries_for_delete = $query->result_array();

        foreach ($countries_for_delete as $file) {

            $this->db->where('file_id', $file['file_id']);
            $this->db->delete('files_countries');
        }

        $this->db->join('cases', 'cases.id = sent_emails.case_id', 'left');
        $this->db->where('cases.id is NULL');
        $query = $this->db->get('sent_emails');
        $entries_for_delete = $query->result_array();

        foreach ($entries_for_delete as $emails) {

            $this->db->where('case_id', $emails['case_id']);
            $this->db->delete('sent_emails');
        }

        $this->db->join('cases', 'cases.id = zipped_files.case_id', 'left');
        $this->db->where('cases.id is NULL');
        $query = $this->db->get('zipped_files');
        $entries_for_delete = $query->result_array();

        foreach ($entries_for_delete as $cases) {

            $this->db->where('case_id', $cases['case_id']);
            $this->db->delete('zipped_files');
        }

        $this->db->join('cases', 'cases.id = related_cases.parent_case_id', 'left');
        $this->db->where('cases.id is NULL');
        $query = $this->db->get('related_cases');
        $entries_for_delete = $query->result_array();

        foreach ($entries_for_delete as $footnotes) {

            $this->db->where('parent_case_id', $footnotes['parent_case_id']);
            $this->db->delete('related_cases');
        }

        $this->db->join('cases', 'cases.id = estimates_footnotes.case_id', 'left');
        $this->db->where('cases.id is NULL');
        $query = $this->db->get('estimates_footnotes');
        $entries_for_delete = $query->result_array();

        foreach ($entries_for_delete as $footnotes) {

            $this->db->where('case_id', $footnotes['case_id']);
            $this->db->delete('estimates_footnotes');
        }

        $this->db->join('cases', 'cases.id = estimates_countries_fees.case_id', 'left');
        $this->db->where('cases.id is NULL');
        $query = $this->db->get('estimates_countries_fees');
        $entries_for_delete = $query->result_array();

        foreach ($entries_for_delete as $fees) {

            $this->db->where('case_id', $fees['case_id']);
            $this->db->delete('estimates_countries_fees');
        }

        $this->db->join('cases', 'cases.id = case_contacts.case_id', 'left');
        $this->db->where('cases.id is NULL');
        $query = $this->db->get('case_contacts');
        $entries_for_delete = $query->result_array();

        foreach ($entries_for_delete as $contacts) {

            $this->db->where('case_id', $contacts['case_id']);
            $this->db->delete('case_contacts');
        }

        $this->db->join('cases', 'cases.id = cases_tracker.case_id', 'left');
        $this->db->where('cases.id is NULL');
        $query = $this->db->get('cases_tracker');
        $entries_for_delete = $query->result_array();

        foreach ($entries_for_delete as $tracker) {

            $this->db->where('case_id', $tracker['case_id']);
            $this->db->delete('cases_tracker');
        }

        $this->db->select('cases_notes.case_number');
        $this->db->join('cases', 'cases.case_number = cases_notes.case_number', 'left');
        $this->db->where('cases.case_number is NULL');
        $query = $this->db->get('cases_notes');
        $entries_for_delete = $query->result_array();

        foreach ($entries_for_delete as $notes) {

            $this->db->where('case_number', $notes['case_number']);
            $this->db->delete('cases_notes');
        }

        $this->db->join('cases', 'cases.id = cases_associates_data.case_id', 'left');
        $this->db->where('cases.id is NULL');
        $query = $this->db->get('cases_associates_data');
        $entries_for_delete = $query->result_array();

        foreach ($entries_for_delete as $data) {

            $this->db->where('case_id', $data['case_id']);
            $this->db->delete('cases_associates_data');
        }
    }
}

?>