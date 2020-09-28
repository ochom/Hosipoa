<?php
include('../ConnectionClass.php');
include('../db_class.php');
$db = new CRUD();

//REGISTER HOSPITAL
	if (isset($_POST['RegisterHospital'])) {
		sleep(0);
		$hospital_name=mysqli_real_escape_string($conn,$_POST['hospital_name']);
		$mfl_code=mysqli_real_escape_string($conn,$_POST['mfl_code']);
		$postal_address=mysqli_real_escape_string($conn,$_POST['postal_address']);
		$physical_address=mysqli_real_escape_string($conn,$_POST['physical_address']);
		$email=mysqli_real_escape_string($conn,$_POST['email']);
		$phone=mysqli_real_escape_string($conn,$_POST['phone']);
		$financial_month = mysqli_real_escape_string($conn,$_POST['financial_month']);
		$financial_year_from=mysqli_real_escape_string($conn,$_POST['financial_year_from']);
        $financial_year_to=mysqli_real_escape_string($conn,$_POST['financial_year_to']);

		$result = $db->CountRows("SELECT * FROM tbl_hospital");
		if ($result==0) {
			echo $db->Query("INSERT INTO tbl_hospital (hospital_name,mfl_code,postal_address,physical_address,email,phone,financial_month,financial_year_from,financial_year_to) VALUES('$hospital_name','$mfl_code','$postal_address','$physical_address','$email','$phone','$financial_month','$financial_year_from','$financial_year_to')");
		}else{
			echo $db->Query(" UPDATE tbl_hospital SET hospital_name='$hospital_name',mfl_code='$mfl_code',postal_address='$postal_address',physical_address='$physical_address',email='$email',phone='$phone',financial_month='$financial_month',financial_year_from='$financial_year_from',financial_year_to='$financial_year_to'");
		}
	}

//Companies
	if (isset($_POST['GetCompanies'])) {
		$result = $db->ReadAll("SELECT * FROM tbl_ins_companies ORDER BY company_name ASC");
		while ($Company = mysqli_fetch_assoc($result)) {
			?>
			<tr>
				<td><?= $Company['company_id']?></td>
				<td><?= $Company['company_name']?></td>
				<td><?= $Company['company_email']?></td>
				<td><?= $Company['company_phone']?></td>
				<td><?= $Company['credit_limit']?></td>
				<td><?= $Company['status']?></td>
				<td>
					<button onclick="EditCompany($(this).parents('tr'))" class="btn btn-sm btn-outline-success"> <i class="oi oi-pencil"></i> Edit</button>
					<?php 
						if ($Company['status']=='Deleted' || $Company['status']=='Suspended') {
							?>
							<button onclick="ActivateCompany('<?= $Company['company_id']?>')" class="btn btn-sm btn-outline-primary"> <i class="oi oi-trash"></i> Activate</button>
							<?php
						}else{
							?>
							<button onclick="DeleteCompany('<?= $Company['company_id']?>')" class="btn btn-sm btn-outline-danger"> <i class="oi oi-trash"></i> Delete</button>
							<?php
						}
					?>
				</td>
			</tr>
			<?php
		}
	}

	if (isset($_POST['SaveCompany'])) {
		$company_name=mysqli_real_escape_string($conn,$_POST['company_name']);
		$company_email=mysqli_real_escape_string($conn,$_POST['company_email']);
		$company_phone=mysqli_real_escape_string($conn,$_POST['company_phone']);
		$credit_limit=mysqli_real_escape_string($conn,$_POST['credit_limit']);
		$status=mysqli_real_escape_string($conn,$_POST['status']);

		echo $db->Query("INSERT INTO tbl_ins_companies (company_name,company_email,company_phone,credit_limit,status) VALUES ('$company_name','$company_email','$company_phone','$credit_limit','$status')");
	}

	if (isset($_POST['UpdateCompany'])) {
		$company_id=mysqli_real_escape_string($conn,$_POST['company_id']);
		$company_name=mysqli_real_escape_string($conn,$_POST['company_name']);
		$company_email=mysqli_real_escape_string($conn,$_POST['company_email']);
		$company_phone=mysqli_real_escape_string($conn,$_POST['company_phone']);
		$credit_limit=mysqli_real_escape_string($conn,$_POST['credit_limit']);
		$status=mysqli_real_escape_string($conn,$_POST['status']);

		echo $db->Query("UPDATE tbl_ins_companies SET company_name= '$company_name', company_email= '$company_email',company_phone= '$company_phone', credit_limit= '$credit_limit',status= '$status' WHERE company_id= '$company_id'");
	}

	if (isset($_POST['DeleteCompany'])) {
		$company_id = mysqli_real_escape_string($conn,$_POST['company_id']);
		echo $db->Query("UPDATE tbl_ins_companies SET status= 'Deleted' Where company_id='$company_id'");
	}
	if (isset($_POST['ActivateCompany'])) {
		$company_id = mysqli_real_escape_string($conn,$_POST['company_id']);
		echo $db->Query("UPDATE tbl_ins_companies SET status= 'Active' Where company_id='$company_id'");
	}

//User Groups
	if (isset($_POST['AddGroup'])) {
		$group_name = mysqli_real_escape_string($conn,$_POST['group_name']);
		$Exists = $db->CountRows("SELECT * FROM tbl_user_groups WHERE group_name='$group_name'");
		if ($Exists > 0) {
			echo "This Group already exists";
		}else{
			echo $db->Query("INSERT INTO tbl_user_groups (group_name) VALUES ('$group_name')"); 
		}
	}
	//User Privileges
	if (isset($_POST['ModifyPrivilages'])) {
    	$group_name = mysqli_real_escape_string($conn,$_POST['group_name']);
    	$system_setting_priv = mysqli_real_escape_string($conn,$_POST['system_setting_priv']);
    	$revenue_cash_collection_priv = mysqli_real_escape_string($conn,$_POST['revenue_cash_collection_priv']);
    	$revenue_billing_priv = mysqli_real_escape_string($conn,$_POST['revenue_billing_priv']);
    	$procurement_priv = mysqli_real_escape_string($conn,$_POST['procurement_priv']);
    	$records_priv = mysqli_real_escape_string($conn,$_POST['records_priv']);
    	$opd_triage_priv = mysqli_real_escape_string($conn,$_POST['opd_triage_priv']);
    	$opd_treatment_priv = mysqli_real_escape_string($conn,$_POST['opd_treatment_priv']);
    	$maternity_priv = mysqli_real_escape_string($conn,$_POST['maternity_priv']);
    	$pharmacy_priv = mysqli_real_escape_string($conn,$_POST['pharmacy_priv']);
    	$laboratory_priv = mysqli_real_escape_string($conn,$_POST['laboratory_priv']);
    	$radiology_priv = mysqli_real_escape_string($conn,$_POST['radiology_priv']);
    	$morgue_priv = mysqli_real_escape_string($conn,$_POST['morgue_priv']);
    	$orders_stock_priv = mysqli_real_escape_string($conn,$_POST['orders_stock_priv']);
    	$ipd_treatment_priv = mysqli_real_escape_string($conn,$_POST['ipd_treatment_priv']);
    	$ipd_general_service_priv = mysqli_real_escape_string($conn,$_POST['ipd_general_service_priv']);
    	$ipd_stock_return_priv = mysqli_real_escape_string($conn,$_POST['ipd_stock_return_priv']);
    	$ipd_patient_discharge_priv = mysqli_real_escape_string($conn,$_POST['ipd_patient_discharge_priv']);
    	$eye_priv = mysqli_real_escape_string($conn,$_POST['eye_priv']);
    	$dental_priv = mysqli_real_escape_string($conn,$_POST['dental_priv']);

    	$sql = "UPDATE tbl_user_groups SET system_setting_priv = '$system_setting_priv',
					revenue_cash_collection_priv = '$revenue_cash_collection_priv',
					revenue_billing_priv = '$revenue_billing_priv',
					procurement_priv = '$procurement_priv',
					records_priv = '$records_priv',
					opd_triage_priv = '$opd_triage_priv',
					opd_treatment_priv = '$opd_treatment_priv',
					maternity_priv = '$maternity_priv',
					pharmacy_priv = '$pharmacy_priv',
					laboratory_priv = '$laboratory_priv',
					radiology_priv = '$radiology_priv',
					morgue_priv = '$morgue_priv',
					orders_stock_priv = '$orders_stock_priv',
					ipd_treatment_priv = '$ipd_treatment_priv',
					ipd_general_service_priv = '$ipd_general_service_priv',
					ipd_stock_return_priv = '$ipd_stock_return_priv',
					ipd_patient_discharge_priv = '$ipd_patient_discharge_priv',
					eye_priv = '$eye_priv',
					dental_priv = '$dental_priv' 
					WHERE group_name='$group_name'";

		echo $db->Query($sql);
	}
	//users
	if (isset($_POST['GetUsers'])) {
        $result = $db->ReadAll("SELECT * FROM tbl_system_users");
        while ($User = mysqli_fetch_assoc($result)) {
          ?>
            <tr>
              <td><?= $User['reg_no']?></td>
              <td ><?= $User['full_name']?></td>                  
              <td><?= $User['user_level']?></td>
              <td><?= $User['user_group']?></td>
              <td><?= $User['phone']?></td>
              <td><?= $User['status']?></td>
              <td>
                <a href="User Registration.php?serveRef=<?= $User['reg_no']?>" class="btn btn-outline-success btn-sm">
                  <i class="oi oi-pencil"></i> Edit
                </a>
                <a href="User Groups.php?user-group=<?= $User['user_group']?>" class="btn btn-outline-primary btn-sm">
                  <i class="oi oi-cog"></i> Group Privileges
                </a>
                <?php 
					if ($User['status']=='Deactivated') {
						?>
						<button onclick="ActivateUser('<?= $User['reg_no']?>')" class="btn btn-sm btn-outline-success"> <i class="oi oi-lock-unlocked"></i> Activate</button>
						<?php
					}else{
						?>
						<button onclick="DeleteUser('<?= $User['reg_no']?>')" class="btn btn-sm btn-outline-danger"> <i class="oi oi-lock-locked"></i> Deactivate</button>
						<?php
					}
				?>
              </td>
            </tr>
          <?php
        }
	}


	if (isset($_POST['DeleteUser'])) {
		$reg_no = mysqli_real_escape_string($conn,$_POST['reg_no']);
		echo $db->Query("UPDATE tbl_system_users SET status= 'Deactivated' Where reg_no='$reg_no'");
	}

	if (isset($_POST['ActivateUser'])) {
		$reg_no = mysqli_real_escape_string($conn,$_POST['reg_no']);
		echo $db->Query("UPDATE tbl_system_users SET status= 'Active' Where reg_no='$reg_no'");
	}

//User Registration
	if (isset($_POST['RegisterUser'])) {
		sleep(0);
		$enc_dec_password = '89897575';
		$regno = mysqli_real_escape_string($conn,$_POST['regno']);
		$fullname = mysqli_real_escape_string($conn,$_POST['fullname']); 
		$username = mysqli_real_escape_string($conn,$_POST['username']); 
		$email = mysqli_real_escape_string($conn,$_POST['email']); 
		$phone = mysqli_real_escape_string($conn,$_POST['phone']); 
		$idno = mysqli_real_escape_string($conn,$_POST['idno']); 
		$user_group = mysqli_real_escape_string($conn,$_POST['user_group']);
		$password = $db->Encrypt('1234');
		$count = $db->CountRows("SELECT * FROM tbl_system_users WHERE reg_no='$regno' OR username='$username'");
		if ($count==0) {
			echo $db->Query("INSERT INTO tbl_system_users (full_name,username,password,idno,phone,email,user_group) VALUES ('$fullname','$username','$password','$idno','$phone','$email','$user_group')");
		}else{
			echo $db->Query("UPDATE tbl_system_users SET user_group = '$user_group' WHERE reg_no = '$regno'");
		}
	}

?>
