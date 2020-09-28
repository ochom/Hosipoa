<?php
session_start();
include('../ConnectionClass.php');
  $adm_no = $_GET['serveRef'];

  $Body = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM tbl_morgue_admission WHERE adm_no = '$adm_no'"),MYSQLI_ASSOC);
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
            <td rowspan="1" style="width: 100px;">
              <img src="../Images/logo.png" alt="LOGO" style="width: 100%; height: 100%; border: 1px solid #ddd;">
            </td>
            <td colspan="2">
              <span><b style="font-size: 20px;"><?= $Hospital['hospital_name']?></b></span><br>
              <span style="font-size: 15px;"><?= $Hospital['postal_address']?></span><br>
              <span style="font-size: 15px;"><?= $Hospital['email']?></span><br>
              <span style="font-size: 15px;"><?= $Hospital['phone']?></span><br><br>
              <span style="text-decoration: underline;"><b>Charge Sheet</b></span>
            </td>
            <td rowspan="1" style="width: 100px;">
              <img src="../Images/logo.png" alt="LOGO" style="width: 100%; height: 100%; border: 1px solid #ddd;">
            </td>
          </tr>
          <tr><td colspan="2"><p></p></td></tr>
          <tr>
            <td></td>
            <td><b>Date.:</b>  <?= date('d/m/Y') ?></td>
          </tr>
      <!-- Investigation Details -->
          <tr style="border-bottom: 1px solid #ccc;"><td colspan="2"><b>Body Details</b></td></tr>
          <tr>
            <td><b>Name.:</b> <?= $Body['body_name']?></td>
            <td><b>Admission No.:</b>  <?= $Body['adm_no']?></td>
          </tr>
          <tr>
            <td colspan="2"><b>Received From </b> <?= $Body['body_from']?></td>
          </tr>
          <tr><td colspan="2"><p></p></td></tr>
          <tr style="border-bottom: 1px solid #ccc;"><td colspan="2"><b>Body Home/Locality</b></td></tr>
          <tr>
            <td><b>Country.: </b> <?= $Body['country']?></td>
            <td><b>County/State.:</b>  <?= $Body['county']?></td>
          </tr>
          <tr> 
          	<td><b>Sub-County/Estate.: </b> <?= $Body['subcounty_ward']?></td>
          </tr>
          <tr><td colspan="2"><p></p></td></tr>
      <!-- Investigation Details -->
          <tr style="border-bottom: 1px solid #ccc;"><td colspan="2"><b>Services Offered</b></td></tr>
          <tr>
            <td colspan="2">
              <table border="1" style="width: 100%;">
                <thead>
                  <th class="text-center">#</th>
                  <th>Date</th>
                  <th>Service</th>
                  <th>Amount</th>
                </thead>
                <tbody class="request_list">
          				<?php
                    $GrandTotal = 0;
          					$query = mysqli_query($conn,"SELECT * FROM tbl_morgue_bills WHERE adm_no='$adm_no'");
          					$i=0;
          					while ($Service = mysqli_fetch_assoc($query)) {
          						$i++;
                      $GrandTotal  += $Service['bill_amount'];
          				?>
              				<tr>
              					<td class="text-center"><?= $i?></td>
              					<td><?= $Service['bill_date'] ?></td>
              					<td><?= $Service['bill_name'] ?></td>
              					<td><?= $Service['bill_amount'] ?></td>
              				</tr>
              				<?php
              					}
              				?>              				
              			</tbody>
                    <tfoot>
                      <tr style="font-weight: bold; font-size: 20px;">
                        <td colspan="3" class="text-right">Grand Total Ksh.</td>
                        <td align="center" style=" padding: 10px 0px;"><b id="totalAmount" style="text-decoration: underline;"><?= 0.00?></b></td>
                      </tr>
                    </tfoot>
                </table>
        </tbody>
      </table>
    <script>
      var GrandTotal = <?= $GrandTotal?>;
          GrandTotal = GrandTotal.toFixed(2);
          $('#totalAmount').text(GrandTotal);
    </script>
</body>
</html>