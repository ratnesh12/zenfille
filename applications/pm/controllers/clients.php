<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Clients extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->app_security_model->check_session()) {
            redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
        }
    }

    /**
     * A list of clients
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function index()
    {
        $this->load->library('table');
        $this->load->model('customers_model', 'customers');
        $data['search_string'] = $search_string = $this->input->post('search_string');
        if (!empty($search_string)) {
            $data['customers'] = $this->customers->search_customers($search_string);
        }
        else
        {
            $data['customers'] = $this->customers->get_all_customers();
        }
        $header['selected_menu'] = 'clients';
        $header['page_name'] = 'clients';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Clients'
        );

        $this->load->view('parts/header', $header);
        $this->load->view('customers/list', $data);
        $this->load->view('footer');
    }

    /**
     * Create client form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function create()
    {
        $this->load->library('table');
        $this->load->model('customers_model', 'customers');
        $this->load->model('cases_model', 'cases');
        $this->load->model('countries_model', 'countries');
        $header['selected_menu'] = 'clients';
        $header['page_name'] = 'Add a Client';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Add a Client'
        );

        $data['bdv'] = $this->cases->get_sales_managers();
        $data['managers'] = $this->get_managers();
        $data['firms'] = $this->get_firms();
        $data['phone_countries'] = $this->countries->get_all_countries();
        $data['us_states'] = $this->countries->get_us_states();
        $this->load->view('parts/header', $header);
        $this->load->view('customers/create', $data);
        $this->load->view('footer');
    }


    public function set_deleted($customer_id = '')
    {
        $this->load->model('customers_model', 'customers');
        if ($this->session->userdata('type') == 'supervisor') {
            $this->customers->set_deleted($customer_id);
        }
        redirect('/clients/');

    }

    public function get_firms()
    {
        $firms = $this->customers->get_firms();
        $firm_id = array('0' => 'Select Parent Firm');
        foreach ($firms as $firm) {
            $firm_id[$firm['id']] = $firm['company_name'];
        }
        return $firm_id;
    }

    public function get_managers()
    {
        $managers = $this->customers->get_managers();
        $manager_id = array('0' => 'Select Project Manager');
        foreach ($managers as $manager) {
            $manager_id[$manager['id']] = $manager['firstname'] . ' ' . $manager['lastname'];
        }
        return $manager_id;
    }

    /**
     * Edit client form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    client ID
     * @return    void
     * */
    public function edit($customer_id = '')
    {
        $this->load->library('table');
        $this->load->model('customers_model', 'customers');
        $this->load->model('cases_model', 'cases');
        $this->load->model('countries_model', 'countries');
        $data['customer'] = $this->customers->get_user($customer_id, 'customer');
        $data['firms'] = $this->get_firms();
        $data['managers'] = $this->get_managers();
        $data['bdv'] = $this->cases->get_sales_managers();
        $data['client_notes'] = $this->cases->client_notes($customer_id);
        $data['phone_countries'] = $this->countries->get_all_countries();
        $data['us_states'] = $this->countries->get_us_states();
        $header['selected_menu'] = 'clients';
        $header['page_name'] = 'Edit Client';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            anchor('/clients/', 'Сlients'),
            $data['customer']['firstname'] . ' ' . $data['customer']['lastname']
        );
        $this->load->view('parts/header', $header);
        $this->load->view('customers/edit', $data);
        $this->load->view('footer');
    }

    /**
     * Inserts client entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function insert()
    {
        $this->load->library('form_validation');
        $this->load->model('customers_model', 'customers');
        $this->form_validation->set_rules('username', 'Username', 'required|min_length[6]|is_unique[zen_customers.username]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[7]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('bdv', 'BDV', 'required');
        if ($this->form_validation->run() == FALSE) {
            $this->create();
        }
        else
        {
            $customer_id = $this->customers->insert_customer();
            redirect('/clients/edit/' . $customer_id);
        }
    }

    /**
     * Updates client entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    customer ID
     * @return    void
     * */
    public function update($customer_id = '')
    {
        $this->load->library('form_validation');
        $this->load->model('customers_model', 'customers');
        $this->form_validation->set_rules('username', 'Username', 'required|min_length[6]');
        $this->form_validation->set_rules('password', 'Password', 'min_length[7]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('bdv', 'BDV', 'required');
        if ($this->form_validation->run() == FALSE) {
            $this->edit($customer_id);
        }
        else
        {
            $this->customers->update_customer($customer_id);
            redirect('/clients/edit/' . $customer_id);
        }
    }

    /**
     * Login from PM to client area
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    customer ID
     * @return    void
     * */
    public function login($client_id = '')
    {
        $this->load->model('customers_model', 'customers');
        if (!is_null($client = $this->customers->get_user($client_id, 'customer'))) {
            $session_array = array(
                'client_user_id' => $client['id'],
                'client_username' => $client['username'],
                'client_firstname' => $client['firstname'],
                'client_lastname' => $client['lastname'],
                'client_email' => $client['email'],
                'client_bdv_id' => $client['bdv_id'],
                'client_allow_email' => $client['allow_email'],
                'client_type' => $client['type'],
                'client_tooltips_disable' => $client['is_disable_tooltips'],
                'parent_firm_id' => '',
                'client_manager_id' => '',
                'client_logged_in' => TRUE
            );

            $array_items = array(
                'client_user_id' => '',
                'client_username' => '',
                'client_firstname' => '',
                'client_lastname' => '',
                'client_email' => '',
                'client_bdv_id' => '',
                'client_allow_email' => '',
                'client_type' => '',
                'client_tooltips_disable' => '',
                'client_logged_in' => ''
            );
            $this->session->unset_userdata($array_items);
            $this->session->set_userdata($session_array);
            echo '<script type="text/javascript">
					window.location.href = "https://' . $_SERVER["HTTP_HOST"] . '/client/dashboard/";
				  </script>';
        }
    }

    public function ajax_search_country()
    {
        $this->load->model('countries_model', 'countries');
        $term = $this->input->get('term');
        echo json_encode($this->countries->get_iso_data($term));
    }

    public function get_google_address_data()
    {
        $this->load->library('gmap_geocoding');
        $q = $this->input->post('q');
        echo json_encode($this->gmap_geocoding->forwardSearch($q, 3));
    }
}

/* End of file clients.php */
/* Location: ./application/controllers/clients.php */