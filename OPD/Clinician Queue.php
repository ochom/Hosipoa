<?php
session_start();
include('../ConnectionClass.php');
include('../db_class.php');
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
if (!($User_level=='admin' || $GroupPrivileges['opd_treatment_priv']==1)) {
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> OPD</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; margin-bottom: 10px; border-radius: 3px; ">
          <b><i class="oi oi-timer"></i> Clinical Queue</b>
        </div> 
          <div class="page_scroller">
            <table class="table table-sm table-striped table-bordered table-responsive{sm}">
              <thead class="bg-dark text-light">
                <th>File No.</th>
                 <th>Queued at</th>
                <th>Name</th>
                <th>Age</th>
                <th>Duration in queue</th>
                <th>From <small>(Room)</small></th>
                <th>Note</th>
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

<div id="notificationArea">
  <div class="bg-primary" style="height: 35px; padding: 3px; color: #fff;">Notification
    <button type="button" class="close" id="notification_toggle" aria-label="close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <div class="notification_box">
    
  </div>
</div>
<!-- Menu Toggle Script -->
  <script>
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });
    $("#notification_toggle").click(function(e) {
      e.preventDefault();
      $("#notificationArea").toggleClass("toggle");
    });
    
    $(document).ready(function(){
      GetQueue();
    });

    GetReadyResults();

    function GetReadyResults(){
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{GetReadyResults:'1'},
        success:function(response){
          $('.notification_box').html(response);
          $("#notificationArea").toggleClass("toggle");
        }
      });
    }
    function GetQueue(){
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{GetConsultationQueue:'1'},
        success:function(response){
          $('#processDialog').modal('hide');
          $('#queue_tbody').html(response);
        }
      });
    }
    
    setInterval(function(){GetQueue();},10000); 


    //Individual countdowns
    function createCountDown(elementID,date){
    var startDate = new Date(date*1000);
      setInterval(function(){
        var currentDate = new Date(date*1000);
        var currentDate = CreateTimeOnZone();
        var diff = currentDate - startDate;
        var d = Math.floor(diff/(1000*60*60*24));
        var h = Math.floor(diff%(1000*60*60*24)/(1000*60*60));
        var m = Math.floor(diff%(1000*60*60)/(1000*60));
        var s = Math.floor(diff%(1000*60)/(1000));

        h = (h<10)?'0'+h:h;
        m = (m<10)?'0'+m:m;
        s = (s<10)?'0'+s:s;
        var timeElapse = d+':'+h+':'+m+':'+s;
        $('#'+elementID).html(timeElapse); 
        
      },1000);
    }
      <?php        
        $sqlTimes = "SELECT * FROM tbl_opd_service_request WHERE req_department='OPD' AND req_status='granted'";
        $qry = mysqli_query($conn,$sqlTimes);
        while ($Service = mysqli_fetch_assoc($qry)) {
          $d = date_create_from_format('d/m/Y H:i:s',$Service['req_date']);
          $longTime = date_timestamp_get($d);          
          ?>
            createCountDown("<?= $Service['req_id']?>","<?= $longTime; ?>");
          <?php
        }
      ?>
    function CreateTimeOnZone(){
      var nairobiOffset = 3*3600000;
      var d = new Date();
      var utc = d.getTime() + (d.getTimezoneOffset()*60000);
      return new Date(utc+(nairobiOffset)).getTime();
    }
  </script>
</body>
</html>