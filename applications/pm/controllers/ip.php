<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Ip extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->app_security_model->check_session()) {
            redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
        }
        if ($this->session->userdata('type') != 'admin') {
            rredirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
        }
    }

    /**
     * A list of allowed IP addresses
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     */
    public function index()
    {
        $this->load->library('table');
        $this->load->model('ip_model', 'ip');

        $data['ip_list'] = $this->ip->get_all_ip();
        $header['page_name'] = 'Dashboard';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'IP addresses'
        );
        $this->load->view('parts/header', $header);
        $this->load->view('ip/list', $data);
        $this->load->view('parts/footer');
    }

    /**
     * New IP address form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     */
    public function create()
    {
        $this->load->library('table');
        $header['page_name'] = 'Dashboard';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'New IP address'
        );

        $this->load->view('parts/header', $header);
        $this->load->view('ip/create');
        $this->load->view('parts/footer');
    }

    /**
     * Edit IP address form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    ip ID
     * @return    void
     */
    public function edit($ip_id = '')
    {
        $this->load->library('form_validation');
        $this->load->library('table');
        $this->load->model('ip_model', 'ip');
        $data['ip'] = $this->ip->get_ip($ip_id);
        $header['page_name'] = 'Edit IP';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'IP ' . $ip_id
        );

        $this->load->view('parts/header', $header);
        $this->load->view('ip/edit', $data);
        $this->load->view('parts/footer');
    }

    /**
     * Update IP entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    ip ID
     * @return    void
     */
    public function update($ip_id = '')
    {
        $this->load->library('form_validation');
        $this->load->model('ip_model', 'ip');
        $this->form_validation->set_rules('ip_address', 'IP address', 'required|valid_ip');
        if ($this->form_validation->run() == FALSE) {
            $this->edit($ip_id);
        }
        else
        {
            $this->ip->update_ip($ip_id);
            redirect('/ip/');
        }
    }

    /**
     * Inserts IP entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     */
    public function insert()
    {
        $this->load->library('form_validation');
        $this->load->model('ip_model', 'ip');
        $this->form_validation->set_rules('ip_address', 'IP address', 'required|valid_ip');

        if ($this->form_validation->run() == FALSE) {
            $this->create();
        }
        else
        {
            $this->ip->insert_ip();
            redirect('/ip/');
        }
    }

    /**
     * Inserts IP entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    IP id
     * @return    void
     */
    public function delete($ip_id)
    {
        $this->load->model('ip_model', 'ip');
        $this->ip->delete_ip($ip_id);
        redirect('/ip/');
    }
}

/* End of file ip.php */
/* Location: ./application/controllers/ip.php */