<?php
include('../ConnectionClass.php');
include('../db_class.php');
session_start();


$db = new CRUD();
?>

<div id="wards">
	<?php
	  $Wards = $db->ReadAll("SELECT * FROM tbl_ipd_wards ORDER BY ward_name ASC");
	  while ($Ward = mysqli_fetch_assoc($Wards)) {
	   ?>
	    <a  class="ward" href="WardPage.php?ward_id=<?= $Ward['ward_id']?>" >
	      <p><?= $Ward['ward_name']?></p>
	      <div class="bar">
	        <div id="<?= $Ward['ward_id']?>" class="bar_length"><?= $Ward['ward_capacity']."/".$Ward['bed_capacity']?></div>
	      </div>
	    </a>
	    <script>
	      createBar("<?= $Ward['ward_id']?>",<?= $Ward['ward_capacity']?>,<?= $Ward['bed_capacity']?>);
	      function createBar(id,t,o) {
	        var per = parseInt((parseInt(t) / parseInt(o)) * 100);
	        $('#'+id).css("width",per+"%");
	      }
	    </script>
	   <?php
	  }
	?>
</div>

<div id="beds">
	<?php
		$ward_id = isset($_GET['ward_id'])?$_GET['ward_id']:"";
	?>
	  <div class="ward_area col-12">
        <p style="border-bottom: 1px solid blue;">Occupied Beds</p>
        <?php
          $Beds = $db->ReadAll("SELECT * FROM tbl_ipd_beds WHERE ward_id='$ward_id' AND bed_status != 'Empty' ORDER BY bed_number ASC");
          while ($Bed = mysqli_fetch_assoc($Beds)) {
            $refno = $Bed["bed_status"];
            $IPD_Patient = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE refno='$refno' AND status='Active'");
            $Patient = $db->ReadOne("SELECT * FROM tbl_patient WHERE refno='$refno'");
            $patient_name = $Patient['fullname'];
              ?>
              <div class="bed occupied_bed">
                <span><?=$patient_name?></span><br>
                <span><?= $Bed['bed_number']?></span>
                <button onclick="window.location.href='Patient Page.php?adm_no=<?= $IPD_Patient['adm_no']?>'">Open File</button>
              </div>
              <?php
          }
        ?>
      </div>
      <div class="ward_area col-12">
        <p style="border-bottom: 1px solid blue;">Empty Beds</p>
        <?php
          $Beds = $db->ReadAll("SELECT * FROM tbl_ipd_beds WHERE ward_id='$ward_id' AND bed_status = 'Empty' ORDER BY bed_number ASC");
          while ($Bed = mysqli_fetch_assoc($Beds)) {
            $bed_no = $Bed['bed_number'];
            $opd_no = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE adm_no='$Bed[bed_status]'")['refno'];
            $Patient = $db->ReadOne("SELECT * FROM tbl_ipd_admission WHERE refno='$opd_no'");
              ?>
              <div class="bed empty_bed">
                <span><?= $Bed['bed_number']?></span>
                <button onclick="FindPatient('<?= $bed_no?>')">Admit Patient</button>
              </div>
              <?php
          }
        ?>
      </div>
</div>