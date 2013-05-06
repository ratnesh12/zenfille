<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Countries extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->app_security_model->check_session()) {
            redirect('/');
        }
    }

    public function json_search_countries()
    {
        $search_string = $this->input->get('term');
        $case_type = $this->input->get('case_type');
        $this->load->model('countries_model', 'countries');
        $result = $this->countries->json_search_countries($search_string, $case_type);
        echo json_encode($result);
    }


    public function json_static_countries()
    {
        $this->load->model('countries_model', 'countries');
        $result_direct = $this->countries->json_search_countries(NULL, 'direct');
        $result_ep = $this->countries->json_search_countries(NULL, 'ep');
        $result_pct = $this->countries->json_search_countries(NULL, 'pct');
        /// should be different in further
        echo 'var directCountries = ' . json_encode($result_direct) . ';';
        echo 'var epCountries = ' . json_encode($result_ep) . ';';
        echo 'var pctCountries = ' . json_encode($result_pct) . ';';
    }


    public function json_static_countries_for_related($parent_id = '')
    {
        $this->load->model('countries_model', 'countries');
        $this->load->model("cases_model", "cases");

        $CASE = $case = $this->cases->get_case($parent_id);
        if (!empty($CASE)) {
            switch ($case['case_type_id'])
            {
                case '1':
                    $parent_type = 'pct';
                    break;
                case '2':
                    $parent_type = 'ep-validation';
                    break;
                case '3':
                    $parent_type = 'direct-filing';
                    break;
                default:
                    $parent_type = 'pct';
                    break;
            }
            $result = $this->countries->json_search_countries_for_related($CASE["id"], $parent_type);
            echo 'var directCountries = ' . json_encode($result) . ';';
            echo 'var epCountries = ' . json_encode($result) . ';';
            echo 'var pctCountries = ' . json_encode($result) . ';';
        }
        else
        {
            echo 'var directCountries = ' . json_encode(array()) . ';';
            echo 'var epCountries = ' . json_encode(array()) . ';';
            echo 'var pctCountries = ' . json_encode(array()) . ';';
        }
    }


}

/* End of file countries.php */
/* Location: ./application/controllers/countries.php */