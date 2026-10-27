<style>
  body{
    font-family: 'Montserrat', sans-serif;
  background-color: #F0F2F5!important;
  backdrop-filter: blur(4px);
-webkit-backdrop-filter: blur(4px);
font-size:.6rem;
  }
</style>

<?php 

date_default_timezone_set('Asia/Manila');

$query = "SELECT * FROM users where user_id = $user_id";
                                            $result = mysqli_query($con, $query);
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                  $username = strtolower($row['email']);
                                                  $userRoles = $row['role'];
                                                }
                                              }

?>

<aside id="sidebar" class="sidebar break-point-sm has-bg-image">
        <a id="btn-collapse" class="sidebar-collapser"><i class="ri-arrow-left-s-line"></i></a>
        <div class="sidebar-layout">
          <div class="sidebar-header">
            <div class="pro-sidebar-logo">
                <div><i class="ri-admin-line"></i></div>
              <h5 style="line-height: 1em; margin:0; padding:0">Administrator<br><span style="line-height:1em;font-size:.6rem;"><?= $username ?></span></h5>
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
                        <a href="../users/list.php">
                          <span class="menu-title"><span class="menu-icon"><i class="ri-list-unordered"></i></span>&nbsp;Clients</span>
                        </a>
                      </li>

                      <li class="menu-item">
                        <a href="../users/list.php">
                          <span class="menu-title"><span class="menu-icon"><i class="ri-list-unordered"></i></span>&nbsp;Technicians</span>
                        </a>
                      </li>

                      <li class="menu-item">
                        <a href="../users/list.php">
                          <span class="menu-title"><span class="menu-icon"><i class="ri-list-unordered"></i></span>&nbsp;Admins</span>
                        </a>
                      </li>
                    </ul>
                  </div>
                </li>
              
                
               
            
               
                
                
                
                <li class="menu-header" style="padding-top: 20px"><span> OTHERS </span></li>
                
                <?php if($userRoles!='super_admin'){ ?>
                <li class="menu-item sub-menu">
                  <a href="#">
                    <span class="menu-icon">
                     <i class="ri-settings-fill"></i>
                    </span>
                    <span class="menu-title">Settings</span>
                  </a>
                  <div class="sub-menu-list">
                    <ul>
                      <li class="menu-item">
                        <a href="../settings/my-profile.php">
                        <span class="menu-icon">
                        <i class="ri-user-settings-line"></i>
                    </span>
                          <span class="menu-title">My Profile</span>
                        </a>
                      </li>
                    </ul>
                  </div>
                </li>
                <?php 
                }
                ?>
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
      