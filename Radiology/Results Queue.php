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
if (!($User_level=='admin' || $GroupPrivileges['radiology_priv']==1)) {
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Radiology</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-pencil"></i> Results Queue</b>
        </div> 
        <div class="page_scroller">
          <table class="table table-sm table-striped table-bordered">
            <thead class="bg-dark text-light">
              <th>File No.</th>
              <th>Date/Time</th>
              <th>Req. No.</th>
              <th>Reguest</th>
              <th>Client Name</th>
              <th>Action</th>
            </thead>
            <tbody id="queue_tbody" style="cursor: pointer;">
         <!-- Add from CRUD-->
            </tbody>
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
    
    $(document).ready(function(){
      GetQueue();
    });

    function GetQueue(){
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{GetRunningInvestigations:'1'},
        success:function(response){
          $('#queue_tbody').html(response);
        }
      });
    }
    
    setInterval(function(){
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{GetRunningInvestigations:'1'},
        success:function(response){
          $('#queue_tbody').html(response);
        }
      });
    },2000); 
  </script>
</body>
</html>