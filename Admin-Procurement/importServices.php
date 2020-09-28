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
if (!($User_level=='admin' || $GroupPrivileges['procurement_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
}
if (isset($_GET['Item-type'])) {
  $item_type = $_GET['Item-type'];
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
  <style type="text/css">
  	sup{
  		color: #f00;
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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Procurement, Service & Stationary Management</a>
		</div>
      </nav>

      <div class="container-fluid">
      	<!-- /#page-content-wrapper -->
      	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">      		
      		<b><i class="oi oi-cloud-download"></i> Import <?= $item_type?> List from File</b>
      	</div>
        <div class="page_scroller">
          <form method="post" action="" enctype="multipart/form-data" style="position: fixed;right: 20px; bottom: 150px; width: 100px;">
              <input type="file" name="myFile" class="btn btn-success" onchange="SnackNotice(false,'File uploaded click on Process File to process it')"> <br>
              <input type="submit" name="btnLoadFile" value="Process File" class="btn btn-primary">   
          </form>
          <div class="row"> 
        <div class="form-group col-sm-12 col-md-3">
          <a href="home.php" class="btn btn-outline-primary col-12"><i class="oi oi-arrow-left"></i> Back</a>
        </div>
        <div class="form-group col-sm-12 col-md-3">
          <button class="btn btn-outline-success col-12" onclick="SaveItemList()">
            <i class="oi oi-check"></i> Save</button>
        </div>   
      </div>
        	<div class="row">				
				<table class="table table-sm table-bordered" style="margin-top: 5px;">
					<thead>
						<th class="tetx-center text-success"><i class="oi oi-check"></i></th>
						<th>code</th>
            <th>Service Name</th>             
            <th>Department</th>
            <th>Cost <small>(Ksh)</small></th>
            <th>Description</th>
					</thead>
					<tbody id="import_list">
            <?php //import from file
              if (isset($_POST['btnLoadFile'])) {
                  $file_name = $_FILES['myFile']['name'];
                  $file_size = $_FILES['myFile']['size'];
                  $file_tmp = $_FILES['myFile']['tmp_name'];
                  $file_type = $_FILES['myFile']['type'];
                  move_uploaded_file($file_tmp, "UploadFiles/".$file_name);
                  $File = fopen("UploadFiles/".$file_name, "r");
                  $rowCount = 0;
                  while (($line = fgets($File)) !== false){  
                  $string_array = explode(",", $line);
                  $rowCount++;
                  if ($rowCount>1) {//To skip column titles                 
              ?>
                    <tr>
                      <td class="text-center"><input type="checkbox" checked=""></td>
                      <td><?php echo  $string_array[0]?></td>
                      <td><?php echo  $string_array[1]?></td>
                      <td><?php echo  $string_array[2]?></td>
                      <td><?php echo  $string_array[3]?></td>
                      <td><?php echo  $string_array[4]?></td>
                    </tr>
            <?php } } fclose($File); } ?>
						
					</tbody>
				</table>					
			</div>
		</div>
    </div>
  </div>
<!--Process popup-->
 <div class="modal modal" id="processDialog" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" >
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-body" style="background-color: transparent;" >
			<div class="text-center">
				<div>
					<img src="../images/loading.gif" class="icon" style="width: 200px; height:200px; opacity: 0.5">
				</div>
			</div>
		</div>
	</div>	
</div>
  <!-- Menu Toggle Script -->

  <script>
  	var req = null;
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });
    $('#fileInput').change(function(e){      
      $('#processDialog').modal('show');
      var filename = e.target.files[0];
      var formData = new FormData();
      formData.append("ReadFile","1");
      formData.append("excelFile","filename");
      ReadFile(filename);
    });
    function ReadFile(formData){
    	$.ajax({
    		method:'POST',
    		url:'CRUD.php',  
        contentType: false,
        enctype: 'multipart/form-data',
        processData: false,
    		data: {formData},
    		success:function(response){
    			$('#import_list').html(response);    			
    			$('#processDialog').modal('hide');    			
    			SnackNotice(true,response);
    		}
    	});
    }
  </script>
</body>
</html>