<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Users_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Deletes user by ID
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    user ID
     * @param    string    user type
     * @return    bool
     */
    public function delete_user($user_id, $type = 'customer')
    {
        $this->db->where('id', $user_id);
        if ($type == 'customer' || $type == 'firm') {
            $this->db->delete('customers');
        }
        else
        {
            $this->db->delete('managers');
        }
        // Delete another related records
        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Inserts new user
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string    user type
     * @return    bool
     */
    public function insert_user($type = 'customer')
    {
        $this->load->model('customers_model', 'customers');
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $firstname = $this->input->post('firstname');
        $lastname = $this->input->post('lastname');
        $email = $this->input->post('email');

        $data = array(
            'username' => $username,
            'password' => md5(sha1($password)),
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'type' => $type
        );

        if ($type == 'customer' || $type == 'firm') {
            // Brain Daniel's id by default as Victor He asked to do in https://park12.teamlab.com/products/projects/tasks.aspx?prjID=342194&id=2304281

            if ($type == 'customer') {
                $firm_data = $this->customers($this->input->post('parent_firm_id'), $type);
                $data['parent_firm_id'] = $this->input->post('parent_firm_id');
                $data['bdv_id'] = $firm_data['bdv_id'];
                $data['manager_id'] = $firm_data['manager_id'];

                $this->db->update('customers', $data);
            }elseif ($type == 'firm') {
                $data['bdv_id'] = $this->input->post('parent_sales_id');
                $data['manager_id'] = $this->input->post('parent_manager_id');
            }
            if ($type == 'customer') {
                $firm = $this->customers->get_user($this->input->post('parent_firm_id'), $type);
                $data['bdv_id'] = $firm['bdv_id'];
                $data['manager_id'] = $firm['manager_id'];
                $data['address'] = $firm['address'];
                $data['address2'] = $firm['address2'];
                $data['phone_number'] = $firm['phone_number'];
                $data['ext'] = $firm['ext'];
                $data['fax'] = $firm['fax'];
                $data['city'] = $firm['city'];
                $data['state'] = $firm['state'];
                $data['zip_code'] = $firm['zip_code'];
                $data['country'] = $firm['country'];
                $data['google_formatted_address'] = $firm['google_formatted_address'];
                $data['company_name'] = $firm['company_name'];
            }
            $this->db->insert('customers', $data);
            $user_id = $this->db->insert_id();
        }
        else
        {
            $data['supervisor_id'] = $this->input->post('supervisor_id');

            $this->db->insert('managers', $data);
        }
        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Updates user entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    user ID
     * @param    string    user type
     * @return    bool
     */
    public function update_user($user_id, $type = 'customer')
    {
        $this->load->model('customers_model', 'customers');
        $password = $this->input->post('password');
        $confirm_password = $this->input->post('confirm_password');
        $firstname = $this->input->post('firstname');
        $lastname = $this->input->post('lastname');
        $email = $this->input->post('email');
        $blocked = $this->input->post('blocked');

        $data = array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email
        );
        if ((!empty($password)) && ($password == $confirm_password)) {
            $data['password'] = md5(sha1($password));
        }
        if ($blocked == '1') {
            $data['blocked'] = '1';
        }
        else
        {
            $data['blocked'] = '0';
            $data['login_attempts'] = '0';
        }
        $this->db->where('id', $user_id);
        if ($type == 'customer' || $type == 'firm') {
            $data['parent_firm_id'] = $this->input->post('parent_firm_id');
            $this->db->update('customers', $data);
        }
        else
        {
            unset($data['blocked']);
            unset($data['login_attempts']);
            $data['supervisor_id'] = $this->input->post('supervisor_id');
            $data['type'] = $this->input->post('type');
            $this->db->where('id', $user_id);
            $this->db->update('managers', $data);
        }
        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    public function get_supervisors()
    {
        $this->db->where('type', 'supervisor');
        $query = $this->db->get('managers');
        $supervisers = array('0' => 'Select Parent User');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $superviser) {
                $supervisers[$superviser['id']] = $superviser['firstname'] . ' ' . $superviser['lastname'];
            }
        }
        return $supervisers;
    }

    public function get_firms()
    {
        $this->db->where('type', 'firm');
        $query = $this->db->get('customers');
        $firms = array('0' => 'Select Parent User');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $firm) {
                $firms[$firm['id']] = $firm['firstname'] . ' ' . $firm['lastname'];
            }
        }
        return $firms;
    }

    public function get_sales()
    {
        $this->db->where('type', 'sales');
        $query = $this->db->get('managers');
        $sales = array('0' => 'Select Parent User');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $sale) {
                $sales[$sale['id']] = $sale['firstname'] . ' ' . $sale['lastname'];
            }
        }
        return $sales;
    }

    public function get_managers()
    {
        $this->db->where('type', 'pm');
        $this->db->or_where('type', 'supervisor');
        $query = $this->db->get('managers');
        $managers = array('0' => 'Select Manager');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $manager) {
                $managers[$manager['id']] = $manager['firstname'] . ' ' . $manager['lastname'];
            }
        }
        return $managers;
    }

    /**
     * Returns users entries
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     */
    public function get_all_users()
    {
        $q = 'SELECT c.id, c.username, c.firstname, c.lastname, c.type, c.email, c.is_deleted
			  FROM `zen_customers` c
			  UNION ALL
			  SELECT m.id, m.username, m.firstname, m.lastname, m.type, m.email, ""
			  FROM `zen_managers` m';
        $query = $this->db->query($q);
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }
}

?>