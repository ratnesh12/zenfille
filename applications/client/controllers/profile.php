<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Profile extends CI_Controller
{

    function __construct()
    {

        parent::__construct();
        if (!$this->app_security_model->check_session()) {
            redirect('/');
        }
    }

    /**
     * Profile form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     */
    public function index()
    {
        $this->load->library('table');
        $this->load->model('profile_model', 'profile');
        $this->load->model('countries_model', 'countries');
        $header['selected_menu'] = 'user-profile';
        $header['page_name'] = 'Profile';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Client Profile'
        );
        $data['bdv'] = $this->profile->get_bdv();
        $data['profile'] = $this->profile->get_profile_info();
        $data['us_states'] = $this->countries->get_us_states();

        $this->load->view('parts/header', $header);
        $this->load->view('profile/profile_info', $data);
        $this->load->view('parts/footer');
    }

    /**
     * Update profile info
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     */
    public function update()
    {
        $this->load->model('profile_model', 'profile');

        if ($this->profile->update_profile()) {
            $this->session->set_flashdata('notification', 'Profile has been updated');
        }

        if (!is_ajax()) {
            redirect('/profile/');
        }
    }

    public function get_google_address_data()
    {
        $this->load->library('gmap_geocoding');
        $q = $this->input->post('q');
        echo json_encode($this->gmap_geocoding->forwardSearch($q, 3));
    }

    public function ajax_search_country()
    {
        $this->load->model('countries_model', 'countries');
        $term = $this->input->get('term');
        echo json_encode($this->countries->get_iso_data($term));
    }

    public function create_user()
    {

        $this->load->library('table');
        $this->load->model('profile_model', 'profile');
        $this->load->model('countries_model', 'countries');
        $this->load->model('customers_model', 'customers');
        if ($this->session->userdata('client_type') != 'firm') {
            redirect('/dashboard');
        }
        $this->form_validation->set_rules('firstname', 'Firstname', 'required');
        $this->form_validation->set_rules('lastname', 'Lastname', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('username', 'Username', 'required|callback__username_check');
        $this->form_validation->set_rules('password', 'Password', 'required');
        if ($this->form_validation->run()) {
            $this->customers->create_customer($this->input->post());
            $inform_message = $this->form_validation->set_value('username').' has been created successfully';
            $this->session->set_userdata('inform_message',$inform_message);
            redirect('/dashboard');
        }

        $header['selected_menu'] = 'create_user';
        $header['page_name'] = 'Create user';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Create user'
        );
        $this->load->view('parts/header', $header);
        $this->load->view('profile/create_user');
        $this->load->view('parts/footer');


    }

    function _email_check()
    {

        $this->load->model('customers_model', 'customers');
        if ($this->customers->check_email($this->input->post('email'))) {
            return true;
        }
        $this->form_validation->set_message('_email_check', 'Email ' . $this->input->post("email") . '  is already exist');
        return false;
    }

    function _username_check()
    {

        $this->load->model('customers_model', 'customers');
        if ($this->customers->check_username($this->input->post('username'))) {
            return true;
        }
        $this->form_validation->set_message('_username_check', 'Username ' . $this->input->post("username") . ' is already exist');
        return false;

    }

    public function show_vcard()
    {
        $this->load->library('vcard');
        $this->load->model('profile_model', 'profile');

        if (!is_null($bdv = $this->profile->get_bdv())) {
            $data = array(
                'firstname' => $bdv['firstname'],
                'surname' => $bdv['lastname'],
                'nickname' => '',
                'birthday' => '',
                'company' => 'ZenFile LLC',
                'jobtitle' => 'BDV Rep',
                'workbuilding' => '',
                'workstreet' => $bdv['address'],
                'worktown' => 'New York',
                'workcounty' => 'USA',
                'workpostcode' => '',
                'workcountry' => 'USA',
                'worktelephone' => $bdv['phone'],
                'workemail' => $bdv['email'],
                'workurl' => 'http://www.zenfile.com/',
                'homebuilding' => '',
                'homestreet' => '',
                'hometown' => '',
                'homecounty' => '',
                'homepostcode' => '',
                'homecountry' => '',
                'hometelephone' => '',
                'homeemail' => '',
                'homeurl' => '',
                'mobile' => '',
                'notes' => '');
            $this->vcard->load_data($data);
            $this->vcard->show();
        }

    }
}

/* End of file dashboard.php */
/* Location: ./application/controllers/dashboard.php */