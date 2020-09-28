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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Revenue & Billing</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
      		<b><i class="oi oi-tags"></i> Morgue Billing</b>
      	</div> 
          <div class="page_scroller">
          	<div class="form-row">
          		<div class="form-group col-sm-12 col-md-2">
          			<label>Filter By</label>
          			<select class="form-control form-control-sm" id="searchBy">
          				<option value="">Select</option>                  
                  <option value="adm_no">Admission No.</option>
          				<option value="body_name">Name</option>
          			</select>
          		</div>
              <div class="form-group col-sm-12 col-md-4">
                <label style="color: #fff;">Filter By</label>                
                <div class="input-group">
                  <input  class="form-control form-control-sm" id="searchVal" onkeyup="FilterMorgueBillingList()">
                  <div class="input-group-prepend" onclick="FilterMorgueBillingList()">
                      <span class="input-group-text"> <i class="oi oi-magnifying-glass"></i> </span>
                   </div>
                </div>
              </div>
          	</div>
            <table class="table table-sm table-striped table-bordered">
              <thead class="bg-dark text-light">
                <th>Admission No.</th>
                <th>Name</th>
                <th>Admission Date</th>
                <th>Kin Name</th>
                <th>Kin Phone</th>
                <th>Action</th>
              </thead>
              <tbody id="billing_list" style="cursor: pointer;">
          <?php 
            include('../ConnectionClass.php');
            $sql = "SELECT * FROM tbl_morgue_admission WHERE status='Active'";
            $res = mysqli_query($conn,$sql);
            if(!$res){
            	echo "Sorry: ".mysqli_error($conn);
            }
            while ($Body = mysqli_fetch_assoc($res)) {
              $adm_no = $Body['adm_no'];
              ?>
                <tr>
                  <td><?= $Body['adm_no']?></td>
                  <td ><?= $Body['adm_date']?></td>
                  <td ><?= $Body['body_name']?></td>
                  <td ><?= $Body['kin_name']?></td>
                  <td ><?= $Body['kin_phone']?></td>
                  <td>
                    <button class="btn btn-outline-primary btn-sm" onclick="var w = window.open('Morgue Bill.php?serveRef=<?= $adm_no?>'); w.focus();"><i class="oi oi-print"></i> Print Bill</button>
                    <button class="btn btn-outline-success btn-sm" onclick="ClearMorgueBill('<?= $adm_no?>')"><i class="oi oi-dollar"></i>  Clear Bill</button>
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

    var req = null;
    function FilterMorgueBillingList(){
      var searchBy = $('#searchBy').val();
      var searchVal = $('#searchVal').val();
      if (req != null) req.abort();
      req = $.ajax({
        method: 'POST',
        url: 'CRUD.php',
        data: {FilterMorgueBillingList:'1', searchBy:searchBy, searchVal:searchVal},
        success: function(response){
            $('#billing_list').html(response);
        }
      });   
    }

    function ClearMorgueBill(adm_no){
      $.ajax({
        method: 'POST',
        url: 'CRUD.php',
        data: {ClearMorgueBill:'1', adm_no:adm_no},
        success: function(response){
            RitchConfirm("Proceed ?",response,function() {
              PerfomMorgueClearance(adm_no);
            });
        }
      });
    }
    function PerfomMorgueClearance(adm_no){
      $.ajax({
        method: 'POST',
        url: 'CRUD.php',
        data: {PerfomMorgueClearance:'1', adm_no:adm_no},
        success: function(response){
            SnackNotice(false,response);
            location.href = location.href;
        }
      });
    }

  </script>
</body>
</html>