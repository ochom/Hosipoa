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
if (!($User_level=='admin' || $GroupPrivileges['procurement_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
}
if (isset($_GET['Item-type'])) {
  $item_type = $_GET['Item-type'];
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
  <style type="text/css">
    p{
      margin: 2px;
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Accounts</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-monitor"></i> Profit & Loss Accounts</b>
        </div>
        <div class="page_scroller">
          <div class="form-row col-12">
            <div class="form-group  col-sm-12 col-lg-4">
              <label>Year</label>
              <select class="form-control form-control-sm">
                <option><?= date('Y')?></option>
              </select>
            </div>
            <div class="form-group  col-sm-12 col-lg-4">
              <label>Month</label>
              <select class="form-control form-control-sm">
                <option><?= date('M')?></option>
              </select>
            </div>
            <div class="form-group  col-sm-12 col-lg-4">
              <label>Day</label>
              <select class="form-control form-control-sm" >
                <option><?= date('j')?></option>
              </select>
            </div>
          </div>
          <table class="table table-sm table-bordered table-striped">
            <thead class="bg-dark text-light">
              <th style="width: 30px;">#</th>
              <th>Account Name</th>
              <th style="width: 200px;">Credit <small>Ksh</small></th>
              <th style="width: 200px;">Debit <small>Ksh</small></th>
            </thead>
          </table>
      </div>
    </div>
  </div>
</div>
  <!-- Menu Toggle Script -->

  <script>
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });
  </script>
</body>
</html>