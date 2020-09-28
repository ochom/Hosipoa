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

//Process page
    $labno = $_GET['labno'];
    $LogBook = mysqli_fetch_array(mysqli_query($conn,"SELECT * From tbl_laboratory_log WHERE labno='$labno' "),MYSQLI_ASSOC);
      $refno = $LogBook['refno'];
      $name=$LogBook['patient_name'];
      $labno = $LogBook['labno'];
      $from = $LogBook['facility_from'];
      $age=$LogBook['patient_age']; 
      $sex=$LogBook['patient_sex'];
      $investigation=$LogBook['investigation'];
      $specimen=$LogBook['specimen'];
      $test_lower_range=$LogBook['test_lower_range'];
      $test_upper_range=$LogBook['test_upper_range'];
      $specimen_cond=$LogBook['specimen_condition'];
      $analysisdate=$LogBook['date_specimen_received'];
    $Test = $db->ReadOne("SELECT * FROM tbl_item WHERE item_name='$specimen' AND item_type='Laboratory Service'");
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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Laboratory</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="col-11 text-secondary" style="background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
      		<b><i class="oi oi-timer"></i> Feed Investigation Results</b>
      	</div> 
          <div class="col-sm-11 col-md-9 col-lg-9" style="height: auto; padding: 5px 20px; border-radius: 5px; border:1px solid #ccc; margin:auto; margin-top: 10px;">
            <div style="width: 100%; background-color: white; border-bottom: 1px solid #ccc; margin-bottom: 5px;">
              <table style="width: 100%;">
                <tr>
                  <td>Name: <b><?= $name?></b></td>
                  <td>Lab No. <b><?= $labno?></b></td>
                  <td>Reg NO. <b><?= $refno ?></b></td>
                </tr>
                <tr>
                  <td>Age: <b><?= $age?></b></td>
                  <td>Sex. <b><?= $sex ?></b></td>
                  <td>From. <b><?= $from ?></b></td>
                </tr>
              </table>
            </div>
            <div class="row">
              <div class="form-group col-sm-12 col-md-8">
                <label>Investigation</label>
                <input id="investigation" class="form-control form-control-sm" placeholder="Investigation" value="<?= $investigation ?>" readonly>
              </div>
              <div class="form-group col-sm-12 col-md-4">
                <label>Specimen</label>
                <input id="specimen" class="form-control form-control-sm"  placeholder="Specimen" value="<?= $specimen ?>" readonly>
              </div>
            </div>
            <div class="row">
              <div class="form-group col-sm-12 col-md-3">
                <label>Specimen Condition</label>
                <input id="specimen_cond" class="form-control form-control-sm" value="<?= $specimen_cond?>"readonly>
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <label>Start Date/Time</label>
                <input class="form-control form-control-sm"  id="analysis_date" value="<?= $analysisdate?>"  readonly>
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <label>Results Date/Time</label>
                <input class="form-control form-control-sm" id="result_date" readonly>
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <label>Turn Around Time</label>
                <input class="form-control form-control-sm" id="turn_around_time" value="<?= $LogBook['turn_around_time']?>" readonly>
              </div>
              <div class="form-group col-sm-12 col-md-4">
                <label class="text-primary"><strong>Lower Range</strong></label>
                <input class="form-control form-control-sm" readonly value="<?= $test_lower_range ?>">
              </div>
              <div class="form-group col-sm-12 col-md-4">
                <label class="text-primary"><strong>Upper Range</strong></label>
                <input class="form-control form-control-sm" readonly value="<?= $test_upper_range ?>">
              </div>
              <div class="form-group col-sm-12 col-md-4">
                <label class="text-danger"><strong>Result</strong></label>
                <input id="result" class="form-control form-control-sm" placeholder="Result...">
              </div>
              <div class="form-group col-12">
                <label>Laboratory Note</label>
                <textarea id="comment" class="form-control form-control-sm" placeholder="Laboratory result note..." ></textarea>
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <button class="btn btn-success col-12" onclick="SaveResults()"><i class="oi oi-check"></i> Submit</button>
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <a href="results queue.php" class="btn btn-danger col-12"><i class="oi oi-x"></i> Back</a>
              </div>            
            </div>
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
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });
  </script>
  <script type="text/javascript">
    function SaveResults(){
      var refno = "<?= $refno?>";
      var labno = "<?= $labno?>";
      var analysing_officer = "<?= $Fullname ?>";
      var analysis_date  = $('#analysis_date').val();
      var result_date  = $('#result_date').val();
      var turn_around_time  = $('#turn_around_time').val();
      var comment  = $('#comment').val();
      var result  = $('#result').val();

      if (analysis_date.length==0) {SnackNotice(false,'Enter the analysis date'); $('#analysis_date').focus(); return;}
      if (result_date.length==0) {SnackNotice(false,'Enter the result date'); $('#result_date').focus(); return;}
      if (result.length==0) {SnackNotice(false,'Enter the result of the investigation'); $('#result').focus(); return;}


      $('#processDialog').modal('toggle');
      $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{SaveResults:'1',refno:refno,labno:labno,analysing_officer:analysing_officer,analysis_date:analysis_date,result_date:result_date,turn_around_time:turn_around_time,comment:comment,result:result},
        success:function(response){
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            SnackNotice(true,'Investigation Result saved');
            location.href="results queue.php";
          }
        }
      });
    }

    function createCountDown(date){
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

        $('#turn_around_time').val(timeElapse); 
        var now = new Date();
        var Y,M,D,H,i,sec;
        D = (now.getDate()<10)?'0'+now.getDate():now.getDate();
        M = ((now.getMonth()+1)<10)?'0'+(now.getMonth()+1):(now.getMonth()+1);
        Y = now.getFullYear();

        H = (now.getHours()<10)?'0'+now.getHours():now.getHours();
        i = (now.getMinutes()<10)?'0'+now.getMinutes():now.getMinutes();
        sec = (now.getSeconds()<10)?'0'+now.getSeconds():now.getSeconds();

        $('#result_date').val(D +"/"+ M +"/"+Y +" "+ H+":"+i+":"+sec);
      },1000);
    }
      <?php        
        $sqlTimes = "SELECT * FROM tbl_laboratory_log WHERE labno='$labno'";
        $qry = mysqli_query($conn,$sqlTimes);
        while ($match = mysqli_fetch_assoc($qry)) {
          $d = date_create_from_format('d/m/Y H:i:s',$match['date_specimen_received']);
          $longTime = date_timestamp_get($d);          
          ?>
            createCountDown("<?= $longTime; ?>");
          <?php
        }
      ?>
  </script>
</body>
</html>