<?php
session_start();
if (!(isset($_SESSION['Username']))) {
  header("refresh:0, url=../login.php");
  return;
}
//Session Values
$Username = $_SESSION['Username'];
$Fullname = $_SESSION['Fullname'];
$User_level = $_SESSION['User_level'];
$GroupPrivileges = $_SESSION['GroupPrivileges'];

//Deny permissions
if (!($User_level=='admin' || $GroupPrivileges['morgue_priv']==1)) {
  header("refresh:0, url=Permission.php");
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);">  Morgue</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">         
          <b><i class="oi oi-book"></i> Morgue Logbook</b>
        </div>
        <div class="page_scroller">
        	<div style="background-color: #eee; overflow: hidden;" id="progressBar"><div class="box2"></div></div>
        	<div class="form-row">
              <div class="form-group col-sm-12 col-md-3">
                <label>Search By</label>
                <select id="searchBy" class="form-control form-control-sm">
                  <option value="">Select</option>
                  <option value="body_name">Name</option>
                  <option value="adm_no">Admission No</option>
                </select>
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <label>Search</label>
                <input id="searchVal" class="form-control form-control-sm" onkeyup="FilterBodies()">
              </div>
            </div>
        	<table class="table table-sm table-bordered table-striped">
        		<thead class="bg-dark text-light">
        			<th>ADM NO</th>
        			<th>Name</th>
        			<th>Date Admitted</th>
        			<th>Country</th>
        			<th>County</th>
        			<th>Status</th>
        			<th>Action</th>
        		</thead>
        		<tbody id="body_list">
        			<!-- ADD FROM CRUD -->
        		</tbody>
        	</table>
        </div>
      </div>
    </div>
  </div>
<!--Proccessing dialog-->
<div class="modal modal-static" id="processDialog" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" >
  <div style="background-color: #eee;"><div class="box2"></div></div>  
</div>
  <!-- Menu Toggle Script -->
  <script>
    var req = null;
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });
    $('#progressBar').hide();
    GetBodies();

    function GetBodies(){
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{GetBodies:'1'},
        success:function(response){
          $('#body_list').html(response);
        }
      });
    }

    function FilterBodies(){
    	var searchBy = $('#searchBy').val();
    	var searchVal = $('#searchVal').val();
      if (req != null) { req.abort();}
      $('#progressBar').show();
      req = $.ajax({
            method:'post',
            url:'crud.php',
            data:{FilterBodies:'1',searchBy:searchBy,searchVal:searchVal},
            success:function(response){
              $('#progressBar').hide();
              $('#body_list').html(response);
            }
          });
    }

    function ReleaseBody(adm_no){
      $('#processDialog').modal('show');
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{ReleaseBody:'1',adm_no:adm_no},
        success:function(response){
          $('#processDialog').modal('hide');
          SnackNotice(false,response);
          GetBodies();
        }
      });
    }
  </script>
</body>
</html>