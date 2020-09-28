<?php
session_start();
include('../ConnectionClass.php');
include('../db_class.php');
$db = new CRUD();

$fileno = $_GET['fileno'];
$refno = $_GET['refno'];
	$Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$refno'"); 
    $age = $db->getPatientAge($Patient['dob']);
	$Hospital = $db->ReadOne("SELECT * FROM tbl_hospital");
	$IPD_File = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE adm_no = '$fileno'");
	$bed = $db->ReadOne("SELECT * From tbl_ipd_beds where bed_status = '$refno'"); 
	$ward = $db->ReadOne("SELECT * From tbl_ipd_wards where ward_id = '$bed[ward_id]'"); 
?>

<div id="print_paper" style="background-color: #fff; width: 90%; height: 90%; margin: auto; box-shadow: 8px 8px 8px 8px rgba(0,0,0,0.5); padding: 1cm;">
	<table align="center" style="width: 100%;">
		<tr>
			<div style="width: 100% height:auto;">
				<div style="float: left; width: 100px; height: 100px;">
					<img src="../Images/logo.png" alt="LOGO" style="width: 100px; height: 100%; border: 1px solid #ddd;">
				</div>
				<div style="float: left; width: calc(100% - 100px); height: auto; padding-left: 10px;">
					<span><b style="font-size: 20px;"><?= $Hospital['hospital_name'].", MFL-".$Hospital['mfl_code']?></b></span><br>
					<span style="font-size: 15px;"><?= $Hospital['postal_address']?></span><br>
					<span style="font-size: 15px;"><?= $Hospital['email']?></span><br>
					<span style="font-size: 15px;"><?= $Hospital['phone']?></span><br>
				</div>
			</div>
		</tr>
	</table>

	<p style="text-decoration: underline; font-size: 25px; text-align: center;"><b>Discharge Summary/Referral Form</b></p>

  	<!-- Investigation Details -->
    <p style="border-bottom: 1px solid #444; margin: 10px 0px 0px 2px;"><b>Patient Personal Details</b></p>
    <table align="center" style="width: 100%;">
		<tr>
			<td><b>OPD No.</b>  <?= $Patient['refno']?></td>
			<td><b>IPD No.:</b>  <?= $fileno?></td>
			<td><b>Patient Name.:</b> <?= $Patient['fullname']?></td>
		</tr>
		<tr>
			<td><b>Date of Birth:</b> <?= $Patient['dob']?></td>
			<td><b>Age: </b>  <?= $age?></td>
			<td><b>Gender</b>  <?= $Patient['sex']?></td> 
		</tr>
		<tr>
			<td><b>Admission Date.:</b>  <?= $IPD_File['adm_date'] ?></td>
			<td><b>Ward:</b>  <?= $ward['ward_name']?></td>
			<td><b>Bed:</b>  <?= $bed['bed_number'] ?></td>
		</tr>
		<tr>
			<td><b>Date of Discharge:</b>  <?= $IPD_File['discharge_date'] ?></td>
		</tr>
	</table>

	<p style="border-bottom: 1px solid #444; margin: 10px 0px 0px 2px;"><td><b>Provisional Diagnosis (on admission)</b></p>
	<table style="width: 100%;" border="1">
		<thead>
			<th>#</th>
			<th>Disease</th>
			<th>ICD 10</th>
		</thead>
		<?php
		$sql = "SELECT * FROM tbl_ipd_provisional_diagnosis WHERE ipd_fileno='$fileno'";
		$res = $db->ReadAll($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='3'>There is no provisional diagnosis recorded.</td></tr>";
		}
		$i=0;
		while ($row = mysqli_fetch_assoc($res)) {
			$i++;
			?>
			<tr>
				<td><?= $i."."?></td>
				<td><?= $row['diagnosis'] ?></td>
				<td><?= $row['icd10'] ?></td>	
			</tr>
			<?php
		}
		?>
	</table>


	<p style="border-bottom: 1px solid #444; margin: 10px 0px 0px 2px;"><td><b>Presenting Complaints and Examination</b></p>
	<table style="width: 100%;" border="1">
		<thead>
			<th>#</th>
			<th>Complaint</th>
			<th>Examination Note</th>
			<th>Nursing/Dr. Note</th>
		</thead>
		<?php
		$sql = "SELECT * FROM tbl_ipd_observations WHERE ipd_fileno='$fileno'";
		$res = $db->ReadAll($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='8'>There is no complaint recorded.</td></tr>";
		}
		$i=0;
		while ($row = mysqli_fetch_assoc($res)) {
			$i++;
			?>
			<tr>
				<td><?= $i."."?></td>
				<td><?= $row['complaint'] ?></td>
				<td><?= $row['observation'] ?></td>	
				<td><?= $row['nursing_note'] ?></td>
			</tr>
			<?php
		}
		?>
	</table>

	<p style="border-bottom: 1px solid #444; margin: 10px 0px 0px 2px;"><td><b>Discharge Diagnosis</b></p>
	<table style="width: 100%;" border="1">
		<thead>
			<th>#</th>
			<th>Disease Name</th>
			<th>ICD 10</th>
			<th>Note</th>
		</thead>
		<?php
		$sql = "SELECT * FROM tbl_ipd_disease_diagnosis WHERE fileno = '$fileno'";
		$res = $db->ReadAll($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='4'>There is no diagnosis done</td></tr>";
		}
		$i=0;
		while ($row = mysqli_fetch_assoc($res)) {
			$i++;
			?>
		<tr>
			<td><?= $i."."?></td>
			<td><?= $row['d_name']?></td>
			<td><?= $row['d_code']?></td>
			<td><?= $row['d_comment']?></td>
		</tr>
		<?php
		}
		?>
	</table>

	<p style="border-bottom: 1px solid #444; margin: 10px 0px 0px 2px;"><td><b>Labaratory Investigations Done</b></p>
	<table style="width: 100%;" border="1">
		<thead>
			<th>#</th>
			<th>Investigation</th>
			<th>Specimen</th>
			<th>Result</th>
		</thead>
		<?php
		$sql1 = "SELECT * FROM tbl_laboratory_log WHERE fileno = '$fileno'  AND facility_from='In-patient'";
		if ($db->CountRows($sql1)==0) {
			echo "<tr><td colspan='4'>No Laboratory Investigation Done</td></tr>";
		}
		$res = $db->ReadAll($sql1);
		$i=0;
		while ($row = mysqli_fetch_assoc($res)) {
			$i++;
			?>
			<tr>
				<td><?= $i."."?></td>
				<td><?= $row['investigation']?></td>
				<td><?= $row['specimen']?></td>
				<td><?= $row['result']?></td>
			</tr>
		<?php
		}
		?>
	</table>

	<p style="border-bottom: 1px solid #444; margin: 10px 0px 0px 2px;"><td><b>Radiology Investigations Done</b></p>
	<table style="width: 100%;" border="1">
		<thead>
			<th>#</th>
			<th>Investigation</th>
			<th>Body Area</th>
			<th>Result</th>
		</thead>
		<?php
		$sql2 = "SELECT * FROM tbl_radiology_log WHERE fileno = '$fileno' AND facility_from='In-patient'";
		if ($db->CountRows($sql2)==0) {
			echo "<tr><td colspan='4'>No Radiology Investigation Done</td></tr>";
		}
		$res = $db->ReadAll($sql2);
		$i=0;
		while ($row = mysqli_fetch_assoc($res)) {
			$i++;
			?>
			<tr>
				<td><?= $i."."?></td>
				<td><?= $row['investigation']?></td>
				<td>---</td>
				<td><?= $row['comment']?></td>
			</tr>
		<?php
		}
		?>
	</table>

	<p style="border-bottom: 1px solid #444; margin: 10px 0px 0px 2px;"><td><b>Discharge care plan and instruction</b></p>
	<b>Care/Plan: </b><?= $IPD_File['discharge_type'] ?><br>
	<b>Patient Destination: </b><?= $IPD_File['discharge_destination'] ?><br>
	<b>Reason: </b><?= $IPD_File['discharge_reason'] ?><br>
	<b>Instructions: </b><?= $IPD_File['discharge_instructions'] ?><br>

	<br><br><br><br>
	<p><b>Name of Discharge Doctor/Clinician:</b> <span style="border-bottom: 2px dotted #000;"><?= $IPD_File['discharged_by'] ?></span></b></p>
	<p><b>Date:</b> <span style="border-bottom: 2px dotted #000;"><?= $IPD_File['discharge_date'] ?></span></b></p>
	<p><b>Signature: .................................</b></p>
	</div>