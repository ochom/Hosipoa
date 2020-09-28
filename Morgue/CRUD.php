<?php
include('../ConnectionClass.php');
session_start();

//Patient Admission
	if (isset($_POST['GetMorgueCost'])) {
	    $morgue_id = $_POST['morgue_id'];
	    $Morgue = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_morgues WHERE morgue_id ='$morgue_id'"),MYSQLI_ASSOC);
	     echo $Morgue['morgue_admission_fee'].";".$Morgue['morgue_daily_fee'];
	  }


	if (isset($_POST['AdmitBody'])) { 
		$adm_date = date('d/m/Y H:i:s');
		$body_name = mysqli_real_escape_string($conn,$_POST['body_name']);
        $body_from = mysqli_real_escape_string($conn,$_POST['body_from']);
        $country = mysqli_real_escape_string($conn,$_POST['country']);
        $county = mysqli_real_escape_string($conn,$_POST['county']);
        $subcounty_ward = mysqli_real_escape_string($conn,$_POST['subcounty_ward']);
        $kin_name = mysqli_real_escape_string($conn,$_POST['kin_name']);
        $kin_idno = mysqli_real_escape_string($conn,$_POST['kin_idno']);
        $kin_phone = mysqli_real_escape_string($conn,$_POST['kin_phone']);
        $kin_relationship = mysqli_real_escape_string($conn,$_POST['kin_relationship']);
        $morgue_id = mysqli_real_escape_string($conn,$_POST['morgue_id']);	
        $admit_fee = mysqli_real_escape_string($conn,$_POST['admit_fee']);
        $daily_charge = mysqli_real_escape_string($conn,$_POST['daily_charge']);
        $admitted_by = mysqli_real_escape_string($conn,$_POST['admitted_by']);

		$sql = "INSERT INTO tbl_morgue_admission 
							(adm_date, body_name, body_from, country, county, subcounty_ward, kin_name, kin_idno, kin_phone, kin_relationship, morgue_id, admit_fee, daily_charge, admitted_by) 
							VALUES ('$adm_date', '$body_name', '$body_from', '$country', '$county', '$subcounty_ward', '$kin_name', '$kin_idno', '$kin_phone', '$kin_relationship', '$morgue_id', '$admit_fee', '$daily_charge', '$admitted_by')";
		if (mysqli_query($conn,$sql)) {
			//Get the admission number of the just admitted patient
			$BodyDetails = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_morgue_admission ORDER BY adm_no DESC LIMIT 1"),MYSQLI_ASSOC);
			$refno  = $BodyDetails['adm_no'];

			$query = "INSERT INTO tbl_morgue_bills (`adm_no`, `bill_date`, `bill_name`, `bill_amount`, `bill_status`) VALUES ('$refno','$adm_date','Admission Fee','$admit_fee','granted')";
			if (mysqli_query($conn,$query)) {
				if (mysqli_query($conn,"UPDATE tbl_morgues SET morgue_capacity = (morgue_capacity+1) WHERE morgue_id='$morgue_id'")) {
					echo "Success";
				}else{
					echo "Sorry - ".mysqli_error($conn);
				}				
			}else{
				echo "Sorry - ".mysqli_error($conn);
			}
		}else{
			echo "Sorry - ".mysqli_error($conn);
		}
	}

//GetAll BOdies
	if (isset($_POST['GetBodies'])) {
		$rs = mysqli_query($conn,"SELECT * FROM tbl_morgue_admission ORDER BY status ASC");
		while ($Body = mysqli_fetch_assoc($rs)) {
			$status = $Body['status'];
			?>
			<tr>
				<td><?= $Body['adm_no']?></td>
				<td><?= $Body['body_name']?></td>
				<td><?= $Body['adm_date']?></td>
				<td><?= $Body['country']?></td>
				<td><?= $Body['county']?></td>
				<td><?= $Body['status']?></td>
				<td>
				<?php
					if ($status=='Active') {
						?>
						<button class="btn btn-outline-success btn-sm" onclick="ReleaseBody('<?= $Body['adm_no']?>')"><i class="oi oi-check"></i>  Release</button>
						<?php
					}
				?>
					<button onclick="var w = window.open('Body Details.php?serveRef=<?= $Body['adm_no']?>'); w.focus();" class="btn btn-outline-primary btn-sm"><i class="oi oi-info"></i> More Info</button>
				</td>
			</tr>
			<?php
		}
	}

//FilterPatientList
	if (isset($_POST['FilterBodies'])) {
		$searchby = mysqli_real_escape_string($conn,$_POST['searchBy']);
		$searchval = mysqli_real_escape_string($conn,$_POST['searchVal']);
		switch ($searchby) {
			case 'body_name':
				$sql = "SELECT * FROM tbl_morgue_admission WHERE body_name LIKE '%$searchval%' ORDER BY status ASC";
				break;
			case 'adm_no':
				$sql = "SELECT * FROM tbl_morgue_admission WHERE adm_no LIKE '%$searchval%' ORDER BY status ASC";
				break;
			default:
				$sql = "SELECT * FROM tbl_morgue_admission ORDER BY status ASC";
				break;
		}
		$res = mysqli_query($conn,$sql);
        while ($Body = mysqli_fetch_assoc($res)) {
			?>
			<tr>
				<td><?= $Body['adm_no']?></td>
				<td><?= $Body['body_name']?></td>
				<td><?= $Body['adm_date']?></td>
				<td><?= $Body['country']?></td>
				<td><?= $Body['county']?></td>
				<td><?= $Body['status']?></td>
				<td>
					<button class="btn btn-outline-success btn-sm" onclick="ReleaseBody('<?= $Body['adm_no']?>')"><i class="oi oi-check"></i>  Release</button>
					<button onclick="var w = window.open('Body Details.php?serveRef=<?= $Body['adm_no']?>'); w.focus();" class="btn btn-outline-primary btn-sm"><i class="oi oi-info"></i> More Info</button>
				</td>
			</tr>
			<?php
		}
	}

//Release Body
	if (isset($_POST['ReleaseBody'])) {
		$adm_no = mysqli_real_escape_string($conn,$_POST['adm_no']);
		$today = date('d/m/Y H:i:s');
		$Bills = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM tbl_morgue_bills WHERE bill_status != 'cleared' AND adm_no='$adm_no' "));
		if ($Bills > 0) {
			echo "This body still has unpaid bills and therefore cannot be released";
			return;
		}else{
			if (mysqli_query($conn,"UPDATE tbl_morgue_admission SET discharge_date='$today', status = 'Discharged' WHERE adm_no='$adm_no'")) {
				echo "Body Succesfully Released";
			}else{
				echo "Sorry - ".mysqli_error($conn);
			}
		}

	}
?>