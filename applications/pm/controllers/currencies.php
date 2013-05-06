<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Currencies extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * A list of currencies
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function index()
    {
        $this->output->enable_profiler(TRUE);
        $this->load->library('table');
        $this->load->model('currencies_model', 'currencies');

        $header['selected_menu'] = 'currencies';
        $header['page_name'] = 'Currencies';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Currencies'
        );
        $data['search_string'] = $search_string = $this->input->post('search_string');
        if (!empty($search_string)) {
            $data['currencies'] = $this->currencies->search_currencies($search_string);
        }
        else
        {
            $data['currencies'] = $this->currencies->get_list_currencies();
        }
        $this->load->view('parts/header', $header);
        $this->load->view('currencies/list', $data);
        $this->load->view('parts/footer');
    }

    /**
     * Edit currency form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    currency ID
     * @return    void
     * */
    public function edit($record_id = '')
    {
        $this->load->library('table');
        $this->load->model('currencies_model', 'currencies');

        $header['selected_menu'] = 'currencies';
        $header['page_name'] = 'Currencies';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            anchor('/currencies/', 'Currencies'),
            'Edit Currency'
        );
        $data['currency'] = $this->currencies->get_currency_record($record_id);

        $this->load->view('parts/header', $header);
        $this->load->view('currencies/edit', $data);
        $this->load->view('parts/footer');
    }

    /**
     * Updates currency entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    currency ID
     * @return    void
     * */
    public function update_record($record_id = '')
    {
        $this->load->model('currencies_model', 'currencies');
        $this->currencies->update_currency_record($record_id);
        redirect('/currencies/edit/' . $record_id);
    }

    /**
     * Updates currencies list from European Central Bank site
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function update()
    {
        $this->load->model('currencies_model', 'currencies');
        $this->currencies->update_rate_list();
    }
}

/* End of file currencies.php */
/* Location: ./application/controllers/dashboard.php */