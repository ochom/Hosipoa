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
if (!($User_level=='admin' || $GroupPrivileges['laboratory_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
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
    .btn-outline-primary{
      margin:30px 10px; width: 200px; padding: 20px 0px; border-radius: 3px;
    }
    .col-11 a i{
        font-size:40px; margin-bottom: 10px;
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Laboratory</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-dashboard"></i> Dashboard</b>
        </div>
      <div class="page_scroller">
        <a href="../Home.php" class="btn btn-outline-primary">
            <i class="oi oi-home"></i><br> Home
        </a> 
        <a href="SamplingQueue.php" class="btn btn-outline-primary">
            <i class="oi oi-people"></i><br>Sample Collection
        </a>
        <a href="Results Queue.php" class="btn btn-outline-primary">
            <i class="oi oi-pencil"></i><br>Feed Results
        </a>
        <a href="Verification Queue.php" class="btn btn-outline-primary">
            <i class="oi oi-circle-check"></i><br> Results Verification
        </a> 
        <a href="Logbook.php" class="btn btn-outline-primary">
            <i class="oi oi-book"></i><br>Logbook
        </a>
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