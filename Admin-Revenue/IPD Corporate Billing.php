<?php
include('../ConnectionClass.php');
include('../db_class.php');
$db = new CRUD();
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
if (!($User_level=='admin' || $GroupPrivileges['revenue_billing_priv']==1)) {
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
  <style type="text/css">
    .minimize_me{
      background-color: #eee;
    }
    .minimize_me table{
      background-color: #fff; 
    }
    p {
      position: relative; background-color: blue; padding: 5px; color: #fff; border-top-right-radius: 5px; border-top-left-radius: 5px; 
      margin-top: 10px; margin-bottom: 3px; cursor: pointer;
    } 
    p span {
      position: absolute;right: 25px; top: 5px;
    }
    .toggle_list{
       display: none;
    }
    @media print{
      @page { size: auto; margin: 0px;}
      body  { margin: 0px; padding: 0px;}
    } 
  </style>
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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Revenue</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
      		<b><i class="oi oi-dollar"></i>IPD Corporate Billing</b>
      	</div> 
          <div class="page_scroller">
          	<div class="form-row">
          		<div class="form-group col-sm-12 col-md-2">
          			<label>Filter By</label>
          			<select class="form-control form-control-sm" id="searchBy">
          				<option value="">Select</option>                  
                  <option value="refno">Registration No.</option>
          				<option value="idno">ID No.</option>
          				<option value="ins_card_no">Insurance Number</option>
          				<option value="fullname">Name</option>
          			</select>
          		</div>
              <div class="form-group col-sm-12 col-md-4">
                <label style="color: #fff;">Filter By</label>                
                <div class="input-group">
                  <input  class="form-control form-control-sm" id="searchVal" onkeyup="FilterBillingList()">
                  <div class="input-group-prepend" onclick="FilterBillingList()">
                      <span class="input-group-text"> <i class="oi oi-magnifying-glass"></i> </span>
                   </div>
                </div>
              </div>
          	</div>
            <table class="table table-sm table-striped table-bordered">
              <thead class="bg-dark text-light">
                <th>Created</th>
                <th>IPD File</th>
                <th>REG No</th>
                <th>Name</th>
                <th>Total (Ksh)</th>
                <th>Rebate (Ksh)</th>
                <th>Action</th>
              </thead>
              <tbody id="billing_list" style="cursor: pointer;">
              <?php 
                include('../ConnectionClass.php');
                $sql = "SELECT * FROM tbl_ipd_admission WHERE adm_no IN(SELECT fileno FROM tbl_ipd_service_request WHERE req_status != 'cleared' AND payment_type='Corporate')";
                $rows = $db->ReadArray($sql);
                foreach($rows as $row):
                  $patient = $db->ReadOne("SELECT * FROM tbl_patient WHERE refno='$row[refno]'");
                  $bill_amount = 0;
                  $bills = $db->ReadArray("SELECT req_cost from tbl_ipd_service_request WHERE fileno='$row[adm_no]'");
                  foreach($bills as $bill){$bill_amount += $bill['req_cost'];}

                  $rebate_amount = 0;
                  $rebates = $db->ReadArray("SELECT rebate_amount from tbl_ipd_nhif_rebates WHERE fileno='$row[adm_no]'");
                  foreach($rebates as $rebate){$rebate_amount += $rebate['rebate_amount'];}
                  ?>
                  <tr>
                    <td><?= $row['adm_date']?></td>
                    <td><?= $row['adm_no']?></td>
                    <td><?= $row['refno']?></td>
                    <td><?= $patient['fullname']?></td>
                    <td align="right"><b><?= number_format((float)$bill_amount,2,'.','')?></b></td>
                    <td align="right"><b><?= number_format((float)$rebate_amount,2,'.','')?></b></td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary" onclick="ViewBill('<?= $row['adm_no']?>',$(this).parent('td'))"><i class="oi oi-eye"></i> View Bill</button>
                      <button class="btn btn-sm btn-outline-success" onclick="Categorise('<?= $row['adm_no']?>')"><i class="oi oi-dollar"></i>  Categorise Claims</button>
                    </td>
                  </tr>
                <?php
                endforeach;
              ?>
              </tbody>
            </table>
          </div>
      </div>
  </div>
</div>


<div class="modal fade" id="ViewBillPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content" style="width: 800px; margin-left: calc((100% - 800px)/2);">
      <div class="modal-header bg-success" style="padding: 5px;">
        <b class="modal-title" id="exampleModalLabel" style="color: #FFF;" > Client's Bill</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group col-5"><input class="form-control form-control-sm" id="refno" readonly></div>
          <div class="form-group col-7"><input class="form-control form-control-sm" id="client_name" readonly></div>      
          <div class="col-12 minimize_me">
          <p>Bills <span id="bills">25,000.00</span></p>
            <table class="table table-sm table-bordered table-striped" id="bills_list">
              <!-- crud -->
            </table>
          </div>
          <div class="col-12 minimize_me">
          <p>NHIF Rebates <span id="rebates">25,000.00</span></p>
            <table class="table table-sm table-bordered table-striped" id="rebates_list">
              <!-- crud -->
            </table>
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

  $(".minimize_me p").click(function() {
    var r = $(this).parent('.minimize_me');
    $(r.find('table')).toggleClass("toggle_list");
  });

  setInterval(function(){
    //FilterBillingList();
  },2000);

  var req = null;
  function FilterBillingList(){
    if (req != null) req.abort();
    req = $.ajax({
      method: 'POST',
      url: 'CRUD.php',
      data: {FilterBillingList:'1'},
      success: function(response){
        $('#billing_list').html(response);
      }
    });   
  }
  function ViewBill(fileno,td){
    var row = td.parent('tr');
    $('#refno').val(row.find('td:nth-child(3)').text());
    $('#client_name').val(row.find('td:nth-child(4)').text());
    $('#bills').text((+row.find('td:nth-child(5)').text()).toFixed(2));
    $('#rebates').text((+row.find('td:nth-child(6)').text()).toFixed(2));
    $('#ViewBillPopUp').modal('show');
    RichUrl($('#bills_list'),{GetBillsList:'1',fileno:fileno});
    RichUrl($('#rebates_list'),{GetRebatessList:'1',fileno:fileno});
  }
</script>
</body>
</html>