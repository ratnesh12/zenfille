<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Ip_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Removes IP address from a list of allowed IP addresses
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    IP id
     * @return    bool
     */
    public function delete_ip($ip_id)
    {
        $this->db->where('id', $ip_id);
        $this->db->delete('allowed_ip');

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Inserts new IP address to a list of allowed IP addresses
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     */
    public function insert_ip()
    {
        $ip_address = $this->input->post('ip_address');
        $is_active = $this->input->post('is_active');
        $description = $this->input->post('description');

        $data = array(
            'ip_address' => $ip_address,
            'is_active' => "$is_active",
            'description' => $description,
        );

        $this->db->insert('allowed_ip', $data);

        return ($this->db->affected_rows() > 0) ? $this->db->insert_id() : FALSE;
    }

    /**
     * Updates IP address entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    ID of ip
     * @param    string    type of user
     * @return    bool
     */
    public function update_ip($ip_id, $type = 'customer')
    {
        $ip_address = $this->input->post('ip_address');
        $is_active = $this->input->post('is_active');
        $description = $this->input->post('description');

        $data = array(
            'ip_address' => $ip_address,
            'is_active' => "$is_active",
            'description' => $description,
        );

        $this->db->where('id', $ip_id);
        $this->db->update('allowed_ip', $data);

        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Returns IP entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    ID of ip
     * @return    mixed
     */
    public function get_ip($ip_id = '')
    {
        $this->db->where('id', $ip_id);
        $query = $this->db->get('allowed_ip');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Returns all IP entries
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     */
    public function get_all_ip()
    {
        $query = $this->db->get('allowed_ip');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }
}

?>