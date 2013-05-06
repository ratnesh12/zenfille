<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Eduard
 * Date: 18.02.13
 * Time: 13:11
 * To change this template use File | Settings | File Templates.
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Send_emails_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $smtp_user user for sending emails via smpt (by default is no-replay@dev.zenfile.com or no-replay@zenfile.com) depends on test mode
     * @param $from
     * @param $subject
     * @param $content
     * @param $to can be an array
     * @param $cc is array
     * @param $attachments is array
     */

    public function send_email($smtp_user = '', $from = '', $subject, $content, $to, $cc = '', $attachments = '')
    {

        $message = Swift_Message::newInstance();
        $message->setFrom(array($from));
        if (empty($from)) {
            $from = 'no-replay' . $this->config->item('default_email_box');
        }
        if (empty($smtp_user)) {
            $smtp_user = 'no-replay' . $this->config->item('default_email_box');
        }

        if (is_array($to)) {
            foreach ($to as $send_to) {
                $message->addTo($send_to);
            }
        }
        else {
            $message->setTo(array($to));
        }
        if (!empty($cc)) {
            foreach ($cc as $send_cc) {

                $message->addcc($send_cc);
            }
        }
        $message->setBcc(array($this->config->item('zenfile_bcc_email')));
        $message->setSubject($subject);
        $text = $this->swift_libraries->embed_images($message, $content);
        $message->setBody($text, 'text/html');
        if ($attachments) {
            foreach ($attachments as $file)
            {
                $message->attach(Swift_Attachment::fromPath('../pm/' . $file['location']));
            }
        }
        if ($this->config->item('smpt_sendmail') == '1') {
            //use smtp
            $transport = Swift_SmtpTransport::newInstance('127.0.0.1', 465, 'ssl')
                ->setUsername($smtp_user)
                ->setPassword($this->config->item('zenfile_default_email_password'));
        }
        else {
            //use sendmail
            $transport = Swift_MailTransport::newInstance();
        }
        $mailer = Swift_Mailer::newInstance($transport);

        if ($mailer->send($message)) {
            return true;
        }
        return false;
    }
}