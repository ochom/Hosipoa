<?php
session_start();
include('../ConnectionClass.php');
include('../db_class.php');
$db = new CRUD();

//Triage Queue
	if (isset($_POST['GetTriageQueue'])) {
		$res =$db->ReadAll("SELECT * FROM tbl_triage_queue ORDER BY q_id ASC");
        while ($Triage = mysqli_fetch_assoc($res)) {
        	$Patient = $db->ReadOne("SELECT * From tbl_patient WHERE refno='$Triage[refno]' ");
  			$age = $db->getPatientAge($Patient['dob']);
		    $fileno = $Triage['fileno'];
          ?>
            <tr onclick="window.location.href='Triage.php?fileno=<?= $fileno?>'">
              <td><?= $Triage['q_date']?></td>
              <td><?= $Patient['fullname']?></td>
              <td><?= $age?></td>
              <td id="<?= $Triage['q_id']?>"><!-- JS --></td>
              <td><?= "Records"?></td>
              <td><?= "---"?></td>
            </tr>
          <?php
        }
	}

	if (isset($_POST['SaveVitals'])) {
		sleep(0);
		$fileno= mysqli_real_escape_string($conn,$_POST['fileno']);
		$vitals_date = date('m/d/Y'); 
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$bp_systolic= mysqli_real_escape_string($conn,$_POST['bp_systolic']);
		$bp_diastolic= mysqli_real_escape_string($conn,$_POST['bp_diastolic']);	
		$hypertension_index= mysqli_real_escape_string($conn,$_POST['hypertension_index']);	
		$hypertensive= mysqli_real_escape_string($conn,$_POST['hypertensive']);
		$pulse= mysqli_real_escape_string($conn,$_POST['pulse']);
		$mass= mysqli_real_escape_string($conn,$_POST['mass']);
		$height= mysqli_real_escape_string($conn,$_POST['height']);		
		$bmi= mysqli_real_escape_string($conn,$_POST['bmi']);
		$bmi_index= mysqli_real_escape_string($conn,$_POST['bmi_index']);
		$temperature= mysqli_real_escape_string($conn,$_POST['temperature']);
		$triage_note= mysqli_real_escape_string($conn,$_POST['triage_note']);

		$sql_array = array();
		$sql_array[] = "DELETE FROM tbl_triage_queue WHERE refno='$refno'";
		$sql_array[] = "INSERT INTO tbl_opd_vitals (fileno,vitals_date,refno, hypertension_index, hypertensive, bp_systolic, bp_diastolic,pulse,mass,height,bmi,bmi_index, temperature,triage_note,recorded_by) VALUES ('$fileno','$vitals_date','$refno', '$hypertension_index', '$hypertensive', '$bp_systolic', '$bp_diastolic','$pulse','$mass','$height','$bmi','$bmi_index','$temperature','$triage_note','$_SESSION[Fullname]')";

		echo $db->query_sql_array($sql_array);
	}

//CONSULTATION QUEUE
	if (isset($_POST['GetConsultationQueue'])) {
		$rows = $db->ReadArray("SELECT * FROM tbl_opd_visits WHERE file_status='Opened' AND patient_id IN (SELECT refno FROM tbl_opd_service_request WHERE req_department='OPD' AND req_status='granted') ORDER BY fileno ASC ");
        foreach($rows as $row):
        	$refno = $row['patient_id'];
        	$Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$refno'"); 
    		$age = $db->getPatientAge($Patient['dob']);
			$fileno = $row['fileno'];
			$Request = $db->ReadOne("SELECT * FROM tbl_opd_service_request WHERE req_department='OPD' AND req_status='granted' AND fileno='$fileno' LIMIT 1 ");
          ?>
            <tr>
              <td><?= $row['fileno']?></td>
              <td ><?= $Request['req_date']?></td>
              <td ><?= $Patient['fullname']?></td>
              <td ><?= $age?></td>
              <td id="<?= $Request['req_id']?>" ></td>
              <td ><?= $Request['req_from']?></td>
              <td ><?= "---"?></td>
              <td>
              	<a  href="Patient Page.php?fileno=<?= $fileno?>" class="btn btn-outline-primary btn-sm"><i class="oi oi-circle-check"></i> Open File</a>
              </td>
            </tr>
          <?php
        endforeach;
	}


	if (isset($_POST['GetReadyResults'])) {
		$ReadyLab = $db->CountRows("SELECT * FROM tbl_laboratory_log WHERE status='verified' AND fileno IN (SELECT fileno FROM tbl_opd_visits WHERE file_status='Opened')");
		$ReadyRadiology = $db->CountRows("SELECT * FROM tbl_radiology_log WHERE status='ready' AND fileno IN (SELECT fileno FROM tbl_opd_visits WHERE file_status='Opened')");
		if ($ReadyLab===0 AND $ReadyRadiology===0) {
			echo "Currently, there is no ready investigation result..!";
			return;
		}
		$rows = $db->ReadArray("SELECT * FROM tbl_laboratory_log WHERE status='verified' AND fileno IN (SELECT fileno FROM tbl_opd_visits WHERE file_status='Opened')");
		foreach($rows as $row):
			echo "<b>File No. ".$row['fileno']."</b> Lab result. "."<a class='btn btn-sm btn-outline-success' href='Patient Page.php?fileno=$row[fileno]'><i class='oi oi-open'> Open</i></a>";
			echo "<hr style='margin:5px;'>";
		endforeach;

		$rows = $db->ReadArray("SELECT * FROM tbl_radiology_log WHERE status='ready' AND fileno IN (SELECT fileno FROM tbl_opd_visits WHERE file_status='Opened')");
		foreach($rows as $row):
			echo "<b>File No. ".$row['fileno']."</b> Radiology result. "."<a class='btn btn-sm btn-outline-success' href='Patient Page.php?fileno=$row[fileno]'><i class='oi oi-open'> Open</i></a>";
			echo "<hr style='margin:5px;'>";
		endforeach;
	}

//Patient Home
	if (isset($_POST['GetVitals'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$row = $db->ReadOne("SELECT * FROM tbl_opd_vitals WHERE fileno = '$fileno' ORDER BY fileno DESC");
		?>
		<tr>
			<td>Blood pressure: <b><?= $row['bp_systolic']."/".$row['bp_diastolic']?> mm/Hg</b></td>
			<td>BP Meter: <b><?= $row['hypertension_index'] ?></b></td>
			<td>Hypertensive: <b><?= $row['hypertensive'] ?></b></td>
			<td>Pulse rate: <b><?= $row['pulse']?> bpm</b></td>
		</tr>
		<tr>
			<td>Temperature: <b><?= $row['temperature']?> <sup>0</sup>C</b></td>
			<td>Height: <b><?= $row['height']?> m</b></td>
			<td>Weight: <b><?= $row['mass']?> Kg</b></td>
			<td>BMI: <b><?= $row['bmi']?> - <?= $row['bmi_index']?></b></td>
		</tr>
		<tr>
			<td colspan="4">Triage Note: <b><?= $row['triage_note']?></b></td>
		</tr>

		<?php
	}

	//MONITORS
	if (isset($_POST['GetGraphData'])) {
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$legends = array();
		$sys = array();
		$dia = array();
		$temp = array();
		$weight = array();
		$height = array();
		$bmi = array();
		$pulse = array();

		$rows = $db->ReadArray("SELECT * FROM tbl_opd_vitals WHERE refno = '$refno' ORDER BY fileno ASC");
		foreach($rows as $row):
			array_push($legends, $row['vitals_date']);
			array_push($sys, $row['bp_systolic']);
			array_push($dia, $row['bp_diastolic']);
			array_push($temp, $row['temperature']);
			array_push($weight, $row['mass']);
			array_push($height, $row['height']*100);
			array_push($bmi, $row['bmi']);
			array_push($pulse, $row['pulse']);
		endforeach;
		$dataset = array(
			'legends'=>$legends,
			'Systolic'=>$sys,
			'Diastolic'=>$dia,
			'Temp'=>$temp,
			'Weight'=>$weight,
			'Height'=>$height,
			'BMI'=>$bmi,
			'Pulse'=>$pulse
		);		
		echo json_encode($dataset);
	}

	if (isset($_POST['GetHealthCases'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$sql = "SELECT * FROM tbl_healthcase WHERE fileno='$fileno'";
		$rows = $db->ReadArray($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='8'>There is no health case yet.</td></tr>";
			return;
		}
		$i=0;
		foreach($rows as $row):
			$i++;
			?>
			<tr>
				<td><?= $i."."?></td>
				<td><?= $row['case_date'] ?></td>
				<td><?= $row['complaint'] ?></td>
				<td><?= $row['period']?></td>	
				<td><?= $row['pre_med_note'] ?></td>	
				<td><?= $row['physical_examination_note'] ?></td>
				<td><?= $row['doctor'] ?></td>
			</tr>
		<?php
		endforeach;
	}

	if (isset($_POST['GetProcedures'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$sql = "SELECT * FROM tbl_opd_medical_procedure WHERE fileno = '$fileno'";
		$rows = $db->ReadArray($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='5'>No Medical Procedure added</td></tr>"; return;
		}
		$i=0;
		foreach($rows as $row):
			$i++;
		?>
			<tr>
				<td><?= $i?></td>
				<td><?= $row['procedure_date']?></td>
				<td><?= $row['procedure_name']?></td>
				<td><?= $row['procedure_note']?></td>
				<td><?= $row['doctor']?></td>
			</tr>
		<?php
		endforeach;
	}

	if (isset($_POST['GetInvestigationRequests'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$rows = $db->ReadArray("SELECT * FROM tbl_opd_service_request WHERE fileno = '$fileno' AND (req_department='Laboratory' OR req_department='Radiology') AND req_status != 'delivered'");
		foreach($rows as $Request):?>
			<tr>
				<td><?= $Request['req_date']?></td>
				<td><?= $Request['req_name']?></td>
				<td><?= $Request['req_department']?></td>
				<td><?= $Request['req_cost']?></td>
				<td><?php if($Request['req_status']=='not granted'){echo "NOT PAID";}else{echo "NOT TESTED";}?></td>
				<td><?= $Request['req_by']?></td>
			</tr>
		<?php
		endforeach;
	}

	if (isset($_POST['GetInvestigationResults'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$rows = $db->ReadArray("SELECT * FROM tbl_laboratory_log WHERE fileno = '$fileno' AND facility_from='Out-patient'");
		foreach($rows as $LogBook): ?>
			<tr onclick="window.open('labreport.php?labno=<?= $LogBook['labno']?>')" style="cursor: pointer;">
				<td><?= "Laboratory"?></td>
				<td><?= $LogBook['date_specimen_received']?></td>
				<td><?= $LogBook['investigation']?></td>
				<td><?= $LogBook['specimen']?></td>
				<td><?= $LogBook['turn_around_time']?></td>
				<td><?= $LogBook['test_lower_range']." - ".$LogBook['test_upper_range']?></td>
				<td><?= $LogBook['result']?></td>
				<td><?= $LogBook['status']?></td>
			</tr>
		<?php
		endforeach;

		$rows = $db->ReadArray("SELECT * FROM tbl_radiology_log WHERE fileno = '$fileno' AND facility_from='Out-patient'");
		foreach($rows as $LogBook): ?>
		?>
			<tr onclick="GetImages('<?= $LogBook['req_id']?>')" style="cursor: pointer;">
				<td><?= "Radiology"?></td>
				<td><?= $LogBook['investigation_date']?></td>
				<td><?= $LogBook['investigation']?></td>
				<td><?= "--"?></td>
				<td><?= "--"?></td>
				<td><?= "--"?></td>
				<td><?= $LogBook['comment']?></td>
				<td><?= $LogBook['status']?></td>
			</tr>
		<?php
		endforeach;
	}

	if (isset($_POST['GetImages'])) {
		$req_id = mysqli_real_escape_string($conn,$_POST['req_id']);
		$rows = $db->ReadArray("SELECT * FROM tbl_radiology_images WHERE req_id='$req_id'");
		$image_list = array();
		foreach($rows as $row):
			array_push($image_list, $row['image_url']);
		endforeach;
		echo json_encode($image_list);
	}

	if (isset($_POST['GetDiagnosis'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$sql = "SELECT * FROM tbl_opd_disease_diagnosis WHERE fileno = '$fileno' ";
		$rows = $db->ReadArray($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='5'>There is no diagnosis yet.</td></tr>";
			return;
		}
		foreach($rows as $row) :?>
		<tr>
			<td><?= $row['d_date']?></td>
			<td><?= $row['d_name']?></td>
			<td><?= $row['d_code']?></td>
			<td><?= $row['d_comment']?></td>
			<td><?= $row['doctor']?></td>
		</tr>
		<?php
		endforeach;
	}

	if (isset($_POST['GetPrescription'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$sql = "SELECT * FROM tbl_opd_service_request WHERE fileno = '$fileno' AND req_department ='Pharmacy' ORDER BY req_id DESC";
		$rows = $db->ReadArray($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='5'>There is no drug precribed yet.</td></tr>";
			return;
		}
		foreach($rows as $row) :?>
		?>
		<tr>
			<td><?= $row['req_date']?></td>
			<td><?= $row['req_name']?></td>
			<td><?= $row['req_des']?></td>
			<td><?= $row['req_comment']?></td>
			<td><?= $row['req_by']?></td>
		</tr>
		<?php
		endforeach;
	}
	
	if (isset($_POST['GetConsumables'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$sql = "SELECT * FROM tbl_opd_service_request WHERE fileno = '$fileno' AND req_comment = 'Consumable' ORDER BY req_id DESC";
		$rows = $db->ReadArray($sql);
		$i=0;
		foreach($rows as $row) : $i++;?>
		?>
		<tr>
			<td><?= $i."."?></td>
			<td><?= $row['req_date']?></td>
			<td><?= $row['req_name']?></td>
			<td><?= $row['req_des']?></td>
			<td><?= $row['req_by']?></td>
		</tr>
		<?php
		endforeach;
	}

	if (isset($_POST['GetDispositions'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$res = $db->ReadAll( "SELECT * FROM tbl_opd_disposition where fileno='$fileno' ");
		while ($row=mysqli_fetch_assoc($res)) {
		 	?>
		 	<tr style="cursor: pointer;">
		 		<td><?= $row['ref_date'] ?></td>
		 		<td><?= $row['ref_type'] ?></td>
		 		<td><?= $row['ref_to'] ?></td>
		 		<td><?= $row['ref_reason'] ?></td>
		 		<td><?= $row['ref_comment'] ?></td>		 		
		 		<td><?= $row['doctor'] ?></td>
		 	</tr>
		 	<?php
		 } 
	}

//Health CASES
	if (isset($_POST['SaveHealthCase'])) {
		$case_date = date('d/m/Y H:i:s');
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
        $complaint = mysqli_real_escape_string($conn,$_POST['complaint']);
        $period = mysqli_real_escape_string($conn,$_POST['period']);
        $pre_med_note = mysqli_real_escape_string($conn,$_POST['pre_med_note']);
        $physical_examination_note = mysqli_real_escape_string($conn,$_POST['physical_examination_note']);

		$doctor = $_SESSION['Fullname'];

		if ($db->Exists("SELECT * FROM tbl_opd_disposition WHERE fileno = '$fileno'")) {
			echo "Patient already discharged"; return;
		}
		$sql = "INSERT INTO tbl_healthcase(fileno,case_date, complaint, period, pre_med_note ,physical_examination_note,doctor) VALUES ('$fileno','$case_date','$complaint','$period','$pre_med_note','$physical_examination_note','$doctor')";
		echo $db->Query($sql);
	}

//PROCEDURES
	if (isset($_POST['GetProceduresServices'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$procedure_name = mysqli_real_escape_string($conn, $_POST['procedure_name']);
		if ($procedure_name =='') {return;}

		$treatement_scheme = $db->ReadOne("SELECT * FROM tbl_opd_visits WHERE fileno='$fileno'")['treatement_scheme'];
		$sql = "SELECT * FROM tbl_item WHERE item_name LIKE '%$procedure_name%' AND item_type = 'Medical Procedure' ORDER BY item_name ASC LIMIT 10";
		$rows = $db->ReadArray($sql);
		if (!$db->Exists($sql)) {
			echo "<tr><td colspan='3'>Medical procedure not found in database</td></tr>"; return;
		}
		foreach ($rows as $row):
			$coporatable = ($treatement_scheme=='Corporate' && $row['cop_payment'] == 'Yes')?true:false;
			?>
			<tr onclick="$('#procedure_name').val($(this).find('td:nth-child(1)').text()); $('#procedure_cost').val($(this).find('td:nth-child(2)').text()); $('#procedure_payment').val($(this).find('td:nth-child(3)').text())">
				<td><?= $row['item_name']?></td>
				<td><?= ($coporatable)?$row['item_rate_cop']:$row['item_rate_cash']?></td>
				<td><?= ($coporatable)?"Corporate":"Cash"?></td>
			</tr>
		<?php
		endforeach;
	}

	if (isset($_POST['SaveProcedure'])) {
		$today = date('d/m/Y H:i:s');
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$procedure_name = mysqli_real_escape_string($conn,$_POST['procedure_name']);
		$procedure_cost = mysqli_real_escape_string($conn,$_POST['procedure_cost']);
		$procedure_note = mysqli_real_escape_string($conn,$_POST['procedure_note']);
		$payment_type = mysqli_real_escape_string($conn,$_POST['payment_type']);
		$req_status = ($payment_type=='Cash')?'not granted':'granted';

		if ($db->CountRows("SELECT * FROM tbl_healthcase WHERE fileno = '$fileno'")==0) {
			echo "Record Observation and Examination in order to add procedures"; return;
		}

		if ($db->Exists("SELECT * FROM tbl_opd_disposition WHERE fileno = '$fileno'")) {
			echo "Patient already discharged"; return;
		}
		$sql_array = array();
		$sql_array[] = "INSERT INTO tbl_opd_medical_procedure(fileno,procedure_date,procedure_name,procedure_note,doctor) VALUES ('$fileno','$today','$procedure_name','$procedure_note','$_SESSION[Fullname]')";
		$sql_array[] = "INSERT INTO tbl_opd_service_request(fileno, refno, req_date, req_name, req_department, req_cost, payment_type,req_status, req_by) VALUES ('$fileno','$refno','$today','$procedure_name','OPD','$procedure_cost','$payment_type','$req_status','$_SESSION[Fullname]')";

		echo $db->query_sql_array($sql_array);
	}		

//INVESTIGATION
	if (isset($_POST['GetInvestigationServices'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$service_point = mysqli_real_escape_string($conn,$_POST['service_point']);
		$service_name = mysqli_real_escape_string($conn,$_POST['service_name']);
		$sql = null;
		switch ($service_point) {
			case 'Laboratory':
				$sql = "SELECT * FROM tbl_item WHERE item_type='Laboratory Service' AND item_name like '%$service_name%' LIMIT 15";
				break;
			case 'Radiology':
				$sql = "SELECT * FROM tbl_item WHERE item_type='Radiology Service' AND  item_name like '%$service_name%' LIMIT 15";
				break;
			case '':
				return;
		}
		$treatement_scheme = $db->ReadOne("SELECT * FROM tbl_opd_visits WHERE fileno='$fileno'")['treatement_scheme'];
		$rows = $db->ReadArray($sql);
		foreach($rows as $row):
			$category = $db->ReadOne("SELECT * FROM tbl_item_drug_lab_types WHERE id='$row[item_category]'")['cat_name'];
			$coporatable = ($treatement_scheme=='Corporate' && $row['cop_payment'] == 'Yes')?true:false;?>
			<tr onclick="Mark(this)">
				<td><input type="checkbox"></td>
				<td><?= $row['item_code'] ?></td>
				<td><?= $row['item_name'] ?></td>
				<td><?= $category ?></td>
				<td><?= ($coporatable)?$row['item_rate_cop']:$row['item_rate_cash']?></td>
				<td><?= ($coporatable)?"Corporate":"Cash"?></td>
			</tr>
		<?php
		endforeach;
	}

	if (isset($_POST['SendInvestigationRequest'])) {
		sleep(0);
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$req_department = mysqli_real_escape_string($conn,$_POST['req_department']);
		if ($req_department=='') {echo "Select Service Point"; return;}
		$requests = json_decode($_POST['requests']);
		$today = date('d/m/Y H:i:s');

		if ($db->CountRows("SELECT * FROM tbl_healthcase WHERE fileno = '$fileno'")==0) {
			echo "Record Observation and Examination in order to request investigations"; return;
		}

		if ($db->Exists("SELECT * FROM tbl_opd_disposition WHERE fileno = '$fileno'")) {
			echo "Patient already discharged"; return;
		}
		$sql_array = array();
		foreach ($requests as $request):
			$i++;
			$item_code = $request[0];
			$Item = $db->ReadOne("SELECT * FROM tbl_item WHERE item_code='$item_code'");
			$item_name = $Item['item_name'];
			$item_cost = $request[1];
			$payment_type = $request[2];
			$req_status = ($request[2]=='Cash')?'not granted':'granted';
			$sql_array[] = "INSERT INTO tbl_opd_service_request (fileno, refno, req_date, req_name, req_department, req_cost,payment_type,req_status, req_by)VALUES('$fileno','$refno','$today','$item_name','$req_department','$item_cost','$payment_type','$req_status', '$_SESSION[Fullname]') ";							
		endforeach;
		 echo $db->query_sql_array($sql_array);
	}

//CODIFICATION
	if (isset($_POST['SearchDiseaqseCode'])) {
		$disease = $_POST['disease'];
		$sql = "SELECT * FROM tbl_icd10 Where filed4 like '%$disease%' ";
		$res = mysqli_query($conn,$sql);
		$count = 0;
		while ($row = mysqli_fetch_assoc($res)) {
			$count++;
			?>
			<i>
				<span onmouseover="$('#d_name').val($(this).text()); $('#d_code').val($('#'+<?= $count?>).text())" onclick="$('#SearchResult').hide()" style="width: 80%;"><?= $row['filed4']?></span>
				<span id="<?= $count?>" class="code" style="width: 20%; font-weight: bold; border-left: 1px solid rgba(100,100,100,0.5);"><?= $row['filed1']; echo "."; echo $row['filed2']; ?></span>
			</i>
			<?php
		}
	}

	if (isset($_POST['SaveDiagnosis'])) {
		sleep(0);
		$fileno = $_POST['fileno'];
		$today = date('d/m/Y H:i:s');
		$d_name = mysqli_real_escape_string($conn,$_POST['d_name']);
		$d_code = mysqli_real_escape_string($conn,$_POST['d_code']);
		$d_comment = $_POST['d_comment'];
		$doctor = $_SESSION['Fullname'];

		if ($db->CountRows("SELECT * FROM tbl_opd_disposition WHERE fileno = '$fileno'")>0) {
			echo "You cannot edit this file because the final action/plan already made on it. Contact your system admin for any help regarding this"; return;
		}
		echo $db->Query("INSERT INTO  tbl_opd_disease_diagnosis (fileno,d_date, d_name, d_code, d_comment,doctor)  VALUES ('$fileno','$today','$d_name','$d_code','$d_comment','$doctor')");
	}

//PRESCRIPTION
	if (isset($_POST['GetDrugList'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$drugname = mysqli_real_escape_string($conn,$_POST['drugname']);

		if ($drugname=='') {return;}

		$sql  = "SELECT * FROM tbl_item WHERE item_type='Drug' AND item_name LIKE '%$drugname%' LIMIT 10";
		$query = $db->ReadArray($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='5'>There is no such drug in database.</td></tr>";
			return;
		}
	    $treatement_scheme = $db->ReadOne("SELECT * FROM tbl_opd_visits WHERE fileno='$fileno'")['treatement_scheme'];
		$rows = $db->ReadArray($sql);
		foreach($rows as $row):
			$category = $db->ReadOne("SELECT * FROM tbl_item_drug_lab_types WHERE id='$row[item_category]'")['cat_name'];
			$sub_category = $db->ReadOne("SELECT * FROM tbl_item_drug_lab_sub_types WHERE id='$row[item_sub_category]'")['sub_cat_name'];
			$coporatable = ($treatement_scheme=='Corporate' && $row['cop_payment'] == 'Yes')?true:false;?>
	        <tr onclick="GetDrugProperties('<?= $row['item_code']?>',$(this))">
				<td><?= $row['item_name']?></td>
				<td><?= $category?></td>	          
				<td><?= $sub_category?></td>
				<td><?= ($coporatable)?$row['item_rate_cop']:$row['item_rate_cash']?></td>
				<td><?= ($coporatable)?"Corporate":"Cash"?></td>
	        </tr>
        <?php 
    	endforeach;
	}


	if (isset($_POST['SavePrescription'])) {
		$refno = $_POST['refno'];
		$today = date('d/m/Y H:i:s');
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$drugname = mysqli_real_escape_string($conn,$_POST['drugname']);
		$dosage = mysqli_real_escape_string($conn,$_POST['dosage']);
		$drugPrice = mysqli_real_escape_string($conn,$_POST['drugPrice']);
		$instructions = mysqli_real_escape_string($conn,$_POST['instructions']);
		$payment_type = mysqli_real_escape_string($conn,$_POST['drug_payment']);
		$req_status = ($payment_type=='Cash')?'not granted':'granted';

		if ($db->CountRows("SELECT * FROM tbl_healthcase WHERE fileno = '$fileno'")==0) {
			echo "Record Observation and Examination in order to prescribe drugs"; return;
		}

		if ($db->Exists("SELECT * FROM tbl_opd_disposition WHERE fileno = '$fileno'")) {
			echo "Patient already discharged"; return;
		}

		echo $db->Query("INSERT INTO tbl_opd_service_request (fileno,refno, req_date, req_name, req_des, req_comment, req_department, req_cost,payment_type,req_status,req_by)VALUES('$fileno','$refno','$today','$drugname','$dosage','$instructions', 'Pharmacy','$drugPrice','$payment_type','$req_status','$_SESSION[Fullname]') ");
	}

//CONSUMABLES
	if (isset($_POST['GetConsumableList'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$consumable_type = mysqli_real_escape_string($conn,$_POST['consumable_type']);
		$cons_search = mysqli_real_escape_string($conn,$_POST['cons_search']);

		if ($consumable_type=='Consumable') {
			$sql  = "SELECT * FROM tbl_item WHERE (item_type='$consumable_type' AND item_name LIKE '%$cons_search%' AND chargeable='Yes') LIMIT 10";
		}else{
			$sql  = "SELECT * FROM tbl_item WHERE item_type='$consumable_type' AND item_name LIKE '%$cons_search%' LIMIT 10";
		}

		$query = $db->ReadArray($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='3'>There is no such item in database.</td></tr>";
			return;
		}
	    $treatement_scheme = $db->ReadOne("SELECT * FROM tbl_opd_visits WHERE fileno='$fileno'")['treatement_scheme'];
		$rows = $db->ReadArray($sql);
		foreach($rows as $row):
			$coporatable = ($treatement_scheme=='Corporate' && $row['cop_payment'] == 'Yes')?true:false;?>
	        <tr onclick="GetConsumeProperties($(this))">
				<td><?= $row['item_name']?></td>
				<td><?= ($coporatable)?$row['item_rate_cop']:$row['item_rate_cash']?></td>
				<td><?= ($coporatable)?"Corporate":"Cash"?></td>
	        </tr>
        <?php 
    	endforeach;
	}


	if (isset($_POST['SaveConsumable'])) {
		$today = date('d/m/Y H:i:s');
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$cons_name = mysqli_real_escape_string($conn,$_POST['cons_name']);
		$cons_cost = mysqli_real_escape_string($conn,$_POST['cons_cost']);
		$cons_quantity = mysqli_real_escape_string($conn,$_POST['cons_quantity']);
		$cons_note = mysqli_real_escape_string($conn,$_POST['cons_note']);
		$payment_type = mysqli_real_escape_string($conn,$_POST['cons_payment']);

		$req_status = ($payment_type=='Cash')?'not granted':'granted';

		if ($db->Exists("SELECT * FROM tbl_opd_disposition WHERE fileno = '$fileno'")) {
			echo "Patient already discharged"; return;
		}

		echo $db->Query("INSERT INTO tbl_opd_service_request (fileno,refno, req_date, req_name, req_des, req_comment, req_department, req_cost,payment_type,req_status,req_by)VALUES('$fileno','$refno','$today','$cons_name','$cons_quantity','Consumable', 'OPD','$cons_cost','$payment_type','$req_status','$_SESSION[Fullname]') ");
	}


//DISPOSITION
	if (isset($_POST['SaveDisposition'])) {
		sleep(0);
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$today = date('d/m/Y H:i:s');
		$ref_type = mysqli_real_escape_string($conn,$_POST['ref_type']); 
		$ref_to = mysqli_real_escape_string($conn,$_POST['ref_to']); 
		$ref_reason = mysqli_real_escape_string($conn,$_POST['ref_reason']); 
		$doctor = $_SESSION['Fullname']; 

		if ($db->CountRows("SELECT * FROM tbl_healthcase WHERE fileno = '$fileno'")==0) {
			echo "Record Observation and Examination in order to make final action or plan for the patient"; return;
		}

		if ($db->CountRows("SELECT * FROM tbl_opd_disposition WHERE fileno = '$fileno'")>0) {
			echo "You cannot edit this file because the final action/plan already made on it. Contact your system admin for any help regarding this"; return;
		}
		
		echo $db->Query("INSERT INTO tbl_opd_disposition (`fileno`,`ref_type`, `ref_date`, `ref_to`, `ref_reason`, `doctor`) VALUES ('$fileno','$ref_type','$today','$ref_to','$ref_reason','$doctor')");

	}

//Summary
	if (isset($_POST['CloseHealthFile'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		if ($db->CountRows("SELECT * FROM tbl_healthcase WHERE fileno = '$fileno'")==0) {
			echo "Record Observation and Examination in order to close the health file"; return;
		}

		echo $db->Query("UPDATE tbl_opd_visits SET file_status='Closed' WHERE fileno='$fileno'");
	}

	if (isset($_POST['GetSummary'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
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
	<?php
	}