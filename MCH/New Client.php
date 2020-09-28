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
          <b><i class="oi oi-person"></i> Patient Admission Form</b>
        </div>
        <div class="page_scroller">
          <div class="row">
              <div class="input-group col-sm-12 col-md-6 col-lg-4">
                <input id="searchVal" class="form-control form-control-sm"  placeholder="Enter Registration Number">
                <div class="input-group-prepend" onclick="GetPatientInfo()" >
                    <span class="input-group-text"> <i class="oi oi-magnifying-glass"></i> </span>
                 </div>
              </div>            
          </div>
          <hr>
          <div id="patient_info" class="row">
            <!-- Add from crud -->           
          </div>
          <hr>
          <div class="row">
            <div class="form-group  col-sm-12 col-md-3">
              <label class="text-primary">Register In</label>
              <select class="form-control form-control-sm" id="mch_category">
                <option value="">Select</option>
                <option value="ANC">Antinatal Care (ANC)</option>
                <option value="PNC">Postnatal Care (PNC)</option>
              </select>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="form-group col-sm-12 col-md-8">
              <label class="text-success">Admitted By</label>
              <input class="form-control form-control-sm" id="admitted_by" value="<?= $_SESSION['Fullname']?>" readonly>
            </div>
          </div>
          <hr>                
          <div class="row">
            <div class="form-group col-sm-12 col-md-4">
              <button class="btn btn-success col-12" id="btnSave" onclick="AdmitPatient()"><i class="oi oi-check"></i> Admit</button>
            </div>
            <div class="form-group col-sm-12 col-md-4">
              <a href="home.php" class="btn btn-danger col-12"><i class="oi oi-x"></i> Cancel</a>
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
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });

    window.addEventListener('keyup',function(e){
      if (e.keyCode ===13) {
        GetPatientInfo();
      }
    });
    function GetPatientInfo(){
      var refno = $('#searchVal').val();
      $('#processDialog').modal('toggle');
      if (req != null) { req.abort();}
      req = $.ajax({
            method:'post',
            url:'crud.php',
            data:{GetPatientInfo:'1',refno:refno},
            success:function(response){
              $('#patient_info').html(response);
              $('#processDialog').modal('toggle');
            }
          });

    }

    function AdmitPatient(){
      var refno = $('#refno').val();

      var admitted_by = $('#admitted_by').val();
      var sex = $('#sex').val();
      var mch_category = $('#mch_category').val();

      if (refno.length===0) {SnackNotice(false,'You have not selected a patient'); return;}
      if (mch_category.length===0) {SnackNotice(false,'Select the clinic type for this patient'); return;}
      if (sex=='Male' && mch_category=='ANC') {SnackNotice(false,'You cannot Register a male patient in Antinatal Clinic'); return;}

      $('#processDialog').modal('toggle');
      $.ajax({
            method:'post',
            url:'crud.php',
            data:{AdmitPatient:'1',refno:refno,mch_category:mch_category,admitted_by:admitted_by},
            success:function(response){
              $('#processDialog').modal('toggle');
              if (response.includes('success')) {
                SnackNotice(true,'Patient Admitted sucesfully to Maternity Clinic');
                location.href='Home.php';
              }else{
                SnackNotice(false,response);
              }
            }
          });

    }
  </script>
</body>
</html>