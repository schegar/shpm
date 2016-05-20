<?php 

class Model_User extends RedBean_SimpleModel {

		public function encryptPassword() {
			$this->password = password_hash($this->password, PASSWORD_BCRYPT, ['cost'=>12]);
		}

		public function verify($passwordInput) {
			return password_verify($passwordInput, $this->password);
		}
		
		public function generateHashSalt() {
			$this->hashsalt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
		}
		 
	}
 

 