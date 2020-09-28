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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Procurement, Service & Stationary Management</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-eyedropper"></i> Drugs</b>
      	</div>
      	<div class="page_scroller">
      		<div class="row" style="position: fixed; right: 50px; top: 60px; width: 200px; display: inline-block; z-index: 1;">
      			<div class="form-group">
      				<button class="btn btn-sm btn-primary col-12" onclick="NewItem()"><i class="oi oi-plus"></i> Add Drug</button>
      			</div>
      			<div class="form-group">
		      	 	<button class="btn btn-sm btn-success col-12" onclick="Export('Laboratory Services')"><i class="oi oi-file"></i> Export To Excel</button>
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
	      	<table class="table table-bordered table-sm table-striped" style="margin: 5px;">
	      		<thead class="bg-dark text-light">
	      			<th>#</th>
	      			<th>code</th>
	      			<th>Drug Name</th>	
	      			<th>Quantity <small>Boxes</small></th>
	      			<th>Total Pieces</th>
	      			<th>Cash <small>(Ksh)</small></th> 
	      			<th>Corporate <small>(Ksh)</small></th> 
	      			<th>Payment</th>
	      			<th>Category</th>
	      			<th>Sub Category</th>
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

<!--New Drug-->
<div class="modal fade" id="newItemPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
	<div class="modal-dialog modal-dialog-lg" role="document">
		<div class="modal-content" style="width: 800px; margin-left: calc((100% - 800px)/2);">
			<div class="modal-header bg-primary">
				<b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >Drug Details</b>
				<button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-row">
					<div class="form-group col-sm-12">
						<label>Drug Name</label>
						<input type="text" class="form-control form-control-sm" id="item_name">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-3">
						<label>Packets in Store</label>
						<input class="form-control form-control-sm" id="item_quantity" onkeyup="ModifyFields()">
					</div>
					<div class="form-group col-3 text-primary">
						<label>Packet Purchase Price</label>
						<input class="form-control form-control-sm" id="purchase_price" onkeyup="ModifyFields()">
					</div>
					<div class="form-group col-3 text-success">
						<label>% Profit</label>
						<input class="form-control form-control-sm" id="per_profit" onkeyup="ModifyFields()">
					</div>
					<div class="form-group col-3 text-success">
						<label>Selling Price</label>
						<input class="form-control form-control-sm" id="selling_price" readonly>
					</div>	
					<div class="form-group col-3 text-danger">
						<label>Packet Size</label>
						<input type="text" class="form-control form-control-sm" id="item_pieces_per_unit" onkeyup="ModifyFields()">
					</div>
					<div class="form-group col-3">
						<label>Total Pieces</label>
						<input type="text" class="form-control form-control-sm" id="total_pieces" readonly>
					</div>	
					<div class="form-group col-3 text-danger">
						<label>Cash Rate <small>(Ksh)</small></label>
						<input class="form-control form-control-sm" id="item_rate_cash">
					</div>	
					<div class="form-group col-3 text-danger">
						<label>Corporate Rate<small>(Ksh)</small></label>
						<input class="form-control form-control-sm" id="item_rate_cop">
					</div>	
					<div class="form-group col-3 text-danger">
						<label>NHIF Rebate <small>(Ksh)</small></label>
						<input class="form-control form-control-sm" id="nhif_rebate">
					</div>	
					<div class="form-group col-4">
						<label>Payments</label>
						<select class="form-control form-control-sm" id="cop_payment">
							<option value="">Cash Only</option>
							<option value="Yes">Both Cash and Corporate</option>
						</select>
					</div>		
					<div class="form-group col-sm-3">
						<label>Drug Category</label>
						<select class="form-control form-control-sm" id="item_category" onchange="GetSubcategories($(this).val())">
		                    <option value="">Select</option>
		                    <?php 
		                    $rows = $db->ReadArray("SELECT * FROM tbl_item_drug_lab_types WHERE cat_for='Drugs' ORDER BY cat_name ASC");
		                    foreach($rows as $row): ?>
		                        <option value="<?= $row['id']?>"><?= $row['cat_name']?></option>
		                    <?php endforeach; ?>
		                 </select>
					</div>
					<div class="form-group col-sm-3">
						<label>Drug Sub Category</label>
						<select class="form-control form-control-sm" id="item_sub_category">
		                    <option value="">Select</option>
		                    <!-- FROM CRUD -->
		                 </select>
					</div>				
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
					<div class="form-group col-12">
						<button class="btn btn-outline-success" onclick="SaveItem()">
							<i class="oi oi-check"></i> Save</button>
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
			data:{GetPages:'1',page_size:page_size,item_type:'Drug'},
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
		RichUrl($('#item_list'),{GetDrugs:'1',page:page,page_size:page_size});
	}

	function ModifyFields(){
		var purchase_price = $('#purchase_price').val();	
		var per_profit = ($('#per_profit').val()=='')?0:$('#per_profit').val();	
		var unitInStore = $('#item_quantity').val().length==0?1:$('#item_quantity').val();
		var piecesInUnit = $('#item_pieces_per_unit').val().length==0?1:$('#item_pieces_per_unit').val();

		$('#total_pieces').val(unitInStore * piecesInUnit);
		$.ajax({
			method:'post',url:'crud.php',
			data:{
				CalculateprofitsMargins:'1',per_profit:per_profit,purchase_price:purchase_price,piecesInUnit:piecesInUnit
			},success:function(response){
				console.log(response);
				response = JSON.parse(response);
				$('#selling_price').val(response.selling_price);
				$('#item_rate_cash').val(response.item_rate_cash.toFixed(2));
			}
		});
		
	}

	var selected_item_code = null;

	function NewItem(){
		selected_item_code = null;
		$('.form-control').val('');
		$('#newItemPopUp').modal('toggle');
	}

	function GetSubcategories(cat_id){
		$.ajax({
			method:'post',
			url:'crud.php',
			data:{GetSubcategories:'1',cat_id:cat_id},
			success:function(response){
				$('#item_sub_category').html(response);
			}
		});
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
				$("#purchase_price").val(item.purchase_price);	
				$("#item_quantity").val(item.item_quantity);			
				$("#selling_price").val(item.selling_price);
				$('#per_profit').val(item.item_des);
				$("#item_pieces_per_unit").val(item.item_pieces_per_unit);
				$('#total_pieces').val(item.total_pieces);
				$("#item_rate_cash").val(item.item_rate_cash);
				$("#item_rate_cop").val(item.item_rate_cop); 
				$("#nhif_rebate").val(item.nhif_rebate);
				$("#cop_payment").val(item.cop_payment);
				$('#item_supplier').val(item.item_supplier);
				$('#item_category').val(item.item_category);
				GetSubcategories(item.item_category);
				$('#newItemPopUp').modal('toggle');
			}

		});
	}

	function SaveItem(){
		var item_name = $("#item_name").val();
		var item_type = 'Drug';
		var item_quantity= $("#item_quantity").val();
		var purchase_price = $("#purchase_price").val();
		var per_profit = $('#per_profit').val();				
		var selling_price= $("#selling_price").val();
		var item_pieces_per_unit = $("#item_pieces_per_unit").val();
		var total_pieces = $('#total_pieces').val();
		var item_rate_cash= $("#item_rate_cash").val();
		var item_rate_cop= $("#item_rate_cop").val(); 
		var nhif_rebate= $("#nhif_rebate").val();
		var cop_payment= $("#cop_payment").val();
		var item_supplier = $('#item_supplier').val();
		var item_category = $('#item_category').val();
		var item_sub_category = $('#item_sub_category').val();


		if (item_name=='') {SnackNotice(false,'Enter the item name');return;}
		if (item_quantity=='' || isNaN(item_quantity)) {SnackNotice(false,'Enter the quantity of this item currently in stock');return;}
		if (purchase_price=='' || isNaN(purchase_price)) {SnackNotice(false,'Enter Purchase price of this item as a unit');return;}	
		if (+per_profit < 0 || isNaN(per_profit)) {SnackNotice(false,'Percentage profit must be a positive value not less than 0');$('#per_profit').val(); return}
		
		
		if (selling_price=='' || isNaN(selling_price)) {SnackNotice(false,'Enter Selling price of this item');return;}
		if (item_pieces_per_unit=='' || isNaN(item_pieces_per_unit)) {SnackNotice(false,'Enter the number of pieces in a unit of this item');return;}
		if ((item_rate_cop=='' || isNaN(item_rate_cop)) && cop_payment=='Yes') {SnackNotice(false,'Enter the numeric cost for Corporate Clients');$("#item_rate_cop").focus();return;}
		if (item_supplier=='') {SnackNotice(false,'Enter the supplier name of this item');return;}

		if(+selling_price < +purchase_price){SnackNotice(false,'Selling Price cannot be less than the Purchase Price');return;}

		item_rate_cash = (+item_rate_cash).toFixed(2);
		item_rate_cop = (item_rate_cop>0)?(+item_rate_cop).toFixed(2):'';

		var Data = null;
		RitchConfirm("Proceed ?","Note that once the Item is saved, you will ONLY be able to modify the ITEM NAME and SUPPLIER directly.<br> Do you want to proceed ?").then(function(){
			if (selected_item_code == null) {
			//New item
				Data = {
					SaveDrug:'1',
					item_name:item_name,
					item_type:item_type, 
					item_quantity:item_quantity,
					purchase_price:purchase_price,
					selling_price:selling_price,
					item_pieces_per_unit:item_pieces_per_unit,
					total_pieces:total_pieces,
					item_rate_cash:item_rate_cash,
					item_rate_cop:item_rate_cop,
					nhif_rebate:nhif_rebate,
					cop_payment:cop_payment,
					item_des:per_profit,
					item_supplier:item_supplier,
					item_category:item_category,
					item_sub_category:item_sub_category
				};
			}else{//Update item
				Data = {
					UpdateDrug:'1',
					item_code:selected_item_code,
					item_name:item_name,
					purchase_price:purchase_price,
					selling_price:selling_price,
					item_rate_cash:item_rate_cash,
					item_rate_cop:item_rate_cop,
					nhif_rebate:nhif_rebate,
					cop_payment:cop_payment,
					item_des:per_profit,
					item_supplier:item_supplier,
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
					GetItems(page_size,page);
					if (response.includes('success')) {
						SnackNotice(true,(selected_item_code === null)?'Drug details saved successfully':'Drug details updated successfully');
						$('#newItemPopUp').modal('toggle');
					}else{
			          SnackNotice(false,response);
			        }
					$('#processDialog').modal('toggle');
				},
			});
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