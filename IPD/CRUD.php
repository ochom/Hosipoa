<?php
include('../ConnectionClass.php');
include('../db_class.php');
session_start();


$db = new CRUD();

//Patient Admission
	if (isset($_POST['searchOPD_Patient'])) {
		$searchVal = mysqli_real_escape_string($conn,$_POST['searchVal']);
		$sql = "SELECT * FROM tbl_patient WHERE refno NOT IN (SELECT refno from tbl_ipd_admission WHERE status='Active') AND (refno LIKE '%$searchVal%' or Fullname LIKE '%$searchVal%' ) ORDER BY refno ASC LIMIT 15";
		$res = $db->ReadAll($sql);
		while ($patient = mysqli_fetch_assoc($res)) {
		?>
			<tr>
				<td><?= $patient['refno'] ?></td>
				<td><?= $patient['fullname']?></td>
				<td><?= $patient['sex'] ?></td>
				<td>
				<?php if($patient['ins_status']=='NO'):?>
					<button class="btn btn-sm btn-outline-success" onclick="SelectPatient('<?= $patient['refno']?>','Cash')">Cash</button>
				<?php else:?>
					<button class="btn btn-sm btn-outline-success" onclick="SelectPatient('<?= $patient['refno']?>','Cash')">Cash</button>
					<button class="btn btn-sm btn-outline-primary" onclick="SelectPatient('<?= $patient['refno']?>','Corporate')">NHIF/Corporate</button>
				<?php endif;?>
				</td>
			</tr>
		<?php
		}
	}

	if (isset($_POST['GetMyOPDDiagnosis'])) {
		$refno = mysqli_real_escape_string($conn,$_POST['refno']); 
		$latestVisit = $db->ReadOne("SELECT * FROM tbl_opd_visits WHERE patient_id='$refno' ORDER BY fileno DESC LIMIT 1");
		$fileno = $latestVisit['fileno'];
		$opd_diagnosis = $db->ReadAll("SELECT * FROM tbl_opd_disease_diagnosis WHERE fileno='$fileno'");
		$i=0;
		while ($row = mysqli_fetch_assoc($opd_diagnosis)) {
			$i++;
		?>
			<tr>
				<td><?= $i?></td>
				<td><?= $row['d_name']?></td>
				<td><?= $row['d_code']?></td>
			</tr>
		<?php
		}
	}

	if (isset($_POST['AdmitPatient'])) {
		$adm_date = date('d/m/Y H:i:s');
		$refno = mysqli_real_escape_string($conn,$_POST['refno']); 
		$treatement_scheme = mysqli_real_escape_string($conn,$_POST['treatement_scheme']);
		$ward_id = mysqli_real_escape_string($conn,$_POST['ward_id']); 
		$bed_number = mysqli_real_escape_string($conn,$_POST['bed_number']);
		$admission_notes = mysqli_real_escape_string($conn,$_POST['admission_notes']);

		$Ward = $db->ReadOne("SELECT * FROM tbl_ipd_wards WHERE ward_id='$ward_id'");
		$admit_fee = ($treatement_scheme=='Corporate' || $Ward['ward_admin_cop'] !='')?$Ward['ward_admin_cop']:$Ward['ward_admin_cash'];
		$daily_bed_fee = ($treatement_scheme=='Corporate' || $Ward['ward_rate_cop'] !='' )?$Ward['ward_rate_cop']:$Ward['ward_rate_cash'];

		if ($db->Exists("SELECT * FROM tbl_opd_visits WHERE patient_id='$refno' AND file_status='Opened'")) {
			echo "This patient has active OPD file opened. You cannot admit until the file is closed."; return;
		}

		if ($db->CountRows("SELECT * FROM tbl_ipd_admission Where refno='$refno' AND status='Active'")) {
			echo "This patient has active IPD file opened. You cannot admit until the file is closed."; return;
			return;			
		}

		if ($db->CountRows("SELECT * FROM tbl_ipd_beds Where bed_number='$bed_number'  AND bed_status != 'Empty' ")) {
			echo "This bed has already been allocated to another patient";
			return;			
		}

		echo $db->Query("INSERT INTO tbl_ipd_admission (refno,treatement_scheme, ipd_ward, daily_charge, adm_date,bed_number,admitted_by) VALUES ('$refno','$treatement_scheme', '$ward_id','$daily_bed_fee','$adm_date','$bed_number','$_SESSION[Fullname]')");

		$AI = $db->ReadOne("SELECT last_insert_id() AS LAST_ID FROM tbl_opd_visits");
		$zeros = "00000";
		$len = strlen($AI['LAST_ID']);
		$ipd_fileno = substr($zeros,0,5-$len).$AI['LAST_ID'];

		$latestVisit = $db->ReadOne("SELECT * FROM tbl_opd_visits WHERE patient_id='$refno' ORDER BY fileno DESC LIMIT 1");
		$opd_fileno = $latestVisit['fileno'];
		$opd_diagnosis = $db->ReadArray("SELECT * FROM tbl_opd_disease_diagnosis WHERE fileno='$opd_fileno'");
		foreach($opd_diagnosis as $row):
			$db->Query("INSERT INTO tbl_ipd_provisional_diagnosis(ipd_date, opd_fileno,ipd_fileno, diagnosis, icd10) VALUES ('$row[d_date]','$row[fileno]','$ipd_fileno','$row[d_name]','$row[d_code]')");
		endforeach;
		
		$db->Query("UPDATE tbl_ipd_wards SET ward_capacity = (ward_capacity+1) WHERE ward_id='$ward_id'");
		$db->Query("UPDATE tbl_ipd_beds SET bed_status='$refno' WHERE bed_number='$bed_number'");

		echo $db->Query("INSERT INTO tbl_ipd_service_request (fileno,refno,req_date, req_name, req_department, payment_type, req_cost, req_status) VALUES ('$ipd_fileno','$refno','$adm_date','Admission','Inpatient','$treatement_scheme','$admit_fee','granted')");
	}

//FilterPatientList
	if (isset($_POST['FilterPatientList'])) {
		$searchby = mysqli_real_escape_string($conn,$_POST['searchBy']);
		$searchVal = mysqli_real_escape_string($conn,$_POST['searchVal']);
		switch ($searchby) {
			case 'refno':
			$sql = "SELECT tbl_patient.*, tbl_ipd_admission.* From tbl_patient RIGHT JOIN tbl_ipd_admission ON tbl_patient.refno = tbl_ipd_admission.refno Where (tbl_ipd_admission.status = 'Active' AND tbl_ipd_admission.refno LIKE '%$searchVal%') ORDER BY tbl_patient.fullname ASC LIMIT 30";
				break;
			case 'adm_no':
			$sql = "SELECT tbl_patient.*, tbl_ipd_admission.* From tbl_patient RIGHT JOIN tbl_ipd_admission ON tbl_patient.refno = tbl_ipd_admission.refno Where (tbl_ipd_admission.status = 'Active' AND tbl_ipd_admission.adm_no LIKE '%$searchVal%') ORDER BY tbl_patient.fullname ASC LIMIT 30";
				break;
			case 'fullname':
			$sql = "SELECT tbl_patient.*, tbl_ipd_admission.* From tbl_patient RIGHT JOIN tbl_ipd_admission ON tbl_patient.refno = tbl_ipd_admission.refno Where (tbl_ipd_admission.status = 'Active' AND tbl_patient.fullname LIKE '%$searchVal%') ORDER BY tbl_patient.fullname ASC LIMIT 30";
				break;
			case 'ward_id':
			$sql = "SELECT tbl_patient.*, tbl_ipd_admission.* From tbl_patient RIGHT JOIN tbl_ipd_admission ON tbl_patient.refno = tbl_ipd_admission.refno Where (tbl_ipd_admission.status = 'Active' AND tbl_ipd_admission.ipd_ward LIKE '%$searchVal%') ORDER BY tbl_patient.fullname ASC LIMIT 30";
				break;
			default:
				break;
		}
		$res = mysqli_query($conn,$sql);
        while ($Patient = mysqli_fetch_assoc($res)) {
          $ward_id = $Patient['ipd_ward'];
          $WardInfo = $db->ReadOne("SELECT * FROM tbl_ipd_wards WHERE ward_id='$ward_id'");
          ?><tr>
              <td><?= $Patient['refno']?></td>
              <td ><?= $Patient['adm_no']?></td>
              <td ><?= $Patient['fullname']?></td>
              <td ><?= $WardInfo['ward_name']?></td>
              <td>
                <a class="btn btn-sm btn-outline-primary" href="Patient Page.php?adm_no=<?= $Patient['adm_no']?>"> Open File
                </a>
              </td>
            </tr>
          <?php
        }
	}


//PATIENT PAGE
	if (isset($_POST['GetVitals'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$res = $db->ReadAll("SELECT * FROM tbl_ipd_vitals WHERE ipd_fileno='$fileno' ORDER BY id DESC");
		$i=0;
		while ($row = mysqli_fetch_assoc($res)) {
			$i++;
		?>
		<tr>
			<td><?= $i?></td>
			<td><?= $row['vitals_date']?></td>
			<td><?= $row['temperature']?></td>
			<td><?= $row['weight']?></td>
			<td><?= $row['bp_sys']."/".$row['bp_dia']?></td>
			<td><?= $row['pulse']?></td>
			<td><?= $row['respiration']?></td>
			<td><?= $row['remarks']?></td>
			<td><?= $row['doctor']?></td>
		</tr>
		<?php
		}
	}

	if (isset($_POST['GetObservations'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$res = $db->ReadAll("SELECT * FROM tbl_ipd_observations WHERE ipd_fileno='$fileno'");
		$i=0;
		while ($row = mysqli_fetch_assoc($res)) {
			$i++;
		?>
		<tr>
			<td><?= $i?></td>
			<td><?= $row['ob_date']?></td>
			<td><?= $row['complaint']?></td>
			<td><?= $row['observation']?></td>
			<td><?= $row['nursing_note']?></td>
			<td><?= $row['doctor']?></td>
		</tr>
		<?php
		}
	}

	if (isset($_POST['GetCardex'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$res = $db->ReadAll("SELECT * FROM tbl_ipd_patient_cardex WHERE ipd_fileno='$fileno'");
		$i=0;
		while ($row = mysqli_fetch_assoc($res)) {
			$i++;
		?>
		<tr>
			<td><?= $i?></td>
			<td><?= $row['ipd_date']?></td>
			<td><?= $row['remarks']?></td>
			<td><?= $row['nurse']?></td>
		</tr>
		<?php
		}
	}

	if (isset($_POST['GetItems'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$rows = $db->ReadArray("SELECT * FROM tbl_ipd_items_provided WHERE ipd_fileno='$fileno'");
		$i=0;
		foreach($rows as $row): $i++;?>
			<tr>
				<td><?= $i?></td>
				<td><?= $row['ipd_date']?></td>
				<td><?= $row['item_name']?></td>
				<td><?= $row['quantity']?></td>
				<td><?= $row['doctor']?></td>
			</tr>
		<?php
		endforeach;
	}

	if (isset($_POST['GetProcedures'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$res = $db->ReadAll("SELECT * FROM tbl_ipd_procedures WHERE ipd_fileno = '$fileno'");
		$i=0;
		while ($row = mysqli_fetch_assoc($res)) {
			$i++;
		?>
		<tr>
			<td><?= $i?></td>
			<td><?= $row['ipd_date']?></td>
			<td><?= $row['procedure_name']?></td>
			<td><?= $row['procedure_cost']?></td>
			<td><?= $row['created_by']?></td>
			<td><?= $row['surgeon_name']?></td>
			<td><?= $row['scheduled_date']?></td>
			<td>
				<button onclick="SwitchConsent('<?= $row['id']?>','<?= $row['status']?>')" class="btn btn-sm btn-outline-primary"><?= $row['status']?></button>
			</td>
		</tr>
		<?php
		}
	}

	if (isset($_POST['GetInvestigationRequests'])) {
		$fileno = $_POST['fileno'];
		$query = $db->ReadAll("SELECT * FROM tbl_ipd_service_request WHERE fileno = '$fileno' AND (req_department='Laboratory' OR req_department='Radiology') AND req_status != 'delivered'");
		while ($Request = mysqli_fetch_assoc($query)) {
		?>
			<tr>
				<td><?= $Request['req_date']?></td>
				<td><?= $Request['req_name']?></td>
				<td><?= $Request['req_department']?></td>
				<td><?= $Request['req_cost']?></td>
				<td><?= ($Request['req_status']=='granted')?"NOT TESTED":""?></td>
				<td><?= $Request['req_by']?></td>
			</tr>
		<?php
		}
	}

	if (isset($_POST['GetInvestigationResults'])) {
		$fileno = $_POST['fileno'];
		$query = $db->ReadAll("SELECT * FROM tbl_laboratory_log WHERE fileno = '$fileno' AND facility_from='In-patient'");
		while ($LogBook = mysqli_fetch_assoc($query)) {
		?>
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
		}
		$query = $db->ReadAll("SELECT * FROM tbl_radiology_log WHERE fileno = '$fileno' AND facility_from='In-patient'");
		while ($LogBook = mysqli_fetch_assoc($query)) {
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
		}
	}

	if (isset($_POST['GetImages'])) {
		$req_id = mysqli_real_escape_string($conn,$_POST['req_id']);
		$Images = $db->ReadAll("SELECT * FROM tbl_radiology_images WHERE req_id='$req_id'");
		$image_list = array();
		while ($row = mysqli_fetch_assoc($Images)) {
			array_push($image_list, $row['image_url']);
		}
		echo json_encode($image_list);
	}

	if (isset($_POST['GetDiagnosis'])) {
		$fileno = $_POST['fileno'];
		$sql = "SELECT * FROM tbl_ipd_disease_diagnosis WHERE fileno = '$fileno'";
		$query = $db->ReadAll($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='5'>There is no diagnosis yet.</td></tr>";
			return;
		}
		while ($row = mysqli_fetch_assoc($query)) {
		?>
		<tr>
			<td><?= $row['d_date']?></td>
			<td><?= $row['d_name']?></td>
			<td><?= $row['d_code']?></td>
			<td><?= $row['d_comment']?></td>
			<td><?= $row['doctor']?></td>
		</tr>
		<?php
		}
	}

	if (isset($_POST['GetPrescriptions'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$sql = "SELECT * FROM tbl_ipd_prescriptions WHERE ipd_fileno = '$fileno' ORDER BY id DESC";
		$query = $db->ReadAll($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='9'>There is no drug precribed yet.</td></tr>";
			return;
		}
		$i=0;
		while ($row = mysqli_fetch_assoc($query)) {
			$i++;
		?>
		<tr>
			<td><?= $i?></td>
			<td><?= $row['ipd_date']?></td>
			<td><?= $row['drug_name']?></td>
			<td><?= $row['prescribed_qty']?></td>
			<td><?= $row['given_qty']?></td>
			<td><?= $row['instructions']?></td>
			<td><?= $row['dosage']?></td>
			<td><?= $row['doctor']?></td>
			<td>
				<button class="btn btn-outline-primary btn-sm" onclick="IssueDrug('<?= $row['id']?>','<?= mysqli_real_escape_string($conn,$row['drug_name'])?>')"><i class="oi oi-circle-check"></i> Dispense</button>
				<button class="btn btn-outline-primary btn-sm" onclick="DrugHistory('<?= $row['id']?>')"><i class="oi oi-document"></i> View</button>
			</td>
		</tr>
		<?php
		}
	}

	if (isset($_POST['DrugHistory'])) {
		$prescription_id = mysqli_real_escape_string($conn,$_POST['prescription_id']);
		$sql = "SELECT * FROM tbl_ipd_drug_issue_history WHERE prescription_id = '$prescription_id' ORDER BY id DESC";
		$query = $db->ReadAll($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='5'>No history yet.</td></tr>";
			return;
		}
		$i=0;
		while ($row = mysqli_fetch_assoc($query)) {
			$i++;
		?>
		<tr>
			<td><?= $i?></td>
			<td><?= $row['pres_date']?></td>
			<td><?= $row['quantity_issued']?></td>
			<td><?= $row['issued_by']?></td>
		</tr>
		<?php
		}
	}


//VITALS AND REVIEW
	if (isset($_POST['SaveVitals'])) {
		$today = date('d/m/Y H:i:s');
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$temperature = mysqli_real_escape_string($conn,$_POST['temperature']);
		$weight = mysqli_real_escape_string($conn,$_POST['weight']);
		$bp_systolic = mysqli_real_escape_string($conn,$_POST['bp_systolic']);
		$bp_diastolic = mysqli_real_escape_string($conn,$_POST['bp_diastolic']);
		$pulse = mysqli_real_escape_string($conn,$_POST['pulse']);
		$respiration = mysqli_real_escape_string($conn,$_POST['respiration']);
		$remarks = mysqli_real_escape_string($conn,$_POST['remarks']);

		echo $db->Query("INSERT INTO tbl_ipd_vitals(vitals_date, ipd_fileno, temperature, bp_sys, bp_dia, weight,pulse, respiration, remarks, doctor) VALUES ('$today','$fileno','$temperature','$bp_systolic','$bp_diastolic','$weight','$pulse','$respiration','$remarks','$_SESSION[Fullname]')");
	}

	if (isset($_POST['SaveObservations'])) {
		$today = date('d/m/Y H:i:s');
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$complaint = mysqli_real_escape_string($conn,$_POST['complaint']);
		$observation = mysqli_real_escape_string($conn,$_POST['observation']);
		$nursing_note = mysqli_real_escape_string($conn,$_POST['nursing_note']);

		$treatement_scheme = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE adm_no='$fileno'")['treatement_scheme'];
		$static_charges = $db->ReadOne("SELECT * FROM tbl_static_services");
		$req_cost = ($treatement_scheme=='Corporate' || $static_charges['ipd_doc_cop'] !='' )?$static_charges['ipd_doc_cop']:$static_charges['ipd_doc_cash'];

		$db->Query("INSERT INTO tbl_ipd_observations(ob_date, ipd_fileno, complaint, observation, nursing_note,doctor) VALUES ('$today','$fileno','$complaint','$observation','$nursing_note','$_SESSION[Fullname]')");
		echo $db->Query("INSERT INTO tbl_ipd_service_request (fileno,refno,req_date, req_name, req_department, payment_type, req_cost, req_status) VALUES ('$fileno','$refno','$today','Doctor Review','Inpatient','$treatement_scheme','$req_cost','granted')");
	}

	if (isset($_POST['SaveCardex'])) {
		$today = date('d/m/Y H:i:s');
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$remarks = mysqli_real_escape_string($conn,$_POST['remarks']);
		echo $db->Query("INSERT INTO tbl_ipd_patient_cardex(ipd_date, ipd_fileno, remarks,nurse) VALUES ('$today','$fileno','$remarks','$_SESSION[Fullname]')");
	}



	if (isset($_POST['GetAvailableItems'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$consumable_type = mysqli_real_escape_string($conn,$_POST['consumable_type']);
		$cons_search = mysqli_real_escape_string($conn,$_POST['cons_search']);

		if ($consumable_type=='Consumable') {
			$sql  = "SELECT * FROM tbl_item WHERE (item_type='$consumable_type' AND item_name LIKE '%$cons_search%' AND chargeable='Yes') LIMIT 10";
		}else{
			$sql  = "SELECT * FROM tbl_item WHERE item_type='$consumable_type' AND item_name LIKE '%$cons_search%' LIMIT 10";
		}

		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='2'>IPD Item not found in database</td></tr>"; return;
		}
		$treatement_scheme = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE adm_no='$fileno'")['treatement_scheme'];
		$rows = $db->ReadArray($sql);
		foreach($rows as $row):
			$coporatable = ($treatement_scheme=='Corporate' && $row['cop_payment'] == 'Yes')?true:false;?>
			<tr onclick="$('#item_name').val($(this).find('td:nth-child(1)').text()); $('#item_cost').val($(this).find('td:nth-child(2)').text())">
				<td><?= $row['item_name']?></td>
				<td><?= ($coporatable)?$row['item_rate_cop']:$row['item_rate_cash']?></td>
			</tr>
		<?php
		endforeach;
	}

	if (isset($_POST['SaveItem'])) {
		$today = date('d/m/Y H:i:s');
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$item_name = mysqli_real_escape_string($conn,$_POST['item_name']);
		$item_quantity = mysqli_real_escape_string($conn,$_POST['item_quantity']);

		//**Payment criteria
		$Item = $db->ReadOne("SELECT * FROM tbl_item WHERE item_name='$item_name'");
		$treatement_scheme = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE adm_no='$fileno'")['treatement_scheme'];
		$coporatable = ($treatement_scheme=='Corporate' && $Item['cop_payment'] == 'Yes')?true:false;
		$item_rate = ($coporatable)?$Item['item_rate_cop']:$Item['item_rate_cash'];		
		$item_cost = $item_quantity * $item_rate;
		//**Payment criteria

		$qty_after = $db->ReadOne("SELECT (total_pieces-$item_quantity) AS qty_after FROM tbl_item WHERE item_name='$item_name'")['qty_after'];
		if ($qty_after<1) {
			echo "This item is out of stock and can therefore not be given to the patient."; return;
		}
		$sql_array = array();
		//reduce stock
		$sql_array[] = "UPDATE tbl_item SET item_quantity=((total_pieces-$item_quantity)/item_pieces_per_unit), total_pieces=(total_pieces-$item_quantity) WHERE item_name = '$item_name'";
		//give item to patient
		$sql_array[] = "INSERT INTO tbl_ipd_service_request (fileno,refno,req_date, req_name, req_des,req_comment, req_department, payment_type, req_cost) VALUES ('$fileno','$refno','$today','$item_name','$item_quantity','$item_rate','Inpatient','$treatement_scheme','$item_cost')";
		//add item to ipd items providex
		$sql_array[] = "INSERT INTO tbl_ipd_items_provided(ipd_date, ipd_fileno, item_name, quantity, cost,doctor) VALUES ('$today','$fileno', '$item_name', '$item_quantity', '$item_cost','$_SESSION[Fullname]')";
		if((int)$Item['nhif_rebate']>0 && ($coporatable)){
			$sql_array[] = "INSERT INTO tbl_ipd_nhif_rebates(rebate_date,fileno,provided_service,rebate_amount) VALUES ('$today','$fileno', '$Item[item_name]', '$Item[nhif_rebate]')";
		}

		echo $db->query_sql_array($sql_array);
	}



	if (isset($_POST['GetProceduresServices'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$procedure_name = mysqli_real_escape_string($conn, $_POST['procedure_name']);
		if($procedure_name==''){return;}

		$sql = "SELECT * FROM tbl_item WHERE item_name LIKE '%$procedure_name%' AND item_type = 'Medical Procedure' ORDER BY item_name ASC LIMIT 10";
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='2'>Medical procedure not found in database</td></tr>"; return;
		}

		$treatement_scheme = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE adm_no='$fileno'")['treatement_scheme'];
		$rows = $db->ReadArray($sql);
		foreach($rows as $row):
			$coporatable = ($treatement_scheme=='Corporate' && $row['cop_payment'] == 'Yes')?true:false;
			?>
			<tr onclick="$('#procedure_name').val($(this).find('td:nth-child(1)').text()); $('#procedure_cost').val($(this).find('td:nth-child(2)').text())">
				<td><?= $row['item_name']?></td>
				<td><?= ($coporatable)?$row['item_rate_cop']:$row['item_rate_cash']?></td>
			</tr>
		<?php
		endforeach;
	}


	if (isset($_POST['SaveProcedure'])) {
		$today = date('d/m/Y H:i:s');
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$procedure_name = mysqli_real_escape_string($conn,$_POST['procedure_name']);
	    $procedure_cost = mysqli_real_escape_string($conn,$_POST['procedure_cost']);
	    $scheduled_date = mysqli_real_escape_string($conn,$_POST['scheduled_date']);
	    $surgeon_name = mysqli_real_escape_string($conn,$_POST['surgeon_name']);

	    echo $db->Query("INSERT INTO tbl_ipd_procedures(ipd_fileno, ipd_date, procedure_name, procedure_cost, scheduled_date, created_by, surgeon_name) VALUES ('$fileno','$today','$procedure_name','$procedure_cost','$scheduled_date','$_SESSION[Fullname]','$surgeon_name')");
	/* Patient charged at patient consignment */	}

	if (isset($_POST['SavePatientConsent'])) {
		$today = date('d/m/Y H:i:s');
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$procedure_id = mysqli_real_escape_string($conn,$_POST['procedure_id']);
		$witness = mysqli_real_escape_string($conn,$_POST['witness']);
		$witness_name = mysqli_real_escape_string($conn,$_POST['witness_name']);
	    $image = $_POST['image'];
	    $image = substr($image,strpos($image,",")+1);
	    $image = base64_decode($image);

	    $file = "../images/patient_signs/patient_consent_sign_".$procedure_id.".png";
	    file_put_contents($file, $image);
	    echo "File created";

	    //**Payment criteria
	    $procedure_name = $db->ReadOne("SELECT * FROM tbl_ipd_procedures WHERE id='$procedure_id'")['procedure_name'];
	    $Item = $db->ReadOne("SELECT * FROM tbl_item WHERE item_name='$procedure_name'");
		$treatement_scheme = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE adm_no='$fileno'")['treatement_scheme'];
		$coporatable = ($treatement_scheme=='Corporate' && $Item['cop_payment'] == 'Yes')?true:false;
		$item_cost = ($coporatable)?$Item['item_rate_cop']:$Item['item_rate_cash'];	
		//**Payment criteria

		$sql_array = array();
		    $sql_array[] = "UPDATE tbl_ipd_procedures SET patient_consent_sign='$file',witness='$witness',witness_name='$witness_name',status='Nurse Checklist' WHERE id='$procedure_id'";

		    $sql_array[] = "INSERT INTO tbl_ipd_service_request (fileno,refno,req_date, req_name, req_department, payment_type, req_cost) VALUES ('$fileno','$refno','$today','$procedure_name','Theatre','$$treatement_scheme','$item_cost')";

		    if((int)$Item['nhif_rebate']>0 && ($coporatable)){
				$sql_array[] = "INSERT INTO tbl_ipd_nhif_rebates(rebate_date,fileno,provided_service,rebate_amount) VALUES ('$today','$fileno', '$Item[item_name]', '$Item[nhif_rebate]')";
			}
		echo $db->query_sql_array($sql_array);
    }

    if (isset($_POST['SaveNurseChecklist'])) {
		$today = date('d/m/Y H:i:s');
		$procedure_id = mysqli_real_escape_string($conn,$_POST['procedure_id']);
		$has_belongings = mysqli_real_escape_string($conn,$_POST['has_belongings']);
		$belongings = mysqli_real_escape_string($conn,$_POST['belongings']);
		$sugar = mysqli_real_escape_string($conn,$_POST['sugar']);
		$albumin = mysqli_real_escape_string($conn,$_POST['albumin']);
		$bladder_n_urinary = mysqli_real_escape_string($conn,$_POST['bladder_n_urinary']);
		$blood_in_litres = mysqli_real_escape_string($conn,$_POST['blood_in_litres']);
		$nurse_note = mysqli_real_escape_string($conn,$_POST['nurse_note']);

    	echo $db->Query("INSERT INTO tbl_ipd_procedure_nurse_checklist(procedure_id, ipd_date, has_property, patient_properties, sugar, albumin, bladder_n_urinary, blood_in_litres, nurse_name,nurse_note) VALUES ('$procedure_id','$today','$has_belongings','$belongings','$sugar','$albumin','$bladder_n_urinary','$blood_in_litres','$_SESSION[Fullname]','$nurse_note')");

    	echo $db->Query("UPDATE tbl_ipd_procedures SET status='Doctor Checklist' WHERE id='$procedure_id'");
    }

    if (isset($_POST['SaveDoctorChecklist'])) {
		$today = date('d/m/Y H:i:s');
		$procedure_id = mysqli_real_escape_string($conn,$_POST['procedure_id']);
    	$hydration = mysqli_real_escape_string($conn,$_POST['hydration']);
        $electrolyte = mysqli_real_escape_string($conn,$_POST['electrolyte']);
        $chest = mysqli_real_escape_string($conn,$_POST['chest']);
        $hb = mysqli_real_escape_string($conn,$_POST['hb']);
        $pvc = mysqli_real_escape_string($conn,$_POST['pvc']);
        $temperature = mysqli_real_escape_string($conn,$_POST['temperature']);
        $bp_sys = mysqli_real_escape_string($conn,$_POST['bp_sys']);
        $bp_dia = mysqli_real_escape_string($conn,$_POST['bp_dia']);
        $pulse = mysqli_real_escape_string($conn,$_POST['pulse']);
        $blood = mysqli_real_escape_string($conn,$_POST['blood']);
        $doctor_note = mysqli_real_escape_string($conn,$_POST['doctor_note']);

        echo $db->Query("INSERT INTO tbl_ipd_procedure_doctor_checklist(procedure_id, ipd_date, hydration, electrolyte, chest, hb, pvc, temperature, bp_sys, bp_dia, pulse, blood, doctor, doctor_note) VALUES ('$procedure_id','$today','$hydration','$electrolyte','$chest','$hb','$pvc','$temperature','$bp_sys','$bp_dia','$pulse','$blood','$_SESSION[Fullname]','$doctor_note')");

    	echo $db->Query("UPDATE tbl_ipd_procedures SET status='Anaesthetist Checklist' WHERE id='$procedure_id'");
    }

    if (isset($_POST['SaveAnaesthetistChecklist'])) {
		$today = date('d/m/Y H:i:s');
		$procedure_id = mysqli_real_escape_string($conn,$_POST['procedure_id']);
        $hb = mysqli_real_escape_string($conn,$_POST['hb']);
        $pvc = mysqli_real_escape_string($conn,$_POST['pvc']);
        $temperature = mysqli_real_escape_string($conn,$_POST['temperature']);
        $bp_sys = mysqli_real_escape_string($conn,$_POST['bp_sys']);
        $bp_dia = mysqli_real_escape_string($conn,$_POST['bp_dia']);
        $pulse = mysqli_real_escape_string($conn,$_POST['pulse']);
        $albumin = mysqli_real_escape_string($conn,$_POST['albumin']);
        $sugar = mysqli_real_escape_string($conn,$_POST['sugar']);
        $note = mysqli_real_escape_string($conn,$_POST['note']);
        $patient_fit = mysqli_real_escape_string($conn,$_POST['patient_fit']);

        $fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$refno = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE adm_no='$fileno'")['refno'];

		$item_name = $db->ReadOne("SELECT * FROM tbl_ipd_procedures WHERE id='$procedure_id'")['procedure_name'];
		$item_cost = $db->ReadOne("SELECT * FROM tbl_item WHERE item_name='$ProcedureName'")['item_rate_cash'];

		$sql_array = array();
        $sql_array[] = "INSERT INTO tbl_ipd_procedure_anaesthetist_checklist(procedure_id, ipd_date, hb, pvc, temperature, bp_sys, bp_dia, pulse, albumin, sugar, doctor, note, patient_fit) VALUES ('$procedure_id','$today','$hb','$pvc','$temperature','$bp_sys','$bp_dia','$pulse','$albumin','$sugar','$_SESSION[Fullname]','$note','$patient_fit')";

        if ($patient_fit=='Yes') {
        	$sql_array[] = "UPDATE tbl_ipd_procedures SET status='Operation Diagnosis' WHERE id='$procedure_id'";
        }else{
        	$sql_array[] = "UPDATE tbl_ipd_procedures SET status='Patient Not Fit, Start Over' WHERE id='$procedure_id'";
        }

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
		$treatement_scheme = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE adm_no='$fileno'")['treatement_scheme'];
		$rows = $db->ReadArray($sql);
		foreach($rows as $row):
			$coporatable = ($treatement_scheme=='Corporate' && $row['cop_payment'] == 'Yes')?true:false;
			$category = $db->ReadOne("SELECT * FROM tbl_item_drug_lab_types WHERE id='$row[item_category]'")['cat_name'];?>
			<tr onclick="Mark(this)">
				<td><input type="checkbox"></td>
				<td><?= $row['item_code'] ?></td>
				<td><?= $row['item_name'] ?></td>
				<td><?= $category ?></td>
				<td><?= ($coporatable)?$row['item_rate_cop']:$row['item_rate_cash']?></td>
			</tr>
		<?php
		endforeach;
	}

	if (isset($_POST['SendInvestigationRequest'])) {
		sleep(0);
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$req_department = mysqli_real_escape_string($conn,$_POST['req_department']);
		$requests = json_decode($_POST['requests']);
		$today = date('d/m/Y H:i:s');

		$sql_array = array();
		foreach ($requests as $request):
			//**Payment criteria
			$item_code = $request[0];
		    $Item = $db->ReadOne("SELECT * FROM tbl_item WHERE item_code='$item_code'");
			$treatement_scheme = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE adm_no='$fileno'")['treatement_scheme'];
			$coporatable = ($treatement_scheme=='Corporate' && $Item['cop_payment'] == 'Yes')?true:false;
			$item_cost = ($coporatable)?$Item['item_rate_cop']:$Item['item_rate_cash'];		
			//**Payment criteria
			$sql_array[] = "INSERT INTO tbl_ipd_service_request (fileno, refno, req_date, req_name, req_department, req_cost, payment_type, req_by)VALUES('$fileno','$refno','$today','$Item[item_name]','$req_department','$item_cost', '$treatement_scheme', '$_SESSION[Fullname]') ";	
			
			if((int)$Item['nhif_rebate']>0 && ($coporatable)){
				$sql_array[] = "INSERT INTO tbl_ipd_nhif_rebates(rebate_date,fileno,provided_service,rebate_amount) VALUES ('$today','$fileno', '$Item[item_name]', '$Item[nhif_rebate]')";
			}
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
				<span onmouseover="$('#d_name').val($(this).text()); $('#d_code').val($('#'+<?= $count?>).text())" onclick="$('#code_list').hide()" style="width: 80%;"><?= $row['filed4']?></span>
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
		$d_comment = mysqli_real_escape_string($conn,$_POST['d_comment']);
		$doctor = $_SESSION['Fullname'];

		echo $db->Query("INSERT INTO  tbl_ipd_disease_diagnosis (fileno,d_date, d_name, d_code, d_comment,doctor)  VALUES ('$fileno','$today','$d_name','$d_code','$d_comment','$doctor')");
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
	    $treatement_scheme = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE adm_no='$fileno'")['treatement_scheme'];
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
		$drug_quantity = mysqli_real_escape_string($conn,$_POST['drug_quantity']);
		$instructions = mysqli_real_escape_string($conn,$_POST['instructions']);

		$sql_array = array();
		$sql_array[] = "INSERT INTO tbl_ipd_service_request (fileno,refno, req_date, req_name, req_des, req_comment, req_department, req_cost,payment_type,req_by)VALUES('$fileno','$refno','$today','$drugname','$drug_quantity','$instructions', 'Pharmacy','$drugPrice','$treatement_scheme','$_SESSION[Fullname]') ";

		$sql_array[] = "INSERT INTO tbl_ipd_prescriptions (ipd_fileno, ipd_date, drug_name, instructions, prescribed_qty, dosage, doctor)VALUES('$fileno','$today','$drugname','$instructions','$drug_quantity', '$dosage','$_SESSION[Fullname]') ";
			
		if((int)$Item['nhif_rebate']>0 && ($coporatable)){
			$sql_array[] = "INSERT INTO tbl_ipd_nhif_rebates(rebate_date,fileno,provided_service,rebate_amount) VALUES ('$today','$fileno', '$Item[item_name]', '$Item[nhif_rebate]')";
		}
		echo $db->query_sql_array($sql_array);
	}

	if (isset($_POST['SaveDrugIssue'])) {
		$today = date('d/m/Y H:i:s');
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$prescription_id = mysqli_real_escape_string($conn,$_POST['prescription_id']);
		$given_qty = mysqli_real_escape_string($conn,$_POST['given_qty']);

		$Pre = $db->ReadOne("SELECT * FROM tbl_ipd_prescriptions WHERE id='$prescription_id'");
		
		$dosage_right_now = explode("x", $Pre['dosage'])[1];

		if ($given_qty > $dosage_right_now) {
			echo "This patient is supposed to receive only ".$dosage_right_now." piece(s) of this drug at a time"; return;
		}
		if ($given_qty+$Pre['given_qty'] > $Pre['prescribed_qty']) {
			echo "You cannot give more drugs than was prescribed"; return;
		}
		$sql_array = array();
		$sql_array[] = "UPDATE tbl_ipd_prescriptions SET given_qty=(given_qty+$given_qty) WHERE id='$prescription_id'";
		$sql_array[] = "INSERT INTO tbl_ipd_drug_issue_history(prescription_id,pres_date,quantity_issued,issued_by)values('$prescription_id','$today','$given_qty','$_SESSION[Fullname]')";

		echo $db->query_sql_array($sql_array);
	}

//CarePlan
	if (isset($_POST['SavePlan'])) {
		$today = date('d/m/Y H:i:s');
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$dis_type = mysqli_real_escape_string($conn,$_POST['dis_type']);
		$dis_destination = mysqli_real_escape_string($conn,$_POST['dis_destination']);
		$dis_reason  = mysqli_real_escape_string($conn,$_POST['dis_reason']);
		$dis_instruction  = mysqli_real_escape_string($conn,$_POST['dis_instruction']);

		$IPD = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE adm_no='$fileno'");

		if ($db->Exists("SELECT * FROM tbl_ipd_admission WHERE status='Discharged' AND adm_no='$fileno'")) {
			echo "This patient has already been discharged."; return;
		}

		if ($db->Exists("SELECT * FROM tbl_ipd_service_request WHERE req_status != 'cleared' AND refno='$refno'")) {
			echo "This patient still has unpaid bills and therefore cannot be released";
			return;
		}

		$sql_array = array();
		$sql_array[] = "UPDATE tbl_ipd_admission SET discharge_date='$today',discharge_type='$dis_type',discharge_reason='$dis_reason',discharge_destination='$dis_destination', discharge_instructions='$dis_instruction', discharged_by='$_SESSION[Fullname]',status='Discharged' WHERE adm_no='$fileno'";

		$sql_array[] = "UPDATE tbl_ipd_beds SET bed_status='Empty' WHERE bed_number='$IPD[bed_number]'";
		$sql_array[] = "UPDATE tbl_ipd_wards SET ward_capacity=(ward_capacity-1) WHERE ward_id='$IPD[ipd_ward]'";

		echo $db->query_sql_array($sql_array);

	}

//Bed Shift
	if (isset($_POST['BedShiftGetWardCost'])) {
		$ward_id = $_POST['ward_id'];
	    $Ward = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_ipd_wards WHERE ward_id ='$ward_id'"),MYSQLI_ASSOC);
	    echo $Ward['ward_rate_cash'];
	}

	if (isset($_POST['GetFreeBeds'])) {
		$ward_id = $_POST['ward_id'];
	    $res = mysqli_query($conn,"SELECT * FROM tbl_ipd_beds WHERE ward_id ='$ward_id' AND bed_status='Empty'");
		echo "<option value=''>Select</option>";
	    while ($Bed = mysqli_fetch_assoc($res)) {
	    	echo "<option value='$Bed[bed_number]'>$Bed[bed_number]</option>";
	    }
	}

	if (isset($_POST['ShiftBed'])) {
		$adm_no = mysqli_real_escape_string($conn,$_POST['adm_no']);
		$ward_id = mysqli_real_escape_string($conn,$_POST['ward_id']);
		$daily_bed_fee = mysqli_real_escape_string($conn,$_POST['daily_bed_fee']);
		$bed_number = mysqli_real_escape_string($conn,$_POST['bed_number']);

		$Admission = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_ipd_admission WHERE adm_no='$adm_no'"),MYSQLI_ASSOC);
		$currentBed = $Admission['bed_number'];

		//Free current bed
		if (mysqli_query($conn,"UPDATE tbl_ipd_beds SET bed_status='Empty' WHERE bed_number='$currentBed'")) {
			// Set new bed to be occupied by above refno
			if (mysqli_query($conn,"UPDATE tbl_ipd_beds SET bed_status='$Admission[refno]' WHERE bed_number='$bed_number'")) {
				//Update patient ward and bed status
				if (mysqli_query($conn,"UPDATE tbl_ipd_admission SET ipd_ward='$ward_id', daily_charge='$daily_bed_fee', bed_number='$bed_number' WHERE adm_no='$adm_no'")) {
					//Update former ward population
					if (mysqli_query($conn,"UPDATE tbl_ipd_wards SET ward_capacity=(ward_capacity-1) WHERE ward_id='$Admission[ipd_ward]'")) {
						//Update population of new ward
						if (mysqli_query($conn,"UPDATE tbl_ipd_wards SET ward_capacity=(ward_capacity+1) WHERE ward_id='$ward_id'")) {
							echo "Success";
						}else{echo "Sorry - ".mysqli_error($conn);}
					}else{echo "Sorry - ".mysqli_error($conn);}
				}else{echo "Sorry - ".mysqli_error($conn);}
			}else{echo "Sorry - ".mysqli_error($conn);}
		}else{echo "Sorry - ".mysqli_error($conn);}
	}

//MONITORS
	if (isset($_POST['GetGraphData'])) {
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$legends = array();
		$sys = array();
		$dia = array();
		$temp = array();
		$weight = array();
		$pulse = array();

		$res = $db->ReadAll("SELECT * FROM tbl_ipd_vitals WHERE ipd_fileno='$fileno'  ORDER BY ID ASC ");
		while ($test = mysqli_fetch_assoc($res)) {
			array_push($legends, $test['vitals_date']);
			array_push($sys, $test['bp_sys']);
			array_push($dia, $test['bp_dia']);
			array_push($temp, $test['temperature']);
			array_push($weight, $test['weight']);
			array_push($pulse, $test['pulse']);
		}
		$row = array(
			'legends'=>$legends,
			'Systolic'=>$sys,
			'Diastolic'=>$dia,
			'Temp'=>$temp,
			'Weight'=>$weight,
			'Pulse'=>$pulse
		);		
		echo json_encode($row);
	}
?>