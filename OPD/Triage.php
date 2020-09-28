<?php
session_start();
include('../ConnectionClass.php');
include('../db_class.php');
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
if (!($User_level=='admin' || $GroupPrivileges['opd_triage_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
}

//Process page
    $fileno = $_GET['fileno'];
    $refno =  $db->ReadOne("SELECT * FROM tbl_opd_visits WHERE fileno = $fileno")['patient_id'];
    $Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$refno' "); 
    $name = $Patient['fullname'];
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
      padding: 5px 20px; border-radius: 5px; color: #fff; margin-bottom: 10px;
    }
    sup{
      color: #f00;
    }
    td{
      padding: 5px 20px;
    }

    .indicator{
      color: #888;  width: 200px; height: auto; text-align: center;
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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Triage</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-pencil"></i> Vitals and Screening</b>
        </div>
          <div class="page_scroller">
              <div style="width: 100%; background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); margin:auto; border-radius: 3px;">
                <table class="table table-sm">
                  <tr>
                    <td>Name: <b><?= $name?></b></td>
                    <td>Reg No. <b id="refno"><?= $refno ?></b></td>
                  </tr>
                </table>
              </div>              
              <div class="title bg-dark">Hypertension Screening/Blood Pressure</div>
              <div class="row">
                  <div class="form-group col-sm-12 col-md-2">
                    <label>BP Systolic</label>
                    <input class="form-control form-control-sm" placeholder="100-130" onkeyup="ClassifyBP()" id="bp_systolic">
                  </div>
                  <div class="form-group col-sm-12 col-md-2">
                    <label>BP Diastolic</label>
                    <input class="form-control form-control-sm" placeholder="60-85" onkeyup="ClassifyBP()" id="bp_diastolic">
                  </div>
                  <div class="form-group col-sm-12 col-md-2">
                    <label>Pulse (Heart Rate)</label>
                    <input class="form-control form-control-sm" placeholder="60-77" id="pulse">
                  </div>
                  <div class="form-group col-sm-12 col-md-2">
                    <label>Hypertension Meter</label>
                    <input class="form-control form-control-sm" placeholder="Normal" id="hypertension_index" readonly>                    
                  </div>
                  <div class="form-group col-sm-12 col-md-2">
                    <label>Hypertensive</label>
                    <input class="form-control form-control-sm" placeholder="Yes/No" id="hypertensive" readonly>
                  </div>
              </div>
              <div class="title bg-dark">Physical</div>                              
                <div class="row">
                  <div class="form-group col-sm-12 col-md-2">
                    <label>Temperature <small>(<sup>o</sup>C)</small></label>
                    <input class="form-control form-control-sm" id="temperature" placeholder="0.0">
                  </div>
                  <div class="form-group col-sm-12 col-md-2">
                    <label>Mass <small>(Kg)</small> </label>
                    <input class="form-control form-control-sm" id="mass" onkeyup="if($('#height').val().length>0){calcBMI()}" placeholder="0.0">
                  </div>
                  <div class="form-group col-sm-12 col-md-2">
                    <label>Height (m)</label>
                    <input class="form-control form-control-sm" id="height" onkeyup="if($('#height').val().length>0){calcBMI();} if($(this).val()>3){$(this).val(''); SnackNotice(false,'Height cannot be more than 3 metres')}" placeholder="0.00">
                  </div>                 
                  <div class="form-group col-sm-12 col-md-2">                    
                    <label>BMI</label>                    
                    <input class="form-control form-control-sm" id="bmi" placeholder="0.00" readonly>
                  </div>
                  <div class="form-group col-sm-12 col-md-2">                    
                    <label>BMI Meter</label>                    
                    <input class="form-control form-control-sm" id="bmi_index" placeholder="Normal" readonly>
                  </div>
                </div>
                <div class="title bg-dark">Triage/Nurse Note</div>
                <div class="row">
                  <div class="form-group col-sm-12 col-md-12">
                    <textarea class="form-control form-control-sm" id="triage_note" placeholder="Note..."></textarea>
                  </div>
                  <div class="form-group col-sm-12 col-md-3">
                    <button class="btn btn-success col-12" onclick="SaveVitals()"><i class="oi oi-check"></i> Save</button>
                  </div>
                  <div class="form-group col-sm-12 col-md-3">
                    <a href="Triage Queue.php" class="btn btn-danger col-12"><i class="oi oi-x"></i> Cancel</a>
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
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });

    window.addEventListener('keyup',function(e){
      if (e.keyCode ===13) {
        SaveVitals();
      }
    });


    function ClassifyBP(){
      var bp_systolic = $('#bp_systolic').val();
      var bp_diastolic = $('#bp_diastolic').val();

      if (bp_systolic.length===0 || bp_diastolic.length===0) {
        $('#hypertension_index').val('---');
        $('#hypertensive').val('---'); return;
      }

      bp_systolic = +$('#bp_systolic').val();
      bp_diastolic = +$('#bp_diastolic').val();
      /*Systolic*/
      if ((bp_systolic>=100 && bp_systolic<130) && (bp_diastolic>=60 && bp_diastolic<85)) {
        $('#hypertension_index').val('Normal');
        $('#hypertensive').val('No');
      }else if ((bp_systolic>=130 && bp_systolic<140) && (bp_diastolic>=85 && bp_diastolic<90)) {
        $('#hypertension_index').val('High Normal');
        $('#hypertensive').val('Yes');
      }else if ((bp_systolic>=140 && bp_systolic<150) && (bp_diastolic>=90 && bp_diastolic<100)) {
        $('#hypertension_index').val('Stage 1 Mild');
        $('#hypertensive').val('Yes');
      }else if ((bp_systolic>=150 && bp_systolic<160) && (bp_diastolic>=100 && bp_diastolic<110)) {
        $('#hypertension_index').val('Stage 2 Moderate');
        $('#hypertensive').val('Yes');
      }else if (bp_systolic>=180  && bp_diastolic>=110) {
        $('#hypertension_index').val('Stage 3 Severe');
        $('#hypertensive').val('Yes');
      }else{
        $('#hypertension_index').val('Panic');
        $('#hypertensive').val('Yes');
      }

    }

    function calcBMI(){
      var height = $('#height').val();
      var mass = $('#mass').val();
      var bmi = (mass * 1.0 / (height * height)).toFixed(2);
      $('#bmi').val(bmi);

      if (bmi<18.5) {
        $('#bmi_index').val("Underweight");
      }

      if (bmi>=18.5 && bmi<25.0) {
        $('#bmi_index').val("Normal");
      }
      if (bmi>=25.0 && bmi<30.0) {
        $('#bmi_index').val("Overweight");
      }
      if (bmi>30.0) {
        $('#bmi_index').val("Obesity");
      }

    }

    function SaveVitals(){
    	var refno = "<?= $refno ?>";
      var fileno = "<?= $fileno ?>";
  		
      var bp_systolic = $('#bp_systolic').val();
      var bp_diastolic = $('#bp_diastolic').val();
      var hypertension_index = $('#hypertension_index').val();
      var hypertensive = $('#hypertensive').val();
      var pulse = $('#pulse').val();
      var mass = $('#mass').val();
      var height = $('#height').val();
      var bmi = $('#bmi').val();
      var bmi_index = $('#bmi_index').val();
      var temperature = $('#temperature').val(); 
      var triage_note = $('#triage_note').val();

      if (bp_systolic.length==0  || isNaN(bp_systolic)) {SnackNotice(false,'Enter valid Systolic Measurement'); $('#bp_systolic').focus();  return;}
      if (bp_diastolic.length==0 || isNaN(bp_diastolic)) {SnackNotice(false,'Enter valid Diastolic Measurement');  $('#bp_diastolic').focus();  return;}
      if (pulse.length==0 || isNaN(pulse)) {SnackNotice(false,'Enter valid pulse rate of the patient');  $('#pulse').focus();  return;}  
      if (mass.length==0  || isNaN(mass)) {SnackNotice(false,'Enter valid Weight or Mass of the patient');  $('#mass').focus();  return;} 
      if (height.length==0  || isNaN(height)) {SnackNotice(false,'Enter the Height of the patient'); $('#height').focus();  return;} 
      if (isNaN(bmi)) {SnackNotice(false,'The BMI recorded is invalid'); return;}
      if (isNaN(temperature) || temperature.length===0) {SnackNotice(false,'Enter valid numeric Temperature reading of the patient'); $('#temperature').focus(); return;} 
      if (temperature < 34) {SnackNotice(false,'Temperature is below limit 34C'); $('#temperature').focus(); return;};
      if (temperature > 40) {SnackNotice(false,'Temperature is above limit 40C'); $('#temperature').focus(); return;};


      $('#processDialog').modal('show');
  		$.ajax({
  				method:'POST',
  				url: 'CRUD.php',
  				data:{SaveVitals: '1',
          fileno:fileno,
          refno:refno,
          bp_systolic:bp_systolic,
          bp_diastolic:bp_diastolic,
          hypertension_index:hypertension_index,
          hypertensive:hypertensive,
          pulse:pulse,
          mass:mass,
          height:height,
          bmi:bmi,
          bmi_index:bmi_index,
          temperature:temperature,
          triage_note:triage_note
        },
  				success: function(response){
  					$('#processDialog').modal('hide');
  					if (response.includes('success')) {
              SnackNotice(true,'Vitals saved succesfully');
  						window.location.href='Triage Queue.php';
  					}else{
             SnackNotice(false,response);
            }
  				}
  			});
  	}
  </script>
</body>
</html>