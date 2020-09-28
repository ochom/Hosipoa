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
if (!($User_level=='admin' || $GroupPrivileges['ipd_general_service_priv']==1)) {
  header("refresh:0, url=Permission.php");
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> In-Patient</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-people"></i> List of Patients</b>
        </div> 
          <div class="page_scroller">
            <div class="form-row">
              <div class="form-group col-sm-12 col-md-3">
                <label>Ward</label>
                <select class="form-control form-control-sm" id="ward_id" onchange="FilterByWard()">
                  <option value="">Select</option>
                  <?php
                   $result = mysqli_query($conn,"SELECT * FROM tbl_ipd_wards ORDER BY ward_name ASC");
                   while($Ward = mysqli_fetch_assoc($result)){?>
                    <option value="<?= $Ward['ward_id']?>"><?= $Ward['ward_name']?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <label>Filter by</label>
                <select id="searchBy" class="form-control form-control-sm">
                  <option value="refno">OPD No</option>
                  <option value="adm_no">IPD File No</option>
                  <option value="fullname">Patient Name</option>
                </select>
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <label>Search</label>
                <input id="searchVal" class="form-control form-control-sm" onkeyup="FilterPatientList()">
              </div>
              <div class="form-group">
                <label style="color: #fff;">>></label>
                <button class="btn btn-outline-primary btn-sm col-12" onclick="FilterPatientList()">Filter</button>
              </div>
            </div>
            <table class="table table-sm table-bordered table-striped">
              <thead class="bg-dark text-light">
                <th>OPD No.</th>
                <th>IPD File No.</th>
                <th>Name.</th>
                <th>Ward</th>
                <th>Serve</th>
              </thead>
              <tbody id="patient_list" style="cursor: pointer;">
          <!-- Add from CRUD-->
              </tbody>
            </table>
          </div>
      </div>
  </div>
</div>
</body>
  <!-- Menu Toggle Script -->
  <script>
    var req = null;
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });

    function FilterPatientList(){
      var searchBy = $('#searchBy').val();
      var searchVal = $('#searchVal').val();
      if (req != null) {req.abort();}
      req = $.ajax({
        method:'post',
        url:'crud.php',
        data:{FilterPatientList:'1',searchBy:searchBy,searchVal:searchVal},
        success:function(response){
          $('#patient_list').html(response);
        }
      });
    }
    function FilterByWard(){
      var searchBy = 'ward_id';
      var searchVal = $('#ward_id').val();
      if (req != null) {req.abort();}
      req = $.ajax({
        method:'post',
        url:'crud.php',
        data:{FilterPatientList:'1',searchBy:searchBy,searchVal:searchVal},
        success:function(response){
          $('#patient_list').html(response);
        }
      });
    }
  </script>
</body>
</html>