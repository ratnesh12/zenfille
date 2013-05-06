<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Files_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns zip file by its hash
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string    file hash
     * @param    bool    TRUE - last file
     * @return    mixed
     */
    public function get_zip_by_hash($hash = '')
    {
        $this->db->select('*');
        $this->db->where('hash', $hash);
        $this->db->order_by('id', 'desc');
        $query = $this->db->get('zipped_files');
        return $query->row_array();
    }

    /**
     * Adds zip file to database
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    string    file path
     * @param    string    file hash
     * @param    string    email type
     * @param    string    countries
     * @param    string    countries (json)
     * @return    mixed
     */
    public function add_zipped_file($case_id = '', $path = '', $hash = '', $email_type = '', $countries)
    {
        $mime_type = mime_content_type($path);
        $data = array(
            'case_id' => $case_id,
            'path' => $path,
            'mime_type' => $mime_type,
            'email_type' => $email_type,
            'created_at' => date('Y-m-d H:i:s'),
            'hash' => $hash,
            'countries' => $countries
        );
        $this->db->insert('zipped_files', $data);

        return ($this->db->affected_rows() > 0) ? $this->db->insert_id() : FALSE;
    }

    /**
     * Returns file entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    int    user ID
     * @param    int    file type
     * @param    bool    TRUE - last entry
     * @return    mixed
     */
    public function get_file_by_type($case_id = '', $user_id = '', $file_type = 1, $last = FALSE)
    {
        $this->db->limit(1);
        if ($last) {
            $this->db->order_by('created_at', 'desc');
        }
        $this->db->where('case_id', $case_id);
        $this->db->where('file_type_id', $file_type);
        if (!empty($user_id)) {
            $this->db->where('user_id', $user_id);
        }
        $query = $this->db->get('cases_files');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Returns file entry by give ID
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    file ID
     * @return    mixed
     */
    public function get_file_by_id($file_id)
    {
        $this->db->order_by('created_at', 'desc');
        if (is_array($file_id)) {
            $this->db->where_in('id', $file_id);
        } else {
            $this->db->where('id', $file_id);
            $this->db->limit(1);
        }

        $query = $this->db->get('cases_files');
        if ($query->num_rows()) {
            if (is_array($file_id)) {
                return $query->result_array();
            } else {
                return $query->row_array();
            }

        }
        return NULL;
    }

    /**
     * Returns file entry by give ID
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    case ID
     * @param    array    a list of file types
     * @return    mixed
     */
    public function get_countries_by_file_types($case_id = '', $file_types = array())
    {
        $q = 'SELECT DISTINCT cs.id, cs.country
			  FROM `zen_cases_countries` cc, 
			  	   `zen_countries` cs, 
				   `zen_cases` c, 
				   `zen_cases_files` cf, 
				   `zen_files_countries` fc
			  WHERE (c.id = ' . $case_id . ') AND
			  		(c.id = cc.case_id) AND
					(cc.country_id = cs.id) AND
					(cf.id = fc.file_id) AND
					(cf.file_type_id IN (' . implode(',', $file_types) . ')) AND
					(fc.country_id = cs.id)';

        $query = $this->db->query($q);
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    public function get_file_countries($file_id)
    {
        $this->db->where('files_countries.file_id', $file_id);
        $this->db->join('countries', 'countries.id = files_countries.country_id');
        $query = $this->db->get('files_countries');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    // function for enabling/disabling "download all confirmation report link"
    // recognizes if at least 1 file exist
    function is_have_filing_report_files_with_assigned_countries($case_id) {
        $this->db->where('case_id' , $case_id);
        $this->db->where('file_type_id' , 6);
        $this->db->join('files_countries' , 'files_countries.file_id = cases_files.id');
        $query = $this->db->get('cases_files');
        return $query->result();
    }

    function insert_file($options = array()) {
        $this->db->insert('cases_files' , $options);
        return $this->db->insert_id();
    }

    function insert_file_link($options = array()) {
        $this->db->insert('files_countries' , $options);
        return $this->db->insert_id();
    }

}

?>