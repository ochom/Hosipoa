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
          <b><i class="oi oi-pulse"></i> Wards & Wards Fee</b>
      	</div>
      	<div class="page_scroller">
      		<div class="row">
      			<div class="col-sm-12 col-md-3">
      				<button class="btn btn-warning btn-block" onclick="NewWard()"><i class="oi oi-plus"></i> New Ward</button>
      			</div>
      		</div>
	      	<table class="table table-bordered table-sm table-striped" style="margin: 10px;">
	      		<thead class="bg-dark text-light">
	      			<th>#</th>
	      			<th>Ward Name</th>
	      			<th>Admission (Cash) <small>(Ksh)</small></th>
	      			<th>Admission (Corporate) <small>(Ksh)</small></th>
	      			<th>Daily Charges (Cash) <small>(Ksh)</small></th>
	      			<th>Daily Charges (Corporate) <small>(Ksh)</small></th>
	      			<th>Payment</th>
	      			<th>Bed Capacity</th>
	      			<th>Current Capacity</th>
	      			<th>Actions</th>
	      		</thead>
	      		<tbody id="ward_list">
	      			<!--Add from CRUD-->
	      		</tbody>
	      	</table>
       </div>
      </div>
    </div>
</div>
<!--New Ward-->
<div class="modal fade" id="newWardPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
	<div class="modal-dialog modal-dialog-lg" role="document">
		<div class="modal-content" style="width: 800px; margin-left: calc((100% - 800px)/2);">
			<div class="modal-header bg-warning">
				<b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >Ward Details</b>
				<button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Ward Name</label>
						<input class="form-control form-control-sm" id="ward_name">
					</div>
					<div class="form-group col-sm-6">
						<label>Admission Fee (Cash) <small>(Ksh)</small></label>
						<input class="form-control form-control-sm" id="ward_admin_cash">
					</div>
					<div class="form-group col-sm-6">
						<label>Admission Fee (Corporate) <small>(Ksh)</small></label>
						<input class="form-control form-control-sm" id="ward_admin_cop">
					</div>
					<div class="form-group col-sm-6">
						<label>Daily Accomodation Fee (Cash) <small>(Ksh)</small></label>
						<input class="form-control form-control-sm" id="ward_rate_cash">
					</div>
					<div class="form-group col-sm-6">
						<label>Daily Accomodation Fee (Corporate) <small>(Ksh)</small></label>
						<input class="form-control form-control-sm" id="ward_rate_cop">
					</div>
					<div class="form-group col-sm-6 text-danger">
						<label>Payments</label>
						<select class="form-control form-control-sm" id="cop_payment">
							<option value="">Cash Only</option>
							<option value="Yes">Both Cash and Corporate</option>
						</select>
					</div>			
					<div class="form-group col-sm-6">
						<label>Bed Capacity</label>
						<input class="form-control form-control-sm" id="bed_capacity">
					</div>			
					<div class="form-group col-sm-12 col-md-12">
						<button class="btn btn-outline-success col-12" onclick="SaveWard()">
							<i class="oi oi-check"></i> Save
						</button>
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
    
	$(document).ready(function(){
		GetWards();
	});

	setInterval(function(){
		GetWards();
	},2000);

	var selected_ward_id = null;

	//Edit ward deils
	function EditWard(ward_id){
		$('.form-control').val('');
		selected_ward_id = ward_id;
	    $.ajax({
	        method:'post',
	        url:'crud.php',
	        data:{GetWardProps:'1',ward_id:ward_id},
	        success:function(respose){
	            respose = JSON.parse(respose);
				$('#ward_name').val(respose.ward_name);
				$('#ward_admin_cash').val(respose.ward_admin_cash);
				$('#ward_admin_cop').val(respose.ward_admin_cop);
				$('#ward_rate_cash').val(respose.ward_rate_cash);
				$('#ward_rate_cop').val(respose.ward_rate_cop);
				$('#cop_payment').val(respose.cop_payment);
				$('#bed_capacity').val(respose.bed_capacity);
				$('#newWardPopUp').modal('show');
	        }
	    });
	}

	function NewWard(){
		selected_ward_id = null;
		$('.form-control').val('');
		$('#newWardPopUp').modal('show');
	}

	function SaveWard(){
		var ward_name = $("#ward_name").val();
		var ward_admin_cash = $("#ward_admin_cash").val();
		var ward_admin_cop = $("#ward_admin_cop").val();
		var ward_rate_cash= $("#ward_rate_cash").val();
		var ward_rate_cop= $("#ward_rate_cop").val();
		var cop_payment= $("#cop_payment").val();
		var bed_capacity= $("#bed_capacity").val();
		console.log(cop_payment);
		var Data = null;

		if (ward_name =='') {SnackNotice(false,'Enter the ward name');return;}	
		if (ward_admin_cash == '') {SnackNotice(false,'Enter the admission fee required for this ward');$("#ward_admin_cash").focus(); return;}
		if (ward_rate_cash =='') {SnackNotice(false,'Enter Daily accomodation fee charged at the ward');$("#ward_rate_cash").focus(); return;}
		if ((ward_admin_cop=='' || isNaN(ward_admin_cop) || ward_rate_cop=='' || isNaN(ward_rate_cop))  && cop_payment=='Yes') {SnackNotice(false,'Enter the numeric costs for Corporate Clients');$("#item_rate_cop").focus();return;}
		if (bed_capacity =='' || isNaN(bed_capacity)) {SnackNotice(false,'Enter numeric Bed Capacity of the ward');$("#bed_capacity").focus(); return;}

		if (selected_ward_id == null) {//New Ward Save
			Data = {
				SaveWard:'1',
				ward_name:ward_name,
				ward_admin_cash:ward_admin_cash,
				ward_admin_cop:ward_admin_cop,
				ward_rate_cash:ward_rate_cash,
				ward_rate_cop:ward_rate_cop,
				cop_payment:cop_payment,
				bed_capacity:bed_capacity
			};
		}else{//Update ward info
			Data = {
				UpdateWard:'1',
				ward_id:selected_ward_id,
				ward_name:ward_name,
				ward_admin_cash:ward_admin_cash,
				ward_admin_cop:ward_admin_cop,
				ward_rate_cash:ward_rate_cash,
				ward_rate_cop:ward_rate_cop,
				cop_payment:cop_payment,
				bed_capacity:bed_capacity
			};
		}
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:Data,
			success:function(response){
				if (response.includes('success')) {
					SnackNotice(true,'Ward details saved');
				}else{
					SnackNotice(false,response);
				}
				$("#newWardPopUp").modal('hide');
				GetWards();
			}
		});
	}

	function GetWards(){
		RichUrl($("#ward_list"),{GetWards:'1'});
	}

	

	function DeleteWard(ward_id){
		RitchConfirm("Proceed ?","Delete this Ward from the database").then(function() {
			$.ajax({
				method:'POST',
				url:'CRUD.php',
				data:{DeleteWard:'1',ward_id:ward_id},
				success:function(response){
					if (response.includes('success')) {
						SnackNotice(true,'Ward details deleted');
					}else{
						SnackNotice(false,response);
					}
					GetWards();
				}
			});
		});
	}
</script>
</body>
</html>