<?php
session_start();
if (!(isset($_SESSION['Username']))) {
  header("refresh:0, url=../index.php");
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
    .btn-outline-primary{
      margin:30px 10px; padding: 10px 0px; border-radius: 3px;
    }
    .col-11 a i{
        font-size:40px; margin-bottom: 10px;
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Reports</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-dashboard"></i> Dashboard</b>
        </div>
      <button style="position: fixed; right: 20px; top: 80px; z-index: 200" class="btn btn-success"><i class="oi oi-document"></i> Export To Excel</button>
      <div class="page_scroller">
        <div class="form-row">
          <div class="form-group col-sm-12 col-lg-6">
            <label>Select Report to Generate</label>
            <select class="form-control form-control-sm">
              <option>Select</option>
              <option value="MOH 204A">Outpatient Register: Under 5 years (MOH 204A)</option>  
              <option value="MOH 204B">Outpatient Register: Over 5 years (MOH 204B)</option>
              <option value="MOH 209">Radiology Register (X-RAY) (MOH 209)</option>
              <option value="MOH 301">Inpatient Register (MOH 301)</option>
              <option value="MOH 240A">Blood Cross Match Register (MOH 240A)</option>
            </select>
          </div>
          <div class="form-group col-sm-6 col-lg-3">
            <label>Year</label>
            <select class="form-control form-control-sm">
              <option value="<?= date('Y')?>"><?= date('Y')?></option>
            </select>
          </div>
          <div class="form-group col-sm-6 col-lg-3">
            <label>Month</label>
            <select type="date" class="form-control form-control-sm">
              <option value="all">All</option>
            </select>
          </div>
      </div>
      <div style="background-color: #ccc;">        
        <table class="table table-sm">
          <!-- FROM CRUD -->      
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
  </script>
</body>
</html>