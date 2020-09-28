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

include '../ConnectionClass.php';

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
      	<div class="col-11 text-secondary" style="background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
      		<b><i class="oi oi-cart"></i> Receive Goods</b>
      	</div> 
          <div class="page_scroller">
             <table class="table table-sm table-bordered table-striped" style="margin: 10px;">
                <thead class="bg-light">
                  <th>Supplier Code</th>
                  <th>Supplier Name</th>
                  <th>Total Orders</th>
                  <th>Action</th>
                </thead>
                <tbody id="order_list">
                <!-- Add from crud --> 
                </tbody>
              </table>
          </div>
      </div>
  </div>
</div>

<!--New Drug-->
<div class="modal fade" id="ReceiveGoodsPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success">
        <b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >Orders from this Supplier</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <table class="table table-bordered table-sm" style="cursor: pointer;">
            <thead class="bg-light">
              <th>Order Code</th>
              <th>Item Code</th>
              <th>Item Name</th>
            </thead>
            <tbody id="stock_list">
        <!-- Add from crud--> 
            </tbody>
          </table>
      </div>
    </div>
  </div>  
</div>

<!--New Drug-->
<div class="modal fade" id="ReceiveItemPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >Receive Goods</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          	<div class="form-group col-sm-12 col-md-4">
              <label>Oder Code</label>
              <input class="form-control form-control-sm" type="text" id="order_code" readonly>
        	</div>
          	<div class="form-group col-sm-12 col-md-8">
              <label>Item Code</label>
              <input class="form-control form-control-sm" type="text" id="item_code" readonly>
          	</div>
        </div>
        <div class="row">
          	<div class="form-group col-sm-12">
              <label>Item Name</label>
              <input class="form-control form-control-sm" type="text" id="item_name" readonly>
          	</div>
        </div>
        <div class="row">
          <div class="form-group col-sm-12 col-md-6">
              <label>Ordered Quantity</label>
              <input class="form-control form-control-sm" type="text" id="order_quantity" readonly >
        </div>
          <div class="form-group col-sm-12 col-md-6">
              <label>Supplied Quantity</label>
              <input class="form-control form-control-sm" type="text" id="supply_quantity">
          </div>
        </div>
        <div class="row">
          <div class="form-group col-sm-12 col-md-6">
            <button class="btn btn-success col-12" id="btnSave" onclick="ReceiveItem()"><i class="oi oi-check"></i> Save </button>
          </div>
          <div class="form-group col-sm-12 col-md-6">
            <button class="btn btn-danger col-12" onclick="$('#ReceiveItemPopUp').modal('hide')"><i class="oi oi-x"></i> Close</button>
          </div>              
        </div>
      </div>
    </div>
  </div>  
</div>

<!--Proccessing dialog-->
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
      GetConsignedOrdersToReceiveGoods();
    });

    function GetConsignedOrdersToReceiveGoods(){
      $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{GetConsignedOrdersToReceiveGoods:'1'},
        success:function(response){
          $('#order_list').html(response);
        }
      });
    }

    function ReceiveGoodsPopUp(supplier_code){
    	$('#ReceiveGoodsPopUp').modal('show');
    	$.ajax({
	        method:'POST',
	        url:'CRUD.php',
	        data:{ReceiveGoodsPopUp:'1',supplier_code:supplier_code},
	        success:function(response){
	          $('#stock_list').html(response);
	        }
     	});
    }

    function GetItemPropsToReceive(od_id){
    	$('#ReceiveItemPopUp').modal('show');
    	$.ajax({
	        method:'POST',
	        url:'CRUD.php',
	        data:{GetItemPropsToReceive:'1',od_id:od_id},
	        success:function(response){
	          var ItemProps = response.split(';');

	          $('#order_code').val(ItemProps[0]);
	          $('#item_code').val(ItemProps[1]);
	          $('#item_name').val(ItemProps[2]);
	          $('#order_quantity').val(ItemProps[3]);
	          $('#supply_quantity').val(ItemProps[3]);
	        }
     	});
    }

    function ReceiveItem(){
    	var od_id = $('#order_code').val();
      	var item_code = $('#item_code').val();
      	var item_name = $('#item_name').val();
      	var order_quantity = $('#order_quantity').val();
      	var supply_quantity = $('#supply_quantity').val();

      	if (isNaN(supply_quantity) || supply_quantity=='' || +supply_quantity==0) {SnackNotice(false,'Enter the numeric value of the received goods.');$('#supply_quantity').focus(); return;}
      	if (+supply_quantity > +order_quantity) {SnackNotice(false,'You cannot receive more Goods that those ordered for.'); $('#supply_quantity').focus();  return;}
    	$.ajax({
	        method:'POST',
	        url:'CRUD.php',
	        data:{
	        	ReceiveItem:'1',
	        	od_id:od_id,
	        	item_code:item_code,
	        	item_name:item_name,
	        	order_quantity:order_quantity,
	        	supply_quantity:supply_quantity
	        },
	        success:function(response){
            if (response.includes('success')) {
              SnackNotice(true,'Goods successfully received');
          		$('#ReceiveItemPopUp').modal('hide');
          	}else{
              SnackNotice(false,response);
            }
				    GetConsignedOrdersToReceiveGoods();
	        }
     	});
    }
  </script>
</body>
</html>