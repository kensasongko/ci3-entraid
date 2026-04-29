<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth gate for protected controllers. Any controller that should require
 * a logged-in session extends MY_Controller instead of CI_Controller.
 *
 * Login-flow controllers (Account, Auth_azure) MUST keep extending
 * CI_Controller so they remain reachable while logged out.
 */
class MY_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('user')) {
            $this->session->set_userdata('url_ref', current_url());
            redirect('login');
        }
    }
}
