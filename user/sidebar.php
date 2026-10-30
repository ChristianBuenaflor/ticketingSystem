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
        $user_role = normalizeUserRole($row['role']);
    }
}
?>

<aside id="sidebar" class="sidebar break-point-sm has-bg-image">
  <a id="btn-collapse" class="sidebar-collapser"><i class="ri-arrow-left-s-line"></i></a>
  <div class="sidebar-layout">
    <div class="sidebar-header">
      <div class="pro-sidebar-logo">
        <div>
          <?php if ($user_role === 'clerk') { ?>
            <i class="ri-account-pin-box-line"></i>
          <?php } else { ?>
            <i class="ri-admin-line"></i>
          <?php } ?>
        </div>
        <h5 style="line-height: .8em; margin:0; padding:0">
          <?php
          if ($user_role === 'clerk') {
            echo "Clerk Portal";
          } else {
            echo htmlspecialchars(ucfirst($user_role), ENT_QUOTES, 'UTF-8');
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
          <?php if (isManagementRole($user_role)) { ?>
            <li class="menu-item<?= sidebarLinkIsCurrent('../users/list.php') ? ' active' : '' ?>">
                <a href="../users/list.php?list=clerk"<?= sidebarLinkIsCurrent('../users/list.php') ? ' aria-current="page"' : '' ?>>
                <span class="menu-icon"><i class="ri-user-settings-line"></i></span>
                  <span class="menu-title">Clerks</span>
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
                  <li class="menu-item<?= sidebarLinkIsCurrent('../users/list.php') ? ' active' : '' ?>">
                    <a href="../users/list.php?list=admin"<?= sidebarLinkIsCurrent('../users/list.php') ? ' aria-current="page"' : '' ?>>
                      <span class="menu-title"><span class="menu-icon"><i class="ri-account-pin-circle-line"></i></span>&nbsp;Admins</span>
                    </a>
                  </li>
                  <li class="menu-item<?= sidebarLinkIsCurrent('../users/list.php') ? ' active' : '' ?>">
                    <a href="../users/list.php?list=director"<?= sidebarLinkIsCurrent('../users/list.php') ? ' aria-current="page"' : '' ?>>
                      <span class="menu-title"><span class="menu-icon"><i class="ri-admin-line"></i></span>&nbsp;Directors</span>
                    </a>
                  </li>
                  <li class="menu-item<?= sidebarLinkIsCurrent('../users/list.php') ? ' active' : '' ?>">
                    <a href="../users/list.php?list=head"<?= sidebarLinkIsCurrent('../users/list.php') ? ' aria-current="page"' : '' ?>>
                      <span class="menu-title"><span class="menu-icon"><i class="ri-admin-line"></i></span>&nbsp;Heads</span>
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
                <?php if (isManagementRole($user_role)) { ?>
                  <li class="menu-item<?= sidebarLinkIsCurrent('../settings/my-profile.php') ? ' active' : '' ?>">
                    <a href="../settings/my-profile.php"<?= sidebarLinkIsCurrent('../settings/my-profile.php') ? ' aria-current="page"' : '' ?>>
                      <span class="menu-icon">
                        <i class="ri-profile-line"></i>
                      </span>
                      <span class="menu-title">My Profile</span>
                    </a>
                  </li>
                <?php } ?>

                <?php if (isManagementRole($user_role)) { ?>
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
