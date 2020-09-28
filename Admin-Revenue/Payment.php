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
if (!($User_level=='admin' || $GroupPrivileges['revenue_cash_collection_priv']==1)) {
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
    .receiptPop{
      width: 100%; height: 100%; position: fixed; top: 0; left: 0; background-color: rgba(200,200,200,0.5); display: none; overflow-y: scroll;
    }
    #receipt_view{
     position: absolute; top: 0; left: 0; width: auto; height: auto; z-index: 500; border: 1px solid #777; border-radius: 5px; margin-left: calc((100% - 100mm)/2)
    }
    .print_me{
        position: absolute;top: 7px; right:7px; border: none; background-color: transparent; border-radius: 3px; cursor: pointer;
    }
    .print_me:hover{
        background-color: #ccc;
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
          <b><i class="oi oi-people"></i> Service Queue</b>
        </div> 
          <div class="page_scroller">
            <table class="table table-sm table-bordered table-striped">
              <thead class="bg-dark text-light">
                <th>#</th>
                <th>Queued at</th>
                <th>OPD No.</th>
                <th>Client Name</th>
                <th>Cash Bills</th>
                <th>Corporate Bills</th>
                <th>Serve</th>
              </thead>
              <tbody id="queue_tbody" style="cursor: pointer;">
                <!-- ADD FROM CRUD -->
              </tbody>
            </table>
          </div>
      </div>
  </div>
</div>


<div class="modal fade" id="ReceiveCashPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content" style="width: 800px; margin-left: calc((100% - 800px)/2);">
      <div class="modal-header bg-success">
        <b class="modal-title" id="exampleModalLabel" style="color: #FFF;" > Client Cash Payments</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group col-4">
            <label>Registration Number</label>
            <input class="form-control form-control-sm" id="refno" readonly>
          </div>
          <div class="form-group col-8">
            <label>Client Name</label>
            <input class="form-control form-control-sm" id="name" readonly>
          </div>
          <div class="form-group col-4">
            <label>Total (Ksh)</label>
            <input class="form-control form-control-sm" id="total_bill" readonly>
          </div>
          <div class="form-group col-4">
            <label>Amount Paid (Ksh)</label>
            <input class="form-control form-control-sm" id="amount_paid" onkeyup="GetDiff($(this).val(),$('#total_bill').val())">
          </div>
          <div class="form-group col-4">
            <label>Change (Ksh)</label>
            <input class="form-control form-control-sm" id="balance" readonly>
          </div>
          <div class="form-group col-12">
            <button class="btn btn-success btn-sm" onclick="SaveAndPrint()"><i class="oi oi-check"></i> Save and Print</button>
          </div>
          <div class="col-12">
            <table class="table table-sm table-bordered table-striped">
              <thead class="bg-dark text-light">
                <th><input type="checkbox" id="select_all" checked="true"></th>   
                <th>Code</th>                
                <th>Request</th>    
                <th>Cost</th>            
              </thead>
              <tbody id="cash_list">
                <!-- crud -->
              </tbody>
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
<div class="receiptPop">
  <div id="receipt_view">
    <button class="print_me" style="right: 40px;" onclick="printReceipt()"><i class="oi oi-print"></i> Print</button>
    <button class="print_me" onclick="$('.receiptPop').hide()"><i class="oi oi-x"></i></button>
    <div id="receipt">
      <!-- js -->
    </div>
  </div>
</div>


<!-- Menu Toggle Script -->
  <script>
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });
    var req = null,selected_refno = null;

    $(document).ready(function(){
      GetQueue();
    });

    function GetQueue(){
      if (req !== null) {req.abort;}
      req = $.ajax({
        method:'post',
        url:'crud.php',
        data:{GetQueue:'1'},
        success:function(response){
          $('#queue_tbody').html(response);
        }
      });
    }
    
    setInterval(function(){
      GetQueue();
    },2000); 
    
  function ReceiveCash(refno){
    selected_refno = refno;
    $('#refno').val(refno);
    $('#ReceiveCashPopUp').modal('toggle');
    $.ajax({
      method:'post',
      url:'crud.php',
      data:{GetCashBills:'1',refno:selected_refno},
      success:function(response){
        response = JSON.parse(response);
        var list_data = "";
        $('#name').val(response.fullname);
        $('#total_bill').val((response.total_bill).toFixed(2));
        for (var i = 0; i < (response.requests_list).length; i++) {
          list_data +=
           "<tr>"
              +"<td><input type='checkbox' checked='true' onclick='CalculateTotal()'></td>"
              +"<td>"+response.requests_list[i][0]+"</td>"
              +"<td>"+response.requests_list[i][1]+"</td>"
              +"<td class='amount' align='right'>"+response.requests_list[i][2]+"</td>"
           +"</tr>"
        }
        $('#cash_list').html(list_data);
      }
    });
  }

  function GetDiff(amount_paid,total_bill){
    $.ajax({
      method:'post',
      url:'crud.php',
      data:{GetDiff:'1',amount_paid:amount_paid,total_bill:total_bill},
      success:function(response){
        console.log(response);
        $('#balance').val((+response).toFixed(2));
      }
    });
  }

  function CalculateTotal(){
    var totalAmount = 0;
    $('#cash_list tr').each(function(){
      var row = $(this);
      if (row.find('input[type=checkbox]').is(':checked')) {
        var amount = row.find('.amount').text();
        totalAmount += +amount;
      }
        $('#total_bill').val(totalAmount.toFixed(2));
    });
    GetDiff($('#amount_paid').val(),$('#total_bill').val());
  }

  $('#select_all').click(function(){
    if ($('#select_all').is(':checked')) {
      $('#cash_list tr').each(function(){
        var row = $(this);
        row.find('input[type=checkbox]').prop('checked',true)
      });
    }else{
      $('#cash_list tr').each(function(){
        var row = $(this);
        row.find('input[type=checkbox]').prop('checked',false)
      });
    }
    GetDiff($('#amount_paid').val(),$('#total_bill').val());
  });

  function SaveAndPrint(){    
      var total_bill = $('#total_bill').val();
      var amount_paid = $('#amount_paid').val();
      var balance = $('#balance').val();
      var selected_items = 0;
      var req_codes = new Array();
      $('#cash_list tr').each(function(){
          var row = $(this);
          if (row.find('input[type=checkbox]').is(':checked')) {
              req_codes.push(row.find('td:nth-child(2)').text());
              selected_items++;
          }
        });
      if (selected_items===0) {SnackNotice(false,'You have not selected any item to bill');return;}
      if (+amount_paid < +total_bill) {SnackNotice(false,'Amount Paid is Less than the required bill amount'); $('#amount_paid').focus();return;}
      RitchConfirm("Proceed ?","Do you wish to confirm receiving payments amounting to <b> Ksh. "+total_bill+"</b>").then(function(){
        $('#processDialog').modal('toggle');
        $.ajax({
          method:'post',
          url:'crud.php',
          data:{SaveCashPayment:'1',refno:selected_refno,req_codes:JSON.stringify(req_codes),total_bill:total_bill,amount_paid:amount_paid,balance:balance},
          success:function(response){
            $('#processDialog').modal('toggle');
            $('#ReceiveCashPopUp').modal('toggle');
            if (response.includes('success')) {
              response = JSON.parse(response);
              var receipt_no = response.receipt_no;
              SnackNotice(true,'Payment successfully saved and Receipt Generated');
              $('.receiptPop').show();
              RichUrl($('#receipt'),{GetCashReceipt:'1',refno:selected_refno,receipt_no:receipt_no});
            }else{
              SnackNotice(false,response);
            }
          }
        });
    });
  }   
  function GetCreditSlip(refno,req_id){
    $.ajax({
      method:'post',
      url:'crud.php',
      data:{GrantCreditSlip:'1',refno:refno,req_id:req_id},
      success:function(response){
        if (response.includes('success')) {
          $('.receiptPop').show();
          RichUrl($('#receipt'),{GetSlip:'1',refno:refno});

        }
      }
    });
  }
  function printReceipt(){
    $('body').html($('#receipt').html());
    print();
    location.href=location.href;
  }
  </script>
</body>
</html>