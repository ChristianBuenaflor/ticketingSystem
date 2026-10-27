<?php
date_default_timezone_set('Asia/Manila');

$query = "SELECT * FROM users WHERE user_id = $user_id";
$result = mysqli_query($con, $query);
if (mysqli_num_rows($result) > 0) {
    while ($row = $result->fetch_assoc()) {
        $username = strtolower($row['email']);
        $user_role = $row['role']; // Consistent variable name
    }
}
?>

<aside id="sidebar" class="sidebar break-point-sm has-bg-image">
  <a id="btn-collapse" class="sidebar-collapser"><i class="ri-arrow-left-s-line"></i></a>
  <div class="sidebar-layout">
    <div class="sidebar-header">
      <div class="pro-sidebar-logo">
        <div>
          <?php if ($user_role == "client") { ?>
            <i class="ri-account-pin-box-line"></i>
          <?php } elseif ($user_role == 'technician') { ?>
            <i class="ri-user-settings-line"></i>
          <?php } else { ?>
            <i class="ri-admin-line"></i>
          <?php } ?>
        </div>
        <h5 style="line-height: .8em; margin:0; padding:0">
          <?php
          if ($user_role == "client") {
            echo "Client Portal";
          } elseif ($user_role == 'technician') {
            echo "Technician";
          } else {
            echo "Admin";
          }
          ?>
          <br><span style="line-height:1em;font-size:.6rem;"><?= $username ?></span>
        </h5>
      </div>
    </div>

    <div class="sidebar-content">
      <nav class="menu open-current-submenu">
        <ul>
          <li class="menu-header"><span> GENERAL </span></li>
          <li class="menu-item">
            <a href="../dashboard/dashboard.php">
              <span class="menu-icon">
                <i class="ri-dashboard-horizontal-fill"></i>
              </span>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>
          <li class="menu-item">
            <a href="../projects/list.php">
              <span class="menu-icon">
                <i class="ri-server-line"></i>
              </span>
              <span class="menu-title">Projects</span>
            </a>
          </li>
          <li class="menu-item">
            <a href="../tickets/list.php">
              <span class="menu-icon">
                <i class="ri-aed-electrodes-line"></i>
              </span>
              <span class="menu-title">Tickets</span>
            </a>
          </li>

          <!-- SHOW ONLY FOR ADMIN / SUPER_ADMIN -->
          <?php if ($user_role != 'client' && $user_role != 'technician') { ?>
            <li class="menu-item">
              <a href="../users/technicians.php">
                <span class="menu-icon"><i class="ri-user-settings-line"></i></span>
                <span class="menu-title">Technicians</span>
              </a>
            </li>

            <li class="menu-item sub-menu">
              <a href="#">
                <span class="menu-icon">
                  <i class="ri-group-line"></i>
                </span>
                <span class="menu-title">Users</span>
              </a>
              <div class="sub-menu-list">
                <ul>
                  <li class="menu-item">
                    <a href="../users/clients.php">
                      <span class="menu-title"><span class="menu-icon"><i class="ri-account-pin-circle-line"></i></span>&nbsp;Clients</span>
                    </a>
                  </li>
                  <li class="menu-item">
                    <a href="../users/admins.php">
                      <span class="menu-title"><span class="menu-icon"><i class="ri-admin-line"></i></span>&nbsp;Admins</span>
                    </a>
                  </li>
                  <li class="menu-item">
                    <a href="../users/list.php">
                      <span class="menu-title"><span class="menu-icon"><i class="ri-group-line"></i></span>&nbsp;All Users</span>
                    </a>
                  </li>
                </ul>
              </div>
            </li>

            <li class="menu-item">
              <a href="../evaluations/list.php">
                <span class="menu-icon"><i class="ri-survey-line"></i></span>
                <span class="menu-title">Evaluations</span>
              </a>
            </li>
          <?php } ?>
          <!-- END ADMIN / SUPER_ADMIN -->

          <li class="menu-header" style="padding-top: 20px"><span> OTHERS </span></li>

          <li class="menu-item sub-menu">
            <a href="#">
              <span class="menu-icon">
                <i class="ri-settings-fill"></i>
              </span>
              <span class="menu-title">Settings</span>
            </a>
            <div class="sub-menu-list">
              <ul>
                <?php if ($user_role != 'super_admin') { ?>
                  <li class="menu-item">
                    <a href="../settings/my-profile.php">
                      <span class="menu-icon">
                        <i class="ri-profile-line"></i>
                      </span>
                      <span class="menu-title">My Profile</span>
                    </a>
                  </li>
                <?php } ?>

                <?php if ($user_role == 'admin' || $user_role == 'super_admin') { ?>
                  <li class="menu-item">
                    <a href="../settings/ticket-category.php">
                      <span class="menu-icon">
                        <i class="ri-list-indefinite"></i>
                      </span>
                      <span class="menu-title">Ticket Category</span>
                    </a>
                  </li>
                <?php } ?>
              </ul>
            </div>
          </li>

          <li class="menu-item">
            <a href="../logout.php">
              <span class="menu-icon">
                <i class="ri-logout-box-line"></i>
              </span>
              <span class="menu-title">Sign Out</span>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</aside>
<div id="overlay" class="overlay"></div>
