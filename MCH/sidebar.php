<!-- Sidebar -->
<div class="border-right" id="sidebar-wrapper">
  <div class="sidebar-heading">
  	<img src="../logo2.png" height="30" width="60" class="inline-block align-top" alt="">
     Lysofts inc.
  </div>
  <div class="list-group list-group-flush">
    <a href="../home.php" class="list-group-item list-group-item-action">
      <i class="oi oi-home"></i> Home
    </a>
    <a href="Home.php" class="list-group-item list-group-item-action">
      <i class="oi oi-dashboard"></i> Dashboard
    </a>
    <a href="New Client.php" class="list-group-item list-group-item-action">
        <i class="oi oi-person"></i> New Client
    </a>
    <a href="Client List.php?type=ANC" class="list-group-item list-group-item-action">
        <i class="oi oi-document"></i> ANC Clinic
    </a>
    <a href="Client List.php?type=PNC" class="list-group-item list-group-item-action">
        <i class="oi oi-file"></i> PNC Clinic
    </a>
    <a href="../Profile Manager/index.php?dep=Morgue" class="list-group-item list-group-item-action ">
      <i class="oi oi-cog"></i> Profile
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
<!-- /#sidebar-wrapper -->