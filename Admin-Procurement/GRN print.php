<?php
session_start();
include('../ConnectionClass.php');
  $item_code = $_GET['item_code'];

  $Hospital = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM tbl_hospital"),MYSQLI_ASSOC);

  $Item = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM tbl_item WHERE item_code = '$item_code'"),MYSQLI_ASSOC); 
  
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
              <span style="text-decoration: underline;"><b>GOODS RECEIVE NOTE</b></span>
            </td>
            <td rowspan="1" style="width: 100px;">
              <img src="../Images/logo.png" alt="LOGO" style="width: 100%; height: 100%; border: 1px solid #ddd;">
            </td>
          </tr>
          <tr><td colspan="2"><p></p></td></tr>
          <tr>
            <td colspan="2" align="right"><b>Print Date.:</b>  <?= date('d/m/Y') ?></td>
          </tr>
      <!-- Investigation Details -->
          <tr>
          	<td colspan="4"><b>ITEM CODE: <?= $Item['item_code']?></b></td>
          </tr>
          <tr style="border-bottom: 1px solid #ccc;">
          	<td colspan="4"><b>ITEM NAME: <?= $Item['item_name']?></b></td>
          </tr>
          </tbody>
      </table>
      <table border="1" style="width: 100%; border-collapse: collapse;">
        <tr style="font-weight: bold; background-color: #ccc;">
          <td  align="center">#</td>
          <td>Date</td>
          <td>Persons/Dept.</td>
          <td>Type</td>
          <td>Quantity</td>
          <td>Cummulative Quantity</td>
        </tr>
        <?php
          $i=0;
          $goods = mysqli_query($conn,"SELECT * FROM tbl_item_flow WHERE item_code='$item_code' ORDER BY flow_id ASC");
          while ($Good = mysqli_fetch_assoc($goods)) {
            $i++;
            if ($Good['flow_type']=='receive') {
            echo "<tr class='text-danger'>";
            }else{
            echo "<tr>";
            }
	            echo "<td align='center'>$i.</td>";
	            echo "<td>".$Good['flow_date']."</td>";
	            echo "<td>".$Good['flow_persons']."</td>";
	            echo "<td>".$Good['flow_type']."</td>";
	            echo "<td>".$Good['flow_quantity']."</td>";
	            echo "<td>".$Good['cummulative_quantity']."	</td>";
	        echo "</tr>";
          }
        ?>
        </tr>  
      </table>
      <p></p>
      <p>Prepared By.................................................................... Sign.............................. Date.....................</p>
      <p>Verified By................................................................... Sign.............................. Date.....................</p>
  </div>
    

  <script>
    var GrandTotal = <?= $GrandTotal?>;
    GrandTotal = GrandTotal.toFixed(2);
    $('#totalAmount').text(GrandTotal);
  </script>
</body>
</html>