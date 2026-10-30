<?php

function isManagementRole(string $role): bool
{
    $role = normalizeUserRole($role);
    return in_array($role, ['admin', 'director', 'head'], true);
}

function canAccessTicket(array $ticket, string $role, int $userId): bool
{
    $role = normalizeUserRole($role);
    return isManagementRole($role)
        || ($role === 'clerk'
            && ((int) ($ticket['user_id'] ?? 0) === $userId
                || (int) ($ticket['technician'] ?? 0) === $userId));
}

function canAccessProject(mysqli $connection, int $projectId, string $role, int $userId): bool
{
    $role = normalizeUserRole($role);
    $stmt = $connection->prepare(
        "SELECT projects.created_by, creator.role AS creator_role
         FROM projects
         LEFT JOIN users AS creator ON creator.user_id = projects.created_by
         WHERE projects.project_id = ?"
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $projectId);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }

    $project = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$project) {
        return false;
    }

    if ($role === 'admin') {
        return true;
    }

    if (isManagementRole($role)
        && (!in_array($role, ['director', 'head'], true)
            || empty($project['created_by'])
            || (int) $project['created_by'] === $userId
            || strtolower((string) $project['creator_role']) !== 'admin')) {
        return true;
    }

    if (empty($project['created_by'])) {
        $share = $connection->prepare(
            'SELECT 1 FROM project_shares WHERE project_id = ? AND user_id = ?'
        );
        if (!$share) {
            return false;
        }
        $share->bind_param('ii', $projectId, $userId);
        $hasAccess = $share->execute() && $share->get_result()->num_rows > 0;
        $share->close();
        return $hasAccess;
    }

    $share = $connection->prepare(
        'WITH RECURSIVE access_chain (user_id) AS (
            SELECT created_by FROM projects WHERE project_id = ?
            UNION DISTINCT
            SELECT users.user_id
            FROM projects
            INNER JOIN users AS creator ON creator.user_id = projects.created_by
            CROSS JOIN users
            WHERE projects.project_id = ?
              AND creator.role <> \'admin\'
              AND users.role IN (\'admin\', \'director\', \'head\')
            UNION DISTINCT
            SELECT shares.user_id
            FROM project_shares AS shares
            INNER JOIN access_chain ON shares.shared_by = access_chain.user_id
            WHERE shares.project_id = ?
        )
        SELECT 1 FROM access_chain WHERE user_id = ? LIMIT 1'
    );
    if (!$share) {
        return false;
    }
    $share->bind_param('iiii', $projectId, $projectId, $projectId, $userId);
    $hasAccess = $share->execute() && $share->get_result()->num_rows > 0;
    $share->close();
    return $hasAccess;
}

function getAccessibleProjectIds(mysqli $connection, string $role, int $userId): array|false
{
    $role = normalizeUserRole($role);
    if ($role === 'admin') {
        $result = $connection->query('SELECT project_id FROM projects');
    } else {
        $managementVisibility = isManagementRole($role)
            ? "projects.created_by IS NULL
                OR projects.created_by = $userId
                OR COALESCE(creator.role, '') <> 'admin'
                OR"
            : '';
        $result = $connection->query(
            "WITH RECURSIVE project_access (project_id, user_id) AS (
                SELECT project_id, created_by
                FROM projects
                WHERE created_by IS NOT NULL
                UNION DISTINCT
                SELECT projects.project_id, users.user_id
                FROM projects
                INNER JOIN users AS creator ON creator.user_id = projects.created_by
                CROSS JOIN users
                WHERE creator.role <> 'admin'
                  AND users.role IN ('admin', 'director', 'head')
                UNION DISTINCT
                SELECT projects.project_id, project_shares.user_id
                FROM projects
                INNER JOIN project_shares ON project_shares.project_id = projects.project_id
                WHERE projects.created_by IS NULL
                UNION DISTINCT
                SELECT project_shares.project_id, project_shares.user_id
                FROM project_shares
                INNER JOIN project_access
                    ON project_access.project_id = project_shares.project_id
                   AND project_access.user_id = project_shares.shared_by
            )
            SELECT projects.project_id
            FROM projects
            LEFT JOIN users AS creator ON creator.user_id = projects.created_by
            WHERE $managementVisibility EXISTS (
                SELECT 1
                FROM project_access
                WHERE project_access.project_id = projects.project_id
                  AND project_access.user_id = $userId
            )
            ORDER BY projects.project_id"
        );
    }

    if (!$result) {
        return false;
    }

    $projectIds = [];
    while ($row = $result->fetch_assoc()) {
        $projectIds[] = (int) $row['project_id'];
    }
    return $projectIds;
}

function normalizeUserRole(string $role): string
{
    $role = strtolower(trim($role));

    if (in_array($role, ['admin', 'director', 'head', 'clerk'], true)) {
        return $role;
    }

    if ($role === 'super_admin') {
        return 'admin';
    }

    if (in_array($role, ['client', 'technician'], true)) {
        return 'clerk';
    }

    return '';
}
