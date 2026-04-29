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

	/**
	 * Look up a user row by Entra object id (oid). Stable key across renames.
	 */
	function findByAzureOid($oid) {
		if (empty($oid)) {
			return NULL;
		}
		$row = $this->db->get_where('user', ['azure_oid' => $oid], 1);
		if ($row->num_rows() === 0) {
			return NULL;
		}
		$arr = $row->row_array();
		unset($arr['password']);
		return $arr;
	}

	/**
	 * Insert a `user` row from validated Entra ID claims and return it.
	 * Uniqueness is enforced by the uq_user_azure_oid index.
	 */
	function provisionFromAzure(array $claims) {
		$username = $claims['preferred_username'] ?? $claims['upn'] ?? $claims['email'] ?? $claims['oid'];
		$email    = $claims['email'] ?? $claims['upn'] ?? NULL;
		$name     = $claims['name'] ?? NULL;

		$insert = [
			'username'      => $username,
			'email'         => $email,
			'display_name'  => $name,
			'azure_oid'     => $claims['oid'],
			'auth_source'   => 'azure',
			'last_login_at' => date('Y-m-d H:i:s'),
		];
		$this->db->insert('user', $insert);

		return $this->findByAzureOid($claims['oid']);
	}

	/**
	 * Update last_login_at to NOW() for the given user id.
	 */
	function touchLastLogin($userId) {
		if (empty($userId)) {
			return;
		}
		$this->db->where('id', $userId);
		$this->db->update('user', ['last_login_at' => date('Y-m-d H:i:s')]);
	}

}

?>
