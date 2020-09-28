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
if (!($User_level=='admin' || $GroupPrivileges['records_priv']==1)) {
  header("refresh:0, url=../Permission.php");
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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Records</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">      		
      		<b><i class="oi oi-magnifying-glass"></i> Search Patients</b>
      	</div>
        <div class="page_scroller">
        	<div class="row">
        		<div class="form-group col-sm-12 col-md-4">
        			<select class="form-control form-control-sm" id="searchby">
                <option value="">Search By</option>
                <option value="refno">Reg. No</option>
        				<option value="fullname">Name</option>
        				<option value="idno">ID Number</option>
        				<option value="ins_card_no">Insurance Card No.</option>
        			</select>
        		</div>
            <div class="form-group col-sm-12 col-md-4">               
              <div class="input-group">
                <input  class="form-control form-control-sm" id="searchVal" onkeyup="SearchPatient()">
                <div class="input-group-prepend" onclick="SearchPatient()">
                    <span class="input-group-text"> <i class="oi oi-magnifying-glass"></i> </span>
                 </div>
              </div>
            </div>
        	</div>
	      	<table class="table table-bordered table-sm table-striped" style="margin: 10px;">
	      		<thead class="bg-dark text-light">
	      			<th>OPD No.</th>
	      			<th>Name</th>
	      			<th>ID Number</th>
	      			<th>Actions</th>
	      		</thead>
	      		<tbody id="patient_list">
				<!-- Add from crud--> 
	      		</tbody>
	      	</table>
      	</div>
      </div>
    </div>
  </div>


<div class="modal fade" id="SelectSchemePopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content" style="width: 600px; margin-left: calc((100% - 600px)/2);">
      <div class="modal-header bg-primary">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >Treatement Scheme</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="row">
            <div class="form-group col-sm-12">
              <label>Specify client treatement scheme</label>
              <select class="form-control form-control-sm" id="treatement_scheme">
                <option value="Cash">Cash</option>
                <option value="Corporate">Corporate</option>
              </select>
            </div> 
            <div class="form-group col-sm-12">
              <button class="btn btn-primary" onclick="CreateHealthFile()"><i class="oi oi-check"></i> Create Health File</button>
            </div>            
          </div>
          <span><b>Note: </b><i class="text-success">The System will automatically toggle services prices to match Scheme<i></i></span>
        </div>
      </div>
  </div>
</div>  


<div class="modal fade" id="AddSchemePopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 600px; margin-left: calc((100% - 600px)/2);">
      <div class="modal-header bg-primary">
        <b class="modal-title" id="exampleModalLabel" style="color: #FFF;" >Add Insurance Schemes to this Patient</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="form-row">
            <span>Patient Insurance Schemes</span>
            <table class="table table-sm table-bordered table-striped">
              <thead class="bg-dark text-light">
                <th>#</th>
                <th>Company</th>
                <th>Card Number</th>
              </thead>
              <tbody id="scheme_list">
                <!-- CRUD -->
              </tbody>
            </table>
          </div>
          <div class="row">
            <div class="form-group col-sm-12">
              <label>Registration Number</label>
             <input class="form-control form-control-sm" id="refno2" readonly>
            </div>
            <div class="form-group col-sm-7">
              <label>Insurance Company/Scheme</label>
              <select class="form-control form-control-sm" id="ins_company">
                <option value="">Select</option>
                <?php
                $result = mysqli_query($conn,"SELECT * FROM tbl_ins_companies ORDER BY company_name ASC");
                while ($Company = mysqli_fetch_assoc($result)) {
                  ?>
                  <option value="<?= $Company['company_id']?>"><?= $Company['company_name']?></option>
                  <?php
                }?>
              </select>
            </div> 
            <div class="form-group col-sm-5">
              <label>Insurance Card Number</label>
             <input class="form-control form-control-sm" id="card_no" >
            </div>
            <div class="form-group col-sm-12 col-md-6">
              <button class="btn btn-primary col-12" onclick="SaveScheme()"><i class="oi oi-check"></i> Save Scheme</button>
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
	var req = null,selected_refno = null,patient_insured = null;
  $("#menu-toggle").click(function(e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
  });

  function SearchPatient(){
  	var searchby = $('#searchby').val();
  	var searchVal = $('#searchVal').val();
    
    RichUrl($('#patient_list'),{SearchPatient:'1',searchby:searchby,searchVal:searchVal});
  }

  function SelectCheme(refno,insurance_status){
    selected_refno = refno;
    patient_insured = insurance_status;

    $('#SelectSchemePopUp').modal('toggle');

  }

  function CreateHealthFile(){
    var treatement_scheme = $('#treatement_scheme').val();
    if (treatement_scheme=='Corporate' && patient_insured=='NO') {SnackNotice(false,'This is not a Corporate Patient, Kindly Choose Cash');$('#treatement_scheme').focus(); return;}

    $('#processDialog').modal('toggle');
    $.ajax({
      method:'post',
      url:'crud.php',
      data:{CreateHealthFile:'1', refno:selected_refno,treatement_scheme:treatement_scheme},
      success:function(response){
        $('#processDialog').modal('toggle');
        $('#SelectSchemePopUp').modal('toggle');
        if (response.includes('success')) {
            SnackNotice(true,'Health File successfully Created');
            SearchPatient();
        }else{
          SnackNotice(false,response);
        }
      }
    });
  }

  function AddScheme(refno){
    selected_refno = refno;
    $('#refno2').val(selected_refno);
    GetMySchemes();

    $('#AddSchemePopUp').modal('show');
  }

  function GetMySchemes() {
    RichUrl($('#scheme_list'),{GetMySchemes:'1',refno:selected_refno});
  }

  function SaveScheme(){
    var company_id = $('#ins_company').val();
    var card_no = $('#card_no').val();
    if (company_id=='') {SnackNotice(false,'Select the insurance scheme to add...'); $('#ins_company').focus(); return;}
    if (card_no=='') {SnackNotice(false,'Enter a valid card number of the selected card scheme...'); $('#card_no').focus(); return;}
    RitchConfirm("Proceed ?","<b>Are you sure you want to add this Scheme to the patient</b>.<br>Note: You schould only add a scheme after verifying it's credibility.").then(function(){
      $('#processDialog').modal('toggle');
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{SaveScheme:'1',refno:selected_refno,company_id:company_id,card_no:card_no},
        success:function(response){
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            SnackNotice(true,"Insurance Scheme successfully added to the clients's scheme list");
            GetMySchemes();
            $('.form-control').val('');
            $('#refno2').val(selected_refno);
          }else{
            SnackNotice(false,response);
          }
        }
      });
    });
  }
</script>
</body>
</html>