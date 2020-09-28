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
    .online{
      animation: blink 1s linear infinite; width: 5px; height: 5px; border-radius: 50%; background-color: green; color: green; margin: 5px; 
    } 
    @keyframes blink{
      50%{
        opacity: 0;
      }
    }
    @-webkit-keyframes blink{
      50%{
        opacity: 0;
      }
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
      		<b><i class="oi oi-dollar"></i> Billing</b>
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
                <th>REG No.</th>
                <th>ID No.</th>
                <th>Name</th>
                <th>Insurance Company</th>
                <th>Insured</th>
                <th>Action</th>
              </thead>
              <tbody id="billing_list" style="cursor: pointer;">
          <?php 
            include('../ConnectionClass.php');
            $sql = "SELECT * FROM tbl_patient WHERE refno IN (SELECT refno FROM tbl_opd_service_request WHERE req_status != 'cleared' AND payment_type='Billing')";
            $res = mysqli_query($conn,$sql);
            while ($Patient = mysqli_fetch_assoc($res)) {
              switch ($Patient['ins_status']) {
                case 'NO':
                  $ins_color = "red";
                  break;
                default:
                  $ins_color = "green";
                  break;
              }
              ?>
                <tr>
                  <td><?= $Patient['refno']?></td>
                  <td ><?= $Patient['idno']?></td>
                  <td ><?= $Patient['fullname']?></td>
                  <td ><?= $Patient['ins_company']?></td>
                  <td style="font-weight: bold; color: <?= $ins_color?>;"><?= $Patient['ins_status']?></td>
                  <td>
                    <button class="btn btn-outline-primary btn-sm" onclick="var w = window.open('Bill.php?serveRef=<?= $Patient['refno']?>'); w.focus();"><i class="oi oi-print"></i> Print Bill</button>
                    <button class="btn btn-outline-success btn-sm" onclick="ClearBill('<?= $Patient['refno']?>')"><i class="oi oi-dollar"></i>  Clear Bill</button>
                  </td>
                </tr>
              <?php
            }
          ?>
              </tbody>
            </table>
          </div>
      </div>
  </div>
</div>

</body>
  <!-- Menu Toggle Script -->
  <script>
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });

    setInterval(function(){
      FilterBillingList();
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


    function ClearBill(refno){
      $.ajax({
        method: 'POST',
        url: 'CRUD.php',
        data: {ClearBill:'1', refno:refno},
        success: function(response){
            RitchConfirm("Proceed ?",response,function() {
              PerfomClearance(refno);
            });
        }
      });
    }
    function PerfomClearance(refno){
      $.ajax({
        method: 'POST',
        url: 'CRUD.php',
        data: {PerfomClearance:'1', refno:refno},
        success: function(response){
          if (response.includes('success')) {
            SnackNotice(true,"Bill Succesfully cleared. The patient should be discharged immediately to avoid further billing");
            FilterBillingList();
          }else{
            SnackNotice(false,response);
          }
        }
      });
    }
  </script>
</body>
</html>