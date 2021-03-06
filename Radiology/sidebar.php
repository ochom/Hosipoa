<!-- Sidebar -->
<div class="border-right" id="sidebar-wrapper">
  <div class="sidebar-heading">
  	<img src="../logo2.png" height="30" width="60" class="inline-block align-top" alt="">
     Lysofts 
  </div>
  <div class="list-group list-group-flush">
    <a href="../home.php" class="list-group-item list-group-item-action">
      <i class="oi oi-home"></i> Home
    </a>
    <a href="home.php" class="list-group-item list-group-item-action">
      <i class="oi oi-dashboard"></i> Dashborad
    </a>
    <a href="opd_queue.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-people"></i> Out-patient Queue
    </a>
    <a href="ipd_queue.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-people"></i> In-patient Queue
    </a>
    <a href="Results Queue.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-pencil"></i> Feed Results
    </a>
    <a href="logbook.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-book"></i> Logbook
    </a>
    <a href="../FindPatient/index.php?dep=Radiology" class="list-group-item list-group-item-action">
        <i class="oi oi-magnifying-glass"></i> Find Patient
    </a>
    <a href="../Profile Manager/index.php?dep=Radiology" class="list-group-item list-group-item-action ">
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