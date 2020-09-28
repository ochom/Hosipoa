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
          <b><i class="oi oi-people"></i> Suppliers</b>
      	</div>
      	<div class="page_scroller">
      		<button class="btn btn-primary" onclick="NewSupplier()"><i class="oi oi-plus"></i> New Supplier</button>
	      	<table class="table table-bordered table-sm table-striped" style="margin: 10px;">
	      		<thead class="bg-dark text-light">
	      			<th>code</th>
	      			<th>Supplier Name</th>	      			
	      			<th>Email/Postal Address</th>
	      			<th>Phone Number</th>
	      			<th>Town</th>
	      			<th>Actions</th>
	      		</thead>
	      		<tbody id="supplier_list">
	      			<!--Add from CRUD-->
	      		</tbody>
	      	</table>
       </div>
      </div>
    </div>
</div>
<!--New Supplier-->
<div class="modal fade" id="newSupplierPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
	<div class="modal-dialog modal-dialog-lg" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary">
				<b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >Supplier Details</b>
				<button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Supplier Name</label>
						<input type="text" class="form-control form-control-sm" id="supplier_name">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Postal/Email Address</label>
						<input type="text" class="form-control form-control-sm" id="address">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Phone Number</label>
						<input type="text" class="form-control form-control-sm" id="phone">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>City/Town</label>
						<input type="text" class="form-control form-control-sm" id="town">
					</div>
				</div>
				<div class="row">	
					<div class="form-group col-sm-12 col-md-4">
						<button class="btn btn-outline-success col-12" onclick="SaveSupplier()">
							<i class="oi oi-check"></i> Save</button>
					</div>			
			      	<div class="form-group col-sm-12 col-md-4">
						<button class="btn btn-outline-danger col-12" onclick="$('#newSupplierPopUp').modal('toggle')" ><i class="oi oi-x"></i> Close
						</button>
				</div>	
			</div>
			</div>
		</div>
	</div>	
</div>

<!-- Proccessing dialog -->
<div class="modal modal-static" id="processDialog" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" >
    <div style="background-color: #eee;" id="progressBar"><div class="box2"></div></div>  
</div>

 <!-- Menu Toggle Script -->
  <script>
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });
    
	$(document).ready(function(){
		GetSuppliers();
	});


	var selected_supplier_code = null;

	function NewSupplier(){
		selected_supplier_code = null;
		$('input').val('');
		$('#newSupplierPopUp').modal('show');
	}
	function EditSupplier(row){
		selected_supplier_code = row.find('td:nth-child(1)').text();

		$("#supplier_name").val(row.find('td:nth-child(2)').text());
		$("#address").val(row.find('td:nth-child(3)').text());
		$("#phone").val(row.find('td:nth-child(4)').text());
		$("#town").val(row.find('td:nth-child(5)').text());

		$('#newSupplierPopUp').modal('show');

	}

	function SaveSupplier(){
		var supplier_name = $("#supplier_name").val();
		var address =  $("#address").val();	
		var phone =  $("#phone").val();	
		var town =  $("#town").val();	


		if (supplier_name=='') {SnackNotice(false,'Enter the supplier name');return;}
		if (address=='') {SnackNotice(false,'Enter the Postal or Email Address of the supplier');return;}	
		if (phone=='') {SnackNotice(false,'Enter Phone Number of the suplier');return;}	
		if (town=='') {SnackNotice(false,'Enter City or Town of the Supplier');return;}

		var Data = null;
		if (selected_supplier_code == null) {//New supplier
			Data = {
				SaveSupplier:'1',
				supplier_name:supplier_name,
				address:address,
				phone:phone,
				town:town
			};
		}else{//Update supplier
			Data = {
				UpdateSupplier:'1',
				supplier_code:selected_supplier_code,
				supplier_name:supplier_name,
				address:address,
				phone:phone,
				town:town
			};
		}
		$('#processDialog').modal('show');
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:Data,
			success:function(response){
				GetSuppliers();
				if (response.includes('success')) {
					SnackNotice(true,'Supplier Details successfully saved');
					$('#newSupplierPopUp').modal('hide');
				}else{
	              SnackNotice(false,response);
	            }
				$('#processDialog').modal('hide');
			},
		});
	}
	function GetSuppliers(){
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:{GetSuppliers:'1',supplier_type:'Supplier'},
			success:function(response){
				$("#supplier_list").html(response);
			}
		});
	}
	function DeleteSupplier(supplier_code){
		RitchConfirm("Proceed ?","Delete this Supplier from the database").then(function() {
			$('#processDialog').modal('show');
			$.ajax({
				method:'POST',
				url:'CRUD.php',
				data:{DeleteSupplier:'1',supplier_code:supplier_code},
				success:function(response){
					$('#processDialog').modal('hide');
					GetSuppliers();
					SnackNotice(true,response);
				},
			});
		});
	}
</script>
</body>
</html>