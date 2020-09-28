<?php
session_start();
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
	include 'Sidebar.php';
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
      		<b><i class="oi oi-person"></i> Patient Registration</b>
      	</div>
      	<button class="btn btn-success" style="position: fixed; bottom: 80px; right: 20px; width: 150px; z-index: 1" onclick="RegisterPatient()"><i class="oi oi-check"></i> Register</button>
      	<button class="btn btn-danger" style="position: fixed; bottom: 20px; right: 20px; width: 150px; z-index: 1" onclick="ClearFields()"><i class="oi oi-x"></i> Clear</button>
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
						<input class="form-control form-control-sm"  id="dob" placeholder="Date" onfocus="SetToDate()" onchange="GetMyAge($(this).val());">
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
						<select class="form-control form-control-sm" id="id_type" onchange="if($(this).val()==''){$('#idno').prop('readonly',true)}else{$('#idno').prop('readonly',false)}">
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
						<input class="form-control form-control-sm" id="idno" placeholder="Identification Number" readonly onfocus="if($('#id_type').val()==''){$('#id_type').focus()}">
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
					<canvas id="image" src=""  style="background: url('../images/passport.png'); background-size: 100% 100%; width: 150px; height: 200px; margin: 10px; border: 1px solid #ccc; border-radius: 3px;"></canvas>
					<input type="file"  accept="image/*" onchange="CreateImage(window.URL.createObjectURL(this.files[0]))">
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
				Primary Insurance Information
			</div>
			<div class="row">
				<div class="form-group col-sm-12 col-md-4 col-lg-4">
					<label>Patient Insured?<sup>*</sup></label>
					<select class="form-control form-control-sm" id="ins_status" onchange="if($(this).val()=='YES'){$('#ins_company').attr('disabled',false);$('#ins_card_no').attr('disabled',false);}else{$('#ins_company').attr('disabled',true);$('#ins_card_no').attr('disabled',true);}">
						<option value="NO">NO</option>
						<option value="YES">YES</option>
					</select>
				</div>
				<div class="form-group col-sm-12 col-md-4 col-lg-4">
					<label>Primary Insurance Company<sup>*</sup></label>
					<select class="form-control form-control-sm" id="ins_company" disabled>
						<option value="">Select</option>
						<?php
						$result = mysqli_query($conn,"SELECT * FROM tbl_ins_companies WHERE company_name LIKE '%nhif%' ORDER BY company_name ASC");
						while ($Company = mysqli_fetch_assoc($result)) {
							?>
							<option value="<?= $Company['company_name']?>"><?= $Company['company_name']?></option>
							<?php
						}?>
					</select>
				</div>
				<div class="form-group col-sm-12 col-md-4 col-lg-4">
					<label>Card Number<sup>*</sup></label>
					<input class="form-control form-control-sm" id="ins_card_no" placeholder="card number" disabled>
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

    function SetToDate(elem){
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
    	$('#processDialog').modal('toggle');
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
    	$('#processDialog').modal('toggle');
    }

    function ClearFields(){
    	$('input').val('');
    	$('select').val('');
    	var canvas = document.getElementById('image');
    	var ctx = canvas.getContext('2d');
    	ctx.clearRect(0, 0, canvas.width, canvas.height);
    	image_available = false;
    }

    function RegisterPatient(){
    	var fullname = $('#fullname').val();
    	var id_type = $('#id_type').val();
    	var idno = $('#idno').val();
    	var dob = $('#dob').val();
    	var sex = $('#sex').val();
    	var marital_status = $('#marital_status').val();
    	var image = image_available ? document.getElementById('image').toDataURL():"";

    	var occupation = $('#occupation').val();
    	var phone = $('#phone').val();
    	var posta = $('#posta').val();
    	var country = $('#country').val();
    	var county = $('#county').val();
    	var sub_county = $('#sub_county').val();
    	var ward = $('#ward').val();

    	var kin_name = $('#kin_name').val(); 
    	var kin_phone = $('#kin_phone').val();
    	var kin_relationship = $('#kin_relationship').val();
    	var kin_id = $('#kin_id').val();
    	var ins_status = $('#ins_status').val();
    	var ins_company = $('#ins_status').val()=='YES' ? $('#ins_company').val():'';
    	var ins_card_no = $('#ins_status').val()=='YES' ? $('#ins_card_no').val():'';

    	if (fullname.length==0) {SnackNotice(false,'Full name is required'); $('#fullname').focus(); return;} 
    	if (id_type=='') {SnackNotice(false,'Select the ID type used by the patient');$('#id_type').focus(); return;}
    	if (idno=='') {SnackNotice(false,'Enter valid ID Number used by the patient');$('#idno').focus(); return;}
		if (dob.length==0) {SnackNotice(false,'Date of birth is required'); $('#dob').focus(); return;} 
		if (sex.length==0) {SnackNotice(false,'Gender/Sex is required'); $('#sex').focus(); return;} 
		if (marital_status.length==0) {SnackNotice(false,'Marital Status is required'); $('#marital_status').focus(); return;}
    	if (kin_name.length==0) {SnackNotice(false,'Kin Name is required'); $('#kin_name').focus(); return;} 
    	if (kin_phone.length==0) {SnackNotice(false,'Kin mobile number is required'); $('#kin_phone').focus(); return;} 
    	if (kin_relationship.length==0) {SnackNotice(false,'Kin Relationship with the Next of Kin is required'); $('#kin_relationship').focus(); return;} 

    	if (ins_status=='YES' && ins_company.length==0) {
    		SnackNotice(false,'If this patient is insured then enter  the Insurance Company Name');$('#ins_company').focus(); return;
    	}
    	if (ins_company.length !== 0 && ins_card_no.length==0) {
    		SnackNotice(false,'Enter the Insurance Card Number');$('#ins_card_no').focus(); return;
    	}

    	$('#processDialog').modal('toggle');
    	var form = new FormData();
    		form.append('RegisterPatient','1');
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
    		form.append('ins_company',ins_company);
    		form.append('ins_card_no',ins_card_no);

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
    				SnackNotice(true,'Patient registered succesfully');
    				ClearFields();
    			}else{
    				SnackNotice(false,response);
    			}
    		}
    	});
    }
  </script>
</body>
</html>