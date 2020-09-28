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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Procurement, Procedures & Stationary Management</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-beaker"></i> Medical Procedures</b>
      	</div>
      	<div class="page_scroller">
      		<div class="row" style="position: fixed; right: 50px; top: 60px; width: 200px; display: inline-block; z-index: 1;">
      			<div class="form-group">
      				<button class="btn btn-sm btn-primary col-12" onclick="NewProcedure()"><i class="oi oi-plus"></i> Add Procedure</button>
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
	      			<th>code</th>
	      			<th>Procedure Name</th>	  
	      			<th>Cash <small>(Ksh)</small></th> 
	      			<th>Corporate <small>(Ksh)</small></th> 
	      			<th>Payment</th>  	
	      			<th>System</th>      			
	      			<th>Sub System</th>      			
	      			<th>ICD 10</th>
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
<!--New Item-->
<div class="modal fade" id="newItemPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
	<div class="modal-dialog modal-dialog-lg" role="document">
		<div class="modal-content" style="width: 800px; margin-left: calc((100% - 700px)/2);">
			<div class="modal-header bg-primary">
				<b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" > Medical Procedure</b>
				<button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-row">
					<div class="form-group col-8">
						<label>Procedure Name</label>
						<input type="text" class="form-control form-control-sm" id="item_name">
					</div>
					<div class="form-group col-4 text-danger">
						<label>ICD 10 Procedure Code</label>
						<input class="form-control form-control-sm" id="item_des">
					</div>
					<div class="form-group col-3 text-danger">
						<label>Cash <small>(Ksh)</small></label>
						<input class="form-control form-control-sm" id="item_rate_cash">
					</div>	
					<div class="form-group col-3 text-danger">
						<label>Corporate <small>(Ksh)</small></label>
						<input class="form-control form-control-sm" id="item_rate_cop">
					</div>	
					<div class="form-group col-3 text-danger">
						<label>NHIF Rebate <small>(Ksh)</small></label>
						<input class="form-control form-control-sm" id="nhif_rebate">
					</div>	
					<div class="form-group col-3 text-danger">
						<label>Payments</label>
						<select class="form-control form-control-sm" id="cop_payment">
							<option value="">Cash Only</option>
							<option value="Yes">Both Cash and Corporate</option>
						</select>
					</div>
					<div class="form-group col-sm-6">
						<label>System</label>
						<select class="form-control form-control-sm" id="item_category" onchange="GetSubcategories($(this).val())">
		                    <option value="">Select</option>
		                    <?php 
		                      $res = mysqli_query($conn,"SELECT * FROM tbl_item_drug_lab_types WHERE cat_for='Theatre Procedures' ORDER BY cat_name ASC");
		                      while ($Type = mysqli_fetch_assoc($res)) {
		                        ?>
		                        <option value="<?= $Type['id']?>"><?= $Type['cat_name']?></option>
		                        <?php
		                      }
		                    ?>
		                 </select>
					</div>
					<div class="form-group col-sm-6">
						<label>Sub System</label>
						<select class="form-control form-control-sm" id="item_sub_category">
		                    <option value="">Select</option>
		                    <!-- FROM CRUD -->
		                 </select>
					</div>		
					<div class="form-group col-sm-12 col-md-4">
						<button class="btn btn-outline-success col-12" onclick="SaveProcedure()"><i class="oi oi-check"></i> Save</button>
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
			data:{GetPages:'1',page_size:page_size,item_type:'Medical Procedure'},
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
		RichUrl($('#item_list'),{GetMedicalProcedures:'1',page:page,page_size:page_size});
	}
	
	var selected_item_code = null;

	function NewProcedure(){
		selected_item_code = null;
		$('.form-control').val('');
		$('#newItemPopUp').modal('toggle');
	}
	function EditItem(item_code){
		$('.form-control').val('');
		selected_item_code = item_code;
		$.ajax({
			method:'post',
			url:'crud.php',
			data:{GetItemProps:'1',item_code:selected_item_code},
			success:function(response){
				var item  = JSON.parse(response);
				$("#item_name").val(item.item_name);
				$("#item_des").val(item.item_des);	
				$("#item_rate_cash").val(item.item_rate_cash);	
				$("#item_rate_cop").val(item.item_rate_cop); 
				$("#nhif_rebate").val(item.nhif_rebate);
				$("#cop_payment").val(item.cop_payment);				
				$("#item_category").val(item.item_category);
				GetSubcategories(item.item_category,item.item_sub_category);

				$('#newItemPopUp').modal('show');
			}

		});
	}

	function GetSubcategories(cat_id,sub_cat_id){
		$.ajax({
			method:'post',
			url:'crud.php',
			data:{GetSubcategories:'1',cat_id:cat_id},
			success:function(response){
				$('#item_sub_category').html(response);
				$('#item_sub_category').val(sub_cat_id);
			}
		});
	}

	function SaveProcedure(){
		var item_name = $("#item_name").val();
		var item_des = $('#item_des').val();
		var item_type= 'Medical Procedure';
		var item_rate_cash = $("#item_rate_cash").val();
		var item_rate_cop= $("#item_rate_cop").val(); 
		var nhif_rebate= $("#nhif_rebate").val();
		var cop_payment= $("#cop_payment").val();
		var item_category = $('#item_category').val();
		var item_sub_category = $('#item_sub_category').val();

		if (item_name=='') {SnackNotice(false,'Enter the Service name');$("#item_name").focus();return;}
		if (item_rate_cash=='' || isNaN(item_rate_cash)) {SnackNotice(false,'Enter the numeric cost for Cash Clients');$("#item_rate_cash").focus();return;}	
		if ((item_rate_cop=='' || isNaN(item_rate_cop)) && cop_payment=='Yes') {SnackNotice(false,'Enter the numeric cost for Corporate Clients');$("#item_rate_cop").focus();return;}	
		
		item_rate_cash = (+item_rate_cash).toFixed(2);
		item_rate_cop = (item_rate_cop>0)?(+item_rate_cop).toFixed(2):'';

		var Data = null;
		if (selected_item_code == null) {//New Procedure Save
			Data = {
				SaveProcedure:'1',
				item_name:item_name,
				item_des:item_des,
				item_type:item_type, 
				item_rate_cash:item_rate_cash,
				item_rate_cop:item_rate_cop,
				nhif_rebate:nhif_rebate,
				cop_payment:cop_payment,
				item_category:item_category,
				item_sub_category:item_sub_category
			};
		}else{//Update ward info
			Data = {
				UpdateProcedure:'1',
				item_code:selected_item_code,
				item_name:item_name,
				item_des:item_des,
				item_rate_cash:item_rate_cash,
				item_rate_cop:item_rate_cop,
				nhif_rebate:nhif_rebate,
				cop_payment:cop_payment,
				item_category:item_category,
				item_sub_category:item_sub_category
			};
		}

		$('#processDialog').modal('toggle');
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:Data,
			success:function(response){
				$('#processDialog').modal('toggle');
				GetItems(page_size,page);
				if (response.includes('success')) {
					SnackNotice(true,(selected_item_code === null)?'Procedure details saved successfully':'Procedure details updated successfully');
					$('#newItemPopUp').modal('toggle');
				}else{
		          SnackNotice(false,response);
		        }
			}
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