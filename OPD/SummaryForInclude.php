<?php
session_start();
include('../ConnectionClass.php');
include('../db_class.php');
$db = new CRUD();
  $fileno = $_GET['fileno'];
  $refno = $_GET['refno'];
  $Hospital = $db->ReadOne("SELECT * FROM tbl_hospital");
  $Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$refno'"); 
  $age = $db->getPatientAge($Patient['dob']);
  $Visit = $db->ReadOne("SELECT * FROM tbl_opd_visits WHERE fileno = '$fileno'");
  $Triage = $db->ReadOne("SELECT * FROM tbl_opd_vitals WHERE fileno = '$fileno' ORDER BY fileno DESC");
?>

<div id="print_paper" style="background-color: #fff; width: 90%; height: 90%; margin: auto; box-shadow: 8px 8px 8px 8px rgba(0,0,0,0.5); padding: 1cm;">
	<table align="center" style="width: 100%; font-family:'Courier New', Courier, monospace;">
    <tbody>
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
					<span style="text-decoration: underline;"><b>Outpatient Health File</b></span><br>
				</div>
			</div>
		</tr>
      <tr>
        <td><b>File NO.:</b>  <?= $fileno?></td>
        <td><b>Visit Date.:</b>  <?= $Visit['visit_date'] ?></td>
      </tr>
      <tr><td><p></p></td></tr>
  	<!-- Investigation Details -->
      <tr style="border-bottom: 1px solid #444;"><td><b>Patient Personal Details</b></td></tr>
      <tr>
        <td><b>OPD No.</b>  <?= $Patient['refno']?></td>
        <td><b>Patient Name.:</b> <?= $Patient['fullname']?></td>
      </tr>
      <tr>
        <td><b>Date of Birth:</b> <?= $Patient['dob']?></td>
        <td><b>Age: </b>  <?= $age?></td>
      </tr>
      <tr>   
      	<td><b>Gender</b>  <?= $Patient['sex']?></td>         
        <td><b>Marital Status</b> <?= $Patient['marital_status']?></td>
      </tr>
      <tr><td colspan="2"><p></p></td></tr>
		<tr style="border-bottom: 1px solid #444;">
			<td colspan="2"><b>Triage Record</b></td>
		</tr>
      	<tr  colspan="2">
			<td>Hypertensive: <b><?= $Triage['hypertensive'] ?></b></td>
		</tr>
		<tr  colspan="2">
			<td>Blood pressure: <b><?= $Triage['bp_systolic']."/".$Triage['bp_diastolic']?>mm/Hg</b></td>
		</tr>
		<tr  colspan="2">
			<td>Body Temperature: <b><?= $Triage['temperature']?><sup>0</sup>C</b></td>
		</tr>
		<tr  colspan="2">
			<td>Height: <b><?= $Triage['height']?>m</b></td>
		</tr>
		<tr  colspan="2">
			<td>Weight: <b><?= $Triage['mass']?>Kg</b></td>
		</tr>
		<tr  colspan="2">
			<td><b>Nurse: </b> <?= $Triage['recorded_by']?></td>
		</tr>


		<tr><td><p></p></td></tr>
		<tr style="border-bottom: 1px solid #444;">
			<td colspan="2"><b>Observation and Examination</b></td>
		</tr>
      	<tr  colspan="2">
			<table style="width: 100%;" border="1">
				<thead>
					<th>#</th>
					<th>Complaint</th>
					<th>Period</th>
					<th>Pre-Med</th>
					<th>Examination Note</th>
					<th>Dr.</th>
				</thead>
				<?php
				$sql = "SELECT * FROM tbl_healthcase WHERE fileno='$fileno'";
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
						<td><?= $row['period']?></td>
						<td><?= $row['pre_med_note'] ?></td>	
						<td><?= $row['physical_examination_note'] ?></td>
						<td><?= $row['doctor'] ?></td>
					</tr>
					<?php
				}
				?>
			</table>
		</tr>


		<tr><td><p></p></td></tr>
		<tr style="border-bottom: 1px solid #444;">
			<td colspan="2"><b>Procedures</b></td>
		</tr>
      	<tr  colspan="2">
			<table style="width: 100%;" border="1">
				<thead>
					<th>#</th>
					<th>Procedure</th>
					<th>Notes</th>
					<th>Dr.</th>
				</thead>
				<?php
				$sql = "SELECT * FROM tbl_opd_medical_procedure WHERE fileno = '$fileno'";
				$query = $db->ReadAll($sql);
				if ($db->CountRows($sql)==0) {
					echo "<tr><td colspan='4'>No Medical Procedure done</td></tr>";
				}
				$i=0;
				while ($row = mysqli_fetch_assoc($query)) {
					$i++;
				?>
				<tr>
					<td><?= $i."."?></td>
					<td><?= $row['procedure_name']?></td>
					<td><?= $row['procedure_note']?></td>
					<td><?= $row['doctor']?></td>
				</tr>
				<?php
				}
				?>
			</table>
		</tr>


		<tr><td><p></p></td></tr>
		<tr style="border-bottom: 1px solid #444;">
			<td colspan="2"><b>Investigations</b></td>
		</tr>
      	<tr  colspan="2">
			<table style="width: 100%;" border="1">
				<thead>
					<th>Result Type</th>
					<th>Investigation</th>
					<th>Specimen</th>
					<th>Result</th>
				</thead>
				<?php
				$sql1 = "SELECT * FROM tbl_laboratory_log WHERE fileno = '$fileno'";
				$sql2 = "SELECT * FROM tbl_radiology_log WHERE fileno = '$fileno'";
				if ($db->CountRows($sql1)==0 && $db->CountRows($sql2)==0) {
					echo "<tr><td colspan='4'>No Laboratory or Radiology Investigation Done</td></tr>";
				}
				$query = $db->ReadAll($sql1);
				while ($LogBook = mysqli_fetch_assoc($query)) {
				?>
					<tr onclick="window.open('labreport.php?labno=<?= $LogBook['labno']?>')" style="cursor: pointer;">
						<td><?= "Laboratory Result"?></td>
						<td><?= $LogBook['investigation']?></td>
						<td><?= $LogBook['specimen']?></td>
						<td><?= $LogBook['result']?></td>
					</tr>
				<?php
				}
				$query = $db->ReadAll($sql2);
				while ($LogBook = mysqli_fetch_assoc($query)) {
				?>
					<tr onclick="GetImages('<?= $LogBook['req_id']?>')" style="cursor: pointer;">
						<td><?= "Radiology Result"?></td>
						<td><?= $LogBook['investigation']?></td>
						<td>---</td>
						<td><?= $LogBook['comment']?></td>
					</tr>
				<?php
				}
				?>
			</table>
		</tr>

		<tr><td><p></p></td></tr>
		<tr style="border-bottom: 1px solid #444;">
			<td colspan="2"><b>Diagnosis</b></td>
		</tr>
      	<tr  colspan="2">
			<table style="width: 100%;" border="1">
				<thead>
					<th>Disease Name</th>
					<th>ICD 10</th>
					<th>Note</th>
					<th>Dr.</th>
				</thead>
				<?php
				$sql = "SELECT * FROM tbl_opd_disease_diagnosis WHERE fileno = '$fileno' ";
				$query = $db->ReadAll($sql);
				if ($db->CountRows($sql)==0) {
					echo "<tr><td colspan='4'>There is no diagnosis done</td></tr>";
				}
				while ($row = mysqli_fetch_assoc($query)) {
				?>
				<tr>
					<td><?= $row['d_name']?></td>
					<td><?= $row['d_code']?></td>
					<td><?= $row['d_comment']?></td>
					<td><?= $row['doctor']?></td>
				</tr>
				<?php
				}
				?>
			</table>
		</tr>


		<tr><td><p></p></td></tr>
		<tr style="border-bottom: 1px solid #444;">
			<td colspan="2"><b>Medication</b></td>
		</tr>
      	<tr  colspan="2">
			<table style="width: 100%;" border="1">
				<thead>
					<th>Drug Name</th>
					<th>Dosage</th>
					<th>Instructions</th>
					<th>Dr.</th>
				</thead>
				<?php
				$sql = "SELECT * FROM tbl_opd_service_request WHERE fileno = '$fileno' AND req_department ='Pharmacy' ORDER BY req_id DESC";
				$query = $db->ReadAll($sql);
				if ($db->CountRows($sql)==0) {
					echo "<tr><td colspan='4'>There is no drug precribed.</td></tr>";
				}
				while ($row = mysqli_fetch_assoc($query)) {
				?>
				<tr>
					<td><?= $row['req_name']?></td>
					<td><?= $row['req_des']?></td>
					<td><?= $row['req_comment']?></td>
					<td><?= $row['req_by']?></td>
				</tr>
				<?php
				}
				?>
			</table>
		</tr>


		<tr><td><p></p></td></tr>
		<tr style="border-bottom: 1px solid #444;">
			<td colspan="2"><b>Final Action</b></td>
		</tr>
      	<tr  colspan="2">
			<table style="width: 100%;" border="1">
				<?php
				$sql = "SELECT * FROM tbl_opd_disposition where fileno='$fileno' ";
				$res = $db->ReadAll($sql);
				if ($db->CountRows($sql)==0) {
					echo "<tr><td>Final Action/Plan not recorded</td></tr>";
				}
				while ($row=mysqli_fetch_assoc($res)) {
				 	?>
				 	<tr>
				 		<td>
				 			Date/Time: <b><?= $row['ref_date'] ?></b><br>
				 			Action/Plan: <b><?= $row['ref_type'] ?></b><br>
				 			Patient Destination: <b><?= $row['ref_to'] ?></b><br>
				 			Note/Reason: <b><?= $row['ref_reason'] ?></b><br>
				 			Doctor: <b><?= $row['doctor'] ?></b><br>
				 		</td>
				 	</tr>
				 	<?php
				 } 
				?>
			</table>
		</tr>
    </tbody>
  </table> 
	</div>