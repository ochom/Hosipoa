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

  if (isset($_GET['item_code'])) {
    include('../ConnectionClass.php');
    $item_code = $_GET['item_code'];
    $sql = "SELECT * From tbl_item where item_code= '$item_code' ";
    $Order = mysqli_fetch_array(mysqli_query($conn, $sql),MYSQLI_ASSOC);
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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Procurement, Service & Stationary Management</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div style="width: 100%; background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-graph"></i> Item Dispatch</b>
      	</div> 
        <div class="row">
          <div class="col-sm-9 col-md-6 col-lg-6" style="height: auto; padding: 10px 20px; border-radius: 5px; border:1px solid #ccc; margin:auto;">
          	<div class="row">
	          	<div class="form-group col-sm-12 col-md-4">
	                <label>Code</label>
	                <input class="form-control form-control-sm" type="text" id="item_code" value="<?= $Order['item_code']?>" readonly>
	         	</div>
	            <div class="form-group col-sm-12 col-md-8">
	                <label>Item Name</label>
	                <input class="form-control form-control-sm" type="text" id="item_name" readonly value="<?= $Order['item_name']?>">
	            </div>
            </div>
	        <div class="row">
	          	<div class="form-group col-sm-12 col-md-6">
	                <label>Stock Quantity (Boxes)</label>
	                <input class="form-control form-control-sm" type="text" id="stock_quantity" readonly value="<?= $Order['item_quantity']?>">
	         	</div>
	            <div class="form-group col-sm-12 col-md-6">
	                <label>Dispatch Quantity (Boxes)</label>
	                <input class="form-control form-control-sm" type="text" id="dispatch_quantity" >
	            </div>
            </div>
            <div class="row">
              <div class="form-group col-sm-12 col-md-8">
                <label>Receiving Person</label>
                <input class="form-control form-control-sm" type="text" id="receiving_officer" >
              </div>
          	</div>
            <div class="row">
              <div class="form-group col-sm-12 col-md-5 col-lg-4">
                <button class="btn btn-success col-12" id="btnSave" onclick="DispatchItem()"><i class="oi oi-check"></i> Dispatch</button>
              </div>
              <div class="form-group col-sm-12 col-md-5 col-lg-4">
                <a href="Items.php" class="btn btn-danger col-12" id="btnSave"><i class="oi oi-x"></i> Close</a>
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
</body>
</html>