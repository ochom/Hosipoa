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
          <b><i class="oi oi-clipboard"></i> Stationary & Consumables</b>
      	</div>
      	<div class="page_scroller">
      		<div class="row" style="position: fixed; right: 50px; top: 60px; width: 200px; display: inline-block; z-index: 1;">
      			<div class="form-group">
      				<button class="btn btn-sm btn-primary col-12" onclick="NewItem()"><i class="oi oi-plus"></i> Add Item</button>
      			</div>
      			<div class="form-group">
		      	 	<button class="btn btn-sm btn-success col-12" onclick="Export('Consumables')"><i class="oi oi-file"></i> Export To Excel</button>
      			</div>
      		</div>
      		<div class="row">
      			<div class="form-group col-sm-12" style="margin-bottom: 0px;">
      				<label>Page Size </label>
		      	 	<select onchange="GetItems($(this).val(),$('#pages').val());GetPages();" id="page_size">
		      		<?php
		      			for ($i=5; $i < 35; $i += 5) { 
		      				echo "<option value='$i'>$i</option>";	
		      			}
		      		?>
		      		</select>
		      	 	<label>Pages </label>
		      	 	<select onchange="GetItems($('#page_size').val(),$(this).val())" id="pages">
		      	 		<!-- crud -->
		      		</select>
      			</div>
      		</div>
	      	<table class="table table-bordered table-sm table-striped" style="margin: 10px;">
	      		<thead class="bg-dark text-light">
	      			<th>#</th>
	      			<th>Stationary ID</th>
	      			<th>Stationary Name</th>
	      			<th>Stationary Tag</th>	
	      			<th>Condition</th>
	      			<th>Department</th>
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
<!--New Stationary-->
<div class="modal fade" id="newItemPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
	<div class="modal-dialog modal-dialog-lg" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary">
				<b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >Stationary Details</b>
				<button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Stationary Name</label>
						<input type="text" class="form-control form-control-sm" id="item_name">
					</div>
					<div class="form-group col-sm-12">
						<label>Stationary Tag</label>
						<input class="form-control form-control-sm" id="item_des">
					</div>
					<div class="form-group col-sm-12">
						<label>Condition</label>
						<select class="form-control form-control-sm" id="item_condition">
							<option value="">Select</option>
							<option value="Good">Good</option>
							<option value="Broken">Broken</option>
							<option value="Under Maintainace">Under Maintainace</option>
						</select>
					</div>
					<div class="form-group col-sm-12 text-primary">
						<label>Assigned Department</label>
						<select class="form-control form-control-sm"  id="service_point">
			                <option value="">Department</option>
			            <?php
			                 $rows = $db->ReadArray("SELECT * FROM tbl_service_points  ORDER BY sp_name ASC");
			                 foreach($rows as $row): ?>
			                  	<option value="<?= $row['sp_name']?>"><?= $row['sp_name']?></option>
			            <?php endforeach; ?>
		              	</select>
					</div>
					<div class="form-group col-sm-12 col-md-4">
						<button class="btn btn-outline-success col-12" onclick="SaveItem()">
						<i class="oi oi-check"></i> Save</button>
					</div>			
			      	<div class="form-group col-sm-12 col-md-4">
						<button class="btn btn-outline-danger col-12" onclick="$('#newItemPopUp').modal('toggle')" ><i class="oi oi-x"></i> Close
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
    var page = 1, page_size=5;
	$(document).ready(function(){
		GetPages();
		GetItems(page_size,page);
	});

	setInterval(function(){
		page_size = $('#page_size').val();
		page = $('#pages').val();
		GetItems(page_size,page);
	},2000);

	function GetPages(){
		page_size = $('#page_size').val();
		$.ajax({
			method:'post',url:'crud.php',
			data:{GetPages:'1',page_size:page_size,item_type:'Stationary'},
			success:function(response){
				response = parseInt(response);
				console.log(response);
				var pages = "";
				for (var i = 1; i < response+1; i++) {
					pages += "<option value='"+i+"'>Page "+i+"</option>";
				}
				$('#pages').html(pages);
			}
		});
	}


	function GetItems(ps,pg){
		page_size = ps;
		page = pg;
		RichUrl($('#item_list'),{GetStationary:'1',page:page,page_size:page_size});
	}

	var selected_item_code = null;

	function NewItem(){
		selected_item_code = null;
		$('.form-control').val('');
		$('#newItemPopUp').modal('toggle');
	}

	function EditItem(row){
		selected_item_code = row.find('td:nth-child(2)').text();

		$('#item_name').val(row.find('td:nth-child(3)').text());
		$('#item_des').val(row.find('td:nth-child(4)').text());
		$('#item_condition').val(row.find('td:nth-child(5)').text());
		$('#service_point').val(row.find('td:nth-child(6)').text());

		$('#newItemPopUp').modal('toggle');
	}

	function SaveItem(){
		var item_name = $("#item_name").val();
		var item_type = 'Stationary';
		var item_des= $("#item_des").val();
		var item_condition= $("#item_condition").val();
		var service_point = $("#service_point").val();


		if (item_name=='') {SnackNotice(false,'Enter the Stationary Name');return;}
		if (item_des=='') {SnackNotice(false,'Enter the Stationary Tag');return;}	
		if (item_condition=='') {SnackNotice(false,'Enter the current condition of the Stationary');return;}
		if (service_point=='') {SnackNotice(false,'Select the Department where this Stationary has been assigned to.');return;}

		var Data = null;
		if (selected_item_code == null) {	
		//New item
			Data = {
				SaveStationary:'1',
				item_name:item_name,
				item_des:item_des,
				item_condition:item_condition,
				service_point:service_point
			};
		}else{//Update item
			Data = {
				UpdateStationary:'1',
				item_code:selected_item_code,
				item_name:item_name,
				item_des:item_des,
				item_condition:item_condition,
				service_point:service_point
			};
		}

		$('#processDialog').modal('toggle');
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:Data,
			success:function(response){
				GetItems(page_size,page);
				if (response.includes('success')) {
					SnackNotice(true,'Stationary details saved successfully');
					$('#newItemPopUp').modal('toggle');
				}else{
		          SnackNotice(false,response);
		        }
				$('#processDialog').modal('toggle');
			},
		});
	}

	function DeleteItem(item_code){
		RitchConfirm("Proceed ?","Delete this Item from the database").then(function() {
			$('#processDialog').modal('toggle');
			$.ajax({
				method:'POST',
				url:'CRUD.php',
				data:{DeleteItem:'1',item_code:item_code},
				success:function(response){
					$('#processDialog').modal('toggle');
					GetItems(page_size,page);
					SnackNotice(true,response);
				},
			});
		});
	}
</script>
</body>
</html>