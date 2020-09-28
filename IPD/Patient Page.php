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

$Hospital = $db->ReadOne("SELECT * FROM tbl_hospital"); 
$fileno = $_GET['adm_no'];
$IPDFILE = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE adm_no = '$fileno'");
$refno =  $IPDFILE['refno'];
$Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$refno'"); 
$age = $db->getPatientAge($Patient['dob']);

$bed = $db->ReadOne("SELECT * From tbl_ipd_beds where bed_status = '$refno'"); 
$ward = $db->ReadOne("SELECT * From tbl_ipd_wards where ward_id = '$bed[ward_id]'"); 

if (empty($Patient['image'])) {
  $image = "../images/passport.png";
}else{
  $image = $Patient['image'];
}

?>
<!DOCTYPE html>
<html>
<head>
  <!-- Links -->
  <?php
    include '../sub_links.php';
  ?>
  <!-- Sign JS -->

  <!-- L:inks -->
  <style type="text/css">
    .requestButtons{
      position: fixed; bottom: 20px; left: 45%; text-decoration: none;
    }

    #tabs { 
      background: transparent; width: 100%; padding: 0px; font-size: 1rem;
    }
    #tabs .scroller{
      height: 60px;
      overflow: auto;
    }
    #tabs ul{
      width: 100%; 
      border: 0;
    } 
    #tabs .ui-widget-header { 
        background: #9f9; 
        border: none; 
        border-bottom: 1px solid #c0c0c0; 
        -moz-border-radius: 0px; 
        -webkit-border-radius: 0px; 
        border-radius: 0px; 
    } 
    #tabs .ui-tabs-nav .ui-state-default { 
        background: #252; 
        border: none; 
    } 
    #tabs .ui-tabs-nav .ui-state-active { 
        background: transparent url(../images/uiTabsArrow.png) no-repeat bottom center; 
        border: none; 
    } 
    #tabs .ui-tabs-nav .ui-state-default a { 
        color: #ffd;  font-size: 12px;  padding: 5px;
    } 
    #tabs .ui-tabs-nav .ui-state-active a { 
        color: #252;  
        background-color: white; border: none;
    }
  </style>
  <style type="text/css">
    .SlideMore {
      height: calc(100% - 80px); width: 0px;  position: fixed; top: 30px;  right: 20px;
      transition: 2s; overflow: hidden;
      background-color: #ccc;

      z-index: 999;
      border-radius: 5px;
      -webkit-transition: width 1s ease;
      -moz-transition: width 1s ease;
      -o-transition: width 1s ease;
      transition: width 1s ease;

    }
    .in { 
      width: calc(100% - 60px);  border: 1px solid grey;
    }

    .slide_title{
      text-align: right; padding-right: 5px; color: #000; background-color: #fff;
    }
    .slide_title button{
      margin-left: 1px; border: none; background-color: transparent; cursor: pointer; font-size: 15px; padding: 2px 12px;
    }
    .slide_title button:hover{
      background-color: grey;
    }

    /*CODIFICATION*/
    .SearchResult{
        position:absolute;top: 80px; border: none; border-radius: 3px;
        height: 400px; max-height: 400px; margin:auto; background-color: rgba(255,255,255,0.9); 
        box-shadow: 0px 2px 3px #ccc;   padding: 2px 2px 5px 2px; z-index: 100; 
        cursor: pointer; overflow-y: scroll; display: none;
    }
    .SearchResult i{
        margin-bottom: 5px; border-bottom: 5px solid grey;
    }
    .SearchResult i:hover{
        font-weight: bold;  border-bottom: 1px solid rgb(255,150,0);
    }
    .SearchResult i:hover  .code{
        background-color: rgba(255,150,0); border-radius: 4px;
    }
    .SearchResult i span{
        float: left; padding: 2px 5px; overflow: hidden;  height: auto;
    }
    /*print*/
    @media print{
      @page { size: auto; margin: 0px;}
      body  { margin: 0px; padding: 0px;}
    } 
  </style>
</head>
<body>
  <div class="SlideMore">
    <div class="slide_title"><button class="print_me"><i class="oi oi-print"></i> Print</button><button class="close_me">X</button></div>
    <div id="canvas_container" style="padding: 10px; height: 100%; background-color: #fff;">
      <canvas id="MonitorChart" style="background-color: #fff; width: 100%; height: calc(100% - 20px);"></canvas>
    </div>
  </div>
  <div style="position: fixed; bottom: 0;right: 5px; z-index: 999;">
    <button class="btn btn-sm btn-success" onclick="initMonitors('bp')">BP Monitor</button>
    <button class="btn btn-sm btn-success" onclick="initMonitors('temperature')">Temperature Monitor</button>
    <button class="btn btn-sm btn-success" onclick="initMonitors('weight')">Weight Monitor</button>
    <button class="btn btn-sm btn-success" onclick="initMonitors('pulse')">Pulse Monitor</button>
  </div>
<div class="d-flex" id="wrapper">
    <!-- Sideline -->
    <?php
      include('sidebar.php');
    ?>
    <!-- /#sideline-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
      <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <span class="navbar-toggler-icon" id="menu-toggle"></span>  
        <div class="navbar-header">
          <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> IPD</a>
        </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
          <div class="col-11" style="height: auto; border-radius: 3px; border:1px solid #CCC; margin: auto; margin-top: 5px;">
            <span style="width: 100%; padding: 5px; background-color: #2c2; position: absolute; left: 0; text-align: center;">
              <b><?= $Patient['fullname']?></b> (<?= $refno?>) - <?= $Patient['sex'].", ".$age.", DoB - ".$Patient['dob']?>
            </span>
            <div style="margin-top: 35px;">
              <img style="position: absolute; top: 0; height: 50px; width: 50px; border-radius: 50%; border: 1px solid #ccc; background-color: #fff; overflow: hidden; margin: 5px; float: left;" src="<?= $image?>">
              <p align="center">
                <b><i class="oi oi-heart text-danger"></i> IPD Health File ( File No.: <?= $fileno ?>)</b><br>
                <b>DoA:</b> <?= $IPDFILE['adm_date']?>, <b>Ward: </b> <?= $ward['ward_name']?>, <b>Bed: </b><?= $bed['bed_number']?>
              </p> 

            </div> 
          </div>
         <div class="page_scroller" style="height: calc(100vh - 200px);">
          <div id="tabs" style="max-height: calc(100vh - 200px); overflow: auto;">
            <div class="scroller">
              <ul>              
                <li><a href="#tab1">Observation Chart</a></li>
                <li><a href="#tab2">Reviews</a></li>
                <li><a href="#tab3">Cardex</a></li>
                <li><a href="#tab4">Items</a></li>
                <li><a href="#tab5">Theatre</a></li>
                <li><a href="#tab6">Investigations</a></li>        
                <li><a href="#tab7">Diagnosis</a></li>  
                <li><a href="#tab8">Medication</a></li> 
                <li><a href="#tab9">Discharge</a></li>  
                <li><a href="#tab10">Summary (Rx)</a></li>    
              </ul>              
            </div>
            <div id="tab1">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Home.php" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Wards</a>
                <a data-toggle="modal" data-target="#VitalsPopUp" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Add</a>
              </div>
              <table class="table table-sm table-striped table-bordered" style="margin-top: 20px;">
                <thead class="bg-success text-light">
                  <th>#</th>
                  <th>Date/Time</th>
                  <th>Temperature (<sup>0</sup>C)</th>
                  <th>Weight (Kg)</th>
                  <th>BP (mm/Hg)</th>
                  <th>Pulse (bpm)</th>
                  <th>Respiration (bpm)</th>
                  <th>Remarks</th>
                  <th>Nurse/Dr.</th>
                </thead>
                <tbody class="vitals_list">
                  <!-- from crud -->
                </tbody>
              </table>
            </div> 
            <div id="tab2">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Home.php" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Wards</a>
                <a data-toggle="modal" data-target="#ObservationPopUp" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Add</a>
              </div>
              <table class="table table-sm table-striped table-bordered" style="margin-top: 20px;">
                <thead class="bg-success text-light">
                  <th>#</th>
                  <th>Date/Time</th>
                  <th>Patient Complaint</th>
                  <th>Examination observation</th>
                  <th>Nursing Remarks</th>
                  <th>Nurse/Dr.</th>
                </thead>
                <tbody class="observations_list">
                  <!-- from crud -->
                </tbody>
              </table>
            </div>   
            <div id="tab3">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Home.php" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Wards</a>
                <a data-toggle="modal" data-target="#CardexPopUp" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Add</a>
              </div>
              <table class="table table-sm table-striped table-bordered" style="margin-top: 20px;">
                <thead class="bg-success text-light">
                  <th>#</th>
                  <th>Date/Time</th>
                  <th>Nursing Intervention/Remarks</th>
                  <th>Nurse</th>
                </thead>
                <tbody class="cardex_list">
                  <!-- from crud -->
                </tbody>
              </table>
            </div>     
            <div id="tab4">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Home.php" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Wards</a>
                <a data-toggle="modal" data-target="#ItemsPopUp" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Add</a>
              </div>
              <table class="table table-sm table-striped table-bordered" style="margin-top: 20px;">
                <thead class="bg-success text-light">
                  <th>#</th>
                  <th>Date/Time</th>
                  <th>Item</th>
                  <th>Quantity</th>
                  <th>Nurse/Dr.</th>
                </thead>
                <tbody class="item_list">
                  <!-- from crud -->
                </tbody>
              </table>
            </div>
            <div id="tab5">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Home.php" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Wards</a>
                <a data-toggle="modal" data-target="#TheatrePopUp" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Create new procedure</a>
              </div>
              <table class="table table-sm table-striped table-bordered" style="margin-top: 20px;">
                <thead class="bg-success text-light">
                  <th>#</th>
                  <th>Date/Time</th>
                  <th>Procedure Name</th>
                  <th>Cost</th>
                  <th>Booked By</th>
                  <th>Surgeon</th>
                  <th>Scheduled Date</th>
                  <th>Checklists (Actions)</th>
                </thead>
                <tbody class="procedures_list">
                  <!-- ADD FROM CRUD -->
                </tbody>
              </table>
            </div>   
            <div id="tab6">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Home.php" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Wards</a>
                <a data-toggle="modal" data-target="#InvestigationPopUp" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Add</a>
              </div>
              <div style="margin-top: 20px;">
                <h3 style="border-bottom: 1px solid lime;">Investigation Requests</h3>
                <table class="table table-sm table-striped table-bordered">
                  <thead class="bg-success text-light">
                    <th>Date/Time</th>
                    <th>Request Name</th>
                    <th>Category</th>
                    <th>Request Cost</th>
                    <th>Status</th>
                    <th>Doctor</th>
                  </thead>
                  <tbody class="investigation_request_list">
                    <!-- ADD FROM CRUD -->
                  </tbody>
                </table>
                <h3 style="border-bottom: 1px solid lime;">Investigation Results</h3>
                <table class="table table-sm table-striped table-bordered">
                  <thead class="bg-success text-light">
                    <th>Category</th>
                    <th>Date/Time</th>
                    <th>Investigation</th>
                    <th>Specimen/Sample</th>
                    <th>Turn Around Time</th>
                    <th>Range</th>
                    <th>Results</th>
                    <th>Status</th>
                  </thead>
                  <tbody class="investigation_results_list">
                    <!-- ADD FROM CRUD -->
                  </tbody>
                </table>
              </div>
            </div>   
            <div id="tab7">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Home.php" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Wards</a>
                <a data-toggle="modal" data-target="#DiagnosisPopUp" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Add</a>
              </div>
              <table class="table table-sm table-striped table-bordered" style="margin-top: 20px;">
                <thead class="bg-success text-light">
                  <th>Date/Time</th>
                  <th>Disease Name</th>
                  <th>Disease Code</th>
                  <th>Diagnosis Note</th>
                  <th>Doctor</th>
                </thead>
                <tbody class="diagnosis_list">
                  <!-- ADD FROM CRUD -->
                </tbody>
              </table>
            </div> 
            <div id="tab8">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Home.php" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Wards</a>
                <a data-toggle="modal" data-target="#DrugListPopUp" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Order Drug</a>
              </div>
              <table class="table table-sm table-striped table-bordered" style="margin-top: 20px;">
                <thead class="bg-success text-light">
                  <th>#</th>
                  <th>Date/Time</th>
                  <th>Drug Name</th>
                  <th>Qty Prescribed</th>
                  <th>Qty Issued</th>
                  <th>Instructions</th>
                  <th>Dosage</th>
                  <th>Doctor</th>
                  <th>Action (Issue Drug)</th>
                </thead>
                <tbody class="prescription_list">
                  <!-- ADD FROM CRUD -->
                </tbody>
              </table>
            </div>
            <div id="tab9">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Home.php" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Wards</a>
                <a data-toggle="modal" data-target="#DispositionPopUp" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Add</a>
              </div>
              <table class="table table-sm table-striped table-bordered" style="margin-top: 20px;">
                <thead class="bg-success text-light">
                  <th>Date/Time</th>
                  <th>Care/Plan</th>
                  <th>Destination</th>
                  <th>Reason</th>
                  <th>Instructions</th>
                  <th>Doctor</th>
                </thead>
                <tbody class="dispostion_list">
                  <tr>
                    <td><?= $IPDFILE['discharge_date'] ?></td>
                    <td><?= $IPDFILE['discharge_type'] ?></td>
                    <td><?= $IPDFILE['discharge_destination'] ?></td>
                    <td><?= $IPDFILE['discharge_reason'] ?></td>
                    <td><?= $IPDFILE['discharge_instructions'] ?></td>
                    <td><?= $IPDFILE['discharged_by'] ?></td>
                  </tr>
                </tbody>
              </table>
            </div> 
            <div id="tab10">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Home.php" class="btn btn-sm btn-outline-success" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Wards</a>
                <button class="btn btn-sm btn-outline-success" onclick="PrintSummary()" style="float: right;  margin-right: 5px;"><i class="oi oi-print"></i> Print</button>
              </div>
              <div id="summary" style="background-color: #ccc; height: auto; width: 100%; margin-top: 20px; padding: 20px 0px;">
                <!-- FROM JS -->
              </div>
            </div>                              
          </div>
          </div>
        </div>
      </div>
    </div>


<div class="radiology_result_image_preview">
  <button id="close_image_preview">x</button>
  <button class="button" onclick="PrevImage()"><i class="oi oi-chevron-left"></i></button>
  <button class="button2" onclick="NextImage()"><i class="oi oi-chevron-right"></i> </button>
  <img class="image_preview" src="">
</div>


<div class="modal fade" id="VitalsPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Vitals Monitor (Add)</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group col-6">
            <label>Temperature (<sup>0</sup>C)</label>
            <input class="form-control form-control-sm" id="temperature" placeholder="35.0 - 42.0">
          </div>
          <div class="form-group col-6">
            <label>Weight (Kg)</label>
            <input class="form-control form-control-sm" id="weight" placeholder=">1">
          </div>
          <div class="form-group col-6">
            <label>BP-Systolic</label>
            <input class="form-control form-control-sm" id="bp_systolic" placeholder="100-129">
          </div>
          <div class="form-group col-6">
            <label>BP-Diastolic</label>
            <input class="form-control form-control-sm" id="bp_diastolic" placeholder="60-84">
          </div>
          <div class="form-group col-6">
            <label>Pulse (bpm)</label>
            <input class="form-control form-control-sm" id="vitals_pulse" placeholder=">60">
          </div>
          <div class="form-group col-6">
            <label>Respiration  (bpm)</label>
            <input class="form-control form-control-sm" id="vitals_respiration" placeholder="0.0">
          </div>
          <div class="form-group col-12">
            <label>Remarks</label>
            <input class="form-control form-control-sm" id="vitals_remarks" placeholder="Remarks...">
          </div>
          <div class="form-group col-12">
            <button class="btn btn-sm btn-success" onclick="SaveVitals()">></i> Save</button>
          </div>  
        </div>
      </div>
    </div>
  </div>
</div>  

<div class="modal fade" id="ObservationPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Observations and Examination</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row">
        <div class="form-group col-12">
            <label>Patient Complaint/Remarks</label>
            <textarea  class="form-control form-control-sm" id="complaint" placeholder="Patient Remarks..."></textarea>
        </div>
        <div class="form-group col-12">
            <label>Physical Examination Observation</label><br>
            <textarea  class="form-control form-control-sm" id="physical_examination_note" placeholder="Any physical Examination Observations..."></textarea>
        </div>
        <div class="form-group col-12">
            <label>Doctor/Nurse Remarks</label><br>
            <textarea class="form-control form-control-sm" id="nursing_note" placeholder="Nursing notes..."></textarea>
        </div>
        <div class="form-group col-2">
            <button class="btn btn-sm btn-success" onclick="SaveObservations()">Save</button>
        </div>  
          </div>
          </div>
      </div>
    </div>
</div>  

<div class="modal fade" id="CardexPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Patient's Cardex</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group col-12">
              <label>Nursing Intervention/Remarks</label>
              <textarea class="form-control form-control" style="height: 150px;" id="cardex_remarks" placeholder="Nursing intervention notes...."></textarea>
            </div>
            <div class="form-group col-12">
              <button class="btn btn-sm btn-success" onclick="SaveCardex()"><i class="oi oi-check"></i> Add</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>  


<div class="modal fade" id="ItemsPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 800px; margin-left: calc((100% - 800px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Add Items provided to the patient</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-7">
            <div class="form-row">
              <div class="form-group col-6">
                <label>Item Type</label>
                <select class="form-control form-control-sm" onchange="GetAvailableItems()" id="consumable_type">
                  <option value="">Select</option>
                  <option value="Consumable">Consumable Item</option>
                  <option value="General Service">General Service</option>
                </select>
              </div>
              <div class="form-group col-6">
                <label>Search by Name</label>
                <input class="form-control form-control-sm col-12" id="cons_search" placeholder="Type to search items..." onkeyup="GetAvailableItems()">
              </div>
              <table class="table table-sm table-striped table-bordered">
                <thead class="bg-dark text-light">
                  <th>Item</th>
                  <th>Cost (Ksh.)</th>
                </thead>
                <tbody id="available_items_list">
                  <!-- FROM CRUD -->
                </tbody>
              </table>
            </div>
          </div>
          <div class="col-5">
            <div class="form-group col-12">
              <label>Item Name</label>
              <input class="form-control form-control-sm" id="item_name" readonly>
            </div>
            <div class="form-group col-12">
              <label>Cost</label>
              <input class="form-control form-control-sm" id="item_cost" readonly>
            </div>
            <div class="form-group col-12">
              <label>Quantity</label>
              <input class="form-control form-control-sm" id="item_quantity">
            </div>
            <div class="form-group col-12">
              <button class="btn btn-sm btn-success" onclick="SaveItem()"><i class="oi oi-check"></i> Add</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>  


<div class="modal fade" id="TheatrePopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Theatre Form (Create)</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-7">
            <input class="form-control form-control-sm" id="search_procedures" onkeyup="GetProceduresServices()"  placeholder="Type to search operations...">
            <table class="table table-sm table-striped table-bordered">
              <thead class="bg-dark text-light">
                <th>Procedure Name</th>
                <th>Cost (Ksh.)</th>
              </thead>
              <tbody id="procedure_service_list">
                <!-- FROM CRUD -->
              </tbody>
            </table>
          </div>
          <div class="col-5">
            <div class="form-group col-12">
              <label>Procedure Name</label>
              <input class="form-control form-control-sm" id="procedure_name" readonly>
            </div>
            <div class="form-group col-12">
              <label>Cost</label>
              <input class="form-control form-control-sm" id="procedure_cost" readonly>
            </div>
            <div class="form-group col-12">
              <label>Date Scheduled</label>
              <input type="date" class="form-control form-control-sm" id="scheduled_date">
            </div>
            <div class="form-group col-12">
              <label>Main Surgeon's Name</label>
              <input class="form-control form-control-sm" id="surgeon_name">
            </div>
            <div class="form-group col-12">
              <button class="btn btn-sm btn-success" onclick="SaveProcedure()"><i class="oi oi-check"></i> Save</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>  

<div class="modal fade" id="PatientConsentPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Patient Consent</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <p><input type="checkbox" id="patient_agree"> <b>I hereby give permission for anaesthesia and for any medical or surgical treatement which doctors may consider neccessary to be performed upon me/my spouse/my child</b> (Nakubali dawa ya kupoteza fahamu itumiwe na pia daktari aweze kufanya utabibu wowote ambao ataona ni lazima)</p>
          <div class="form-group col-sm-4">
            <label>Witness (Shahidi)</label>
            <select class="form-control form-control-sm" id="witness">
              <option value="">Select</option>
              <option value="Father">Father</option>
              <option value="Mother">Mother</option>            
              <option value="Brother">Brother</option>            
              <option value="Sister">Sister</option>
              <option value="Husband">Husband</option>
              <option value="Wife">Wife</option>
              <option value="Grandmother">Guardian</option>
            </select>
          </div>
          <div class="form-group col-sm-8">
            <label>Witness Name (Majina ya Shahidi)</label>
            <input class="form-control form-control-sm" id="witness_name">
          </div>
          <div class="col-sm-12" style="border: 1px solid #0f0;border-radius: 5px; padding: 3px;">
            <b class="text-primary">Sign Here (Weka Sahihi yako hapa)</b><br>
            <!-- Canvas -->
            <canvas id="sketchpad" height="300" width="650" style="border:1px solid #ccc;"></canvas>
            <button class=" btn btn-sm btn-outline-success" onclick="UseSign()"><i class="oi oi-check"></i> Use</button>
            <button class=" btn btn-sm btn-outline-secondary" onclick="SnackNotice(false,'Crop feature is not enabled, contact system admin.')"><i class="oi oi-crop"></i> Crop</button>
            <button class=" btn btn-sm btn-outline-danger" onclick="clearCanvas(canvas,ctx);"><i class="oi oi-x"></i> Clear</button>
          </div>         
          <div class="col-sm-6">
            <P>
              Date (Tarehe): <b><u><?= date('d/m/Y H:i:s')?></u></b><br>
              <div>
                <img id="patient_sign" src="" alt="Sign" style="width: 250px; height: 70px; border: none;"><br>
              </div>
              <b>Signature (Sahihi)</b>
            </P>
          </div>
          <div class="col-sm-6" style="padding: 10px;">
            <button class="btn btn-sm btn-success" onclick="SavePatientConsent()"><i class="oi oi-check"></i> Save Consent</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="NurseChecklistPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Nurse Checklist</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row" style="padding: 10px;">
          <div class="form-group col-12">
            <label><b>(A)</b> Does the patient has any personal belongings ?</label>
            <select  id="has_belongings" onchange="if($(this).val()=='Yes'){$('#belongings').attr('readonly',false)}else{$('#belongings').attr('readonly',true)}">
              <option value="">No</option>
              <option value="Yes">Yes</option>
            </select>
            <textarea  id="belongings" class="form-control form-control-sm" readonly placeholder="Specify any belongings that the patient has"></textarea>
          </div>
          <div class="form-group col-12">
            <b>(B)</b> I.V Urinalysis
          </div>
          <div class="form-group col-4">
            <label>Sugar</label><input id="sugar" class="form-control form-control-sm">
          </div>
           <div class="form-group col-4">
            <label>Albumin</label><input id="albumin" class="form-control form-control-sm ">
          </div>
           <div class="form-group col-4">
            <label>Blood available in Litres</label><input id="blood_in_litres" class="form-control form-control-sm" placeholder="0.0">
          </div>
          <div class="form-group col-12">
            <label><b>(C)</b> Bladder Check and urinary</label><br>
            <label>Catheter, Gastric Tube, X-Rays</label><textarea  id="bladder_n_urinary" class="form-control form-control-sm"></textarea>
          </div>
          <div class="form-group col-12">
            <label>Nurse' Note</label><textarea  id="nurse_note" class="form-control form-control-sm"></textarea>
          </div>
          <button class="btn btn-sm btn-success" onclick="SaveNurseChecklist()"><i class="oi oi-plus"></i> Save</button>
        </div>
      </div>
    </div>
  </div>
</div>  

<div class="modal fade" id="DoctorChecklistPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Doctor Checklist</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row" style="padding: 10px;">
          <div class="form-group col-6">
            <label>Hydration Normal ?</label>
            <select  id="hydration">
              <option value="">No</option>
              <option value="Yes">Yes</option>
            </select>
          </div>
          <div class="form-group col-6">
            <label>Electrolyte Normal ?</label>
            <select  id="electrolyte">
              <option value="">No</option>
              <option value="Yes">Yes</option>
            </select>
          </div>
          <div class="form-group col-12">
            <label>Chest Normal ?</label>
            <select  id="chest">
              <option value="">No</option>
              <option value="Yes">Yes</option>
            </select>
          </div>
          <div class="form-group col-4">
            <label>Hb (Gms)</label><input id="hb" class="form-control form-control-sm">
          </div>
          <div class="form-group col-4">
            <label>PVC (%)</label><input id="pvc" class="form-control form-control-sm ">
          </div>
          <div class="form-group col-4">
            <label>Temperature (<sup>0</sup>C)</label><input id="doctro_temp" class="form-control form-control-sm" placeholder="35.0-42.0">
          </div>
          <div class="form-group col-4">
            <label>Bp Systolic (mm/Hg)</label><input id="bp_sys" class="form-control form-control-sm" placeholder="100-129">
          </div>
          <div class="form-group col-4">
            <label>Bp Diastolic (mm/Hg)</label><input id="bp_dia" class="form-control form-control-sm" placeholder="60-85">
          </div>
          <div class="form-group col-4">
            <label>Pulse (bpm)</label><input id="pulse" class="form-control form-control-sm" placeholder=">60">
          </div>
          <div class="form-group col-4">
            <label>Blood available (Litres)</label><input id="blood" class="form-control form-control-sm">
          </div>
          <div class="form-group col-12">
            <label>Doctor's Note</label><textarea  id="doctor_note" class="form-control form-control-sm"></textarea>
          </div>
          <button class="btn btn-sm btn-success" onclick="SaveDoctorChecklist()"><i class="oi oi-plus"></i> Save</button>
        </div>
      </div>
    </div>
  </div>
</div>  


<div class="modal fade" id="AnaesthetistChecklistPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Anaesthetist Checklist</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row" style="padding: 10px;">
          <div class="form-group col-4">
            <label>Hb (Gms)</label><input id="ana_hb" class="form-control form-control-sm">
          </div>
          <div class="form-group col-4">
            <label>PVC (%)</label><input id="ana_pvc" class="form-control form-control-sm ">
          </div>
          <div class="form-group col-4">
            <label>Temperature (<sup>0</sup>C)</label><input id="ana_temp" class="form-control form-control-sm " placeholder="35.0-42.0">
          </div>
          <div class="form-group col-4">
            <label>Bp Systolic (mm/Hg)</label><input id="ana_bp_sys" class="form-control form-control-sm" placeholder="100-129">
          </div>
          <div class="form-group col-4">
            <label>Bp Diastolic (mm/Hg)</label><input id="ana_bp_dia" class="form-control form-control-sm" placeholder="60-85">
          </div>
          <div class="form-group col-4">
            <label>Pulse (bpm)</label><input id="ana_pulse" class="form-control form-control-sm" placeholder=">60">
          </div>
          <div class="form-group col-4">
            <label>Albumin</label><input id="ana_albumin" class="form-control form-control-sm ">
          </div>
          <div class="form-group col-4">
            <label>Sugar</label><input id="ana_sugar" class="form-control form-control-sm ">
          </div>
          <div class="form-group col-12">
            <label>Anaesthetist's Note</label><textarea  id="ana_note" class="form-control form-control-sm"></textarea>
          </div>
          <div class="form-group col-12">
            <label>Is the patient fit for operation ?</label>
            <select  id="patient_fit">
              <option value="">No</option>
              <option value="Yes">Yes</option>
            </select>
          </div>
          <button class="btn btn-sm btn-success" onclick="SaveAnaesthetistChecklist()"><i class="oi oi-plus"></i> Save</button>
        </div>
      </div>
    </div>
  </div>
</div>  

<div class="modal fade" id="OperationDiagnosisPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Operation Diagnosis</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group col-8">
            <label>Operation Name</label>
            <input class="form-control form-control-sm" id="op_name" onkeyup="SearchOperationCode($(this).val())" placeholder="Type to Search...">
          <div class="progressline" id="op_progressline" style="background-color: #eee; overflow: hidden;">
            <div class="box2" style="height: 2px; border-radius: 1px;"></div>
          </div>
          </div>
          <div class="form-group col-4">
            <label>ICD10 Code</label>
            <input class="form-control form-control-sm " id="op_code" readonly>
          </div>
          <!-- Results list -->
          <div class="col-11 SearchResult" id="op_code_list">
          <!-- ADD FROM CRUD -->
          </div>
          <div class="form-group col-12">
            <label>Diagnosis Note</label>
            <textarea class="form-control form-control-sm " id="op_comment" placeholder="Diagnosis Note..."></textarea>
          </div>            
          <div class="form-group col-12">
            <button class="btn btn-success btn-sm" onclick="SaveOPDiagnosis()"><i class="oi oi-check"></i> Save</button>
          </div>
          </div>
        </div>
      </div>
    </div>
</div> 

<div class="modal fade" id="InvestigationPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 800px; margin-left: calc((100% - 800px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Investigation Request</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group col-5">
            <label>Service Point</label>
            <select class="form-control form-control-sm" onchange="GetInvestigationServices($(this).val(),'')" id="service_point">
              <option value="">Select</option>
              <option value="Laboratory">Laboratory</option>
              <option value="Radiology">Radiology</option>
            </select>
          </div>
          <div class="form-group col-7">
            <label>Search by Name</label>
            <input class="form-control form-control-sm col-12" placeholder="Search Investigation..." onkeyup="GetInvestigationServices($('#service_point').val(),$(this).val())">
          </div>
          <div class="fform-group col-6">
            <button class="btn btn-primary btn-sm" onclick="SaveRequest()"><i class="oi oi-check"></i> Request Selected Investigation</button>
          </div>
          <div class="form-group col-6">
            <b>Total</b> Ksh. <b id="total">0</b>
          </div>
        </div>
        <table class="table table-striped table-sm table-bordered" style="cursor: pointer;">
          <thead class="bg-dark text-light">
            <th><i class="oi oi-check text-success"></i></th>
            <th>Service Code</th>
            <th>Service Name</th>
            <th>Category</th>
            <th>Cost</th>
          </thead>
          <tbody id="investigation_service_list">
           <!-- Add from CRUD -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>  

<div class="modal fade" id="DiagnosisPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Disease Diagnosis</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group col-8">
            <label>Disease Name</label>
            <input class="form-control form-control-sm" id="d_name" onkeyup="SearchDisease($(this).val())" placeholder="Type to Search...">
          <div class="progressline" id="progressline" style="background-color: #eee; overflow: hidden;">
            <div class="box2" style="height: 2px; border-radius: 1px;"></div>
          </div>
          </div>
          <div class="form-group col-4">
            <label>ICD10 Code</label>
            <input class="form-control form-control-sm " id="d_code" readonly>
          </div>
          <!-- Results list -->
          <div class="col-11 SearchResult" id="code_list">
          <!-- ADD FROM CRUD -->          
          </div>
          <div class="form-group col-12">
            <label>Diagnosis Note</label>
            <textarea class="form-control form-control-sm " id="d_comment" placeholder="Diagnosis Note..."></textarea>
          </div>            
          <div class="form-group col-12">
            <button class="btn btn-success btn-sm" onclick="SaveDiagnosis()"><i class="oi oi-check"></i> Save</button>
          </div>
          </div>
        </div>
      </div>
    </div>
</div> 

<div class="modal fade" id="DrugListPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Drug Prescription</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">  
        <div class="form-row">
            <div class="form-group col-12">                   
              <label>Search</label>
              <input class="form-control form-control-sm" placeholder="Type to search drug..." id="drug_search" onkeyup="GetDrugList()">
            </div>          
            <table class="table table-sm table-striped table-bordered">
              <thead class="bg-dark text-light">
                <th>Drug</th>
                <th>Category</th>
                <th>Sub Category</th>
                <th>Cost</th>
                <th>Billing</th>
              </thead>
              <tbody id="drug_list">
                <!-- FROM CRUD -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>
</div>  

<div class="modal fade" id="PrescriptionPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Drug Prescription</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">  
        <div class="form-row">
            <div class="form-row">
                <div class="form-group  col-12">                   
                  <label>Drug Name</label>
                  <input class="form-control form-control-sm" id="drugname" readonly>
                </div>  
                <div class="form-group col-4">
                  <label>Store</label>
                  <input class="form-control form-control-sm" id="drug_total_pieces" readonly>
                </div>        
                <div class="form-group col-4">
                  <label>Cost/unit</label>
                  <input class="form-control form-control-sm" id="drug_rate" readonly>
                </div>      
                <div class="form-group col-4">
                  <label>Payment</label>
                  <input class="form-control form-control-sm" id="drug_payment" readonly>
                </div>
                <div class="form-group col-4">
                  <label>Dosage</label>
                  <input  onkeyup="if(+$(this).val()> 5){$(this).val(''); SnackNotice(false,'That input value is too high limit(5)');}" class="form-control form-control-sm" id="dosage">
                </div>
                <div class="form-group col-4">
                  <label>Frequency</label>
                  <input onkeyup="if(+$(this).val()> 12){$(this).val('');SnackNotice(false,'That input value is too high limit(12)');}" class="form-control form-control-sm" id="freq">
                </div>
                <div class="form-group col-4">
                  <label>Days</label>
                  <input onkeyup="if(+$(this).val()> 360){$(this).val('');SnackNotice(false,'That input value is too high limit(360)');}" class="form-control form-control-sm" id="days">
                </div>
                <div class="form-group col-12">
                  <label>Additional Instructions</label>
                  <textarea style="height: 30px;" class="form-control form-control-sm" id="instructions" placeholder="Add instructions"></textarea>
                </div>
                <div class="form-group col-6">
                <button class="btn btn-primary btn-sm col-12" onclick="SavePrescription()"><i class="oi oi-check"></i> Prescribe Drug</button>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>  


<div class="modal fade" id="IssueDrugPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content" style="width: 800px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Give Drugs to patient</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">  
        <div class="row">
          <div class="form-group col-4">
            <label>Precription ID</label>
            <input id="prescrion_id" class="form-control form-control-sm" readonly>
          </div>
          <div class="form-group col-8">
            <label>Drug Name</label>
            <input id="drug_name" class="form-control form-control-sm" readonly>
          </div>
          <div class="form-group col-4">
            <label>Given Quantity</label>
            <input id="given_qty" class="form-control form-control-sm" placeholder="0">
          </div>
          <div class="form-group col-12">
            <button class="btn btn-success btn-sm" onclick="SaveDrugIssue()"><i class="oi oi-check"></i> Save</button>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>
</div> 


<div class="modal fade" id="DrugHistoryPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content" style="width: 800px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">History of dispense</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">  
        <div class="form-row">
          <table class="table  table-sm table-striped table-bordered">
            <thead class="bg-success text-light">
              <th>#</th>
              <th>Date</th>
              <th>Quantity</th>
              <th>Dispensed By</th>
            </thead>
            <tbody id="drug_history">
              <!-- FROM CRUD -->
            </tbody>
          </table>
        </div>
      </div>
      </div>
    </div>
  </div>
</div> 


<div class="modal fade" id="DispositionPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content" style="width: 800px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-success">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Patient Discharge Care/Plan</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body"> 
        <div class="form-row">
          <div class="form-group col-4">
            <label>Care/Plan</label><br>
            <select class="form-control form-control-sm" id="dis_type">
              <option value="">Select</option>
              <option value="Referred to another facility">Referred to another facility</option>
              <option value="Discharged">Discharged</option>
              <option value="Death">Death</option>
            </select>
          </div>
          <div class="form-group col-8">
            <label>Destination</label><br>
            <input type="text" class="form-control form-control-sm" id="dis_destination" placeholder="Destination...">
          </div>
          <div class="form-group col-6">
            <label>Discharge Reason</label><br>
            <textarea type="text" class="form-control form-control-sm" id="dis_reason" placeholder="Reasons..."></textarea>
          </div>  
          <div class="form-group col-6">
            <label>Discharge Instructions</label><br>
            <textarea type="text" class="form-control form-control-sm" id="dis_instruction" placeholder="Instructions..."></textarea>
          </div>           
          <div class="form-group">
            <button class="btn btn-success btn-sm" onclick="SavePlan()"><i class="oi oi-check"></i> Save</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div> 


  <!-- Menu Toggle Script -->
<script type="text/javascript">
  $("#menu-toggle").click(function(e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
  });
  
  $('#close_image_preview').click(function(e) {
    e.preventDefault();
    $(".radiology_result_image_preview").toggleClass("show");
  });

  $('#tabs').tabs();

  var req = null;
  var hospital = "<?= $Hospital['hospital_name']?>";
  var refno = "<?= $refno?>";
  var fileno = "<?= $fileno?>";
  var name = "<?= $Patient['fullname']?>";
  var age = "<?= $age?>";
  var adm_date = "<?= $IPDFILE['adm_date']?>";

  $('.accordion').accordion();

  GetVitals();
  GetObservations();
  GetCardex();
  GetItems();
  GetProcedures();
  GetInvestigationRequests();
  GetInvestigationResults();
  GetDiagnosis();
  GetPrescriptions();
  GetSummary();

  setInterval(function(){GetInvestigationRequests();},10000);
  setInterval(function(){GetInvestigationResults();},10000);

  setInterval(function(){
      GetVitals();
      GetObservations();
      GetCardex();
      GetItems();
      GetProcedures();
      GetInvestigationRequests();
      GetInvestigationResults();
      GetDiagnosis();
      GetPrescriptions();
      GetSummary();
  },5000);

  //Gets
    function GetVitals(){
      RichUrl($('.vitals_list'),{GetVitals:'1',fileno:fileno});
    }

    function GetObservations(){
      RichUrl($('.observations_list'),{GetObservations:'1',fileno:fileno});
    }

    function GetCardex(){
      RichUrl($('.cardex_list'),{GetCardex:'1',fileno:fileno});
    }

    function GetItems(){
      RichUrl($('.item_list'),{GetItems:'1',fileno:fileno});
    }

    function GetProcedures(){
      RichUrl($('.procedures_list'),{GetProcedures:'1',fileno:fileno});
    }

    function GetInvestigationRequests(){
      RichUrl($('.investigation_request_list'),{GetInvestigationRequests:'1',fileno:fileno});
    }

    function GetInvestigationResults(){
      RichUrl($('.investigation_results_list'),{GetInvestigationResults:'1',fileno:fileno});
    }

    function PreviewImage(img_src){
      $('.radiology_result_image_preview').toggleClass('show');
      $('.radiology_result_image_preview>img').attr('src',img_src);
    }

    function GetDiagnosis(){
      RichUrl($('.diagnosis_list'),{GetDiagnosis:'1',fileno:fileno});
    }

    function GetPrescriptions(){
      RichUrl($('.prescription_list'),{GetPrescriptions:'1',fileno:fileno});
    }

    function GetAvailableItems(){
      var consumable_type = $('#consumable_type').val();
      var cons_search = $('#cons_search').val();
      if (req != null) {req.abort();} 
      req = RichUrl($('#available_items_list'),{GetAvailableItems:'1',fileno:fileno,consumable_type:consumable_type,cons_search:cons_search});
    }

    function GetProceduresServices(){
      var procedure_name = $('#search_procedures').val();
      if (req != null) {req.abort();} 
      req = RichUrl($('#procedure_service_list'),{GetProceduresServices:'1',fileno:fileno, procedure_name:procedure_name});           
    }

    function GetInvestigationRequests(){
      RichUrl($('.investigation_request_list'),{GetInvestigationRequests:'1',fileno:fileno});
    }

    function GetInvestigationResults(){
      RichUrl($('.investigation_results_list'),{GetInvestigationResults:'1',fileno:fileno});
    }
  
    function GetSummary(){
      $('#summary').load('SummaryForInclude.php?refno='+refno+'&fileno='+fileno);
    }

  //Saves
    function SaveVitals(){
      var fileno = "<?= $fileno?>";
      var temperature = $('#temperature').val();
      var bp_systolic = $('#bp_systolic').val();
      var bp_diastolic = $('#bp_diastolic').val();
      var weight = $('#weight').val();
      var pulse = $('#vitals_pulse').val();
      var respiration = $('#vitals_respiration').val();
      var remarks = $('#vitals_remarks').val();

      if (isNaN(temperature) || temperature=='' || +temperature<35 || +temperature>42) { SnackNotice(false,'Enter a valid body temperature between 35.0 and 42.0C');  $('#temperature').focus(); return; }

      if (isNaN(weight) || weight=='' || +weight<1) { SnackNotice(false,'Enter a valid body weight in Kg of the patient'); $('#weight').focus(); return;}
      if (isNaN(bp_systolic) || bp_systolic=='' || +bp_systolic<1) { SnackNotice(false,'Enter a valid systolic blood pressure'); $('#bp_systolic').focus(); return;}
      if (isNaN(bp_diastolic) || bp_diastolic=='' || +bp_diastolic<1) { SnackNotice(false,'Enter a valid diastolic blood pressure'); $('#bp_diastolic').focus(); return;}

      if (isNaN(pulse) || pulse=='' || +pulse<1) { SnackNotice(false,'Enter a valid pulse of the patient'); $('#vitals_pulse').focus(); return;}
      if (isNaN(respiration) || respiration=='' || +respiration<1) { SnackNotice(false,'Enter a valid respiration values'); $('#vitals_respiration').focus(); return;}
      if (remarks=='') { SnackNotice(false,'Enter your remarks on the vitals'); $('#vitals_remarks').focus(); return;}
      
      var data = { SaveVitals:'1',fileno:fileno,temperature:temperature,weight:weight,bp_systolic:bp_systolic,bp_diastolic:bp_diastolic,pulse:pulse,respiration:respiration,remarks:remarks };

      $('#processDialog').modal('toggle');
      $.ajax({
        method:'post',
        url:'crud.php',
        data:data,
        success:function(response){
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            GetVitals();
            SnackNotice(true,'Vitals saved succesfully');
            $('.form-control').val('');

            $('#VitalsPopUp').modal('toggle');
          }else{
            SnackNotice(false,response);
          }
        }
      });
    }

    function SaveObservations(){
      var complaint = $('#complaint').val();
      var observation = $('#physical_examination_note').val();
      var nursing_note = $('#nursing_note').val();
      if(complaint==''){SnackNotice(false,'Enter the patients complaint or remarks'); $('#complaint').focus(); return;}
      if(observation==''){SnackNotice(false,'Enter Examination notes');  $('#physical_examination_note').focus(); return;}
      if(nursing_note==''){SnackNotice(false,'Enter your remarks on the observation made');  $('#nursing_note').focus(); return;}

      var data = { SaveObservations:'1',refno:refno, fileno:fileno,complaint:complaint, observation:observation,nursing_note:nursing_note }
      RitchConfirm("Procced ?","Are you sure you want to save this doctor review. <br>Note: The patient will be charged <b>Doctor's Review Fee</b>").then(function(){
        $('#processDialog').modal('toggle');
        $.ajax({
          method:'POST',
          url:'CRUD.php',
          data:data,
          success:function(response){
            console.log(response);
            $('#processDialog').modal('toggle');
            if (response.includes('success')) {
              SnackNotice(true,'Doctor review saved succesfully');
              GetObservations();

              $('.form-control').val('');
              $('#ObservationPopUp').modal('hide')
            }else{
              SnackNotice(false,response);
            }
          }
        });
      });
    }

    function SaveCardex(){
      var remarks = $('#cardex_remarks').val();
      if(remarks==''){SnackNotice(false,'Enter nursing intervention or remarks of the cardex'); $('#cardex_remarks').focus(); return;}

      var data = { SaveCardex:'1', fileno:fileno,remarks:remarks}

      $('#processDialog').modal('toggle');
      $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:data,
        success:function(response){
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            SnackNotice(true,'Nursing intervention saved succesfully');
            
            $('.form-control').val('');
            $('#CardexPopUp').modal('toggle')
          }else{
            SnackNotice(false,response);
          }
        }
      });
    }

    function SaveItem(){
      var item_name = $('#item_name').val();
      var item_cost = $('#item_cost').val();
      var item_quantity = $('#item_quantity').val();

      if(item_name==''){SnackNotice(false,'Select an item to add'); return;}
      if(item_quantity=='' || +item_quantity <1 || isNaN(item_quantity)){SnackNotice(false,'Enter the quantity given to this patient'); $('#item_quantity').focus();  return;}

      var data = { SaveItem:'1',refno:refno, fileno:fileno,item_name:item_name, item_cost:item_cost,item_quantity:item_quantity }

      $('#processDialog').modal('toggle');
      $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:data,
        success:function(response){
          console.log(response);
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            SnackNotice(true,"Item added to the patient's charge list succesfully");
            GetItems();

              $('.form-control').val('');

            $('#ItemsPopUp').modal('toggle')
          }else{
            SnackNotice(false,response);
          }
        }
      });
    }

    function SaveProcedure() {
      var procedure_name = $('#procedure_name').val();
      var procedure_cost = $('#procedure_cost').val();
      var scheduled_date = $('#scheduled_date').val();
      var surgeon_name = $('#surgeon_name').val();

      if(procedure_name==''){SnackNotice(false,'Select a procedure to create a new one.'); return;}
      if(scheduled_date==''){SnackNotice(false,'Enter the date the operation is to be performed.'); $('#scheduled_date').focus(); return;}
      if(surgeon_name==''){SnackNotice(false,'Enter the name of the doctor or surgeon incharge of this operation.');$('#surgeon_name').focus(); return;}

      var data = { SaveProcedure:'1', fileno:fileno,procedure_name:procedure_name, procedure_cost:procedure_cost, scheduled_date:scheduled_date,surgeon_name:surgeon_name }
      RitchConfirm("proceed ?","Do you want to continue and save this Medical Procedure ?<br> Note: The patient will be automatically charged <b> Ksh. "+procedure_cost+"</b> by giving consent").then(function(){
        $('#processDialog').modal('toggle');
        $.ajax({
          method:'POST',
          url:'CRUD.php',
          data:data,
          success:function(response){
            console.log(response);
            $('#processDialog').modal('toggle');
            if (response.includes('success')) {
              SnackNotice(true,"Theatre procedure added created succesfully");
              GetProcedures();
              $('.form-control').val('');
              $('#TheatrePopUp').modal('hide')
            }else{
              SnackNotice(false,response);
            }
          }
        });
      });
    }

    var selected_procedure_id = null;
    
    function SwitchConsent(procedure_id,procedure_status){
      selected_procedure_id = procedure_id;
      switch (procedure_status){
        case 'Patient Consent':
          $('#PatientConsentPopUp').modal('toggle');
          break;
        case 'Nurse Checklist':
          $('#NurseChecklistPopUp').modal('toggle');
          break;
        case 'Doctor Checklist':
          $('#DoctorChecklistPopUp').modal('toggle');
          break;
        case 'Anaesthetist Checklist':
          $('#AnaesthetistChecklistPopUp').modal('toggle');
          break;
        case 'Operation Diagnosis':
          $('#OperationDiagnosisPopUp').modal('toggle');
          break;
        case 'Patient Not Fit, Start Over':
          SnackNotice(false,"The patient is not fit for this operation. Create New Checklist");
          break;
      }
    }

    var usbale_sign = false;
    function UseSign(){
      if (usbale_sign) {
        var canvas = document.getElementById('sketchpad');
        var img_url = canvas.toDataURL();
        $('#patient_sign').prop('src',img_url);
      }else{
        SnackNotice(false,'Patient signature is requires, please sign in the space provided');
        return;
      }
    }

    function SavePatientConsent(){
      var witness = $('#witness').val();
      var witness_name = $('#witness_name').val();
      var img_url = document.getElementById('sketchpad').toDataURL();
      if (!($('#patient_agree').is(':checked'))) {SnackNotice(false,'Patient consent has not been given. Please check the checkbox above to consent'); $('#patient_agree').focus();return; }
      if (witness=='') {SnackNotice(false,'Select the relationship between patient and witness'); $('#witness').focus();return; }
      if (witness_name=='') {SnackNotice(false,'Enter the name of the witness'); $('#witness_name').focus();return; }
      if ($('#patient_sign').attr('src')=='') {SnackNotice(false,'Patient signature is required in the consent form'); $('#patient_agree').focus();return; }

      $('#processDialog').modal('toggle');
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{SavePatientConsent:'1',fileno:fileno,refno:refno, procedure_id:selected_procedure_id,image:img_url,witness:witness,witness_name:witness_name},
        success:function(response){
          console.log(response);
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            SnackNotice(true,"Patient consent saved succesfully");
            GetProcedures();

            $('input').val('');
            $('select').val('');
            $('textarea').val('');

            $('#PatientConsentPopUp').modal('toggle')
          }else{
            SnackNotice(false,response);
          }
        }
      });
    }

    function SaveNurseChecklist(){
      var has_belongings = $('#has_belongings').val().length>0?'Yes':'No';
      var belongings = $('#belongings').val();
      var sugar = $('#sugar').val();
      var albumin = $('#albumin').val();
      var bladder_n_urinary = $('#bladder_n_urinary').val();
      var blood_in_litres = $('#blood_in_litres').val();
      var nurse_note = $('#nurse_note').val();
      if (has_belongings=='Yes' && belongings=='') {SnackNotice(false,'Enter the specification of patient belongings'); $('#belongings').focus(); return; }
      if (sugar=='') {SnackNotice(false,'Enter the sugar level present'); $('#sugar').focus(); return; }
      if (albumin=='') {SnackNotice(false,'Enter the albumin present'); $('#albumin').focus(); return; }
      if (bladder_n_urinary=='') {SnackNotice(false,'Enter the bladder check and urinary information'); $('#bladder_n_urinary').focus(); return; }
      if (blood_in_litres=='') {SnackNotice(false,'Enter the amount of blood in Litres'); $('#blood_in_litres').focus(); return; }

      $('#processDialog').modal('toggle');
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{ SaveNurseChecklist:'1',
        procedure_id:selected_procedure_id,
        has_belongings:has_belongings,
        belongings:belongings,
        sugar:sugar,
        albumin:albumin,
        bladder_n_urinary:bladder_n_urinary,
        blood_in_litres:blood_in_litres,
        nurse_note:nurse_note
      },
        success:function(response){
          console.log(response);
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            SnackNotice(true,"Nurse Checklist saved succesfully");
            GetProcedures();

            $('input').val('');
            $('select').val('');
            $('textarea').val('');

            $('#NurseChecklistPopUp').modal('toggle')
          }else{
            SnackNotice(false,response);
          }
        }
      });
    }

    function SaveDoctorChecklist(){
      var hydration = $('#hydration').val().length>0?'Normal':'Not Normal';
      var electrolyte = $('#electrolyte').val().length>0?'Normal':'Not Normal';
      var chest = $('#chest').val().length>0?'Normal':'Not Normal';      
      var hb = $('#hb').val();
      var pvc = $('#pvc').val();
      var temperature = $('#doctro_temp').val();
      var bp_sys = $('#bp_sys').val();
      var bp_dia = $('#bp_dia').val();
      var pulse = $('#pulse').val();
      var blood = $('#blood').val();
      var doctor_note = $('#doctor_note').val();

      if (hb=='') {SnackNotice(false,'Enter the Hb values'); $('#hb').focus(); return; }
      if (pvc=='') {SnackNotice(false,'Enter the PVC level present'); $('#pvc').focus(); return; }
      if (isNaN(temperature) || temperature=='' || +temperature<35 || +temperature>42) {SnackNotice(false,'Enter the temperature reading'); $('#doctro_temp').focus(); return; }
      if (isNaN(bp_sys) || bp_sys=='' || +bp_sys<80) {SnackNotice(false,'Enter valid BP Systolic value'); $('#bp_sys').focus(); return; }
      if (isNaN(bp_dia) || bp_dia=='' || +bp_dia<60) {SnackNotice(false,'Enter valid BP Diastolic value'); $('#bp_dia').focus(); return; }
      if (isNaN(pulse) || pulse=='' || +pulse<60) {SnackNotice(false,'Enter valid pulse value'); $('#pulse').focus(); return; }
      if (blood=='') {SnackNotice(false,'Enter the blood available in litres'); $('#blood').focus(); return; }

      $('#processDialog').modal('toggle');
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{ SaveDoctorChecklist:'1',
        procedure_id:selected_procedure_id,
        hydration:hydration,
        electrolyte:electrolyte,
        chest:chest,
        hb:hb,
        pvc:pvc,
        temperature:temperature,
        bp_sys:bp_sys,
        bp_dia:bp_dia,
        pulse:pulse,
        blood:blood,
        doctor_note:doctor_note
      },
        success:function(response){
          console.log(response);
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            SnackNotice(true,"Doctor Checklist saved succesfully");
            GetProcedures();
            
            $('input').val('');
            $('select').val('');
            $('textarea').val('');

            $('#DoctorChecklistPopUp').modal('toggle')
          }else{
            SnackNotice(false,response);
          }
        }
      });
    }

    function SaveAnaesthetistChecklist(){    
      var hb = $('#ana_hb').val();
      var pvc = $('#ana_pvc').val();
      var temperature = $('#ana_temp').val();
      var bp_sys = $('#ana_bp_sys').val();
      var bp_dia = $('#ana_bp_dia').val();
      var pulse = $('#ana_pulse').val();
      var albumin = $('#ana_albumin').val();
      var sugar = $('#ana_sugar').val();
      var note = $('#ana_note').val();
      var patient_fit = $('#patient_fit').val().length>0?'Yes':'No';  

      if (hb=='') {SnackNotice(false,'Enter the Hb values'); $('#ana_hb').focus(); return; }
      if (pvc=='') {SnackNotice(false,'Enter the PVC level present'); $('#ana_pvc').focus(); return; }
      if (isNaN(temperature) || temperature=='' || +temperature<35 || +temperature>42) {SnackNotice(false,'Enter valid temperature reading'); $('#ana_temp').focus(); return; }
      if (isNaN(bp_sys) || bp_sys=='' || +bp_sys<80) {SnackNotice(false,'Enter valid BP Systolic value'); $('#ana_bp_sys').focus(); return; }
      if (isNaN(bp_dia) || bp_dia=='' || +bp_dia<60) {SnackNotice(false,'Enter valid BP Diastolic value'); $('#ana_bp_dia').focus(); return; }
      if (isNaN(pulse) || pulse=='' || +pulse<60) {SnackNotice(false,'Enter valid pulse value'); $('#ana_pulse').focus(); return; }
      if (albumin=='') {SnackNotice(false,'Enter the pulse value'); $('#ana_albumin').focus(); return; }
      if (sugar=='') {SnackNotice(false,'Enter the BP Diastolic value'); $('#ana_sugar').focus(); return; }
      if (note=='') {SnackNotice(false,'Enter the blood available in litres'); $('#ana_note').focus(); return; }

      $('#processDialog').modal('toggle');
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{ SaveAnaesthetistChecklist:'1',
        fileno:fileno,
        procedure_id:selected_procedure_id,
        hb:hb,
        pvc:pvc,
        temperature:temperature,
        bp_sys:bp_sys,
        bp_dia:bp_dia,
        pulse:pulse,
        albumin:albumin,
        sugar:sugar,
        note:note,
        patient_fit:patient_fit
      },
        success:function(response){
          console.log(response);
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            SnackNotice(true,"Anaesthetist Checklist saved succesfully");
            GetProcedures();
            
            $('input').val('');
            $('select').val('');
            $('textarea').val('');

            $('#AnaesthetistChecklistPopUp').modal('toggle')
          }else{
            SnackNotice(false,response);
          }
        }
      });
    }


  /*LABORATORY*/
  function GetInvestigationServices(service_point,service_name){
    RichUrl($('#investigation_service_list'),{GetInvestigationServices:'1',fileno:fileno,service_point:service_point,service_name:service_name});
  }


  function Mark(elem){
    totalAmount = 0;
    var row = $(elem);
    if (row.find('input[type=checkbox]').is(':checked')) {
      row.find('input[type=checkbox]').attr('checked',false);
    }else{
      row.find('input[type=checkbox]').attr('checked',true);
    }

    $('#investigation_service_list tr').each(function(){      
      var row = $(this);
      if (row.find('input[type=checkbox]').is(':checked')) {
        var amount = row.find('td:nth-child(5)').text();
        totalAmount += +amount;
      }
        $('#total').text((totalAmount).toFixed(2));
    });
  }

  function SaveRequest(){
    var req_department = $('#service_point').val();
    var total_requests = 0;
      var requests = new Array();
      //get request ids
      $('#investigation_service_list tr').each(function(){
          var row = $(this);
          if (row.find('input[type=checkbox]').is(':checked')) {
            total_requests ++;
            req_code = row.find('td:nth-child(2)').text();
            requests.push([req_code]);
          }
        });
      if (total_requests==0) {SnackNotice(false,'You have not selected any Service Request');return;} 
 
    RitchConfirm("Proceed ?","Do you want to request for Selected nvestigations.<br>The patient will be automatically billed <b> Ksh. "+ $('#total').text()+"</b>").then(function(){
      $('#processDialog').modal('toggle');
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{SendInvestigationRequest:'1',fileno:fileno, refno:refno,requests:JSON.stringify(requests),req_department:req_department},
        success:function(response){
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            SnackNotice(true,'Investigation request sent succesfully');
            GetInvestigationRequests();            
          }else{
             SnackNotice(false,response);
          }
          $('#InvestigationPopUp').modal('hide');
        }
      });
    });
  }
  /*DIGNOSIS*/
  $(window).ready(function(){
    $('#op_progressline').hide();
    $('.progressline').hide();
  });

  function SearchDisease(){
    var d_name = $('#d_name').val();
    if (d_name=='') {
        $('#d_code').val('');
        $('#code_list').html('');
        $('#code_list').hide();
        return;
    }
    if (d_name.length < 3) {
        return;
    }        
     
    if (req != null) {req.abort();} 

    $('.progressline').show(); 
      req = $.ajax({
        method: 'POST',
        url: 'CRUD.php',
        data: { SearchDiseaqseCode:'1', disease:d_name},
        success: function(response){
           if (response != '') {                    
                $('#code_list').show();
                $('#code_list').html(response);                    
            }
            $('.progressline').hide();
        }
    });            
  }

  function SaveDiagnosis(){
    var d_name = $('#d_name').val();
    var d_code = $('#d_code').val();
    var d_comment = $('#d_comment').val();
    if (d_name.length === 0) {SnackNotice(false,'Enter the name of the disease for codification'); return;}
    if (d_code.length === 0) {SnackNotice(false,'The disease ICD10 Code is invalid');return;}
    $('#processDialog').modal('toggle');
    $.ajax({
        method: 'POST',
        url: 'CRUD.php',
        data: {SaveDiagnosis:'1',fileno:fileno,d_name:d_name,d_code:d_code,d_comment:d_comment},
        success: function(response){
            $('#processDialog').modal('toggle');
            if (response.includes('success')) {
                SnackNotice(true,'Disease Codification Saved succesfully');
                GetDiagnosis();
                
            }else{
                SnackNotice(false,response);
            }

          $('input').val('');
          $('select').val('');
          $('textarea').val(''); 

          $('#DiagnosisPopUp').modal('toggle'); 
        }
    });
  }


  /*PRESCRIPTION*/
  function GetDrugList(){
    var drugname = $('#drug_search').val();if (req != null) {req.abort();} 
    req = RichUrl($('#drug_list'),{GetDrugList:'1',fileno:fileno,drugname:drugname});
  }

  function GetDrugProperties(item_code,row){
    $('#drug_rate').val(row.find('td:nth-child(4)').text());
    $('#drug_payment').val(row.find('td:nth-child(5)').text());
    $('#DrugListPopUp').modal('hide');
    $('#PrescriptionPopUp').modal('show');
    $.ajax({
      method:'POST',
      url:'../Admin-Procurement/crud.php',
      data:{GetItemProps:'1',item_code:item_code},
      success:function(response){
        response = JSON.parse(response);
        $('#drugname').val(response.item_name);
        $('#drug_total_pieces').val(response.total_pieces);
      }
    });
  }

  function SavePrescription(){
    var drugname = $('#drugname').val();
    var q_instore = +$('#drug_total_pieces').val();
    var cost = +$('#drug_rate').val();
    var dosage = +$('#dosage').val();
    var freq = +$('#freq').val();
    var days = +$('#days').val();
    var instructions = $('#instructions').val();
    var drug_payment = $('#drug_payment').val();

    if (drugname.length==0) {SnackNotice(false,'Select/Enter the drug name'); $('#drugname').focus(); return;}
    if (isNaN(q_instore) || q_instore <= 0) {SnackNotice(false,'Selected drug is currently not available in store');return;}
    if (isNaN(+dosage) || dosage <= 0) {SnackNotice(false,'Dosage must a number greater than Zero'); $('#dosage').focus(); return;}
    if (isNaN(+freq) || freq <= 0) {SnackNotice(false,'The Frequency/Number of times this drug is taken per day must be a number greater than Zero'); $('#freq').focus(); return;}
    if (isNaN(+days) || days<= 0) {SnackNotice(false,'The number of days for this prescription must be a number greater than Zero'); $('#days').focus(); return;}

    var drugPrice = (cost * dosage * freq * days).toFixed(2);
    var drug_quantity = Math.round(dosage * freq * days);
    dosage = dosage +'x'+ freq;
    RitchConfirm("Proceed ?","Do you want to proceed and prescribe this drug to the patient <br> The patient will be automatically billed <b> Ksh."+drugPrice+"</b> ?").then(function(){
      $('#processDialog').modal('toggle');
      $.ajax({
          method:'post',
          url:'crud.php',
          data:{SavePrescription:'1',fileno:fileno,refno:refno,drugname:drugname,dosage:dosage,drugPrice:drugPrice,drug_quantity:drug_quantity, instructions:instructions,drug_payment:drug_payment},
          success:function(response){
            console.log(response);
            $('#processDialog').modal('toggle');
             if (response.includes('success')) {
              $('.form-control').val('');
              $('#PrescriptionPopUp').modal('hide');
              SnackNotice(true,'Prescription saved successfully');
            }else{
              SnackNotice(false,response);          
            }
        }
      });
    });
  }

    function IssueDrug(prescription_id,drugname){
      $('#prescrion_id').val(prescription_id);
      $('#drug_name').val(drugname);
      $('#IssueDrugPopUp').modal('show');
    }
    function SaveDrugIssue(){
      var prescription_id = $('#prescrion_id').val();
      var given_qty = $('#given_qty').val();
      if (isNaN(given_qty) || given_qty<1) {SnackNotice(false,'Enter a valid drug quantity Issued'); $('#given_qty').focus(); return;}
      $('#processDialog').modal('toggle');
        $.ajax({
          method:'post',
          url:'crud.php',
          data:{SaveDrugIssue:'1',fileno:fileno,refno:refno,prescription_id:prescription_id,given_qty:given_qty},
          success:function(response){
            $('#processDialog').modal('toggle');
            if (response.includes('success')) {
              $('.form-control').val('');
              SnackNotice(true,'Drug issued successfully');
              $('#IssueDrugPopUp').modal('hide');
            }else{
             SnackNotice(false,response);
            }
          }
        });
    }

    function DrugHistory(prescription_id){
      RichUrl($('#drug_history'),{DrugHistory:'1',prescription_id:prescription_id});
      $('#DrugHistoryPopUp').modal('show');
    }


  /*Care/Plan*/
    function SavePlan(){
      var dis_type = $('#dis_type').val();
      var dis_destination = $('#dis_destination').val();
      var dis_reason  = $('#dis_reason').val();
      var dis_instruction  = $('#dis_instruction').val();

      if (dis_type=='') {SnackNotice(false,'Select the care/plan for the patient'); $('#dis_type').focus(); return;}
      if (dis_destination=='') {SnackNotice(false,'Enter the name of the facility or Destination of the patient');$('#dis_destination').focus();return;}
      if (dis_reason=='') {SnackNotice(false,'Enter Discharge Reason');$('#dis_reason').focus();return;}
      if (dis_instruction=='') {SnackNotice(false,'Enter Discharge instructions');$('#dis_instruction').focus();return;}

        $('#processDialog').modal('toggle');
      $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{ 
          SavePlan:'1', 
          fileno:fileno,
          refno:refno,
          dis_type:dis_type,
          dis_destination:dis_destination,
          dis_reason :dis_reason,
          dis_instruction :dis_instruction
        },
        success:function(response){
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            SnackNotice(true,'Patient Discharged successfully');
            location.href = location.href;
          }else{
           SnackNotice(false,response);
          }
          $('#DispositionPopUp').modal('toggle');
        }
      });
    }

    var image_list = null;
    var current_image = 0;
    function GetImages(req_id){
      image_list = new Array();

      $.ajax({
        method:'POST',
        url:'crud.php',
        data:{GetImages:'1',req_id:req_id},
        success:function(response){
          image_list = JSON.parse(response);
          if (image_list.length>0) {
            $('.radiology_result_image_preview').toggleClass('show');
            $('.image_preview').attr('src',image_list[0]);
          }
        }
      });
    }

    function NextImage(){
      if ((current_image+1) === image_list.length) {
        current_image = 0;
      }else{
        current_image++;
      }
      $('.image_preview').attr('src',image_list[current_image]);
    }
    function PrevImage(){
      if (current_image === 0) {
        current_image = (image_list.length-1);
      }else{
        current_image--;
      }
      $('.image_preview').attr('src',image_list[current_image]);
    }

    function PrintSummary(){
      var page_content = $('body').html();
      var print_paper_content = $('#print_paper');

      $('body').html(print_paper_content);

      print();

      $('body').html(page_content);
      location.href = location.href;
    }

</script>

<script src="sign.js"></script>
<script src="monitors.js"></script>
</body>
</html>