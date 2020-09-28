<?php
include('../ConnectionClass.php');
include('../db_class.php');
session_start();


$db = new CRUD();
//Get Queue
	if (isset($_POST['GetQueue'])) {
		$sql = "SELECT * From tbl_patient WHERE refno IN (SELECT refno FROM tbl_opd_service_request WHERE req_department='Pharmacy' AND req_status='granted')";
        $res = $db->ReadAll($sql);
        while ($rowSet = mysqli_fetch_assoc($res)) {
        	$Service = $db->ReadOne("SELECT * FROM tbl_opd_service_request WHERE refno='$rowSet[refno]' AND req_status='granted'");
          ?>
            <tr>
              <td><?= $rowSet['refno']?></td>
              <td ><?= $rowSet['fullname']?></td>
              <td style="font-weight: bold;"><?= $rowSet['ins_status']?></td>
              <td><?= $Service['req_date']?></td>
              <td id="<?= $rowSet['refno']?>">--</td>
              <td>
				<button class="btn btn-outline-primary btn-sm" onclick="window.location.href='Dispensing.php?serveRef=<?= $rowSet['refno'] ?>'"> Dispense Drugs</button>
              </td>
            </tr>
          <?php
        }
	}
	if (isset($_POST['GetPrescriptionQueue'])) {        
        $res = $db->ReadAll("SELECT * FROM tbl_walk_in_queue WHERE target_department='Pharmacy'");
        while ($rowSet = mysqli_fetch_assoc($res)) {
        	$Patient = $db->ReadOne("SELECT * FROM tbl_patient WHERE refno='$rowSet[refno]'");
        	?>
            <tr>
              <td><?= $rowSet['refno']?></td>
              <td ><?= $Patient['fullname']?></td>
              <td style="font-weight: bold;"><?= $Patient['ins_status']?></td>
              <td><?= $rowSet['q_date']?></td>
              <td id="<?= $rowSet['refno']?>">--</td>
              <td>
              	<button class="btn btn-outline-primary btn-sm" onclick="window.location.href='Prescription.php?serveRef=<?= $rowSet['refno'] ?>'"> Prescribe Drugs</button>
              </td>
            </tr>
          <?php
        }
	}

//DISPENSE
	if (isset($_POST['SaveDispense'])) {
		sleep(0);
		$data  = mysqli_real_escape_string($conn,$_POST['data']);
		$Lines = explode("---", $data);
		foreach ($Lines as $line) {
			if (!empty($line)) {
				$Strings = explode(";", $line);
				$req_id = $Strings[0];
				$drug_name = $Strings[1];
				$drug_quantity = $Strings[2];
				//Get drug properties to compare with the requested amounts
				$DrugProperties = $db->ReadOne("SELECT * FROM tbl_item WHERE item_name = '$drug_name'");
				$StoreQuantity = $DrugProperties['total_pieces'];
				$stockBalance = $StoreQuantity - $drug_quantity;
				if ($stockBalance<=0) {
					echo "Sorry. The drug you want to dispense is currently out of Stock";
					//Stop execution if the item is out of stock....
					return;
				}
				//Reduce the stock quantity
				echo $db->Query("UPDATE tbl_item SET item_quantity=('$stockBalance'/item_pieces_per_unit), total_pieces=(total_pieces-'$drug_quantity') WHERE item_name = '$drug_name'");
				echo $db->Query("UPDATE tbl_opd_service_request SET req_status = 'delivered' WHERE req_id = '$req_id'");
			}
		}
	}


//PRESCRIPTION
	if (isset($_POST['GetDrugProperties'])) {
		$drugname = mysqli_real_escape_string($conn, $_POST['drugname']);
		$Drug = $db->ReadOne("SELECT * from tbl_item where item_name='$drugname' ");
		echo $Drug['total_pieces'].';'.$Drug['item_rate_cash'];
	}

	if (isset($_POST['SavePrescription'])) {
		sleep(0);
		$refno = mysqli_real_escape_string($conn, $_POST['refno']);
		$today = date('d/m/Y H:i:s');
		$lines = explode("---", mysqli_real_escape_string($conn, $_POST['data']));
		foreach($lines as $line) {
			if ($line != '') {
				$string_arr = explode(";", $line);
				$drugname = $string_arr[0];
				$dosage = $string_arr[1];
				$instructions = $string_arr[2];
				$dosage_scheme = explode("X",$dosage);
				$req_quantity = +$dosage[0]*+$dosage[1]*+$dosage[2];		
				$cost = $string_arr[3];	

				$StockProp = $db->ReadOne("SELECT * FROM tbl_item WHERE item_name='$drugname'");
				$stock_quantity = $StockProp['total_pieces'];

				if ($stock_quantity>=$req_quantity) {
					$stock_quantity = +$stock_quantity - +$req_quantity;//Dedeuct the quantity in stock
					$sql = "INSERT INTO tbl_opd_service_request (refno, req_date, req_name,req_des,req_comment,req_department, req_cost,req_by)VALUES('$refno','$today','$drugname','$dosage','$instructions', 'Pharmacy','$cost','$_SESSION[Fullname]') ";
					
					echo $db->Query($sql);
					//Stock count is finalized from the pharmacy department Crud so as to ensure
					//that that drugs are only removed from the database once dispensed
				}				
			}
		}
		echo $db->Query("DELETE FROM tbl_walk_in_queue WHERE refno='$refno' AND target_department='Pharmacy'");
	}



//Purchase Requisition
	if (isset($_POST['GetPurchaseRequisitions'])) {
		$searchVal = mysqli_real_escape_string($conn,$_POST['searchVal']);
		$sql = "SELECT * FROM tbl_item_orders WHERE od_item_name LIKE '%$searchVal%' AND (od_status = 'pending' OR od_status='rejected') ORDER BY od_id DESC,od_status ASC LIMIT 30";
		$cmd = $db->ReadAll($sql);
		$res = $db->ReadAll($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='6' class='text-primary'>There are no items matching criteria.</td></tr>"; return;
		}
		while ($row = mysqli_fetch_assoc($cmd)) {
			$item_code = $row['item_code'];
			$Item  = $db->ReadOne("SELECT * FROM tbl_item WHERE item_code='$item_code'");
			if ($Item['item_type']=='Drug') {
			?>
  			<tr>
      			<td><?= $row['od_id']?></td>
      			<td><?= $row['od_date']?></td>
      			<td><?= $row['item_code']?></td>
      			<td><?= $row['od_item_name']?></td>
      			<td><?= $row['od_item_quantity']?></td>	
      			<td><?= $row['od_status']?></td>
  			</tr>
			<?php
			}
		}    			
	}

	if (isset($_POST['GetStockItemsForRequsition'])) {
		$searchVal = mysqli_real_escape_string($conn,$_POST['searchVal']);
		$sql = "SELECT * FROM tbl_item WHERE item_name LIKE '%$searchVal%' AND (item_type ='Drug') ORDER BY (item_quantity+0) ASC, item_name ASC LIMIT 20";
		$cmd = $db->ReadAll($sql);
		$requests = $db->CountRows($sql);
		if ($requests == 0) {
			echo "<tr><td colspan='4' class='text-primary'>There are no drug with such name</td></tr>"; return;
		}
		while ($row = mysqli_fetch_assoc($cmd)) {
			$item_code = $row['item_code'];
			?>
  			<tr>
      			<td><?= $row['item_code']?></td>
      			<td><?= $row['item_name']?></td>
      			<td><?= $row['item_quantity']?></td>
      			<td>
      				<button onclick="$('#ItemListPopUp').modal('toggle'); PurchaseRequestThisItem('<?= $item_code?>');" class="btn btn-outline-success btn-sm">
      					<i class="oi oi-check"></i> Request
      				</button>
      			</td>	
  			</tr>
			<?php
		}    			
	}

	if (isset($_POST['PurchaseRequestThisItem'])) {
		$item_code = mysqli_real_escape_string($conn,$_POST['item_code']);
		$Item = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_item WHERE item_code= '$item_code'"),MYSQLI_ASSOC);
		echo $Item['item_code'].";".$Item['item_name'].";".$Item['item_quantity'];
	}

	if (isset($_POST['MakePurchaseRequest'])) {
		sleep(0);
		$order_date=date('d/m/Y H:i:s');
		$item_code=$_POST['item_code'];
		$item_name=$_POST['item_name'];
		$order_quantity=$_POST['order_quantity'];
		$stock_quantity=$_POST['stock_quantity'];
		$ordering_officer=$_POST['ordering_officer'];

		$sql = "INSERT INTO tbl_item_orders(od_date,item_code,od_item_name,od_item_quantity,item_current_quantity,od_officer) VALUES ('$order_date','$item_code','$item_name','$order_quantity','$stock_quantity','$ordering_officer')";
		echo $db->Query($sql);
	}

//Receive Drugs
	if (isset($_POST['GetConsignedOrdersToReceiveDrugs'])) {
		//Get suppliers who are  supplying drugs
		$sql = "SELECT * from tbl_supplier WHERE supplier_code IN ( SELECT item_supplier from tbl_item_orders where od_status = 'Consigned' AND od_item_name in (select item_name from tbl_item where item_type = 'Drug')) ORDER BY  supplier_name DESC";
		$res = $db->ReadAll($sql);
		$requests = $db->CountRows($sql);
		if ($requests == 0) {
			echo "<tr><td colspan='4'>There are no consigned drug orders. You therefore cannot receive new drugs</td></tr>"; return;
		}
		while ($Supplier = mysqli_fetch_assoc($res)) {
			$supplier_code = $Supplier['supplier_code'];
			$TotalOrders  = $db->CountRows("SELECT * from tbl_item_orders where od_status = 'Consigned' AND item_supplier='$supplier_code' AND  od_item_name in (select item_name from tbl_item where item_type = 'Drug') ");
			?>
			<tr onclick="$('#ReceiveGoodsPopUp').modal('hide')">
      			<td><?= $Supplier['supplier_code']?></td>
      			<td><?= $Supplier['supplier_name']?></td>
      			<td><b><?= $TotalOrders?></b></td>
      			<td>
			      	<button onclick="ReceiveDrugsPopUp('<?= $supplier_code?>')" class="btn btn-outline-success btn-sm"><i class="oi oi-cart"></i> Receive Goods</button>
			     </td>
  			</tr>
			<?php
		} 
	}

	if (isset($_POST['ReceiveDrugsPopUp'])) {
		$supplier_code = mysqli_real_escape_string($conn,$_POST['supplier_code']);
		$res = mysqli_query($conn,"SELECT * FROM tbl_item_orders WHERE item_supplier = '$supplier_code' AND od_status='Consigned' ");
		while ($Order = mysqli_fetch_assoc($res)) {
			$order_id = $Order['od_id'];
			$item_code = $Order['item_code'];
			$Item  = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_item WHERE item_code='$item_code'"),MYSQLI_ASSOC);
			if ($Item['item_type']=='Drug') {
			?>
			<tr onclick="$('#ReceiveDrugsPopUp').modal('hide'); GetItemPropsToReceive('<?= $order_id?>');">
      			<td><?= $Order['od_id']?></td>
      			<td><?= $Order['item_code']?></td>
      			<td><?= $Order['od_item_name']?></td>
  			</tr>
			<?php
			}
		} 
	}

	if (isset($_POST['GetItemPropsToReceive'])) {
		$od_id = mysqli_real_escape_string($conn,$_POST['od_id']);
		$Order = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_item_orders WHERE od_id='$od_id'"),MYSQLI_ASSOC);
		echo $Order['od_id'].";".$Order['item_code'].";".$Order['od_item_name'].";".$Order['od_item_quantity'];
	}

	if (isset($_POST['ReceiveItem'])) {
		$flow_date = date('d/m/Y H:i:s');
		$od_id = mysqli_real_escape_string($conn,$_POST['od_id']);
    	$item_code = mysqli_real_escape_string($conn,$_POST['item_code']);
    	$item_name = mysqli_real_escape_string($conn,$_POST['item_name']);
    	$order_quantity = mysqli_real_escape_string($conn,$_POST['order_quantity']);
    	$supply_quantity = mysqli_real_escape_string($conn,$_POST['supply_quantity']);
    	$batch_no = mysqli_real_escape_string($conn,$_POST['batch_no']);
    	$expiry_date = mysqli_real_escape_string($conn,$_POST['expiry_date']);
    	$receiption_note = mysqli_real_escape_string($conn,$_POST['receiption_note']);


    	$Order = $db->ReadOne("SELECT * FROM tbl_item_orders WHERE od_id='$od_id'");

    	$Item = $db->ReadOne("SELECT * FROM tbl_item WHERE item_code='$item_code'");

    	$cummulative_quantity = $supply_quantity + $Item['item_quantity']; 

    	$Supplier = $db->ReadOne("SELECT * FROM tbl_supplier WHERE supplier_code='$Order[item_supplier]'");

    	echo $db->Query("UPDATE tbl_item_orders SET supply_quantity='$supply_quantity', od_status = 'received' WHERE od_id='$od_id'");
    	echo $db->Query("UPDATE tbl_item SET item_quantity=(item_quantity+'$supply_quantity'), total_pieces=(total_pieces + (item_pieces_per_unit * '$supply_quantity')) WHERE item_code='$item_code'");
    	echo $db->Query("INSERT INTO tbl_item_flow (batch_no,item_code, item_name, flow_date, flow_type, flow_quantity, cummulative_quantity, flow_persons,received_by,receiption_note) VALUES ('$batch_no','$item_code', '$item_name', '$flow_date', 'receive', '$supply_quantity', '$cummulative_quantity', '$Supplier[supplier_name]','$_SESSION[Fullname]','$receiption_note')");
	}