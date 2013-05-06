<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Park_fees extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->app_security_model->check_session()) {
            redirect('http://' . $_SERVER["HTTP_HOST"] . '/login/');
        }
    }
    
    function index(){
    	$this->load->model('park_fees_model','park_fees');
    	$data = array();
    	if(!$search_string = $this->input->post('search_string')){
    		$search_string = '';
    	}
    	$data['search_string'] = $search_string;
    	$data['park_fees'] = $this->park_fees->get_list($search_string);
    	$header['selected_menu'] = 'park_fees';
        $header['page_name'] = 'Park Fees';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            'Park Fees'
        );
    	
    	$this->load->view('parts/header', $header);
        $this->load->view('park_fees/list', $data);
        $this->load->view('footer');
    }
    
    function create_edit($fee_id = false){
    	$this->load->model('park_fees_model','park_fees');
    	$data = array();
    	$this->form_validation->set_rules('target_language', 'Target Language', 'required');
        $this->form_validation->set_rules('standart_rate', 'Park Client Rate', 'required');
        $this->form_validation->set_rules('zenfile_client_rate', 'Zenfile Client Rate', 'required');
        $this->form_validation->set_rules('zenfile_rate', 'Zenfile Rate', 'required');
        $this->form_validation->set_rules('cost', 'Cost', 'required');
        $this->form_validation->set_rules('into_english', 'Park Rate Into English', 'required');
        $this->form_validation->set_rules('zenfile_into_english', 'Zenfile Rate Into English', 'required');
    	if($this->input->post('submit')){
    		if($this->form_validation->run()){
    			$fee = array();
    			$fee['target_language'] = set_value('target_language');
    			$fee['standart_rate']   = set_value('standart_rate');
    			$fee['zenfile_client_rate']    = set_value('zenfile_client_rate');
    			$fee['zenfile_rate']    = set_value('zenfile_rate');
    			$fee['cost']            = set_value('cost');
    			$fee['into_english']    = set_value('into_english');
    			$fee['zenfile_into_english']    = set_value('zenfile_into_english');
    			if($fee_id){
    				$fee['id']    = $fee_id;
    				$this->park_fees->update($fee);
    			}else{
    				$fee_id = $this->park_fees->insert($fee);
    			}
    			redirect('/park_fees/');
    		}
    	}else{
	    	if($fee_id){
	    		if($fee = $this->park_fees->get_by_id($fee_id)){
	    			$data['fee'] = $fee;
	    		}else{
	    			redirect('/park_fees/');
	    		}
	    	}else{
	    		$data['fee'] = array();
	    	}
    	}
    	
    	$header['selected_menu'] = 'park_fees';
        $header['page_name'] = $fee_id ? 'Edit Fee' : 'Create a Fee';
        $header['breadcrumb'] = array(
            anchor('/dashboard/', 'Dashboard'),
            anchor('/park_fees/', 'Park Fees'),
            $fee_id ? 'Edit Fee' : 'Create a Fee'
        );
    	
    	$this->load->view('parts/header', $header);
        $this->load->view('park_fees/create_edit', $data);
        $this->load->view('footer');
    }
    
    function delete($fee_id){
    	$this->load->model('park_fees_model','park_fees');
    	$this->park_fees->delete($fee_id);
    	redirect('/park_fees/');
    }
    
}