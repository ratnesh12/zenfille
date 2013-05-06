<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Emails extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->app_security_model->check_session()) {
            redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
        }
    }

    /**
     * Show a list of email templates
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function index()
    {
        $this->load->library('table');
        $this->load->model('emails_model', 'emails');
        $data['emails'] = $this->emails->get_all_templates();
        $header['selected_menu'] = 'emails';
        $header['page_name'] = 'Email Templates';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Email Templates'
        );

        $this->load->view('parts/header', $header);
        $this->load->view('emails/list', $data);
        $this->load->view('footer');
    }

    /**
     * Edit email template form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    template ID
     * @return    void
     * */
    public function edit($template_id = '')
    {
        $this->load->library('table');
        $this->load->model('emails_model', 'emails');
        $data['template'] = $this->emails->get_template($template_id);

        $header['selected_menu'] = 'emails';
        $header['page_name'] = 'Edit Email Template';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            anchor('/emails/', 'Email Templates'),
            $data['template']['title']
        );

        $this->load->view('parts/header', $header);
        $this->load->view('emails/edit', $data);
        $this->load->view('footer');
    }

    public function create()
    {
        $this->load->library('table');

        $header['selected_menu'] = 'emails';
        $header['page_name'] = 'Create Email Template';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            anchor('/emails/', 'Email Templates'),
            'Create New Template'
        );

        $this->load->view('parts/header', $header);
        $this->load->view('emails/create');
        $this->load->view('footer');
    }

    public function insert()
    {
        $this->load->model('emails_model', 'emails');
        $this->emails->insert_template();
        redirect('/emails/');
    }

    /**
     * Updates email template
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    template ID
     * @return    void
     * */
    public function update($template_id = '')
    {
        $this->load->model('emails_model', 'emails');

        $this->emails->update_template($template_id);

        redirect('/emails/edit/' . $template_id);
    }

    /**
     * Shows a list of template variables. For POP UP form
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function variables()
    {
        $this->load->library('table');
        $this->load->view('emails/variables');
    }

    /**
     * Shows a list of template variables. With header and footer
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function available_variables()
    {
        $this->load->library('table');
        $header['selected_menu'] = 'emails';
        $header['page_name'] = 'Available Variables';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            anchor('/emails/', 'Email Templates'),
            'Available Variables'
        );

        $this->load->view('parts/header', $header);
        $this->load->view('emails/variables');
        $this->load->view('footer');
    }

    /**
     * Pipes email (OLD FUNCTION)
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function pipe()
    {
        $this->load->model('emails_model', 'emails');

        $fd = fopen("php://stdin", "r");
        $email = "";
        while (!feof($fd))
        {
            $email .= fread($fd, 1024);
        }
        fclose($fd);

        $lines = explode("\n", $email);
        if (check_array($lines)) {
            $replace_variables = array(
                'To:',
                ' ',
                ':',
                'to',
                'case',
                'zenfile.com',
                '@'
            );
            foreach ($lines as $line)
            {
                if (strpos(strtolower($line), 'to') !== FALSE) {
                    $case_number = str_replace($replace_variables, '', $line);

                    $this->emails->set_new_email_sign($case_number, 1);
                }
            }
        }
    }

    /**
     * Opens email box. Horde email tool on cpanel
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */
    public function open_cases_email_box($case_number)
    {
        $this->load->model('emails_model', 'emails');
        $this->emails->set_new_email_sign($case_number, 0);

        // cURL stuff
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0';
        $case_email = 'case' . $case_number . $this->config->item('email_open');
        $case_email_password = $this->config->item('zenfile_default_email_password');
        $cookie = 'cookie.txt';
        $round_cube_mailbox = 'http://' . $_SERVER["HTTP_HOST"] . ':2096/horde/login.php';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_USERPWD, $case_email . ':' . $case_email_password);
        curl_setopt($ch, CURLOPT_URL, $round_cube_mailbox); // set url to post to
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent); // allow redirects
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after Ns
        curl_setopt($ch, CURLOPT_REFERER, $round_cube_mailbox);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_close($ch);

        echo '<form id="login" action="http://' . $_SERVER["HTTP_HOST"] . ':2096/login/" method="post">
		
<input type="hidden" name="login_theme" value="cpanel">
<input type="hidden" name="user" value="' . $case_email . '">
<input type="hidden" name="pass" value="' . $case_email_password . '">
<input type="hidden" name="goto_uri" value="3rdparty/roundcube/?_task=mail&_mbox=INBOX.' . $case_number . '&_refresh=1">

</form> 
<script type="text/javascript">
setTimeout("document.getElementById(\'login\').submit()",1000);
</script>';

    }

    /**
     * Opens email box. Horde email tool on cpanel. NEW FUNCTION!
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case number
     * @return    void
     * */

    function new_email_box($case_number)
    {

        $this->load->model('emails_model', 'emails');
        $this->emails->set_new_email_sign($case_number, 0);

        // cURL stuff
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0';
        $case_email = 'case' . $case_number . $this->config->item('email_open');
        $case_email_password = $this->config->item('zenfile_default_email_password');
        $cookie = 'cookie.txt';
        $round_cube_mailbox = base_url() . ':2095/horde/login.php';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_USERPWD, $case_email . ':' . $case_email_password);
        curl_setopt($ch, CURLOPT_URL, $round_cube_mailbox); // set url to post to
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent); // allow redirects
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after Ns
        curl_setopt($ch, CURLOPT_REFERER, $round_cube_mailbox);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_close($ch);

        echo '<form id="login" action="' . base_url() . ':2095/login/" method="post">
		
<input type="hidden" name="login_theme" value="cpanel">
<input type="hidden" name="user" value="' . $case_email . '">
<input type="hidden" name="pass" value="' . $case_email_password . '">
<input type="hidden" name="goto_uri" value="3rdparty/roundcube/index.php">

</form>
<script type="text/javascript">
setTimeout("document.getElementById(\'login\').submit()",1000);
</script>';

    }
}

/* End of file emails.php */
/* Location: ./application/contollers/emails.php */