<?php
include('../ConnectionClass.php');
include('../db_class.php');
session_start();


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
if (!($User_level=='admin' || $GroupPrivileges['ipd_general_service_priv']==1)) {
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
    .ward_area{
      height: auto; padding: 10px; border-radius: 5px; border:1px solid #ccc; margin:auto; margin-top: 10px;
    }
    .ward{
      float: left; margin: 10px; width: 250px; height: 125px; 
      border-radius: 5px; box-shadow: 3px 3px 8px 2px rgba(0,0,0,0.5);
      background: url('../images/ipd_ward.png'); background-size: cover;
      text-decoration-style: none;
    }
    .ward p{
      color: #FFF; position: relative; top: 5px; font-weight: bold; text-align: center;
    }
    .ward .bar{
      color: #666; position: relative; left: 5px; top: 50px; font-weight: bold; width: 240px; text-align: center; background-color: #fc0; border-radius: 5px; overflow: hidden;
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> In-Patient</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-people"></i> Wards</b>
        </div>
      <div class="row page_scroller">
        <!-- CRUD -->
      </div>
    </div>
  </div>
  <!-- Menu Toggle Script -->

  <script>
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });

    $(document).ready(function(){
      $('.page_scroller').load('wards.php #wards');
    });

    setInterval(function(){
      $('.page_scroller').load('wards.php #wards');
    },2000);

  </script>
</body>
</html>