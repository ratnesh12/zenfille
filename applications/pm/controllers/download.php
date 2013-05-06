<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Download extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Downloads ZIP file from the server
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string    file hash
     * @return    void
     * */
    public function zip($hash = '')
    {
        $this->load->helper('download');
        $this->load->model('files_model', 'files');
        $this->load->model('cases_model', 'cases');

        if (!is_null($data = $this->files->get_zip_by_hash($hash))) {

            if ($data['email_type'] == 'filing-confirmation') {
                $email = 'fr_client_received';
            } else if ($data['email_type'] == 'fa-request') {
                $email = 'fi_requests_received_fa';
            } else if ($data['email_type'] == 'document-instruction') {
                $email = 'doc_forms_received';
            } else if ($data['email_type'] == 'translation_request') {
                $email = 'translation_request_sent_to_park';
            }
            $countries = explode(',', $data['countries']);
            foreach ($countries as $country) {
                if ($data['email_type'] != 'translation_request') {
                    $this->cases->save_tracker($data['case_id'], $country, $email, 'current_date');
                }
            }

            if (file_exists($data['path'])) {
                $file_data = pathinfo($data['path']);
                $file_content = file_get_contents($data['path']);
                force_download($file_data['basename'], $file_content);
            }
        }
    }
}

/* End of file download.php */
/* Location: ./application/controllers/download.php */