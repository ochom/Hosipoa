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
          <b><i class="oi oi-lock-locked"></i> Change password</b>
        </div> 
        <div class="row">
          <div class="col-sm-9 col-md-6 col-lg-6" style="height: auto; padding: 10px 20px; border-radius: 5px; border:1px solid #ccc; margin:auto; margin-top: 10px;">            
            <div class="form-group">
              <label>Old Password</label>
              <input class="form-control form-control-sm" type="password" id="old_pass" placeholder="********">
            </div>
            <div class="form-group">
              <label>New Password</label>
              <input class="form-control form-control-sm" type="password" id="new_pass" placeholder="********">
            </div>
            <div class="form-group">
              <label>Confirm New Password</label>
              <input class="form-control form-control-sm" type="password" id="confirm_pass" placeholder="********">
            </div>
            <div class="row">
              <div class="form-group col-sm-12 col-md-6">
                <button class="btn btn-success col-12" onclick="ChangePassword()"><i class="oi oi-check"></i> Change Password</button>
              </div>
              <div class="form-group col-sm-12 col-md-6">
                <a href="Profile.php" class="btn btn-danger col-12"><i class="oi oi-x"></i> Close</a>
              </div>              
            </div>
          </div>
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

    function ChangePassword(){
    	var old_pass = $('#old_pass').val();
    		if (old_pass.length===0) {
    			SnackNotice(false,'Enter the old password');
    			return;
    		}
    	var new_pass = $('#new_pass').val();
    		if (new_pass.length===0) {
    			SnackNotice(false,'Enter the new password');
    			return;
    		}
    	var confirm_pass = $('#confirm_pass').val();
    		if (confirm_pass.length===0) {
    			SnackNotice(false,'Confirm the new password you just entered');
    			return;
    		}
    	if (old_pass === new_pass) {
    		SnackNotice(false,'Old and New password cannot be the same');
    			return;
    	}
    	if (!(new_pass === confirm_pass)) {
    		SnackNotice(false,'New password and Confirmation do not match');
    			return;
    	}
    	$.ajax({
    		method:'post',
    		url:'crud.php',
    		data:{ChangePassword:'1',old_pass:old_pass,new_pass:new_pass},
    		success:function(response){    			
    			if (response.includes('success')) {
    				SnackNotice(true,'Password changed succesfully');
    			}else{
    				SnackNotice(false,response);
    			}
    		}
    	});
    }
  </script>
</body>
</html>