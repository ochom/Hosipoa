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
//
$refno = '';
$body_name='';
$body_from='';
$country='';
$county='';
$subcounty_ward='';
$kin_name=''; 
$kin_id='';
$kin_phone='';
$kin_relationship=''; 
if (isset($_GET['refno'])) {
  $refno = $_GET['refno'];
  $Patient = mysqli_fetch_array(mysqli_query($conn,"SELECT * From tbl_patient where refno = '$refno' "), MYSQLI_ASSOC);
  $body_name= $Patient['fullname'];
  $body_from='In-Patient';
  $country=$Patient['country'];
  $county=$Patient['county'];
  $subcounty_ward=$Patient['sub_county']." - ".$Patient['ward'];
  $kin_name=$Patient['kin_name'];
  $kin_id=$Patient['kin_id'];
  $kin_phone=$Patient['kin_phone'];
  $kin_relationship=$Patient['kin_relationship'];
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);">  Morgue</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">         
          <b><i class="oi oi-person"></i> Morgue Admission Form</b>
        </div>
        <div class="col-11" style="height: auto; padding: 10px 20px; border-radius: 5px; border:1px solid #ccc; margin:auto; margin-top: 10px; background-color: #ddd;">
          <div class="row">
            <div class="form-group col-sm-12 col-md-6">
              <label class="text-primary">Body Name</label>
              <input id="body_name" class="form-control form-control-sm" value="<?= $body_name?>">
            </div>
            <div class="form-group col-sm-12 col-md-6">
              <label class="text-primary">Body From</label>
              <input id="body_from" class="form-control form-control-sm" value="<?= $body_from?>">
            </div>
          </div>
          <div class="row">
            <div class="form-group col-sm-12 col-md-4">
              <label class="text-primary">Home Country</label>
              <input id="country" class="form-control form-control-sm" value="<?= $country?>">
            </div>
            <div class="form-group col-sm-12 col-md-4">
              <label class="text-primary">County/State</label>
              <input id="county" class="form-control form-control-sm" value="<?= $country?>">
            </div>
            <div class="form-group col-sm-12 col-md-4">
              <label class="text-primary">Sub-County/Ward/Estate</label>
              <input id="subcounty_ward" class="form-control form-control-sm" value="<?= $subcounty_ward?>">
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="form-group col-sm-12 col-md-6">
              <label>Kin Name</label>
              <input id="kin_name" class="form-control form-control-sm" value="<?= $kin_name?>">
            </div>
            <div class="form-group col-sm-12 col-md-3">
              <label>Kin ID NO.</label>
              <input id="kin_idno" class="form-control form-control-sm" value="<?= $kin_id?>">
            </div>
            <div class="form-group col-sm-12 col-md-3">
              <label>Kin Phone</label>
              <input id="kin_phone" class="form-control form-control-sm" value="<?= $kin_phone?>">              
            </div>
          </div>
          <div class="row">
            <div class="form-group col-sm-12 col-md-6">
              <label>Kin Relationship.</label>
              <input id="kin_relationship" class="form-control form-control-sm" value="<?= $kin_relationship?>">
            </div>
          </div>
          <hr>
          <div class="row">
          <div class="form-group col-sm-12 col-md-6">
              <label class="text-danger">Morgue</label>
              <select class="form-control form-control-sm" id="morgue_id" onchange="GetMorgueCost($(this).val())">
                <option value="">Select</option>
                <?php
                 $result = mysqli_query($conn,"SELECT * FROM tbl_morgues ORDER BY morgue_name ASC");
                 while($Morgue = mysqli_fetch_assoc($result)){?>
                  <option value="<?= $Morgue['morgue_id']?>"><?= $Morgue['morgue_name']?></option>
                <?php } ?>
              </select>
          </div>
          <div class="form-group col-sm-12 col-md-3">
              <label class="text-danger">Admission Fee</label>
              <input id="admit_fee" class="form-control form-control-sm" readonly>
          </div>
          <div class="form-group col-sm-12 col-md-3">
              <label class="text-danger">Daily Fee</label>
              <input id="daily_charge" class="form-control form-control-sm" readonly>
          </div>
        </div>
          <hr> 
          <div class="row">            
            <div class="form-group col-sm-12 col-md-8">
                <label class="text-success">Admitted By</label>
                <input id="admitted_by" class="form-control form-control-sm" value="<?= $_SESSION['Fullname']?>" readonly>
            </div>
          </div>               
          <div class="row">
            <div class="form-group col-sm-12 col-md-4">
              <button class="btn btn-success col-12" onclick="AdmitBody()"><i class="oi oi-check"></i> Admit</button>
            </div>
            <div class="form-group col-sm-12 col-md-4">
              <a href="home.php" class="btn btn-danger col-12"><i class="oi oi-x"></i> Cancel</a>
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
    var req = null;
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });


    function AdmitBody(){
      var body_name = $('#body_name').val();
      var body_from = $('#body_from').val();
      var country = $('#country').val();
      var county = $('#county').val();
      var subcounty_ward = $('#subcounty_ward').val();
      var kin_name = $('#kin_name').val();
      var kin_idno = $('#kin_idno').val();
      var kin_phone = $('#kin_phone').val();
      var kin_relationship = $('#kin_relationship').val();
      var morgue_id = $('#morgue_id').val();
      var admit_fee = $('#admit_fee').val();
      var daily_charge = $('#daily_charge').val();
      var admitted_by = $('#admitted_by').val();

      if(body_name.length===0){SnackNotice(false,'Enter the name of the body'); return;}
      if(body_from.length===0){SnackNotice(false,'Enter the place where the body is from'); return;}
      if(country.length===0){SnackNotice(false,'Enter the home country of the body'); return;}
      if(county.length===0){SnackNotice(false,'Enter the home county/state of the body'); return;}
      if(kin_name.length===0){SnackNotice(false,'Enter the name of the Next of kin'); return;}
      if(kin_idno.length===0){SnackNotice(false,'Enter the ID Number of the next of Kin'); return;}
      if(kin_phone.length===0){SnackNotice(false,'Enter the phone number of the next of kin'); return;}
      if(kin_relationship.length===0){SnackNotice(false,'Enter the relation between the next of kin and the body'); return;}
      if(morgue_id.length===0){SnackNotice(false,'Select the morgue in which the body is to be admitted'); return;}

      if (admit_fee>0 && !confirm('Confirm payment of the body admission fee has been received ?')) {
        return;
      }
      $('#processDialog').modal('toggle');
      $.ajax({
            method:'post',
            url:'crud.php',
            data:{  AdmitBody:'1',
                    body_name:body_name,
                    body_from:body_from,
                    country:country,
                    county:county,
                    subcounty_ward:subcounty_ward,
                    kin_name:kin_name,
                    kin_idno:kin_idno,
                    kin_phone:kin_phone,
                    kin_relationship:kin_relationship,
                    morgue_id:morgue_id,
                    admit_fee:admit_fee,
                    daily_charge:daily_charge,
                    admitted_by:admitted_by
                  },
            success:function(response){
              $('#processDialog').modal('toggle');
              if (response.includes('success')) {
                SnackNotice(true,'Body admitted succesfully');
                var ipd = "<?= $refno?>";
                if (ipd=='') {
                  location.href='Home.php';
                }else{
                  location.href="../IPD/home.php";
                }
              }else{
                SnackNotice(false,response);
              }
            }
          });

    }

    function GetMorgueCost(morgue_id){
      if (req != null) { req.abort();}
      req = $.ajax({
            method:'post',
            url:'crud.php',
            data:{GetMorgueCost:'1',morgue_id:morgue_id},
            success:function(response){
              var costs = response.split(";");
              $('#admit_fee').val(costs[0]);
              $('#daily_charge').val(costs[1]);
            }
          });
    }
  </script>
</body>
</html>