<?php
include('../ConnectionClass.php');
include('../db_class.php');
session_start();

$db = new CRUD();

//Get Waiting Queue
	if (isset($_POST['GetOutPatientQueue'])) {
		$sql = "SELECT * FROM tbl_opd_service_request WHERE req_status='granted' AND req_department ='Radiology' ORDER BY req_id ASC";
        $res = mysqli_query($conn,$sql);
        while ($Request = mysqli_fetch_assoc($res)) {
        	$Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$Request[refno]'"); 
          ?>
			<tr>
				<td><?= $Request['fileno']?></td>
				<td ><?= $Patient['fullname']?></td>
				<td><?= $Request['req_id']?></td>
				<td><?= $Request['req_date']?></td>
				<td><?= $Request['req_name']?></td>
				<td>---</td>
				<td>
					<button onclick="StartInvestigation('<?= $Request['req_id']?>')" class="btn btn-sm btn-outline-primary"> Start Investigation</button>
				</td>
			</tr>
          <?php
        }
	}

//Get Waiting Queue
	if (isset($_POST['GetInPatientQueue'])) {
		$sql = "SELECT * FROM tbl_ipd_service_request WHERE (req_status='granted' AND req_department ='Radiology') ORDER BY req_id ASC";
        $res = mysqli_query($conn,$sql);
        while ($Request = mysqli_fetch_assoc($res)) {
        	$Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$Request[refno]'"); 
          ?>
			<tr>
				<td><?= $Request['fileno']?></td>
				<td ><?= $Patient['fullname']?></td>
				<td><?= $Request['req_id']?></td>
				<td><?= $Request['req_date']?></td>
				<td><?= $Request['req_name']?></td>
				<td>---</td>
				<td>
					<button onclick="StartInvestigation('<?= $Request['req_id']?>')" class="btn btn-sm btn-outline-primary"> Start Investigation</button>
				</td>
			</tr>
          <?php
        }
	}

//Start Investigation
	if (isset($_POST['StartInvestigation'])) {
		$investigation_date = date('d/m/Y H:i:s');
		$req_id = mysqli_real_escape_string($conn,$_POST['req_id']);
		$patient_from = mysqli_real_escape_string($conn,$_POST['patient_from']);
		if ($patient_from=='Out-patient') {
			$Request  =$db->ReadOne("SELECT * FROM tbl_opd_service_request WHERE req_id='$req_id'");
			$Patient = $db->ReadOne("SELECT * FROM tbl_patient WHERE refno = '$Request[refno]'");
			$name = $Patient['fullname'];
			$age = $db->getPatientAge($Patient['dob']);
			$sex = $Patient['sex'];
			if ($db->Exists("SELECT * FROM tbl_radiology_log WHERE req_id='$req_id' AND refno='$Request[refno]' ")) {
				echo "This Radiology Investigation is already started"; return;
			}
			$sql_array = array();
				$sql_array[] = "INSERT INTO tbl_radiology_log (refno,fileno,req_id,fullname,dob,age,sex,investigation,investigation_date,facility_from) values ('$Request[refno]','$Request[fileno]','$req_id','$name','$Patient[dob]','$age','$sex','$Request[req_name]','$investigation_date','$patient_from')";
				$sql_array[] =  "UPDATE tbl_opd_service_request SET req_status='delivered' WHERE req_id='$req_id'";
			echo $db->query_sql_array($sql_array);
		}else{
			$Request  =$db->ReadOne("SELECT * FROM tbl_ipd_service_request WHERE req_id='$req_id'");
			$Patient = $db->ReadOne("SELECT * FROM tbl_patient WHERE refno = '$Request[refno]'");
			$name = $Patient['fullname'];
			$age = $db->getPatientAge($Patient['dob']);
			$sex = $Patient['sex'];
			if ($db->Exists("SELECT * FROM tbl_radiology_log WHERE req_id='$req_id' AND refno='$Request[refno]' ")) {
				echo "This Radiology Investigation is already started"; return;
			}
			$sql_array = array();
				$sql_array[] = "INSERT INTO tbl_radiology_log (refno,fileno,req_id,fullname,dob,age,sex,investigation,investigation_date,facility_from) values ('$Request[refno]','$Request[fileno]','$req_id','$name','$Patient[dob]','$age','$sex','$Request[req_name]','$investigation_date','$patient_from')";
				$sql_array[] = "UPDATE tbl_ipd_service_request SET req_status='delivered' WHERE req_id='$req_id'";
			echo $db->query_sql_array($sql_array);
		}
	}

//Get Running Tasks
	if (isset($_POST['GetRunningInvestigations'])) {
		$sql = "SELECT * FROM tbl_radiology_log WHERE status='running'";
        $res = $db->ReadAll($sql);
        while ($Logbook = mysqli_fetch_assoc($res)) {
          ?>
            <tr>
                  <td><?= $Logbook['fileno']?></td>
                  <td><?= $Logbook['investigation_date']?></td>
                  <td><?= $Logbook['req_id']?></td>
                  <td><?= $Logbook['investigation']?></td>
                  <td><?= $Logbook['fullname']?></td>
                  <td>
                  	<a href="Feed Results.php?reqcode=<?= $Logbook['req_id']?>" class="btn btn-sm btn-outline-primary">Feed Result</a>
                  </td>
                </tr>
          <?php
        }
	}
//Feed Results
	if (isset($_POST['FeedResults'])) {
	    $req_id = mysqli_real_escape_string($conn,$_POST['req_id']);
	    $result_date = date('d/m/Y H:i:s');
	    $comment = mysqli_real_escape_string($conn,$_POST['investigation_note']);
	    $analysing_officer = $_SESSION['Fullname'];
		
		$sql = "UPDATE tbl_radiology_log SET result_date='$result_date',comment='$comment', analysing_officer='$analysing_officer',status='ready' WHERE req_id='$req_id'";
	    echo $db->Query($sql);
	}

	if (isset($_POST['SaveImages'])) {
		$uploadDir = "../images/radiology/";
		$req_id = mysqli_real_escape_string($conn,$_POST['req_id']);
		$total_images = mysqli_real_escape_string($conn,$_POST['total_images']);
		if ($total_images == 0) {
			echo "No Images to save";
			return;
		}else{
			if (!empty($_FILES['image_name']['name'])) {
				$fileName = $_FILES['image_name']['name'];	
				$tmpName = $_FILES['image_name']['tmp_name'];	
				$filePath = $uploadDir . $fileName; 
				$result = move_uploaded_file($tmpName, $filePath);
				echo $db->Query("INSERT INTO tbl_radiology_images(req_id,image_url) VALUES ('$req_id','$filePath')");
			}
		}
	}

//logbook
	if (isset($_POST['FilterLogBook'])) {
		$searchBy= mysqli_real_escape_string($conn,$_POST['searchBy']);
		$searchVal=mysqli_real_escape_string($conn,$_POST['searchVal']);
		switch ($searchBy) {
			case 'log_id':
				$sql = "SELECT * FROM tbl_radiology_log WHERE log_id LIKE '%$searchVal%'  ORDER BY log_id DESC LIMIT 20";
				break;
			case 'refno':
				$sql = "SELECT * FROM tbl_radiology_log WHERE refno LIKE '%$searchVal%'  ORDER BY log_id DESC LIMIT 20";
				break;	
			case 'fullname':
				$sql = "SELECT * FROM tbl_radiology_log WHERE fullname LIKE '%$searchVal%'  ORDER BY log_id DESC LIMIT 20";
				break;		
			default:
				$sql =  "SELECT * FROM tbl_radiology_log WHERE fullname LIKE '%$searchVal%' OR refno LIKE '%$searchVal%' OR log_id LIKE '%$searchVal%' ORDER BY log_id DESC LIMIT 20";
				break;
		}
		
        $res = mysqli_query($conn,$sql);
        while ($rowSet = mysqli_fetch_assoc($res)) {
        	$log_id = $rowSet['log_id'];
          ?>
            <tr>
              <td><?= $rowSet['refno']?></td>
              <td ><?= $rowSet['log_id']?></td>
              <td ><?= $rowSet['fullname']?></td>
              <td ><?= $rowSet['investigation']?></td>
                <td ><?= $rowSet['comment']?></td>
              <td><button class="btn btn-outline-primary btn-sm" onclick="var w = window.open('print log.php?log-id='+'<?= $log_id?>'); w.focus();"><i class="oi oi-print"></i> Print</button></td>
            </tr>
          <?php
        }
	}
?>