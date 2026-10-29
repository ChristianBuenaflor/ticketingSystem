<?php
function loadRecentTaskShares(mysqli $connection, array $recentTasks): array
{
    $projectIds = array_values(array_unique(array_filter(array_map(
        static fn($task) => (int) ($task['project_id'] ?? 0),
        $recentTasks
    ))));
    if (!$projectIds) {
        return [];
    }

    $projectIdList = implode(',', $projectIds);
    $query = mysqli_query($connection, "SELECT project_shares.project_id, users.user_id, users.email,
        users_info.first_name, users_info.last_name, users_info.profile, users_info.profile_type
      FROM project_shares
      INNER JOIN users ON users.user_id = project_shares.user_id
      LEFT JOIN users_info ON users_info.user_id = users.user_id
      WHERE project_shares.project_id IN ($projectIdList)
      ORDER BY users_info.first_name, users.email");
    if (!$query) {
        throw new RuntimeException('Unable to load shared accounts for recent tasks.');
    }

    $defaultProfile = null;
    $sharesByProject = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $profile = $row['profile'] ?? '';
        $profileType = $row['profile_type'] ?? '';
        if ($profile === '') {
            if ($defaultProfile === null) {
                $defaultProfile = file_get_contents(dirname(__DIR__) . '/user/projects/images/Default_pfp.png');
                if ($defaultProfile === false) {
                    throw new RuntimeException('Unable to load the default shared account profile.');
                }
            }
            $profile = $defaultProfile;
            $profileType = 'image/png';
        }
        if (!preg_match('/^[\w.+-]+\/[\w.+-]+$/', $profileType)) {
            $profileType = 'image/png';
        }

        $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        $sharesByProject[(int) $row['project_id']][] = [
            'name' => $name ?: $row['email'],
            'profile' => base64_encode($profile),
            'profile_type' => $profileType,
        ];
    }

    return $sharesByProject;
}
