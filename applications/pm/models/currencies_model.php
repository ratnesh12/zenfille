<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Currencies_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Updates currencies rates list
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    void
     * */
    public function update_rate_list()
    {
        //For the next command you will need the config option allow_url_fopen=On (default)
        $xml = simplexml_load_file('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
        //the file is updated daily between 2.15 p.m. and 3.00 p.m. CET

        $rates = array();
        $usd_rate = array();

        foreach ($xml->Cube->Cube->Cube as $rate)
        {
            $currency_code = $rate["currency"];
            $rates["$currency_code"] = $rate["rate"];
        }
        $usd_rate_in_euro = floatval($rates['USD']); // 1.3535

        $usd = 1.0000 / $usd_rate_in_euro;
        $one_euro = round($usd, 4); // 0,7388
        foreach ($rates as $key => $rate)
        {
            $rates[$key] = round(floatval($rate) * $one_euro, 4);
        }
        unset($rates['USD']);
        $rates['EUR'] = $one_euro;

        foreach ($rates as $code => $rate)
        {
            $data = array(
                'code' => $code,
                'rate' => $rate,
                'last_update' => date('Y-m-d H:i:s')
            );
            $this->db->where('code', $code);
            $query = $this->db->get('currencies_rates');
            if ($query->num_rows()) {
                $this->db->where('code', $code);
                $this->db->update('currencies_rates', $data);
            }
            else
            {
                $this->db->insert('currencies_rates', $data);
            }
        }
    }

    /**
     * Return currency entry by code
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string    currency code
     * @return    float
     * */
    public function get_currency_rate_by_code($code = 'EUR')
    {
        $this->db->where('code', $code);
        $query = $this->db->get('currencies_rates');
        if ($query->num_rows()) {
            $record = $query->row_array();
            return $record['rate'];
        }
        return '1';
    }

    /**
     * Returns a list of currencies
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    mixed
     * */
    public function get_list_currencies()
    {
        $q = 'SELECT cr.id as cur_id, c.country, cr.id, cr.code, cr.rate, DATE_FORMAT(cr.last_update, "%m/%d/%y %r") as last_update
		      FROM `zen_currencies_rates` cr, `zen_countries` c
			  WHERE (c.currency_code = cr.code)';
        $query = $this->db->query($q);
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Does search on currencies
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    string    search string
     * @return    mixed
     * */
    public function search_currencies($search_string)
    {
        $q = 'SELECT c.country, cr.id, cr.code, cr.rate, DATE_FORMAT(cr.last_update, "%m/%d/%y %r") as last_update
		      FROM `zen_currencies_rates` cr, `zen_countries` c
			  WHERE (c.currency_code = cr.code) AND
			  		(c.country LIKE "%' . $search_string . '%")';
        $query = $this->db->query($q);
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }

    /**
     * Returns currency entry by ID
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    entry ID
     * @return    mixed
     * */
    public function get_currency_record($record_id)
    {
        $this->db->select('currencies_rates.id, currencies_rates.code, currencies_rates.rate, countries.country');
        $this->db->join('countries', 'countries.currency_code = currencies_rates.code');
        $query = $this->db->where('currencies_rates.id', $record_id)->get('currencies_rates');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }

    /**
     * Updates currency entry
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @param    int    entry ID
     * @return    void
     * */
    public function update_currency_record($record_id)
    {
        $rate = $this->input->post('rate');
        $data = array(
            'rate' => $rate,
            'last_update' => date('Y-m-d H:i:s')
        );
        $this->db->where('id', $record_id);
        $this->db->update('currencies_rates', $data);
    }

    /**
     * Returns a list of currencies rates and their rates
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     * @return    array
     * */
    public function get_currencies_array()
    {
        // array(key = currency code => value = exchange rate)
        $result = array();
        $query = $this->db->get('currencies_rates');
        if ($query->num_rows()) {
            foreach ($query->result_array() as $item)
            {
                $result[$item['code']] = $item['rate'];
            }
        }
        return $result;
    }
}

?>