<?php
session_start();
include('../ConnectionClass.php');
include('../db_class.php');

$db = new CRUD();

//Patient Admission
if (isset($_POST['GetServiceProps'])) {
	    $item_name = mysqli_real_escape_string($conn,$_POST['item_name']);
	    $Item = $db->ReadOne("SELECT * FROM tbl_item WHERE item_name ='$item_name'");
	     echo $Item['item_rate_cash'];
	  }

	if (isset($_POST['GetPatientInfo'])) {
	    sleep(0);
	    $refno = mysqli_real_escape_string($conn,$_POST['refno']);
	    $Patient = $db->ReadOne("SELECT * FROM tbl_patient WHERE refno='$refno'");
	    $count = $db->CountRows("SELECT * FROM tbl_patient WHERE refno='$refno'");
	    if ($count>0) {
	    	$age = $db->getPatientAge($Patient['dob']);
		    ?>
			<div class="form-row col-12">
				<div class="form-group col-sm-12 col-md-6">
					<label class="text-primary">Full name</label>
					<input class="form-control form-control-sm" value="<?= $Patient['fullname'] ?>" readonly>
				</div>
				<div class="form-group col-sm-12 col-md-6">
					<label class="text-primary">Registration Number</label>
					<input id="refno" class="form-control form-control-sm" value="<?= $Patient['refno'] ?>" readonly>
				</div>
			</div>
			<div class="form-row col-12">
				<div class="form-group col-sm-12 col-md-4">
					<label class="text-primary">DOB</label>
					<input class="form-control form-control-sm" value="<?= $Patient['dob'] ?>" readonly>
				</div>
				<div class="form-group col-sm-12 col-md-4">
					<label class="text-primary">Age</label>
					<input class="form-control form-control-sm" value="<?= $age ?>" readonly>
				</div>
				<div class="form-group col-sm-12 col-md-4">
					<label class="text-primary">Gender/Sex</label>
					<input id="sex" class="form-control form-control-sm" value="<?= $Patient['sex'] ?>" readonly>
				</div>
			</div>
			<div class="form-row col-12">
				<div class="form-group col-sm-12 col-md-6">
					<label class="text-danger">Kin Name</label>
					<input class="form-control form-control-sm" value="<?= $Patient['kin_name'] ?>" readonly>
				</div>
				<div class="form-group col-sm-12 col-md-3">
					<label class="text-danger">Kin Phone</label>
					<input class="form-control form-control-sm" value="<?= $Patient['kin_phone'] ?>" readonly>
				</div>
				<div class="form-group col-sm-12 col-md-3">
					<label class="text-danger">Kin Relationship</label>
					<input class="form-control form-control-sm" value="<?= $Patient['kin_relationship'] ?>" readonly>
				</div>
			</div>
	    <?php
	  }
	    }



	if (isset($_POST['AdmitPatient'])) {
		$adm_date = date('d/m/Y H:i:s');
		$refno = mysqli_real_escape_string($conn,$_POST['refno']); 
		$mch_category  =mysqli_real_escape_string($conn,$_POST['mch_category']);
		$admitted_by = mysqli_real_escape_string($conn,$_POST['admitted_by']);
		
		$Active = $db->CountRows("SELECT * FROM tbl_mch_admission WHERE refno = '$refno'");
		if ($Active > 0) {echo "You cannot register a patient twice in Maternity Clinic";return;}
		echo $db->Query("INSERT INTO tbl_mch_admission (refno, adm_date, mch_category, admitted_by)VALUES ('$refno', '$adm_date','$mch_category','$admitted_by')");	
	}

//SEARCH PATIENTS
	if (isset($_POST['SearchPatient'])) {
		$mch_category = mysqli_real_escape_string($conn,$_POST['mch_category']);
		$sql=null;
		if (isset($_POST['searchby'])) {
			$searchby = mysqli_real_escape_string($conn,$_POST['searchby']);
			$searchVal = mysqli_real_escape_string($conn,$_POST['searchVal']);
			switch ($searchby) {
				case 'fullname':
					$sql = "SELECT * FROM tbl_patient Where fullname LIKE '%$searchVal%'  AND refno IN (SELECT refno FROM tbl_mch_admission WHERE mch_category='$mch_category') LIMIT 20 ";
					break;
				case 'refno':
					$sql = "SELECT * FROM tbl_patient Where refno LIKE '%$searchVal%'  AND refno IN (SELECT refno FROM tbl_mch_admission WHERE mch_category='$mch_category') LIMIT 20 ";
					break;
				case 'idno':
					$sql = "SELECT * FROM tbl_patient Where idno LIKE '%$searchVal%'  AND refno IN (SELECT refno FROM tbl_mch_admission WHERE mch_category='$mch_category') LIMIT 20 ";
					break;
				case 'ins_card_no':
					$sql = "SELECT * FROM tbl_patient Where ins_card_no LIKE '%$searchVal%'  AND refno IN (SELECT refno FROM tbl_mch_admission WHERE mch_category='$mch_category') LIMIT 20 ";
					break;
				case '':
					$sql = "SELECT * FROM tbl_patient Where (fullname LIKE '%$searchVal%' OR refno LIKE '%$searchVal%' OR ins_card_no LIKE '%$searchVal%' )  AND refno IN (SELECT refno FROM tbl_mch_admission WHERE mch_category='$mch_category') LIMIT 20 ";
					break;
			}
		}else{
			$sql = "SELECT * FROM tbl_patient WHERE refno IN (SELECT refno FROM tbl_mch_admission WHERE mch_category='$mch_category') LIMIT 20 ";
		}
		$patients = $db->ReadAll($sql);
		while ($Patient = mysqli_fetch_assoc($patients)) {
			$MCH = $db->ReadOne("SELECT * FROM tbl_mch_admission WHERE refno='$Patient[refno]'");
		?>
		<tr>
			<td><?= $Patient['refno']?></td>
			<td><?= $Patient['fullname']?></td>
			<td><?= $Patient['idno']?></td>
			<td>
				<button onclick="$('input').val(''); $('#refno').val('<?= $Patient['refno']?>'); $('#BookAppointmentPopUp').modal('show');" class="btn btn-outline-success btn-sm"><i class="oi oi-calendar"></i> Book Appointment</button>
			</td>
		</tr>
	<?php						
		}
	}
//Book Appointment
	if (isset($_POST['BookAppointment'])){
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$appointment_date = mysqli_real_escape_string($conn,$_POST['appointment_date']);
		echo $db->Query("INSERT INTO tbl_mch_appointment (refno, appointment_date) VALUES ('$refno', '$appointment_date')");	
	}

//Book Appointment
	if (isset($_POST['GetAppointment'])){
		$mch_category = mysqli_real_escape_string($conn,$_POST['mch_category']);
		$today = date('Y-m-d');
		$sql = $db->ReadAll("SELECT * FROM tbl_mch_appointment WHERE appointment_date='$today' AND refno IN (SELECT refno FROM tbl_mch_admission WHERE mch_category='$mch_category')");
		while ($Appointment = mysqli_fetch_assoc($sql)) {
			$refno = $Appointment['refno'];
			$Client = $db->ReadOne("SELECT * FROM tbl_mch_admission WHERE refno = '$refno'");	
			$Patient = $db->ReadOne("SELECT * FROM tbl_patient WHERE refno = '$refno'");	
		?>
			<tr onclick="window.location.href='ANC Patient Serve.php?refno=<?= $Patient['refno']?>&appointment_id=<?= $Appointment['appointment_id']?>'">
				<td><?= $Appointment['appointment_id'] ?></td>
				<td><?= $Patient['refno'] ?></td>
				<td><?= $Patient['fullname'] ?></td>
			</tr>
		<?php
		}		
	}
//Save appointment data
	if (isset($_POST['SaveANCVisit'])) {
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$appointment_id = mysqli_real_escape_string($conn,$_POST['appointment_id']);
		$hiv_test = mysqli_real_escape_string($conn,$_POST['hiv_test']);
		$sti_test = mysqli_real_escape_string($conn,$_POST['sti_test']);
		$sti_specification = mysqli_real_escape_string($conn,$_POST['sti_specification']);
		$baby_movement = mysqli_real_escape_string($conn,$_POST['baby_movement']);
		$fundal_height = mysqli_real_escape_string($conn,$_POST['fundal_height']);
		$no_of_babies = mysqli_real_escape_string($conn,$_POST['no_of_babies']);
		$appointment_date = mysqli_real_escape_string($conn,$_POST['appointment_date']);

		$sql = "UPDATE tbl_mch_appointment SET hiv_test='$',hiv_test='$',hiv_test='$',hiv_test='$',hiv_test='$',hiv_test='$' WHERE appointment_id='$appointment_id'";
		$db->Query($sql);
		$bookedOnThisDay = $db->CountRows("SELECT * FROM tbl_mch_appointment WHERE appointment_date='$appointment_date'");
		if ($bookedOnThisDay==0) {
			echo $db->Query("INSERT INTO tbl_mch_appointment (refno, appointment_date) VALUES ('$refno', '$appointment_date')");
		}
	}
?>