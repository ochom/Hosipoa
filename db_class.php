<?php
/**
 * 
 */
class CRUD
{
	var $connection= null;
	var $pdo_conn = null;
	var $result = null;
	var $enc_dec_key = '89897575';
	function __construct(){
		$this->connection = new mysqli('127.0.0.1', 'richard', 'ochozyritchie', 'sjmh');

		$servername = "localhost";
		$username = "root";
		$password = "";
		$dbname = "sjmh";
		$this->pdo_conn = new PDO("mysql:host=$servername;dbname=$dbname",$username,$password);

		$this->pdo_conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	}

	function Query($sql){
		try {
			if (mysqli_query($this->connection,$sql)) {
				return "success";
			}else{
				return mysqli_error($this->connection);
			}
		} catch (Exception $e) {
			return "Caught Exception: ".$e->message();
		}
	}
	function query_sql_array($sql_array){
		try {
			$this->pdo_conn->beginTransaction();
			foreach ($sql_array as $sql) {
				$this->pdo_conn->exec($sql);
			}
			$this->pdo_conn->commit();
			return "success";
		} catch (PDOException $e) {
			$this->pdo_conn->rollback();
			return "Caught Exception: ".$e->getMessage();
		}
	}

	function ReadAll($sql){
		try {
			$result = mysqli_query($this->connection,$sql);
			return $result;
		} catch (Exception $e) {
			return "Caught Exception: ".$e->Message();
		}
	}

	function ReadArray($sql){
		$result = array();
		try {
			$res = mysqli_query($this->connection,$sql);
			while ($row = mysqli_fetch_assoc($res)) {
				$result[] = $row;
			}
			return $result;
		} catch (Exception $e) {
			return "Caught Exception: ".$e->Message();
		}
	}

	function ReadOne($sql){
		try {
			$result = mysqli_fetch_array(mysqli_query($this->connection,$sql),MYSQLI_ASSOC);
			return $result;
		} catch (Exception $e) {
			return "Caught Exception: ".$e->message();
		}
	}

	function Exists($sql){
		try {
			return $this->CountRows($sql)>0;
		} catch (Exception $e) {
			return "Caught Exception: ".$e->message();
		}
	}

	function CountRows($sql){
		try {
			$result = mysqli_num_rows(mysqli_query($this->connection,$sql));
			return $result;
		} catch (Exception $e) {
			return "Caught Exception: ".$e->message();
		}
	}

	function Encrypt($string){
		return mysqli_real_escape_string($this->connection,openssl_encrypt($string, "AES-128-ECB", $this->enc_dec_key));
	}
	function Decrypt($string){
		return mysqli_real_escape_string($this->connection,openssl_decrypt($string, "AES-128-ECB", $this->enc_dec_key));
	}

	function getPatientAge($dob){
		$dob = new DateTime(date($dob));
	    $today = new DateTime(date('Y/m/d'));
	    $agediff = $today->diff($dob);
	    $age = sprintf('%d Years',$agediff->y);
	    return $age;
	}
}
