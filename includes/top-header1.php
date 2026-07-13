<div class="account-wrapper">
<?php 
if (isset($_SESSION['login']) && $_SESSION['login']) {
    $hour = date('H');
    if ($hour >= 5 && $hour < 12) {
        $greeting = "Good Morning";
        $icon = "fas fa-sun";
        $colorClass = "morning";
    } elseif ($hour >= 12 && $hour < 17) {
        $greeting = "Good Afternoon";
        $icon = "fas fa-leaf";
        $colorClass = "afternoon";
    } elseif ($hour >= 17 && $hour < 21) {
        $greeting = "Good Evening";
        $icon = "fas fa-moon";
        $colorClass = "evening";
    } else {
        $greeting = "Good Night";
        $icon = "fas fa-star";
        $colorClass = "night";
    }
?>
  <button class="account-toggle-btn <?php echo $colorClass; ?>" onclick="toggleAccountMenu()">
    <i class="<?php echo $icon; ?>"></i>
    <div class="text-container">
      <span class="greeting"><?php echo $greeting; ?></span>
      <span class="username"><?php echo htmlentities($_SESSION['username']); ?></span>
    </div>
  </button>
<?php 
} else {
?>
  <a href="login.php" class="account-toggle-btn night">
    <i class="fas fa-user-circle"></i>
    <div class="text-container">
      <span class="greeting">Welcome</span>
      <span class="username">Login</span>
    </div>
  </a>
<?php 
}
?>

  <div class="account-menu" id="accountMenu">
    <ul>
      <?php if(!isset($_SESSION['login']) || strlen($_SESSION['login']) == 0) { ?>
        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
      <?php } else { ?>
        <li><a href="my-account.php"><i class="fas fa-user-cog"></i> My Account</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      <?php } ?>
    </ul>
  </div>
</div>

<style>
.account-wrapper {
  display: flex;
  justify-content: flex-end;
  padding: 0px 4px;
  position: relative;
  background: transparent;
  flex-shrink: 0; /* Layout shrink compression control injection */
}

/* Base Light Premium Button Interface */
.account-toggle-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 2px 10px;
  width: 130px; /* Layout constraint matching standard framework */
  height: 35px;
  border-radius: 10px;
  border: 1px solid #e2e8f0; /* Soft border trace */
  background-color: #ffffff; /* Crisp white background base */
  cursor: pointer;
  text-decoration: none;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 1px 2px rgba(0, 0, 0, 0.04);
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.account-toggle-btn:hover {
  background-color: #f8fafc;
  border-color: #cbd5e1;
  transform: translateY(-1px);
}

/* High Contrast Responsive Text Configuration */
.text-container {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  line-height: 1.1;
  overflow: hidden;
  width: 100%;
}

.greeting {
  font-size: 9px;
  font-weight: 600;
  color: #64748b; /* Muted modern slate for sub-labels */
  text-transform: uppercase;
  letter-spacing: 0.2px;
  white-space: nowrap;
}

.username {
  font-size: 11px;
  font-weight: 700;
  color: #1e293b; /* Strong deep value dark slate gray */
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  width: 100%;
  text-align: left;
}

/* Time-based Premium Adaptive Icon Color Accent Mapping */
.account-toggle-btn.morning i { color: #f59e0b; }    /* Golden Amber Sun */
.account-toggle-btn.afternoon i { color: #10b981; }  /* Emerald Green Leaf */
.account-toggle-btn.evening i { color: #f43f5e; }    /* Coral Sunrise Pink Moon */
.account-toggle-btn.night i { color: #6366f1; }      /* Premium Indigo Star/User */

.account-toggle-btn i {
  font-size: 14px; /* Slightly upscaled for pixel-perfect clarity */
  transition: transform 0.2s;
  flex-shrink: 0;
}

.account-toggle-btn:hover i {
  transform: scale(1.08);
}

/* Premium Light Menu Dropdown Stack */
.account-menu {
  display: none;
  position: absolute;
  top: 42px;
  right: 0;
  background-color: #ffffff;
  border-radius: 12px;
  width: 170px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
  z-index: 99999;
  overflow: hidden;
  animation: menuSlideDown 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes menuSlideDown {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}

.account-menu.show { display: block; }

.account-menu ul {
  margin: 0;
  padding: 4px;
  list-style: none;
}

.account-menu a {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  font-size: 13px;
  font-weight: 600;
  color: #475569;
  border-radius: 8px;
  text-decoration: none;
  transition: all 0.15s ease;
}

.account-menu a i {
  font-size: 14px;
  color: #94a3b8;
  transition: color 0.15s;
}

.account-menu a:hover {
  background-color: #f1f5f9;
  color: #0d9488; /* Accent signature brand teal on hover selection */
}

.account-menu a:hover i {
  color: #0d9488;
}

/* Compact Responsive Media Query Adjustments */
@media (max-width: 768px) {
  .account-wrapper { justify-content: center; }
  .account-toggle-btn { 
    font-size: 12px; 
    padding: 2px 8px; 
    width: 115px; 
    height: 33px; 
    gap: 4px; 
  }
  .account-menu { width: 150px; top: 38px; }
}
</style>

<script>
function toggleAccountMenu(){
  const menu = document.getElementById('accountMenu');
  menu.classList.toggle('show');
}

// Close account menu dropdown automatically when user clicks anywhere outside
window.addEventListener('click', function(e) {
  const wrapper = document.querySelector('.account-wrapper');
  if (wrapper && !wrapper.contains(e.target)) {
    const menu = document.getElementById('accountMenu');
    if(menu) menu.classList.remove('show');
  }
});
</script>