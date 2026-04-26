ALTER TABLE users
    MODIFY COLUMN role ENUM('admin', 'designer', 'staff', 'manager') NOT NULL DEFAULT 'designer';

UPDATE users
SET role = 'designer'
WHERE role = 'staff';

ALTER TABLE users
    MODIFY COLUMN role ENUM('admin', 'designer', 'manager') NOT NULL DEFAULT 'designer';
