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
    <a href="home.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-dashboard"></i> Dashboard
    </a>
    <a href="Payment.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-briefcase"></i> Payments
    </a>
    <a href="OPD Billing.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-dollar"></i> OPD Billing
    </a>
    <a href="Night Audit.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-dollar"></i> Night Audit
    </a>
    <a href="IPD Cash Billing.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-dollar"></i> IPD Cash Billing
    </a>
    <a href="IPD Corporate Billing.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-dollar"></i> IPD Corporate Billing
    </a>
    <a href="Morgue Billing.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-tags"></i> Morgue Billing
    </a>
    <a href="../Profile Manager/index.php?dep=Admin-Revenue" class="list-group-item list-group-item-action ">
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