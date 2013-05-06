<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Profile_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns customer's profile data
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     */

    public function get_profile_info()
    {
        $customer_id = $this->session->userdata('client_user_id');
        $this->db->where('id', $customer_id);
        $query = $this->db->get('customers');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Updates customer's profile
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     */
    public function update_profile()
    {

        $data = array(
            'email' => $this->input->post('email'),
            'firstname' => $this->input->post('first_name'),
            'lastname' => $this->input->post('last_name'),
            'company_name' => $this->input->post('company_name'),
            'address' => $this->input->post('address'),
            'address2' => $this->input->post('address2'),
            'city' => $this->input->post('city'),
            'state' => $this->input->post('state'),
            'zip_code' => $this->input->post('zip_code'),
            'country' => $this->input->post('country'),
            'phone_number' => $this->input->post('phone_number'),
            'phone_country_code' => $this->input->post('phone_country_code'),
            'ext' => $this->input->post('ext'),
            'fax' => $this->input->post('fax')
        );

        $customer_id = $this->session->userdata('client_user_id');
        $this->db->where('id', $customer_id);
        $this->db->update('customers', $data);
        $affected = $this->db->affected_rows();

        if ($affected > 0) {
            $this->session->set_userdata('client_lastname', $this->input->post('last_name'));
            $this->session->set_userdata('client_firstname', $this->input->post('first_name'));
            return TRUE;
        }
        return FALSE;
    }

    public function get_google_address_data()
    {
        $fresult = array();

        $search_string = $this->input->post('q');
        $address = urlencode($search_string);

        $url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&sensor=true';
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.202 Safari/535.1';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent); // allow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // times out after Ns

        $raw_result = curl_exec($ch); // run the whole process
        $zip_code = '';
        $result = json_decode($raw_result);
        if ($result->status == 'OK') {
            // If we have just 1 result from Google Maps API
            if (is_array($result->results) && count($result->results) == 1) {
                if (is_array($address_components = $result->results[0]->address_components)) {
                    $address = $address_components[0]->long_name . ' ' . $address_components[1]->long_name;
                    $city = $address_components[4]->long_name; // City
                    $country = $address_components[7]->long_name; // Country
                    $postal_code = $address_components[8]->long_name; // Postal Code
                    $formatted_address = $result->results[0]->formatted_address;

                    $fresult['city'] = $city;
                    $fresult['zip_code'] = $zip_code;
                    $fresult['country'] = $country;
                    $fresult['address'] = $address;
                    $fresult['formatted_address'] = $formatted_address;
                }
            }
            elseif (is_array($result->results) && count($result->results) > 1)
            {
                echo '<pre>';
                print_r($result->results);
                echo '</pre>';
                exit();
                $index = 0;
                foreach ($result->results as $result_entry)
                {
                    if (is_array($address_components = $result_entry->address_components)) {
                        $address = $address_components[0]->long_name . ' ' . $address_components[1]->long_name;
                        $city = $address_components[4]->long_name; // City
                        $country = $address_components[7]->long_name; // Country
                        $zip_code = isset($address_components[8]->long_name) ? $address_components[8]->long_name : ''; // Postal Code
                        $formatted_address = $result_entry->formatted_address;
                        $fresult[$index]['city'] = $city;
                        $fresult[$index]['zip_code'] = $zip_code;
                        $fresult[$index]['country'] = $country;
                        $fresult[$index]['address'] = $address;
                        $fresult[$index]['formatted_address'] = $formatted_address;

                        $index++;
                    }
                }
            }
        }


        if (is_ajax()) {
            echo json_encode($fresult);
        }
        else
        {
            return $fresult;
        }
    }

    public function get_bdv()
    {
        $this->db->select('managers.*');
        $this->db->join('customers', 'customers.bdv_id = managers.id');
        $this->db->where('customers.id', $this->session->userdata('client_user_id'));
        $query = $this->db->get('managers');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /*
     * Returns data for current PM
     *
     * @access	public
     * @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return	mixed
     */

    public function get_pm_profile()
    {
        $user_id = $this->session->userdata('manager_user_id');
        $this->db->where('id', $user_id);
        $query = $this->db->get('managers');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Updates PM profile
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     */
    public function update_pm_profile()
    {
        $data = array(
            'firstname' => $this->input->post('firstname'),
            'lastname' => $this->input->post('lastname'),
            'email' => $this->input->post('email'),
            'phone' => $this->input->post('phone'),
        );
        $user_id = $this->session->userdata('manager_user_id');
        $this->db->where('id', $user_id);
        $this->db->update('managers', $data);
    }
}

?>