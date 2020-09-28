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
if (!($User_level=='admin' || $GroupPrivileges['procurement_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
}
include('../ConnectionClass.php');
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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Procurement, Service & Stationary Management</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-document"></i> Goods Receive Note</b>
      	</div>
      	<div class="page_scroller">
	      	<table class="table table-bordered table-sm table-striped" style="margin: 10px;">
	      		<thead class="bg-dark text-light">
	      			<th>Item code</th>
	      			<th>Item Name</th> 
	      			<th>Quantity</th>
	      			<th>Total Pieces</th>
	      			<th>Actions</th>
	      		</thead>
	      		<tbody id="item_list">
      			<?php
      				$items = mysqli_query($conn,"SELECT * FROM tbl_item WHERE item_code IN (SELECT item_code FROM tbl_item_flow) AND item_type != 'Drug' ORDER BY item_name ASC");
      				while($Item = mysqli_fetch_assoc($items)){
  					?>
  					<tr>
  						<td><?= $Item['item_code']?></td>
  						<td><?= $Item['item_name']?></td>
  						<td><?= $Item['item_quantity']?></td>
  						<td><?= $Item['total_pieces']?></td>
  						<td><button class="btn btn-outline-primary btn-sm" onclick="var w = window.open('GRN print.php?item_code=<?= $Item['item_code']?>'); w.focus();"><i class="oi oi-print"></i> Print G.R.N</button></td>
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

 <!-- Menu Toggle Script -->
  <script>
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });
</script>
</body>
</html>