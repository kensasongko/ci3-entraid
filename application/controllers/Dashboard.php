<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

	function __construct() {
		parent::__construct();
	}

	public function index() {
		$data["title"] = "Dashboard";
		$data["content"] = "v_dashboard";
		$data["active"] = "Dashboard";

		$this->load->view('v_main', $data);
	}

}
