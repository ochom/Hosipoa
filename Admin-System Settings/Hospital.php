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
if (!($User_level=='admin' || $GroupPrivileges['system_setting_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
}
include("../ConnectionClass.php");
$Hospital = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_hospital"),MYSQLI_ASSOC);
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
      background-color: #ccc; padding: 5px 20px; border-radius: 5px;
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> System Settings</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; margin-bottom: 10px; border-radius: 3px;">         
          <b><i class="oi oi-medical-cross"></i> Hospital Info.</b>
        </div>
        <div class="col-sm-11 col-lg-8" style="background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); height: auto; padding: 10px 20px; border-radius: 5px; border:1px solid #ccc; margin:auto;">
<!--Details-->
      <div class="row">
        <div class="col-12 bg-dark text-light" style="padding: 5px; border-radius: 3px;"> Hospital Details</div>
        <div class="form-group col-sm-12 col-md-12">
          <label>Hospital Name<sup>*</sup></label>
          <input class="form-control form-control-sm"  id="hospital_name" placeholder="Full Hospital Name" onkeyup="$(this).val($(this).val().toUpperCase())" value="<?= $Hospital['hospital_name']?>">
        </div>
        <div class="form-group col-sm-12 col-md-6">
          <label>MFL Code</label>
          <input class="form-control form-control-sm"  id="mfl_code" placeholder="MFL Code" onkeyup="$(this).val($(this).val().toUpperCase())"  value="<?= $Hospital['mfl_code']?>">
        </div>
        <div class="form-group col-sm-12 col-md-6">
          <label>Postal Address</label>
          <input class="form-control form-control-sm"  id="postal_address" placeholder="Postal Code" onkeyup="$(this).val($(this).val().toUpperCase())"  value="<?= $Hospital['postal_address']?>">
        </div>
        <div class="form-group col-sm-12 col-md-6">
          <label>Physical Address</label>
          <input class="form-control form-control-sm"  id="physical_address" placeholder="Road/Street/City" onkeyup="$(this).val($(this).val().toUpperCase())"  value="<?= $Hospital['physical_address']?>">
        </div>
        <div class="form-group col-sm-12 col-md-6">
          <label>Email Address</label>
          <input class="form-control form-control-sm" type="email"  id="email" placeholder="Email"  value="<?= $Hospital['email']?>">
        </div>
        <div class="form-group col-sm-12 col-md-6">
          <label>Phone Number</label>
          <input class="form-control form-control-sm"  id="phone" placeholder="Phone"  value="<?= $Hospital['phone']?>">
        </div>
<!-- Financial Year -->
        <div class="col-12 bg-dark text-light" style="padding: 5px; border-radius: 3px; margin-bottom: 10px"> Financial Year</div> 
        <div class="form-group col-sm-12 col-md-4">
          <label>Month</label>
          <select class="form-control form-control-sm" id="financial_month" >
            <option value="1">JANUARY</option>
            <option value="2">FEBRUARY</option>
            <option value="3">MARCH</option>
            <option value="4">APRIL</option>
            <option value="5">MAY</option>
            <option value="6">JUNE</option>
            <option value="7">JULY</option>
            <option value="8">AUGUST</option>
            <option value="9">SEPTEMBER</option>
            <option value="10">OCTOBER</option>
            <option value="11">NOVEMBER</option>
            <option value="12">DECEMBER</option>
          </select>
        </div>
        <div class="form-group col-sm-12 col-md-4">
          <label>From</label>
          <input class="form-control form-control-sm" type="date"  id="financial_year_from"   value="<?= $Hospital['financial_year_from']?>">
        </div>
        <div class="form-group col-sm-12 col-md-4">
          <label>To</label>
          <input class="form-control form-control-sm" type="date"  id="financial_year_to"  value="<?= $Hospital['financial_year_to']?>">
        </div>
<!-- Buttons -->
        <div class="form-group col-sm-12 col-md-4">
          <button class="btn btn-outline-success col-12" onclick="RegisterHospital()">
            <i class="oi oi-check"></i> Save</button>
        </div>      
            <div class="form-group col-sm-12 col-md-4">
          <a href="home.php" class="btn btn-outline-danger col-12" ><i class="oi oi-x"></i> Close</a>
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
    $(document).ready(function (){
      $('#financial_month').val("<?= $Hospital['financial_month']?>");
    })

    function RegisterHospital(){
      var hospital_name = $('#hospital_name').val();
      var mfl_code = $('#mfl_code').val();
      var postal_address = $('#postal_address').val();
      var physical_address = $('#physical_address').val();
      var email = $('#email').val();
      var phone = $('#phone').val();
      var financial_month = $('#financial_month').val();
      var financial_year_from = $('#financial_year_from').val();
      var financial_year_to = $('#financial_year_to').val();

      if (hospital_name.length==0) {SnackNotice(false,'Hospital name is required to register an hospital'); $('#hospital_name').focus(); return;}

      $('#processDialog').modal('show');
      $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{
          RegisterHospital:'1',
          hospital_name:hospital_name,
          mfl_code:mfl_code,
          postal_address:postal_address,
          physical_address:physical_address,
          email:email,
          phone:phone,
          financial_month:financial_month,
          financial_year_from:financial_year_from,
          financial_year_to:financial_year_to
        },
        success:function(response){
          $('#processDialog').modal('hide');
          if (response.includes('success')) { 
              SnackNotice(true,'Hospital information updated successfully');  
            }else{
              SnackNotice(false,response);
            }
        }
      });
    }
  </script>
</body>
</html>