<?php
session_start();
include('../ConnectionClass.php');
  $supplier_code = $_GET['supplier_code'];

  $Hospital = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM tbl_hospital"),MYSQLI_ASSOC);

  $Supplier = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM tbl_supplier WHERE supplier_code = '$supplier_code'"),MYSQLI_ASSOC); 
  
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
  <div style="margin: 1cm 0.5mm">
    <table align="center" style="width: 100%; font-family:'Courier New', Courier, monospace; margin-bottom: 20px;">
        <tbody>
          <tr align="center">
            <td rowspan="1" style="width: 100px;">
              <img src="../Images/logo.png" alt="LOGO" style="width: 100%; height: 100%; border: 1px solid #ddd;">
            </td>
            <td colspan="2">
              <span><b style="font-size: 20px;"><?= $Hospital['hospital_name']?></b></span><br>
              <span style="font-size: 15px;"><?= $Hospital['postal_address']?></span><br>
              <span style="font-size: 15px;"><?= $Hospital['email']?></span><br>
              <span style="font-size: 15px;"><?= $Hospital['phone']?></span><br><br>
              <span style="text-decoration: underline;"><b>LOCAL PURCHASE ORDER</b></span>
            </td>
            <td rowspan="1" style="width: 100px;">
              <img src="../Images/logo.png" alt="LOGO" style="width: 100%; height: 100%; border: 1px solid #ddd;">
            </td>
          </tr>
          <tr><td colspan="2"><p></p></td></tr>
          <tr>
            <td colspan="2" align="right"><b>Date.:</b>  <?= date('d/m/Y') ?></td>
          </tr>
      <!-- Investigation Details -->
          <tr style="border-bottom: 1px solid #ccc;"><td colspan="2"><b>SUPPLIER NAME: <?= $Supplier['supplier_name']?></b></td></tr>
          </tbody>
      </table>
      <table border="1" style="width: 100%; border-collapse: collapse;">
        <tr style="font-weight: bold; background-color: #ccc;">
          <td  align="center">#</td>
          <td>Item Code</td>
          <td>ITEM NAME</td>
          <td class="text-right">QUANTITY</td>
          <td class="text-right">UNIT PRICE</td>
          <td class="text-right">TOTOAL PRICE</td>
        </tr>
        <?php
          $GrandTotal = 0;
          $i=0;
          $orders = mysqli_query($conn,"SELECT * FROM tbl_item_orders WHERE item_supplier='$supplier_code' AND od_status='Consigned'");
          while ($Order = mysqli_fetch_assoc($orders)) {
            $i++;
            $GrandTotal += $Order['od_cost'];
            ?>
            <tr>
            <td align="center"><?= $i."."?></td>
            <td><?= $Order['item_code']?></td>
            <td><?= $Order['od_item_name']?></td>
            <td class="text-right"><?= $Order['od_item_quantity']?></td>
            <td class="text-right"><?= $Order['od_unit_cost']?></td>
            <td class="text-right"><?= $Order['od_cost']?></td>
          </tr>
            <?php
          }
        ?>
        </tr>  
            <tr style="font-weight: bold; font-size: 20px;">
              <td colspan="5" class="text-right">Grand Total Ksh.</td>
              <td class="text-right"><b id="totalAmount" style="text-decoration: underline;"><?= 0.00/*round($GrandTotal,2,1);*/ ?></b></td>
            </tr>
      </table>
      <p></p>
      <p>Prepared By.................................................................... Sign.............................. Date.....................</p>
      <p>Consigned By................................................................... Sign.............................. Date.....................</p>
      <p>Approved By.................................................................... Sign.............................. Date.....................</p>
  </div>
    

  <script>
    var GrandTotal = <?= $GrandTotal?>;
    GrandTotal = GrandTotal.toFixed(2);
    $('#totalAmount').text(GrandTotal);
  </script>
</body>
</html>