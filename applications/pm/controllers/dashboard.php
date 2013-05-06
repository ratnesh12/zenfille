<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
     
	     if (!$this->app_security_model->check_session()) {
	
           		 // redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
	      
		  }
    }

    public function cleardb()
    {
        $this->load->model('cron_model', 'cron');
        $this->cron->clear_database_and_files_after_case_deleted();
    }

    /**
     * Dashboard. Active, Pending, Completed cases
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function index()
    {
        $this->load->library('table');
        $this->load->model('cases_model', 'cases');
        $data['active_cases'] = $this->cases->get_active_cases();
        $data['pending_cases'] = $this->cases->get_pending_cases();
        $data['completed_cases'] = $this->cases->get_completed_cases();
        $data['cases_regions'] = $this->cases->get_case_regions();
        $data['approved_regions'] = $this->cases->get_approved_regions_for_all_cases();
        $header['selected_menu'] = 'dashboard';
        $header['page_name'] = 'Dashboard';
        $header['breadcrumb'] = array(
            'Dashboard'
        );
        $header['subheader_message'] = 'PM Portal';
        $this->load->view('parts/header', $header);
        $this->load->view('dashboard', $data);
        $this->load->view('parts/footer');
    }

    /**
     * Function for AJAX search on dashboard
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    string
     * */
    public function search_cases()
    {
        $this->load->library('table');
        $this->load->model('cases_model', 'cases');
        $search_string = $this->input->post('search_string');
        $type = $this->input->post('type');
        $found_cases = $this->cases->do_search_by_case_number($search_string, $type);
        $approved_regions = $this->cases->get_approved_regions_for_all_cases();

        if (check_array($found_cases)) {
            if ($type == 'active') {
                // Custom sorting for active cases
                function cmp($a, $b)
                {
                }

                // Sort and print the resulting array
                uasort($found_cases, 'cmp');
            }
            foreach ($found_cases as $key => $found_case)
            {
                $approved_regions_array = isset($approved_regions[$found_case['id']]) ? $approved_regions[$found_case['id']] : array();
                $regions = array();
                if (check_array($found_case['regions'])) {
                    foreach ($found_case['regions'] as $region)
                    {
                        if ($found_case['manager_id'] > 0) {
                            if (in_array($region['code'], $approved_regions_array)) {
                                $regions[] = trim($region['code']);
                            }
                        }
                        else
                        {
                            $regions[] = trim($region['code']);
                        }
                    }
                }
                $link_class = '';
                if ($type == 'active') {
                    if ($found_case['highlight'] == '1' && strtotime(date('Y-m-d H:i:s')) >= (strtotime($found_case['filing_deadline']) - 5 * 86400)) {
                        $link_class = 'red-box';

                    } else {
                        $link_class = 'empty-box';
                    }
                }
                elseif ($type == 'pending')
                {

                    if ($found_case['common_status'] == 'estimating-reestimate') {
                        $link_class = 'yellow-box';
                    }
                    elseif ($found_case['common_status'] == 'pending-intake')
                    {
                        $link_class = 'green-box';
                    }
                    elseif (!empty($found_case['approved_at']))
                    {
                        $link_class = 'green-box';
                    }
                    if (empty($link_class)) {
                        $link_class = 'empty-box';
                    }
                }
                if (empty($link_class)) {
                    $link_class = 'empty-box';
                }

                $found_cases[$key]['regions'] = $regions;
                $found_cases[$key]['link_class'] = $link_class;
                $this->table->add_row(
                    anchor(
                        '/cases/view/' . $found_case['case_number'],
                        $found_case['case_number'],
                        'class="' . $link_class . '"'
                    ),
                    $found_case['filing_deadline'],
                    implode(', ', $regions),
                    $found_case['reference_number'],
                    $found_case['client_name'],
                    $found_case['manager_name']
                );
            }
        }
        $data['searched_cases'] = $found_cases;
        $this->load->view('/cases/searched_cases', $data);
    }


    // new functions for refactoring associates don't touch till update on live db!!!!

    //set all case active

    public function active()
    {
        $this->db->set('is_active', '1');
        $this->db->update('cases');
    }

    // first step
    public function replace_associates()
    {

        $query = $this->db->get('custom_associates');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $insert_data) {
                $data = array(
                    'associate' => $insert_data['associate'],
                    'email' => $insert_data['email'],
                    'country_id' => '0',
                    'fee' => $insert_data['fee'],
                    'fee_currency' => $insert_data['fee_currency'],
                    'translation_required' => $insert_data['translation_required'],
                    'contact_name' => $insert_data['contact_name'],
                    '30_months' => $insert_data['30_months'],
                    '31_months' => $insert_data['31_months'],
                    'ep_validation' => $insert_data['ep_validation'],
                    'reference_number' => $insert_data['reference_number'],
                    'is_direct_case_allowed' => '0' // we need to clarify this issue with Nikita whether we need it to set '1' or '0' for replaced associates
                );

                $this->db->insert('associates', $data);
                $insert_id = $this->db->insert_id();
                $case_data = array(
                    'case_id' => $insert_data['case_id'],
                    'associate_id' => $insert_id,
                    'country_id' => $insert_data['country_id'],
                    'reference_number' => $insert_data['reference_number']
                );
                $this->db->insert('cases_associates_data', $case_data);
            }
        }
    }

    public function delete_associates()
    {
        $this->db->where('reference_number is NULL');
        $this->db->delete('cases_associates_data');
    }

    /// second step
    public function insert_all_associates()
    {
        $case_associate_data = $this->get_associates();
        if ($case_associate_data) {
            foreach ($case_associate_data as $data) {
                $check_data = $this->check_case_associate($data['case_id'], $data['id'], $data['country_id']);
                if ($check_data) {
                    $active_ass = $this->check_case_disable_associates($data['case_id'], $data['id']);
                    $active = '0';
                    if ($active_ass) {
                        $active = '1';
                    }
                    $insert_data = array(
                        'case_id' => $data['case_id'],
                        'associate_id' => $data['id'],
                        'country_id' => $data['country_id'],
                        'reference_number' => $data['reference_number'],
                        'is_active' => $active
                    );
                    $this->db->insert('cases_associates_data', $insert_data);
                }
            }
        }


    }

    public function add_country()
    {

        $this->db->select('cases_associates_data.case_id, cases_associates_data.associate_id,associates.country_id');
        $this->db->join('associates', 'associates.id = cases_associates_data.associate_id');
        $this->db->where('cases_associates_data.country_id', '0');
        $query = $this->db->get('cases_associates_data');
        if ($query->num_rows) {
            $result = $query->result_array();
            foreach ($result as $data) {
                $this->db->set('country_id', $data['country_id']);
                $this->db->where('case_id', $data['case_id']);
                $this->db->where('associate_id', $data['associate_id']);
                $this->db->update('cases_associates_data');
            }
            return false;
        }
    }

    public function check_case_associate($case_id, $associate_id, $country_id)
    {

        $this->db->where('case_id', $case_id);
        $this->db->where('associate_id', $associate_id);
        $query = $this->db->get('cases_associates_data');
        if ($query->num_rows) {
            $this->db->set('country_id', $country_id);
            $this->db->where('case_id', $case_id);
            $this->db->where('associate_id', $associate_id);
            $this->db->update('cases_associates_data');

            return false;
        }
        return true;
    }

    public function check_case_disable_associates($case_id, $associate_id)
    {

        $this->db->where('case_id', $case_id);
        $this->db->where('associate_id', $associate_id);
        $query = $this->db->get('disabled_associates');
        if ($query->num_rows) {
            return false;
        }
        return true;
    }

    public function get_associates()
    {

        $this->db->select('associates.*, estimates_countries_fees.case_id');
        $this->db->join('associates', 'associates.country_id = estimates_countries_fees.country_id');
        $this->db->where('is_approved', '1');
        $this->db->or_where('is_approved_by_pm', '1');
        $query = $this->db->get('estimates_countries_fees');
        return $query->result_array();
    }


    public function backup()
    {
        $this->load->model('cases_model', 'cases');
        $this->db->where('case_number >= ' . '2441', FALSE, FALSE);
        $this->db->where('case_number <= ' . '2557', FALSE, FALSE);

        $query = $this->db->get('cases');

        if ($query->num_rows()) {
            foreach ($query->result_array() as $case)
            {
                $this->cases_tracker1($case['id']);
                $this->cases_files1($case['id']);
            }
        }
    }

    public function cases_tracker1($case_id)
    {

        $this->db->where('case_id', $case_id);
        $query = $this->db->get('cases_tracker1');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $case)
            {
                unset($case['id']);
                $this->cases_tracker($case);
            }
        }
    }

    public function cases_tracker($case)
    {

        $this->db->where('case_id', $case['case_id']);
        $this->db->where('country_id', $case['country_id']);
        $query = $this->db->get('cases_tracker');
        if ($query->num_rows()) {

            $data = $query->row_array();

            $this->db->where('id', $data['id']);
            $this->db->update('cases_tracker', $case);

        } else {
            $this->db->insert('cases_tracker', $case);
        }
    }

    public function cases_files1($case_id)
    {

        $this->db->where('case_id', $case_id);
        $query = $this->db->get('cases_files1');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $case)
            {
                $this->country_files1($case);
                unset($case['id']);
                $this->cases_files($case);
            }
        }
    }

    public function cases_files($case)
    {

        $this->db->where('case_id', $case['case_id']);
        $this->db->where('filename', $case['filename']);
        $query = $this->db->get('cases_files');
        if ($query->num_rows()) {

            $data = $query->row_array();

            $this->db->where('id', $data['id']);
            $this->db->update('cases_files', $case);

        } else {
            $this->db->insert('cases_files', $case);
        }
    }

    public function country_files1($data)
    {

        $this->db->where('file_id', $data['id']);
        $query = $this->db->get('files_countries1');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $case)
            {
                unset($case['id']);
                $this->country_files($case);
            }
        }
    }

    public function country_files($case)
    {

        $this->db->where('country_id', $case['country_id']);
        $this->db->where('file_id', $case['file_id']);
        $query = $this->db->get('files_countries');
        if (!$query->num_rows()) {
            $this->db->insert('files_countries', $case);

        }
    }

    public function zip_files()
    {
        $this->db->where('id >' . '2742', FALSE, FALSE);
        $query = $this->db->get('zipped_files1');

        if ($query->num_rows()) {
            foreach ($query->result_array() as $data)
            {
                unset($data['id']);
                $this->db->insert('zipped_files', $data);
            }
        }
    }

    public function dir_check(){
        $path = realpath('../pm/uploads');
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $filename)
        {
            $path = explode("/",$filename);
            if(is_numeric($path['6'])){
                $result = $this->check_path_user($path['6']);
                if($result){
                    echo 'removed '.$path['6'].'</br>';
                    break;
                }else{
                        $result = $this->check_path_case($path['6'],$path['7']);
                        if($result){
                            echo 'removed '.$path['7'].'</br>';
                            break;
                        }
                }
            }
        }
    }

    public function check_path_user($path){
        $this->load->model('cases_model', 'cases');
        $this->db->where('id', $path);
        $query = $this->db->get('customers');
        if($query->num_rows){
            return false;
        }else{
            $path_tmp = $this->config->item('path_upload') . 'pm/uploads/'.$path;
            $this->cases->removedir($path_tmp);
            return true;
        }
    }

    public function check_path_case($path, $case_number){
        $this->load->model('cases_model', 'cases');
        $this->db->where('case_number', $case_number);
        $query = $this->db->get('cases');
        if($query->num_rows){
            return false;
        }else{
            $path_tmp = $this->config->item('path_upload') . 'pm/uploads/'.$path.'/'.$case_number;
            $this->cases->removedir($path_tmp);
            return true;
        }
    }

    public function consolidating(){
        $this->db->set('file_type_id', '6');
        $this->db->where('file_type_id', '5');
        $this->db->update('cases_files');
    }

    // end new functions for refactoring associates don't touch till update on live db!!!!
}

/* End of file dashboard.php */
/* Location: ./application/controllers/dashboard.php */
