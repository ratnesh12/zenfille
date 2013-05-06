<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Wp_engine extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->app_security_model->check_session()) {
            if ($this->input->is_ajax_request()) {

            } else {
                redirect('/');
            }
        }
    }

    /**
     * Loads data from WIPO
     *
     * @access    public
     * @param    string    WO number
     * @return    string
     * */
    public function load_case_data($case_number = '', $wo_number = '')
    {
        $this->load->model('cases_model', 'cases');
        $this->load->model('estimates_model', 'estimates');
        $reparse = $this->input->post('reparse');
        $parse = $this->input->post('parse') == "true" ? TRUE : FALSE;
        $this->load->model('wipo_model', 'wipo');
        if (empty($case_number)) {
            $case_number = $this->input->post('case_number');
        }
        if (empty($wo_number)) {
            $wo_number = $this->input->post('application_number');
            $wo_number = $this->wipo->prepare_number_for_parser($wo_number);
        }

        if (empty($wo_number)) {
            $wo_number = $this->input->post('wo_number');
            $wo_number = $this->wipo->prepare_number_for_parser($wo_number);
        }

        $case_parse_data = array();
        $wo_number = strtoupper(clearString($wo_number));
        $parts = explode('/', $wo_number);
        if ($parts[0] === 'PCT') {
            $tmp = substr($parts[1], 2);
            if (strlen($tmp) == 2)
                $parts[1] = substr_replace($parts[1], '20', 2, 0);
            $parts[2] = sprintf('%06d', $parts[2]);

            $wo_number = implode('/', $parts);
        }

        $case_number = strtoupper(clearString($case_number));

        if (is_array($case_data = $this->wipo->get_entry($wo_number))) {

            if (empty($case_data['number_pages']) || $reparse) {
                $case_parse_data = $this->wipo->parse_entry($wo_number);

                if (!empty($case_parse_data)) {
                    $this->delete_from_wipo(array('application_number' => $wo_number));
                    $this->wipo->append_data_to_case($case_number, $case_parse_data);
                    $this->wipo->append_wipo_data_entry($case_parse_data);
                }
            }

            if (!empty($case_number)) {
                $this->wipo->append_data_to_case($case_number, $case_data);
            }
            $output['case_data'] = $case_data;
            $output['result'] = '1';
        } elseif ($parse) {

            if ($case_data = $this->wipo->parse_entry($wo_number)) {

                if (!empty($case_number))
                    $this->wipo->append_data_to_case($case_number, $case_data);

                $this->wipo->append_wipo_data_entry($case_data);

                $output['case_data'] = $case_data;
                $output['result'] = '1';
            } else {
                $output['result'] = '0';
            }
            $case_parse_data = $this->wipo->parse_entry($wo_number);
        } else {

            $output['result'] = '0';
        }

        if ($reparse) {

            $output['case_data'] = $case_parse_data;

            if (!empty($output['case_data']['first_priority_date'])) {
                $output['case_data']['first_priority_date'] = date('m/d/y', strtotime($output['case_data']['first_priority_date']));

                $date = new DateTime($output['case_data']['first_priority_date']);
                $year = $this->estimates->addYears($date, '2');
                $date = new DateTime($year);
                $output['case_data']['30_month_filing_deadline'] = date('m/d/y', strtotime($this->estimates->addMonths($date, '6')));
                $date30 = new DateTime($year);
                $output['case_data']['31_month_filing_deadline'] = date('m/d/y', strtotime($this->estimates->addMonths($date30, '7')));

            } else {
                $output['case_data']['first_priority_date'] = 'N/A';
                $output['case_data']['30_month_filing_deadline'] = 'N/A';
                $output['case_data']['31_month_filing_deadline'] = 'N/A';
            }

            if (!empty($output['case_data']['international_filing_date'])) {
                $output['case_data']['international_filing_date'] = date('m/d/y', strtotime($output['case_data']['international_filing_date']));
            } else {
                $output['case_data']['international_filing_date'] = 'N/A';
            }

            if (!empty($output['case_data']['publication_date'])) {
                $output['case_data']['publication_date'] = date('m/d/y', strtotime($output['case_data']['publication_date']));
            } else {
                $output['case_data']['publication_date'] = 'N/A';
            }

            if (empty($output['case_data']['number_claims'])) {
                $output['case_data']['number_claims'] = 'N/A';
            }
        }


        $this->notify->setData($output);
        if ($output['result'])
            $this->notify->returnSuccess('Parsed');
        else
            $this->notify->returnError('Not parsed');

    }

    function light_parsing()
    {
        $result = array();
        if ($this->input->post('parse') !== 'true') {
            echo json_encode($result);
            return false;
        }
        $this->load->model('wipo_model', 'wipo');
        $result = $this->wipo->light_parser($_POST['application_number']);
        if ($result) {
            if (empty($result['first_priority_date']) || $result['filing_deadline'] == '0000-00-00') {
                $result['filing_deadline'] = 'N/A';
                $result['31_month_filing_deadline'] = 'N/A';
                $result['30_month_filing_deadline'] = 'N/A';
            } else {
                $result['filing_deadline'] = date($this->config->item('client_date_format') , strtotime($result['filing_deadline']));
                $result['31_month_filing_deadline'] = date($this->config->item('client_date_format') , strtotime($result['31_month_filing_deadline']));
                $result['30_month_filing_deadline'] = date($this->config->item('client_date_format') , strtotime($result['30_month_filing_deadline']));
            }
        }
        echo json_encode($result);
    }

    function delete_from_wipo($options = array())
    {
        if (isset($options['application_number'])) {
            $this->db->where('pct_number', trim($options['application_number']));
            $this->db->or_where('wo_number', trim($options['application_number']));
        }

        $this->db->delete('wipo_data');
    }
}

/* End of file wp_engine.php */
/* Location: ./application/controllers/wp_engine.php */
