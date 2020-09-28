<?php
include('../ConnectionClass.php');
include('../db_class.php');
session_start();

$db = new CRUD();if (!(isset($_SESSION['Username']))) {
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
    $sql = "SELECT * FROM tbl_laboratory_log WHERE labno = '$labno'";
    $LogBook = mysqli_fetch_array(mysqli_query($conn, $sql),MYSQLI_ASSOC);
      $refno = $LogBook['refno'];
      $name=$LogBook['patient_name'];
      $labno = $LogBook['labno'];
      $from = $LogBook['facility_from'];
      $age=$LogBook['patient_age']; 
      $sex=$LogBook['patient_sex'];
      $investigation=$LogBook['investigation'];
      $specimen=$LogBook['specimen'];
      $specimen_cond=$LogBook['specimen_condition'];
      $analysis_date = $LogBook['date_of_analysis'];
      $result_date = $LogBook['result_date_time'];
      $analysing_officer = $LogBook['analysing_officer'];
      $turn_around_time = $LogBook['turn_around_time'];
      $analysing_officer_comment = $LogBook['analysing_officer_comment'];
      $result = $LogBook['result'];

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
      	<div class="col-11 text-secondary" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
      		<b><i class="oi oi-circle-check"></i> Results Verification</b>
      	</div> 
        <div class="row">
          <div class="col-sm-9 col-md-9 col-lg-9" style="height: auto; padding: 5px 20px; border-radius: 5px; border:1px solid #ccc; margin:auto; margin-top: 10px;">
            <div style="width: 100%; background-color: white; border-bottom: 1px solid #ccc; margin-bottom: 5px;">
              <table style="width: 100%;">
                <tr>
                  <td>Name: <b><?= $name?></b></td>
                  <td>Lab No.: <b><?= $labno?></b></td>
                  <td>OPD No.: <b><?= $refno ?></b></td>
                </tr>
                <tr>
                  <td>Age: <b><?= $age?></b></td>
                  <td>Sex: <b><?= $sex ?></b></td>
                  <td>From: <b><?= $from ?></b></td>
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
              <div class="form-group col-sm-12 col-md-4">
                <label>Specimen Condition</label>
                <input id="specimen_cond" class="form-control form-control-sm" value="<?= $specimen_cond?>"readonly>
              </div>
              <div class="form-group col-sm-12 col-md-4">
                <label>Analysis Date</label>
                <input class="form-control form-control-sm"  id="analysis_date" readonly value="<?= $analysis_date?>">
              </div>
              <div class="form-group col-sm-12 col-md-4">
                <label>Results Date/Time</label>
                <input class="form-control form-control-sm" id="result_date" value="<?= $result_date?>" readonly>
              </div>
            </div>
            <div class="row">
              <div class="form-group col-sm-12 col-md-6">
                <label>Turn Around Time</label>
                <input id="observation" class="form-control form-control-sm" readonly value="<?= $turn_around_time ?>">
              </div>
              <div class="form-group col-sm-12 col-md-6">
                <label>Investigating Officer  Comment</label>
                <textarea style="height: 30px;" id="comment" class="form-control form-control-sm" readonly><?= $analysing_officer_comment?></textarea>
              </div>
              <div class="form-group col-sm-12 col-md-6">
                <label>Result</label>
                <input id="result" class="form-control form-control-sm" readonly value="<?= $result?>">
              </div>
            <hr style="border: 1px solid lime; width: 100%; margin:3px;">
              <div class="form-group col-sm-12">
                <label>Verification Note</label>
                <textarea id="confirm_comment" style="height: 30px;" class="form-control form-control-sm" placeholder="Note..."></textarea>
              </div>
            </div>
            <div class="row">
              <div class="form-group col-sm-12 col-md-3">
                <button class="btn btn-success col-12" onclick="VerifyResults()"><i class="oi oi-check"></i> Verify</button>
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <button class="btn btn-primary col-12" onclick="CancelResults()"><i class="oi oi-x"></i> Cancel Result</button>
              </div>  
              <div class="form-group col-sm-12 col-md-3">
                <a href="verification queue.php" class="btn btn-outline-danger col-12"><i class="oi oi-x"></i> Close</a>
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
    $(document).ready(function(){
      $('#analysis_date').datepicker();
      $('#analysis_date').focus(function(){
          $('#analysis_date').datepicker('show');
      })
      $('#result_date').datepicker();
      $('#result_date').focus(function(){
          $('#result_date').datepicker('show');
      })
    });

    function SetDateFormat(element){
       var jsdate = $(element).val();
       $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{jsDAteToPhpDate:'1',jsdate:jsdate},
        success:function(response){
          $(element).val(response);
        }
       });
    }
    function VerifyResults(){
      var refno = "<?= $refno?>";
      var labno = "<?= $labno?>";
      var analysing_officer = "<?= $analysing_officer ?>";
      var verifying_officer = "<?= $Fullname ?>";
      var my_comment  = $('#confirm_comment').val();

      if (verifying_officer==analysing_officer) {
        SnackNotice(false,'You are the one who perfomed this analysis and therefore cannot Verify it.');
        return;
      }
      
      $('#processDialog').modal('toggle');
      $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{VerifyResults:'1',refno:refno,labno:labno,verifying_officer:verifying_officer,my_comment:my_comment},
        success:function(response){
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            SnackNotice(true,"Laboratory result Verified succesfully");
          }else{
              SnackNotice(true,response);
          }
          location.href="verification queue.php";
        }
      });
    }

    function CancelResults(){
      var refno = "<?= $refno?>";
      var labno = "<?= $labno?>";
      var analysing_officer = "<?= $analysing_officer ?>";
      var verifying_officer = "<?= $Fullname ?>";
      var my_comment  = $('#confirm_comment').val();

      if (verifying_officer==analysing_officer) {
        SnackNotice(false,'You are the one who perfomed this analysis and therefore cannot Verify it.');
        return;
      }
      if (my_comment.length===0) {
        SnackNotice(false,'You must give a reason for cancelling this result.');
        return;
      }
      $('#processDialog').modal('toggle');
      $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{CancelResults:'1',refno:refno,labno:labno,verifying_officer:verifying_officer,my_comment:my_comment},
        success:function(response){
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            SnackNotice(true,"Laboratory result cancel");
          }else{
              SnackNotice(true,response);
          }
          location.href="verification queue.php";
        }
      });
    }
  </script>
</body>
</html>