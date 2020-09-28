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
if (!($User_level=='admin' || $GroupPrivileges['opd_treatment_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
}
//Process page$fileno = $db->Decrypt($_GET['fileno']);
    $fileno = $_GET['fileno'];
    $refno =  $db->ReadOne("SELECT * FROM tbl_opd_visits WHERE fileno = $fileno")['patient_id'];
    $Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$refno'"); 
    $age = $db->getPatientAge($Patient['dob']);

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
        background: #99f; 
        border: none; 
        border-bottom: 1px solid #c0c0c0; 
        -moz-border-radius: 0px; 
        -webkit-border-radius: 0px; 
        border-radius: 0px; 
    } 
    #tabs .ui-tabs-nav .ui-state-default { 
        background: #00f; 
        border: none; 
    } 
    #tabs .ui-tabs-nav .ui-state-active { 
        background: transparent url(../images/uiTabsArrow.png) no-repeat bottom center; 
        border: none; 
    } 
    #tabs .ui-tabs-nav .ui-state-default a { 
        color: #ff0; 
    } 
    #tabs .ui-tabs-nav .ui-state-active a { 
        color: #00f; 
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
  </style>
  <!-- CODIFICATION STYLE -->
  <style type="text/css">
        #SearchResult{
            position:absolute;top: 80px; border: none; border-radius: 3px;
            height: 400px; max-height: 400px; margin:auto; background-color: rgba(255,255,255,0.9); 
            box-shadow: 0px 2px 3px #ccc;   padding: 2px 2px 5px 2px; z-index: 100; 
            cursor: pointer; overflow-y: scroll; display: none;
        }
        #SearchResult i{
            margin-bottom: 5px; border-bottom: 5px solid grey;
        }
        #SearchResult i:hover{
            font-weight: bold;  border-bottom: 1px solid rgb(255,150,0);
        }
        #SearchResult i:hover  .code{
            background-color: rgba(255,150,0); border-radius: 4px;
        }
        #SearchResult i span{
            float: left; padding: 2px 5px; overflow: hidden;  height: auto;
        }
  </style>
  <style type="text/css">
    @media print{
      @page { size: auto; margin: 0px;}
      body  { margin: 0px; padding: 0px;}
      #print_paper{page-break-after: always;}
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
  <div style="position: absolute;bottom: 0;right: 5px; z-index: 999;">
    <button class="btn btn-sm btn-primary" onclick="initMonitors('bp')">BP Monitor</button>
    <button class="btn btn-sm btn-primary" onclick="initMonitors('temperature')">Temperature Monitor</button>
    <button class="btn btn-sm btn-primary" onclick="initMonitors('bmi')">BMI Monitor</button>
    <button class="btn btn-sm btn-primary" onclick="initMonitors('pulse')">Pulse Monitor</button>
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
          <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> OPD</a>
        </div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
          <div class="col-11" style="height: auto; border-radius: 3px; border:1px solid #CCC; margin: auto; margin-top: 5px;">
            <span style="width: 100%; padding: 5px; background-color: #eee; position: absolute; left: 0; text-align: center;">
              <b><?= $Patient['fullname']?></b> (<?= $refno?>) - <?= $Patient['sex'].", ".$age?>
            </span>
            <div style="margin-top: 35px;">
              <img style="position: absolute; top: 0; height: 50px; width: 50px; border-radius: 50%; border: 1px solid #ccc; background-color: #fff; overflow: hidden; margin: 5px; float: left;" src="<?= $image?>">
              <p align="center">Date of Birth <?= $Patient['dob']?> <br>
                <b><i class="oi oi-heart text-danger"></i> OPD Health File ( File No.: <?= $fileno ?>)</b>
              </p> 

            </div> 
          </div>
         <div class="page_scroller">
          <div id="tabs" style="max-height: calc(100vh - 200px); overflow: auto;">
            <div class="scroller">
              <ul>              
                <li><a href="#tab1">Vitals</a></li>
                <li><a href="#tab2">Observations</a></li>
                <li><a href="#tab3">Investigations</a></li>        
                <li><a href="#tab4">Diagnosis</a></li>  
                <li><a href="#tab5">Medication</a></li> 
                <li><a href="#tab6">Custom Charges</a></li>
                <li><a href="#tab7">Disposition</a></li>  
                <li><a href="#tab8">Summary (Rx)</a></li>   
              </ul>              
            </div>
            <div id="tab1">
              <div class="row">
                <table  class="table table-sm" class="table table-sm">
                  <tbody class="vital_signs">
                    <!-- From CRUD -->
                  </tbody>
                </table>
               </div> 
            </div> 
            <div id="tab2">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Clinician Queue.php" class="btn btn-sm btn-outline-primary" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Queue</a>
                <a data-toggle="modal" data-target="#HealthCasePopUp" class="btn btn-sm btn-outline-primary" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Add</a>
              </div>
              <table class="table table-sm table-striped table-bordered" style="margin-top: 20px;">
                <thead class="bg-dark text-light">
                  <th>#</th>
                  <th>Date/Time</th>
                  <th>Complaint</th>
                  <th>Period</th>
                  <th>Pre-med</th>
                  <th>Physical Examination</th>
                  <th>Doctor</th>
                </thead>
                <tbody class="health_cases_list">
                  <!-- from crud -->
                </tbody>
              </table>
            </div>
            <div id="tab3">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Clinician Queue.php" class="btn btn-sm btn-outline-primary" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Queue</a>
                <a data-toggle="modal" data-target="#InvestigationPopUp" class="btn btn-sm btn-outline-primary" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Add</a>
              </div>
              <div class="accordiong"  style="margin-top: 20px;">
                <h3 style="border-bottom: 1px solid lime;">Investigation Requests</h3>
                <table class="table table-sm table-striped table-bordered">
                  <thead class="bg-primary text-light">
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
                  <thead class="bg-primary text-light">
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
            <div id="tab4">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Clinician Queue.php" class="btn btn-sm btn-outline-primary" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Queue</a>
                <a data-toggle="modal" data-target="#DiagnosisPopUp" class="btn btn-sm btn-outline-primary" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Add</a>
              </div>
              <table class="table table-sm table-striped table-bordered" style="margin-top: 20px;">
                <thead class="bg-dark text-light">
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
            <div id="tab5">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Clinician Queue.php" class="btn btn-sm btn-outline-primary" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Queue</a>
                <a data-toggle="modal" data-target="#DrugListPopUp" class="btn btn-sm btn-outline-primary" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Add</a>
              </div>
              <table class="table table-sm table-striped table-bordered" style="margin-top: 20px;">
                <thead class="bg-dark text-light">
                  <th>Date/Time</th>
                  <th>Drug Name</th>
                  <th>Dosage</th>
                  <th>Instructions</th>
                  <th>Doctor</th>
                </thead>
                <tbody class="prescription_list">
                  <!-- ADD FROM CRUD -->
                </tbody>
              </table>
            </div>          
            <div id="tab6">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Clinician Queue.php" class="btn btn-sm btn-outline-primary" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Queue</a>
                <a data-toggle="modal" data-target="#ProcedurePopUp" class="btn btn-sm btn-outline-primary" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Add Minor Procedure</a>
                <a data-toggle="modal" data-target="#GeneralServiceAdnConsummablePopUp" class="btn btn-sm btn-outline-primary" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Add Service or Consumable</a>
              </div>
              <p style="margin-bottom: 5px; border-bottom: 1px solid lime;">Minor Operations </p>
              <table class="table table-sm table-striped table-bordered" style="margin-top: 10px;">
                <thead class="bg-dark text-light">
                  <th>#</th>
                  <th>Date/Time</th>
                  <th>Procedure</th>
                  <th>Procedure Notes</th>
                  <th>Doctor</th>
                </thead>
                <tbody class="procedures_list">
                  <!-- from crud -->
                </tbody>
              </table>
              <p style="margin-bottom: 5px; border-bottom: 1px solid lime;">Other services and consumables</p>
              <table class="table table-sm table-striped table-bordered" style="margin-top: 10px;">
                <thead class="bg-dark text-light">
                  <th>#</th>
                  <th>Date/Time</th>
                  <th>Item Name</th>
                  <th>Quantity</th>
                  <th>Doctor</th>
                </thead>
                <tbody class="consumables_list">
                  <!-- from crud -->
                </tbody>
              </table>
            </div> 
            <div id="tab7">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Clinician Queue.php" class="btn btn-sm btn-outline-primary" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Queue</a>
                <a data-toggle="modal" data-target="#DispositionPopUp" class="btn btn-sm btn-outline-primary" style="float: right;  margin-right: 5px;"><i class="oi oi-plus"></i> Add</a>
              </div>
              <table class="table table-sm table-striped table-bordered" style="margin-top: 20px;">
                <thead class="bg-dark text-light">
                  <th>Date/Time</th>
                  <th>Disposition Type</th>
                  <th>Destination</th>
                  <th>Reason</th>
                  <th>Dispositioned To</th>
                  <th>Doctor</th>
                </thead>
                <tbody class="dispostion_list">
                  <!-- ADD FROM CRUD -->
                </tbody>
              </table>
            </div> 
            <div id="tab8">
              <div style="position: absolute; right: 20px; top: 50px;">
                <a href="Clinician Queue.php" class="btn btn-sm btn-outline-primary" style="float: right;  margin-right: 5px;"><i class="oi oi-people"></i> Back to Queue</a>
                <button class="btn btn-sm btn-outline-danger" onclick="CloseHealthFile()"  style="float: right; margin-right: 5px;"><i class="oi oi-circle-x"></i> Close File</button>
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


<div class="modal fade" id="HealthCasePopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-primary">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Observations and Examination</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row">
        <div class="form-group col-5">
            <label>Complaint</label>
            <input class="form-control form-control-sm" id="complaint" placeholder="Complaint...">
        </div>
        <div class="form-group col-3">
            <label>Period</label>
            <input class="form-control form-control-sm" id="period" placeholder="0">
        </div>
        <div class="form-group col-4">
            <label>Units</label>
            <select class="form-control form-control-sm" id="period_units">
              <option value="">Select</option>
              <option value="Minute(s)">Minute(s)</option>
              <option value="Hour(s)">Hour(s)</option>
              <option value="Day(s)">Day(s)</option>
              <option value="Week(s)">Week(s)</option>
              <option value="Month(s)">Month(s)</option>
              <option value="Year(s)">Year(s)</option>
            </select>
        </div>
        <div class="form-group col-12">
            <label>Pre-medication Note</label><br>
            <textarea style="height: 30px;" class="form-control form-control-sm" id="pre_med_note" placeholder="Pre Meds..."></textarea>
        </div>
        <div class="form-group col-12">
            <label>Physical Examination Note</label><br>
            <textarea style="height: 30px;" class="form-control form-control-sm" id="physical_examination_note" placeholder="Any physical Examination..."></textarea>
        </div> 

        <div class="form-group col-2">
            <button class="btn btn-sm btn-success" onclick="SaveHealthCase()"><i class="oi oi-plus"></i> Add</button>
        </div>  
          </div>
          </div>
      </div>
    </div>
</div>  


<div class="modal fade" id="InvestigationPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 700px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-primary">
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
            <th>Payment</th>
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
      <div class="modal-header bg-primary">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Diagnosis of this Health Case</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group col-8">
            <label>Disease Name</label>
            <input class="form-control form-control-sm" id="d_name" onkeyup="SearchDisease($(this).val())" placeholder="Type to Search...">
          <div class="progressline" id="progressline" style="background-color: #eee; overflow: hidden;"><div class="box2" style="height: 2px; border-radius: 1px;"></div></div>
          </div>
          <div class="form-group col-4">
            <label>ICD10 Code</label>
            <input class="form-control form-control-sm " id="d_code" readonly>
          </div>
          <!-- Results list -->
          <div class="col-11" id="SearchResult">
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
      <div class="modal-header bg-primary">
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
                <th>Payment</th>
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
      <div class="modal-header bg-primary">
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

<div class="modal fade" id="GeneralServiceAdnConsummablePopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 800px; margin-left: calc((100% - 800px)/2);">
      <div class="modal-header bg-primary">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Custom Services and Consumable Items</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-sm-12 col-lg-7">
            <div class="form-row">
              <div class="form-group col-6">
                <label>Item Type</label>
                <select class="form-control form-control-sm" onchange="GetConsumableList()" id="consumable_type">
                  <option value="">Select</option>
                  <option value="Consumable">Consumable Item</option>
                  <option value="General Service">General Service</option>
                </select>
              </div>
              <div class="form-group col-6">
                <label>Search by Name</label>
                <input class="form-control form-control-sm col-12" id="cons_search" placeholder="Type to search items..." onkeyup="GetConsumableList()">
              </div>
            </div>
            <table class="table table-sm table-striped table-bordered">
              <thead class="bg-dark text-light">
                <th>Item</th>
                <th>Cost</th>
                <th>Payment</th>
              </thead>
              <tbody id="consume_list">
                <!-- FROM CRUD -->
              </tbody>
            </table>
          </div>
          <div class="col-sm-12 col-lg-5">
            <div class="form-row">
              <div class="form-group col-12">
                <label>Item Name</label>
                <input class="form-control form-control-sm" id="cons_name" readonly>
              </div>
              <div class="form-group col-6">
                <label>Cost</label>
                <input class="form-control form-control-sm" id="cons_rate" readonly>
              </div>
              <div class="form-group col-6">
                <label>Payment Type</label>
                <input class="form-control form-control-sm" id="cons_payment" readonly>
              </div>
              <div class="form-group col-6">
                <label>Quantity</label>
                <input class="form-control form-control-sm" id="cons_quantity" value="1">
              </div>
              <div class="form-group col-12">
                <label>Doctors Note</label>
                <textarea class="form-control form-control-sm" id="cons_note"></textarea>
              </div>
              <div class="form-group col-12">
                <button class="btn btn-sm btn-success" onclick="SaveConsumable()"><i class="oi oi-check"></i> Save</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div> 

<div class="modal fade" id="ProcedurePopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 800px; margin-left: calc((100% - 800px)/2);">
      <div class="modal-header bg-primary">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">OPD Medical Procedures</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-sm-12 col-lg-7">
            <input class="form-control form-control-sm" id="search_procedures" onkeyup="GetProceduresServices()"  placeholder="Type to search procedures...">
            <table class="table table-sm table-striped table-bordered">
              <thead class="bg-dark text-light">
                <th>Procedure</th>
                <th>Cost (Ksh.)</th>
                <th>Payment</th>
              </thead>
              <tbody id="procedure_service_list">
                <!-- FROM CRUD -->
              </tbody>
            </table>
          </div>
          <div class="col-sm-12 col-lg-5">
            <div class="form-row">
              <div class="form-group col-12">
                <label>Procedure Name</label>
                <input class="form-control form-control-sm" id="procedure_name" readonly>
              </div>
              <div class="form-group col-6">
                <label>Cost</label>
                <input class="form-control form-control-sm" id="procedure_cost" readonly>
              </div>
              <div class="form-group col-6">
                <label>Payment Type</label>
                <input class="form-control form-control-sm" id="procedure_payment" readonly>
              </div>
              <div class="form-group col-12">
                <label>Doctors Note</label>
                <textarea class="form-control form-control-sm" id="procedure_note"></textarea>
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
</div>  


<div class="modal fade" id="DispositionPopUp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog modal-dialog-lg" role="document">
    <div class="modal-content" style="width: 800px; margin-left: calc((100% - 700px)/2);">
      <div class="modal-header bg-primary">
        <b class="glyphicon glyphicon-plus"></b><b class="modal-title" id="exampleModalLabel" style="color: #FFF;">Patient Disposition</b>
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body"> 
        <div class="form-group">
          <label>Disposition Type</label><br>
          <select class="form-control form-control-sm" id="ref_type">
            <option value="">Select</option>
            <option value="Referred to another facility">Referred to another facility</option>
            <option value="Discharged">Discharged</option>
            <option value="In-patient Admission">In-patient Admission</option>
            <option value="Death">Death</option>
          </select>
        </div>
        <div class="form-group">
          <label>Destination</label><br>
          <input type="text" class="form-control" id="ref_to" placeholder="Facility name, Home, Ward or Morgue name">
        </div>
        <div class="form-group">
          <label>Note/Cause</label><br>
          <textarea type="text" class="form-control" id="ref_reason" placeholder="Reasons..."></textarea>
        </div>           
        <div class="form-group">
          <button class="btn btn-success btn-sm" onclick="SaveDisposition()"><i class="oi oi-check"></i> Save</button>
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
  var refno = "<?= $refno?>";
  var fileno = "<?= $fileno?>";

  $('.accordion').accordion();

  GetVitals();
  GetHealthCases();
  GetInvestigationRequests();
  GetInvestigationResults();
  GetDiagnosis();
  GetPrescription();
  GetProcedures();
  GetConsumables();
  GetDispositions();
  GetSummary();

  setInterval(function(){
  GetInvestigationRequests();
    GetInvestigationResults();
    GetSummary();
  },5000);

  //Vitals
  function GetVitals(){
    RichUrl($('.vital_signs'),{GetVitals:'1',fileno:fileno});
  }

  function GetHealthCases(){
    RichUrl($('.health_cases_list'),{GetHealthCases:'1',fileno:fileno});
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

  function GetPrescription(){
    RichUrl($('.prescription_list'),{GetPrescription:'1',fileno:fileno});
  }

  function GetProcedures(){
    RichUrl($('.procedures_list'),{GetProcedures:'1',fileno:fileno});
  }

  function GetConsumables(){
    RichUrl($('.consumables_list'),{GetConsumables:'1',fileno:fileno});
  }

  function GetDispositions(){
    RichUrl($('.dispostion_list'),{GetDispositions:'1',fileno:fileno});
  }

  function GetSummary(){
    RichUrl($('#summary'),{GetSummary:'1',refno:refno,fileno:fileno});
  }

  //Health case create and save
  function SaveHealthCase(){
    var fileno = "<?= $fileno?>";
    var complaint = $('#complaint').val();
    var period = $('#period').val();
    var period_units = $('#period_units').val();
    var pre_med_note = $('#pre_med_note').val();
    var physical_examination_note = $('#physical_examination_note').val();
    if(complaint.length===0){SnackNotice(false,'Enter the patients complaint'); $('#complaint').focus(); return;}
    if(period.length===0 || isNaN(period)){SnackNotice(false,'Enter the numeric period for which the patient has suffered from the complaint'); $('#period').focus(); return;}
    if(period_units.length===0){SnackNotice(false,'Select the units of the period stated');  $('#period_units').focus(); return;}

    period = period+" "+period_units;
    $('#processDialog').modal('toggle');
    $.ajax({
      method:'POST',
      url:'CRUD.php',
      data:{ SaveHealthCase:'1', fileno:fileno,
          complaint:complaint, period:period,
          pre_med_note:pre_med_note,physical_examination_note:physical_examination_note
        },
      success:function(response){
        console.log(response);
        $('#processDialog').modal('toggle');
        if (response.includes('success')) {
          SnackNotice(true,'Observation and Examination recorded');
          GetHealthCases();
          
          $('.form-control').val('');
          $('#HealthCasePopUp').modal('toggle');
          location.href = location.href;
        }else{
          SnackNotice(false,response);
        }
      }
    });
  }


  //HEALTH PROCEDURES
  function GetProceduresServices(){
    var procedure_name = $('#search_procedures').val();
    RichUrl($('#summary'),{SummaryForInclude:'1',refno:refno,fileno:fileno,procedure_name:procedure_name});
  }

  function SaveProcedure(){
    var procedure_name = $('#procedure_name').val();
    var procedure_cost = $('#procedure_cost').val();
    var procedure_note = $('#procedure_note').val();
    var payment_type = $('#procedure_payment').val();
    if (procedure_name.length===0) {SnackNotice(false,'Select a medical procedure'); return;}

    $.ajax({
      method: 'POST',
      url: 'CRUD.php',
      data: { SaveProcedure:'1',fileno:fileno,refno:refno, procedure_name:procedure_name,procedure_cost:procedure_cost,procedure_note:procedure_note,payment_type:payment_type},
      success: function(response){
        if (response.includes("success")) {
          SnackNotice(true,'Medical Procedure saved');
        }else{
          SnackNotice(false,response);
        }
        GetProcedures();
        $('.form-control').val('');
        $('#ProcedurePopUp').modal('toggle');           
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
            req_cost = row.find('td:nth-child(5)').text();
            req_payment = row.find('td:nth-child(6)').text();
            requests.push([req_code,req_cost,req_payment]);
          }
        });
      if (total_requests==0) {SnackNotice(false,'You have not selected any Service Request');return;} 
 
    RitchConfirm("Proceed ?","Send request list with a total cost of <b> Ksh. "+ $('#total').text()+"</b>").then(function(){
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

  ///CODIFICATION 
    $(window).ready(function(){
        $('.progressline').hide();
    });

    function SearchDisease(){
      var d_name = $('#d_name').val();
        if (d_name.length===0) {
            $('#d_code').val('');
            $('#SearchResult').html('');
            $('#SearchResult').hide();
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
                    $('#SearchResult').show();
                    $('#SearchResult').html(response);                    
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
    var drugname = $('#drug_search').val();
    RichUrl($('#drug_list'),{GetDrugList:'1',fileno:fileno,drugname:drugname});
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
    dosage = dosage +'x'+ freq + 'x'+days;
    RitchConfirm("Proceed ?","Do you want to proceed and prescribe drug costing <b> Ksh."+drugPrice+"</b> to this patient ?").then(function(){
      $('#processDialog').modal('toggle');
      $.ajax({
          method:'post',
          url:'crud.php',
          data:{SavePrescription:'1',fileno:fileno,refno:refno,drugname:drugname,dosage:dosage,drugPrice:drugPrice,instructions:instructions,drug_payment:drug_payment},
          success:function(response){
            $('#processDialog').modal('toggle');
             if (response.includes('success')) {
              $('.form-control').val('');
              $('#PrescriptionPopUp').modal('hide');
              SnackNotice(true,'Prescription saved successfully');
              GetPrescription();
            }else{
              SnackNotice(false,response);          
            }
        }
      });
    });
  }

//Consumables
  function GetConsumableList(){
    var consumable_type = $('#consumable_type').val();
    var cons_search = $('#cons_search').val();
    RichUrl($('#consume_list'),{GetConsumableList:'1',fileno:fileno,consumable_type:consumable_type,cons_search:cons_search});
  }

  function GetConsumeProperties(row){
    $('#cons_name').val(row.find('td:nth-child(1)').text());
    $('#cons_rate').val(row.find('td:nth-child(2)').text());
    $('#cons_payment').val(row.find('td:nth-child(3)').text());
  }

  function SaveConsumable(){
    cons_name = $('#cons_name').val();
    cons_rate = $('#cons_rate').val();
    cons_payment = $('#cons_payment').val();
    cons_quantity = $('#cons_quantity').val();
    cons_note = $('#cons_note').val();
    if (cons_name=='') {SnackNotice(false,'Select an Item to add');$('#cons_name').focus();return; }
    if (isNaN(cons_quantity) || cons_quantity<1) {SnackNotice(false,'Item Quantity must be a valid number greater than zero');$('#cons_quantity').focus();return; }
    var cons_cost = (+cons_rate * +cons_quantity).toFixed(2);

    $('#processDialog').modal('toggle');
    $.ajax({
        method:'post',
        url:'crud.php',
        data:{SaveConsumable:'1',refno:refno,fileno:fileno,cons_name:cons_name,cons_cost:cons_cost,cons_payment:cons_payment, cons_quantity:cons_quantity,cons_note:cons_note},
        success:function(response){
          $('#processDialog').modal('toggle');
           if (response.includes('success')) {
            $('.form-control').val('');
            $('#GeneralServiceAdnConsummablePopUp').modal('toggle');
            SnackNotice(true,'Item saved successfully');
            GetConsumables();
          }else{
            SnackNotice(false,response);          
          }
        }
    });
  }


  //DIPOSITION
  function SaveDisposition(){
    var ref_type = $('#ref_type').val();
    var ref_to = $('#ref_to').val();
    var ref_reason  = $('#ref_reason').val();

    if (ref_type.length==0) {SnackNotice(false,'Select the Disposition Type');return;}
    if (ref_to.length==0) {SnackNotice(false,'Enter the name of the facility or Destination of the patient');return;}
    if (ref_reason.length==0) {SnackNotice(false,'Enter the reason for tranfering this patient to stated destination');return;}

      $('#processDialog').modal('toggle');
    $.ajax({
      method:'POST',
      url:'CRUD.php',
      data:{ SaveDisposition:'1', fileno:fileno,ref_type:ref_type, ref_to:ref_to,ref_reason:ref_reason},
      success:function(response){
        $('#processDialog').modal('toggle');
        if (response.includes('success')) {
          GetDispositions();
          SnackNotice(true,'Patient Disposition Saved');
          $('#DispositionPopUp').modal('toggle');
          
        }else{
         SnackNotice(false,response);
        }
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
    location.href = location.href +"#tab8";
  }

  function CloseHealthFile(){
    RitchConfirm("Proceed ?","Are you sure you want to close this health file ?").then(function(){
      $('#processDialog').modal('toggle');
    $.ajax({
      method:'POST',
      url:'crud.php',
      data:{CloseHealthFile:'1',fileno:fileno},
      success:function(response){
        $('#processDialog').modal('toggle');
        if (response.includes('success')) {
          SnackNotice(true,'Health File closed successfully');
          location.href="Clinician Queue.php";
        }else{
          SnackNotice(false,response);
        }
      }
    });
    });
  }
</script>

<script src="monitors.js"></script>
</body>
</html>