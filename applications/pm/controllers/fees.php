<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Fees extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->app_security_model->check_session()) {
            redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
        }
    }

    /**
     * A list of fees (by countries).
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function index()
    {
        $this->load->library('table');
        $this->load->model('fees_model', 'fees');
        // If we do search
        $data['search_string'] = $search_string = $this->input->post('search_string');
        if (!empty($search_string)) {
            $data['fees'] = $this->fees->search_fees($search_string);
        }
        else
        {
            $data['fees'] = $this->fees->get_all_records();
        }

        $header['selected_menu'] = 'fees';
        $header['page_name'] = 'Fees';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Fees'
        );

        $this->load->view('parts/header', $header);
        $this->load->view('fees/list', $data);
        $this->load->view('footer');
    }

    /**
     * Removes fee entry.
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    fee entry ID
     * @return    void
     * */
    public function delete($fee_id = '')
    {
        $this->load->model('fees_model', 'fees');
        $this->fees->delete_fee($fee_id);
        redirect('/fees/');
    }

    /**
     * Create fee entry form.
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */

    public function create($country_id = "")
    {
        $this->load->library('table');
        $this->load->model('countries_model', 'countries');
        $data['countries'] = $this->countries->get_all_countries();
        $data['country_id'] = $country_id;
        $header['selected_menu'] = 'fees';
        $header['page_name'] = 'Create Fee';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            anchor('/fees/', 'Fees'),
            'Create'
        );

        $this->load->view('parts/header', $header);
        $this->load->view('fees/create', $data);
        $this->load->view('footer');
    }

    public function footnote_list()
    {
        $this->load->library('table');
        $this->load->model('estimates_model', 'estimates');
        $data['footnes'] = $this->estimates->get_common_footnotes();
        $header['selected_menu'] = 'fees';
        $header['page_name'] = 'Footnotes';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Fees'
        );
        $this->load->view('parts/header', $header);
        $this->load->view('fees/footnote_list', $data);
        $this->load->view('footer');

    }

    public function create_footnote()
    {
        $this->load->library('table');
        $data['action'] = 'Create';
        $header['selected_menu'] = 'fees';
        $header['page_name'] = 'Footnotes';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Fees'
        );
        $this->load->view('parts/header', $header);
        $this->load->view('fees/edit_footnote', $data);
        $this->load->view('footer');
    }

    public function edit_footnote($id = '')
    {
        $this->load->library('table');
        $this->load->model('estimates_model', 'estimates');
        $data['footnes'] = $this->estimates->get_common_footnote($id);
        $data['action'] = 'Edit';
        $header['selected_menu'] = 'fees';
        $header['page_name'] = 'Footnotes';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Fees'
        );
        $this->load->view('parts/header', $header);
        $this->load->view('fees/edit_footnote', $data);
        $this->load->view('footer');
    }

    public function update_footnote($action)
    {
        $this->load->model('estimates_model', 'estimates');
        $this->estimates->update_footnote($action);
        redirect('/fees/footnote_list');
    }

    public function delete_footnote($id)
    {
        $this->load->model('estimates_model', 'estimates');
        $this->estimates->delete_footnote($id);
        redirect('/fees/footnote_list');
    }

    /**
     * Edit fee entry form.
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    country ID
     * @return    void
     * */

    public function edit($country_id = '')
    {
        $this->load->library('table');
        $this->load->model('fees_model', 'fees');
        $this->load->model('countries_model', 'countries');
        $data['pct_fees'] = $this->fees->get_fee_by_country_id_and_case_type_id($country_id, 1);
        $data['ep_fees'] = $this->fees->get_fee_by_country_id_and_case_type_id($country_id, 2);
        $data['direct_fees'] = $this->fees->get_fee_by_country_id_and_case_type_id($country_id, 3);
        $data['countries'] = $this->countries->get_all_countries();
        $data['country_id'] = $country_id;
        $header['selected_menu'] = 'fees';
        $header['page_name'] = 'Edit Fee';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            anchor('/fees/', 'Fees'),
            $country_id
        );

        $this->load->view('parts/header', $header);
        $this->load->view('fees/edit', $data);
        $this->load->view('footer');
    }

    /**
     * Inserts fee entry .
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */

    public function insert()
    {
        $this->load->model('fees_model', 'fees');
        $this->fees->insert_fee();
        redirect('/fees/');
    }

    /**
     * Updates fee entry .
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    country ID
     * @return    void
     * */

    public function update($country_id = '')
    {
        $this->load->model('fees_model', 'fees');
        $this->fees->update_fee($country_id);
        redirect('/fees/edit/' . $country_id);
    }
}

/* End of file fees.php */
/* Location: ./application/controllers/fees.php */