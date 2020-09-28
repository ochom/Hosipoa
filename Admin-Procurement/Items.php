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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Procurement, Service & Stationary Management</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-monitor"></i> Drugs, Services & Stationary</b>
        </div>
      <div class="page_scroller">        
        <a href="Item Categories.php" class="btn btn-outline-primary">
            <i class="oi oi-pencil"></i><br>Register Categories
        </a>
        <a href="Drugs.php" class="btn btn-outline-primary">
            <i class="oi oi-eyedropper"></i><br>Drugs
        </a>
        <a href="Laboratory Services.php" class="btn btn-outline-primary">
            <i class="oi oi-beaker"></i><br>Laboratory Services
        </a>
        <a href="Radiology Services.php" class="btn btn-outline-primary">
            <i class="oi oi-aperture"></i><br>Radiology Services
        </a>
        <a href="Medical Procedures.php" class="btn btn-outline-primary">
            <i class="oi oi-pencil"></i><br> Theatre Procedures
        </a>
        <a href="General Services.php" class="btn btn-outline-primary">
            <i class="oi oi-globe"></i><br> General Services
        </a>
        <a href="Static Services.php" class="btn btn-outline-primary">
          <i class="oi oi-pulse"></i><br> Static Services
        </a>
        <a href="Stationary.php" class="btn btn-outline-primary">
            <i class="oi oi-clipboard"></i><br>Stationery & Equipments
        </a>
        <a href="Consumables.php" class="btn btn-outline-primary">
            <i class="oi oi-paperclip"></i><br>Consumables
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