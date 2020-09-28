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
if (!($User_level=='admin' || $GroupPrivileges['laboratory_priv']==1)) {
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Laboratory</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-people"></i> Sampling Queue (Out-patient)</b>
        </div> 
          <div class="page_scroller">
            <table class="table table-sm table-bordered table-striped">
              <thead class="bg-dark text-light">
                <th>File No.</th>
                <th>Req. No.</th>
                <th>Date/Time</th>
                <th>Name.</th>
                <th>Request</th>
                <th>Sample</th>
                <th>Action</th>
              </thead>
              <tbody id="queue_tbody" style="cursor: pointer;">
           <!-- Add from CRUD-->
              </tbody>
            </table>
          </div>
      </div>
  </div>
</div>


<div class="modal fade" id="SamplingPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-primary">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Laboratory Sample Collection</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group col-6">
            <label>Request Code</label>
            <input id="req_id" class="form-control form-control-sm" readonly>
          </div>
          <div class="form-group col-6">
            <label>Patient From</label>
            <input id="patient_from" class="form-control form-control-sm">
          </div>
          <div class="form-group col-6">
            <label>Sample Collection Date</label>
            <input id="date_of_sample_collection" class="form-control form-control-sm" value="<?= date('d/m/Y')?>" readonly>
          </div>
          <div class="form-group col-6">
            <label>Investigation</label>
            <input id="investigation" class="form-control form-control-sm" placeholder="Investigation" readonly>
          </div>
          <div class="form-group col-6">
            <label>Specimen</label>
            <input id="specimen" class="form-control form-control-sm" >
          </div>
          <div class="form-group col-6">
            <label>Specimen Condition</label>
            <select id="specimen_cond" class="form-control form-control-sm">
              <option value="">Select</option>
              <option value="Good">Good</option>
              <option value="Haemolysed">Haemolysed</option>
              <option value="Clotted">Clotted</option>
              <option value="Insufficient">Insufficient</option>
              <option value="Bad">Wrong Container</option>
              <option value="Unlabelled Specimen">Unlabelled Specimen</option>
              <option value="Contaminated Specimen">Contaminated Specimen</option>
              <option value="Brough beyond stipulated time">Brough beyond stipulated time</option>
            </select>
          </div>
          <div class="form-group col-12">
            <label>Sample Collection Note</label>
            <textarea id="comment" class="form-control form-control-sm" placeholder="Your comment..."></textarea>
          </div>
          <div class="form-group col-3">
            <button class="btn btn-sm btn-success col-12" onclick="CollectSample()"><i class="oi oi-check"></i> Save</button>
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
    
    $(document).ready(function(){
      GetQueue();
    });
    
    setInterval(function(){
      GetQueue();
    },2000); 

    function GetQueue(){
      RichUrl($('#queue_tbody'),{GetOPDQueue:'1'});
    }

    function SetRequestProperties(row){
      $('#req_id').val(row.find('td:nth-child(2)').text());
      $('#patient_from').val('Out-patient');
      $('#investigation').val(row.find('td:nth-child(5)').text());
      $('#specimen').val(row.find('td:nth-child(6)').text());

      $('#SamplingPopUp').modal('toggle');
    }

    function CollectSample(){
      var req_id = $('#req_id').val();
      var patient_from  = 'Out-patient';
      var investigation  = $('#investigation').val();
      var specimen  = $('#specimen').val();
      var date_of_sample_collection = $('#date_of_sample_collection').val();
      var specimen_cond  = $('#specimen_cond').val();
      var receiving_officer_comment  = $('#comment').val();

      if (patient_from.length==0) {SnackNotice(false,'Enter the details of where the client is from');return;}
      if (date_of_sample_collection.length==0) {SnackNotice(false,'Enter the date of sample collection');return;}
      if (specimen.length==0) {SnackNotice(false,'Enter the specimen collected');return;}
      if (specimen_cond.length==0) {SnackNotice(false,'Select the condition of the specimen');return;}

      $('#processDialog').modal('toggle');
      $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{CollectSample:'1',req_id:req_id,patient_from:patient_from,investigation:investigation,specimen:specimen,date_of_sample_collection:date_of_sample_collection,specimen_cond:specimen_cond,receiving_officer_comment:receiving_officer_comment},
        success:function(response){
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            SnackNotice(true,'Sample collection saved');
            GetQueue();
            $('#SamplingPopUp').modal('toggle');
          }else{
            SnackNotice(false,response);
          }
        }
      });
    }
  </script>
</body>
</html>