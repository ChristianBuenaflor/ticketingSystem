<?php
function renderPageBreadcrumbs($portal)
{
    $currentPath = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
    $portalRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $portal);
    if (!$currentPath || !$portalRoot || strpos($currentPath, $portalRoot . DIRECTORY_SEPARATOR) !== 0) {
        return;
    }

    $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', substr($currentPath, strlen($portalRoot) + 1));
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $portalUrl = dirname(dirname($scriptPath));
    $portalUrl = $portalUrl === '.' ? '' : rtrim($portalUrl, '/');
    $dashboardUrl = $portalUrl . '/dashboard/dashboard.php';
    $breadcrumbs = [['label' => 'Dashboard', 'href' => $dashboardUrl]];

    if ($relativePath === 'dashboard/dashboard.php' || strpos($relativePath, 'dashboard/dashboard copy') === 0) {
        $breadcrumbs[0]['href'] = null;
    } elseif (strpos($relativePath, 'projects/') === 0) {
        $breadcrumbs[] = ['label' => 'Projects', 'href' => $portalUrl . '/projects/list.php'];
        if (strpos($relativePath, 'projects/view') === 0) {
            $projectId = (int) ($_GET['project'] ?? 0);
            $projectTitle = '';
            if ($projectId > 0) {
                $connectionFile = __DIR__ . DIRECTORY_SEPARATOR . 'connections.php';
                if (!is_file($connectionFile)) {
                    throw new RuntimeException('Unable to load project breadcrumb.');
                }
                require $connectionFile;
                if (!isset($con) || !($con instanceof mysqli)) {
                    throw new RuntimeException('Unable to load project breadcrumb.');
                }
                $projectStmt = mysqli_prepare($con, 'SELECT project_title FROM projects WHERE project_id = ?');
                if (!$projectStmt) {
                    throw new RuntimeException('Unable to load project breadcrumb.');
                }
                mysqli_stmt_bind_param($projectStmt, 'i', $projectId);
                if (!mysqli_stmt_execute($projectStmt)) {
                    mysqli_stmt_close($projectStmt);
                    throw new RuntimeException('Unable to load project breadcrumb.');
                }
                $projectResult = mysqli_stmt_get_result($projectStmt);
                $project = $projectResult ? mysqli_fetch_assoc($projectResult) : null;
                mysqli_stmt_close($projectStmt);
                $projectTitle = trim($project['project_title'] ?? '');
            }
            $projectTitle = $projectTitle ?: 'Project details';
            if (function_exists('mb_substr')) {
                $projectTitle = mb_substr($projectTitle, 0, 100, 'UTF-8');
            } else {
                $projectTitle = substr($projectTitle, 0, 100);
            }
            $breadcrumbs[] = ['label' => $projectTitle, 'href' => null];
        } else {
            $breadcrumbs[count($breadcrumbs) - 1]['href'] = null;
        }
    } elseif (strpos($relativePath, 'tickets/') === 0) {
        $breadcrumbs[] = ['label' => 'Tickets', 'href' => $portalUrl . '/tickets/list.php'];
        if (strpos($relativePath, 'tickets/view') === 0) {
            $ticketId = (int) ($_GET['ticketID'] ?? 0);
            $breadcrumbs[] = ['label' => $ticketId ? 'Ticket #' . $ticketId : 'Ticket details', 'href' => null];
        } else {
            $breadcrumbs[count($breadcrumbs) - 1]['href'] = null;
        }
    } elseif (strpos($relativePath, 'users/') === 0) {
        $userPage = basename($relativePath);
        $usersLabel = match ($userPage) {
            'clients.php' => 'Clerks',
            'technicians.php' => 'Clerks',
            'admins.php' => 'Admins',
            'profile.php' => 'User profile',
            'profile-eval.php' => 'User evaluation',
            default => 'Users',
        };
        $breadcrumbs[] = ['label' => $usersLabel, 'href' => null];
    } elseif (strpos($relativePath, 'settings/') === 0) {
        $breadcrumbs[] = ['label' => 'Settings', 'href' => null];
    } elseif (strpos($relativePath, 'evaluations/') === 0) {
        $breadcrumbs[] = ['label' => 'Evaluations', 'href' => null];
    } else {
        return;
    }

    $items = json_encode($breadcrumbs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    ?>
    <script>
      (function() {
        var items = <?= $items ?>;
        var insertBreadcrumb = function() {
          var main = document.querySelector('main.content');
          if (!main || main.querySelector('.page-breadcrumb')) {
            return;
          }

          var nav = document.createElement('nav');
          nav.className = 'page-breadcrumb';
          nav.setAttribute('aria-label', 'Breadcrumb');
          var list = document.createElement('ol');

          items.forEach(function(item, index) {
            var entry = document.createElement('li');
            var isCurrent = index === items.length - 1;
            var label = document.createElement(item.href && !isCurrent ? 'a' : 'span');
            label.textContent = item.label;
            if (item.href && !isCurrent) {
              label.href = item.href;
            } else if (isCurrent) {
              label.setAttribute('aria-current', 'page');
            }
            entry.appendChild(label);

            if (index > 0) {
              var separator = document.createElement('span');
              separator.className = 'breadcrumb-separator';
              separator.setAttribute('aria-hidden', 'true');
              separator.textContent = '/';
              entry.insertBefore(separator, label);
            }
            list.appendChild(entry);
          });

          nav.appendChild(list);
          main.insertBefore(nav, main.firstChild);
        };

        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', insertBreadcrumb, { once: true });
        } else {
          insertBreadcrumb();
        }
      })();
    </script>
    <?php
}
