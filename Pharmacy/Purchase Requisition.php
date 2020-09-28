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
if (!($User_level=='admin' || $GroupPrivileges['orders_stock_priv']==1)) {
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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Pharmacy</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="col-11 text-secondary" style="background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
      		<b><i class="oi oi-dollar"></i> Purchase Requisition</b>
      	</div> 
          <div class="page_scroller">
            <div class="form-row">
              <div class="form-group col-12">
                <button class="btn btn-primary" onclick="$('#ItemListPopUp').modal('show')" style="height: 35px; padding: 0 5px;"><i class="oi oi-plus"></i> New Requisition</button>
              </div>
              <div class="form-group col-12">
                <input class="form-control form-control-sm" placeholder="Search for requisitions by drug name" onkeyup="GetPurchaseRequisitions()" id="searchVal">
              </div>
             <table class="table table-sm table-bordered table-striped" style="margin: 10px;">
                <thead class="bg-dark text-light">
                  <th>Order ID</th>
                  <th>Date</th>
                  <th>Item Code</th>
                  <th>Item Name</th>
                  <th>Order Quantity</th>
                  <th>Status</th> 
                </thead>
                <tbody id="order_list">
                <!-- Add from crud--> 
                </tbody>
              </table>
            </div>
          </div>
      </div>
  </div>
</div>

<!--New Drug-->
<div class="modal fade" id="ItemListPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >Item Search</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="form-group col-12">
            <input class="form-control form-control-sm" id="searchVal2" placeholder="Search for drugs by name..." onkeyup="GetStockItemsForRequsition()">
          </div>
          <table class="table table-bordered table-sm" style="margin: 10px;">
            <thead class="bg-primary text-light">
              <th>code</th>
              <th>Item</th>
              <th>Quantity</th>
              <th>Actions</th>
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
<div class="modal fade" id="RequisitionPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >Make Item Requisition</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="form-group col-sm-12 col-md-4">
              <label>Code</label>
              <input class="form-control form-control-sm" type="text" id="item_code" readonly>
        </div>
          <div class="form-group col-sm-12 col-md-8">
              <label>Item Name</label>
              <input class="form-control form-control-sm" type="text" id="item_name" readonly>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-sm-12 col-md-6">
              <label>Stock Quantity</label>
              <input class="form-control form-control-sm" type="text" id="stock_quantity" readonly >
        </div>

          <div class="form-group col-sm-12 col-md-6">
              <label>Order Quantity</label>
              <input class="form-control form-control-sm" type="text" id="order_quantity">
          </div>
        </div>
        <div class="row">
          <div class="form-group col-sm-12 col-md-6">
            <button class="btn btn-success col-12" id="btnSave" onclick="MakePurchaseRequest()"><i class="oi oi-check"></i> Save Requisition</button>
          </div>
          <div class="form-group col-sm-12 col-md-6">
            <button class="btn btn-danger col-12" onclick="$('#RequisitionPopUp').modal('toggle')"><i class="oi oi-x"></i> Close</button>
          </div>              
        </div>
      </div>
    </div>
  </div>  
</div>

<!--New Drug-->
<div class="modal fade" id="ApproveRequisitionPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success">
        <b class="modal-title" id="exampleModalLabel" style="color: #FFF;"> Approve Requisition</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="form-group col-sm-12 col-md-6">
              <label>Request ID</label>
                <input class="form-control form-control-sm" type="text" id="app_order_code" readonly>
            </div>
            <div class="form-group col-sm-12 col-md-6">
                <label>Item Code</label>
                <input class="form-control form-control-sm" type="text" id="app_item_code" readonly>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-sm-12 col-md-12">
              <label>Item Name</label>
              <input class="form-control form-control-sm" type="text" id="app_item_name" readonly >
        </div>
      </div>
      <div class="row">
          <div class="form-group col-sm-12 col-md-12">
              <label>Supplier Name</label>
              <select class="form-control form-control-sm" id="app_item_supplier" >
                <option value="">Select</option>
                <?php
                  $res = mysqli_query($conn,"SELECT * FROM tbl_supplier ORDER BY supplier_name ASC");
                  while ($Supplier  = mysqli_fetch_assoc($res)) {
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
              <label>Supply Unit Cost</label>
                <input class="form-control form-control-sm" id="app_supply_cost" onkeyup="$('#app_order_total_cost').val(($(this).val() * $('#app_order_unit').val()).toFixed(2))">
            </div>
            <div class="form-group col-sm-12 col-md-4">
                <label>Order Units</label>
                <input class="form-control form-control-sm"  id="app_order_unit" onkeyup="$('#app_order_total_cost').val(($(this).val() * $('#app_supply_cost').val()).toFixed(2))">
            </div>
            <div class="form-group col-sm-12 col-md-4">
                <label>Total Cost</label>
                <input class="form-control form-control-sm" id="app_order_total_cost" readonly>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-sm-12 col-md-6">
            <button class="btn btn-success col-12" id="btnSave" onclick="ApproveRequisition()"><i class="oi oi-check"></i> Approve</button>
          </div>
          <div class="form-group col-sm-12 col-md-6">
            <button class="btn btn-danger col-12" onclick="$('#ApproveRequisitionPopUp').modal('hide')"><i class="oi oi-x"></i> Close</button>
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

    var req= null;

    $(document).ready(function(){
      GetPurchaseRequisitions();
    });

    function GetPurchaseRequisitions(){
      var searchVal = $('#searchVal').val();
      if (req != null) {req.abort()}
      req = $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{GetPurchaseRequisitions:'1',searchVal:searchVal},
        success:function(response){
          $('#order_list').html(response);
        }
      });
    }




    function GetStockItemsForRequsition(){
      var searchVal = $('#searchVal2').val();
      if (req != null) {req.abort()}
      req = $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{GetStockItemsForRequsition:'1',searchVal:searchVal},
        success:function(response){
          $("#stock_list").html(response);
        }
      });
  }

  function PurchaseRequestThisItem(item_code){
    $('input').val('');
    $('#processDialog').modal('toggle');
    $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{PurchaseRequestThisItem:'1',item_code:item_code},
        success:function(response){

          var ItemProps = response.split(';');
          $('#item_code').val(ItemProps[0]);
          $('#item_name').val(ItemProps[1]);
          $('#stock_quantity').val(ItemProps[2]);

          $('#processDialog').modal('toggle');
          $("#RequisitionPopUp").modal('show');
        }
      });
  }

  function MakePurchaseRequest(){
      var item_code = $('#item_code').val();
      var item_name = $('#item_name').val();
      var stock_quantity = $('#stock_quantity').val();
      var order_quantity = $('#order_quantity').val();
      var ordering_officer = "<?= $Fullname ?>";

      if (order_quantity.length===0 || isNaN(order_quantity)) {SnackNotice(false,'Enter the numeric order quantity');return;}
      $('#processDialog').modal('toggle');
      $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{
          MakePurchaseRequest:'1',
          item_code:item_code,
          item_name:item_name,
          stock_quantity:stock_quantity,
          order_quantity:order_quantity,
          ordering_officer:ordering_officer
        },
        success:function(response){
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
              SnackNotice(true,'Purchase requested successfully');
              GetPurchaseRequisitions();
            $('#RequisitionPopUp').modal('toggle');
          }else{
            SnackNotice(false,response);
          }
        }
      });
  }

  </script>
</body>
</html>