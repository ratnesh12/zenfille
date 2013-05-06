<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pipe extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * For email piping. Gets request from email filter. Look at cpanel account level filtering
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function run()
    {
        $this->load->model('emails_model', 'emails');
        $this->load->library('MimeMailParser');
        $fd = fopen("php://stdin", "r");
        $email = "";
        while (!feof($fd))
        {
            $email .= fread($fd, 1024);
        }
        fclose($fd);
        $Parser = new MimeMailParser();
        $Parser->setText($email);

        // get the email parts
        $to = $Parser->getHeader('to');
        $lines = explode("\n", $email);
        //log_message('error', json_encode($lines));
        if (check_array($lines)) {
            $replace_variables = array(
                'To:',
                ' ',
                ':',
                'to',
                'case',
                'zenfile.com',
                '@',
                '<',
                '>',
                '"'
            );
            foreach ($lines as $line)
            {
                //log_message('error', $line);
                if (strpos(strtolower($line), 'to') !== FALSE) {
                    if (strpos(strtolower($line), ' ') !== FALSE) {
                        $temp_array = explode(' ', $line);
                        if (isset($temp_array[1])) {
                            $line = $temp_array[1];
                        }
                    }
                }
            }

            preg_match("/case([0-9a-zA-Z]+)@zenfile.com/", $to, $matches);

            $case_number = isset($matches[1]) ? $matches[1] : '';
            $this->emails->set_new_email_sign($case_number, 1);
        }


        // Resend email to the same folder
        $Parser->setText($email);
        $from = $Parser->getHeader('from');
        // In case we have from address like caseXXXX@zen.zenfile.com
        if (strpos($from, 'zen.zenfile.com')) {
            $from = str_replace('zen.zenfile.com', 'zenfile.com', $from);
        }

        $subject = $Parser->getHeader('subject');
        $text = $Parser->getMessageBody('text');
        $html = $Parser->getMessageBody('html');
        $attachments = $Parser->getAttachments();

        $this->load->library('email');
        $email_config = array(
            'protocol' => 'smtp',
            'charset' => 'utf-8',
            'mailtype' => 'html',
            'smtp_user' => 'case' . $case_number . $this->config->item('default_email_box'),
            'smtp_pass' => $this->config->item('zenfile_default_email_password')
        );
        $this->email->initialize($email_config);
        $save_dir = 'uploads/tmp/';
        if (check_array($attachments)) {
            foreach ($attachments as $att)
            {
                // get the attachment name
                $filename = $att->filename;
                // write the file to the directory you want to save it in
                if ($fp = fopen($save_dir . $filename, 'w')) {
                    while ($bytes = $att->read())
                    {
                        fwrite($fp, $bytes);
                    }
                    fclose($fp);
                }
                $this->email->attach($save_dir . $filename);
            }
        }

        $email_config = array(
            'protocol' => 'sendmail',
            'charset' => 'utf-8',
            'mailtype' => 'html',
        );
        $this->email->initialize($email_config);
        $this->email->from($from);
        $this->email->to('case' . $case_number . $this->config->item('default_email_box'));
        $this->email->cc('brian.daniel@zenfile.com');

        $this->email->subject('{|' . $subject . '|}');
        if (empty($html)) {
            $this->email->message($text);
        }
        else
        {
            $this->email->message($html);
        }
        $this->email->send();
    }
}

/* End of file pipe.php */
/* Location: ./application/controllers/pipe.php */