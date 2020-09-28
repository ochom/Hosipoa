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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Accounts</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-monitor"></i> Account Categories</b>
        </div>
        <div class="row" style="position: fixed; right: 50px; top: 60px; width: 200px; display: inline-block; z-index: 1;">
  			<div class="form-group">
  				<button class="btn btn-sm btn-primary col-12" onclick="NewItem()"><i class="oi oi-plus"></i> Add Category</button>
  			</div>
  			<div class="form-group">
	      	 	<button class="btn btn-sm btn-success col-12" onclick="Export('Laboratory Services')"><i class="oi oi-file"></i> Export To Excel</button>
  			</div>
  		</div>
        <div class="page_scroller">
			<table class="table table-bordered table-sm table-striped" style="margin: 5px;">
	      		<thead class="bg-dark text-light">
	      			<th>#</th>
	      			<th>Account Category</th>
	      			<th>Account Type</th>	
	      			<th>Description</th>
	      			<th>Actions</th>
	      		</thead>
	      		<tbody id="item_list">
	      			<!--Add from CRUD-->
	      		</tbody>
	      	</table>
        </div>
    </div>
  </div>
</div>


<div class="modal fade" id="newItemPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
	<div class="modal-dialog modal-dialog-lg" role="document">
		<div class="modal-content" style="width: 600px; margin-left: calc((100% - 600px)/2);">
			<div class="modal-header bg-primary">
				<b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >Category Details</b>
				<button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-row">
					<div class="form-group col-12">
						<label>Category Name</label>
						<input class="form-control form-control-sm">
					</div>
					<div class="form-group col-12">
						<label>Type</label>
						<select class="form-control form-control-sm">
							<option value="">Select</option>
							<option value="Creditors">Creditors (Suppliers)</option>
							<option value="Debitors">Debitors (Customers)</option>
						</select>
					</div>
					<div class="form-group col-12">
						<label>Description</label>
						<textarea class="form-control form-control-sm"></textarea>
					</div>
					<div class="form-group col-12">
						<button class="btn btn-sm btn-success" onclick="SaveAccount()"><i class="oi oi-check"></i> Save</button>
					</div>
				</div>
			</div>
		</div>
	</div>	
</div>

	<script>
	    $("#menu-toggle").click(function(e) {
	      e.preventDefault();
	      $("#wrapper").toggleClass("toggled");
	    });

		function NewItem(){
			selected_item_code = null;
			$('.form-control').val('');
			$('#newItemPopUp').modal('toggle');
		}
	</script>
</body>
</html>