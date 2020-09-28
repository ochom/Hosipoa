<?php
include('../ConnectionClass.php');
include('../db_class.php');
$db = new CRUD();

  $refno = $_POST['refno'];
  $receipt_no = $_POST['receipt_no'];
  $Hospital = $db->ReadOne("SELECT * FROM tbl_hospital");
  $Patient = $db->ReadOne("SELECT * FROM tbl_patient WHERE refno='$refno'");
  $Receipt = $db->ReadOne("SELECT * FROM tbl_payment_receipts WHERE id='$receipt_no'");
?>
  
<div style="box-shadow: 3px 5px 5px rgba(0,0,0,0.5); width: 100mm; background-color: #fff; padding: 10px; font-family:'Courier New', Courier, monospace;">
      <table align="center" style="width: 100%;">
        <tbody>
          <tr align="center">
            <td>
              <span><img src="../Images/logo.png" alt="LOGO" style="width: 50px; height:50px; border: 1px solid #ddd;"></span><br>
              <span><b style="font-size: 20px;"><?= $Hospital['hospital_name']?></b></span><br>
              <span style="font-size: 15px;"><?= $Hospital['postal_address']?></span><br>
              <span style="font-size: 15px;"><?= $Hospital['email']?></span><br>
              <span style="font-size: 15px;"><?= $Hospital['phone']?></span><br>
              <span style="text-decoration: underline;"><b>Payment Receipt</b></span>
            </td>
          </tr>          
        </tbody>
      </table>
      <p><b>Recipt No.</b> <?= $receipt_no ?>  <b>Date.</b><?= $Receipt['pay_date']?></p>
      <p align="left"><b>Client.:</b> <?= $Patient['fullname']?></p>
      <p align="center">
        <table style="width: 100%;">
          <tr><td colspan="2"><span>````````````````````````````````````</span></td>
          <?php
            $rows = $db->ReadArray("SELECT * FROM tbl_opd_service_request WHERE payment_receipt='$receipt_no'");
            foreach ($rows as $row):?>
                <tr>
                  <td><?= $row['req_name']?></td>
                  <td align="right"><?= $row['req_cost']?></td>
                </tr>
           <?php 
            endforeach;
          ?>
          <tfoot>
            <tr><td colspan="2"><span>````````````````````````````````````</span></td>
            </tr>
            <tr>
              <td align="right">Total Ksh.</td>
              <td align="right"><b><?= $Receipt['amount'] ?></b></td>
            </tr>
          </tfoot>
        </table>
      <span>````````````````````````````````````</span>
      <p align="center"><b>Received By.</b>  <?= $Receipt['received_by']?></p>
      <p align="center"><span style="font-family: Times New Romans;">This is a system generated receipt</span></p>
      <span>````````````````````````````````````</span>
  </div>