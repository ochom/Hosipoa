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
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-circle-check"></i> Pending Results Verification</b>
      	</div> 
          <div class="page_scroller">
            <table class="table table-sm table-striped">
              <thead class="bg-dark text-light">
                <th>Reg No.</th>
                <th>Lab No.</th>
                <th>Name</th>
                <th>Investigation</th>
                <th>Result Time</th>
                <th>Actions</th>
              </thead>
              <tbody id="queue_tbody" style="cursor: pointer;">
          <?php 
            include('../ConnectionClass.php');
            $sql = "SELECT * FROM tbl_laboratory_log WHERE status='pending' ORDER BY labno DESC";
            $res = mysqli_query($conn,$sql);
            while ($rowSet = mysqli_fetch_assoc($res)) {
              ?>
                <tr>
                  <td><?= $rowSet['refno']?></td>
                  <td ><?= $rowSet['labno']?></td>
                  <td ><?= $rowSet['patient_name']?></td>
                  <td ><?= $rowSet['investigation']?></td>
                  <td><?= $rowSet['result_date_time']?></td>
                  <td><a class="btn btn-outline-success btn-sm" href="VerifyResults.php?labno=<?=$rowSet['labno']?>"><i class="oi oi-circle-check"></i> Verify Results</a>
                  </td>
                </tr>
              <?php
            }
          ?>
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
  </script>
</body>
</html>