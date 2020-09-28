<?php
include('../ConnectionClass.php');
include('../db_class.php');
session_start();

$db = new CRUD();
$log_id = $_GET['log-id'];
  $LogBook = $db->ReadOne("SELECT * FROM tbl_radiology_log WHERE log_id = '$log_id'");
  $refno = $LogBook['refno'];

  $Hospital = $db->ReadOne("SELECT * FROM tbl_hospital");
  $Patient = $db->ReadOne("SELECT * FROM tbl_patient WHERE refno = '$refno'");
  $age = $db->getPatientAge($Patient['dob']);
?>
<html>
<head>
  <!--Links-->
  <?php 
    include('../sub_links.php');
  ?>
  <style type="text/css">
    @media print{
      @page { size: auto; margin: 0px;}
      body  { margin: 0px; padding: 0px;}
    } 
  </style>
  </style>
</head>
<body style="padding: 20px 50px;" onload="print(); close();" >
      <table align="center" style="width: 100%; font-family:'Courier New', Courier, monospace;">
        <tbody>
          <tr align="center">
            <td colspan="2">
              <span><b style="font-size: 20px;"><?= $Hospital['hospital_name']?></b></span><br>
              <span style="font-size: 15px;"><?= $Hospital['postal_address']?></span><br>
              <span style="font-size: 15px;"><?= $Hospital['email']?></span><br><br>
              <span style="text-decoration: underline;"><b>Radiology Report</b></span>
            </td>
          </tr>
          <tr><td colspan="2"><p></p></td></tr>
          <tr>
            <td><b>Log ID.:</b>  <?= $LogBook['log_id']?></td>
            <td><b>Date.:</b>  <?= date('d/m/Y') ?></td>
          </tr>
          <tr><td colspan="2"><p></p></td></tr>
      <!-- Investigation Details -->
          <tr style="border-bottom: 1px solid #444;"><td colspan="2"><b>Patient Information</b></td></tr>
          <tr>
            <td><b>Patient Name.:</b> <?= $Patient['fullname']?></td>
            <td><b>Registration No</b>  <?= $Patient['refno']?></td>
          </tr>
          <tr>
            <td><b>DOB & Age (Years)</b> <?= $Patient['dob']?> (<?= $age?>)</td>
            <td><b>Gender</b>  <?= $Patient['sex']?></td>
          </tr>
          <tr>            
            <td><b>Marital Status</b> <?= $Patient['marital_status']?></td>
          </tr>
          <tr><td colspan="2"><p></p></td></tr>
      <!-- Investigation Details -->
              <tr style="border-bottom: 1px solid #444;"><td colspan="2"><b>Investigation Details</b></td></tr>
              <tr>
                <td><b>Investigation</b></td>
                <td><?= $LogBook['investigation']?></td>
              </tr>
              <tr>
                <td><b>Investigating Officer Comment</b></td>
                <td><?= $LogBook['comment']?></td>
              </tr>
        </tbody>
      </table> 
</body>
</html>