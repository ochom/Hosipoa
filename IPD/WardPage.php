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
if (!($User_level=='admin' || $GroupPrivileges['ipd_general_service_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
}
$ward_id = $_GET["ward_id"];
$Ward = $db->ReadOne("SELECT * FROM tbl_ipd_wards WHERE ward_id='$ward_id'");
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
    .ward_area{
      float: left; height: auto; padding: 10px 20px; border-radius: 5px; margin:auto; margin-top: 10px;
    }
    .ward_area p{
     margin: 0px; padding: 5px 20px; font-weight: bold; background-color: #888; color: #fff; border-radius: 5px;
    }
    .bed{
      float: left; margin: 10px; width: 250px; height: 125px; 
      border-radius: 5px; box-shadow: 3px 3px 8px 2px rgba(0,0,0,0.5);
    }
    .bed span{
      color: #FFF; position: relative; left: 5px; top: 5px; font-weight: bold;
    }
    .empty_bed{
      background: url('../images/empty_bed.png'); background-size: cover;
    }
    .occupied_bed{
      background: url('../images/occupied_bed.png'); background-size: cover;
    }
    .empty_bed button{
      margin-top: 65px; margin-left: 130px; color: #292; cursor: pointer; border-radius: 5px; background: #FFF; border: none; padding:1px 5px;
    }
    .empty_bed button:hover{
     color: #fff; background-color: #292;
    }
    .occupied_bed button{
      margin-top: 40px; margin-left: 130px; color: #922; cursor: pointer; border-radius: 5px; background: #FFF; border: none; padding:1px 5px;
    }
    .occupied_bed button:hover{
     color: #fff; background-color: #922;
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> In-Patient</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style="background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-people"></i> <?= $Ward['ward_name']?></b>
        </div>
      <div id="page_conent" class="page_scroller">
        <!-- FROM CRUD -->
      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="SelectPatientPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 800px; margin-left: calc((100% - 800px)/2);">
      <div class="modal-header bg-success">
        <b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Admit Patient From OPD to this Ward/Bed</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-12">
              <input class="form-control form-control-sm" id="searchVal" onkeyup="searchOPD_Patient($(this).val())" placeholder="Search Patient using OPD Number or Patient Name...">
            </div> 
            <table class="table table-sm table-striped table-bordered">
              <thead class="bg-dark text-light">
                <th>OPD No.</th>
                <th>Name</th>
                <th>Sex</th>
                <th>Treatement Scheme</th>
              </thead>
              <tbody id="patient_list">
                <!-- FROM CRUD -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
</div>  


<div class="modal fade" id="AdmitPatientPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Admit patient to this ward bed</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <div class="modal-body">
          <div class="form-row">
            <label>Provisional Diagnosis</label>
            <table class="table table-sm" id="my_opd_diagnosis">
            <!-- FROM CRUD -->
            </table>
            <div class="form-group col-12">
              <label>Admission Notes</label>
              <textarea id="admission_notes" class="form-control form-control-sm" style="height: 150px;" placeholder="Admission Notes..."></textarea>
            </div>
            <div class="form-group col-12">
              <button class="btn btn-sm btn-success" onclick="AdmitPatient()"><i class="oi oi-plus"></i> Save</button>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>  

<!-- Proccessing dialog -->
<div class="modal modal-static" id="processDialog" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" >
    <div style="background-color: #eee;" id="progressline"><div class="box2"></div></div>  
</div>


<script>
  $("#menu-toggle").click(function(e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
  });


  var req = null;
  var selected_bed_number, selected_refno, selected_treatement_scheme;
  var ward_id = "<?= $ward_id?>";



  $(document).ready(function(){
      $('.page_scroller').load('wards.php?ward_id='+ward_id +' #beds');
    });
    
    setInterval(function(){
      $('.page_scroller').load('wards.php?ward_id='+ward_id +' #beds');
    },2000);


  function FindPatient(bed_number){
    selected_bed_number = bed_number;
    $('#SelectPatientPopUp').modal('toggle');
  }

  function searchOPD_Patient(searchVal) {
    if (req != null) {req.abort()}
    req = $.ajax({
      method:'post',
      url:'crud.php',
      data:{searchOPD_Patient:'1',searchVal:searchVal},
      success:function(response) {
        $('#patient_list').html(response);
      }
    });
  }


  function SelectPatient(refno,treatement_scheme){
    selected_refno = refno;
    selected_treatement_scheme = treatement_scheme;
    $('#SelectPatientPopUp').modal('toggle');
    $('#AdmitPatientPopUp').modal('toggle');
    $.ajax({
      method:'post',
      url:'crud.php',
      data:{GetMyOPDDiagnosis:'1',refno:selected_refno},
      success:function(response){
        $('#my_opd_diagnosis').html(response);
      }
    });
  }

  function AdmitPatient(){
    var admission_notes = $('#admission_notes').val();
    if (admission_notes=='') {SnackNotice(false,'Enter the admission notes');$('#admission_notes').focus();return;}
    $('#processDialog').modal('toggle');
    $.ajax({
      method:'post',
      url:'crud.php',
      data:{AdmitPatient:'1',refno:selected_refno,treatement_scheme:selected_treatement_scheme,ward_id:ward_id,bed_number:selected_bed_number,admission_notes:admission_notes},
      success:function(response) {
        console.log(response);
        $('#processDialog').modal('toggle');
        if (response.includes('success')) {
          $('#AdmitPatientPopUp').modal('toggle');
          SnackNotice(true,'Patient Admitted succesfully and allocated bed number '+selected_bed_number);
          $('.page_scroller').load('wards.php?ward_id='+ward_id +' #beds');
        }else{
          SnackNotice(false,response);
        }
      }
    });
  }
</script>
</body>
</html>