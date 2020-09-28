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
if (!($User_level=='admin' || $GroupPrivileges['pharmacy_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
}

//Process page
  if (isset($_GET['serveRef'])) {
    include('../ConnectionClass.php');
    $refno = $_GET['serveRef'];
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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Pharmacy</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="col-11" style="background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
      		<b><i class="oi oi-eyedropper"></i> Drug Dispense</b>
      	</div> 
  <!--Service request list-->
          <div class="col-11" style="height: auto;padding: 10px; border-radius: 5px; border:1px solid #ccc;margin: auto; margin-top:10px;">
          <p>
            <button onclick="window.location.href='queue.php'" class="btn btn-sm btn-primary"><i class="oi oi-arrow-left"></i> Back to Queue</button>
          <?php
          	$sql = "SELECT * FROM tbl_opd_service_request WHERE refno = '$refno' AND req_department='Pharmacy' AND  	req_status = 'granted'";
          	$presCount = mysqli_num_rows(mysqli_query($conn,$sql));
          	if ($presCount==0) {
              ?>              </p>
            <p class='text-success'>Prescription List is Empty.</p>
          <?php
        }else{
          ?>
        </p>
            <table class="table table-bordered table-sm table-striped">
              <thead class="bg-dark text-light">
                <th class="text-center"><b class="oi oi-check text-success"></b></th>
                <th>Request Code</th>
                <th>Date</th>
                <th>Drug Name</th>
                <th>Dosage</th>
                <th>Pieces</th>
              </thead>
              <tbody id="request_list">
                <?php                 
                $result = mysqli_query($conn,$sql);
                while ($row = mysqli_fetch_assoc($result)) { 
                	$qnts = explode("x", $row['req_des']);
                	$qnty = +$qnts[0] * +$qnts[1] * +$qnts[2];
                ?>
                <tr style="cursor: pointer;">
                  <td class="text-center"><input type="checkbox"></td>
                  <td><?= $row['req_id']?></td>
                  <td><?= $row['req_date']?></td>
                  <td><?= $row['req_name']?></td>
                  <td><?= $row['req_des']?></td>
                  <td><?= $qnty ?></td>
                </tr>
                <?php } ?>
            </table>
            <div class="form-group">
              <button class="btn btn-success btn-sm" onclick="SaveDispense()"><i class="oi oi-check"></i> Dispense</button>
              <button class="btn btn-danger btn-sm" onclick="window.location.href='queue.php'"><i class="oi oi-x"></i> Close</button>
            </div>            
            <?php
        }
        ?>
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

    var totalAmount = 0;
    $('#request_list tr').each(function(){
      var row = $(this);
      var amount = row.find('.amount').text();
        totalAmount += +amount;
        $('#total').text(totalAmount);
    });

    $('input[type=checkbox]').on('click',function(){
      var totalAmount = 0;
        $('#request_list tr').each(function(){
          var row = $(this);
          if (row.find('input[type=checkbox]').is(':checked')) {
            var amount = row.find('.amount').text();
            totalAmount += +amount;
          }
            $('#total').text(totalAmount);
        });
      });

    $("tr").click(function(){
    	var row = $(this);
    	if (row.find('input[type=checkbox]').is(':checked')) {
    		row.find('input[type=checkbox]').attr('checked',false);
    	}else{
    		row.find('input[type=checkbox]').attr('checked',true);
    	}
    });

    function SaveDispense(){
      var req_count = 0;
      var data = "";
      var req_id,item_name,item_quantity;
      //get request ids
      $('#request_list tr').each(function(){
          var row = $(this);
          if (row.find('input[type=checkbox]').is(':checked')) {
            req_count++;
            req_id = row.find('td:nth-child(2)').text();
            item_name = row.find('td:nth-child(4)').text();
            item_quantity = row.find('td:nth-child(6)').text();
            data += req_id+';'+item_name+";"+item_quantity+"---";
          }
        });
      if (req_count==0) {
        SnackNotice(false,'You have not selected any drugs to dispense');
        return;
      }
      //save payments via ajax
      $('#processDialog').modal('toggle');
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{SaveDispense:'1',data:data},
        success:function(response){
          $('#processDialog').modal('toggle');
          if (response.includes('success')) {
            SnackNotice(true,"Drugs dispensed successfully");
            location.href=location.href;
          }else{
            SnackNotice(false,response);
          }
        }
      });
    }
  </script>
</body>
</html>