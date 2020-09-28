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
if (!($User_level=='admin' || $GroupPrivileges['maternity_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
}
  
include('../ConnectionClass.php');
if (isset($_GET['type'])) {
  $mch_category = $_GET['type'];
}
?>
<!DOCTYPE html>
<html>
<head>
  <!--Links-->
  <?php 
    include('../sub_links.php');
  ?>
  <!--//Links-->
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
          <b><i class="oi oi-people"></i> <?= $_GET['type']?> Patients</b>
        </div>
        <div class="col-sm-9 col-md-9 col-lg-10" style="height: auto; padding: 10px 20px; border-radius: 5px; border:1px solid #ccc; margin:auto; margin-top: 10px;">
          <div class="row">
            <div class="form-group">
              <button class="btn btn-primary" onclick="GetAppointment(); $('#AppointmentsPopUp').modal('show');"><i class="oi oi-calendar"></i>Today's Appointments</button>
            </div>
          </div>
          <div class="row">
            <div class="form-group col-sm-12 col-md-3 col-lg-5">
              <select class="form-control form-control-sm" id="searchby">
                <option value="refno">Reg. No</option>
                <option value="fullname">Name</option>
                <option value="idno">ID Number</option>
                <option value="ins_card_no">Insurance Card No.</option>
              </select>
            </div>
            <div class="form-group col-sm-12 col-md-7 col-lg-6">               
              <div class="input-group">
                <input  class="form-control form-control-sm" id="searchVal" onkeyup="SearchPatient($('#searchby').val(),$('#searchVal').val(),'<?= $mch_category?>')" placeholder="Search...">
                <div class="input-group-prepend" onclick="SearchPatient($('#searchby').val(),$('#searchVal').val(),'<?= $mch_category?>')">
                    <span class="input-group-text"> <i class="oi oi-magnifying-glass"></i> </span>
                 </div>
              </div>
            </div>
          </div>
          <table class="table table-bordered table-sm table-striped" style="margin: 10px;">
            <thead class="bg-light">
              <th>Reg No.</th>
              <th>Name</th>
              <th>ID Number</th>
              <th>Actions</th>
            </thead>
            <tbody id="patient_list">
        <!-- Add from crud--> 
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>


  <!--New Item-->
<div class="modal fade" id="AppointmentsPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >Today's Appointments</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-striped table-sm" style="cursor: pointer;">
          <thead class="text-light bg-dark">
            <th>ID.</th>
            <th>Reg No.</th>
            <th>Name</th>
          </thead>
          <tbody id="todays_appointments">
            <!-- FROM CRUD  -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>  

<div class="modal fade" id="BookAppointmentPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >Today's Appointments</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group col-12">
            <label>Reg No.</label>
            <input class="form-control form-control-sm" id="refno" readonly>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-12">
            <label>Appointment Date</label>
            <input class="form-control form-control-sm" type="date" id="appointment_date">
          </div>
        </div>
        <div class="form-row">
          <button class="btn btn-outline-success btn-sm" onclick="BookAppointment()" style="margin-right: 20px;"><i class="oi oi-check"></i> Book</button>
          <button class="btn btn-outline-danger btn-sm" onclick="$('#BookAppointmentPopUp').modal('hide');"><i class="oi oi-x"></i> Cancel</button>
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
    var req = null, selectedRefno;
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });

    var mch_category = "<?= $mch_category?>";
    SearchPatient('','',mch_category);
    function SearchPatient(searchby,searchVal,mch_category){
      if (req != null) req.abort();
      req = $.ajax({
            method:'post',
            url:'crud.php',
            data:{SearchPatient:'1',searchby:searchby,searchVal:searchVal,mch_category:mch_category},
            success:function(response){
              $('#patient_list').html(response);
            }
          });
    }

    function BookAppointment(){
      var refno = $('#refno').val();
      var appointment_date = $('#appointment_date').val();
      var todayphp = "<?= date('Y-m-d')?>"
      var today = new Date(Date.parse(todayphp));
      var pickedDate = new Date(Date.parse(appointment_date));
      if (appointment_date=='') {SnackNotice(false,'Enter the appointment date');return;}
      if ((pickedDate-today)<0) {SnackNotice(false,'Enter appointment date that is either today or a future date');return;}
      if (((pickedDate-today)/(1000*3600*24))>365) {SnackNotice(false,'You can only book an appointment in a period of not more than 12 Months');return;}
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{
          BookAppointment:'1',
          refno:refno,
          appointment_date:appointment_date
        },
        success:function(response){
          if (response.includes('success')) {
            $('#BookAppointmentPopUp').modal('hide');
            SnackNotice(true,'Appointment date has been booked successfully');
          }else{
            SnackNotice(false,response);
          }
        }
      });
    }

    function GetAppointment(){
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{
          GetAppointment:'1',
          mch_category:'<?= $mch_category?>',
        },
        success:function(response){
          $('#todays_appointments').html(response);
        }
      });
    }
  </script>
</body>
</html>