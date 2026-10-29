<?php
$taskTotal = array_sum($taskStatusCounts);
$isAdminDashboard = in_array($user_role, ['admin', 'super_admin'], true);
?>
<section class="admin-dashboard" aria-labelledby="dashboardHeading">
  <header class="dashboard-welcome">
    <div>
      <p class="dashboard-eyebrow"><?= $isAdminDashboard ? 'ADMIN OVERVIEW' : 'MY WORKSPACE' ?></p>
      <h1 id="dashboardHeading"><?= $isAdminDashboard ? "Good day. Here's your workspace at a glance." : "Good day. Here's your work at a glance." ?></h1>
      <p class="dashboard-date"><?= htmlspecialchars(date('l, F j, Y'), ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <a class="dashboard-primary-link" href="<?= htmlspecialchars($isAdminDashboard ? $dashboardLinks['projects'] : $dashboardLinks['tickets'], ENT_QUOTES, 'UTF-8') ?>">
      <i class="<?= $isAdminDashboard ? 'ri-folder-add-line' : 'ri-customer-service-2-line' ?>" aria-hidden="true"></i>
      <span><?= $isAdminDashboard ? 'Explore projects' : 'View my tickets' ?></span>
    </a>
  </header>

  <section class="dashboard-metrics" aria-label="Key metrics">
    <a class="dashboard-metric metric-projects" href="<?= htmlspecialchars($dashboardLinks['projects'], ENT_QUOTES, 'UTF-8') ?>">
      <span class="metric-icon"><i class="ri-server-line" aria-hidden="true"></i></span>
      <span class="metric-label">Projects</span>
      <strong><?= number_format($dashboardCounts['projects']) ?></strong>
      <span class="metric-caption"><?= $isAdminDashboard ? 'Across your workspace' : 'Shared with you' ?></span>
    </a>
    <a class="dashboard-metric metric-tickets" href="<?= htmlspecialchars($dashboardLinks['tickets'], ENT_QUOTES, 'UTF-8') ?>">
      <span class="metric-icon"><i class="ri-customer-service-2-line" aria-hidden="true"></i></span>
      <span class="metric-label"><?= $isAdminDashboard ? 'All tickets' : 'My tickets' ?></span>
      <strong><?= number_format($dashboardCounts['tickets']) ?></strong>
      <span class="metric-caption"><?= $isAdminDashboard ? 'Support requests received' : ($user_role === 'technician' ? 'Assigned to you' : 'Submitted by you') ?></span>
    </a>
    <?php if ($isAdminDashboard) { ?>
      <a class="dashboard-metric metric-clients" href="<?= htmlspecialchars($dashboardLinks['clients'], ENT_QUOTES, 'UTF-8') ?>">
        <span class="metric-icon"><i class="ri-group-line" aria-hidden="true"></i></span>
        <span class="metric-label">Clients</span>
        <strong><?= number_format($dashboardCounts['clients']) ?></strong>
        <span class="metric-caption">Registered accounts</span>
      </a>
      <a class="dashboard-metric metric-team" href="<?= htmlspecialchars($dashboardLinks['technicians'], ENT_QUOTES, 'UTF-8') ?>">
        <span class="metric-icon"><i class="ri-user-settings-line" aria-hidden="true"></i></span>
        <span class="metric-label">Technicians</span>
        <strong><?= number_format($dashboardCounts['technicians']) ?></strong>
        <span class="metric-caption">Support team members</span>
      </a>
    <?php } ?>
    <?php if ($user_role === 'super_admin') { ?>
      <a class="dashboard-metric metric-admins" href="<?= htmlspecialchars($dashboardLinks['admins'], ENT_QUOTES, 'UTF-8') ?>">
        <span class="metric-icon"><i class="ri-admin-line" aria-hidden="true"></i></span>
        <span class="metric-label">Admins</span>
        <strong><?= number_format($dashboardCounts['admins']) ?></strong>
        <span class="metric-caption">Administrator accounts</span>
      </a>
    <?php } ?>
  </section>

  <section class="dashboard-analytics" aria-label="Workspace analytics and shortcuts">
    <article class="dashboard-panel task-status-panel">
      <div class="dashboard-panel-heading">
        <div>
          <p class="dashboard-eyebrow">DELIVERY</p>
          <h2>Project task progress</h2>
        </div>
        <a href="<?= htmlspecialchars($dashboardLinks['projects'], ENT_QUOTES, 'UTF-8') ?>" class="dashboard-text-link">View projects <i class="ri-arrow-right-line" aria-hidden="true"></i></a>
      </div>
      <div class="task-chart" role="img" aria-label="Project task status totals: <?= number_format($taskStatusCounts['To do']) ?> to do, <?= number_format($taskStatusCounts['In progress']) ?> in progress, <?= number_format($taskStatusCounts['Completed']) ?> completed, <?= number_format($taskStatusCounts['Other']) ?> other.">
        <?php foreach ($taskStatusCounts as $label => $count) {
          $barWidth = $taskTotal ? round($count / $taskTotal * 100, 1) : 0;
          $statusClass = strtolower(str_replace(' ', '-', $label));
        ?>
          <div class="task-chart-row">
            <div class="task-chart-label"><span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span><strong><?= number_format($count) ?></strong></div>
            <div class="task-chart-track" aria-hidden="true"><span class="task-chart-bar task-<?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>" style="width: <?= $barWidth ?>%"></span></div>
          </div>
        <?php } ?>
      </div>
    </article>

    <article class="dashboard-panel dashboard-quick-access" aria-labelledby="quickAccessHeading">
      <div class="dashboard-panel-heading">
        <div>
          <p class="dashboard-eyebrow">SHORTCUTS</p>
          <h2 id="quickAccessHeading">Quick access</h2>
        </div>
      </div>
      <div class="quick-access-links">
        <a href="<?= htmlspecialchars($dashboardLinks['projects'], ENT_QUOTES, 'UTF-8') ?>"><i class="ri-folder-3-line" aria-hidden="true"></i><span>Projects</span><i class="ri-arrow-right-line quick-arrow" aria-hidden="true"></i></a>
        <a href="<?= htmlspecialchars($dashboardLinks['tickets'], ENT_QUOTES, 'UTF-8') ?>"><i class="ri-customer-service-2-line" aria-hidden="true"></i><span>Tickets</span><i class="ri-arrow-right-line quick-arrow" aria-hidden="true"></i></a>
        <?php if ($isAdminDashboard) { ?>
          <a href="<?= htmlspecialchars($dashboardLinks['clients'], ENT_QUOTES, 'UTF-8') ?>"><i class="ri-group-line" aria-hidden="true"></i><span>Clients</span><i class="ri-arrow-right-line quick-arrow" aria-hidden="true"></i></a>
          <a href="<?= htmlspecialchars($dashboardLinks['technicians'], ENT_QUOTES, 'UTF-8') ?>"><i class="ri-user-settings-line" aria-hidden="true"></i><span>Technicians</span><i class="ri-arrow-right-line quick-arrow" aria-hidden="true"></i></a>
        <?php } ?>
        <?php if ($user_role === 'super_admin') { ?>
          <a href="<?= htmlspecialchars($dashboardLinks['admins'], ENT_QUOTES, 'UTF-8') ?>"><i class="ri-admin-line" aria-hidden="true"></i><span>Admins</span><i class="ri-arrow-right-line quick-arrow" aria-hidden="true"></i></a>
        <?php } ?>
        <?php if ($user_role === 'admin' || $user_role === 'super_admin') { ?>
          <a href="<?= htmlspecialchars($dashboardLinks['categories'], ENT_QUOTES, 'UTF-8') ?>"><i class="ri-settings-3-line" aria-hidden="true"></i><span>Ticket categories</span><i class="ri-arrow-right-line quick-arrow" aria-hidden="true"></i></a>
        <?php } ?>
        <?php if (!$isAdminDashboard) { ?>
          <a href="<?= htmlspecialchars($dashboardLinks['profile'], ENT_QUOTES, 'UTF-8') ?>"><i class="ri-user-settings-line" aria-hidden="true"></i><span>My profile</span><i class="ri-arrow-right-line quick-arrow" aria-hidden="true"></i></a>
        <?php } ?>
      </div>
    </article>
  </section>

  <section class="dashboard-panel recent-tasks-panel" aria-labelledby="recentTasksHeading">
    <div class="dashboard-panel-heading">
      <div>
        <p class="dashboard-eyebrow">LATEST ACTIVITY</p>
        <h2 id="recentTasksHeading">Recent tasks</h2>
      </div>
      <a href="<?= htmlspecialchars($dashboardLinks['projects'], ENT_QUOTES, 'UTF-8') ?>" class="dashboard-text-link">View projects <i class="ri-arrow-right-line" aria-hidden="true"></i></a>
    </div>
    <?php if (!$recentTasks) { ?>
      <p class="recent-tasks-empty">No tasks yet.</p>
    <?php } else { ?>
      <div class="recent-tasks-list">
        <?php foreach ($recentTasks as $task) {
          $taskStatusClass = strtolower(str_replace(' ', '-', trim($task['status'])));
          $sharedAccounts = $recentTaskShares[(int) $task['project_id']] ?? [];
          $sharedAccountNames = array_map(static fn($account) => $account['name'], $sharedAccounts);
        ?>
          <a class="recent-task" href="<?= htmlspecialchars('../projects/view.php?project=' . (int) $task['project_id'] . '&task=' . (int) $task['task_id'], ENT_QUOTES, 'UTF-8') ?>">
            <span class="recent-task-icon"><i class="ri-task-line" aria-hidden="true"></i></span>
            <span class="recent-task-main">
              <strong><?= htmlspecialchars($task['task_title'], ENT_QUOTES, 'UTF-8') ?></strong>
              <span><?= htmlspecialchars($task['project_title'], ENT_QUOTES, 'UTF-8') ?></span>
            </span>
            <span class="recent-task-shares" aria-label="<?= htmlspecialchars($sharedAccounts ? 'Shared with: ' . implode(', ', $sharedAccountNames) : 'No shared accounts', ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($sharedAccounts ? 'Shared with: ' . implode(', ', $sharedAccountNames) : 'No shared accounts', ENT_QUOTES, 'UTF-8') ?>">
              <?php if (!$sharedAccounts) { ?>
                <span class="recent-task-no-shares">Not shared</span>
              <?php } else { ?>
                <?php foreach (array_slice($sharedAccounts, 0, 3) as $account) { ?>
                  <img class="recent-task-avatar" src="data:<?= htmlspecialchars($account['profile_type'], ENT_QUOTES, 'UTF-8') ?>;base64,<?= htmlspecialchars($account['profile'], ENT_QUOTES, 'UTF-8') ?>" alt="" title="<?= htmlspecialchars($account['name'], ENT_QUOTES, 'UTF-8') ?>">
                <?php } ?>
                <?php if (count($sharedAccounts) > 3) { ?>
                  <span class="recent-task-avatar-count">+<?= count($sharedAccounts) - 3 ?></span>
                <?php } ?>
              <?php } ?>
            </span>
            <span class="recent-task-status task-status-<?= htmlspecialchars($taskStatusClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($task['status'] ?: 'No status', ENT_QUOTES, 'UTF-8') ?></span>
            <span class="recent-task-date"><?= htmlspecialchars($task['date_created'] ?: 'Date unavailable', ENT_QUOTES, 'UTF-8') ?></span>
            <i class="ri-arrow-right-line recent-task-arrow" aria-hidden="true"></i>
          </a>
        <?php } ?>
      </div>
    <?php } ?>
  </section>
</section>
