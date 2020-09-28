<?php
include('../ConnectionClass.php');
include('../db_class.php');
session_start();

$db = new CRUD();

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
if (!($User_level=='admin' || $GroupPrivileges['radiology_priv']==1)) {
  header("refresh:0, url=../Permission.php");
  return;
}

//Process page
    $reqcode = $_GET['reqcode'];
    $Request = $db->ReadOne("SELECT * From tbl_radiology_log where req_id = '$reqcode'");
    $refno = $Request['refno'];
    $Patient = mysqli_fetch_array(mysqli_query($conn,"SELECT * From tbl_patient where refno = '$refno'"),MYSQLI_ASSOC);
    $age = $db->getPatientAge($Patient['dob']);
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
        <a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Radiology</a>
    </div>
      </nav>

      <div class="container-fluid">
        <!-- /#page-content-wrapper -->
        <div class="col-11 text-secondary" style="background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">
          <b><i class="oi oi-pencil"></i> Feed Investigation Results</b>
        </div> 
          <div class="col-sm-11 col-md-9 col-lg-9" style="height: auto; padding: 5px 20px; border-radius: 5px; border:1px solid #ccc; margin:auto; margin-top: 10px;">
            <div style="width: 100%; background-color: white; border-bottom: 1px solid #ccc; margin-bottom: 5px;">
              <table style="width: 100%;">
                <tr>
                  <td>Name: <b><?= $Patient['fullname']?></b></td>
                  <td>Reg NO. <b><?= $refno ?></b></td>
                </tr>
                <tr>
                  <td>D.O.B (Age) <b><?= $Patient['dob']?> (<?= $age?>)</b></td>
                  <td>Sex <b><?= $Patient['sex'] ?></b></td>
                </tr>
              </table>
            </div>
            <div class="row">
              <div class="form-group col-sm-12 col-md-8">
                <label>Investigation</label>
                <input id="investigation" class="form-control form-control-sm" placeholder="Investigation" value="<?= $Request['investigation']?>" readonly>
              </div>
              <div class="form-group col-sm-12 col-md-4">
                <label>Request Date</label>
                <input id="specimen" class="form-control form-control-sm"  placeholder="Specimen" value="<?= date('d/m/Y H:i:s') ?>" readonly>
              </div>
            </div>
            <div class="row">
              <div class="form-group col-sm-12 col-md-4">
                <label>Results Date/Time</label>
                <input class="form-control form-control-sm" id="result_date" value="<?= date('d/m/Y H:i:s')?>" readonly>
              </div>
              <div class="form-group col-sm-12 col-md-8">
                <label>Investigation Note</label>
                <textarea id="investigation_note" class="form-control form-control-sm" placeholder="Note..." style="height: 100px;"></textarea>
              </div>
              <p class="col-12 text-primary" style="margin: 5px; border-bottom: 1px solid lime; font-family: cambria; font-size: 25px;">Image Results</p>
              <div class="form-row col-12">
                <div class="form-group col-6">
                    <img src="" id="image_prev" style="position: fixed;right: 50px;top:60px; height: 150px; width: 120px; border: 1px solid #eee; margin-left: 50px;">
                    <input type="file" accept="image/*" id="xray_pic" onchange="$('#image_prev').attr('src',window.URL.createObjectURL(this.files[0]))">
                    <input id="pic_des" class="form-control form-control-sm" placeholder="Image Description...">
                    <button class="btn btn-primary btn-sm" onclick="AddImageToList();"><i class="oi oi-plus"></i> Add</button>
                </div>
                <div class="form-group col-6">
                  <table class="table table-sm">
                    <tbody id="xray_pictures_list">
                      <!-- ADD FROM JS -->                      
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="form-group col-sm-12 col-md-4">
                <button class="btn btn-success col-12" onclick="FeedResults()"><i class="oi oi-check"></i> Submit</button>
              </div>
              <div class="form-group col-sm-12 col-md-4">
                <a href="Results opd_queue.php" class="btn btn-danger col-12"><i class="oi oi-x"></i> Cancel</a>
              </div>            
            </div>
          </div>
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
    var req_id = "<?= $reqcode ?>";
    var image_list = new Array();

    function FeedResults(){
      var req_id = "<?= $reqcode?>";
      var result_date  = $('#result_date').val();
      var investigation_note = $('#investigation_note').val();

      if (investigation_note.length==0) {SnackNotice(false,'Enter the investigation note from the investigation'); $('#investigation_note').focus();return;}

      $('#processDialog').modal('show');
      SaveImages();
      $.ajax({
        method:'POST',
        url:'CRUD.php',
        data:{FeedResults:'1',
              req_id:req_id,
              investigation_note:investigation_note,
            },
        success:function(response){
          $('#processDialog').modal('hide');
          if (response.includes('success')) {
            SnackNotice(true,'Results entered succesfully');
            location.href="Results opd_queue.php";
          }else{
            SnackNotice(false,response);
          }
        },
        error:function(err){
          $('#processDialog').modal('hide');
          SnackNotice(false,err);
        }
      });
    }

    function AddImageToList(){
      var pic_des = $('#pic_des').val();
      if (!($('#xray_pic').prop('files').length===0 || pic_des.length===0)) {
        var image = $('#xray_pic').prop('files')[0];
        var image_url = window.URL.createObjectURL($('#xray_pic').prop('files')[0]);
          image_list.push(image);
          $('#xray_pictures_list').append(
            "<tr><td><img src='"+image_url+"' style='width: 30px; height: 30px; border-radius:3px; overflow:hidden;'></td><td>"+pic_des+"</td></tr>"
            );

          $('#xray_pic').parent('div').find('input').val('');
          $('#xray_pic').parent('div').find('img').attr('src','');
      }else{
        SnackNotice(false,"You need to add both the image and its description");
      }
    }

    function SaveImages(){

      var index=-1;
      $('#xray_pictures_list tr').each(function(){
        index++;
        var row = $(this);
        var image = image_list[index];
        var image_desc = row.find('td:nth-child(2)');

        var form = new FormData();
          form.append('SaveImages','1');
          form.append('req_id',req_id);
          form.append('image_name',image);
          form.append('image_desc',image_desc);
          form.append('total_images',image_list.length);
        console.log(image_list.length);

        $.ajax({
          method:'POST',
          url:'CRUD.php',
          data:form,
          processData:false,
          contentType:false,
          success:function(response){
            console.log(response);
          }
        });
      });
    }
  </script>
</body>
</html>