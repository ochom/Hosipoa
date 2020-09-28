<?php
include('../ConnectionClass.php');
$labno = $_GET['labno'];
  $sql = "SELECT * FROM tbl_laboratory_log WHERE labno = '$labno'";
  $LogBook = mysqli_fetch_array(mysqli_query($conn, $sql),MYSQLI_ASSOC);
  $refno = $LogBook['refno'];

  $Patient = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM tbl_patient WHERE refno = '$refno'"),MYSQLI_ASSOC);
  $Hospital = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM tbl_hospital"),MYSQLI_ASSOC);
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
<body style="padding: 20px 50px;" onload="print(); close();"> 
      <table align="center" style="width: 100%; font-family:'Courier New', Courier, monospace;">
        <tbody>
          <tr align="center">
            <td colspan="2">
              <span><b style="font-size: 20px;"><?= $Hospital['hospital_name']?></b></span><br>
              <span style="font-size: 15px;"><?= $Hospital['postal_address']?></span><br>
              <span style="font-size: 15px;"><?= $Hospital['email']?></span><br><br>
              <span style="text-decoration: underline;"><b>Laboratory Report</b></span>
            </td>
          </tr>
          <tr><td colspan="2"><p></p></td></tr>
          <tr>
            <td><b>File No.:</b>  <?= $LogBook['fileno']?> <b>Lab No.:</b>  <?= $LogBook['labno']?></td>
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
            <td><b>DOB & Age (Years)</b> <?= $Patient['dob']?> (<?= $LogBook['patient_age']?>)</td>
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
                <td><b>Specimen</b></td>
                <td><?= $LogBook['specimen']?></td>
              </tr>
              <tr>
                <td><b>Collection Condition</b></td>
                <td><?= $LogBook['specimen_condition']?></td>
              </tr>
              <tr>
                <td><b>Collection Date</b></td>
                <td><?= $LogBook['date_of_sample_collection']?></td>
              </tr>
              <tr>
                <td><b>Date Received</b></td>
                <td><?= $LogBook['date_specimen_received']?></td>
              </tr>
              <tr>
                <td><b>Receiving Comment</b></td>
                <td><?= $LogBook['receiving_officer_comment']?></td>          
              </tr>
              <tr>
                <td><b>Lab-Tech</b></td>
                <td><?= $LogBook['receiving_officer']?></td>
              </tr>
              <tr><td colspan="2"><p></p></td></tr>
      <!-- Investigation Details -->
              <tr style="border-bottom: 1px solid #444;"><td colspan="2"><b>Results</b></td></tr>
              <tr>
                <td><b>Date</b></td>
                <td><?= $LogBook['result_date_time']?></td>
              </tr>
              <tr>
                <td><b>Turn Around Time</b></td>
                <td><?= $LogBook['turn_around_time']?></td>
              </tr>
              <tr>
                <td><b>Result</b></td>
                <td><b><?= $LogBook['result']?></b></td>
              </tr>
              <tr>
                <td><b>Analysis Comment</b></td>
                <td><?= $LogBook['analysing_officer_comment']?></td>
              </tr>
              <tr>
                <td><b>Lab-Tech</b></td>
                <td><?= $LogBook['analysing_officer']?></td>
              </tr>
              <tr><td colspan="2"><p></p></td></tr>
      <!-- Investigation Details -->
          <tr style="border-bottom: 1px solid #444;"><td colspan="2"><b>Verification</b></td></tr>
          <tr>
            <td><b>Date</b></td>
            <td><?= $LogBook['confirmation_time']?></td>
          </tr>
          <tr>
            <td><b>Approval Status</b></td>
            <td><?= $LogBook['status']?></td>
          </tr>
          <tr>
            <td><b>Comment</b></td>
            <td><?= $LogBook['confirming_officer_comment']?></td>
          </tr>
          <tr>
            <td><b>Lab-Tech</b></td>
            <td><?= $LogBook['confirming_officer']?></td>
          </tr>
        </tbody>
      </table> 
</body>
</html>