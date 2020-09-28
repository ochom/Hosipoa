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
      		<b><i class="oi oi-print"></i> Print Local Purchase Orders</b>
      	</div> 
          <div class="page_scroller">
             <table class="table table-sm table-bordered table-striped" style="margin: 10px;">
                <thead class="bg-light">
                  <th>Supplier Code</th>
                  <th>Supplier Name</th>
                  <th class="text-right">Total Orders Cost <small>Ksh.</small></th>
                  <th>L.P.O</th>
                </thead>
                <tbody id="order_list">
                <!-- Add from crud--> 
                </tbody>
              </table>
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
      GetConsignedOrders();
    });

    function GetConsignedOrders(){
      $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{GetConsignedOrders:'1'},
        success:function(response){
          $('#order_list').html(response);
        }
      });
    }

    function PrintLPO(supplier_code){
      var w = window.open('Print LPO.php?supplier_code='+supplier_code);
    }
  </script>
</body>
</html>