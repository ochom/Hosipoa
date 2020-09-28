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
if (!($User_level=='admin' || $GroupPrivileges['system_setting_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
}

include '../ConnectionClass.php';

?>
<!DOCTYPE html>
<html>
<head>
  <!--Links-->h
  <?php 
    include('../sub_links.php');
  ?>
  <!--//Links-->
  <style type="text/css">
  	ul li{
  		color: blue;
  	}
  	ul li ul li{
  		color: green;
  	}
  	li{
  		list-style: none;
  	}
  </style>
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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> System Settings</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
      		<b><i class="oi oi-lock-locked"></i> User Groups & Privileges</b>
      	</div> 
          <div class="row col-11 " style="margin: auto;">
          <div class="col-sm-12 col-md-4" style="height: 500px; padding: 10px 20px; border-radius: 5px; border:1px solid #ccc; margin-top: 10px;">
          	<div class="form-group col-sm-12">
                <label>Group Name</label>
                <?php
          			if (isset($_GET['user-group'])) {
          				$group_name = $_GET['user-group'];
						$Group = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_user_groups WHERE group_name='$group_name'"),MYSQLI_ASSOC);
						$group_name = $Group['group_name'];
          				echo "<input id='group_name' class='form-control form-control-sm' value='$group_name'>";
          			}else{
          				echo "<input id='group_name' class='form-control form-control-sm'>";
          			}
          		?>
              </div>
              <div class="form-group col-sm-12">
                <button class="btn btn-outline-success btn-sm col-12" onclick="AddGroup()">
                	<i class="oi oi-plus"></i> Add Group
                </button>
              </div>
          	<table class="table table-sm table-striped" style="cursor: pointer;">
          		<?php
          			$row = mysqli_query($conn,"Select * From tbl_user_groups");
          			while ($Group = mysqli_fetch_assoc($row)) {
          				?>
          				<tr onclick="window.location.href='User Groups.php?user-group=<?= $Group['group_name']?>'">
          					<td><?= $Group['group_id']?></td>
          					<td><?= $Group['group_name']?></td>
          					<td><i class="oi oi-lock-locked text-success"></i></td>
          				</tr>
          				<?php
          			}
          		?>
          	</table>
          </div>
          <?php
          	if (isset($_GET['user-group'])) {
				$group_name = $_GET['user-group'];
				$Group = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_user_groups WHERE group_name='$group_name'"),MYSQLI_ASSOC);
          ?>
          <div class="col-sm-12 col-md-8" style="height: 500px; padding: 10px 20px; border-radius: 5px; border:1px solid #ccc; margin-top: 10px; overflow: hidden;">
          	<b class="text-info col-12" style="position: absolute; background-color: white; border-bottom: 1px solid #ccc; z-index: 100"><input type="checkbox" onclick="Check(this)"> All Privilages</b>
          	<button onclick="ModifyPrivilages()" class="btn btn-success btn-sm" style="position: absolute; right: 5px; top: 450px;"><i class="oi oi-check"></i> Save changes</button> 
            <div style=" overflow-y: scroll; height: 100%; width: 100%;" >
          	<ul style="margin-left: 20px; margin-top: 30px;">
          		<li><i class="oi oi-fire"></i> Administration
	          		<ul>
	          			<?php
	          				if ($Group['system_setting_priv']==0) {
	          					echo "<li><input id='system_setting_priv' type='checkbox'> Modify System Settings</li>";
	          				}else{
	          					echo "<li><input id='system_setting_priv' type='checkbox' checked> Modify System Settings</li>";
	          				}
	          			?>
	          		</ul>
          		</li>
          		<li><i class="oi oi-fire"></i> Accounts and Revenue
	          		<ul>
	          			<?php
	          				if ($Group['revenue_cash_collection_priv']==0) {
	          					echo "<li><input id='revenue_cash_collection_priv' type='checkbox'> Collect Cash</li>";
	          				}else{
	          					echo "<li><input id='revenue_cash_collection_priv' type='checkbox' checked> Collect Cash</li>";
	          				}
	          			?>
						<?php
	          				if ($Group['revenue_billing_priv']==0) {
	          					echo "<li><input id='revenue_billing_priv' type='checkbox'> Billing</li>";
	          				}else{
	          					echo "<li><input id='revenue_billing_priv' type='checkbox' checked> Billing</li>";
	          				}
	          			?>
	          		</ul>
          		</li>
          		<li><i class="oi oi-fire"></i> Procurement
          			<ul>
          				<?php
	          				if ($Group['procurement_priv']==0) {
	          					echo "<li><input id='procurement_priv' type='checkbox'> Manage procurements, Assets and Equipments</li>";
	          				}else{
	          					echo "<li><input id='procurement_priv' type='checkbox' checked> Manage procurements, Assets and Equipments</li>";
	          				}
	          			?>
	          		</ul>
          		</li>
          		<li><i class="oi oi-fire"></i> Records
          			<ul>
          				<?php
	          				if ($Group['records_priv']==0) {
	          					echo "<li><input id='records_priv' type='checkbox'> Register, Modify and View Patients Records</li>";
	          				}else{
	          					echo "<li><input id='records_priv' type='checkbox' checked> Register, Modify and View Patients Records</li>";
	          				}
	          			?>
	          		</ul>
          		</li>
          		<li><i class="oi oi-fire"></i> Out-patient
          			<ul>
          				<?php
	          				if ($Group['opd_triage_priv']==0) {
	          					echo "<li><input id='opd_triage_priv' type='checkbox'> Triage-Take patient vitals and screening data</li>";
	          				}else{
	          					echo "<li><input id='opd_triage_priv' type='checkbox' checked> Triage-Take patient vitals and screening data</li>";
	          				}
	          			?>
	          			<?php
	          				if ($Group['opd_treatment_priv']==0) {
	          					echo "<li><input id='opd_treatment_priv' type='checkbox'> Treatment - Create treatment forms and prescriptions</li>";
	          				}else{
	          					echo "<li><input id='opd_treatment_priv' type='checkbox' checked> Treatment - Create treatment forms and prescriptions</li>";
	          				}
	          			?>
	          		</ul>
          		</li>
          		<li><i class="oi oi-fire"></i> Laboratory
          			<ul>
          				<?php
	          				if ($Group['laboratory_priv']==0) {
	          					echo "<li><input id='laboratory_priv' type='checkbox'> Conduct investigations and handle laboratory equipments</li>";
	          				}else{
	          					echo "<li><input id='laboratory_priv' type='checkbox' checked> Conduct investigations and handle laboratory equipments</li>";
	          				}
	          			?>
	          		</ul>
          		</li>
          		<li><i class="oi oi-fire"></i> Pharmacy
          			<ul>
	          			<?php
	          				if ($Group['pharmacy_priv']==0) {
	          					echo "<li><input id='pharmacy_priv' type='checkbox'> Dispense drugs to patients</li>";
	          				}else{
	          					echo "<li><input id='pharmacy_priv' type='checkbox' checked> Dispense drugs to patients</li>";
	          				}
	          			?>
	          		</ul>
          		</li>
          		<li><i class="oi oi-fire"></i> Radiology
          			<ul>
          				<?php
	          				if ($Group['radiology_priv']==0) {
	          					echo "<li><input id='radiology_priv' type='checkbox'> Conduct X-Rays and Radilogy investigations</li>";
	          				}else{
	          					echo "<li><input id='radiology_priv' type='checkbox' checked> Conduct X-Rays and Radilogy investigations</li>";
	          				}
	          			?>
	          		</ul>
          		</li>
          		<li><i class="oi oi-fire"></i> MCH
          			<ul>
          				<?php
	          				if ($Group['maternity_priv']==0) {
	          					echo "<li><input id='maternity_priv' type='checkbox'> Maternity Care</li>";
	          				}else{
	          					echo "<li><input id='maternity_priv' type='checkbox' checked> Maternity Care</li>";
	          				}
	          			?>
	          		</ul>
          		</li>
          		<li><i class="oi oi-fire"></i> Eye
          			<ul>
          				<?php
	          				if ($Group['eye_priv']==0) {
	          					echo "<li><input id='eye_priv' type='checkbox'> Examine and handle clients/patients with eye issues</li>";
	          				}else{
	          					echo "<li><input id='eye_priv' type='checkbox' checked> Examine and handle clients/patients with eye issues</li>";
	          				}
	          			?>
	          		</ul>
          		</li>
          		<li><i class="oi oi-fire"></i> Dental
          			<ul>
          				<?php
	          				if ($Group['dental_priv']==0) {
	          					echo "<li><input id='dental_priv' type='checkbox'> Examine and handle clients/patients with dental issues</li>";
	          				}else{
	          					echo "<li><input id='dental_priv' type='checkbox' checked> Examine and handle clients/patients with dental issues</li>";
	          				}
	          			?>
	          		</ul>
          		</li>
          		<li><i class="oi oi-fire"></i> In-patient
          			<ul><?php
	          				if ($Group['ipd_treatment_priv']==0) {
	          					echo "<li><input id='ipd_treatment_priv' type='checkbox'> Treatment - Create treatment forms and prescriptions</li>";
	          				}else{
	          					echo "<li><input id='ipd_treatment_priv' type='checkbox' checked> Treatment - Create treatment forms and prescriptions</li>";
	          				}
	          			?>
	          			<?php
	          				if ($Group['ipd_general_service_priv']==0) {
	          					echo "<li><input id='ipd_general_service_priv' type='checkbox'> Collect laboratory specimen,Shift beds,Charge for services etc</li>";
	          				}else{
	          					echo "<li><input id='ipd_general_service_priv' type='checkbox' checked> Collect laboratory specimen,Shift beds,Charge for services etc</li>";
	          				}
	          			?>
	          			<?php
	          				if ($Group['ipd_stock_return_priv']==0) {
	          					echo "<li><input id='ipd_stock_return_priv' type='checkbox'> Stock Return - Receive unused items from patients</li>";
	          				}else{
	          					echo "<li><input id='ipd_stock_return_priv' type='checkbox' checked> Stock Return - Receive unused items from patients</li>";
	          				}
	          			?>
	          			<?php
	          				if ($Group['ipd_patient_discharge_priv']==0) {
	          					echo "<li><input id='ipd_patient_discharge_priv' type='checkbox'> Discharge In-patients</li>";
	          				}else{
	          					echo "<li><input id='ipd_patient_discharge_priv' type='checkbox' checked> Discharge In-patients</li>";
	          				}
	          			?>
	          		</ul>
          		</li>
          		<li><i class="oi oi-fire"></i> Orders and Stock
          			<ul>
          				<?php
	          				if ($Group['orders_stock_priv']==0) {
	          					echo "<li><input id='orders_stock_priv' type='checkbox'> Place orders and Manage Stock for Respective Department</li>";
	          				}else{
	          					echo "<li><input id='orders_stock_priv' type='checkbox' checked> Place orders and Manage Stock for Respective Department</li>";
	          				}
	          			?>
	          		</ul>
          		</li>
          		<li><i class="oi oi-fire"></i> Morgue
          			<ul>
          				<?php
	          				if ($Group['morgue_priv']==0) {
	          					echo "<li><input id='morgue_priv' type='checkbox'> Receive, Prepare, Store and Release Bodies</li>";
	          				}else{
	          					echo "<li><input id='morgue_priv' type='checkbox' checked> Receive, Prepare, Store and Release Bodies</li>";
	          				}
	          			?>
	          		</ul>
          		</li>
          	</ul>
            </div>
          </div>
      <?php } ?>
        </div>
      </div>
  </div>
</div>
</body>

<!--Proccessing dialog-->
 <div class="modal modal-static" id="processDialog" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div style="background-color: #eee;" id="progressBar"><div class="box2"></div></div>  
</div>
  <!-- Menu Toggle Script -->
  <script>
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });

    function AddGroup(){
    	var group_name = $('#group_name').val();
    	if (group_name.length===0) {SnackNotice(false,'Enter the name of the group to add'); return;}
    	$('#processDialog').modal('toggle');
    	$.ajax({
    		method:'post',
    		url:'crud.php',
    		data:{AddGroup:'1',group_name:group_name},
    		success:function(response){
    			$('#processDialog').modal('toggle');
	            SnackNotice(false,response);
	            window.location.href='User Groups.php';
    		}
    	});
    }

    function Check(elem){
    	if ($(elem).is(':checked')) {
    		$('input[type=checkbox]').attr('checked',true);
    	}else{
    		$('input[type=checkbox]').attr('checked',false);
    	}
    }
    function ModifyPrivilages(){
    	var group_name = $('#group_name').val();
    	var system_setting_priv = $('#system_setting_priv').is(':checked')?1:0;
    	var revenue_cash_collection_priv = $('#revenue_cash_collection_priv').is(':checked')?1:0;
    	var revenue_billing_priv = $('#revenue_billing_priv').is(':checked')?1:0;
    	var procurement_priv = $('#procurement_priv').is(':checked')?1:0;
    	var records_priv = $('#records_priv').is(':checked')?1:0;
    	var opd_triage_priv = $('#opd_triage_priv').is(':checked')?1:0;
    	var opd_treatment_priv = $('#opd_treatment_priv').is(':checked')?1:0;
    	var maternity_priv = $('#maternity_priv').is(':checked')?1:0;
    	var pharmacy_priv = $('#pharmacy_priv').is(':checked')?1:0;
    	var laboratory_priv = $('#laboratory_priv').is(':checked')?1:0;
    	var radiology_priv = $('#radiology_priv').is(':checked')?1:0;
    	var morgue_priv = $('#morgue_priv').is(':checked')?1:0;
    	var orders_stock_priv = $('#orders_stock_priv').is(':checked')?1:0;
    	var ipd_treatment_priv = $('#ipd_treatment_priv').is(':checked')?1:0;
    	var ipd_general_service_priv = $('#ipd_general_service_priv').is(':checked')?1:0;
    	var ipd_bed_ward_shift_priv = $('#ipd_bed_ward_shift_priv').is(':checked')?1:0;
    	var ipd_stock_return_priv = $('#ipd_stock_return_priv').is(':checked')?1:0;
    	var ipd_patient_discharge_priv = $('#ipd_patient_discharge_priv').is(':checked')?1:0;
    	var eye_priv =  $('#eye_priv').is(':checked')?1:0;
    	var dental_priv = $('#dental_priv').is(':checked')?1:0;

    	if (group_name.length===0) {SnackNotice(false,'Enter the name of the group to add'); $('#group_name').focus(); return;}
    	$('#processDialog').modal('toggle');
    	$.ajax({
    		method:'post',
    		url:'crud.php',
    		data:{
    			ModifyPrivilages:'1',
		    	group_name:group_name,
		    	system_setting_priv:system_setting_priv,
		    	revenue_cash_collection_priv:revenue_cash_collection_priv,
		    	revenue_billing_priv:revenue_billing_priv,
		    	procurement_priv:procurement_priv,
		    	records_priv:records_priv,
		    	opd_triage_priv:opd_triage_priv,
		    	opd_treatment_priv:opd_treatment_priv,
		    	maternity_priv:maternity_priv,
		    	pharmacy_priv:pharmacy_priv,
		    	laboratory_priv:laboratory_priv,
		    	radiology_priv:radiology_priv,
		    	morgue_priv:morgue_priv,
		    	orders_stock_priv:orders_stock_priv,
		    	ipd_treatment_priv:ipd_treatment_priv,
		    	ipd_general_service_priv:ipd_general_service_priv,
		    	ipd_stock_return_priv:ipd_stock_return_priv,
		    	ipd_patient_discharge_priv:ipd_patient_discharge_priv,
		    	eye_priv:eye_priv,
		    	dental_priv:dental_priv
    		},
    		success:function(response){
    			$('#processDialog').modal('toggle');
	           if (response.includes('success')) { 
              SnackNotice(true,'Group Privileges updated successfully');             
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