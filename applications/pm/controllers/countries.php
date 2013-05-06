<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Countries extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->app_security_model->check_session()) {
            redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
        }
    }

    /**
     * A list of countries
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function index()
    {
        $this->load->library('table');
        $this->load->model('countries_model', 'countries');

        $data['countries'] = $this->countries->get_all_countries();
        $header['selected_menu'] = 'countries';
        $header['page_name'] = 'Countries';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Countries'
        );

        $this->load->view('parts/header', $header);
        $this->load->view('countries/list', $data);
        $this->load->view('footer');
    }

    /**
     * Removes country entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    country ID
     * @return    void
     * */

    public function delete($countriy_id = '')
    {
        $this->load->library('table');
        $this->load->model('countries_model', 'countries');
        $this->db->where('id', $countriy_id);
        $this->db->delete('countries');
        $data['countries'] = $this->countries->get_all_countries();
        $header['selected_menu'] = 'countries';
        $header['page_name'] = 'Countries';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Countries'
        );
        $this->load->view('parts/header', $header);
        $this->load->view('countries/list', $data);
        $this->load->view('footer');
    }

    /**
     * Edit country form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    country ID
     * @return    void
     * */
    public function edit($country_id = '')
    {
        $this->load->library('table');
        $this->load->model('countries_model', 'countries');

        $data['country'] = $this->countries->get_country($country_id);
        if (!$data['country']['country_filing_deadline']) {
            $data['country']['country_filing_deadline'] = 12;
        }
        $header['selected_menu'] = 'countries';
        $header['page_name'] = $country_id ? 'Edit Country' : 'Create country';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            anchor('/countries/', 'Countries'),
            $country_id ? 'Edit Country' : 'Create country'
        );

        $this->load->view('parts/header', $header);
        $this->load->view('countries/edit', $data);
        $this->load->view('footer');
    }

    /**
     * Inserts country entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function insert()
    {
        $this->load->model('countries_model', 'countries');
        $this->countries->insert_country();
        redirect('/countries/');
    }

    /**
     * Updates country entry. Loads flag image
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    country ID
     * @return    void
     * */
    public function update($country_id = '')
    {
        $this->load->library('upload');
        $this->load->model('countries_model', 'countries');
        $file_field = 'flag_image';
        $flag_image = false;
        $addfee = false;
        if (!$country_id) {
            $addfee = true;
            $this->form_validation->set_rules('country', 'Country', 'required');
        }
        $this->form_validation->set_rules('code', 'Code', 'required');
        $this->form_validation->set_rules('currency_code', 'Currency Code', 'required');
        $this->form_validation->set_rules('pct_language', 'PCT Language', 'required');
        $this->form_validation->set_rules('ep_language', 'EP Language', 'required');
        $this->form_validation->set_rules('direct_language', 'Direct Language', 'required');
        $this->form_validation->set_rules('filling-deadline', 'Filling Deadline', 'required');

        if ($this->form_validation->run() == false) {
            $this->edit($country_id);

        } else {
            if (!$country_id) {
                $country_id = $this->countries->insert_country();
            }
            $country = $this->countries->get_country($country_id);
            if ($_FILES[$file_field]['size'] > 0) {
                $image_upload_path = 'assets/images/flags/';
                $config['upload_path'] = '../client/' . $image_upload_path;
                $config['allowed_types'] = 'png';
                $config['max_size'] = '100';
                $config['max_width'] = '48';
                $config['max_height'] = '48';

                $this->load->library('upload');
                $this->upload->initialize($config);
                if ($this->upload->do_upload($file_field)) {
                    $flag_data = $this->upload->data();
                    $flag_image = $image_upload_path . $flag_data['file_name'];
                    if ($country_id) {
                        if (file_exists(str_replace('pm', 'client', FCPATH) . $country['flag_image'])) {
                        }
                    }
                }
            }

            if ($country_id) {
                $this->countries->update_country($country_id, $flag_image);
            }
            if ($addfee) {
                redirect('/fees/create/' . $country_id);
            }
            redirect('/countries/edit/' . $country_id);
        }
    }

}

/* End of file associates.php */
/* Location: ./application/controllers/associates.php */