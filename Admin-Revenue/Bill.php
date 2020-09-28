<?php
include('../ConnectionClass.php');
include('../db_class.php');
session_start();

$db = new CRUD();

  $refno = $_GET['serveRef'];
  $Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$refno'"); 
  $age = $db->getPatientAge($Patient['dob']);
  $Hospital = $db->ReadOne("SELECT * FROM tbl_hospital");
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
      
    <script>
      var GrandTotal = <?= ?>;
          GrandTotal = GrandTotal.toFixed(2);
          $('#totalAmount').text(GrandTotal);
    </script>
</body>
</html>