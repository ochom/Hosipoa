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
    <a href="Account Names.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-pencil"></i> Accounts
    </a>
    <a href="General Ledger.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-pencil"></i> General Ledger
    </a>
    <a href="home.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-pencil"></i> Balance Sheet
    </a>
    <a href="home.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-pencil"></i> Trial Balance
    </a>
    <a href="Profit_Loss.php" class="list-group-item list-group-item-action ">
      <i class="oi oi-pencil"></i> Profit/Loss Accounts
    </a>
    <a href="../Profile Manager/index.php?dep=Accounts" class="list-group-item list-group-item-action ">
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