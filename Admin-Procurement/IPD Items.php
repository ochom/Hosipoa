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
          <b><i class="oi oi-eyedropper"></i> IPD Items</b>
      	</div>
      	<div class="page_scroller">
      		<div class="row">
      			<div class="col-sm-12 col-md-3">
      				<button class="btn btn-primary col-12" onclick="NewItem()"><i class="oi oi-plus"></i> New IPD Item</button>
      			</div>
      			<div class="col-sm-12 col-md-3">
		      	 <button class="btn btn-success col-12" onclick="Export('Laboratory Services')"><i class="oi oi-file"></i> Export To Excel</button>
      			</div>
      		</div>
	      	<table class="table table-bordered table-sm table-striped" style="margin: 10px;">
	      		<thead class="bg-dark text-light">
	      			<th>Item code</th>
	      			<th>Item Name</th> 
	      			<th>Quantity</th>
	      			<th>Total Pieces</th>
	      			<th>Value <small>(Ksh)</small></th>
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

<!--New IPD Item-->
<div class="modal fade" id="newItemPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
	<div class="modal-dialog modal-dialog-lg" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary">
				<b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >IPD Item Details</b>
				<button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>IPD Item Name</label>
						<input type="text" class="form-control form-control-sm" id="item_name">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-sm-12 col-md-6">
						<label>Units in Store <small>(Boxes)</small></label>
						<input class="form-control form-control-sm" id="item_quantity" onkeyup="ModifyFields()">
					</div>
					<div class="form-group col-sm-12 col-md-6 text-success">
						<label>Box/Package Price <small>(Ksh)</small></label>
						<input class="form-control form-control-sm" id="purchase_price" onkeyup="ModifyFields()">
					</div>
				</div>
				<div class="form-row">	
					<div class="form-group col-sm-12 col-md-4 text-primary">
						<label>Pieces in a Unit</label>
						<input type="text" class="form-control form-control-sm" id="item_pieces_per_unit" onkeyup="ModifyFields()">
					</div>	
					<div class="form-group col-sm-12 col-md-4">
						<label>Total Pieces</label>
						<input type="text" class="form-control form-control-sm" id="total_pieces" readonly>
					</div>	
					<div class="form-group col-sm-12 col-md-4">
						<label>Rate <small>(Ksh)</small></label>
						<input type="text" class="form-control form-control-sm" id="item_rate_cash" readonly>
					</div>			
				</div>
				<div class="form-row">		
					<div class="form-group col-sm-12">
						<label>Supplier</label>
						<select class="form-control form-control-sm" id="item_supplier">
		                    <option value="">Select</option>
		                    <?php 
		                      $res = mysqli_query($conn,"SELECT * FROM tbl_supplier");
		                      while ($Supplier = mysqli_fetch_assoc($res)) {
		                        ?>
		                        <option value="<?= $Supplier['supplier_code']?>"><?= $Supplier['supplier_name']?></option>
		                        <?php
		                      }
		                    ?>
		                 </select>
					</div>		
				</div>
				<div class="row">	
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
    
	$(document).ready(function(){
		GetItems();
	});

	setInterval(function(){
		GetItems();
	},2000);
	
	function ModifyFields(){
		var unitInStore = $('#item_quantity').val().length==0?1:$('#item_quantity').val();
		var piecesInUnit = $('#item_pieces_per_unit').val().length==0?1:$('#item_pieces_per_unit').val();
		$('#total_pieces').val(unitInStore * piecesInUnit);

		var sp = $('#purchase_price').val();
		var rate = (sp/piecesInUnit).toFixed(2);
		$('#item_rate_cash').val(rate);
	}

	var selected_item_code = null;

	function NewItem(){
		selected_item_code = null;
		$('input').val('');
		$('#item_supplier').val('');
		$('#newItemPopUp').modal('show');
	}

	function EditItem(item_code){
		selected_item_code = item_code;
		$.ajax({
			method:'post',
			url:'crud.php',
			data:{GetItemProps:'1',item_code:selected_item_code},
			success:function(response){
				var props  = response.split(';');
				$("#item_name").val(props[0]);
				$("#purchase_price").val(props[1]);	
				$("#item_quantity").val(props[2]);	
				$("#item_pieces_per_unit").val(props[4]);
				$('#total_pieces').val(props[5]);
				$("#item_rate_cash").val(props[6]);
				$('#item_supplier').val(props[7]);

				$('#newItemPopUp').modal('show');
			}

		});

	}

	function SaveItem(){
		var item_name = $("#item_name").val();
		var item_type = 'IPD Item';
		var item_quantity= $("#item_quantity").val();				
		var purchase_price= $("#purchase_price").val();
		var item_pieces_per_unit = $("#item_pieces_per_unit").val();
		var total_pieces = $('#total_pieces').val();
		var item_rate_cash= $("#item_rate_cash").val();
		var item_supplier = $('#item_supplier').val()


		if (item_name=='') {SnackNotice(false,'Enter the item name');return;}
		if (item_quantity=='' || isNaN(item_quantity)) {SnackNotice(false,'Enter the numeric quantity of this item currently in stock');return;}
		if (purchase_price=='' || isNaN(purchase_price)) {SnackNotice(false,'Enter the numeri price of this item');return;}	
		if (item_pieces_per_unit=='' || isNaN(item_pieces_per_unit)) {SnackNotice(false,'Enter the number of pieces in a unit of this item');return;}
		if (item_supplier=='') {SnackNotice(false,'Enter the supplier name of this item');return;}

		var Data = null;
		if (selected_item_code == null) {//New item
			Data = {
				SaveItem:'1',
				item_name:item_name,
				item_type:item_type, 
				item_quantity:item_quantity,
				purchase_price:purchase_price,
				item_pieces_per_unit:item_pieces_per_unit,
				total_pieces:total_pieces,
				item_rate_cash:item_rate_cash,
				item_supplier:item_supplier
			};
		}else{//Update item
			Data = {
				UpdateItem:'1',
				item_code:selected_item_code,
				item_name:item_name,
				purchase_price:purchase_price,
				item_rate_cash:item_rate_cash,
				item_supplier:item_supplier
			};
		}
		$('#processDialog').modal('show');
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:Data,
			success:function(response){
				GetItems();
				if (response.includes('success')) {
					SnackNotice(true,'Item Details saved successfully');
					$('#newItemPopUp').modal('hide');
				}else{
		          SnackNotice(false,response);
		        }
				$('#processDialog').modal('hide');
			},
		});
	}
	function GetItems(){
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:{GetIPDItems:'1'},
			success:function(response){
				$("#item_list").html(response);
			}
		});
	}
	function DeleteItem(item_code){
		RitchConfirm("Proceed ?","Delete this Item from the database").then(function() {
			$('#processDialog').modal('show');
			$.ajax({
				method:'POST',
				url:'CRUD.php',
				data:{DeleteItem:'1',item_code:item_code},
				success:function(response){
					$('#processDialog').modal('hide');
					GetItems();
					SnackNotice(true,response);
				},
			});
		});
	}

</script>
</body>
</html>