<?php
include('../ConnectionClass.php');
include('../db_class.php');
session_start();

$db = new CRUD();
if (!(isset($_SESSION['Username']))) {
  header("refresh:0, url=../index.php");
  return;
}

$refno = $_GET['refno'];

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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Patient History</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper --> 
        <div class="row col-12" style="margin:auto; margin-top: 10px;">   
          <div class="col-sm-12 col-lg-3" style="height: auto; border: 1px solid grey; border-radius: 5px; background-color: #fff; padding: 0px 5px 10px 5px;">
            <h6 style="padding: 5px; background-color: #334; color: #fff; text-align: center; border-bottom-right-radius: 5px;border-bottom-left-radius: 5px;">Health Files</h6>
            <div style=" overflow-y: auto; max-height: 500px; margin-top: 0px;">
            <?php
              $res = $db->ReadAll("SELECT * FROM tbl_opd_visits WHERE patient_id='$refno' order by fileno DESC");
              while ($visit = mysqli_fetch_assoc($res)) {
                ?>
                <button onclick="GetFileInfo('<?= $visit['fileno']?>')"  class='btn btn-sm btn-block btn-outline-primary'> File No. <?= $visit['fileno']?> - <?= $visit['visit_date']?></button>
                <?php
              }
            ?>
            </div>
          </div>
          <div class="col-sm-12 col-lg-9" style="height: auto;border: 1px solid grey; border-radius: 5px; background-color: #eee; padding: 0px 5px 10px 5px; overflow: hidden;">
            <h6 style="width: 300px; padding: 5px; background-color: #422; color: #fff; text-align: center; border-bottom-right-radius: 5px;border-bottom-left-radius: 5px;">File Information</h6>
            <div id="file_info" style=" overflow-y: auto; max-height: 500px;">
              <!-- CRUD -->
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

    var req = null;
    function GetFileInfo(fileno){
      console.log(fileno);
      if (req != null) req.abort();
      req = $.ajax({
        method:'post',
        url:'crud.php',
        data:{GetFileInfo:'1',fileno:fileno},
        success:function(response){
          $('#file_info').html(response);
        }
      });
    }
  </script>
</body>
</html>