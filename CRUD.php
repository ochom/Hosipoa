<?php
session_start();
include('ConnectionClass.php');
include('db_class.php');
$db = new CRUD();

$enc_dec_password = '89897575';
//LOGIN
	if (isset($_POST['Login'])) {
		$username=mysqli_real_escape_string($conn,$_POST['username']);
		$password=mysqli_real_escape_string($conn,$_POST['password']);

		sleep(0);
		$rowdata = $db->ReadOne("SELECT * FROM tbl_system_users WHERE username = '$username'");
		$Username = $rowdata['username'];
		$Password = $db->Decrypt($rowdata['password']);
		$User_level = $rowdata['user_level'];
		$Fullname = $rowdata['full_name'];
		$User_group = $rowdata['user_group'];
		//Get group privileges
		$GroupPrivileges = $db->ReadOne("SELECT * FROM tbl_user_groups WHERE group_name='$User_group'");
		
		$totalrows = $db->CountRows("SELECT * FROM tbl_system_users WHERE username = '$username'");;
		if ($totalrows == 0 ) {echo "Username not found"; return;}
		if($Username != $username){echo "Wrong Username"; return;}
		if($Password != $password){echo "Wrong password"; return;}
		if ($Username == $username && $Password==$password) {	
			if($rowdata['status']=='Deactivated'){echo "This account has been blocked. Contact System Admin for more information"; return;}	
			//otherwise allow login
			$_SESSION['Fullname'] = $Fullname;
			$_SESSION['Username'] = $Username;
			$_SESSION['User_level'] = $User_level;
			$_SESSION['GroupPrivileges'] = $GroupPrivileges;
			$_SESSION['Login_Time'] = date('d/m/Y H:i:s');
			echo "success";
			return;
		}else{
			echo "Wrong Credentials";
		}
	}

	if (isset($_POST['GetGraphData'])) {
		//Monthly
		$monthly_legends = array();
		$monthly_opd = array();
		$monthly_ipd = array();

		//Yearly
		$yearly_legends = array();
		$yearly_opd = array();
		$yearly_ipd = array();



		$Day = date('d/m/Y');
		$Month = substr($Day, 3,10);
		$Year = substr($Day, 6,10);
		
		$return_data = array();
		$OpdToday = $db->ReadOne("SELECT count(*) AS Total FROM tbl_opd_visits WHERE substring(visit_date,1,10)='$Day'")['Total'];
		$IPDToday = $db->ReadOne("SELECT count(*) AS Total FROM tbl_ipd_admission WHERE substring(adm_date,1,10)='$Day'")['Total'];

		//monthlvisits

		$numberOfDaysthisMonth = date('t');
		$date_zeros = 0;
		for ($i=1; $i < $numberOfDaysthisMonth+1; $i++) { 
			$date_two_date = (strlen($i)<2)?"0".$i:$i;
			$superscript = ($i==1 || $i==21 || $i==31)?"st":(($i==2 || $i==22)?"nd":(($i==3 || $i==23)?"rd":"th"));
			array_push($monthly_legends, $date_two_date.$superscript);//create monthly legends
			$full_date = $date_two_date.date('/m/Y');//add zeros read from db
			$total = $db->ReadOne("SELECT count(*) AS Total FROM tbl_opd_visits WHERE substring(visit_date,1,10)='$full_date'")['Total'];
			array_push($monthly_opd, (int)$total);//create monthly opd data
			$total = $db->ReadOne("SELECT count(*) AS Total FROM tbl_ipd_admission WHERE substring(adm_date,1,10)='$full_date'")['Total'];
			array_push($monthly_ipd, (int)$total);//create monthly ipd data
			
		}

		//Yearlyvisits
		$yearly_legends = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
		for ($i=1; $i < 13; $i++) { 
			$date_two_month = (strlen($i)<2)?"0".$i:$i;
			$month_year = $date_two_month.date('/Y');
			$total = $db->ReadOne("SELECT count(*) AS Total FROM tbl_opd_visits WHERE substring(visit_date,4,7)='$month_year'")['Total'];
			array_push($yearly_opd, (int)$total);//create yearly opd data
			$total = $db->ReadOne("SELECT count(*) AS Total FROM tbl_ipd_admission WHERE substring(adm_date,4,7)='$month_year'")['Total'];
			array_push($yearly_ipd, (int)$total);//create yearly ipd data
		}

		
		$return_data = array(
			"daily_data" =>[(int)$OpdToday,(int)$IPDToday],
			"monthly_legends" => $monthly_legends,
			"monthly_opd" => $monthly_opd,
			"monthly_ipd" => $monthly_ipd,
			"yearly_legends" => $yearly_legends,
			"yearly_opd" => $yearly_opd,
			"yearly_ipd" => $yearly_ipd,
		);



		echo json_encode($return_data);
	}
?>