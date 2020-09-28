<?php
include('../ConnectionClass.php');
include('../db_class.php');
session_start();

$db = new CRUD();
//Get Waiting Queue
	if (isset($_POST['GetOPDQueue'])) {
		$sql = "SELECT * FROM tbl_opd_service_request WHERE req_status='granted' AND req_department ='Laboratory' ORDER BY req_id ASC";
        $res = mysqli_query($conn,$sql);
        while ($Request = mysqli_fetch_assoc($res)) {
        	$Patient = mysqli_fetch_array(mysqli_query($conn,"SELECT * From tbl_patient where refno = '$Request[refno]'"),MYSQLI_ASSOC);
        	$Sample = $db->ReadOne("SELECT * FROM tbl_item WHERE item_name='$Request[req_name]' AND item_type='Laboratory Service' ")['item_des']; 
          ?>
            <tr>
				<td><?= $Request['fileno']?></td>
				<td><?= $Request['req_id']?></td>
				<td><?= $Request['req_date']?></td>
				<td ><?= $Patient['fullname']?></td>
				<td><?= $Request['req_name']?></td>
				<td><?= $Sample ?></td>
				<td><button class="btn btn-sm btn-success" onclick="SetRequestProperties($(this).parents('tr'));">Collect Sample</button></td>
            </tr>
          <?php
        }
	}

	if (isset($_POST['GetIPDQueue'])) {
		$sql = "SELECT * FROM tbl_ipd_service_request WHERE req_status='granted' AND req_department ='Laboratory' ORDER BY req_id ASC";
        $res = mysqli_query($conn,$sql);
        while ($Request = mysqli_fetch_assoc($res)) {
        	$Patient = mysqli_fetch_array(mysqli_query($conn,"SELECT * From tbl_patient where refno = '$Request[refno]'"),MYSQLI_ASSOC);
        	$Sample = $db->ReadOne("SELECT * FROM tbl_item WHERE item_name='$Request[req_name]' AND item_type='Laboratory Service' ")['item_des']; 
          ?>
            <tr>
				<td><?= $Request['fileno']?></td>
				<td><?= $Request['req_id']?></td>
				<td><?= $Request['req_date']?></td>
				<td ><?= $Patient['fullname']?></td>
				<td><?= $Request['req_name']?></td>
				<td><?= $Sample ?></td>
				<td><button class="btn btn-sm btn-success" onclick="SetRequestProperties($(this).parents('tr'));">Collect Sample</button></td>
            </tr>
          <?php
        }
	}


//Receive Specimen
	if (isset($_POST['CollectSample'])) {		
		$today = date('d/m/Y H:i:s');
		$req_id = mysqli_real_escape_string($conn,$_POST['req_id']);
		$patient_from= mysqli_real_escape_string($conn,$_POST['patient_from']);
		$investigation= mysqli_real_escape_string($conn,$_POST['investigation']);
		$date_of_sample_collection = mysqli_real_escape_string($conn,$_POST['date_of_sample_collection']);
		$specimen= mysqli_real_escape_string($conn,$_POST['specimen']);
		$specimen_cond= mysqli_real_escape_string($conn,$_POST['specimen_cond']);
		$receiving_officer= $_SESSION['Fullname'];
		$receiving_officer_comment= mysqli_real_escape_string($conn,$_POST['receiving_officer_comment']);

		//Requestprops
		$RequestWas = $db->ReadOne("SELECT * FROM tbl_opd_service_request WHERE req_id='$req_id'");
		$Item = $db->ReadOne("SELECT * FROM tbl_item WHERE item_name='$investigation'");
		//Create code in logbook;
		if ($db->CountRows("SELECT * FROM tbl_laboratory_log WHERE req_id='$req_id' AND facility_from='$patient_from'")>0) {
			echo "This Test sample has already been collected and saved in the system";
			return;
		}else{
			CreateLog($db,$RequestWas['refno'],$RequestWas['fileno'],$req_id,$patient_from);
		}

		//Get the lab number from logbook
		$LogBook = $db->ReadOne("SELECT * FROM tbl_laboratory_log WHERE req_id='$req_id'");
		$labno = $LogBook['labno'];

		//Mark Request Code as Delivered
		if ($patient_from=='Out-patient') {
			$db->Query("UPDATE tbl_opd_service_request SET req_status='delivered' WHERE req_id='$req_id'");
		}else{
			$db->Query("UPDATE tbl_ipd_service_request SET req_status='delivered' WHERE req_id='$req_id'");
		}


		$sql = "UPDATE tbl_laboratory_log SET facility_from = '$patient_from', investigation= '$investigation', specimen= '$specimen',test_lower_range='$Item[purchase_price]',test_upper_range='$Item[selling_price]', date_of_sample_collection='$date_of_sample_collection',date_specimen_received ='$today', specimen_condition = '$specimen_cond', receiving_officer= '$receiving_officer', receiving_officer_comment= '$receiving_officer_comment',status='running' WHERE labno = '$labno' ";
		echo $db->Query($sql);
	}

	function CreateLog($db,$refno,$fileno,$req_id,$req_type){
		$Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$refno'"); 
	    $age = $db->getPatientAge($Patient['dob']);
		$sql = "INSERT INTO tbl_laboratory_log (refno,fileno,req_id,patient_name,patient_age,patient_sex,facility_from) values ('$refno','$fileno','$req_id','$Patient[fullname]','$age','$Patient[sex]','$req_type')";
		echo $db->Query($sql);
	}

//Save Investigation Results
	if (isset($_POST['GetResultsQueue'])) {
        $sql = "SELECT * FROM tbl_laboratory_log WHERE status='running' ORDER BY labno ASC";
        $res = mysqli_query($conn,$sql);
        while ($rowSet = mysqli_fetch_assoc($res)) {
          $counter_id = "timer".$rowSet['labno'];
          ?>
            <tr>
              <td><?= $rowSet['refno']?></td>
              <td ><?= $rowSet['labno']?></td>
              <td ><?= $rowSet['patient_name']?></td>
              <td ><?= $rowSet['investigation']?></td>
              <td><div class="online" style="width: 5px; height: 5px; border-radius: 50%; background-color: green; color: green; margin: 5px; float: left;"></div><b id="<?= $counter_id?>"></b>
              </td>
              <td><a href="feedResults.php?labno=<?=$rowSet['labno']?>" class="btn btn-outline-primary btn-sm"><i class="oi oi-pencil"></i> Feed Results</button></td>
            </tr>
          <?php
        }
	}
	if (isset($_POST['SaveResults'])) {
		sleep(0);
		$refno= $_POST['refno'];
		$labno= mysqli_real_escape_string($conn,$_POST['labno']);
		$analysing_officer  = mysqli_real_escape_string($conn,$_POST['analysing_officer']); 
		$analysis_date= mysqli_real_escape_string($conn,$_POST['analysis_date']);
		$result_date= mysqli_real_escape_string($conn,$_POST['result_date']);
		$turn_around_time= mysqli_real_escape_string($conn,$_POST['turn_around_time']);
		$comment= mysqli_real_escape_string($conn,$_POST['comment']);
		$result= mysqli_real_escape_string($conn,$_POST['result']);
		$sql = "UPDATE tbl_laboratory_log SET  	date_of_analysis='$analysis_date', result_date_time ='$result_date',turn_around_time='$turn_around_time',analysing_officer='$analysing_officer', analysing_officer_comment='$comment',result='$result', status='pending' WHERE labno= '$labno' ";
		echo $db->Query($sql);
	}

//Save Turn Around Time
	if (isset($_POST['SaveTurnAroundTime'])) {
		$labno= mysqli_real_escape_string($conn,$_POST['labno']);
		$mytime= mysqli_real_escape_string($conn,$_POST['mytime']);
		$sql = "UPDATE tbl_laboratory_log SET turn_around_time ='$mytime' WHERE labno= '$labno' ";
		echo $db->Query($sql);
	}

//Verify Investigation Results
	if (isset($_POST['VerifyResults'])) {
		sleep(0);
		$refno= mysqli_real_escape_string($conn,$_POST['refno']);
		$labno= mysqli_real_escape_string($conn,$_POST['labno']);
		$confirming_officer  = mysqli_real_escape_string($conn,$_POST['verifying_officer']); 
		$confirming_officer_comment = mysqli_real_escape_string($conn,$_POST['my_comment']);
		$confirmation_time = date('d/m/Y H:i:s');
		$sql = "UPDATE tbl_laboratory_log SET  	confirming_officer = '$confirming_officer', confirming_officer_comment ='$confirming_officer_comment',confirmation_time='$confirmation_time', status='verified' WHERE labno= '$labno' ";
		echo $db->Query($sql);

	}
	if (isset($_POST['CancelResults'])) {
		sleep(0);
		$refno= mysqli_real_escape_string($conn,$_POST['refno']);
		$labno= mysqli_real_escape_string($conn,$_POST['labno']);
		$confirming_officer  = mysqli_real_escape_string($conn,$_POST['verifying_officer']); 
		$confirming_officer_comment = mysqli_real_escape_string($conn,$_POST['my_comment']);
		$confirmation_time = date('d/m/Y H:i:s');
		$sql = "UPDATE tbl_laboratory_log SET  	confirming_officer = '$confirming_officer', confirming_officer_comment ='$confirming_officer_comment',confirmation_time='$confirmation_time', status='Results Cancelled' WHERE labno= '$labno' ";
		echo $db->Query($sql);

	}
//jsDate to phpDate
	if (isset($_POST['jsDAteToPhpDate'])) {
		$jsdate = $_POST['jsdate'];
        $phpDate = new DateTime($jsdate);
        if ($phpDate!==false) {
         echo $phpDate->format('d/m/Y');
        }else{
        	echo "Stupid";
        }
	}

//Laboratory Log
	if (isset($_POST['FilterLogBook'])) {
		$searchBy= mysqli_real_escape_string($conn,$_POST['searchBy']);
		$searchVal=mysqli_real_escape_string($conn,$_POST['searchVal']);
		$searchCol = null;
		switch ($searchBy) {
			case 'labno':
				$searchCol = 'labno';
				break;
			case 'refno':
				$searchCol = 'refno';
				break;	
			case 'fullname':
				$searchCol = 'patient_name';
				break;		
			default:
				break;
		}
		$sql = "SELECT * FROM tbl_laboratory_log WHERE $searchCol LIKE '%$searchVal%'  ORDER BY labno DESC LIMIT 20";
        $res = mysqli_query($conn,$sql);
        while ($rowSet = mysqli_fetch_assoc($res)) {
        	$labno = $rowSet['labno'];
          ?>
            <tr>
              <td><?= $rowSet['refno']?></td>
              <td ><?= $rowSet['labno']?></td>
              <td ><?= $rowSet['patient_name']?></td>
              <td ><?= $rowSet['investigation']?></td>
              <td><?= $rowSet['status']?></td>
              <td><button class="btn btn-outline-primary btn-sm" onclick="var w = window.open('print log.php?labno='+'<?= $labno?>'); w.focus();"><i class="oi oi-print"></i> Print</button></td>
            </tr>
          <?php
        }
	}
	
//REMOVE FROM QUEUE
	if (isset($_POST['RemoveFromQueue'])) {
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		if (mysqli_query($conn,"UPDATE tbl_queue SET q_status='served' WHERE refno='$refno' ")) {
			echo "Removed from queue";
		}else{
			echo "Sorry: ".mysqli_error($conn);
		}
	}
?>