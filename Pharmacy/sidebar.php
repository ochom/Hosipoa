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
      <i class="oi oi-dashboard"></i> Dashboard
    </a>
    <a href="Queue.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-people"></i> Dispense
    </a>
    <a href="Prescription Queue.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-pencil"></i> Prescribe Drugs
    </a>
    <a href="Purchase Requisition.php" class="list-group-item list-group-item-action ">
        <i class="oi oi-dollar"></i> Purchase Requisition
    </a>
    <a href="Receive Drugs.php" class="list-group-item list-group-item-action ">
        <i class="oi oi-cart"></i> Receive Drugs
    </a>
    <a href="../FindPatient/index.php?dep=Pharmacy" class="list-group-item list-group-item-action">
        <i class="oi oi-magnifying-glass"></i> Find Patient
    </a>
    <a href="../Profile Manager/index.php?dep=Pharmacy" class="list-group-item list-group-item-action ">
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