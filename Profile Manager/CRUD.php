<?php
session_start();
include('../ConnectionClass.php');
include('../db_class.php');

$db = new CRUD();

$Username = $_SESSION['Username'];

//Mofidy Password
  if (isset($_POST['ChangePassword'])) {
    $Username = $_SESSION['Username'];
    $old_pass = mysqli_real_escape_string($conn,$_POST['old_pass']);

    $new_pass = mysqli_real_escape_string($conn,openssl_encrypt($_POST['new_pass'], "AES-128-ECB", $enc_dec_password));
    $User = $db->ReadOne("SELECT * FROM tbl_system_users WHERE username = '$Username'");
    $ExistingPass = $db->Decrypt($User['password']);
    if (!($old_pass==$ExistingPass)) {
        echo "The old password is wrong";
        return;
    }else{
      echo $db->Query("UPDATE tbl_system_users SET password = '$new_pass' WHERE username='$Username'");
    }

  }

//MESSAGING
  if (isset($_POST['FilterUsers'])) {
    $searchval = mysqli_real_escape_string($conn,$_POST['searchval']);
    $res = $db->ReadAll("SELECT * FROM tbl_system_users WHERE (username like '%$searchval%'  OR full_name like '%$searchval%') AND username != '$Username' ORDER BY full_name ASC ");
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
  }

  if (isset($_POST['SendMessage'])) {
    $msg_from = mysqli_real_escape_string($conn,$_POST['msg_from']);
    $msg_to = mysqli_real_escape_string($conn,$_POST['msg_to']);
    $encry_text = $db->Encrypt($_POST['msg_text']);
    $msg_time = date('d/m/Y H:i');
    echo $db->Query("INSERT INTO tbl_messages (`msg_from`, `msg_to`, `msg_text`,`msg_time`) VALUES ('$msg_from', '$msg_to', '$encry_text','$msg_time')");
  }

	if (isset($_POST['GetNewMessages'])) {
    $time_sent=null;
    $today = date('d/m/Y H:i:s');
		$sender_id = mysqli_real_escape_string($conn,$_POST['sender_id']);
    $receiver_id = mysqli_real_escape_string($conn,$_POST['receiver_id']);
		$chatfullname = mysqli_real_escape_string($conn,$_POST['chatfullname']);
    
    $query = $db->ReadAll("SELECT * FROM tbl_messages WHERE (msg_from = '$sender_id' AND  msg_to='$receiver_id') OR (msg_from = '$receiver_id' AND  msg_to='$sender_id') ORDER BY msg_id ASC");
          while ($Messages = mysqli_fetch_assoc($query)) {
            //Decrypt the message
            $messsage = $db->Decrypt($Messages['msg_text']);
            //Get message status
            $status = $Messages['msg_status'];
            if ($today == substr($Messages['msg_time'], 0,10)) {
              $time_sent = substr($Messages['msg_time'], 11,16);
            }else{
              $time_sent = $Messages['msg_time'];
            }
            if($Messages['msg_from']==$sender_id){
              ?>
              <div class="talk-bubble tri-right btm-right darker">
                <div class="img-chat">Me</div>
                <p class="chat-text"><?= $messsage?></p>
                <span class="time-left">
                  <?= $time_sent?> 
                    <?php
                      if ($status=='sent') {
                        echo "<i class='oi oi-check'></i>";
                      }else{
                        echo "<i class='oi oi-check'></i><i class='oi oi-check'></i>";
                      }
                    ?> 
                </span>
              </div> 
              <?php
            }else{
              ?>
                <div class="talk-bubble tri-right btm-left">
                  <div class="img-chat right"><?= substr($chatfullname, 0,1);?></div>
                  <p class="chat-text"><?= $messsage?></p>
                  <span class="time-right"><?= $time_sent?></span>
                </div>
              <?php
            }
          } 
      $db->Query("UPDATE tbl_messages SET msg_status='seen' WHERE msg_to='$sender_id' AND msg_from='$receiver_id'");
	  }

?>