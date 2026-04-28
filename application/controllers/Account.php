<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Account extends CI_Controller {

	public function login() {
        if ($this->session->userdata("user")){
            redirect('Dashboard');
        }
		$this->load->view('v_login');
	}

	public function validate() {
		$data = $this->input->post();

		$res = $this->Maccount->validateLogin($data);
		
		echo json_encode($res);
	}


	function logout() {
		$this->session->sess_destroy();
		redirect('');
	}


}
