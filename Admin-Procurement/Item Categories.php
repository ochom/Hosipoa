<?php
include('../ConnectionClass.php');
include('../db_class.php');
session_start();

$db = new CRUD();

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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Procurement</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-pencil"></i> Drugs, Services and Item Categories</b>
      	</div>
      	<div class="page_scroller">
      		<div class="row">
      			<div class="col-sm-12 col-md-3">
      				<button class="btn btn-primary col-12" onclick="Newcat()"><i class="oi oi-plus"></i> Add Category</button>
      			</div>
      		</div>
	      	<table class="table table-bordered table-sm table-striped" style="margin: 10px;">
	      		<thead class="bg-dark text-light">
	      			<th>#</th>
	      			<th>Category</th>
	      			<th>Items In Category</th>
	      			<th>Sub Category</th>
	      			<th>Action</th>
	      		</thead>
	      		<tbody id="cat_list">
	      			<!--Add from CRUD-->
	      		</tbody>
	      	</table>
       </div>
      </div>
    </div>
</div>

<!--New Cat-->
<div class="modal fade" id="newCatPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
	<div class="modal-dialog modal-dialog-lg" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary">
				<b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" > Register Category</b>
				<button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Category Name</label>
						<input type="text" class="form-control form-control-sm" id="cat_name">
					</div>	
					<div class="form-group col-sm-12">
						<label>Items in this Category</label>
						<select class="form-control form-control-sm" id="cat_for">
							<option value="">Select</option>
							<option value="Drugs">Drugs</option>
							<option value="Laboratory Services">Laboratory Services</option>
							<option value="Radiology Services">Radiology Services</option>
							<option value="Theatre Procedures">Theatre Procedures</option>
						</select>
					</div>		
					<div class="form-group col-sm-12 col-md-4">
						<button class="btn btn-outline-success col-12" onclick="SaveCat()"><i class="oi oi-check"></i> Save</button>
					</div>		
				</div>
			</div>
		</div>
	</div>	
</div>

<!--New Cat-->
<div class="modal fade" id="newSubCatPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
	<div class="modal-dialog modal-dialog-lg" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary">
				<b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" > Register Sub Category</b>
				<button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Category Name</label>
						<input type="text" class="form-control form-control-sm" id="sub_cat_cat_name" readonly>
					</div>	
					<div class="form-group col-sm-12">
						<label>Sub Category Name</label>
						<input type="text" class="form-control form-control-sm" id="sub_cat_name">
					</div>			
					<div class="form-group col-sm-12 col-md-4">
						<button class="btn btn-outline-success col-12" onclick="SaveSubCat()"><i class="oi oi-check"></i> Save</button>
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
		Getcats();
	});

	setInterval(function(){
		Getcats();
	},2000);

	var selected_cat_code = null,selected_sub_cat_code = null;;

	function Newcat(){
		selected_cat_code = null;
		$('.form-control').val('');
		$('#newCatPopUp').modal('toggle');
	}

	function Editcat(row){
		selected_cat_code = row.find('td:nth-child(1)').text();
		$('#cat_name').val(row.find('td:nth-child(2)').text());
		$('#cat_for').val(row.find('td:nth-child(3)').text());
		$('#newCatPopUp').modal('toggle');
	}

	function NewSubcat(cat_id,cat_name){
		selected_cat_code = cat_id;
		selected_sub_cat_code = null;
		$('.form-control').val('');
		$('#sub_cat_cat_name').val(cat_name.text());
		$('#newSubCatPopUp').modal('toggle');
	}

	function EditSubcat(sub_cat_id,cat_name,sub_cat_name){
		$('.form-control').val('');
		selected_sub_cat_code = sub_cat_id;
		$('#sub_cat_cat_name').val(cat_name);
		$('#sub_cat_name').val(sub_cat_name);
		$('#newSubCatPopUp').modal('toggle');
	}


	function SaveCat(){
		var cat_name = $("#cat_name").val();
		var cat_for = $("#cat_for").val();


		if (cat_name=='') {SnackNotice(false,'Enter the category name'); $("#cat_name").focus(); return;}
		if (cat_for=='') {SnackNotice(false,'Specify the Item for which this category belong'); $("#cat_for").focus(); return;}

		var Data = null;
		if (selected_cat_code === null) {
		//New cat
			Data = {
				SaveCat:'1',
				cat_name:cat_name,
				cat_for:cat_for
			};
		}else{//Update cat
			Data = {
				UpdateCat:'1',
				cat_code:selected_cat_code,
				cat_name:cat_name,
				cat_for:cat_for
			};
		}
		$('#processDialog').modal('toggle');
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:Data,
			success:function(response){
				console.log(response);
				$('#processDialog').modal('hide');
				Getcats();
				$('#newCatPopUp').modal('toggle');
				if (response.includes('success')) {
					SnackNotice(true,(selected_cat_code === null)?'Category details saved successfully':'Category details updated successfully');
				}else{
		          SnackNotice(false,response);
		        }
			},
		});
	}

	function SaveSubCat(){
		var sub_cat_name = $("#sub_cat_name").val();


		if (sub_cat_name=='') {SnackNotice(false,'Enter the sub category name'); $("#sub_cat_name").focus(); return;}

		var Data = null;
		if (selected_sub_cat_code === null) {
		//New cat
			Data = {
				SaveSubCat:'1',
				cat_id:selected_cat_code,
				sub_cat_name:sub_cat_name
			};
		}else{//Update cat
			Data = {
				UpdateSubCat:'1',
				cat_id:selected_cat_code,
				sub_cat_code:selected_sub_cat_code,
				sub_cat_name:sub_cat_name
			};
		}
		$('#processDialog').modal('toggle');
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:Data,
			success:function(response){
				console.log(response);
				$('#processDialog').modal('hide');
				Getcats();
				$('#newSubCatPopUp').modal('toggle');
				if (response.includes('success')) {
					SnackNotice(true,(selected_sub_cat_code === null)?'Sub Category details saved successfully':'Sub Category details updated successfully');
				}else{
		          SnackNotice(false,response);
		        }
			},
		});
	}


	function Getcats(){
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:{GetCats:'1'},
			success:function(response){
				$("#cat_list").html(response);
			}
		});
	}
</script>
</body>
</html>