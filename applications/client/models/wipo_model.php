<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Wipo_model extends CI_Model
{

    protected $curl_info = NULL;
    protected $purifier = NULL;
    public $cookie = NULL;
    public $user_agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.202 Safari/535.1';

    public function __construct()
    {
        parent::__construct();

        $this->cookie = tempnam("/tmp", "CURLCOOKIE");

        if (!$this->purifier) {
            include_once(APPPATH . 'libraries/HTMLPurifier/HTMLPurifier.auto.php');

            $config = HTMLPurifier_Config::createDefault();
            $config = array();
            $this->purifier = new HTMLPurifier($config);
        }
    }

    protected function curlRequest($url, $referer, $params = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); // allow redirects
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent); // allow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // return into a variable
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);

        if (isset($params['CURLOPT_HEADER']))
            curl_setopt($ch, CURLOPT_HEADER, $params['CURLOPT_HEADER']);

        if (isset($params['CURLOPT_TIMEOUT']))
            curl_setopt($ch, CURLOPT_TIMEOUT, $params['CURLOPT_TIMEOUT']);

        if (isset($params['CURLOPT_COOKIESESSION']))
            curl_setopt($ch, CURLOPT_COOKIESESSION, $params['CURLOPT_COOKIESESSION']);

        $result = curl_exec($ch);
        $this->curl_info = curl_getinfo($ch);
        curl_close($ch);

        return $result;
    }


    protected function request_openFirstPage($url, $referer)
    {
        return $this->curlRequest($url, $referer, array(
            'CURLOPT_COOKIESESSION' => true,
            'CURLOPT_HEADER' => false
        ));
    }


    protected function request_documentsTab($url, $referer)
    {
        return $this->curlRequest($url, $referer, array(
            'CURLOPT_HEADER' => false
        ));
    }


    protected function request_mainRequest($url, $referer)
    {
        return $this->curlRequest($url, $referer, array(
            'CURLOPT_TIMEOUT' => 30,
            'CURLOPT_COOKIESESSION' => true,
            'CURLOPT_HEADER' => true
        ));
    }


    protected function request_claimsTab($url, $referer)
    {
        $result = $this->curlRequest($url, $referer, array(
            'CURLOPT_COOKIESESSION' => true,
            'CURLOPT_TIMEOUT' => 200,
            'CURLOPT_HEADER' => false
        ));

        $result = $this->removeDuplicateRootTag($result);
        $result = $this->purifier->purify($result);
        return $result;
    }


    protected function downloadZip($xml_url, $zip_path)
    {
        $fp = fopen($zip_path, 'w+'); //This is the file where we save the information
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $xml_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
        $result = curl_exec($ch); // run the whole process
        curl_close($ch);
        fclose($fp);

        return $result;
    }


    // needed when not clean html received
    // removes duplicate <html> tags
    protected function removeDuplicateRootTag($html_string)
    {
        if (substr_count($html_string, '<html') > 1) {
            $position['real_html_open_tag'] = strpos($html_string, '<html') + 20;
            $position['real_html_close_tag'] = strrpos($html_string, '</html') - 20;

            $start_of_clean_html = substr($html_string, 0, $position['real_html_open_tag']);
            $not_clean_html = substr($html_string, $position['real_html_open_tag'], $position['real_html_close_tag']);
            $clean_html = str_ireplace(array('<html', '</html>'), array('<div', '</div>'), $not_clean_html);
            $end_of_clean_html = substr($html_string, $position['real_html_close_tag'], strlen($html_string));
            $html_string = $start_of_clean_html . $clean_html . $end_of_clean_html;
        }
        return $html_string;
    }


    /********************************PUBLIC BELOW***************************************/

    public function get_entry($wo_number = '')
    {
        $this->db->select('application_title as title, number_priorities_claimed, number_pages_drawings, number_pages_claims, number_pages, first_priority_date, international_filing_date, search_location, applicant, publication_language, 30_month_filing_deadline, 30_month_filing_deadline filing_deadline, 31_month_filing_deadline, number_claims, number_words, number_words_claims as number_words_in_claims, number_words_in_application as number_words, sequence_listing, wo_number, pct_number');
        $this->db->where('wo_number', $wo_number);
        $this->db->or_where('pct_number', $wo_number);
        $query = $this->db->get('wipo_data');

        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    public function append_data_to_case($case_number = '', $data = array())
    {

        if (is_array($data)) {

            $data['wipo_wo_number'] = $data['wo_number'];
            unset($data['wo_number']);
            $data['wipo_pct_number'] = $data['pct_number'];
            unset($data['pct_number']);
            $data['application_title'] = $data['title'];
            $data['number_words'] = isset($data['number_words_in_application']) ? $data['number_words_in_application'] : 0;
            unset($data['number_words_in_application']);

            $this->db->where('case_number', $case_number);
            $this->db->update('cases', $data);

            return TRUE;
        }

        return FALSE;
    }

    public function light_parser($wo_number = '')
    {
        $is_exist_record = $this->get_entry($wo_number);

        if ($is_exist_record) {
            return $is_exist_record;
        }

        $wo_number = $this->prepare_number_for_parser($wo_number);

        $is_wo = preg_match('/[wW][oO].*/', $wo_number);
        $is_pct = preg_match('/[pP][cC][tT].*/', $wo_number);

        if (!$is_wo && !$is_pct) {
            return false;
        }


        $not_changed_wo = $wo_number;
        $ci = & get_instance();
        $ci->load->library('SimpleXML');
        $ci->load->library('unzip');
        $ci->load->library('domparser');
        $ci->load->library('logger');
        $ci->load->library('email');
        $this->load->model('cases_model', 'cases');
        $this->load->model('estimates_model', 'estimates');

        ini_set('max_execution_time', '9999');
        ini_set('memory_limit', '2048M');

        $replace_arr = array(' ', '/', ',', '.', '|', ':', ';', '%', '$', '-', '+', '~', '&');
        $wo_number = strtoupper(str_replace($replace_arr, '', urldecode($wo_number)));
        $wipo_search_referrer = 'http://www.wipo.int/patentscope/search/en/search.jsf';

        // If we have WO number
        // A list of working proxy

        if (file_exists('/tmp/CURLCOOKIE')) {
            @unlink('/tmp/CURLCOOKIE');
        }


        // Open the first page to find "Documents" tab link
        // =====================================================================
        //sleep(3);
        if (!$is_wo) {
            $base_wipo_search_url = 'http://patentscope.wipo.int/search/en/' . str_replace('/', '', $wo_number);
            $response = $this->request_openFirstPage($base_wipo_search_url, $wipo_search_referrer);
        } else {
            $wo_search_url = "http://patentscope.wipo.int/search/en/detail.jsf?docId=$wo_number";
            $response = $this->request_openFirstPage($wo_search_url, $wipo_search_referrer);

        }
        preg_match_all("/id=\"detailPCTtableWO\">([^<]*)[\S\s]*id=\"detailPCTtableAN\">([^<]*)[\S\s]*Priority Data:[\W]*<\/B><\/TD>[\W]*<TD>[\W]*<TABLE CELLSPACING=\"0\" CELLPADDING=\"0\">/",
            $response, $out, PREG_PATTERN_ORDER);

        preg_match_all('/<meta name=\"description\" content=\"([^"]*)\" \/>/', $response, $title, PREG_PATTERN_ORDER);
        preg_match_all('/<TR><TD>[^<]*<\/TD><TD WIDTH=\"12\">&nbsp;<\/TD><TD>([^<]*)<\/TD>/', $response, $dates, PREG_PATTERN_ORDER);
        preg_match_all('!detailPCTtablePubDate">([^<]*)</TD>!', $response, $publication_date, PREG_PATTERN_ORDER);
        preg_match_all('/<B>Applicants:[\W]*<\/B><\/TD>[\W]*<TD><span class=\"notranslate\">([\S\s]*)<\/span>[\S\s]*Inventors:/', $response, $applicants_not_parsed, PREG_PATTERN_ORDER);
        if (!$applicants_not_parsed) {
            return false;
        }

        if (isset($applicants_not_parsed[1][0])) {
            preg_match_all(
                '!<B>([^<]*)<\/B>[^<]*<I>\(For All Designated States Except[^<]*<\/I>!'
                , $applicants_not_parsed[1][0], $applicants, PREG_PATTERN_ORDER);

        }

        if (empty($applicants[1][0])) {
            preg_match_all(
                '!<B>([^<]*)<\/B>!'
                , $applicants_not_parsed[1][0], $applicants, PREG_PATTERN_ORDER);
        }
        foreach ($dates[1] as $key => $date) {
            if (!isset($min_date) || strtotime($date) < $min_date) {
                $min_date = strtotime($date);
            }
        }

        $result['applicant'] = '';
        if (isset($applicants[1]) && !empty($applicants[1]))
            foreach ($applicants[1] as $key => $applicant) {
                $result['applicant'] .= $applicant . '; ';
            }

        if (isset($publication_date[1][0])) {
            $result['publication_date'] = date('Y-m-d', strtotime($publication_date[1][0]));
        }

        if (isset($out[1][0]))
            $result['wo_number'] = $out[1][0];
        if (isset($out[2][0]))
            $result['pct_number'] = $out[2][0];
        if (isset($title[1][0])) {
            $result['application_title'] = strip_tags(htmlspecialchars_decode($title[1][0]));

        }
        if (isset($min_date)) {
            $result['first_priority_date'] = date('Y-m-d', $min_date);
        }
        if (isset($result['first_priority_date'])) {
            $date = new DateTime($result['first_priority_date']);
            $year = $this->estimates->addYears($date, '2');
            $date = new DateTime($year);


            $result['30_month_filing_deadline'] = $this->estimates->addMonths($date, '6');
            $date = new DateTime($result['30_month_filing_deadline']);
            $result['31_month_filing_deadline'] = $this->estimates->addMonths($date, '1');
        }
        $result['created_at'] = date('Y-m-d H:i:s');
        if (isset($result['wo_number'])) {
            if (!$is_exist_record) {
                $this->insert_wipo_data($result);
            }
            $result = $this->get_entry($not_changed_wo);
        }
        return $result;
    }

    function insert_wipo_data($options = array())
    {
        $this->db->insert('wipo_data', $options);
        return $this->db->insert_id();
    }

    public function parse_entry($wo_number = '')
    {

        $ci = & get_instance();
        $ci->load->library('SimpleXML');
        $ci->load->library('unzip');
        $ci->load->library('domparser');
        $ci->load->library('logger');
        $ci->load->library('email');
        $ci->load->model('send_emails_model', 'send_emails');

        $kkk = 0;

        $wo_number = $this->prepare_number_for_parser($wo_number);

        ini_set('max_execution_time', '9999');
        ini_set('memory_limit', '2048M');

        $replace_arr = array(' ', '/', ',', '.', '|', ':', ';', '%', '$', '-', '+', '~', '&');

        // Upload folder
        // =================================================================
        $upload_folder = 'uploads/tmp/wipo';
        if (!file_exists($upload_folder)) {
            mkdir($upload_folder, 0777, TRUE);
        }
        @chmod($upload_folder, 0777);
        // =================================================================

        $wo_number = str_replace($replace_arr, '', urldecode($wo_number));

        $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);
        $wipo_search_referrer = 'http://www.wipo.int/patentscope/search/en/search.jsf';
        $number_words = 0;

        // If we have WO number
        // A list of working proxy

        if (file_exists('/tmp/CURLCOOKIE')) {
            @unlink('/tmp/CURLCOOKIE');
        }

        $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);

        // Open the first page to find "Documents" tab link
        // =====================================================================
        //sleep(3);
        $base_wipo_search_url = 'http://patentscope.wipo.int/search/en/' . str_replace('/', '', $wo_number);
        $result = $this->request_openFirstPage($base_wipo_search_url, $wipo_search_referrer);

        preg_match_all('!detailPCTtablePubDate">([^<]*)</TD>!', $result, $publication_date, PREG_PATTERN_ORDER);

        //$this->logger->write(__FILE__.':'.__LINE__."\n".print_r($result, true));

        if (FALSE !== strpos($result, 'Page not found') || empty($result)) {
            $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Empty result for request: ' . $base_wipo_search_url);
            $from = 'portal' . $this->config->item('default_email_box');
            $to = 'it' . $this->config->item('default_email_box');
            $subject = 'Scraping Error';
            $message = 'Application number: ' . $wo_number;
            $ci->send_emails->send_email($from, $from, $subject, $message, $to, false, false);
            return FALSE;
        } else {
            $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Result is OK: ' . $base_wipo_search_url);
        }

        $result = $this->purifier->purify($result);

        $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);
        $dom = $this->domparser->str_get_html($result);
        $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);


        if (FALSE !== strpos($result, 'tab=PCTDescription'))
            $description_link = TRUE;
        if (FALSE !== strpos($result, 'tab=PCTDocuments'))
            $documents_link = TRUE;
        if (FALSE !== strpos($result, 'tab=PCTClaims'))
            $claims_link = TRUE;

        // New parsing code
        if (preg_match('!International Application No\.:(.*?)<\/tr>!si', $result, $match)) {
            if (preg_match('!<td valign="top">(.*?)<\/td>!si', $match[1], $match)) {
                $appl_number = clearString($match[1]);
            }
        } else {
            return FALSE;
        }

        if (preg_match('!Pub. No\.:(.*?)width!si', $result, $match)) {
            if (preg_match('!<td valign="top">(.*?)<\/td>!si', $match[1], $match)) {
                $wo_number = clearString($match[1]);
            }
        } else {
            return FALSE;
        }
        if (preg_match('!International Filing Date:(.*?)<\/tr>!si', $result, $match)) {
            if (preg_match('!<td valign="top">(.*?)<\/td>!si', $match[1], $match)) {
                $international_filing_date = clearString($match[1]);
            }
        } else {
            return FALSE;
        }

        // End of new parsing code

        //$appl_number = isset($appl_number -> innertext) ? $appl_number -> innertext : '';

        // =====================================================================
        $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);
        // "Documents" tab
        if (!empty($description_link)) {
            $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);
            $url = 'http://patentscope.wipo.int/search/en/detail.jsf?docId=' . str_replace('/', '', $wo_number) . '&recNum=1&tab=PCTDescription&office=&prevFilter=&sortOption=&queryString=';

            $result = $this->request_documentsTab($url, $base_wipo_search_url);

            if (empty($result)) {
                log_message('error', 'ERROR: ' . __FILE__ . ':' . __LINE__ . "\n" . 'Empty result for request: ' . $url);
                return FALSE;
            }
            else
                log_message('error', 'SUCCESS: ' . __FILE__ . ':' . __LINE__ . "\n" . 'Result is OK: ' . $url);

            // Open "Documents" tab
            // ====================================================================================
            $html = $ci->domparser->str_get_html($result);
            $words_section = $html->find('td[@id="detailTabForm:PCTDescription"]', 0);

            if (!empty($words_section)) {
                $number_words_arr = explode(' ', $words_section->plaintext);
                $number_words = count($number_words_arr) * 0.968;
            }
        }
        else
        {
            $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);
            log_message('error', 'ERROR: ' . __FILE__ . ':' . __LINE__ . "\n" . 'Directions link is empty: ');
        }

        $url = 'http://patentscope.wipo.int/search/en/detail.jsf?docId=' . str_replace('/', '', trim($wo_number)) . '&recNum=1&tab=PCTDocuments&office=&prevFilter=&sortOption=&queryString=';
        $result = $this->request_mainRequest($url, "http://patentscope.wipo.int/search/en/search.jsf");
        $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);

        // First table #detailTabForm:j_id890
        // International Application Status Report
        libxml_use_internal_errors(true);
        $test_object = new DOMDocument();
        $result = str_replace('&', '&amp;', $result);

        if ($test_object->loadHTML($result)) {
            $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);
            $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);
            $test_object = NULL;
            $html = $this->domparser->str_get_html($result);

            // Look for "Sequence Listings" row
            $sequence_listing_search = $html->find('table[@class="rich-table"]', 1);

            $sequence_listing = '0';
            if (is_object($sequence_listing_search)) {
                foreach ($sequence_listing_search->find('td') as $sq_entry)
                {
                    $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);
                    if (isset($sq_entry) && count($sq_entry) > 0) {
                        if (strtolower($sq_entry->plaintext) == 'sequence listings') {
                            $sequence_listing = '1';
                        }
                    }
                }
            }

            // Look for additional ZIP file (ISR)
            if (is_object($html)) {
                if (method_exists($html, 'find')) {
                    $temp_obj = $html->find('td[@id="detailTabForm:PCTDocuments"] table.rich-table', 1);
                    if (is_object($temp_obj)) {
                        $additional_zip = $temp_obj->find('td', 1);
                    }
                }
            }
            if (isset($additional_zip) && (count($additional_zip) > 0)) {
                $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);
                if (strpos(strtolower($additional_zip->plaintext), 'without') > 0) {
                    $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);
                    // Then download one file more from TD below

                    $add_link = $html->find('td[@id="detailTabForm:PCTDocuments"] table.rich-table', 1)->find('td', 1)->find('a', 1);

                    if (isset($add_link->href)) {
                        $xml_url = 'http://patentscope.wipo.int' . $add_link->href;

                        if (!file_exists('uploads/tmp/wipo')) {
                            mkdir('uploads/tmp/wipo', 0777);
                        }
                        @unlink('uploads/tmp/wipo/tempzip2.zip');
                        $zip_path = 'uploads/tmp/wipo/tempzip2.zip';

                        // download zip file
                        $result = $this->downloadZip($xml_url, $zip_path);

                        $ci->unzip->allow(array('xml'));
                        $ci->unzip->extract($zip_path, 'uploads/tmp/wipo');

                        rename('uploads/tmp/wipo/wo-published-application.xml', 'uploads/tmp/wipo/search-report.xml');
                        @unlink($zip_path);
                    }
                }
            }
            // 0 - PDF
            // 1 - XML
            $xml_link = array();

            if (is_object($html)) {
                if (method_exists($html, 'find')) {
                    $temp_obj = $html->find('td[@id="detailTabForm:PCTDocuments"] table.rich-table', 1);
                    if (is_object($temp_obj)) {
                        $xml_link = $temp_obj->find('td', 3)->find('a');
                    }
                }
            }
            if (isset($xml_link[1])) {
                $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);

                $xml_url = 'http://patentscope.wipo.int' . $xml_link[1]->href;
                // Link 2 - XML REPORTS
                $zip_path = $upload_folder . '/tempzip.zip';

                // download zip file
                $result = $this->downloadZip($xml_url, $zip_path);

                $ci->unzip->allow(array('xml'));
                $ci->unzip->extract($zip_path, 'uploads/tmp/wipo/');
                $fresult = $this->parsexml_file('uploads/tmp/wipo/wo-published-application.xml', 'uploads/tmp/wipo/search-report.xml');

            }
            else
            {
                return FALSE;
            }

            $fresult['sequence_listing'] = $sequence_listing;
        }
        else
        {
            $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);
            log_message('error', 'ERROR: ' . __FILE__ . ':' . __LINE__ . "\n" . 'Test_object failed: ' . print_r($result, true));
            return FALSE;
        }


        // "Claims" tab
        $url = 'http://patentscope.wipo.int/search/en/detail.jsf?docId=' . str_replace('/', '', $wo_number) . '&recNum=1&tab=PCTClaims&office=&prevFilter=&sortOption=&queryString=';
        $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);
        $result = $this->request_claimsTab($url, "http://patentscope.wipo.int/search/en/search.jsf");
        $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);
        if (empty($result)) {
            $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);
            log_message('error', 'ERROR: ' . __FILE__ . ':' . __LINE__ . "\n" . 'Empty result for request: ' . $url);
            return FALSE;
        }
        else
            log_message('error', 'SUCCESS: ' . __FILE__ . ':' . __LINE__ . "\n" . 'Result is OK: ' . $url);
        $number_claims = 'Unable to Scrape';
        $number_words_claims = 0;

        if (preg_match_all('!<p>[ ]?([0-9]+[\.] )[^<]*</p>!si', $result, $match)) {
            if (!empty($match[1])) {
                $number_claims = intval(end($match[1]));
            }
        } else {
            $number_claims = false;
        }

        $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);

        if (isset($publication_date[1][0])) {
            $fresult['publication_date'] = date('Y-m-d', strtotime($publication_date[1][0]));
            ;
        }
        $fresult['number_claims'] = $number_claims;
        $fresult['number_words'] = $number_words;
        $fresult['number_words_claims'] = $number_words_claims;
        $fresult['number_words_in_application'] = ($number_words_claims + $number_words);
        $fresult['appl_number'] = $appl_number;
        $tmpdate = explode('.', $international_filing_date);
        $fresult['international_filing_date'] = $tmpdate[2] . '-' . $tmpdate[1] . '-' . $tmpdate[0];

        $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . 'Trace - ' . $kkk++);

        $this->db->where('wo_number', $wo_number);
        $this->db->where('pct_number', $fresult['appl_number']);
        $num = $this->db->count_all_results('wipo_data');

        $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . ' Num rows=' . $num);
        $this->logger->write(__FILE__ . ':' . __LINE__ . "\n" . ' Last updating settings_hard with $settings=' . print_r($this->db->last_query(), true));
        $fresult['applicant'] = str_ireplace(';', '; ', $fresult['applicant']);

        if (empty($fresult['application_title'])) {
            log_message('error', 'empty application title');
            return NULL;
        }

        $data = array(
            'title' => $fresult['application_title'],
            'number_priorities_claimed' => $fresult['number_priorities_claimed'],
            'number_pages_drawings' => $fresult['number_pages_drawings'],
            'number_pages_claims' => $fresult['number_pages_claims'],
            'number_pages' => $fresult['number_pages'],
            'first_priority_date' => $fresult['first_priority_date'],
            'filing_deadline' => $fresult['filing_deadline'],
            'search_location' => $fresult['search_location'],
            'applicant' => $fresult['applicant'],
            'publication_language' => $fresult['publication_language'],
            '30_month_filing_deadline' => $fresult['_30_month_filing_deadline'],
            '31_month_filing_deadline' => $fresult['_31_month_filing_deadline'],
            'number_claims' => $fresult['number_claims'],
            'number_words' => $fresult['number_words_in_application'],
            'number_words_in_claims' => '',
            'number_words_in_application' => $fresult['number_words_in_application'],
            'sequence_listing' => $fresult['sequence_listing'],
            'wo_number' => $wo_number,
            'pct_number' => $fresult['appl_number'],
            'international_filing_date' => $fresult['international_filing_date'],
            'publication_date' => $fresult['publication_date']
        );

        return $data;
    }

    public function parsexml_file($application_xml_file = '', $search_report_xml_file = '')
    {
        $ci = & get_instance();
        $ci->load->library('SimpleXML');
        $ci->load->library('Domparser');
        $this->load->model('estimates_model', 'estimates');

        $result = array();

        $applicant = '';
        $application_title = '';
        $first_priority_date = '';
        $filing_deadline = '';
        $number_priorities_claimed = 0;
        $number_pages_drawings = 0;
        $number_pages_claims = 0;
        $number_pages = 0;
        $location_search_report = '';
        $publication_language = '';
        $_30_month_filing_deadline = '';
        $_31_month_filing_deadline = '';

        $xml_file = $application_xml_file;

        if (file_exists($xml_file)) {
            $xml_raw = file_get_contents($xml_file);

            $xml_data = $ci->simplexml->xml_parse($xml_raw);

            if (isset($xml_data['wo-bibliographic-data']['wo-priority-info']) && is_array($xml_data['wo-bibliographic-data']['wo-priority-info'])) {
                if (isset($xml_data['wo-bibliographic-data']['wo-priority-info']['priority-claim']) && (count($xml_data['wo-bibliographic-data']['wo-priority-info']['priority-claim']) > 0)) {
                    $row_item = $xml_data['wo-bibliographic-data']['wo-priority-info']['priority-claim'];

                    if (array_key_exists('@attributes', $xml_data['wo-bibliographic-data']['wo-priority-info']['priority-claim']) && is_array($xml_data['wo-bibliographic-data']['wo-priority-info']['priority-claim'])) {
                        $number_priorities_claimed = $xml_data['wo-bibliographic-data']['wo-priority-info']['priority-claim']['@attributes']['sequence'];
                    }
                    elseif (count($xml_data['wo-bibliographic-data']['wo-priority-info']['priority-claim']) > 1)
                    {
                        $highest = 0;

                        foreach ($row_item as $item)
                        {
                            $priorites_claims_arr[] = $item['@attributes']['sequence'];
                        }
                        $number_priorities_claimed = max($priorites_claims_arr);
                    }
                }
            }

            // ===================================================
            if (isset($xml_data['drawings']) && is_array($xml_data['drawings'])) {
                // Get wo-published-application.xml, look for <drawings> and calculate range between first doc-page id= and the last before </drawings
                if (count($xml_data['drawings']['doc-page']) > 1) {
                    $first_val = intval(str_replace('docp', '', $xml_data['drawings']['doc-page'][0]['@attributes']['id']));
                    $last_val = intval(str_replace('docp', '', $xml_data['drawings']['doc-page'][count($xml_data['drawings']['doc-page']) - 1]['@attributes']['id']));
                    $number_pages_drawings = $last_val - $first_val + 1;
                }
                else
                {
                    $number_pages_drawings = 0;
                }
            }
            // ===================================================
            if (isset($xml_data['claims']) && is_array($xml_data['claims'])) {
                $number_pages_claims = count($xml_data['claims']);
            }

            // Applicant
            // ===================================================

            $dom = new DOMDocument('1.0', 'iso-8859-1');
            $dom->loadXML($xml_raw);
            $xpath = new DOMXPath($dom);

            $entries = $xpath->query("//applicants/applicant[@designation='all-except-us']/addressbook/name");

            $applicant = '';

            if (!empty($entries)) {
                foreach ($entries as $entry) {
                    $applicant .= $entry->nodeValue . '; ';
                }
            }

            if (empty($applicant)) {
                $entries = $xpath->query("//applicants/applicant[@designation='all']/addressbook/name");

                if (!empty($entries)) {
                    foreach ($entries as $entry) {
                        $applicant .= $entry->nodeValue . '; ';
                    }
                }
            }

            // ===================================================
            // Application Title

            $dom = new DOMDocument('1.0', 'iso-8859-1');
            $dom->loadXML($xml_raw);
            $xpath = new DOMXPath($dom);

            $entries = $xpath->query("//wo-bibliographic-data/invention-title[@lang='en']");

            if (!empty($entries)) {
                foreach ($entries as $entry) {
                    $application_title = $entry->nodeValue;
                }
            }

            // ===================================================
            if (isset($xml_data['wo-bibliographic-data']['wo-priority-info']) && is_array($xml_data['wo-bibliographic-data']['wo-priority-info'])) {
                if (isset($xml_data['wo-bibliographic-data']['wo-priority-info']['priority-claim']) && is_array($xml_data['wo-bibliographic-data']['wo-priority-info']['priority-claim'])) {
                    if (isset($xml_data['wo-bibliographic-data']['wo-priority-info']['priority-claim'][0]['date'])) {

                        $temp_array = $xml_data['wo-bibliographic-data']['wo-priority-info']['priority-claim'];
                        $all_priorities_dates = array();
                        for ($i = 0; $i <= count($temp_array) - 1; $i++)
                        {
                            $temp_raw_date = $temp_array[$i]['date'];
                            $year = substr($temp_raw_date, 0, 4);
                            $month = substr($temp_raw_date, 4, 2);
                            $day = substr($temp_raw_date, 6, 2);
                            $all_priorities_dates[] = mktime(0, 0, 0, $month, $day, $year);
                        }

                        $first_priority_date = date('Y-m-d', min($all_priorities_dates));
                        unset($temp_array);
                        unset($all_priorities_dates);
                    }
                    else
                    {
                        $raw_date = $xml_data['wo-bibliographic-data']['wo-priority-info']['priority-claim']['date'];
                        $ci->load->model('cases_model', 'cases');
                        $year = substr($raw_date, 0, 4);
                        $month = substr($raw_date, 4, 2);
                        $day = substr($raw_date, 6, 2);
                        $date = mktime(0, 0, 0, $month, $day, $year);
                        $old_date = $date;
                        $new_date = $year . '-' . $month . '-' . $day;

                        $date = new DateTime($new_date);
                        $year = $this->estimates->addYears($date, '2');
                        $date = new DateTime($year);
                        $date31 = new DateTime($year);
                        $_30_month_filing_deadline = $this->estimates->addMonths($date, '6');
                        $_31_month_filing_deadline = $this->estimates->addMonths($date31, '7');
                        $first_priority_date = date('Y-m-d', $old_date);
                    }

                    $filing_deadline = date('Y-m-d', strtotime('+30 months', strtotime($first_priority_date)));
                }
            }
        }

        // Publication language
        if (isset($xml_data['wo-bibliographic-data']['application-reference'])) {
            $publication_language = $xml_data['wo-bibliographic-data']['application-reference']['document-id']['@attributes']['lang'];

        }

        // Location of search report
        if (isset($xml_data['wo-bibliographic-data']['search-authority']['isa']['country'])) {
            $location_search_report = strtolower($xml_data['wo-bibliographic-data']['search-authority']['isa']['country']);
            $multiple_location_search_report = 'jp_ru_au_cn_kr';
            if (strpos($multiple_location_search_report, $location_search_report) !== FALSE) {
                $location_search_report = $multiple_location_search_report;
            }
        }

        //
        /*
              Number of total pages
              If there is a separate Search Report, Get wo-published-application.xml, look for last<doc-page id = ###>
              If search report is included, Get wo-published-application.xml, look for last <doc-page id = ###>  before <wo-search-report>
          */

        if (isset($xml_data['drawings']) && is_array($xml_data['drawings'])) {
            if (isset($xml_data['drawings']['doc-page'][0])) {
                $number_pages = intval(str_replace('docp', '', $xml_data['drawings']['doc-page'][count($xml_data['drawings']['doc-page']) - 1]['@attributes']['id']));
            }
            else
            {
                $number_pages = intval(str_replace('docp', '', $xml_data['drawings']['doc-page']['@attributes']['id']));
            }
        }


        // ===================================================
        //  Take other XML from archive in the table: Later publication of international search report
        if (empty($number_pages) || $number_pages == 0) {
            $xml_file = $search_report_xml_file;
            if (file_exists($xml_file)) {
                $xml_raw = file_get_contents($xml_file);
                $xml_data = $ci->simplexml->xml_parse($xml_raw);

                if (is_array($xml_data['wo-search-report'])) {
                    if (is_array($xml_data['wo-search-report']) && (count($xml_data['wo-search-report']) > 0)) {
                        $last_index_parent_array = count($xml_data['wo-search-report']) - 1;
                        if (isset($xml_data['wo-search-report'][$last_index_parent_array]['doc-page']) && is_array($xml_data['wo-search-report'][$last_index_parent_array]['doc-page'])) {
                            $count_elements = count($xml_data['wo-search-report'][$last_index_parent_array]['doc-page']);
                            $last_element_child_array = $xml_data['wo-search-report'][$last_index_parent_array]['doc-page'][$count_elements - 1];
                            $number_pages = intval(str_replace('docp', '', $last_element_child_array['@attributes']['id']));
                        }
                    }
                }
            }
        }

        $result = array(
            'application_title' => $application_title,
            'number_priorities_claimed' => $number_priorities_claimed,
            'number_pages_drawings' => $number_pages_drawings,
            'number_pages_claims' => $number_pages_claims,
            'number_pages' => $number_pages,
            'first_priority_date' => $first_priority_date,
            'filing_deadline' => $filing_deadline,
            'search_location' => $location_search_report,
            'applicant' => $applicant,
            'publication_language' => $publication_language,
            '_30_month_filing_deadline' => $_30_month_filing_deadline,
            '_31_month_filing_deadline' => $_31_month_filing_deadline,
        );
        //log_message('error', 'result = '.json_encode($result));
        if (file_exists($application_xml_file)) {
            @unlink($application_xml_file);
        }
        if (file_exists($search_report_xml_file)) {
            @unlink($search_report_xml_file);
        }
        return $result;
    }

    public function append_wipo_data_entry($case_data = array())
    {
        $this->load->model('estimates_model', 'estimates');
        if (!empty($case_data['first_priority_date'])) {
            $date = new DateTime($case_data['first_priority_date']);
            $year = $this->cases->estimates->addYears($date, '2');
            $date = new DateTime($year);

            $case_data['30_month_filing_deadline'] = $this->estimates->addMonths($date, '6');
            $date = new DateTime($case_data['30_month_filing_deadline']);
            $case_data['31_month_filing_deadline'] = $this->estimates->addMonths($date, '1');
        }

        $data = array(
            'application_title' => $case_data['title'],
            'number_priorities_claimed' => $case_data['number_priorities_claimed'],
            'number_pages_drawings' => $case_data['number_pages_drawings'],
            'number_pages_claims' => $case_data['number_pages_claims'],
            'number_pages' => $case_data['number_pages'],
            'first_priority_date' => $case_data['first_priority_date'],
            'international_filing_date' => $case_data['international_filing_date'],
            'search_location' => $case_data['search_location'],
            'applicant' => $case_data['applicant'],
            'publication_language' => $case_data['publication_language'],
            '30_month_filing_deadline' => $case_data['30_month_filing_deadline'],
            '31_month_filing_deadline' => $case_data['31_month_filing_deadline'],
            'number_claims' => $case_data['number_claims'],
            'number_words' => $case_data['number_words'],
            'number_words_claims' => $case_data['number_words_in_claims'],
            'number_words_in_application' => $case_data['number_words_in_application'],
            'sequence_listing' => $case_data['sequence_listing'],
            'wo_number' => $case_data['wo_number'],
            'pct_number' => $case_data['pct_number']
        );
        if (isset($case_data['publication_date'])) {
            $data['publication_date'] = $case_data['publication_date'];
        }

        $this->db->insert('wipo_data', $data);

        return $this->db->insert_id();
    }

    function prepare_number_for_parser($number_for_parsing)
    {

        $elements = explode('/', $number_for_parsing);

        if (count($elements) != 3) {
            return false;
        }
        $is_wo = preg_match('/[wW][oO].*/', $number_for_parsing);

        foreach ($elements as $key => $element) {
            $elements[$key] = trim(strtoupper($element));
        }
        if ($is_wo) {
            if (strlen($elements[1]) == 2) {
                $elements[1] = '20' . $elements[1];
            }
        } else {
            if (strlen($elements[1]) == 4) {
                $newstring = substr_replace($elements[1], 20, 2, 0);
                $elements[1] = $newstring;
            }
        }

        if (strlen($elements[2]) < 6) {
            $zeros_to_add = 6 - strlen($elements[2]);
            $string_to_add = '';

            for ($i = 0; $i < $zeros_to_add; $i++) {
                $string_to_add .= '0';
            }
            $elements[2] = $string_to_add . $elements[2];
        }

        $ready_number = implode('/', $elements);

        return $ready_number;
    }
}