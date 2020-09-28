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
			<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> System Settings</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-briefcase"></i> Insurance Companies</b>
      	</div>
      	<div class="page_scroller">
      		<div class="row">
      			<div class="col-sm-12 col-md-3">
      				<button class="btn btn-success btn-block" onclick="NewCompany()"><i class="oi oi-plus"></i> Add Companny</button>
      			</div>
      		</div>
	      	<table class="table table-bordered table-sm table-striped" style="margin: 10px;">
	      		<thead class="bg-light">
	      			<th>Code</th>
	      			<th>Company Name</th>
	      			<th>Email</th>
	      			<th>Phone</th>
	      			<th>Credit Limit</th>
	      			<th>Status</th>
	      			<th>Actions</th>
	      		</thead>
	      		<tbody id="company_list">
	      			<!--Add from CRUD-->
	      		</tbody>
	      	</table>
       </div>
      </div>
    </div>
</div>
<!--New Company-->
<div class="modal fade" id="newCompanyPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
	<div class="modal-dialog modal-dialog-lg" role="document">
		<div class="modal-content">
			<div class="modal-header bg-success">
				<b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >Company Details</b>
				<button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Company Name</label>
						<input class="form-control form-control-sm" id="company_name">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Email Address</label>
						<input class="form-control form-control-sm" id="company_email">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Phone Number</label>
						<input class="form-control form-control-sm" id="company_phone">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Credit Limit</label>
						<input class="form-control form-control-sm" id="credit_limit">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Status</label>
						<select class="form-control form-control-sm" id="status">
							<option value="Active">Active</option>
							<option value="Suspended">Suspended</option>
						</select>
					</div>
				</div>
				<div class="row">	
					<div class="form-group col-sm-12 col-md-4">
						<button class="btn btn-outline-success col-12" onclick="SaveCompany()">
							<i class="oi oi-check"></i> Save
						</button>
					</div>			
		      	<div class="form-group col-sm-12 col-md-4">
					<button class="btn btn-outline-danger col-12" onclick="$('#newCompanyPopUp').modal('toggle')" ><i class="oi oi-x"></i> Close
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
		GetCompanies();
	});

	var selected_company_id = null;

	function NewCompany(){
		selected_company_id = null;
		$('input').val('');
		$('#newCompanyPopUp').modal('show');
	}

	function SaveCompany(){
		var company_name = $("#company_name").val();
		var company_email = $("#company_email").val();
		var company_phone = $("#company_phone").val();
		var credit_limit = $("#credit_limit").val();
		var status = $("#status").val();

		var Data = null;

		if (company_name=='') {SnackNotice(false,'Enter the company name'); $("#company_name").focus(); return;}
		if (company_email=='') {SnackNotice(false,'Enter the company name'); $("#company_email").focus(); return;}
		if (company_phone=='') {SnackNotice(false,'Enter the company name'); $("#company_phone").focus(); return;}	

		if (selected_company_id == null) {//New Company Save
			Data = {
				SaveCompany:'1',
				company_name:company_name,
				company_email:company_email,
				company_phone:company_phone,
				credit_limit:credit_limit,
				status:status
			};
		}else{//Update company info
			Data = {
				UpdateCompany:'1',
				company_id:selected_company_id,
				company_name:company_name,
				company_email:company_email,
				company_phone:company_phone,
				credit_limit:credit_limit,
				status:status
			};
		}
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:Data,
			success:function(response){
				GetCompanies();
				$("#newCompanyPopUp").modal('hide');
				if (response.includes('success')) { 
	              SnackNotice(true,'Insurance company details saved successfully'); 
	            }else{
	              SnackNotice(false,response);
	            }
			}
		});
	}

	function GetCompanies(){
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:{GetCompanies:'1'},
			success:function(response){
				$("#company_list").html(response);
			}
		});
	}

	//Edit company deils
	function EditCompany(row){
		selected_company_id = row.find('td:nth-child(1)').text();

		$('#company_name').val(row.find('td:nth-child(2)').text());
		$('#company_email').val(row.find('td:nth-child(3)').text());
		$('#company_phone').val(row.find('td:nth-child(4)').text());
		$('#credit_limit').val(row.find('td:nth-child(5)').text());
		$('#status').val(row.find('td:nth-child(6)').text());

		$('#newCompanyPopUp').modal('show');
	}
	function DeleteCompany(company_id){
		RitchConfirm("Proceed ?","Delete this Company from the database ?").then(function(){
      	$('#processDialog').modal('toggle');
		    $.ajax({
				method:'POST',
				url:'CRUD.php',
				data:{DeleteCompany:'1',company_id:company_id},
				success:function(response){
					$('#processDialog').modal('toggle');
					GetCompanies();
					SnackNotice(true,"Company deleted");
				}
			});
	    });
	}
	function ActivateCompany(company_id){
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:{ActivateCompany:'1',company_id:company_id},
			success:function(response){
				GetCompanies();
				SnackNotice(true,"Company activated succesfully");
			}
		});
	}
</script>
</body>
</html>