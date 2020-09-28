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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Patients' Health History</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">         
          <b><i class="oi oi-magnifying-glass"></i> Search Patients</b>
        </div>
        <div class="col-sm-9 col-md-9 col-lg-10" style="height: auto; padding: 10px 20px; border-radius: 5px; border:1px solid #ccc; margin:auto; margin-top: 10px;">
          <div class="row">
            <div class="form-group col-sm-12 col-md-4">
              <select class="form-control form-control-sm" id="searchby">
                <option value="refno">OPD No</option>
                <option value="fullname">Patient Name</option>
                <option value="idno">ID Number</option>
              </select>
            </div>
            <div class="form-group col-sm-12 col-md-4">               
              <div class="input-group">
                <input  class="form-control form-control-sm" id="searchVal" onkeyup="SearchPatient()">
                <div class="input-group-prepend" onclick="SearchPatient()">
                    <span class="input-group-text"> <i class="oi oi-magnifying-glass"></i> </span>
                 </div>
              </div>
            </div>
          </div>
          <table class="table table-bordered table-sm table-striped" style="margin: 10px;">
            <thead class="bg-dark text-light">
              <th>OPD No.</th>
              <th>Name</th>
              <th>Age</th>
              <th>Sex</th>
              <th>Action</th>
            </thead>
            <tbody id="patient_list">
        <!-- Add from crud--> 
            </tbody>
          </table>
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

    function SearchPatient(){
      var searchby = $('#searchby').val();
      var searchVal = $('#searchVal').val();

      if (req != null) req.abort();
      req = $.ajax({
            method:'post',
            url:'crud.php',
            data:{SearchPatient:'1',searchby:searchby,searchVal:searchVal},
            success:function(response){
              $('#patient_list').html(response);
            }
          });
    }

  </script>
</body>
</html>