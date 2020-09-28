<?php
session_start();
if (!(isset($_SESSION['Username']))) {
  header("refresh:0, url=../login.php");
  return;
}
//Session Values
$Username = $_SESSION['Username'];
$Fullname = $_SESSION['Fullname'];
$User_level = $_SESSION['User_level'];
$GroupPrivileges = $_SESSION['GroupPrivileges'];

//Deny permissions
if (!($User_level=='admin' || $GroupPrivileges['maternity_priv']==1)) {
  header("refresh:0, url=Permission.php");
  return;
}
include('../ConnectionClass.php');
$appointment_id = mysqli_real_escape_string($conn,$_GET['appointment_id']);
$refno = mysqli_real_escape_string($conn,$_GET['refno']);
$Patient = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_patient WHERE refno='$refno'"),MYSQLI_ASSOC);
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
  	span i{
  		margin-right:  5px;
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Maternity Clinic</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">         
          <b><i class="oi oi-person"></i> ANC Visit (Pulpate)</b>
        </div>
        <div class="row col-11" style="height: auto; padding: 10px 20px; border-radius: 5px; border:1px solid #ccc; margin:auto; margin-top: 10px;">
          <div class="col-sm-12 col-md-7">
          	<div class="row">
          		<div class="form-group col-sm-12 col-md-5 col-lg-4">
          			<label>Reg. Number</label>
          			<input class="form-control form-control-sm col-12" readonly="" value="<?= $Patient['refno']?>">
          		</div>
          		<div class="form-group col-sm-12 col-md-7 col-lg-8">
          			<label>Reg. Number</label>
          			<input class="form-control form-control-sm col-12" readonly="" value="<?= $Patient['fullname']?>">
          		</div>
          	</div>
          	<div class="row">
          		<div class="form-group col-sm-12 col-md-6">
          			<label class="text-primary">HIV Test</label>
          			<select class="form-control form-control-sm col-12" id="hiv_test">
          				<option value="">Select</option>
          				<option value="Negative">Negative</option>
          				<option value="Positive">Positive</option>
          			</select>
          		</div>
          		<div class="form-group col-sm-12 col-md-6">
          			<label class="text-primary">STI/UTI  Test</label>
          			<select class="form-control form-control-sm col-12" id="sti_test" onchange="if($(this).val()=='Positive'){$('#sti_specification').attr('readonly',false);}else{$('#sti_specification').val('');$('#sti_specification').attr('readonly',true);}">
          				<option value="">Select</option>
          				<option value="Negative">Negative</option>
          				<option value="Positive">Positive</option>
          			</select>
          		</div>
          		<div class="form-group col-sm-12">
          			<label class="text-primary">STI Specification</label>
          			<select class="form-control form-control-sm col-12" id="sti_specification" readonly>
          				<option value="">Select</option>
          				<option value="Syphillis">Syphillis</option>
          				<option value="Gonorrhea">Gonorrhea</option>
          				<option value="UTI">Urinary Infections</option>
          			</select>
          		</div>
          	</div>
          	<div class="row">
          		<div class="form-group col-sm-12">
          			<label class="text-primary">Baby Movement</label>
          			<textarea class="form-control form-control-sm col-12" id="baby_movement"></textarea>
          		</div>
          	</div>
          	<div class="row">
          		<div class="form-group col-sm-12 col-md-6">
          			<label class="text-danger">Fundal Heigt (cm)</label>
          			<input class="form-control form-control-sm col-12" id="fundal_height">
          		</div>
          		<div class="form-group col-sm-12 col-md-6">
          			<label class="text-danger">No of Babies</label>
          			<select class="form-control form-control-sm col-12" id="no_of_babies">
          				<option value="">Select</option>
          				<option value="1">1</option>
          				<option value="2">2</option>
          				<option value="3">3</option>
          				<option value="4">4</option>
          				<option value="5">5</option>
          				<option value="6">6</option>
          				<option value="7">7</option>
          				<option value="8">8</option>
          				<option value="9">9</option>
          				<option value="10">10</option>
          			</select>
          		</div>
          	</div>
          	<div class="row">
          		<div class="form-group col-sm-12">
          			<label class="text-primary">Schedule Next Visit</label>
          			<input class="form-control form-control-sm col-12" type="date" id="appointment_date">
          		</div>
          	</div>
          	<div class="form-row">
          		<button class="btn btn-outline-success col-sm-12 col-md-4 col-lg-3" onclick="SaveANCVisit()" style="margin: 10px;"><i class="oi oi-check"></i> Save</button>
          		<a href="Client List.php?type=ANC" class="btn btn-outline-danger col-sm-12 col-md-4 col-lg-3" style="margin: 10px;"><i class="oi oi-x"></i> Cancel</a>
          	</div>
          </div>
          <div align="center" class="col-sm-12 col-md-4" style="padding-left: 20px;">
          	<p align="center" style="margin-top: 10px; border-bottom: 1px solid #ccc; background-image: linear-gradient(left,right,blue,red);">Appointment Days</p>
          	
          	<?php
          		$sql = mysqli_query($conn,"SELECT * FROM tbl_mch_appointment WHERE refno='$refno' ORDER BY appointment_id DESC");
          		while ($Appointment=mysqli_fetch_Assoc($sql)) {
          			switch ($Appointment['status']) {
          				case 'booked':
          					echo "<span class='btn btn-outline-secondary btn-block'><i class='oi oi-check text-secondary'></i>".$Appointment['appointment_date']."</span><br>";
          					break;
          				
          				case 'missed':
          					echo "<span class='btn btn-outline-secondary btn-block'><i class='oi oi-circle-x text-danger'></i>".$Appointment['appointment_date']."</span><br>";
          					break;
          				case 'kept':
          					echo "<span class='btn btn-outline-secondary btn-block' onclick='VisitDetails()'><i class='oi oi-circle-check text-success'></i>".$Appointment['appointment_date']."</span><br>";
          					break;
          			}
          		}
          	?>
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
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });

    window.addEventListener('keyup',function(e){
      if (e.keyCode ===13) {
        SaveANCVisit();
      }
    });

    function SaveANCVisit(){
      var refno = "<?= $refno?>";
      var appointment_id = "<?= $appointment_id?>";
      var hiv_test = $('#hiv_test').val();
      var sti_test = $('#sti_test').val();
      var sti_specification = $('#sti_specification').val();
      var baby_movement = $('#baby_movement').val();
      var fundal_height = $('#fundal_height').val();
      var no_of_babies = $('#no_of_babies').val();
      var appointment_date = $('#appointment_date').val();

      var todayphp = "<?= date('Y-m-d')?>"
      var today = new Date(Date.parse(todayphp));
      var pickedDate = new Date(Date.parse(appointment_date));
      if (appointment_date=='') {SnackNotice(false,'Enter the appointment date');return;}
      if ((pickedDate-today)<0) {SnackNotice(false,'Enter appointment date that is either today or a future date');return;}
      if (((pickedDate-today)/(1000*3600*24))>365) {SnackNotice(false,'You can only book an appointment in a period of not more than 12 Months');return;}

      if (hiv_test==='') {SnackNotice(false,'Enter the results of the current HIV status'); return;}
      if (sti_test==='') {SnackNotice(false,'Enter the STI or UTI test result'); return;}
      if (sti_test !=  'Negative' && sti_specification=='') {SnackNotice(false,'Specify the STI or UTI is result is positive'); return;}
      if (baby_movement=='') {SnackNotice(false,'Enter the baby movements detected'); return;}
      if (fundal_height =='' || isNaN(fundal_height)) {SnackNotice(false,'Enter the numeric value of fundal height as measured'); return;}
      if (no_of_babies =='') {SnackNotice(false,'Enter the the number of babies detected'); return;}
      if (appointment_date=='') {SnackNotice(false,'Enter the Next Appointment date'); return;}

      $('#processDialog').modal('toggle');
      $.ajax({
            method:'post',
            url:'crud.php',
            data:{
            	SaveANCVisit:'1',
            	refno:refno,
            	appointment_id:appointment_id,
				hiv_test:hiv_test,
				sti_test:sti_test,
				sti_specification:sti_specification,
				baby_movement:baby_movement,
				fundal_height:fundal_height,
				no_of_babies:no_of_babies,
				appointment_date:appointment_date
            },
            success:function(response){
              $('#processDialog').modal('toggle');
             console.log(response);
              if (response.includes('success')) {
              	SnackNotice(true,'Visit data captured and saved succesfully');
                location.href='Client List.php?type=ANC';
              }else{
              	SnackNotice(false,response);
              }
            }
          });

    }
  </script>
</body>
</html>