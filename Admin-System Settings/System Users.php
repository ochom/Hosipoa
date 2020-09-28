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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Human Resource</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
      		<b><i class="oi oi-people"></i> System Users</b>
      	</div> 
          <div class="page_scroller">
            <div class="form-row">
              <a href="User Registration.php" class="btn btn-outline-primary" style="height: 30px; padding: 2px 5px;"><i class="oi oi-plus"></i> Add User</a>
              <div class="form-group  col-7">
                <div class="input-group">
                  <input id="searchVal" class="form-control form-control-sm" onkeyup="FilterUsers()" placeholder="Seach user...">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="oi oi-magnifying-glass"></i></span>
                  </div>
                </div>
              </div>
            </div>
            <table class="table table-sm table-striped table-bordered ">
              <thead class="bg-dark text-light">
                <th>Empl. No.</th>
                <th>Full Name</th>
                <th>User Level</th>
                 <th>Security Group</th>
                <th>Phone</th>
                <th>Account Status</th>
                <th>Action</th>
              </thead>
              <tbody id="users_list" style="cursor: pointer;">
          <!-- Add from CRUD-->
              </tbody>
            </table>
          </div>
      </div>
  </div>
</div>
</body>

<!--Proccessing dialog-->
 <div class="modal modal-static" id="processDialog" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" >
  <div style="background-color: #eee;" id="progressBar"><div class="box2"></div></div>  
</div>
  <!-- Menu Toggle Script -->
  <script>
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });
    GetUsers();

    function GetUsers(){
      $.ajax({
      method:'POST',
      url:'CRUD.php',
      data:{GetUsers:'1'},
      success:function(response){
        $("#users_list").html(response);
      }
    });
    }
    
    function DeleteUser(reg_no){
      RitchConfirm("Proceed ?","Terminate Employee from accesing system ?").then(function(){
        $('#processDialog').modal('toggle');
        $.ajax({
          method:'post',
          url:'crud.php',
          data:{DeleteUser:'1', reg_no:reg_no},
          success:function(response){
            $('#processDialog').modal('toggle');
            if (response.includes('success')) { 
              SnackNotice(true,'User account deactivated');             
              GetUsers();
            }else{
              SnackNotice(false,response);
            }
          }
        });
      });
    }

  function ActivateUser(reg_no){
    $('#processDialog').modal('toggle');
    $.ajax({
      method:'POST',
      url:'CRUD.php',
      data:{ActivateUser:'1',reg_no:reg_no},
      success:function(response){
        $('#processDialog').modal('toggle');
          if (response.includes('success')) { 
            SnackNotice(true,'User account activated');             
            GetUsers();
          }else{
            SnackNotice(false,response);
          }
      }
    });
  }
  </script>
</body>
</html>