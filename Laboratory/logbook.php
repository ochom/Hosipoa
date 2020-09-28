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
      	<div class="text-secondary col-11" style="background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-book"></i> Investigations Log</b>
      	</div> 
          <div class="col-11" style="height: auto; padding: 10px 20px; border-radius: 5px; border:1px solid #ccc; margin:auto; margin-top: 10px">
            <div class="form-row">
              <div class="form-group col-sm-12 col-md-4">
                <label>Filter by</label>
                <select id="searchBy" class="form-control form-control-sm">
                  <option value="">Select</option>
                  <option value="labno">Lab No</option>
                  <option value="refno">Registration No</option>
                  <option value="fullname">Name</option>
                </select>
              </div>
              <div class="form-group col-sm-12 col-md-4">
                <label style="color: #fff;">Filter Value</label>                
                <div class="input-group">
                  <input  class="form-control form-control-sm" id="searchVal" onkeyup="FilterLogBook()">
                  <div class="input-group-prepend" onclick="FilterLogBook()">
                      <span class="input-group-text"> <i class="oi oi-magnifying-glass"></i> </span>
                   </div>
                </div>
              </div>
            </div>
            <table class="table table-sm table-bordered table-striped">
              <thead class="bg-dark text-light">
                <th>Reg No.</th>
                <th>Lab No.</th>
                <th>Name</th>
                <th>Investigation</th>
                <th>Status</th>
                <th>Actions</th>
              </thead>
              <tbody id="logbook_list" style="cursor: pointer;">
          <?php 
            include('../ConnectionClass.php');
            $sql = "SELECT * FROM tbl_laboratory_log ORDER BY labno DESC LIMIT 20";
            $res = mysqli_query($conn,$sql);
            while ($rowSet = mysqli_fetch_assoc($res)) {
              $labno = $rowSet['labno'];
              ?>
                <tr>
                  <td><?= $rowSet['refno']?></td>
                  <td ><?= $rowSet['labno']?></td>
                  <td ><?= $rowSet['patient_name']?></td>
                  <td ><?= $rowSet['investigation']?></td>
                  <td><?= $rowSet['status']?></td>
                  <td><button class="btn btn-outline-primary btn-sm" onclick="var w = window.open('print log.php?labno='+'<?= $labno?>'); w.focus();"><i class="oi oi-print"></i> Print</button></td>
                </tr>
              <?php
                }
              ?>
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
  </script>
  <script type="text/javascript">
    var req = null;
    function FilterLogBook(){
      var searchBy = $('#searchBy').val();
      var searchVal = $('#searchVal').val();
      if (req != null) req.abort();
      req = $.ajax({
        method: 'POST',
        url: 'CRUD.php',
        data: { FilterLogBook:'1', searchBy:searchBy, searchVal:searchVal},
        success: function(response){
          if (response != '') {
            $('#logbook_list').html(response);
          }
        }
      });   
    }

  </script>
</body>
</html>