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
$ward_id = $_GET['ward'];
$Ward = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_ipd_wards WHERE ward_id='$ward_id'"));
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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Stationary Management</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-clipboard"></i> <?= $Ward['ward_name']?> -> Beds</b>
      	</div>
      	<div class="page_scroller">
      		<div class="form-row">
      			<div class="col-sm-12 col-md-4">
      				<a href="Wards.php" class="btn btn-outline-primary btn-block"><i class="oi oi-arrow-left"></i> Back to Wards</a>
      			</div>
      			<div class="col-sm-12 col-md-4">
      				<button class="btn btn-outline-info btn-block" onclick="$('#newBedPopUp').modal('show')"><i class="oi oi-plus"></i> New Bed</button>
      			</div>
      		</div>
      		<table class="table table-bordered table-sm table-striped" style="margin: 10px;">
	      		<thead class="bg-dark text-light">
	      			<th>code</th>
	      			<th>Bed Number</th>	      			
	      			<th>Ward</th>
	      			<th>Status</th>
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

<!--New Consumable-->
<div class="modal fade" id="newBedPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
	<div class="modal-dialog modal-dialog-lg" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary">
				<b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >New Bed</b>
				<button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Bed Number</label>
						<input type="text" class="form-control form-control-sm" id="bed_number">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Ward</label>
						<input class="form-control form-control-sm" id="ward_id" value="<?= $Ward['ward_name']?>" readonly>
					</div>
				</div>
				<div class="row">	
					<div class="form-group col-sm-12 col-md-4">
						<button class="btn btn-outline-success col-12" onclick="SaveBed()">
							<i class="oi oi-check"></i> Save</button>
					</div>			
			      	<div class="form-group col-sm-12 col-md-4">
						<button class="btn btn-outline-danger col-12" onclick="$('#newBedPopUp').modal('hide')" ><i class="oi oi-x"></i> Close
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
		GetBeds();
	});

	function SaveBed(){
		var bed_number = $("#bed_number").val();
		var ward_id = "<?= $ward_id?>";

		if (bed_number=='') {SnackNotice(false,'Enter the bed number');return;}
		if (ward_id=='') {SnackNotice(false,'Select the ward where the bed is used');return;}	
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:{SaveBed:'1',
				bed_number:bed_number,
				ward_id:ward_id},
			success:function(response){
				GetBeds();
				$("#newBedPopUp").modal('toggle');
				SnackNotice(true,response);
			}
		});
	}
	function GetBeds(){
		ward_id = "<?= $_GET['ward']?>"
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:{GetBeds:'1',ward_id:ward_id},
			success:function(response){
				$("#item_list").html(response);
			}
		});
	}
	function DeleteBed(bed_id){
		RitchConfirm("Proceed ?","Delete this bed from the database ?").then(function(){
	        $('#processDialog').modal('toggle');
	        $.ajax({
				method:'POST',
				url:'CRUD.php',
				data:{DeleteBed:'1',bed_id:bed_id},
				success:function(response){
					GetBeds();
					SnackNotice(true,response);
				}
			});
	     });
	}
</script>
</body>
</html>