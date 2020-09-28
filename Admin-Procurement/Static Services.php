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
	
$Static = $db->ReadOne("SELECT * FROM tbl_static_services");
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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Procurement</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-pencil"></i> Static Service Settings</b>
      	</div>
      	<div class="page_scroller form-row">
      		<div style="margin: auto; margin-top: 5px; padding: 10px 20px; background-color: #fcc; border-radius: 5px;" class="col-sm-12">
            Out-patient Doctors Consultation Fee
          </div>
      			<div class="form-group col-6">
      				<label>Cash Clients</label>
      				<input class="form-control form-control-sm" id="opd_doc_cash" value="<?= $Static['opd_doc_cash']?>">
      			</div>
            <div class="form-group col-6">
              <label>Corporate Clients</label>
              <input class="form-control form-control-sm" id="opd_doc_cop" value="<?= $Static['opd_doc_cop']?>">
            </div>
          <div style="margin: auto; margin-top: 5px; padding: 10px 20px; background-color: #fcc; border-radius: 5px;" class="col-sm-12">
            In-Patient Doctor Review Fee
          </div>
      			<div class="form-group col-6">
      				<label>Cash Clients</label>
      				<input class="form-control form-control-sm" id="ipd_doc_cash" value="<?= $Static['ipd_doc_cash']?>">
      			</div>
            <div class="form-group col-6">
              <label>Corporate Clients</label>
              <input class="form-control form-control-sm" id="ipd_doc_cop" value="<?= $Static['ipd_doc_cop']?>">
            </div>
            <div style="margin: auto; margin-top: 5px; padding: 10px 20px; background-color: #fcc; border-radius: 5px;" class="col-sm-12">
              NHIF Rebate
            </div>
            <div class="form-group col-6">
              <label>NHIF Daily Rebate</label>
              <input class="form-control form-control-sm" id="ipd_nhif_rebate" value="<?= $Static['ipd_nhif_rebate']?>">
            </div>
      			<div class="form-group col-12">
      				<button class="btn btn-primary" onclick="SaveStaticServices()"><i class="oi oi-check"></i> Save</button>
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
    

	function SaveStaticServices(){
		var opd_doc_cash = (+$('#opd_doc_cash').val()).toFixed(2);
    var opd_doc_cop = (+$('#opd_doc_cop').val()).toFixed(2);
		var ipd_doc_cash = (+$('#ipd_doc_cash').val()).toFixed(2);
    var ipd_doc_cop = (+$('#ipd_doc_cop').val()).toFixed(2);
    var ipd_nhif_rebate = (+$('#ipd_nhif_rebate').val()).toFixed(2);

		if (isNaN(opd_doc_cash) || opd_doc_cash<0) {SnackNotice(false,'Enter valid Cash Clients Doctor Consultation Fee');$('#opd_doc_cash').focus(); return;}
    if (isNaN(opd_doc_cop) || opd_doc_cop<0) {SnackNotice(false,'Enter valid Corporate Clients Doctor Consultation Fee');$('#opd_doc_cop').focus(); return;}
		if (isNaN(ipd_doc_cash) || ipd_doc_cash<0) {SnackNotice(false,'Enter valid In-patient Cash Clients Doctor Review Fee');$('#ipd_doc_cash').focus(); return;}
    if (isNaN(ipd_doc_cop) || ipd_doc_cop<0) {SnackNotice(false,'Enter valid In-patient Corporate Clients Doctor Review Fee');$('#ipd_doc_cop').focus(); return;}
    if (isNaN(ipd_nhif_rebate) || ipd_nhif_rebate<0) {SnackNotice(false,'Enter valid In-patient NHIF Rebate Amount');$('#ipd_nhif_rebate').focus(); return;}

		$('#processDialog').modal('toggle');
		$.ajax({
			method:'POST',
			url:'CRUD.php',
			data:{SaveStaticServices:'1',
      opd_doc_cash:opd_doc_cash,
      opd_doc_cop:opd_doc_cop,
      ipd_doc_cash:ipd_doc_cash,
      ipd_doc_cop:ipd_doc_cop,
      ipd_nhif_rebate
    },
			success:function(response){
				$('#processDialog').modal('toggle');
				console.log(response);
				if (response.includes('success')) {
					SnackNotice(true,'Static service charges saved succesfully');
				}else{
					SnackNotice(false,response);
				}
			}
		});
	}
</script>
</body>
</html>