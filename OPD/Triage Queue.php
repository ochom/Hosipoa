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
if (!($User_level=='admin' || $GroupPrivileges['opd_triage_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
}
include '../COnnectionClass.php';
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Triage</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-people"></i> Triage Queue</b>
        </div> 
          <div class="page_scroller">
            <table class="table table-sm table-striped table-bordered table-primary">
              <thead class="bg-dark text-light">
                <th>Queued at</th>
                <th>Name</th>
                <th>Age</th>
                <th>Duration in queue</th>
                <th>From <small>(Room)</small></th>
                <th>Note</th>
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
        data:{GetTriageQueue:'1'},
        success:function(response){
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

        var hrs,mins,secs;
          hrs = (h<10)?'0'+h:h;
          mins = (m<10)?'0'+m:m;
          secs = (s<10)?'0'+s:s;
        var timeElapse = d+':'+hrs+':'+mins+':'+secs;
        $('#'+elementID).html(timeElapse); 
        
      },1000);
    }
      <?php        
        $sqlTimes = "SELECT * FROM tbl_triage_queue";
        $qry = mysqli_query($conn,$sqlTimes);
        while ($Triage = mysqli_fetch_assoc($qry)) {
          $d = date_create_from_format('d/m/Y H:i:s',$Triage['q_date']);
          $longTime = date_timestamp_get($d);          
          ?>
            createCountDown("<?= $Triage['q_id']?>","<?= $longTime; ?>");
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