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
    .online{
      animation: blink 1s linear infinite; width: 5px; height: 5px; border-radius: 50%; background-color: green; color: green; margin: 5px; 
    } 
    @keyframes blink{
      50%{
        opacity: 0;
      }
    }
    @-webkit-keyframes blink{
      50%{
        opacity: 0;
      }
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
      		<b><i class="oi oi-pencil"></i> Running Tests</b>
      	</div> 
          <div class="page_scroller">
            <table class="table table-sm table-bordered table-striped">
              <thead class="bg-dark text-light">
                <th>Reg No.</th>
                <th>Lab No.</th>
                <th>Name</th>
                <th>Investigation</th>
                <th>Time Elapse</th>
                <th>Actions</th>
              </thead>
              <tbody id="queue_tbody" style="cursor: pointer;">
              <!-- Add from CRUD-->
              </tbody>
            </table>
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

    $(document).ready(function(){
      setInterval(function(){
        saveTimeElapseToDataBase();
      },1000);      
    });

    $(document).ready(function(){
      GetResultsQueue();
      <?php        
        $sqlTimes = "SELECT * FROM tbl_laboratory_log WHERE status='running' ORDER BY labno ASC";
        $qry = mysqli_query($conn,$sqlTimes);
        while ($match = mysqli_fetch_assoc($qry)) {
          $item_id = "timer".$match['labno'];
          $d = date_create_from_format('d/m/Y H:i:s',$match['date_specimen_received']);
          $longTime = date_timestamp_get($d);          
          ?>
            createCountDown("<?php  echo $item_id ?>","<?= $longTime; ?>");
          <?php
        }
      ?>
    });

    function GetResultsQueue(){
      $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{GetResultsQueue:'1'},
        success:function(response){
          $('#queue_tbody').html(response);
        }
      });
    }
    
    setInterval(function(){
      GetResultsQueue();
    },2000); 

    //Individual countdowns
    function createCountDown(elementID,date){
    var startDate = new Date(date*1000);
      setInterval(function(){
        var currentDate = new Date();
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

    function saveTimeElapseToDataBase(){
      var mytime = null;
      var labno = null;
      $('#queue_tbody tr').each(function(){
        labno = $(this).find('td:nth-child(2)').text();
        mytime = $(this).find('td:nth-child(5)>b').text();
        $.ajax({
          method:'POST',
          url:'CRUD.php',
          data:{SaveTurnAroundTime:'1',labno:labno,mytime:mytime},
          success:function(response){
            /*console.log(response);*/
          }
        });
      });
    }  
  </script>
</body>
</html>