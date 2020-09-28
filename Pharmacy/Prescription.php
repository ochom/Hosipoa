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
//Process page

  $refno = $_GET['serveRef'];
  $Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$refno'"); 
  $age = $db->getPatientAge($Patient['dob']);

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
          <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Pharmacy</a>
        </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-eyedropper"></i> Drug Prescription</b>
        </div> 
        <div class="page_scroller">
          <table class="col-sm-12">
            <tr>
              <td>Name: <b><?= $Patient['fullname'];?></b></td>
              <td>Age: <b><?= $age?></b></td>
              <td>Sex: <b><?= $Patient['sex'];?></b></td>
            </tr>
          </table>
        </div>
          <div class="page_scroller">
          <div class="row">
            <div class="col-sm-12 col-md-6">
                <div class="form-row">
                  <div class="form-group  col-md-10">                   
            <label>Drug Name</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="oi oi-book"></i></span>
              </div>
              <select class="form-control form-control-sm"  id="drugname" onchange="GetDrugProperties($(this).val())">
                <option value="">Select</option>
                <?php
                  $sql = "SELECT * FROM tbl_item WHERE item_type='Drug' ORDER BY item_name ASC";
                  $result = mysqli_query($conn,$sql);
                  while ($row = mysqli_fetch_assoc($result)) {
                ?>
                <option value="<?= $row['item_name']?>"><?= $row['item_name']?></option>
                <?php } ?>
              </select>
            </div>
                  </div>  
          <div class="form-group col-md-2">
            <label>Store</label>
            <input class="form-control form-control-sm" id="q_instore" readonly="true"/>
          </div>        
        </div>
        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Cost/unit</label>
            <input class="form-control form-control-sm" id="cost" readonly="true" />
          </div>
          <div class="form-group col-md-3">
            <label>Dosage</label>
            <input  onkeyup="if(+$(this).val()> 5){$(this).val(''); SnackNotice(false,'That input value is too high limit(5)');}" class="form-control form-control-sm" id="dosage"/>
          </div>
          <div class="form-group col-md-3">
            <label>Frequency</label>
            <input onkeyup="if(+$(this).val()> 12){$(this).val('');SnackNotice(false,'That input value is too high limit(12)');}" class="form-control form-control-sm" id="freq"/>
          </div>
          <div class="form-group col-md-3">
            <label>Days</label>
            <input onkeyup="if(+$(this).val()> 360){$(this).val('');SnackNotice(false,'That input value is too high limit(360)');}" class="form-control form-control-sm" id="days"/>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-12">
            <label>Other instructions</label>
            <textarea class="form-control" id="instructions" placeholder="Add instructions"></textarea>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-3">
            <button class="btn btn-success" onclick="addToTable()"><i class="oi oi-plus"></i> Add</button>
          </div>
        </div> 
            </div>
            <div class="col-sm-12 col-md-6" style="border-radius: 5px; border:1px solid #ccc; margin-top: 5px; padding: 5px;">                        
              <div class="row">
                <div class="form-group col-6">
                  <button class="btn btn-success btn-sm col-12" onclick="SavePrescription()"><i class="oi oi-check"></i> Prescribe</button>
                </div>
                <div class="form-group col-6">
                  <a href="Prescription Queue.php" class="btn btn-danger btn-sm col-12"><i class="oi oi-x"></i> Cancel</a>
                </div>
              </div>
              <table class="table table-striped table-sm"  style="margin: 5px;">
                <thead class="bg-primary text-light">
                  <th>Drug Name</th>
                  <th>Dosage</th>
                  <th>instructions</th>
                  <th>Price</th>
                  <th>Action</th>
                </thead>
                <tbody id="prescriptionList">
        <!--Add from CRUD-->
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="4" style="text-align: right;"><strong>TOTAL</strong></td>
                    <td id="totalAmount"></td>
                  </tr>
                </tfoot>          
              </table>              
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
    $('#tabs').tabs();
  </script>
<script type="text/javascript">
  var refno = "<?= $refno?>";
  var totalAmount = 0;
  var Request = null;

  function GetDrugProperties(drugname){
    $.ajax({
      method:'POST',
      url:'CRUD.php',
      data:{GetDrugProperties:'1',drugname:drugname},
      success:function(response){
        var parts = response.split(';');
        $('#q_instore').val(parts[0]);
        $('#cost').val(parts[1]);
      }
    });
  }
//Add drug to the list above
  function addToTable(){
    var drugname = $('#drugname').val();
    var q_instore = +$('#q_instore').val();
    var cost = +$('#cost').val();
    var dosage = +$('#dosage').val();
    var freq = +$('#freq').val();
    var days = +$('#days').val();
    var instructions = $('#instructions').val();

    if (drugname.length==0) {SnackNotice(false,'Select/Enter the drug name');return;}
    if (isNaN(q_instore) || q_instore <= 0) {SnackNotice(false,'Selected drug is currently not available in store');return;}
    if (isNaN(dosage) || dosage <= 0) {SnackNotice(false,'Dosage must a number greater than Zero');return;}
    if (isNaN(+freq) || freq <= 0) {SnackNotice(false,'The Frequency/Number of times this drug is taken per day must be a number greater than Zero');return;}
    if (isNaN(+days) || days<= 0) {SnackNotice(false,'The number of days for this prescription must be a number greater than Zero');return;}

    var drugPrice = (cost * dosage * freq * days).toFixed(2);
    dosage = dosage +'x'+ freq + 'x'+days;

    //Check if drug already added to list
    if (AlreadyAdded(drugname)) {
            SnackNotice(false,'This drug is already added');
      return;
    }

    //Add drug to list
    $('#prescriptionList').append('\n'
      +"<tr>"
      + "<td>"+drugname+"</td>"
      + "<td>"+dosage+"</td>"
      + "<td>"+instructions+"</td>"
      + "<td>"+drugPrice+"</td>"
      + "<td style='text-align:center'>"
      +"<i class='oi oi-trash text-danger' onclick='removeRows($(this))' style='cursor:pointer'></i>"
      +"</td>"
      +"</tr>"
      );

    getListTotal();
  }

  function AlreadyAdded(drugname){
    var added  = false;
    $('#prescriptionList tr').each(function(){
          var row = $(this);
          var addedDrug = row.find('td:nth-child(1)').text();
          if (addedDrug==drugname) {
            added  = true;
          }
        });
        return added;
  }
  function removeRows(elem){
    var row = $(elem).parents('tr');
    row.remove();
    getListTotal();
  }
  function getListTotal(){
    var totalAmount = 0;
    $('#prescriptionList tr td:nth-child(4)').each(function(){
      totalAmount += +$(this).text();
    });
    $('#totalAmount').text(totalAmount.toFixed(2));
  }
//Save Drug Prescriptions
  function SavePrescription(){
    var total_requests = 0;
    var totalAmount = 0;
      var data = "";
      var service_id;
      //get request ids
      $('#prescriptionList tr').each(function(){
          var row = $(this);
          total_requests ++;
          var drugname = row.find('td:nth-child(1)').text();
          var dosage = row.find('td:nth-child(2)').text();
          var instructions = row.find('td:nth-child(3)').text();
          var drugPrice = row.find('td:nth-child(4)').text();

          data += drugname+";"+dosage+";"+instructions+";"+drugPrice+"---";
          totalAmount += +drugPrice; 
        });
      if (total_requests==0) {
        SnackNotice(false,'There is no prescription list to submit');
        return;
      } 
      RitchConfirm("Proceed ?","Send a prescription request worth <b> Ksh. "+totalAmount+"</b>").then(function(){   
      //save payments via ajax
        $('#processDialog').modal('toggle');
          $.ajax({
            method:'post',
            url:'crud.php',
            data:{SavePrescription:'1',refno:refno,data:data},
            success:function(response){
              $('#processDialog').modal('toggle');
              if (response.includes('success')) {
                SnackNotice(true,'Prescription saved successfully');
                window.location.href="Prescription Queue.php";
              }else{
               SnackNotice(false,response);
              }
            },
          });
       });
  }
</script>
</body>
</html>
