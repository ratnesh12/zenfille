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
     * PM profile form.
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function index()
    {
        $this->load->library('table');
        $this->load->model('profile_model', 'profile');
        $data['profile'] = $this->profile->get_pm_profile();
        $header['page_name'] = 'Email Templates';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Profile'
        );

        $this->load->view('parts/header', $header);
        $this->load->view('profile/edit', $data);
        $this->load->view('footer');
    }

    /**
     * Updates profile data.
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function update()
    {
        $this->load->model('profile_model', 'profile');
        $this->profile->update_pm_profile();
        redirect('/profile/');
    }
}

/* End of file profile.php */
/* Location: ./application/controllers/profile.php */