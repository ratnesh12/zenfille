<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends CI_Controller
{

    public function index()
    {
        if ($this->app_security_model->check_session()) {
            redirect('/dashboard/');
        }
        else
        {
            redirect('http://' . $this->input->server('HTTP_HOST') . '/new_project/login');
        }
    }

    /**
     * Login to system
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     *
     */
    public function login(){
 
	    if ($type = $this->app_security_model->do_login()){		 
			     // Depending on user role script redirects to different pages   
			
				if ($type == 'supervisor' || $type == 'pm'){
               
                $previous_url = $this->input->cookie('previous_url');
                if (!empty($previous_url)){                  
				    $this->input->set_cookie('previous_url', NULL);
                    redirect('https://' . $this->input->server('HTTP_HOST') . '/new_project/pm/' . $previous_url);
                }else{
                    redirect('https://' . $this->input->server('HTTP_HOST') . '/new_project/pm/dashboard/');
                }
            }
            elseif ($type == 'admin'){
                redirect('https://' . $this->input->server('HTTP_HOST') . '/new_project/pm/admin/');
            }elseif($type == 'fa'){
                redirect('/fa/');
            }
            else
            {
                $previous_url = $this->input->cookie('previous_url');
                if (!empty($previous_url)) {
                    $this->input->set_cookie('previous_url', NULL);
                    redirect($previous_url);
                }
                else
                {
                    redirect('new_project/dashboard/');
                }
            }
        }else{
			 
       			redirect('http://' . $this->input->server('HTTP_HOST') . '/new_project/login');
        }
    }

    /**
     * Destroys user session
     *
     * @access    public
     * @author    Sergey Koshkarev <koshkarev.ss@gmail.com>
     *
     */
    public function logout()
    {
        $this->app_security_model->logout();
    }
}

/* End of file auth.php */
/* Location: ./application/controllers/auth.php */