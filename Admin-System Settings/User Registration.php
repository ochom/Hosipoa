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
$regno = 'none';
$fullname = '';
$username = '';
$password = '';
$idno = '';
$phone = '';
$email = '';
$user_group = 'Select';
if (isset($_GET['serveRef'])) {
	$regno = mysqli_real_escape_string($conn,$_GET['serveRef']);
	$row = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_system_users WHERE reg_no='$regno' "),MYSQLI_ASSOC);
	$fullname = $row['full_name'];
	$username = $row['username'];
	$password = $row['password'];
	$idno = $row['idno'];
	$phone = $row['phone'];
	$email = $row['email'];
	$user_group = $row['user_group'];
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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> System Manager</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">      		
      		<b><i class="oi oi-people"></i> Employee Registration</b>
      	</div>
        <div class="col-sm-12 col-md-7 col-lg-6" style="height: auto; padding: 10px 20px; border-radius: 5px; border:1px solid #ccc; margin:auto; margin-top: 10px;">
<!--Personal Details-->
			<div class="row">
				<div class="form-group col-sm-12 col-md-12">
					<label>Full name<sup>*</sup></label>
					<input class="form-control form-control-sm" type="text"  id="fullname" placeholder="Full name" value="<?= $fullname?>">
				</div>
			</div>
			<div class="row">
				<div class="form-group col-sm-12 col-md-6">
					<label>Username<sup>*</sup></label>
					<input class="form-control form-control-sm" type="text"  id="username" placeholder="Username" value="<?= $username?>">
				</div>
				<div class="form-group col-sm-12 col-md-6">
					<label>User Level<sup>*</sup></label>
					<input class="form-control form-control-sm" type="text" placeholder="Phone" value="standard" readonly>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-sm-12 col-md-12">
					<label>Email<sup>*</sup></label>
					<input class="form-control form-control-sm" type="email"  id="email" placeholder="Email" value="<?= $email?>" onkeyup="$(this).val($(this).val().toLowerCase())"> 
				</div>
			</div>
			<div class="row">
				<div class="form-group col-sm-12 col-md-6">
					<label>Phone Number<sup>*</sup></label>
					<input class="form-control form-control-sm" type="text"  id="phone" placeholder="Phone" value="<?= $phone?>">
				</div>
				<div class="form-group col-sm-12 col-md-6">
					<label>ID Number<sup>*</sup></label>
					<input class="form-control form-control-sm" type="text"  id="idno" placeholder="ID Number" value="<?= $idno?>">
				</div>
			</div>
			<div class="row">
				<div class="form-group col-sm-12 col-md-12">
					<label>User Group<sup>*</sup></label>
					<select class="form-control form-control-sm" id="user_group">
						<option value="<?= $user_group?>"><?= $user_group?></option>
				<?php
          			$row = mysqli_query($conn,"Select * From tbl_user_groups");
          			while ($Group = mysqli_fetch_assoc($row)) {
          				?>
          				<option value="<?= $Group['group_name']?>"><?= $Group['group_name']?></option>
          				<?php
          			}
          		?>
					</select>
				</div>
			</div>	
			<div class="row">	
				<div class="form-group col-sm-12 col-md-4">
					<button class="btn btn-outline-success col-12" onclick="RegisterUser()">
						<i class="oi oi-check"></i> Save</button>
				</div>			
		      	<div class="form-group col-sm-12 col-md-4">
					<a href="System Users.php" class="btn btn-outline-danger col-12" ><i class="oi oi-x"></i> Close</a>
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

    $('#dob').datepicker();
    function RegisterUser(){
    	var regno = "<?= $regno?>";
    	var fullname = $('#fullname').val();
    	var username = $('#username').val();
    	var email = $('#email').val();
    	var phone = $('#phone').val();
    	var idno = $('#idno').val();
    	var user_group = $('#user_group').val();

    	if ($('.form-control').val().length==0) {SnackNotice(false,'All fields are required to register a system user'); return;}
    	$('#processDialog').modal('toggle');
    	$.ajax({
    		method:'POST',
    		url:'CRUD.php',
    		data:{RegisterUser:'1',regno:regno,fullname:fullname,username:username,email:email,phone:phone,idno:idno,user_group:user_group},
    		success:function(response){
    			$('#processDialog').modal('toggle');
    			if (response.includes('success')) { 
            SnackNotice(true,'User information updated successfully');             
            location.href='System Users.php';
          }else{
            SnackNotice(false,response);
          }
    		}
    	});
    }
  </script>
</body>
</html>