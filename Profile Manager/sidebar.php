<!-- Sidebar -->
<div class="border-right" id="sidebar-wrapper">
  <div class="sidebar-heading">
  	<img src="../logo2.png" height="30" width="60" class="inline-block align-top" alt="">
     Lysofts 
  </div>
  <div class="list-group list-group-flush">
    <a href="../<?= $_SESSION['order_department']?>/home.php" class="list-group-item list-group-item-action">
      <i class="oi oi-dashboard"></i> Dashborad
    </a>
    <a href="profile.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-person"></i> Profile
    </a>
    <a href="Messages.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-comment-square"></i> Messages
    </a>
    <a href="Password.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-lock-locked"></i> Password
    </a>
    <a href="../logout.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-power-standby"></i> Logout
    </a>
  </div>
  <div class="logged_in">
    <i class="oi oi-person"></i> <?= $_SESSION['Fullname']?><br>
    <span class="text-warning"><i class="oi oi-lock-locked"></i> <?= $_SESSION['GroupPrivileges']['group_name']?></span><br>
    <i class="oi oi-clock"></i><?= $_SESSION['Login_Time']?>
  </div>
</div>