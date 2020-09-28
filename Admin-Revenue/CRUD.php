<?php
include('../ConnectionClass.php');
include('../db_class.php');
$db = new CRUD();
session_start();
//Cash Payments
	if (isset($_POST['GetQueue'])) {
        $res = $db->ReadArray("SELECT * From tbl_patient WHERE refno IN (SELECT refno FROM tbl_opd_service_request WHERE req_status='not granted')");
        $i=0;
        foreach ($res as $rowSet):
        	$i++;
        	$request = $db->ReadOne("SELECT * FROM tbl_opd_service_request WHERE refno='$rowSet[refno]' AND req_status='not granted'");
        	$cash_bills = $db->CountRows("SELECT * FROM tbl_opd_service_request WHERE refno='$rowSet[refno]' AND req_status='not granted' AND payment_type='Cash'");
        	$cop_bills = $db->CountRows("SELECT * FROM tbl_opd_service_request WHERE refno='$rowSet[refno]' AND req_status='not granted' AND payment_type='Corporate'");
          ?>
		<tr>
			<td><?=$i?></td>
			<td><?= $request['req_date']?></td>
			<td><?= $rowSet['refno']?></td>
			<td ><?= $rowSet['fullname']?></td>
			<td ><?= $cash_bills?></td>
			<td ><?= $cop_bills?></td>
			<td>
				<?php if((int)$cash_bills>0):?>
					<button class='btn btn-primary btn-sm' onclick="ReceiveCash('<?= $rowSet['refno']?>')"><i class="oi oi-dollar"></i> Receive Cash</button>
				<?php endif?>
				<?php if((int)$cop_bills>0):?>
					<button class='btn btn-success btn-sm' onclick="GetCreditSlip('<?= $rowSet['refno']?>','<?= $request['req_id']?>')"><i class="oi oi-document"></i>  Print Credit Slip</button>
				<?php endif?>
			</td>
		</tr>
    <?php
    	endforeach;
	}

	if (isset($_POST['GetCashBills'])) {
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$fullname = $db->Readone("SELECT * FROM tbl_patient WHERE refno='$refno'")['fullname'];
		$rows = $db->ReadArray("SELECT * FROM tbl_opd_service_request WHERE refno='$refno' AND req_status='not granted' AND payment_type='Cash'");
		$total_bill = 0;
		$requests_list = array();
		foreach($rows as $row):
			$total_bill += $row['req_cost'];
			$requests_list[] = [$row['req_id'],$row['req_name'],$row['req_cost']];
		endforeach;
		$data = array(
			"fullname" => $fullname,
			"total_bill" => $total_bill,
			"requests_list" => $requests_list
		);

		echo json_encode($data);
	}

	if (isset($_POST['GetDiff'])) {
		$amount_paid = mysqli_real_escape_string($conn,$_POST['amount_paid']);
		$total_bill = mysqli_real_escape_string($conn,$_POST['total_bill']);

		echo ((float)$amount_paid - (float)$total_bill);
	}

	if (isset($_POST['SaveCashPayment'])) {
		$today = date('d/m/Y H:i:s');
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$req_codes = json_decode($_POST['req_codes']);
		$total_bill = mysqli_real_escape_string($conn,$_POST['total_bill']);
		$amount_paid = mysqli_real_escape_string($conn,$_POST['amount_paid']);
		$balance = mysqli_real_escape_string($conn,$_POST['balance']);
		$db->Query("INSERT INTO tbl_payment_receipts(pay_date,payment_type,amount,cash_paid,balance,received_by)VALUES('$today','Cash','$total_bill','$amount_paid','$balance','$_SESSION[Fullname]')");
		$receipt_no = $db->ReadOne("SELECT last_insert_id() as receipt_no FROM tbl_payment_receipts")['receipt_no'];
		foreach ($req_codes as $req_code) {				
			$db->Query("UPDATE tbl_opd_service_request SET payment_receipt='$receipt_no',req_status='granted' WHERE req_id='$req_code'");
		}
		$response = array(
			"success" => "success",
			"receipt_no" => $receipt_no
		);
		echo json_encode($response);
	}

	if (isset($_POST['GrantCreditSlip'])) {
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$req_id = mysqli_real_escape_string($conn,$_POST['req_id']);
		echo $db->Query("UPDATE tbl_opd_service_request SET req_status = 'granted' WHERE refno='$refno' AND req_id='$req_id'");
	}

/*cASH Receipt*/
	if (isset($_POST['GetCashReceipt'])) {
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
		      <p><b>Receipt No.</b> <?= $receipt_no ?><br><b>Date.</b><?= $Receipt['pay_date']?></p>
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
		              <td align="right">Total (Ksh)</td>
		              <td align="right"><b><?= $Receipt['amount'] ?></b></td>
		            </tr>
		          </tfoot>
		        </table>
		      <span>````````````````````````````````````</span>
		      <p align="center"><b>Received By.</b>  <?= $Receipt['received_by']?></p>
		      <p align="center"><span style="font-family: Times New Romans;">This is a system generated receipt</span></p>
		      <span>````````````````````````````````````</span>
		  </div>
		<?php
	}

/*CREDIT SLIP*/
	if (isset($_POST['GetSlip'])) {
		$refno = mysqli_real_escape_string($conn,$_POST['refno']);
		$Hospital = $db->ReadOne("SELECT * FROM tbl_hospital");
		$Patient = $db->ReadOne("SELECT * FROM tbl_patient WHERE refno='$refno'");
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
		              <span style="text-decoration: underline;"><b>CREDIT SLIP</b></span>
		            </td>
		          </tr>          
		        </tbody>
		      </table>
		      <p><b>Date.</b><?= date('d/m/Y H:i:s')?></p>
		      <p align="left"><b>Client.:</b> <?= $Patient['fullname']?></p>
		      <p><b>To All Concerned Departments:<br> This Clients is covered by the following insurance scheme(s)</b></p>
		      <span>````````````````````````````````````</span><br>
		      <p>
		          <?php
		            $rows = $db->ReadArray("SELECT * FROM tbl_patient_insurance_schemes WHERE refno='$refno'");
		            $i=0;
		            foreach ($rows as $row):
		            	$i++;
		            	$company_name = $db->ReadOne("SELECT * FROM tbl_ins_companies WHERE company_id='$row[company_id]'")['company_name'];
		            	echo $i.".".$company_name; 
		            endforeach;
		          ?>
		      </p>
		      <span>````````````````````````````````````</span><br>
		      <p align="center"><b>Printed By.</b>  <?= $_SESSION['Fullname']?></p>
		      <p align="center"><span style="font-family: Times New Romans;">This is a system generated receipt</span></p>
		      <span>````````````````````````````````````</span>
		  </div>
		<?php
	}


/*OPD Corporate Bill*/
	if(isset($_POST['GetOPDBillsList'])){
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$opd_file = $db->ReadOne("SELECT * FROM tbl_opd_visits WHERE fileno='$fileno'");
		$refno = $opd_file['patient_id'];
		$Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$refno'"); 
		$age = $db->getPatientAge($Patient['dob']);
		$Hospital = $db->ReadOne("SELECT * FROM tbl_hospital");
		?>
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
						<span style="text-decoration: underline;"><b>Charge Sheet (Bill)</b></span>
					</td>
					<td rowspan="1" style="width: 100px;">
						<img src="../Images/logo.png" alt="LOGO" style="width: 100%; height: 100%; border: 1px solid #ddd;">
					</td>
				</tr>
			</tbody>
		</table>
		<p align="right"><b>VISIT Date.:</b>  <?= $opd_file['visit_date'] ?></p>
		<table align="center" style="width: 100%; font-family:'Courier New', Courier, monospace;">
			<tr style="border-bottom: 1px solid #ccc;"><td colspan="2"><b>Patient Information</b></td></tr>
			<tr>
				<td><b>Patient Name.:</b> <?= $Patient['fullname']?></td>
				<td><b>Hospital Reg. No.:</b>  <?= $Patient['refno']?></td>
			</tr>
			<tr>
				<td><b>DOB & Age (Years): </b> <?= $Patient['dob']?> (<?= $age?>)</td>
				<td><b>ID NO.:</b>  <?= $Patient['idno']?></td>
			</tr>
			<tr> 
				<td><b>Gender:</b>  <?= $Patient['sex']?></td>           
				<td><b>Marital Status:</b> <?= $Patient['marital_status']?></td>
			</tr>
		</table>
		<p style="border-bottom: 2px solid #000;"><b>Services Offered</b></p>
		<?php
		$rows = $db->ReadArray("SELECT * FROM tbl_opd_service_request WHERE refno='$refno' AND fileno='$fileno' GROUP BY req_department ORDER BY req_id ASC");
		$GrandTotal = 0;
		foreach($rows as $ServiceGroup)://First loop
		?>
		<p style="border-bottom: 1px solid #ccc;"><b><?= $ServiceGroup['req_department']?></b></p>
		<table align="center" style="width: 100%; font-family:'Courier New', Courier, monospace;" border="1">
			<thead>
				<th class="text-center">#</th>
				<th>Date</th>
				<th>Service/Item/Drug</th>
				<th>Amount</th>
			</thead>
		<tbody>
		<?php
		$rs = $db->ReadArray("SELECT * FROM tbl_opd_service_request WHERE refno='$refno' AND fileno='$fileno' AND req_department= '$ServiceGroup[req_department]'");
		$i=0;
		$subtotal = 0;
		foreach ($rs as $Request)://Second loop
		$i++;
		$GrandTotal  += $Request['req_cost'];
		$subtotal += $Request['req_cost'];
		?>
			<tr>
				<td class="text-center" style="width: 30px;"><?= $i?></td>
				<td  style="width: 200px;"><?= $Request['req_date'] ?></td>
				<td><?= $Request['req_name'] ?></td>
				<td align="right" style="width: 100px;"><?= $Request['req_cost'] ?></td>
			</tr>
		<?php
		endforeach;/*second loop*/
		?>              				
		</tbody>
		<tfoot>
			<tr>
				<td class="text-right" colspan="3">Sub-Total (Ksh)</td>
				<td align="right"><b><?= $subtotal?></b></td>
			</tr>
		</tfoot>
		</table>
		<?php
		endforeach;/*first loop*/
		?>
		<p style="margin: 20px 10px;">
			Grand Total (Ksh): <b id="totalAmount" style="text-decoration: underline;"><?= number_format((float)$GrandTotal,'2','.','') ?></b>
			<br>
			<b>Prepared By:</b> <?= $_SESSION['Fullname']?><br>
			<b>On:</b> <?= date('d/m/Y H:i:s')?>
		</p>
	<?php

	}

/*IPD CASH Bill*/
	if(isset($_POST['GetIPDCashBillsList'])){
		$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
		$ipd_file = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE adm_no='$fileno'");
		$refno = $ipd_file['refno'];
		$Patient = $db->ReadOne("SELECT * From tbl_patient where refno = '$refno'"); 
		$age = $db->getPatientAge($Patient['dob']);
		$Hospital = $db->ReadOne("SELECT * FROM tbl_hospital");
		?>
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
						<span style="text-decoration: underline;"><b>Charge Sheet (Bill)</b></span>
					</td>
					<td rowspan="1" style="width: 100px;">
						<img src="../Images/logo.png" alt="LOGO" style="width: 100%; height: 100%; border: 1px solid #ddd;">
					</td>
				</tr>
			</tbody>
		</table>
		<p align="right"><b>Admission Date:</b>  <?= $ipd_file['adm_date'] ?></p>
		<p align="right"><b>Discharge Date:</b>  <?= $ipd_file['discharge_date'] ?></p>
		<table align="center" style="width: 100%; font-family:'Courier New', Courier, monospace;">
			<tr style="border-bottom: 1px solid #ccc;"><td colspan="2"><b>Patient Information</b></td></tr>
			<tr>
				<td><b>Patient Name:</b> <?= $Patient['fullname']?></td>
				<td><b>Reg. No.:</b>  <?= $Patient['refno']?> <b> IPD No.:</b>  <?= $ipd_file['adm_no']?></td>
			</tr>
			<tr>
				<td><b>DOB & Age (Years): </b> <?= $Patient['dob']?> (<?= $age?>)</td>
				<td><b>ID No.:</b>  <?= $Patient['idno']?></td>
			</tr>
			<tr> 
				<td><b>Gender:</b>  <?= $Patient['sex']?></td>           
				<td><b>Marital Status:</b> <?= $Patient['marital_status']?></td>
			</tr>
		</table>
		<p style="border-bottom: 2px solid #000;"><b>Services Offered</b></p>
		<?php
		$rows = $db->ReadArray("SELECT * FROM tbl_ipd_service_request WHERE refno='$refno' AND fileno='$fileno' GROUP BY req_department ORDER BY req_id ASC");
		$GrandTotal = 0;
		foreach($rows as $ServiceGroup)://First loop
		?>
		<p style="border-bottom: 1px solid #ccc;"><b><?= $ServiceGroup['req_department']?></b></p>
		<table align="center" style="width: 100%; font-family:'Courier New', Courier, monospace;" border="1">
			<thead>
				<th class="text-center">#</th>
				<th>Date</th>
				<th>Service/Item/Drug</th>
				<th>Amount</th>
			</thead>
		<tbody>
		<?php
		$rs = $db->ReadArray("SELECT * FROM tbl_ipd_service_request WHERE refno='$refno' AND fileno='$fileno' AND req_department= '$ServiceGroup[req_department]'");
		$i=0;
		$subtotal = 0;
		foreach ($rs as $Request)://Second loop
		$i++;
		$GrandTotal  += $Request['req_cost'];
		$subtotal += $Request['req_cost'];
		?>
			<tr>
				<td class="text-center" style="width: 30px;"><?= $i?></td>
				<td style="width: 200px;"><?= $Request['req_date'] ?></td>
				<td><?= $Request['req_name'] ?></td>
				<td align="right" style="width: 100px;"><?= number_format((float)$Request['req_cost'],'2','.','') ?></td>
			</tr>
		<?php
		endforeach;/*second loop*/
		?>              				
		</tbody>
		<tfoot>
			<tr>
				<td class="text-right" colspan="3">Sub-Total (Ksh)</td>
				<td align="right"><b><?= number_format((float)$subtotal,'2','.','') ?></b></td>
			</tr>
		</tfoot>
		</table>
		<?php
		endforeach;/*first loop*/
		?>
		<p style="margin: 20px 10px;">
			Grand Total (Ksh): <b id="totalAmount" style="text-decoration: underline;"><?= number_format((float)$GrandTotal,'2','.','') ?></b>
			<br>
			<b>Prepared By:</b> <?= $_SESSION['Fullname']?><br>
			<b>On:</b> <?= date('d/m/Y H:i:s')?>
		</p>
	<?php

	}


//IPD Corporate Billing
    if (isset($_POST['GetBillsList'])){
    	$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
    	$rows = $db->ReadArray("SELECT * FROM tbl_ipd_service_request WHERE fileno='$fileno' AND req_status='granted' GROUP BY req_name");
    		
    	$th = "<thead>";
		$th .= "<th>#</th><th>Service/Item/Drug</th><th>Qty.</th><th>Amount</th>";
		$th .= "</thead>";
		echo $th;
    	$i=0;
    	foreach($rows as $row):$i++;
    		$total_here = 0;
    		$rs = $db->ReadArray("SELECT req_cost FROM tbl_ipd_service_request WHERE fileno='$fileno' AND req_name='$row[req_name]'");
    		$count = $db->CountRows("SELECT req_cost FROM tbl_ipd_service_request WHERE fileno='$fileno' AND req_name='$row[req_name]'");
    		
    		foreach($rs as $r){$total_here += $r['req_cost'];}
    		?>
    		<tr>
    			<td><?= $i."."?></td>
    			<td><?= $row['req_name']?></td>
    			<td><?= $count ?></td>
    			<td align="right"><?= number_format((float)$total_here,'2','.','') ?></td>
    		</tr>
    	<?php
    	endforeach;
    }


	if (isset($_POST['GetRebatessList'])){
    	$fileno = mysqli_real_escape_string($conn,$_POST['fileno']);
    	$rows = $db->ReadArray("SELECT * FROM tbl_ipd_nhif_rebates WHERE fileno='$fileno' GROUP BY provided_service");
		$th = "<thead>";
		$th .= "<th>#</th><th>Service</th><th>Qty.</th><th>Amount</th>";
		$th .= "</thead>";
		echo $th;
    	$i=0;
    	foreach($rows as $row):$i++;
    		$total_here = 0;
    		$rs = $db->ReadArray("SELECT rebate_amount FROM tbl_ipd_nhif_rebates WHERE fileno='$fileno' AND provided_service='$row[provided_service]'");
    		$count = $db->CountRows("SELECT rebate_amount FROM tbl_ipd_nhif_rebates WHERE fileno='$fileno' AND provided_service='$row[provided_service]'");
    		foreach($rs as $r){$total_here += $r['rebate_amount'];}
    		?>
    		<tr>
    			<td><?= $i."."?></td>
    			<td><?= $row['provided_service']?></td>
    			<td><?= $count ?></td>
    			<td align="right"><?= number_format((float)$total_here,'2','.','')  ?></td>
    		</tr>
    	<?php
    	endforeach;

	}




//Morgue billing
	if (isset($_POST['FilterMorgueBillingList'])) {
		$searchBy = mysqli_real_escape_string($conn,$_POST['searchBy']);
		$searchVal = mysqli_real_escape_string($conn,$_POST['searchVal']);

		$sql = "SELECT * FROM tbl_morgue_admission WHERE $searchBy LIKE '%$searchVal%' AND status = 'Active' LIMIT 20";
        $res = $db->ReadAll($sql);
        while ($Body = mysqli_fetch_assoc($res)) {
          $adm_no = $Body['adm_no'];
          ?>
            <tr>
              <td><?= $Body['adm_no']?></td>
              <td ><?= $Body['adm_date']?></td>
              <td ><?= $Body['body_name']?></td>
              <td ><?= $Body['kin_name']?></td>
              <td ><?= $Body['kin_phone']?></td>
              <td>
              	<button class="btn btn-outline-primary btn-sm" onclick="var w = window.open('Morgue Bill.php?serveRef=<?= $adm_no?>'); w.focus();"><i class="oi oi-print"></i> Print Bill</button>
              	<button class="btn btn-outline-success btn-sm" onclick="ClearMorgueBill('<?= $adm_no?>')"><i class="oi oi-dollar"></i>  Clear Bill</button>
              </td>
            </tr>
      <?php
        }
	}

	if(isset($_POST['ClearMorgueBill'])) {
		$adm_no = mysqli_real_escape_string($conn,$_POST['adm_no']);
		$res =  $db->ReadAll("SELECT * FROM tbl_morgue_bills WHERE adm_no='$adm_no' AND bill_status != 'cleared'");
		$BillAmount = 0;
		while ($Bill = mysqli_fetch_assoc($res)) {
			$BillAmount += $Bill['bill_amount'];
		}
		echo "Do you want to clear the bill Amounting to (Ksh) ".$BillAmount;
    }

    if(isset($_POST['PerfomMorgueClearance'])) {
    	$adm_no = mysqli_real_escape_string($conn,$_POST['adm_no']);
      	if (mysqli_query($conn,"UPDATE tbl_morgue_bills SET bill_status='cleared' WHERE adm_no='$adm_no'")) {
      		echo "Bill Succesfully cleared. The body should be Released immediately to avoid further billing";
      	}
    }


?>