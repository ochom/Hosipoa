<?php
include('../ConnectionClass.php');
include('../db_class.php');
session_start();

$db = new CRUD();

//GetMyAge
	if (isset($_POST['GetMyAge'])) {
		$dob = new DateTime(date($_POST['dob']));
		$today = new DateTime(date('Y/m/d'));
   		$agediff = $today->diff($dob);
   		$age = sprintf('%d Year(s) %d Months(s) %d Day(s)',$agediff->y,$agediff->m,$agediff->d);
		echo $age;
	}

//Get my Date of birth
	if (isset($_POST['GetMyDateOfBirth'])) {
		$myage  = mysqli_real_escape_string($conn,$_POST['myage']); 
		$dob = date('Y-m-d',strtotime('-'.$myage.' years'));
		echo $dob;
	}

	if (isset($_POST['RegisterPatient'])) {
		sleep(0);
		$fullname = mysqli_real_escape_string($conn,$_POST['fullname']);
		$id_type = mysqli_real_escape_string($conn,$_POST['id_type']);
		$idno = mysqli_real_escape_string($conn,$_POST['idno']);
		$dob = mysqli_real_escape_string($conn,$_POST['dob']);
		$sex = mysqli_real_escape_string($conn,$_POST['sex']);
		$marital_status = mysqli_real_escape_string($conn,$_POST['marital_status']);
		$image = $_POST['image'];

		$occupation = mysqli_real_escape_string($conn,$_POST['occupation']);
		
		$reg_date = date('d/m/Y H:i:s');
		$phone = mysqli_real_escape_string($conn,$_POST['phone']);
		$posta = mysqli_real_escape_string($conn,$_POST['posta']);
		$country = mysqli_real_escape_string($conn,$_POST['country']);
		$county = mysqli_real_escape_string($conn,$_POST['county']);
		$sub_county = mysqli_real_escape_string($conn,$_POST['sub_county']);
		$ward = mysqli_real_escape_string($conn,$_POST['ward']);
		$kin_name = mysqli_real_escape_string($conn,$_POST['kin_name']);
		$kin_phone = mysqli_real_escape_string($conn,$_POST['kin_phone']);
		$kin_relationship = mysqli_real_escape_string($conn,$_POST['kin_relationship']);
		$kin_id = mysqli_real_escape_string($conn,$_POST['kin_id']);
		$ins_status = mysqli_real_escape_string($conn,$_POST['ins_status']);
		$ins_company = mysqli_real_escape_string($conn,$_POST['ins_company']);
		$ins_card_no = mysqli_real_escape_string($conn,$_POST['ins_card_no']);

		if ($db->Exists("SELECT * From tbl_patient Where idno='$idno' ")) {
			echo "Patient already in the system"; return;
		}
		$sql = "INSERT INTO tbl_patient (fullname,id_type, idno, dob, sex, reg_date, marital_status, occupation, phone, posta, country, county, sub_county, ward, kin_name, kin_phone, kin_relationship, kin_id, ins_status, ins_company, ins_card_no) VALUES ('$fullname','$id_type', '$idno', '$dob', '$sex', '$reg_date', '$marital_status','$occupation','$phone', '$posta', '$country', '$county', '$sub_county', '$ward', '$kin_name', '$kin_phone', '$kin_relationship', '$kin_id', '$ins_status', '$ins_company', '$ins_card_no')";			
		
		$db->Query($sql);

		$refno = $db->ReadOne("SELECT refno From tbl_patient  ORDER BY refno DESC LIMIT 1")['refno'];
		if ($image !=='') {CopyImageToDirectory($db,$image,$refno);}

		$sql = "INSERT INTO tbl_patient_insurance_schemes (refno,company_id,card_no) VALUES ('$refno','$ins_company','$ins_card_no')";
		echo $db->Query($sql);
		
	}

	if (isset($_POST['UpdatePatient'])) {
		sleep(0);
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$fullname = mysqli_real_escape_string($conn,$_POST['fullname']);
		$id_type = mysqli_real_escape_string($conn,$_POST['id_type']);
		$idno = mysqli_real_escape_string($conn,$_POST['idno']);
		$dob = mysqli_real_escape_string($conn,$_POST['dob']);
		$sex = mysqli_real_escape_string($conn,$_POST['sex']);
		$marital_status = mysqli_real_escape_string($conn,$_POST['marital_status']);

		$image = $_POST['image'];

		$occupation = mysqli_real_escape_string($conn,$_POST['occupation']);
		$phone = mysqli_real_escape_string($conn,$_POST['phone']);
		$posta = mysqli_real_escape_string($conn,$_POST['posta']);
		$country = mysqli_real_escape_string($conn,$_POST['country']);
		$county = mysqli_real_escape_string($conn,$_POST['county']);
		$sub_county = mysqli_real_escape_string($conn,$_POST['sub_county']);
		$ward = mysqli_real_escape_string($conn,$_POST['ward']);
		$kin_name = mysqli_real_escape_string($conn,$_POST['kin_name']);
		$kin_phone = mysqli_real_escape_string($conn,$_POST['kin_phone']);
		$kin_relationship = mysqli_real_escape_string($conn,$_POST['kin_relationship']);
		$kin_id = mysqli_real_escape_string($conn,$_POST['kin_id']);
		$ins_status = mysqli_real_escape_string($conn,$_POST['ins_status']);

		
		echo $db->Query("UPDATE tbl_patient SET fullname='$fullname', id_type='$id_type', idno='$idno', dob='$dob', sex='$sex', marital_status='$marital_status', occupation='$occupation', phone='$phone', posta='$posta', country='$country', county='$county', sub_county='$sub_county', ward='$ward', kin_name='$kin_name', kin_phone='$kin_phone', kin_relationship='$kin_relationship', kin_id='$kin_id', ins_status='$ins_status' WHERE refno='$refno'");

		if ($image !=='') {
			CopyImageToDirectory($db,$image,$refno);
		}
		
	}

	function CopyImageToDirectory($db,$image,$refno){
	    $image = substr($image,strpos($image,",")+1);
	    $image = base64_decode($image);

	    $file = "../images/patient_profiles/pp_".$refno.".png";
	    file_put_contents($file, $image);
	    echo "File created";

	    echo $db->Query("UPDATE tbl_patient SET image='../images/patient_profiles/pp_".$refno.".png.' WHERE refno='$refno'");
	}


//SEARCH PATIENTS
	if (isset($_POST['SearchPatient'])) {
		$searchby  = mysqli_real_escape_string($conn,$_POST['searchby']);
		$searchVal  = mysqli_real_escape_string($conn,$_POST['searchVal']);
		$sql=null;
		switch ($searchby) {
			case 'fullname':
				$sql = "SELECT * FROM tbl_patient Where fullname LIKE '%$searchVal%' LIMIT 20";
				break;
			case 'refno':
				$sql = "SELECT * FROM tbl_patient Where refno LIKE '%$searchVal%' LIMIT 20";
				break;
			case 'idno':
				$sql = "SELECT * FROM tbl_patient Where idno LIKE '%$searchVal%' LIMIT 20";
				break;
			case 'ins_card_no':
				$sql = "SELECT * FROM tbl_patient Where ins_card_no LIKE '%$searchVal%' LIMIT 20";
				break;
			case '':
				$sql = "SELECT * FROM tbl_patient Where (fullname LIKE '%$searchVal%' OR refno LIKE '%$searchVal%' OR idno LIKE '%$searchVal%' OR ins_card_no LIKE '%$searchVal%' ) LIMIT 20";
				break;
		}	
		
		$rows = $db->ReadArray($sql);
		foreach ($rows as $row):?>
		<tr>
			<td><?= $row['refno']?></td>
			<td><?= $row['fullname']?></td>
			<td><?= $row['idno']?></td>
			<td>
				<button class="btn btn-primary btn-sm" onclick="SelectCheme('<?= $row['refno']?>','<?= $row['ins_status']?>')"><i class="oi oi-file"></i> Create Health File</button>
				<a href="Edit Patient.php?serveRef=<?= $row['refno']?>" class="btn btn-success btn-sm"><i class="oi oi-pencil"></i> Edit </a>
				<button onclick="AddScheme('<?= $row['refno']?>')" class="btn btn-primary btn-sm"><i class="oi oi-plus"></i> Add Scheme</a>
			</td>
		</tr>
	<?php endforeach;

	}

	//charge patient and Queue
	if (isset($_POST['CreateHealthFile'])) {
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$treatement_scheme = mysqli_real_escape_string($conn,$_POST['treatement_scheme']);
		$today = date('d/m/Y H:i:s');
		$req_name = 'Doctor Consultation';
		$req_department = 'OPD';
		$Consultation = $db->ReadOne("SELECT* FROM tbl_static_services");

		$cost = ($treatement_scheme=='Cash')? $Consultation['opd_doc_cash']:$Consultation['opd_doc_cop'];	

		if ($db->Exists("SELECT * FROM tbl_opd_visits WHERE patient_id='$refno' AND file_status='Opened'")) {
			echo "This patient has active file opened. You cannot create a new health file."; return;
		}

		if ($db->Exists("SELECT * FROM tbl_ipd_admission WHERE refno='$refno' AND status='Active'")) {
			echo "This patient has active IPD file. You cannot create a new health file."; return;
		}

		echo $db->Query("INSERT INTO tbl_opd_visits (patient_id,visit_date,treatement_scheme)VALUES('$refno','$today','$treatement_scheme')");
		$AI = $db->ReadOne("SELECT last_insert_id() AS LAST_ID FROM tbl_opd_visits");

		QueueToTriage($AI['LAST_ID'],$refno,$db);

		echo  $db->Query("INSERT INTO tbl_opd_service_request(fileno,refno, req_date, req_name, req_department, req_cost,payment_type,req_from) VALUES ('$AI[LAST_ID]','$refno','$today','$req_name','$req_department','$cost','$treatement_scheme','Records')");
	}

	function QueueToTriage($fileno,$refno,$db){
		$today = date('d/m/Y H:i:s');
		$Exists = $db->CountRows("SELECT * FROM tbl_triage_queue WHERE refno = '$refno'");
		if ($Exists==0) {
			$db->Query("INSERT INTO tbl_triage_queue (refno,q_date,fileno) VALUES ('$refno','$today','$fileno') ");
		}
	}

	if (isset($_POST['GetMySchemes'])) {
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		if (!$db->Exists("SELECT * FROM tbl_patient_insurance_schemes WHERE refno = '$refno'")) {
			echo "There is no insurance scheme added to the patient";
		}
		$rows = $db->ReadArray("SELECT * FROM tbl_patient_insurance_schemes WHERE refno = '$refno'");
		$i=0;
		foreach($rows as $row): $i++;
			$company_name = $db->ReadOne("SELECT * FROM tbl_ins_companies WHERE company_id = '$row[company_id]'")['company_name'];
			?>
			<tr>
				<td><?= $i."."?></td>
				<td><?= $company_name?></td>
				<td><?= $row['card_no']?></td>
			</tr>
		<?php
		endforeach;
	}

	if (isset($_POST['SaveScheme'])) {
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$company_id = mysqli_real_escape_string($conn,$_POST['company_id']);
		$card_no = mysqli_real_escape_string($conn,$_POST['card_no']);

		if ($db->Exists("SELECT * FROM tbl_patient_insurance_schemes WHERE (refno = '$refno' AND company_id='$company_id')")) {
			echo "This scheme has already been added to the client schemes list"; return;
		}

		echo $db->Query("INSERT INTO tbl_patient_insurance_schemes (refno,company_id,card_no) VALUES ('$refno','$company_id','$card_no')");
	}
?>