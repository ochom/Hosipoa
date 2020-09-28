<?php
include('../ConnectionClass.php');
include('../db_class.php');
session_start();

$db = new CRUD();

if (!(isset($_SESSION['Username']))) {
  header("refresh:0, url=../index.php");
  return;
}
//Session Values
$Username = $_SESSION['Username'];
$Fullname = $_SESSION['Fullname'];
$User_level = $_SESSION['User_level'];
$GroupPrivileges = $_SESSION['GroupPrivileges'];

//Deny permissions
if (!($User_level=='admin' || $GroupPrivileges['records_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
}
include '../ConnectionClass.php';
	$refno = $_GET['serveRef'];
	$Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$refno'"); 
    $age = $db->getPatientAge($Patient['dob']);

?>
<!DOCTYPE html>
<html>
<head>
  <!--Links-->
  <?php 
    include('../sub_links.php');
  ?>
  <!--//Links-->
  <style type="text/css">   
    .box2{
      background-color: #f80; height: 5px;
      animation: bar-enlarge 2s linear infinite;
    }
    @keyframes bar-enlarge{
      0%{
        width: 30%;
      }
      30%{
        width: 60%; margin-left: 25%;
      }
      100%{
        width: 30%; margin-left: 100%;
      }
    }
  </style>
  <style type="text/css">
  	.title{
  		padding: 5px 20px; border-radius: 5px; color: #fff;
  	}
  	sup{
  		color: #f00;
  	}
  </style>
</head>
<body>
<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <?php
      include('sidebar.php');
    ?>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
      <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <span class="navbar-toggler-icon" id="menu-toggle"></span>	
        <div class="navbar-header">
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Records</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">      		
      		<b><i class="oi oi-pencil"></i> Edit Registration Details</b>
      	</div>
      	<button class="btn btn-success" style="position: fixed; bottom: 80px; right: 20px; width: 150px; z-index: 1" onclick="UpdatePatient()"><i class="oi oi-check"></i> Update</button>
      	<a href="Patient Search.php"  class="btn btn-danger" style="position: fixed; bottom: 20px; right: 20px; width: 150px; z-index: 1"><i class="oi oi-x"></i> Close</a>
        <div class="page_scroller">
<!--Personal Details-->
			<div class="title bg-dark" >
				Personal Details
			</div>
			<div class="row">
				<div class="col-md-12 col-lg-8">
				<div class="row">
					<div class="form-group col-sm-12 col-md-8">
						<label>Full name<sup>*</sup></label>
						<input class="form-control form-control-sm" id="fullname" placeholder="Full name">
					</div>
					<div class="form-group col-sm-12 col-md-4 col-lg-4">
						<label>Sex<sup>*</sup></label>
						<select class="form-control form-control-sm" id="sex">
							<option value="">Select</option>
							<option value="Male">Male</option>
							<option value="Female">Female</option>
							<option value="Intersex">Intersex</option>
						</select>
					</div>	
					<div class="form-group col-sm-12 col-md-4 col-lg-4">
						<label>Date of Birth<sup>*</sup></label>
						<input class="form-control form-control-sm" type="date"  id="dob" onfocus="SetToDate()" onchange="GetMyAge($(this).val());">
					</div>
					<div class="form-group col-sm-12 col-md-4 col-lg-4">
						<label>Age<sup>*</sup></label>
						<input class="form-control form-control-sm" id="age" placeholder="Years" onkeyup="GetMyDateOfBirth($(this).val())">
					</div>				
					<div class="form-group col-sm-12 col-md-4 col-lg-4">
						<label>Occupation<sup>*</sup></label>
						<input class="form-control form-control-sm" id="occupation">
					</div>
					<div class="form-group col-sm-12 col-md-4 col-lg-4">
						<label>ID Type<sup>*</sup></label>
						<select class="form-control form-control-sm" id="id_type">
							<option value="">Select</option>
							<option value="Birth Certificate">Birth Certificate</option>
							<option value="National ID">National ID</option>
							<option value="Alien ID">Alien ID</option>
							<option value="Millitary ID">Millitary ID</option>
							<option value="Passport">Passport</option>
						</select>
					</div>	
					<div class="form-group col-sm-12 col-md-4 col-lg-4">
						<label>ID NO<sup>*</sup></label>
						<input class="form-control form-control-sm" id="idno" placeholder="Identification Number">
					</div>
					<div class="form-group col-sm-12 col-md-4 col-lg-4">
						<label>Marital Status<sup>*</sup></label>
						<select class="form-control form-control-sm" id="marital_status">
							<option value="">Select</option>
							<option value="Child"> Child</option>
							<option value="Single"> Single</option>
							<option value="Married"> Married</option>
							<option value="Separated"> Separated</option>
							<option value="Divorced"> Divorced</option>
						</select>
					</div>
				</div>
				</div>
				<div class="col-md-6 col-lg-3">
					<canvas id="image" style=" background: url('../images/passport.png'); background-size: 100% 100%; width: 150px; height: 200px; margin: 10px; border: 1px solid #ccc; border-radius: 3px;"></canvas>
					<input type="file" accept="image/*" onchange="CreateImage(window.URL.createObjectURL(this.files[0]))">
				</div>
			</div>
<!--Contact Information-->
			<div class="title bg-dark">
				Contact Information
			</div>
			<div class="row">
				<div class="form-group col-sm-12 col-md-4 col-lg-4">
					<label>Phone Number<sup>*</sup></label>
					<input class="form-control form-control-sm" id="phone" placeholder="Phone Number">
				</div>
				<div class="form-group col-sm-12 col-md-4 col-lg-4">
					<label>Postal Address</label>
					<input class="form-control form-control-sm" id="posta" placeholder="Postal Address">
				</div>
				<div class="form-group col-sm-12 col-md-4 col-lg-4">
					<label>Country<sup>*</sup></label>
					<input class="form-control form-control-sm" id="country" value="Kenya">
				</div>
				<div class="form-group col-sm-12 col-md-4 col-lg-4">
					<label>County</label>
					<input class="form-control form-control-sm" id="county" placeholder="County">
				</div>
				<div class="form-group col-sm-12 col-md-4 col-lg-4">
					<label>Sub-county</label>
					<input class="form-control form-control-sm" id="sub_county" placeholder="Sub-county">
				</div>
				<div class="form-group col-sm-12 col-md-4 col-lg-4">
					<label>Ward/Village/Estate/Landmark</label>
					<input class="form-control form-control-sm" id="ward" placeholder="Ward/Village/Estate/Landmark">
				</div>
			</div>
<!--NEXT OF KIN-->
			<div class="title bg-dark">
				Next of Kin
			</div>
			<div class="row">
				<div class="form-group col-sm-12 col-md-4 col-lg-4">
					<label>Kin Name <small>(Full name)</small><sup>*</sup></label>
					<input class="form-control form-control-sm" id="kin_name" placeholder="Kin name">
				</div>
				<div class="form-group col-sm-12 col-md-4 col-lg-4">
					<label>Kin Mobile<sup>*</sup></label>
					<input class="form-control form-control-sm" id="kin_phone" placeholder="Mobile">
				</div>
				<div class="form-group col-sm-12 col-md-4 col-lg-4">
					<label>Kin Relationship<sup>*</sup></label>
					<select class="form-control form-control-sm" id="kin_relationship">
						<option value="">Select</option>
						<option value="Father">Father</option>
						<option value="Mother">Mother</option>						
						<option value="Brother">Brother</option>						
						<option value="Sister">Sister</option>
						<option value="Husband">Husband</option>
						<option value="Wife">Wife</option>
						<option value="Grandmother">Guardian</option>
					</select>
				</div>
				<div class="form-group col-sm-12 col-md-4 col-lg-4">
					<label>Kin ID NO</label>
					<input class="form-control form-control-sm" id="kin_id" placeholder="ID NO">
				</div>
			</div>
<!--Insurance information-->
			<div class="title bg-dark">
				Insurance Information
			</div>
			<div class="row">
				<div class="form-group col-sm-12 col-md-4 col-lg-4">
					<label>Patient Insured?<sup>*</sup></label>
					<select class="form-control form-control-sm" id="ins_status">
						<option value="NO">NO</option>
						<option value="YES">YES</option>
					</select>
				</div>
			</div>
			</div>
      </div>
    </div>
  </div>
<!--Proccessing dialog-->
 <div class="modal modal-static" id="processDialog" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" >
  <div style="background-color: #eee;" id="progressBar"><div class="box2"></div></div>	
</div>
  <!-- Menu Toggle Script -->

  <script>
  	var req = null;
  	var image_available = false;
     $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });


    function SetToDate(){
		$('#dob').attr('type','date');
    }

    function GetMyAge(date_picked){
       	$.ajax({
	        method:'POST',
	        url:'CRUD.php',
	        data:{GetMyAge:'1',dob:date_picked},
	        success:function(response){
	          $('#age').val(response);
	        }
       });
    }
    function GetMyDateOfBirth(myage){
       	$.ajax({
	        method:'POST',
	        url:'CRUD.php',
	        data:{GetMyDateOfBirth:'1',myage:myage},
	        success:function(response){
	        	console.log(response);
	        	$('#dob').attr('type','text');
	          	$('#dob').val(response);
	        }
       });
    }


    function CreateImage(url){
    	var canvas = document.getElementById('image');
    	var context = canvas.getContext('2d');
    	console.log(url);
    	var image = new Image();
    	image.onload = function(){
    		canvas.height = image.height;
    		canvas.width = image.width;
    		context.drawImage(image,0,0);
    	}
    	image.src = url;
    	image_available = true;
    }

    $(window).ready(function(){
    	$('#fullname').val("<?= $Patient['fullname']?>");
    	$('#id_type').val("<?= $Patient['id_type']?>");
    	$('#idno').val("<?= $Patient['idno']?>");
    	$('#dob').val("<?= $Patient['dob']?>");
    	$('#sex').val("<?= $Patient['sex']?>");
    	$('#marital_status').val("<?= $Patient['marital_status']?>");

		if ("<?= $Patient['image']?>" !== "") {
			image_available = true;
			CreateImage("<?= $Patient['image']?>");
		}

    	$('#occupation').val("<?= $Patient['occupation']?>");
    	$('#phone').val("<?= $Patient['phone']?>");
    	$('#posta').val("<?= $Patient['posta']?>");
    	$('#country').val("<?= $Patient['country']?>");
    	$('#county').val("<?= $Patient['county']?>");
    	$('#sub_county').val("<?= $Patient['sub_county']?>");
    	$('#ward').val("<?= $Patient['ward']?>");

    	$('#kin_name').val("<?= $Patient['kin_name']?>"); 
    	$('#kin_phone').val("<?= $Patient['kin_phone']?>");
    	$('#kin_relationship').val("<?= $Patient['kin_relationship']?>");
    	$('#kin_id').val("<?= $Patient['kin_id']?>");

    	$('#ins_status').val("<?= $Patient['ins_status']?>");

    	GetMyAge("<?= $Patient['dob']?>");
    });

    function UpdatePatient(){

    	var refno = "<?= $_GET['serveRef']?>";
    	var fullname = $('#fullname').val();
    	var id_type = $('#id_type').val();
    	var idno = $('#idno').val().length>0 ? $('#idno').val(): '';
    	var dob = $('#dob').val();
    	var sex = $('#sex').val();
    	var marital_status = $('#marital_status').val();

    	var image = image_available ? document.getElementById('image').toDataURL():"";
    	var occupation = $('#occupation').val();

    	var phone = $('#phone').val().length>0 ? $('#phone').val(): '';
    	var posta = $('#posta').val().length>0 ? $('#posta').val(): '';
    	var country = $('#country').val().length>0 ? $('#country').val(): '';
    	var county = $('#county').val().length>0 ? $('#county').val(): '';
    	var sub_county = $('#sub_county').val().length>0 ? $('#sub_county').val(): '';
    	var ward = $('#ward').val().length>0 ? $('#ward').val(): '';

    	var kin_name = $('#kin_name').val(); 
    	var kin_phone = $('#kin_phone').val();
    	var kin_relationship = $('#kin_relationship').val();
    	var kin_id = $('#kin_id').val();
    	var ins_status = $('#ins_status').val();

    	if (fullname.length==0) {SnackNotice(false,'Full name is required'); $('#fullname').focus(); return;} 
  		if (dob.length==0) {SnackNotice(false,'Date of birth is required'); $('#dob').focus(); return;} 
  		if (sex.length==0) {SnackNotice(false,'Gender/Sex is required'); $('#sex').focus(); return;} 
  		if (marital_status.length==0) {SnackNotice(false,'Marital Status is required'); $('#marital_status').focus(); return;} 
    	if (kin_name.length==0) {SnackNotice(false,'Kin Name is required'); $('#kin_name').focus(); return;} 
    	if (kin_phone.length==0) {SnackNotice(false,'Kin mobile number is required'); $('#kin_phone').focus(); return;} 
    	if (kin_relationship.length==0) {SnackNotice(false,'Kin Relationship with the Next of Kin is required'); $('#kin_relationship').focus(); return;}

    	$('#processDialog').modal('show');
    	var form = new FormData();
    		form.append('UpdatePatient','1');
    		form.append('refno',refno);
    		form.append('fullname',fullname);
    		form.append('id_type',id_type);
    		form.append('idno',idno);
    		form.append('dob',dob);
    		form.append('sex',sex);
    		form.append('marital_status',marital_status);
    		form.append('image',image);
    		form.append('occupation',occupation);	
    		form.append('phone',phone);
    		form.append('posta',posta);
    		form.append('country',country);
    		form.append('county',county);
    		form.append('sub_county',sub_county);
    		form.append('ward',ward);
    		form.append('kin_name',kin_name);
    		form.append('kin_phone',kin_phone);
    		form.append('kin_relationship',kin_relationship);
    		form.append('kin_id',kin_id);
    		form.append('ins_status',ins_status);

    	$.ajax({
    		method:'POST',
    		url:'CRUD.php',
    		data:form,
    		processData:false,
    		contentType:false,	
    		success:function(response){
    			$('#processDialog').modal('toggle');
    			console.log(response);
    			if (response.includes('success')) {
    				SnackNotice(true,'Patient details updated succesfully');
    			}else{
    				SnackNotice(false,response);
    			}
    		}
    	});
    }
  </script>
</body>
</html>