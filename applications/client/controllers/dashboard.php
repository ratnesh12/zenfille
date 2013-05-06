<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->app_security_model->check_session()) {
            if (is_ajax()) {
                echo 'Your session has timed out due to inactivity. Please log back in to continue.';
                exit();
            }
            else
            {
                redirect('http://' . $this->input->server('HTTP_HOST') . '/login/');
            }
        }

    }

    /**
     * Dashboard
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     *
     */
    public function index()
    {

        $this->output->enable_profiler(false);
        $this->load->library('table');
        $this->load->model('cases_model', 'cases');
        $header['selected_menu'] = 'dashboard';
        $header['page_name'] = 'Dashboard';
        $header['breadcrumb'] = array(
            'Dashboard'
        );
        $header['subheader_message'] = 'Welcome to the Zenfile Client Portal';
        $this->session->unset_userdata('current_case_id');
        $this->session->unset_userdata('current_case_number');
        $this->session->unset_userdata('current_case_url');
        $this->session->unset_userdata('current_case_type');
        $data['active_cases'] = $this->cases->get_active_cases("DESC");
        $data['pending_cases'] = $this->cases->get_pending_cases("DESC");
        $data['completed_cases'] = $this->cases->get_completed_cases("DESC");

        $this->load->view('parts/header', $header);
        $this->load->view('profile/dashboard', $data);
        $this->load->view('parts/footer');
    }

}

/* End of file dashboard.php */
/* Location: ./application/controllers/dashboard.php */