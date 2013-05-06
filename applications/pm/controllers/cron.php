<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cron extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Updates last case number in DB. For cron job. Runs at 06 AM every day
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function update_last_case_number()
    {
        $this->load->model('cron_model', 'cron');

        $increase_value = $this->config->item('default_case_number_increase');

        $this->cron->update_last_case_number($increase_value);
    }

    public function cron_delete_temp_folder()
    {
        $this->load->model('cron_model', 'cron');
        $path = $this->config->item('path_upload') . 'client/uploads';
        $path_tmp = $this->config->item('path_upload') . 'pm/uploads/tmp';
        $this->cron->cron_removedir($path);
        $this->cron->cron_removedir($path_tmp);
    }

    public function clear_deleted_cases()
    {
        $this->load->model('cron_model', 'cron');
        $this->cron->clear_deleted_cases();
    }
}

/* End of file cron.php */
/* Location: ./application/controllers/cron.php */