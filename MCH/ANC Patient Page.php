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
if (!($User_level=='admin' || $GroupPrivileges['maternity_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
}
//Process page
  if (isset($_GET['serveRef'])) {
    include('../ConnectionClass.php');
    $refno = $_GET['serveRef'];
    
    $Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$refno'"); 
    $age = $db->getPatientAge($Patient['dob']);

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
    .requestButtons{
      position: fixed; bottom: 20px; left: 45%; text-decoration: none;
    }

    #tabs { 
      background: transparent; 
    }
    #tabs .scroller{
      height: 60px;
      overflow: auto;
    }
    #tabs ul{
      width: 800px; 
      border: 0;
    } 
    #tabs .ui-widget-header { 
        background: transparent; 
        border: none; 
        border-bottom: 1px solid #c0c0c0; 
        -moz-border-radius: 0px; 
        -webkit-border-radius: 0px; 
        border-radius: 0px; 
    } 
    #tabs .ui-tabs-nav .ui-state-default { 
        background: transparent; 
        border: none; 
    } 
    #tabs .ui-tabs-nav .ui-state-active { 
        background: transparent url(../images/uiTabsArrow.png) no-repeat bottom center; 
        border: none; 
    } 
    #tabs .ui-tabs-nav .ui-state-default a { 
        color: #888888; 
    } 
    #tabs .ui-tabs-nav .ui-state-active a { 
        color: #12ff00; 
    }
  </style>
  <style type="text/css">
    /*Reg box starts here */
  .RegBox {
      
  }
  </style>
  <style type="text/css">
    .SlideMore {
      height: 600px; width:0px;  position: fixed; top: 30px;  right: 0;
      transition: 2s; overflow: hidden;

      border-radius: 5px;
      -webkit-transition: width 1s ease;
      -moz-transition: width 1s ease;
      -o-transition: width 1s ease;
      transition: width 1s ease;

  }
  .SlideMore.in { 
    width: 500px;  
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
  				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Maternity Clinic</a>
  		  </div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11"  style="background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
      		<b><i class="oi oi-heart text-danger"></i> Patient Health Page</b>
      	</div> 
        <div style="position: fixed; top: 30px; right: 10px; transform: rotate(270deg); z-index: 1;transform-origin: bottom right 0; ">    
          <button data-target="#Codifications"  data-toggle="toggle" class="btn btn-success opener"><i class="oi oi-heart"></i> Codification</button>
          <button data-target="#Prescriptions" data-toggle="toggle" class="btn btn-danger opener"><i class="oi oi-medical-cross"></i> Prescription</button> 
          <button data-target="#Results" data-toggle="toggle" class="btn btn-primary opener"><i class="oi oi-clipboard"></i> Results</button>
        </div>
        <div class="col-11" style="border-radius: 3px; border:1px solid #CCC; margin: auto; margin-top: 10px;">
          <table class="col-sm-12 col-md-9">
            <tr>
              <td>Name: <b><?= $Patient['fullname']?></b></td>
              <td>Age: <b><?= $age?></b></td>
              <td>Sex: <b><?= $Patient['sex']?></b></td>
            </tr>
            <tr>              
              <td>Insured: <b><?= $Patient['ins_status']?></b></td>
              <td>Marital Status: <b><?= $Patient['marital_status']?></b></td>
            </tr>
          </table>
        </div>
<!--OPD process-->
          <div class="col-11" style="margin: auto; margin-top: 10px; z-index: 0;">
          <div id="tabs">
            <div class="scroller">
              <ul>
                <li><a href="#tab1"><i class="oi oi-pulse"></i> Vitals</a></li>              
                <li><a href="#tab2"><i class="oi oi-pencil"></i> Pulpate</a></li>
                <li><a href="#tab3"><i class="oi oi-calendar"></i> Appointments</a></li>
                <li><a href="#tab6"><i class="oi oi-share"></i> Referals</a></li>       
              </ul>              
            </div>
    <!--Vitals-->
            <div id="tab1">
              <table class="table table-sm table-bordered table-striped table-responsive{sm}">
                <thead class="bg-dark text-light">
                  <th>CODE</th>
                  <th>Date</th>
                  <th>BP<small>(mm/Hg)</small></th>
                  <th>Temp. <small>(<sup>0</sup>C)</small></th>
                  <th>Hypertensive</th>
                  <th>Pulse <small>(bpm)</small></th>
                  <th>Weight <small>(Kg)</small></th>
                </thead>
                <tbody id="vitals_list">
                  <!--Add from CRUD-->   
                </tbody>
              </table>
            </div>
    <!--Health History-->
            <div id="tab2">              
              <p>
                <a class="text-success" href="Health Case.php?serveRef=<?= $refno?>"><i class="oi oi-pencil"></i> New Pulpate</a>
              </p>
              <table class="table table-sm table-bordered table-striped table-responsive{sm}">
                <thead class="bg-dark text-light">
                  <th>Pulpate Code</th>
                  <th>Date</th>
                  <th>Fandal Height</th>
                  <th>Movement of the Baby</th>
                  <th>Babies</th>
                  <th>Doctor</th>
                  <th>Actions</th>
                </thead>
                <tbody id="health_cases_list">
                  <!--Add from CRUD-->   
                </tbody>
              </table>
            </div>            
    <!--Investigation-->
            <div id="tab3">
              <table class="table table-sm table-bordered table-striped table-responsive{sm}">
                <thead class="bg-dark text-light">
                  <th>Code</th>
                  <th>Date</th>
                  <th>Service Name</th>
                  <th>Department</th>
                  <th>Status</th>
                </thead>
                <tbody id="service_list">
                  <!-- ADD FROM CRUD -->
                </tbody>
              </table>
            </div>
    <!--Referal-->
            <div id="tab6">
              <p>
                <a class="text-success" href="Referal.php?serveRef=<?= $refno?>"><i class="oi oi-pencil"></i> Refer Patient</a>
              </p>
              <table class="table table-sm table-bordered table-striped table-responsive{sm}">
                <thead class="bg-dark text-light">
                  <th>Code</th>
                  <th>Date</th>
                  <th>Refer to</th>
                  <th>Reason</th>
                  <th>Comment</th>
                  <th>Doctor</th>
                  <th>Action</th>
                </thead>
                <tbody id="referral_list">
                  <!--Add from CRUD-->   
                </tbody>
              </table>
            </div>        
          </div>
          </div>
        </div>
      </div>
    </div>

<!--Results Dialog-->
  <div id="Results" class="SlideMore">
    <button class="btn btn-primary closer" data-toggle="toggle"  data-target="#Results" style="position: absolute;top: 0; left: 0; transform: rotate(270deg); transform-origin: bottom; margin-top: 20px; margin-left: -0px;"><i class="oi oi-clipboard"></i> RESULTS</button> 
    <div class="container-fluid" style="border: 1px solid blue; background-color: white; margin-left: 60px; width: 400px; height: 100%; padding: 5px; overflow-y: auto;" id="investigation_list">
      <!-- FROM CRUD -->
    </div>
  </div>      
<!--Prescriptions Dialog-->
  <div id="Prescriptions" class="SlideMore">
    <button class="btn btn-danger closer" data-toggle="toggle"  data-target="#Prescriptions" style="position: absolute; top: 0; left: 0; transform: rotate(270deg); transform-origin: bottom; margin-top: 50px; margin-left: -25px;"><i class="oi oi-medical-cross"></i> PRESCRIPTIONS</button> 
    <div id="PrescriptionDiv" class="container-fluid" style="border: 1px solid red; background-color: white; margin-left: 60px; width: 400px; height: 100%; padding: 5px; overflow-y: auto;">
      <!-- FROM CRUD --> 
    </div>
  </div>  
<!--Codification Dialog-->
  <div id="Codifications" class="SlideMore">
    <button class="btn btn-success closer" data-toggle="toggle"  data-target="#Codifications" style="position: absolute; top: 0; left: 0; transform: rotate(270deg); transform-origin: bottom; margin-top: 50px; margin-left: -20px;"><i class="oi oi-heart"></i> Codification</button> 
    <div id="disease_list" class="container-fluid" style="border: 1px solid green; background-color: white; margin-left: 60px; width: 400px; height: 100%; overflow-y: auto;">
      <!-- ADD FROM CRUD -->
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
    $('#tabs').tabs();

    $(".opener").click(function() {
      $(".opener").hide();
      var selector = $(this).data("target");      
      $(selector).toggleClass('in');
    });

    $(".closer").click(function() {
      var selector = $(this).data("target");      
      $(selector).toggleClass('in');
      $(".opener").show();
    });
  </script>

<script type="text/javascript">
  var refno = "<?= $refno?>";
  GetVitals();
  GetHealthCases();
  GetServiceRequests();
  GetInvestigations();
  GetEye();
  GetDental();
  GetX_RAY();
  GetDisease();
  GetPrescription();
  GetReferrals();

  function GetVitals(){
    $.ajax({
      method:'POST',
      url:'CRUD.php',
      data:{GetVitals:'1',refno:refno},
      success:function(response){
        $('#vitals_list').append(response);
      }
    });
  }
  function GetHealthCases(){
    $.ajax({
      method:'POST',
      url:'CRUD.php',
      data:{GetHealthCases:'1',refno:refno},
      success:function(response){
        $('#health_cases_list').append(response);
      }
    });
  }
  function GetServiceRequests(){
    $.ajax({
      method:'POST',
      url:'CRUD.php',
      data:{GetServiceRequests:'1',refno:refno},
      success:function(response){
        $('#service_list').append(response);
      }
    });
  }
  function GetInvestigations(){
    $.ajax({
      method:'POST',
      url:'CRUD.php',
      data:{GetInvestigations:'1',refno:refno},
      success:function(response){
        $('#investigation_list').append(response);
      }
    });
  }
  function GetEye(){

  }
  function GetDental(){

  }
  function GetX_RAY(){

  }

  function GetDisease(){
    $.ajax({
      method:'POST',
      url:'CRUD.php',
      data:{GetDisease:'1',refno:refno},
      success:function(response){
        $('#disease_list').append(response);
      }
    });
  }
  function GetPrescription(){
    $.ajax({
      method:'POST',
      url:'CRUD.php',
      data:{GetPrescription:'1',refno:refno},
      success:function(response){
        $('#PrescriptionDiv').html(response);
      }
    });
  }
  function GetReferrals(){
    $.ajax({
      method:'POST',
      url:'CRUD.php',
      data:{GetReferrals:'1',refno:refno},
      success:function(response){
        $('#referral_list').append(response);
      }
    });
  }
</script>
</body>
</html>