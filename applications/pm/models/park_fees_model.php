<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Park_fees_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }
    
    public function get_list($search_string = false){
    	if($search_string){
    		$this->db->like('target_language', $search_string);
    	}
    	$query = $this->db->get('park_fees');
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return NULL;
    }
    
    public function insert($data){
    	$this->db->insert('park_fees', $data);
        return $this->db->insert_id();
    }
    
	public function get_by_id($fee_id){
    	$this->db->where('id',$fee_id);
		$query = $this->db->get('park_fees');
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return NULL;
    }
    
	public function delete($fee_id){
    	$this->db->where('id',$fee_id);
		$this->db->delete('park_fees');
    }
    
    public function update($data){
    	$this->db->where('id', $data['id']);
    	$this->db->update('park_fees', $data);
    }
    
}