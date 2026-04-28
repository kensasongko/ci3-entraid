<?php

class Maccount extends CI_Model {

	function validateLogin($data) {
		// $row = $this->db->get_where('user', array("lower(username)"=>strtolower($data["username"]),"active"=>1));
		$this->db->select("t1.* ");
		$this->db->where("lower(username)", strtolower($data["username"]));
		$row = $this->db->get("user t1");

		if ($row->num_rows()==0){
			$res["status"] = false;
			$res["responseCode"] = "401";
			$res["responseMsg"]  = "Invlid username or password.";
		}else{
			$user = $row->row();

			if ($user->password==md5($data["password"])){
				$xuser = $row->result_array();
				unset($xuser['password']);
				$this->session->set_userdata("user",$xuser[0]);

				

				$res["status"] = true;
				if ($this->session->userdata("url_ref")) {
					$res["redirect"] = $this->session->userdata("url_ref");	
				} else {
					$res["redirect"] = base_url('Dashboard');
				}
			}else{
				$res["status"] = false;
				$res["responseCode"] = "401";
				$res["responseMsg"]  = "Invlid username or password.";
			}
		}
		return $res;
	}

}

?>
