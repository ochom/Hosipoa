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
          <b><i class="oi oi-collapse-down"></i> Morgues & Morgues Fee</b>
      	</div>
      	<div class="page_scroller">
      		<div class="row">
      			<div class="col-sm-12 col-md-3">
      				<button class="btn btn-warning col-12" data-toggle="modal" data-target="#newMorguePopUp">New Morgue</button>
      			</div>
      		</div>
	      	<table class="table table-bordered table-sm" style="margin: 10px;">
	      		<thead class="bg-light">
	      			<th>code</th>
	      			<th>Morgue Name</th>
	      			<th>Admission Fee <small>(Ksh)</small></th>
	      			<th>Daily Charges <small>(Ksh)</small></th>
	      			<th>Description</th>
	      			<th>Capacity</th>
	      			<th>Actions</th>
	      		</thead>
	      		<tbody id="Morgue_list">
	      			<!--Add from CRUD-->
	      		</tbody>
	      	</table>
       </div>
      </div>
    </div>
</div>
<!--New Morgue-->
<div class="modal fade" id="newMorguePopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
	<div class="modal-dialog modal-dialog-lg" role="document">
		<div class="modal-content">
			<div class="modal-header bg-warning">
				<b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >New Morgue</b>
				<button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Morgue Name</label>
						<input class="form-control form-control-sm" id="morgue_name">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Morgue Admission Fee <small>(Ksh)</small></label>
						<input class="form-control form-control-sm" id="morgue_admission_fee">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Daily Service Fee <small>(Ksh)</small></label>
						<input class="form-control form-control-sm" id="morgue_daily_fee">
					</div>
				</div>
				<div class="form-row">		
					<div class="form-group col-sm-12">
						<label>Morgue Description</label>
						<textarea type="text" class="form-control form-control-sm" id="morgue_des"></textarea>
					</div>		
				</div>	
				<div class="row">	
					<div class="form-group col-sm-12 col-md-4">
						<button class="btn btn-outline-success col-12" onclick="SaveMorgue()">
							<i class="oi oi-check"></i> Save
						</button>
					</div>			
		      	<div class="form-group col-sm-12 col-md-4">
					<button class="btn btn-outline-danger col-12" onclick="$('#newMorguePopUp').modal('toggle')" ><i class="oi oi-x"></i> Close
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
		GetMorgues();
	});

	function SaveMorgue(){
		var morgue_name = $("#morgue_name").val();
		var morgue_admission_fee = $("#morgue_admission_fee").val();
		var morgue_daily_fee= $("#morgue_daily_fee").val();
		var morgue_des= $("#morgue_des").val();


		if (morgue_name =='') {SnackNotice(false,'Enter the morgue name');return;}	
		if (morgue_admission_fee == '') {SnackNotice(false,'Enter the admission fee required for this morgue'); return;}
		if (morgue_daily_fee =='') {SnackNotice(false,'Enter Daily Service fee charged at the morgue');return;}
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:{SaveMorgue:'1',
				morgue_name:morgue_name,
				morgue_admission_fee:morgue_admission_fee,
				morgue_daily_fee:morgue_daily_fee,
				morgue_des:morgue_des},
			success:function(response){
				GetMorgues();
				$("#newMorguePopUp").modal('toggle');
				SnackNotice(true,response);
			}
		});
	}
	function GetMorgues(){
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:{GetMorgues:'1'},
			success:function(response){
				$("#Morgue_list").html(response);
			}
		});
	}
	function DeleteMorgue(Morgue_id){
		RitchConfirm("Proceed ?","Delete this Morgue from the database").then(function() {
			$.ajax({
				method:'POST',
				url:'CRUD.php',
				data:{DeleteMorgue:'1',Morgue_id:Morgue_id},
				success:function(response){
					GetMorgues();
					SnackNotice(true,response);
				}
			});
		});
	}
</script>
</body>
</html>