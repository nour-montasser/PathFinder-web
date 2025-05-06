-- Check if admin user already exists
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM app_user WHERE email = 'admin@pathfinder.tn') THEN
        -- Insert admin user with hashed password (123456)
        INSERT INTO app_user (name, email, password, role, image)
        VALUES ('Administrator', 'admin@pathfinder.tn', '$2y$10$HGhJE/jd8JYXw5F6zJawJew1a5DLPsVJaFHsj0JZBZkIrEJT98RQ.', 3, 'default.png');
    END IF;
END $$; 