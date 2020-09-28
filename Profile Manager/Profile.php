<?php
session_start();
if (!(isset($_SESSION['Username']))) {
  header("refresh:0, url=../index.php");
  return;
}
//Session Values//Session Values
$Username = $_SESSION['Username'];
$Fullname = $_SESSION['Fullname'];
$User_level = $_SESSION['User_level'];

//Process page
include('../ConnectionClass.php');
$sql = "SELECT * FROM tbl_system_users WHERE username = '$Username'";
$res = mysqli_query($conn,$sql);
$row  = mysqli_fetch_array($res,MYSQLI_ASSOC);

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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Profile Manager</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-person"></i> My Profile</b>
        </div> 
        <div class="row">
          <div class="col-sm-9 col-md-6 col-lg-6" style="height: auto; padding: 10px 20px; border-radius: 5px; border:1px solid #ccc; margin:auto; margin-top: 10px;">
            <div class="form-group">
              <label>Full name</label>
              <input class="form-control form-control-sm" type="text" id="full_name" value="<?= $row['full_name']?>">
            </div>
            <div class="form-group">
              <label>User Name</label>
              <input class="form-control form-control-sm" type="text" id="user_name" readonly value="<?= $row['username']?>">
            </div>
            <div class="form-group">
              <label>Email</label>
              <input class="form-control form-control-sm" type="email" id="email" placeholder="Email" value="<?= $row['email']?>">
            </div>
          </div>
        </div>
      </div>
  </div>
</div>
</body>
  <!-- Menu Toggle Script -->
  <script>
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });
  </script>
</body>
</html>