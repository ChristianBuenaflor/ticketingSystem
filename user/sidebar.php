<?php
date_default_timezone_set('Asia/Manila');

function sidebarLinkIsCurrent($href)
{
    $currentPath = realpath($_SERVER['SCRIPT_FILENAME']);
    $linkPath = parse_url($href, PHP_URL_PATH);
    $targetPath = $linkPath ? realpath(dirname($currentPath) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $linkPath)) : false;

    return $currentPath && $targetPath && $currentPath === $targetPath;
}

$usersMenuIsActive = sidebarLinkIsCurrent('../users/clients.php')
    || sidebarLinkIsCurrent('../users/admins.php')
    || sidebarLinkIsCurrent('../users/list.php');
$settingsMenuIsActive = sidebarLinkIsCurrent('../settings/my-profile.php')
    || sidebarLinkIsCurrent('../settings/ticket-category.php');

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
          <li class="menu-item<?= sidebarLinkIsCurrent('../dashboard/dashboard.php') ? ' active' : '' ?>">
            <a href="../dashboard/dashboard.php"<?= sidebarLinkIsCurrent('../dashboard/dashboard.php') ? ' aria-current="page"' : '' ?>>
              <span class="menu-icon">
                <i class="ri-dashboard-horizontal-fill"></i>
              </span>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>
          <li class="menu-item<?= sidebarLinkIsCurrent('../projects/list.php') ? ' active' : '' ?>">
            <a href="../projects/list.php"<?= sidebarLinkIsCurrent('../projects/list.php') ? ' aria-current="page"' : '' ?>>
              <span class="menu-icon">
                <i class="ri-server-line"></i>
              </span>
              <span class="menu-title">Projects</span>
            </a>
          </li>
          <!-- SHOW ONLY FOR ADMIN / SUPER_ADMIN -->
          <?php if ($user_role != 'client' && $user_role != 'technician') { ?>
            <li class="menu-item<?= sidebarLinkIsCurrent('../users/technicians.php') ? ' active' : '' ?>">
              <a href="../users/technicians.php"<?= sidebarLinkIsCurrent('../users/technicians.php') ? ' aria-current="page"' : '' ?>>
                <span class="menu-icon"><i class="ri-user-settings-line"></i></span>
                <span class="menu-title">Technicians</span>
              </a>
            </li>

            <li class="menu-item sub-menu<?= $usersMenuIsActive ? ' active open' : '' ?>">
              <a href="#">
                <span class="menu-icon">
                  <i class="ri-group-line"></i>
                </span>
                <span class="menu-title">Users</span>
              </a>
              <div class="sub-menu-list">
                <ul>
                  <li class="menu-item<?= sidebarLinkIsCurrent('../users/clients.php') ? ' active' : '' ?>">
                    <a href="../users/clients.php"<?= sidebarLinkIsCurrent('../users/clients.php') ? ' aria-current="page"' : '' ?>>
                      <span class="menu-title"><span class="menu-icon"><i class="ri-account-pin-circle-line"></i></span>&nbsp;Clients</span>
                    </a>
                  </li>
                  <li class="menu-item<?= sidebarLinkIsCurrent('../users/admins.php') ? ' active' : '' ?>">
                    <a href="../users/admins.php"<?= sidebarLinkIsCurrent('../users/admins.php') ? ' aria-current="page"' : '' ?>>
                      <span class="menu-title"><span class="menu-icon"><i class="ri-admin-line"></i></span>&nbsp;Admins</span>
                    </a>
                  </li>
                  <li class="menu-item<?= sidebarLinkIsCurrent('../users/list.php') ? ' active' : '' ?>">
                    <a href="../users/list.php"<?= sidebarLinkIsCurrent('../users/list.php') ? ' aria-current="page"' : '' ?>>
                      <span class="menu-title"><span class="menu-icon"><i class="ri-group-line"></i></span>&nbsp;All Users</span>
                    </a>
                  </li>
                </ul>
              </div>
            </li>

            <li class="menu-item<?= sidebarLinkIsCurrent('../evaluations/list.php') ? ' active' : '' ?>">
              <a href="../evaluations/list.php"<?= sidebarLinkIsCurrent('../evaluations/list.php') ? ' aria-current="page"' : '' ?>>
                <span class="menu-icon"><i class="ri-survey-line"></i></span>
                <span class="menu-title">Evaluations</span>
              </a>
            </li>
          <?php } ?>
          <!-- END ADMIN / SUPER_ADMIN -->

          <li class="menu-header" style="padding-top: 20px"><span> OTHERS </span></li>

          <li class="menu-item sub-menu<?= $settingsMenuIsActive ? ' active open' : '' ?>">
            <a href="#">
              <span class="menu-icon">
                <i class="ri-settings-fill"></i>
              </span>
              <span class="menu-title">Settings</span>
            </a>
            <div class="sub-menu-list">
              <ul>
                <?php if ($user_role != 'super_admin') { ?>
                  <li class="menu-item<?= sidebarLinkIsCurrent('../settings/my-profile.php') ? ' active' : '' ?>">
                    <a href="../settings/my-profile.php"<?= sidebarLinkIsCurrent('../settings/my-profile.php') ? ' aria-current="page"' : '' ?>>
                      <span class="menu-icon">
                        <i class="ri-profile-line"></i>
                      </span>
                      <span class="menu-title">My Profile</span>
                    </a>
                  </li>
                <?php } ?>

                <?php if ($user_role == 'admin' || $user_role == 'super_admin') { ?>
                  <li class="menu-item<?= sidebarLinkIsCurrent('../settings/ticket-category.php') ? ' active' : '' ?>">
                    <a href="../settings/ticket-category.php"<?= sidebarLinkIsCurrent('../settings/ticket-category.php') ? ' aria-current="page"' : '' ?>>
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
<?php
require_once __DIR__ . '/../includes/page_breadcrumbs.php';
renderPageBreadcrumbs('user');
?>
