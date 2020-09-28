<?php
include('../ConnectionClass.php');
include('../db_class.php');
$db = new CRUD();

//Procurement
//Suppliers
	if (isset($_POST['GetSuppliers'])) {
		$sql = "SELECT * FROM tbl_supplier  ORDER BY supplier_code ASC";
		$res = $db->ReadAll($sql);
		while ($Supplier = mysqli_fetch_assoc($res)) {
			?>
  			<tr>
      			<td><?= $Supplier['supplier_code']?></td>
      			<td><?= $Supplier['supplier_name']?></td>
      			<td><?= $Supplier['address']?></td>
      			<td><?= $Supplier['phone']?></td>
      			<td><?= $Supplier['town']?></td> 
      			<td>
      				<button class="btn btn-outline-success btn-sm" onclick="EditSupplier($(this).parents('tr'))">
      					<i class="oi oi-pencil"></i> Edit
      				</button>
      				<button class="btn btn-outline-danger btn-sm" onclick="DeleteSupplier('<?= $Supplier['supplier_code']?>')">
      					<i class="oi oi-trash"></i> Delete
      				</button>
      			</td>	
  			</tr>
			<?php
		}    			
	}

	if (isset($_POST['SaveSupplier'])) {
		$supplier_name= mysqli_real_escape_string($conn,$_POST['supplier_name']); 
		$address= mysqli_real_escape_string($conn,$_POST['address']); 
		$phone= mysqli_real_escape_string($conn,$_POST['phone']);
		$town= mysqli_real_escape_string($conn,$_POST['town']);
		
		$count = $db->CountRows("SELECT * from tbl_supplier WHERE supplier_name='$supplier_name' ");
		if ($count>0) {echo "The Supplier name entered already exists";return;}
		$qry = "INSERT INTO tbl_supplier (supplier_name,address, phone, town) VALUES ('$supplier_name','$address','$phone','$town')";
		echo $db->Query($qry);
	}
	

	if (isset($_POST['UpdateSupplier'])) {
		$supplier_code= mysqli_real_escape_string($conn,$_POST['supplier_code']); 
		$supplier_name= mysqli_real_escape_string($conn,$_POST['supplier_name']); 
		$address= mysqli_real_escape_string($conn,$_POST['address']); 
		$phone= mysqli_real_escape_string($conn,$_POST['phone']);
		$town= mysqli_real_escape_string($conn,$_POST['town']);
		
		$qry = "UPDATE tbl_supplier SET supplier_name = '$supplier_name',  address = '$address',  phone = '$phone',  town = '$town' WHERE supplier_code='$supplier_code' ";
		echo $db->Query($qry);
	}


	if (isset($_POST['DeleteSupplier'])) {
		$supplier_code = $_POST['supplier_code'];
		echo $db->Query("DELETE From tbl_supplier WHERE supplier_code='$supplier_code'");
	}

//Purchase Requisition
	if (isset($_POST['GetPurchaseRequisitions'])) {
		$searchVal = mysqli_real_escape_string($conn,$_POST['searchVal']);
		$sql = "SELECT * FROM tbl_item_orders WHERE od_item_name LIKE '%$searchVal%' AND (od_status = 'pending' OR od_status='rejected') ORDER BY od_id DESC,od_status ASC LIMIT 30";
		$res = $db->ReadAll($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='7' class='text-primary'>There are no purchase requests made</td></tr>"; return;
		}
		while ($row = mysqli_fetch_assoc($res)) {
			$order_id = $row['od_id'];
			?>
  			<tr>
      			<td><?= $row['od_id']?></td>
      			<td><?= $row['od_date']?></td>
      			<td><?= $row['item_code']?></td>
      			<td><?= $row['od_item_name']?></td>
      			<td><?= $row['od_item_quantity']?></td>	
      			<td><?= $row['od_status']?></td>
      			<td>
			      	<button onclick="ApproveRequisitionPopUp('<?= $order_id?>')" class="btn btn-outline-success btn-sm"><i class="oi oi-circle-check"></i> Approve</button>
			      	<?php 
			      	if ($row['od_status']!="Rejected") {
			      		?>
		      			<button onclick="RejectOrder('<?= $order_id?>')" class="btn btn-outline-danger btn-sm"><i class="oi oi-circle-x"></i> Reject</button>
			      		<?php
			      	}
			      	?>
			     </td>
  			</tr>
			<?php
		}    			
	}

	if (isset($_POST['GetStockItemsForRequsition'])) {
		$searchVal = mysqli_real_escape_string($conn,$_POST['searchVal']);
		$sql = "SELECT * FROM tbl_item WHERE item_name LIKE '%$searchVal%' AND (item_type !='Laboratory Service' AND item_type !='Radiology Service' AND item_type !='General Service' AND item_type !='Drug' AND item_type !='Medical Procedure') ORDER BY (item_quantity+0) ASC, item_name ASC LIMIT 20";
		$res = $db->ReadAll($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='4' class='text-primary'>There are no items with such name. Drugs Requisitions are made at pharmacy module</td></tr>"; return;
		}
		while ($row = mysqli_fetch_assoc($res)) {
			$item_code = $row['item_code'];
			?>
  			<tr>
      			<td><?= $row['item_code']?></td>
      			<td><?= $row['item_name']?></td>
      			<td><?= $row['item_quantity']?></td>
      			<td>
      				<button onclick="$('#ItemListPopUp').modal('hide'); PurchaseRequestThisItem('<?= $item_code?>');" class="btn btn-outline-success btn-sm">
      					<i class="oi oi-check"></i> Request
      				</button>
      			</td>	
  			</tr>
			<?php
		}    			
	}

	if (isset($_POST['PurchaseRequestThisItem'])) {
		$item_code = mysqli_real_escape_string($conn,$_POST['item_code']);
		$Item = $db->ReadOne("SELECT * FROM tbl_item WHERE item_code= '$item_code'");
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
//Purchase approval
	if (isset($_POST['ApproveThisItemGetProps'])) {
		$od_id = mysqli_real_escape_string($conn,$_POST['order_id']);
		$Order = $db->ReadOne("SELECT * FROM tbl_item_orders WHERE od_id='$od_id'");
		$item_code = $Order['item_code'];
		$Item = $db->ReadOne("SELECT * FROM tbl_item WHERE item_code = '$item_code'");

		echo $od_id.";".$Item['item_code'].";".$Item['item_name'].";".$Item['item_supplier'].";".$Item['purchase_price'].";".$Order['od_item_quantity'];
	}

	if (isset($_POST['ApproveRequisition'])) {
		$order_code = mysqli_real_escape_string($conn,$_POST['order_code']);
      	$item_code = mysqli_real_escape_string($conn,$_POST['item_code']);
      	$item_name = mysqli_real_escape_string($conn,$_POST['item_name']);
      	$item_supplier = mysqli_real_escape_string($conn,$_POST['item_supplier']);
      	$supply_cost = mysqli_real_escape_string($conn,$_POST['supply_cost']);
      	$order_unit = mysqli_real_escape_string($conn,$_POST['order_unit']);
      	$order_total_cost = mysqli_real_escape_string($conn,$_POST['order_total_cost']);

      	$db->Query("UPDATE tbl_item SET item_supplier='$item_supplier' WHERE item_code='$item_code'");
      	echo $db->Query("UPDATE tbl_item_orders SET od_item_quantity='$order_unit', item_supplier='$item_supplier', od_unit_cost='$supply_cost', od_cost='$order_total_cost',od_status='Approved' WHERE od_id='$order_code'");
	}

	if (isset($_POST['RejectOrder'])) {
		$od_id = mysqli_real_escape_string($conn,$_POST['order_id']);
		echo $db->Query("UPDATE tbl_item_orders SET od_status='Rejected' WHERE od_id='$od_id'");
	}


	
//Consign Orders
	if (isset($_POST['GetApprovedOrders'])) {
		$sql = "SELECT * FROM tbl_item_orders WHERE od_status = 'Approved' ORDER BY  od_id DESC LIMIT 30";
		$res = $db->ReadAll($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='8' class='text-primary'>There are no approved orders</td></tr>"; return;
		}
		while ($Order = mysqli_fetch_assoc($res)) {
			$order_id = $Order['od_id'];
			$Supplier = $db->ReadOne("SELECT * FROM tbl_supplier WHERE supplier_code='$Order[item_supplier]'");
			?>
  			<tr>
      			<td><?= $Order['od_id']?></td>
      			<td><?= $Order['od_date']?></td>
      			<td><?= $Order['od_item_name']?></td>
      			<td><?= $Order['od_item_quantity']?></td>
      			<td><b><?= $Order['od_cost']?></b></td>	
      			<td><?= $Supplier['supplier_name']?></td>	
      			<td><?= $Order['od_status']?></td>
      			<td>
			      	<button onclick="ConsignOrder('<?= $order_id?>')" class="btn btn-outline-success btn-sm"><i class="oi oi-circle-check"></i> Consign Order</button>
			      	<?php 
			      	?>
			     </td>
  			</tr>
			<?php
		}  
	}

	if (isset($_POST['ConsignOrder'])) {
		$od_id = mysqli_real_escape_string($conn,$_POST['od_id']);
		echo $db->Query("UPDATE tbl_item_orders SET od_status='Consigned' WHERE od_id='$od_id'");
	}

//Purchase LPO
	if (isset($_POST['GetConsignedOrders'])) {
		$sql = "SELECT * FROM tbl_supplier WHERE supplier_code IN (SELECT item_supplier FROM tbl_item_orders  WHERE od_status = 'Consigned') ORDER BY  supplier_name DESC ";
		$res = $db->ReadAll($sql);
		if ($db->CountRows($sql)==0) {
			echo "<tr><td colspan='8' class='text-primary'>There are no consigned orders</td></tr>"; return;
		}
		while ($Supplier = mysqli_fetch_assoc($res)) {
			$supplier_code = $Supplier['supplier_code'];
			$TotalCost = 0;
			$query =  $db->ReadAll("SELECT * FROM tbl_item_orders WHERE  item_supplier='$Supplier[supplier_code]' AND od_status='Consigned'");
			while ($Order = mysqli_fetch_assoc($query)) {
				$TotalCost += $Order['od_cost'];
			}
			?>
			<tr>
      			<td><?= $Supplier['supplier_code']?></td>
      			<td><?= $Supplier['supplier_name']?></td>
      			<td class="text-right"><b><?= $TotalCost?></b></td>
      			<td>
			      	<button onclick="PrintLPO('<?= $supplier_code?>')" class="btn btn-outline-primary btn-sm"><i class="oi oi-print"></i> Print L.P.O</button>
			     </td>
  			</tr>
			<?php
		} 
	}

//Receive Goods
	if (isset($_POST['GetConsignedOrdersToReceiveGoods'])) {
		//Get suppliers who are not supplying drugs
		$sql = "SELECT * from tbl_supplier WHERE supplier_code IN ( SELECT item_supplier from tbl_item_orders where od_status = 'Consigned' AND od_item_name in (select item_name from tbl_item where item_type != 'Drug')) ORDER BY  supplier_name DESC";
		$res = $db->ReadAll($sql);
		$requests = $db->CountRows($sql);
		if ($requests == 0) {
			echo "<tr><td colspan='4'>There are no consigned orders. You therefore cannot receive new good, Drugs are recived at pharmacy module</td></tr>"; return;
		}
		while ($Supplier = mysqli_fetch_assoc($res)) {
			$supplier_code = $Supplier['supplier_code'];
			$TotalOrders  = $db->CountRows("SELECT * from tbl_item_orders where od_status = 'Consigned' AND item_supplier='$supplier_code' AND  od_item_name in (select item_name from tbl_item where item_type != 'Drug') ");
			?>
			<tr onclick="$('#ReceiveGoodsPopUp').modal('hide')">
      			<td><?= $Supplier['supplier_code']?></td>
      			<td><?= $Supplier['supplier_name']?></td>
      			<td><b><?= $TotalOrders?></b></td>
      			<td>
			      	<button onclick="ReceiveGoodsPopUp('<?= $supplier_code?>')" class="btn btn-outline-success btn-sm"><i class="oi oi-cart"></i> Receive Goods</button>
			     </td>
  			</tr>
			<?php
		} 
	}

	if (isset($_POST['ReceiveGoodsPopUp'])) {
		$supplier_code = mysqli_real_escape_string($conn,$_POST['supplier_code']);
		$res = $db->ReadAll("SELECT * FROM tbl_item_orders WHERE item_supplier = '$supplier_code' AND od_status='Consigned' ");
		while ($Order = mysqli_fetch_assoc($res)) {
			$order_id = $Order['od_id'];
			$item_code = $Order['item_code'];
			$Item  = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_item WHERE item_code='$item_code'"),MYSQLI_ASSOC);
			if ($Item['item_type'] !== 'Drug') {
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
		$Order = $db->ReadOne("SELECT * FROM tbl_item_orders WHERE od_id='$od_id'");
		echo $Order['od_id'].";".$Order['item_code'].";".$Order['od_item_name'].";".$Order['od_item_quantity'];
	}

	if (isset($_POST['ReceiveItem'])) {
		$flow_date = date('d/m/Y H:i:s');
		$od_id = mysqli_real_escape_string($conn,$_POST['od_id']);
    	$item_code = mysqli_real_escape_string($conn,$_POST['item_code']);
    	$item_name = mysqli_real_escape_string($conn,$_POST['item_name']);
    	$order_quantity = mysqli_real_escape_string($conn,$_POST['order_quantity']);
    	$supply_quantity = mysqli_real_escape_string($conn,$_POST['supply_quantity']);

    	$Order = $db->ReadOne("SELECT * FROM tbl_item_orders WHERE od_id='$od_id'");

    	$Item = $db->ReadOne("SELECT * FROM tbl_item WHERE item_code='$item_code'");
    	$cummulative_quantity = $supply_quantity + $Item['item_quantity']; 

    	$Supplier = $db->ReadOne("SELECT * FROM tbl_supplier WHERE supplier_code='$Order[item_supplier]'");

    	$db->Query("UPDATE tbl_item_orders SET supply_quantity='$supply_quantity', od_status = 'received' WHERE od_id='$od_id'");
    	$db->Query("UPDATE tbl_item SET item_quantity=(item_quantity+'$supply_quantity'), total_pieces=(total_pieces + (item_pieces_per_unit * '$supply_quantity')) WHERE item_code='$item_code'");
	    echo $db->Query("INSERT INTO tbl_item_flow (item_code, item_name, flow_date, flow_type, flow_quantity, cummulative_quantity, flow_persons) VALUES ('$item_code', '$item_name', '$flow_date', 'receive', '$supply_quantity', '$cummulative_quantity', '$Supplier[supplier_name]')");    	
	}

//ITEMS MANAGEMENT
	//Item Categories
	if (isset($_POST['GetCats'])) {
		$res = $db->ReadAll("SELECT * FROM tbl_item_drug_lab_types ORDER BY  id ASC ");
		while ($row = mysqli_fetch_assoc($res)) {
			?>
  			<tr>
      			<td><?= $row['id']?></td>
      			<td><?= $row['cat_name']?></td>
      			<td><?= $row['cat_for']?></td>
      			<td>
      				<?php $ros = $db->ReadAll("SELECT * FROM tbl_item_drug_lab_sub_types WHERE cat_id ='$row[id]' order by id asc");
					while ($ro = mysqli_fetch_assoc($ros)) { ?>
						<span style="margin-right: 20px;"><?= $ro['sub_cat_name']?></span>
						<span style="cursor: pointer; font-size: 13px;" class="text-primary" onclick="EditSubcat('<?= $ro['id']?>','<?= $row['cat_name']?>','<?= $ro['sub_cat_name']?>')"><i class="oi oi-pencil"></i> Edit</span><br>
					<?php }?>
      			</td>
      			<td>
      				<button class="btn btn-outline-success btn-sm" onclick="Editcat($(this).parents('tr'))"><i class="oi oi-pencil"></i> Edit Category</button>
      				<button class="btn btn-outline-primary btn-sm" onclick="NewSubcat('<?= $row['id']?>',$(this).parents('tr').find('td:nth-child(2)'))"><i class="oi oi-plus"></i> Add Sub Category</button>
      			</td>	
  			</tr>
			<?php
		}    			
	}


	if (isset($_POST['SaveCat'])) {
		$cat_name = mysqli_real_escape_string($conn,$_POST['cat_name']);
		$cat_for = mysqli_real_escape_string($conn,$_POST['cat_for']);

		if ($db->Exists("SELECT * FROM tbl_item_drug_lab_types WHERE cat_name='$cat_name'")) {
			echo "A similar category name has already been used"; return;
		}
		echo $db->Query("INSERT INTO tbl_item_drug_lab_types (cat_name,cat_for) VALUES ('$cat_name','$cat_for')");
	}

	if (isset($_POST['UpdateCat'])) {
		$cat_code = mysqli_real_escape_string($conn,$_POST['cat_code']);
		$cat_name = mysqli_real_escape_string($conn,$_POST['cat_name']);
		$cat_for = mysqli_real_escape_string($conn,$_POST['cat_for']);

		if ($db->Exists("SELECT * FROM tbl_item_drug_lab_types WHERE cat_name='$cat_name'")) {
			echo "A similar category name has already been used"; return;
		}

		echo $db->Query("UPDATE tbl_item_drug_lab_types SET cat_name='$cat_name',cat_for='$cat_for' WHERE id='$cat_code'");
	}

	if (isset($_POST['SaveSubCat'])) {
		$cat_id = mysqli_real_escape_string($conn,$_POST['cat_id']);
		$sub_cat_name = mysqli_real_escape_string($conn,$_POST['sub_cat_name']);

		if ($db->Exists("SELECT * FROM tbl_item_drug_lab_sub_types WHERE cat_id='$cat_id' AND sub_cat_name='$sub_cat_name'")) {
			echo "A similar sub category name has already been used in this category"; return;
		}
		echo $db->Query("INSERT INTO tbl_item_drug_lab_sub_types (cat_id,sub_cat_name) VALUES ($cat_id,'$sub_cat_name')");
	}

	if (isset($_POST['UpdateSubCat'])) {
		$cat_id = mysqli_real_escape_string($conn,$_POST['cat_id']);
		$sub_cat_code = mysqli_real_escape_string($conn,$_POST['sub_cat_code']);
		$sub_cat_name = mysqli_real_escape_string($conn,$_POST['sub_cat_name']);

		if ($db->Exists("SELECT * FROM tbl_item_drug_lab_sub_types WHERE cat_id='$cat_id' AND sub_cat_name='$sub_cat_name'")) {
			echo "A similar sub category name has already been used in this category"; return;
		}

		echo $db->Query("UPDATE tbl_item_drug_lab_sub_types SET sub_cat_name='$sub_cat_name' WHERE id='$sub_cat_code'");
	}

//Stationary
	if (isset($_POST['GetStationary'])) {
		$page_size = (int)$_POST['page_size'];
		$pageStart = $page_size * ((int)$_POST['page']-1);
		$rows = $db->ReadArray("SELECT * FROM tbl_item WHERE item_type='Stationary' ORDER BY  item_name ASC LIMIT $pageStart,$page_size");
		$i=$pageStart;
		foreach ($rows as $Item) :
			$i++; 
			?>
  			<tr>
  				<td><?= $i ?></td>
      			<td><?= $Item['item_code']?></td>
      			<td><?= $Item['item_name']?></td>
      			<td><?= $Item['item_des']?></td>
      			<td><?= $Item['item_condition']?></td>
      			<td><?= $Item['service_point']?></td>   
      			<td>
      				<button class="btn btn-outline-success btn-sm" onclick="EditItem($(this).parents('tr'))">
      					<i class="oi oi-pencil"></i> Edit
      				</button>
      				<button class="btn btn-outline-danger btn-sm" onclick="DeleteItem('<?= $Item['item_code']?>')">
      					<i class="oi oi-trash"></i> Delete
      				</button> 
      			</td>	
  			</tr>
	<?php
		endforeach;   			
	}

	if (isset($_POST['SaveStationary'])) {
		$flow_date = date('d/m/Y H:i:s');
		$item_name = mysqli_real_escape_string($conn,$_POST['item_name']);
		$item_des = mysqli_real_escape_string($conn,$_POST['item_des']);
		$item_condition = mysqli_real_escape_string($conn,$_POST['item_condition']);
		$service_point = mysqli_real_escape_string($conn,$_POST['service_point']);

		$count = $db->CountRows("SELECT * from tbl_item WHERE item_des='$item_des' ");
		if ($count>0) {echo "The Item Tag entered already exists";return;}

		$qry = "INSERT INTO tbl_item (item_name, item_type,item_des, item_condition,service_point) VALUES ('$item_name', 'Stationary','$item_des','$item_condition','$service_point')";
		echo $db->Query($qry);
	}

	if (isset($_POST['UpdateStationary'])) {
		$flow_date = date('d/m/Y H:i:s');
		$item_code= mysqli_real_escape_string($conn,$_POST['item_code']); 
		$item_condition = mysqli_real_escape_string($conn,$_POST['item_condition']);
		$service_point = mysqli_real_escape_string($conn,$_POST['service_point']);

		$qry = "UPDATE tbl_item SET item_condition='$item_condition', service_point='$service_point' WHERE item_code='$item_code'";
		echo $db->Query($qry);
	}


//Get other Items
	if (isset($_POST['GetItems'])) {
		$res = $db->ReadAll("SELECT * FROM tbl_item WHERE item_type='Consumable' ORDER BY  item_name ASC");
		while ($Item = mysqli_fetch_assoc($res)) {
			?>
  			<tr>
      			<td><?= $Item['item_code']?></td>
      			<td><?= $Item['item_name']?></td>
      			<td><?= $Item['item_quantity']?></td>
      			<td><?= $Item['total_pieces']?></td>
      			<td><?= $Item['item_rate_cash']?></td>   
      			<td>
      				<button class="btn btn-outline-success btn-sm" onclick="EditItem('<?= $Item['item_code']?>')"><i class="oi oi-pencil"></i> Edit</button>
      				
      				<button class="btn btn-outline-danger btn-sm" onclick="DeleteItem('<?= $Item['item_code']?>')"><i class="oi oi-trash"></i> Delete</button> 
      			</td>	
  			</tr>
			<?php
		}    			
	}

	if (isset($_POST['GetIPDItems'])) {
		$res = $db->ReadAll("SELECT * FROM tbl_item WHERE item_type='IPD Item' ORDER BY  item_name ASC");
		while ($Item = mysqli_fetch_assoc($res)) {
			?>
  			<tr>
      			<td><?= $Item['item_code']?></td>
      			<td><?= $Item['item_name']?></td>
      			<td><?= $Item['item_quantity']?></td>
      			<td><?= $Item['total_pieces']?></td>
      			<td><?= $Item['item_rate_cash']?></td>   
      			<td>
      				<button class="btn btn-outline-success btn-sm" onclick="EditItem('<?= $Item['item_code']?>')"><i class="oi oi-pencil"></i> Edit</button>
      				<button class="btn btn-outline-danger btn-sm" onclick="DeleteItem('<?= $Item['item_code']?>')"><i class="oi oi-trash"></i> Delete</button>
      			</td>	
  			</tr>
			<?php
		}    			
	}

//Drugs
	if (isset($_POST['GetDrugs'])) {
		$page_size = (int)$_POST['page_size'];
		$pageStart = $page_size * ((int)$_POST['page']-1);
		$rows = $db->ReadArray("SELECT * FROM tbl_item WHERE item_type='Drug' ORDER BY  item_name ASC LIMIT $pageStart,$page_size");
		$i=$pageStart;
		foreach ($rows as $Item) :
			$i++;
			$cat = $db->ReadOne("SELECT * FROM tbl_item_drug_lab_types WHERE id = '$Item[item_category]'");
			$sub_cat = $db->ReadOne("SELECT * FROM tbl_item_drug_lab_sub_types WHERE id = '$Item[item_sub_category]'");
			?>
  			<tr>
  				<td><?= $i?></td>
      			<td><?= $Item['item_code']?></td>
      			<td><?= $Item['item_name']?></td>
      			<td><?= $Item['item_quantity']?></td>
      			<td><?= $Item['total_pieces']?></td>
      			<td><?= $Item['item_rate_cash']?></td>  
      			<td><?= $Item['item_rate_cop']?></td>
				<td><?= ($Item['cop_payment']=='')?'Cash Only':'Cash & Corporate'?></td> 
      			<td><?= $cat['cat_name']?></td> 
      			<td><?= $sub_cat['sub_cat_name']?></td> 
      			<td>
      				<button class="btn btn-outline-success btn-sm" onclick="EditItem('<?= $Item['item_code']?>')">
      					<i class="oi oi-pencil"></i> Edit
      				</button>
      				<button class="btn btn-outline-danger btn-sm" onclick="DeleteItem('<?= $Item['item_code']?>')">
      					<i class="oi oi-trash"></i> Delete
      				</button> 
      			</td>	
  			</tr>
	<?php endforeach;		
	}

	if (isset($_POST['GetItemProps'])) {
		$item_code= mysqli_real_escape_string($conn,$_POST['item_code']);
		$Item = $db->ReadOne("SELECT * FROM tbl_item WHERE item_code='$item_code'"); 
		$arr_item = array("item_code" => $Item['item_code'], "item_type" => $Item['item_type'], "item_name" => $Item['item_name'], "purchase_price" => $Item['purchase_price'], "item_quantity" => $Item['item_quantity'], "item_pieces_per_unit" => $Item['item_pieces_per_unit'], "selling_price" => $Item['selling_price'], "total_pieces" => $Item['total_pieces'], "item_rate_cash" => $Item['item_rate_cash'],"item_rate_cop" => $Item['item_rate_cop'],"nhif_rebate" => $Item['nhif_rebate'],"cop_payment" => $Item['cop_payment'], "item_supplier" => $Item['item_supplier'], "service_point" => $Item['service_point'], "item_des" => $Item['item_des'], "chargeable" => $Item['chargeable'], "item_condition" => $Item['item_condition'], "item_category" => $Item['item_category'], "item_sub_category" => $Item['item_sub_category']
		);
		echo json_encode($arr_item);
	}

	if (isset($_POST['GetSubcategories'])) {
		$cat_id= mysqli_real_escape_string($conn,$_POST['cat_id']); 
		$res = $db->ReadAll("SELECT * FROM tbl_item_drug_lab_sub_types WHERE cat_id = '$cat_id' ORDER BY sub_cat_name ASC");
		echo "<option value=''>Select</option>";
		while ($row = mysqli_fetch_assoc($res)) {
			echo "<option value='$row[id]'>$row[sub_cat_name]</option>";
		}
	}

	if (isset($_POST['CalculateprofitsMargins'])) {
		$selling_price = (int)$_POST['purchase_price'] * (((int)$_POST['per_profit'] + 100)/100);
		$item_rate_cash = $selling_price/(int)$_POST['piecesInUnit'];
			$result = array(
				"selling_price" => $selling_price,
				"item_rate_cash" => $item_rate_cash
			);
		echo json_encode($result);
	}

	if (isset($_POST['GetPages'])) {
		$item_type = $_POST['item_type'];
		$page_size = (int)$_POST['page_size'];
		echo ceil($db->CountRows("SELECT * FROM tbl_item WHERE item_type='$item_type'")/$page_size);
	}

	if (isset($_POST['SaveDrug'])) {
		$flow_date = date('d/m/Y H:i:s');
		$item_name= mysqli_real_escape_string($conn,$_POST['item_name']); 
		$item_type= mysqli_real_escape_string($conn,$_POST['item_type']); 
		$item_quantity= mysqli_real_escape_string($conn,$_POST['item_quantity']);
		$purchase_price= mysqli_real_escape_string($conn,$_POST['purchase_price']);
		$selling_price= mysqli_real_escape_string($conn,$_POST['selling_price']);
		$item_pieces_per_unit= mysqli_real_escape_string($conn,$_POST['item_pieces_per_unit']);
		$total_pieces = mysqli_real_escape_string($conn,$_POST['total_pieces']);
		$item_rate_cash= mysqli_real_escape_string($conn,$_POST['item_rate_cash']);		
		$item_rate_cop= mysqli_real_escape_string($conn,$_POST['item_rate_cop']); 
		$nhif_rebate= mysqli_real_escape_string($conn,$_POST['nhif_rebate']);
		$cop_payment=mysqli_real_escape_string($conn,$_POST['cop_payment']);
		$item_des= mysqli_real_escape_string($conn,$_POST['item_des']);
		$item_supplier = mysqli_real_escape_string($conn,$_POST['item_supplier']);
		$item_category= mysqli_real_escape_string($conn,$_POST['item_category']);
		$item_sub_category= mysqli_real_escape_string($conn,$_POST['item_sub_category']);
		
		$Supplier = $db->ReadOne("SELECT * FROM tbl_supplier WHERE supplier_code='$item_supplier'");

		if ($db->Exists("SELECT * from tbl_item WHERE item_name='$item_name' ")) {echo "The Item name entered already exists";return;}

		$qry = "INSERT INTO tbl_item (item_name, item_type, item_quantity, purchase_price, item_pieces_per_unit, selling_price, total_pieces, item_rate_cash,item_rate_cop, nhif_rebate, cop_payment, item_des, item_supplier,item_category,item_sub_category) VALUES ('$item_name', '$item_type','$item_quantity','$purchase_price','$item_pieces_per_unit','$selling_price','$total_pieces','$item_rate_cash','$item_rate_cop', '$nhif_rebate', '$cop_payment', '$item_des','$item_supplier','$item_category','$item_sub_category')";
		$db->Query($qry);
		$SavedItem = $db->ReadOne("SELECT * FROM tbl_item WHERE item_name='$item_name'");
		echo $db->Query("INSERT INTO tbl_item_flow (item_code, item_name, flow_date, flow_type, flow_quantity, cummulative_quantity, flow_persons) VALUES ('$SavedItem[item_code]', '$item_name', '$flow_date', 'receive', '$item_quantity', '$item_quantity', '$Supplier[supplier_name]')");
	}

	if (isset($_POST['UpdateDrug'])) {
		$item_code= mysqli_real_escape_string($conn,$_POST['item_code']); 
		$item_name= mysqli_real_escape_string($conn,$_POST['item_name']); 
		$purchase_price= mysqli_real_escape_string($conn,$_POST['purchase_price']);
		$selling_price= mysqli_real_escape_string($conn,$_POST['selling_price']);
		$item_rate_cash= mysqli_real_escape_string($conn,$_POST['item_rate_cash']);		
		$item_rate_cop= mysqli_real_escape_string($conn,$_POST['item_rate_cop']); 
		$nhif_rebate= mysqli_real_escape_string($conn,$_POST['nhif_rebate']);
		$cop_payment=mysqli_real_escape_string($conn,$_POST['cop_payment']);
		$item_des= mysqli_real_escape_string($conn,$_POST['item_des']);
		$item_supplier = mysqli_real_escape_string($conn,$_POST['item_supplier']);
		$item_category= mysqli_real_escape_string($conn,$_POST['item_category']);
		$item_sub_category= mysqli_real_escape_string($conn,$_POST['item_sub_category']);

		echo $db->Query("UPDATE tbl_item SET item_name='$item_name', purchase_price='$purchase_price',selling_price='$selling_price',item_rate_cash='$item_rate_cash',item_rate_cop='$item_rate_cop', nhif_rebate='$nhif_rebate',cop_payment='$cop_payment', item_des='$item_des', item_supplier = '$item_supplier', item_category = '$item_category', item_sub_category = '$item_sub_category' WHERE item_code='$item_code'");
	}

	if (isset($_POST['DeleteItem'])) {
		$item_code = $_POST['item_code'];
		echo $db->Query("DELETE From tbl_item WHERE item_code='$item_code' ");
	}

	//Item Dispatch
	if (isset($_POST['DispatchItem'])) {
		sleep(0);
		$flow_date = date('d/m/Y H:i:s');
		$item_code = mysqli_real_escape_string($conn,$_POST['item_code']);
		$item_name = mysqli_real_escape_string($conn,$_POST['item_name']);
		$dispatch_quantity = mysqli_real_escape_string($conn,$_POST['dispatch_quantity']);
		$receiving_officer = mysqli_real_escape_string($conn,$_POST['receiving_officer']);

		$Item = $db->ReadOne("SELECT * FROM tbl_item WHERE item_code = '$item_code' ");

		$quantity = $Item['item_quantity'];
		$cummulative_quantity = $quantity - $dispatch_quantity;

		if ($dispatch_quantity>$quantity) {
			echo "You cannot dispatch more Items than those in the database record";
			return;
		}

		$db->Query("UPDATE tbl_item SET item_quantity=(item_quantity-'$dispatch_quantity'), total_pieces=(total_pieces-('$dispatch_quantity'*item_pieces_per_unit)) WHERE item_code='$item_code'");
		echo $db->Query("INSERT INTO tbl_item_flow (item_code, item_name, flow_date, flow_type, flow_quantity, cummulative_quantity, flow_persons) VALUES ('$item_code', '$item_name', '$flow_date', 'dispatch', '$dispatch_quantity', '$cummulative_quantity', '$receiving_officer')");
	}

//SERVICE
	if (isset($_POST['GetServices'])) {
		$item_type = mysqli_real_escape_string($conn,$_POST['item_type']);
		$res = $db->ReadAll("SELECT * FROM tbl_item WHERE item_type='$item_type' ORDER BY  item_code ASC, item_name ASC");
		while ($row = mysqli_fetch_assoc($res)) {
			?>
  			<tr>
      			<td><?= $row['item_code']?></td>
      			<td><?= $row['item_name']?></td>
      			<td><?= $row['item_rate_cash']?></td>     
      			<td><?= $row['item_des']?></td>
      			<td><?= $row['item_category']?></td>
      			<td>
      				<button class="btn btn-outline-success btn-sm" onclick="EditItem('<?= $row['item_code']?>')">
      					<i class="oi oi-pencil"></i> Edit
      				</button>
      				<button class="btn btn-outline-danger btn-sm" onclick="DeleteItem('<?= $row['item_code']?>')">
      					<i class="oi oi-trash"></i> Delete
      				</button>
      			</td>	
  			</tr>
			<?php
		}    			
	}


/*LABORATORY SERVICES*/
	if (isset($_POST['GetLaboratoryServices'])) {
		$page_size = (int)$_POST['page_size'];
		$pageStart = $page_size * ((int)$_POST['page']-1);
		$rows = $db->ReadArray("SELECT * FROM tbl_item WHERE item_type='Laboratory Service' ORDER BY  item_name ASC LIMIT $pageStart,$page_size");
		$i=$pageStart;
		foreach ($rows as $row) :
			$i++;
			$cat = $db->ReadOne("SELECT * FROM tbl_item_drug_lab_types WHERE id = '$row[item_category]'");
			?>
  			<tr>
  				<td><?= $i?></td>
      			<td><?= $row['item_code']?></td>
      			<td><?= $row['item_name']?></td>
      			<td><?= $row['item_rate_cash']?></td>   
      			<td><?= $row['item_rate_cop']?></td>         			
				<td><?= ($row['cop_payment']=='')?'Cash Only':'Cash & Corporate'?></td> 
      			<td><?= $row['item_des']?></td>
      			<td><?= $cat['cat_name']?></td>
      			<td><?= $row['purchase_price']?></td>
      			<td><?= $row['selling_price']?></td>
      			<td>
      				<button class="btn btn-outline-success btn-sm" onclick="EditItem('<?= $row['item_code']?>')">
      					<i class="oi oi-pencil"></i> Edit
      				</button>
      				<button class="btn btn-outline-danger btn-sm" onclick="DeleteItem('<?= $row['item_code']?>')">
      					<i class="oi oi-trash"></i> Delete
      				</button>
      			</td>	
  			</tr>
	<?php endforeach;   			
	}

	if (isset($_POST['SaveLaboratoryService'])) {
		$item_name= mysqli_real_escape_string($conn,$_POST['item_name']); 
		$item_type= mysqli_real_escape_string($conn,$_POST['item_type']); 
		$item_rate_cash= mysqli_real_escape_string($conn,$_POST['item_rate_cash']);
		$item_rate_cop= mysqli_real_escape_string($conn,$_POST['item_rate_cop']); 
		$nhif_rebate= mysqli_real_escape_string($conn,$_POST['nhif_rebate']);
		$cop_payment= mysqli_real_escape_string($conn,$_POST['cop_payment']);
		$item_des= mysqli_real_escape_string($conn,$_POST['item_des']);
		$item_category= mysqli_real_escape_string($conn,$_POST['item_category']);
		$low_range= mysqli_real_escape_string($conn,$_POST['low_range']);
		$up_range= mysqli_real_escape_string($conn,$_POST['up_range']);
		
		$count = $db->CountRows("SELECT * from tbl_item WHERE item_name='$item_name'");
		if ($count>0) {echo "The Item name entered already exists";return;}
		echo $db->Query("INSERT INTO tbl_item (item_type,item_name, item_rate_cash, item_rate_cop, nhif_rebate, cop_payment, item_des, item_category, purchase_price, selling_price) VALUES ('$item_type','$item_name', '$item_rate_cash','$item_rate_cop', '$nhif_rebate', '$cop_payment', '$item_des','$item_category','$low_range','$up_range')");
	}

	if (isset($_POST['UpdateLaboratoryService'])) {
		$item_code = mysqli_real_escape_string($conn,$_POST['item_code']); 
		$item_name= mysqli_real_escape_string($conn,$_POST['item_name']); 
		$item_rate_cash= mysqli_real_escape_string($conn,$_POST['item_rate_cash']);
		$item_rate_cop= mysqli_real_escape_string($conn,$_POST['item_rate_cop']); 
		$nhif_rebate= mysqli_real_escape_string($conn,$_POST['nhif_rebate']);
		$cop_payment= mysqli_real_escape_string($conn,$_POST['cop_payment']);
		$item_des= mysqli_real_escape_string($conn,$_POST['item_des']);
		$item_category= mysqli_real_escape_string($conn,$_POST['item_category']);
		$low_range= mysqli_real_escape_string($conn,$_POST['low_range']);
		$up_range= mysqli_real_escape_string($conn,$_POST['up_range']);
		
		echo $db->Query("UPDATE tbl_item SET item_name = '$item_name', item_rate_cash = '$item_rate_cash',item_rate_cop='$item_rate_cop', nhif_rebate='$nhif_rebate',cop_payment='$cop_payment', item_des = '$item_des',item_category='$item_category',purchase_price='$low_range',selling_price='$up_range' WHERE item_code='$item_code'");
	}

//Consumables
	if (isset($_POST['GetRadiologyServices'])) {
		$page_size = (int)$_POST['page_size'];
		$pageStart = $page_size * ((int)$_POST['page']-1);
		$rows = $db->ReadArray("SELECT * FROM tbl_item WHERE item_type='Radiology Service' ORDER BY  item_name ASC LIMIT $pageStart,$page_size");
		$i=$pageStart;
		foreach ($rows as $row) :
			$i++;
			$cat = $db->ReadOne("SELECT * FROM tbl_item_drug_lab_types WHERE id = '$row[item_category]'");
			?>
  			<tr>
  				<td><?= $i?></td>
      			<td><?= $row['item_code']?></td>
      			<td><?= $row['item_name']?></td>
      			<td><?= $row['item_rate_cash']?></td>  
      			<td><?= $row['item_rate_cop']?></td>         			
				<td><?= ($row['cop_payment']=='')?'Cash Only':'Cash & Corporate'?></td> 
      			<td><?= $cat['cat_name']?></td>
      			<td>
      				<button class="btn btn-outline-success btn-sm" onclick="EditItem('<?= $row['item_code']?>')">
      					<i class="oi oi-pencil"></i> Edit
      				</button>
      				<button class="btn btn-outline-danger btn-sm" onclick="DeleteItem('<?= $row['item_code']?>')">
      					<i class="oi oi-trash"></i> Delete
      				</button>
      			</td>	
  			</tr>
<?php endforeach;  			
	}

	if (isset($_POST['SaveRadiologyService'])) {
		$item_name= mysqli_real_escape_string($conn,$_POST['item_name']); 
		$item_type= mysqli_real_escape_string($conn,$_POST['item_type']); 
		$item_rate_cash= mysqli_real_escape_string($conn,$_POST['item_rate_cash']);
		$item_rate_cop= mysqli_real_escape_string($conn,$_POST['item_rate_cop']); 
		$nhif_rebate= mysqli_real_escape_string($conn,$_POST['nhif_rebate']);
		$cop_payment= mysqli_real_escape_string($conn,$_POST['cop_payment']);
		$item_category= mysqli_real_escape_string($conn,$_POST['item_category']);
		
		if ($db->Exists("SELECT * from tbl_item WHERE item_name='$item_name'")){echo "The Item name entered already exists";return;}
		echo $db->Query("INSERT INTO tbl_item (item_type,item_name, item_rate_cash, item_rate_cop, nhif_rebate, cop_payment, item_category) VALUES ('$item_type','$item_name', '$item_rate_cash','$item_rate_cop', '$nhif_rebate', '$cop_payment', '$item_category')");
	}

	if (isset($_POST['UpdateRadiologyService'])) {
		$item_code = mysqli_real_escape_string($conn,$_POST['item_code']); 
		$item_name= mysqli_real_escape_string($conn,$_POST['item_name']); 
		$item_rate_cash= mysqli_real_escape_string($conn,$_POST['item_rate_cash']);
		$item_rate_cop= mysqli_real_escape_string($conn,$_POST['item_rate_cop']); 
		$nhif_rebate= mysqli_real_escape_string($conn,$_POST['nhif_rebate']);
		$cop_payment= mysqli_real_escape_string($conn,$_POST['cop_payment']);
		$item_category= mysqli_real_escape_string($conn,$_POST['item_category']);
		
		echo $db->Query("UPDATE tbl_item SET item_name = '$item_name', item_rate_cash = '$item_rate_cash',item_rate_cop = '$item_rate_cop', nhif_rebate='$nhif_rebate', cop_payment='$cop_payment', item_category='$item_category' WHERE item_code='$item_code'");
	}


/*General Services*/
	if (isset($_POST['GetGeneralServices'])) {
		$page_size = (int)$_POST['page_size'];
		$pageStart = $page_size * ((int)$_POST['page']-1);
		$rows = $db->ReadArray("SELECT * FROM tbl_item WHERE item_type='General Service' ORDER BY  item_name ASC LIMIT $pageStart,$page_size");
		$i=$pageStart;
		foreach ($rows as $row) :
			$i++;
			?>
  			<tr>
  				<td><?= $i?></td>
      			<td><?= $row['item_code']?></td>
      			<td><?= $row['item_name']?></td>
      			<td><?= $row['item_rate_cash']?></td> 
      			<td><?= $row['item_rate_cop']?></td>
      			<td><?= ($row['cop_payment']=='')?'Cash Only':'Cash & Corporate'?></td> 
      			<td>
      				<button class="btn btn-outline-success btn-sm" onclick="EditItem('<?= $row['item_code']?>')">
      					<i class="oi oi-pencil"></i> Edit
      				</button>
      				<button class="btn btn-outline-danger btn-sm" onclick="DeleteItem('<?= $row['item_code']?>')">
      					<i class="oi oi-trash"></i> Delete
      				</button>
      			</td>	
  			</tr>
	<?php
		endforeach;    			
	}

	if (isset($_POST['SaveGeneralService'])) {
		$item_name= mysqli_real_escape_string($conn,$_POST['item_name']); 
		$item_type= mysqli_real_escape_string($conn,$_POST['item_type']); 
		$item_rate_cash= mysqli_real_escape_string($conn,$_POST['item_rate_cash']);
		$item_rate_cop= mysqli_real_escape_string($conn,$_POST['item_rate_cop']); 
		$nhif_rebate= mysqli_real_escape_string($conn,$_POST['nhif_rebate']);
		$cop_payment= mysqli_real_escape_string($conn,$_POST['cop_payment']);

		if ($db->Exists("SELECT * from tbl_item WHERE item_name='$item_name'")) {echo "The Item name entered already exists";return;}
		echo $db->Query("INSERT INTO tbl_item (item_type,item_name, item_rate_cash,item_rate_cop,nhif_rebate,cop_payment) VALUES ('$item_type','$item_name', '$item_rate_cash','$item_rate_cop','$nhif_rebate','$cop_payment')");
	}

	if (isset($_POST['UpdateGeneralService'])) {
		$item_code = mysqli_real_escape_string($conn,$_POST['item_code']); 
		$item_name= mysqli_real_escape_string($conn,$_POST['item_name']); 
		$item_rate_cash= mysqli_real_escape_string($conn,$_POST['item_rate_cash']);
		$item_rate_cop= mysqli_real_escape_string($conn,$_POST['item_rate_cop']); 
		$nhif_rebate= mysqli_real_escape_string($conn,$_POST['nhif_rebate']);
		$cop_payment= mysqli_real_escape_string($conn,$_POST['cop_payment']);
		
		echo $db->Query("UPDATE tbl_item SET item_name = '$item_name', item_rate_cash = '$item_rate_cash', item_rate_cop = '$item_rate_cop', nhif_rebate='$nhif_rebate', cop_payment = '$cop_payment'  WHERE item_code='$item_code'");
	}

//Medical Procedures
	if (isset($_POST['GetMedicalProcedures'])) {
		$page_size = (int)$_POST['page_size'];
		$pageStart = $page_size * ((int)$_POST['page']-1);
		$rows = $db->ReadArray("SELECT * FROM tbl_item WHERE item_type='Medical Procedure' ORDER BY  item_name ASC LIMIT $pageStart,$page_size");
		$i=$pageStart;
		foreach ($rows as $row) :
			$i++;
			$cat = $db->ReadOne("SELECT * FROM tbl_item_drug_lab_types WHERE id='$row[item_category]'");
			$sub_cat = $db->ReadOne("SELECT * FROM tbl_item_drug_lab_sub_types WHERE id='$row[item_sub_category]'");
			?>
  			<tr>
  				<td><?= $i?></td>
      			<td><?= $row['item_code']?></td>
      			<td><?= $row['item_name']?></td>
      			<td><?= $row['item_rate_cash']?></td> 
      			<td><?= $row['item_rate_cop']?></td>
				<td><?= ($row['cop_payment']=='')?'Cash Only':'Cash & Corporate'?></td> 
      			<td><?= $cat['cat_name']?></td>
      			<td><?= $sub_cat['sub_cat_name']?></td>       
      			<td><?= $row['item_des']?></td>
      			<td>
      				<button class="btn btn-outline-success btn-sm" onclick="EditItem('<?= $row['item_code']?>')">
      					<i class="oi oi-pencil"></i> Edit
      				</button>
      				<button class="btn btn-outline-danger btn-sm" onclick="DeleteItem('<?= $row['item_code']?>')">
      					<i class="oi oi-trash"></i> Delete
      				</button>
      			</td>	
  			</tr>
	<?php 
		endforeach;  			
	}

	if (isset($_POST['SaveProcedure'])) {
		$item_name= mysqli_real_escape_string($conn,$_POST['item_name']); 
		$item_type= mysqli_real_escape_string($conn,$_POST['item_type']); 
		$item_rate_cash= mysqli_real_escape_string($conn,$_POST['item_rate_cash']);
		$item_rate_cop= mysqli_real_escape_string($conn,$_POST['item_rate_cop']); 
		$nhif_rebate= mysqli_real_escape_string($conn,$_POST['nhif_rebate']);
		$cop_payment= mysqli_real_escape_string($conn,$_POST['cop_payment']);
		$item_des= mysqli_real_escape_string($conn,$_POST['item_des']);
		$item_category= mysqli_real_escape_string($conn,$_POST['item_category']);
		$item_sub_category= mysqli_real_escape_string($conn,$_POST['item_sub_category']);
		
		if ($db->Exists("SELECT * from tbl_item WHERE item_name='$item_name'")) {echo "The Item name entered already exists";return;}

		echo $db->Query("INSERT INTO tbl_item (item_type,item_name, item_rate_cash, item_rate_cop, nhif_rebate, cop_payment, item_des,item_category,item_sub_category) VALUES ('$item_type','$item_name', '$item_rate_cash',  '$item_rate_cop', '$nhif_rebate', '$cop_payment', '$item_des','$item_category','$item_sub_category')");
	}

	if (isset($_POST['UpdateProcedure'])) {
		$item_code = mysqli_real_escape_string($conn,$_POST['item_code']); 
		$item_name= mysqli_real_escape_string($conn,$_POST['item_name']); 
		$item_rate_cash= mysqli_real_escape_string($conn,$_POST['item_rate_cash']);	
		$item_rate_cop= mysqli_real_escape_string($conn,$_POST['item_rate_cop']); 
		$nhif_rebate= mysqli_real_escape_string($conn,$_POST['nhif_rebate']);
		$cop_payment= mysqli_real_escape_string($conn,$_POST['cop_payment']);	
		$item_des= mysqli_real_escape_string($conn,$_POST['item_des']);
		$item_category= mysqli_real_escape_string($conn,$_POST['item_category']);
		$item_sub_category= mysqli_real_escape_string($conn,$_POST['item_sub_category']);
		
		echo $db->Query("UPDATE tbl_item SET item_name = '$item_name', item_rate_cash = '$item_rate_cash', item_rate_cop = '$item_rate_cop', nhif_rebate='$nhif_rebate', cop_payment='$cop_payment', item_des = '$item_des',item_category='$item_category',item_sub_category='$item_sub_category' WHERE item_code='$item_code'");
	}

//Consumables
	if (isset($_POST['GetConsumables'])) {
		$page_size = (int)$_POST['page_size'];
		$pageStart = $page_size * ((int)$_POST['page']-1);
		$rows = $db->ReadArray("SELECT * FROM tbl_item WHERE item_type='Consumable' ORDER BY  item_name ASC LIMIT $pageStart,$page_size");
		$i=$pageStart;
		foreach ($rows as $Item):
			$i++;
			?>
  			<tr>
  				<td><?= $i?></td>
      			<td><?= $Item['item_code']?></td>
      			<td><?= $Item['item_name']?></td>
      			<td><?= $Item['item_quantity']?></td>
      			<td><?= $Item['total_pieces']?></td>
      			<td><?= $Item['item_rate_cash']?></td>  
      			<td><?= $Item['item_rate_cop']?></td>
      			<td><?= ($Item['cop_payment']=='')?'Cash Only':'Cash & Corporate'?></td>    
      			<td>
      				<button class="btn btn-outline-success btn-sm" onclick="EditItem('<?= $Item['item_code']?>')">
      					<i class="oi oi-pencil"></i> Edit
      				</button>
      				<button onclick="DispatchItemPopUp($(this).parents('tr'))" class="btn btn-outline-primary btn-sm"><i class="oi oi-share-boxed"></i> Dispatch</button>
      				<button class="btn btn-outline-danger btn-sm" onclick="DeleteItem('<?= $Item['item_code']?>')">
      					<i class="oi oi-trash"></i> Delete
      				</button> 
      			</td>	
  			</tr>
	<?php endforeach;    			
	}

	if (isset($_POST['SaveConsumable'])) {
		$flow_date = date('d/m/Y H:i:s');
		$item_name= mysqli_real_escape_string($conn,$_POST['item_name']); 
		$item_type= mysqli_real_escape_string($conn,$_POST['item_type']); 
		$item_quantity= mysqli_real_escape_string($conn,$_POST['item_quantity']);
		$purchase_price= mysqli_real_escape_string($conn,$_POST['purchase_price']);
		$selling_price= mysqli_real_escape_string($conn,$_POST['selling_price']);
		$item_des= mysqli_real_escape_string($conn,$_POST['item_des']);
		$item_pieces_per_unit= mysqli_real_escape_string($conn,$_POST['item_pieces_per_unit']);
		$total_pieces = mysqli_real_escape_string($conn,$_POST['total_pieces']);
		$item_rate_cash= mysqli_real_escape_string($conn,$_POST['item_rate_cash']);			
		$item_rate_cop= mysqli_real_escape_string($conn,$_POST['item_rate_cop']); 
		$nhif_rebate= mysqli_real_escape_string($conn,$_POST['nhif_rebate']);
		$cop_payment= mysqli_real_escape_string($conn,$_POST['cop_payment']);		
		$chargeable= mysqli_real_escape_string($conn,$_POST['chargeable']);
		$item_supplier = mysqli_real_escape_string($conn,$_POST['item_supplier']);
		
		$Supplier = $db->ReadOne("SELECT * FROM tbl_supplier WHERE supplier_code='$item_supplier'");

		if ($db->Exists("SELECT * from tbl_item WHERE item_name='$item_name' ")) {echo "The Item name entered already exists";return;}

		$qry = "INSERT INTO tbl_item (item_name, item_type, item_quantity, purchase_price, item_pieces_per_unit, selling_price,item_des, total_pieces, item_rate_cash,item_rate_cop, nhif_rebate, cop_payment, chargeable,item_supplier) VALUES ('$item_name', '$item_type','$item_quantity','$purchase_price','$item_pieces_per_unit','$selling_price','$item_des','$total_pieces','$item_rate_cash','$item_rate_cop','$nhif_rebate','$cop_payment', '$chargeable','$item_supplier')";
		$db->Query($qry);
		$SavedItem = $db->ReadOne("SELECT * FROM tbl_item WHERE item_name='$item_name'");
		echo $db->Query("INSERT INTO tbl_item_flow (item_code, item_name, flow_date, flow_type, flow_quantity, cummulative_quantity, flow_persons) VALUES ('$SavedItem[item_code]', '$item_name', '$flow_date', 'receive', '$item_quantity', '$item_quantity', '$Supplier[supplier_name]')");
	}

	if (isset($_POST['UpdateConsumable'])) {
		$item_code= mysqli_real_escape_string($conn,$_POST['item_code']); 
		$item_name= mysqli_real_escape_string($conn,$_POST['item_name']); 
		$purchase_price= mysqli_real_escape_string($conn,$_POST['purchase_price']);
		$selling_price= mysqli_real_escape_string($conn,$_POST['selling_price']);
		$item_des= mysqli_real_escape_string($conn,$_POST['item_des']);
		$item_rate_cash= mysqli_real_escape_string($conn,$_POST['item_rate_cash']);		
		$item_rate_cop= mysqli_real_escape_string($conn,$_POST['item_rate_cop']); 
		$nhif_rebate= mysqli_real_escape_string($conn,$_POST['nhif_rebate']);
		$cop_payment= mysqli_real_escape_string($conn,$_POST['cop_payment']);	
		$chargeable= mysqli_real_escape_string($conn,$_POST['chargeable']);
		$item_supplier = mysqli_real_escape_string($conn,$_POST['item_supplier']);

		echo $db->Query("UPDATE tbl_item SET item_name='$item_name', purchase_price='$purchase_price',selling_price='$selling_price',item_des='$item_des', item_rate_cash='$item_rate_cash',item_rate_cop='$item_rate_cop', nhif_rebate='$nhif_rebate',cop_payment='$cop_payment', chargeable='$chargeable', item_supplier = '$item_supplier'  WHERE item_code='$item_code'");
	}

//Static Service
	if (isset($_POST['SaveStaticServices'])) {
		$opd_doc_cash = mysqli_real_escape_string($conn,$_POST['opd_doc_cash']);
		$opd_doc_cop = mysqli_real_escape_string($conn,$_POST['opd_doc_cop']);  
		$ipd_doc_cash = mysqli_real_escape_string($conn,$_POST['ipd_doc_cash']); 
		$ipd_doc_cop = mysqli_real_escape_string($conn,$_POST['ipd_doc_cop']);
		$ipd_nhif_rebate =  mysqli_real_escape_string($conn,$_POST['ipd_nhif_rebate']);

		if ($db->Exists("SELECT * FROM tbl_static_services")) {
			echo $db->Query("UPDATE tbl_static_services SET opd_doc_cash='$opd_doc_cash', opd_doc_cop='$opd_doc_cop', ipd_doc_cash='$ipd_doc_cash',ipd_doc_cop='$ipd_doc_cop',ipd_nhif_rebate='$ipd_nhif_rebate'");
		}else{
			echo $db->Query("INSERT INTO tbl_static_services(opd_doc_cash,opd_doc_cop,ipd_doc_cash,ipd_doc_cop,ipd_nhif_rebate)VALUES('$opd_doc_cash',$opd_doc_cop','$ipd_doc_cash','$ipd_doc_cop','$ipd_nhif_rebate')");
		}
	}

//WARDS
	if (isset($_POST['GetWards'])) {
		$rows = $db->ReadArray("SELECT * FROM tbl_ipd_wards");
		$i=0;
		foreach($rows as $Ward): $i++; ?>
			<tr>
				<td><?= $i."."?></td>
				<td><?= $Ward['ward_name']?></td>
				<td><?= $Ward['ward_admin_cash']?></td>	
				<td><?= $Ward['ward_admin_cop']?></td>				
				<td><?= $Ward['ward_rate_cash']?></td>			
				<td><?= $Ward['ward_rate_cop']?></td>
				<td><?= ($Ward['cop_payment']=='')?'Cash Only':'Cash & Corporate'?></td>
				<td><?= $Ward['bed_capacity']?></td>
				<td><?= $Ward['ward_capacity']?></td>
				<td>
					<button onclick="EditWard('<?= $Ward['ward_id']?>')" class="btn btn-sm btn-outline-success"> <i class="oi oi-pencil"></i> Edit</button>
					<a href="Beds.php?ward=<?= $Ward['ward_id']?>" class="btn btn-sm btn-outline-primary"><i class="oi oi-plus"></i> Beds</a>
					<button onclick="DeleteWard('<?= $Ward['ward_id']?>')" class="btn btn-sm btn-outline-danger"> <i class="oi oi-trash"></i> Delete</button>
				</td>
			</tr>
		<?php
	endforeach;
	}

	if (isset($_POST['GetWardProps'])) {
		$ward_id=mysqli_real_escape_string($conn,$_POST['ward_id']);
		$row = $db->ReadOne("SELECT * FROM tbl_ipd_wards WHERE ward_id='$ward_id'");
		$res = array(
			"ward_id" => $row['ward_id'], "ward_name" => $row['ward_name'], "ward_admin_cash" => $row['ward_admin_cash'], "ward_admin_cop" => $row['ward_admin_cop'], "ward_rate_cash" => $row['ward_rate_cash'], "ward_rate_cop" => $row['ward_rate_cop'], "cop_payment" => $row['cop_payment'], "bed_capacity" => $row['bed_capacity'], "ward_capacity" => $row['ward_capacity']
		);
		echo json_encode($res);
	}

	if (isset($_POST['SaveWard'])) {
		$ward_name=mysqli_real_escape_string($conn,$_POST['ward_name']);
		$ward_admin_cash = mysqli_real_escape_string($conn,$_POST['ward_admin_cash']);
		$ward_admin_cop = mysqli_real_escape_string($conn,$_POST['ward_admin_cop']);
		$ward_rate_cash=mysqli_real_escape_string($conn,$_POST['ward_rate_cash']);
		$ward_rate_cop=mysqli_real_escape_string($conn,$_POST['ward_rate_cop']);
		$cop_payment=mysqli_real_escape_string($conn,$_POST['cop_payment']);
		$bed_capacity=mysqli_real_escape_string($conn,$_POST['bed_capacity']);
		echo $db->Query("INSERT INTO tbl_ipd_wards (ward_name, ward_admin_cash, ward_admin_cop, ward_rate_cash, ward_rate_cop,cop_payment, bed_capacity) VALUES ('$ward_name', '$ward_admin_cash', '$ward_admin_cop', '$ward_rate_cash', '$ward_rate_cop','$cop_payment','$bed_capacity')");
	}

	if (isset($_POST['UpdateWard'])) {
		$ward_id=mysqli_real_escape_string($conn,$_POST['ward_id']);
		$ward_name=mysqli_real_escape_string($conn,$_POST['ward_name']);
		$ward_admin_cash = mysqli_real_escape_string($conn,$_POST['ward_admin_cash']);
		$ward_admin_cop = mysqli_real_escape_string($conn,$_POST['ward_admin_cop']);
		$ward_rate_cash=mysqli_real_escape_string($conn,$_POST['ward_rate_cash']);
		$ward_rate_cop=mysqli_real_escape_string($conn,$_POST['ward_rate_cop']);
		$cop_payment=mysqli_real_escape_string($conn,$_POST['cop_payment']);
		$bed_capacity=mysqli_real_escape_string($conn,$_POST['bed_capacity']);

		echo $db->Query("UPDATE tbl_ipd_wards SET ward_name= '$ward_name', ward_admin_cash= '$ward_admin_cash', ward_admin_cop= '$ward_admin_cop', ward_rate_cash= '$ward_rate_cash', ward_rate_cop= '$ward_rate_cop',cop_payment='$cop_payment', bed_capacity = '$bed_capacity' WHERE ward_id= '$ward_id'");
	}

	if (isset($_POST['DeleteWard'])) {
		$ward_id = mysqli_real_escape_string($conn,$_POST['ward_id']);
		echo $db->Query("DELETE from tbl_ipd_wards Where ward_id='$ward_id'");
	}

	//Beds
	if (isset($_POST['GetBeds'])) {
		$ward_id = mysqli_real_escape_string($conn,$_POST['ward_id']);
		$result = $db->ReadAll("SELECT * FROM tbl_ipd_beds WHERE ward_id='$ward_id'");
		while ($Bed = mysqli_fetch_assoc($result)) {
			$Ward = $db->ReadOne("SELECT * FROM tbl_ipd_wards WHERE ward_id='$Bed[ward_id]'");
			?>
			<tr>
				<td><?= $Bed['bed_id']?></td>
				<td><?= $Bed['bed_number']?></td>
				<td><?= $Ward['ward_name']?></td>				
				<td><?= $Bed['bed_status']?></td>
				<td>
					<button onclick="DeleteBed('<?= $Bed['bed_id']?>')" class="btn btn-sm btn-outline-danger"> <i class="oi oi-trash"></i> Delete</button>
				</td>
			</tr>
			<?php
		}
	}

	if (isset($_POST['SaveBed'])) {
		$bed_number=mysqli_real_escape_string($conn,$_POST['bed_number']);
		$ward_id = mysqli_real_escape_string($conn,$_POST['ward_id']);
		echo $db->Query("INSERT INTO tbl_ipd_beds (bed_number,ward_id) VALUES ('$bed_number','$ward_id')");
	}

	if (isset($_POST['DeleteBed'])) {
		$bed_id = mysqli_real_escape_string($conn,$_POST['bed_id']);
		echo $db->Query("DELETE from tbl_ipd_beds Where bed_id='$bed_id'");
	}

//MORGUE
	if (isset($_POST['GetMorgues'])) {
		$result = $db->ReadAll("SELECT * FROM tbl_morgues");
		while ($Morgue = mysqli_fetch_assoc($result)) {
			?>
			<tr>
				<td><?= $Morgue['morgue_id']?></td>
				<td><?= $Morgue['morgue_name']?></td>
				<td><?= $Morgue['morgue_admission_fee']?></td>				
				<td><?= $Morgue['morgue_daily_fee']?></td>
				<td><?= $Morgue['morgue_des']?></td>
				<td><?= $Morgue['morgue_capacity']?></td>
				<td>
					<button onclick="DeleteMorgue('<?= $Morgue['morgue_id']?>')" class="btn btn-sm btn-outline-danger"> <i class="oi oi-trash"></i> Delete</button>
				</td>
			</tr>
			<?php
		}
	}

	if (isset($_POST['SaveMorgue'])) {
		$morgue_name=mysqli_real_escape_string($conn,$_POST['morgue_name']);
		$morgue_admission_fee = mysqli_real_escape_string($conn,$_POST['morgue_admission_fee']);
		$morgue_daily_fee=mysqli_real_escape_string($conn,$_POST['morgue_daily_fee']);
		$morgue_des=mysqli_real_escape_string($conn,$_POST['morgue_des']);

		$count = $db->CountRows("SELECT * FROM tbl_morgues WHERE morgue_name='$morgue_name'");
		if ($count>0) {
			echo "The morgue you are trying to register already exist";
		}else{
			echo $db->Query("INSERT INTO tbl_morgues (morgue_name,morgue_admission_fee,morgue_daily_fee,morgue_des) VALUES ('$morgue_name','$morgue_admission_fee','$morgue_daily_fee','$morgue_des')");
		}
	}

	if (isset($_POST['DeleteMorgue'])) {
		$morgue_id = mysqli_real_escape_string($conn,$_POST['morgue_id']);
		echo $db->Query("DELETE from tbl_morgues Where morgue_id='$morgue_id'");
	}
?>
