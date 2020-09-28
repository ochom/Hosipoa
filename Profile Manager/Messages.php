<?php
session_start();
if (!(isset($_SESSION['Username']))) {
  header("refresh:0, url=index.php");
  return;
}
//Session Values
$Username = $_SESSION['Username'];
$Fullname = $_SESSION['Fullname'];
$User_level = $_SESSION['User_level'];


include("../ConnectionClass.php");
$chatname = '';
$chatfullname = '';
if (isset($_GET['username'])) {
  $userid = $_GET['username'];
  $Chat = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM tbl_system_users where reg_no='$userid'"),MYSQLI_ASSOC);
  $chatname = $Chat['username'];
  $chatfullname = $Chat['full_name'];
}
if (!mysqli_query($conn,"UPDATE tbl_messages SET msg_status='received' WHERE msg_to='$Username'")) {
    echo "Sorry - ".mysqli_error($conn);
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
    .contacts a{
      display: inline-block; border-bottom: 1px solid #eee; width: 100%; padding: 3px;
    }
    .contacts a div{
      float: left;
    }
    .contacts a .img{
      height: 50px;width: 50px; 
      font-size: 30px; text-align: center;
      border-radius: 50%; overflow: hidden; background-color: #ccc; margin:2px 5px;
    }
    .input-group .input-group-prepend{
      background-color: green;
    }

/* CSS talk bubble */
.talk-bubble {
  margin: 40px;
  display: table;
  position: relative;
  background-color: #fff;
  border-radius: 5px;
  padding: 10px;
  margin: 10px;
  width: 400px;
  max-width: 400px;
  margin-left: 20px;
}
/* Darker chat message */
  .darker {
    position: relative; margin-left: 30px; 
    color: #fff;
    border-color: #fff;
    background-color: #66f;
  }
.border{
  border: 8px solid #666;
}

.tri-right.btm-left:after{
  content: ' ';
  position: absolute;
  width: 0;
  height: 0;
  left: -15px;
  right: auto;
  top: auto;
  bottom: 0px;
  border: 22px solid;
  border-color: transparent transparent #fff transparent;
}


/*Right triangle, placed bottom right side slightly in*/

.tri-right.btm-right:after{
  content: ' ';
  position: absolute;
  width: 0;
  height: 0;
  left: auto;
  right: -12px;
  bottom: 0px;
  border: 22px solid;
  border-color: transparent transparent #66f transparent;
}



    /* Style images */
    .talk-bubble .img-chat {
      float: left;
      max-width: 60px;
      width: 40px;
      height: 40px;
      margin-right: 20px;
      border-radius: 50%;
      padding: 5px;
      background-color: rgba(200,200,200,0.5);
      text-align: center; font-size: 20px;
    }
    .talk-bubble .chat-text{
      width: 320px; float: left; margin-bottom: 0px;
      overflow-wrap: break-word;
    }
    /* Style the right image */
    .talk-bubble .img-chat.right {
      float: right;
      margin-left: 20px;
      margin-right:0;
    }

    /* Style time text */
    .time-right {
      width: 100%; float: right; margin-right: 10%; text-align: right; 
      float: right;
      color: #777;
    }

    /* Style time text */
    .time-left {
      width: 100%; float: left; margin-left: 10%;
      float: left;
      color: #ddd;
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
				<a class="navbar-brand" href="" style="color: rgb(255,153,0);"> Messages</a>
		</div>
      </nav>

  <div class="message-fluid">
      	<!-- /#page-content-wrapper -->
    	<div class="text-secondary col-11" style=" background-color: white; box-shadow: 0 3px 5px rgba(0,0,0,0.5); padding: 10px 20px; margin:auto; border-radius: 3px;">      		
    		<b><i class="oi oi-comment-square"></i> Messages</b>
    	</div>
    <div class="col-11" style=" height: 500px; padding: 0px; border-radius: 5px; border:1px solid #ccc; margin:auto; margin-top: 10px;">
      <div style="float: left; width: 35%; height: 100%; background-image: linear-gradient(to right,#50f,#f70,#f30);"> 
        <div style="float: left; width: 100%; padding: 2px;">
          <input id="searchval" placeholder="Search..." onkeyup="FilterUsers($(this).val())" style="background:transparent; border:none; border-radius: 0px; width: 90%; margin: 5px; color: white;">
        </div>       
        <div class="contacts" style="float: left; width: 100%; height: 470px; overflow-y: scroll; overflow: auto; overflow-x: hidden;" id="contact_list">
          <?php 
            $sql = "SELECT * FROM tbl_system_users WHERE username != '$Username' ORDER BY full_name ASC ";
            $res = mysqli_query($conn,$sql);
            while ($rowSet = mysqli_fetch_assoc($res)) {
              ?>
              <a href="Messages.php?username=<?= $rowSet['reg_no']?>">
                <div class="img"><?= substr($rowSet['full_name'], 0,1);?></div>
                <div class="text-light">
                  <b style="color: green"><?= $rowSet['username']?></b><br>
                  <span style="font-size: 13px;"><?= $rowSet['full_name']?></span>                  
                </div>
              </a>
              <?php
            }
          ?>
        </div>
      </div>
      <div style="float: left; width: 65%; height: 100%; background-image: url('../images/chatbg.jpg');">
        <div class="contacts" style="float: left; width: 100%; height: auto; background-image: linear-gradient(to right,#f30,#fd0); color: #fff;">
          <a href=""  style="border:none;">
            <div class="img"><?= substr($chatfullname, 0,1);?></div>
            <div class="text-secondary">
              <b style="color: green;"><?= $chatname?></b><br>
              <span style="font-size: 15px; color: #555;"><?= $chatfullname?></span>                  
            </div>
          </a>
        </div>        
          <?php
            if (isset($_GET['username'])) {
              ?>
              <div id="messages_body" style="float: left; margin: 0px; width: 100%; height: 80%; overflow-y: scroll; overflow: auto; overflow-x: hidden;">
                <div id="messages_list">
                  <!-- Add from Crud -->
                </div>
            </div>
            <div style="float: left; width: 100%; padding-bottom: 0px; background-color: rgba(30,30,30,0.3);">
              <div class="input-group">
                  <input id="msg_text" class="form-control form-control-sm">
                  <div class="input-group-prepend" style="cursor: pointer;" onclick="SendMessage()">
                    <span class="input-group-text"> <i class="oi oi-location"></i> </span>
                 </div>
              </div>
            </div>            
              <script type="text/javascript">                
                setInterval(function(){
                  GetNewMessages("<?= $Username?>");
                },1000);                
              </script>
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
  	var req = null;
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });


    window.addEventListener('keyup',function(e){
      if (e.keyCode ===13) {
        SendMessage();
        scrollMessage();
      }
    }); 

    $(document).ready(function(){
      GetMessages();
    }); 

    function FilterUsers(){
      if (req != null) {req.abort();}
      var searchval = $('#searchval').val();
      req = $.ajax({
        method:'post',
        url:'crud.php',
        data:{FilterUsers:'1',searchval:searchval},
        success:function(response){
          $('#contact_list').html(response);
        }
      });
    }

    function GetMessages(){
      var sender_id = "<?= $Username?>";
      var chatfullname = "<?= $chatfullname?>";
      var receiver_id = "<?= $chatname?>";
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{GetNewMessages:'1',sender_id:sender_id,receiver_id:receiver_id,chatfullname:chatfullname},
        success:function(response){
          $('#messages_list').html(response); 
          scrollMessage();
        }
      });
    }
    
    function scrollMessage(){
      $('#messages_body').animate({scrollTop: $('#messages_list').height()});
    }

    function SendMessage(){
      var msg_from = "<?= $Username?>";
    	var msg_to = "<?= $chatname?>";
    	var msg_text = $('#msg_text').val();
      if (msg_text.length===0) {return;}
      $('#msg_text').val('');
    	$.ajax({
    		method:'POST',
    		url:'CRUD.php',
    		data:{SendMessage:'1',msg_from:msg_from,msg_to:msg_to,msg_text:msg_text},
    		success:function(response){
          scrollMessage();
          GetNewMessages(msg_from);
    		}
    	});
    }

    function GetNewMessages(sender_id){
      var chatfullname = "<?= $chatfullname?>";
      var receiver_id = "<?= $chatname?>";
      $.ajax({
        method:'post',
        url:'crud.php',
        data:{GetNewMessages:'1',sender_id:sender_id,receiver_id:receiver_id,chatfullname:chatfullname},
        success:function(response){
          $('#messages_list').html(response); 
        }
      });
    }
  </script>
</body>
</html>