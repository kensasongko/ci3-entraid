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
		$user = $this->session->userdata('user');
		$isAzure = is_array($user) && isset($user['auth_source']) && $user['auth_source'] === 'azure';

		$logoutUrl = NULL;
		if ($isAzure) {
			try {
				$this->load->library('azure_auth');
				$logoutUrl = $this->azure_auth->build_logout_url();
			} catch (Exception $e) {
				log_message('error', 'Azure federated logout URL build failed: ' . $e->getMessage());
			}
		}

		$this->session->sess_destroy();
		redirect($logoutUrl ?: '');
	}


}
