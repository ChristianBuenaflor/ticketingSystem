UPDATE users
SET role = CASE
    WHEN role = 'super_admin' THEN 'admin'
    WHEN role IN ('client', 'technician') THEN 'clerk'
    ELSE role
END
WHERE role IN ('super_admin', 'client', 'technician');
