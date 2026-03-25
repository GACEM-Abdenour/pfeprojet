-- Test Data Insertion Script for GIA Incident Management Platform
-- Run this in SQL Server Management Studio or via sqlcmd
-- Note: Passwords are hashed using SQL Server's HASHBYTES (for testing only)
-- For production, use PHP password_hash() which creates bcrypt hashes

USE GIA_IncidentDB;
GO

-- Clear existing test data (optional - comment out if you want to keep existing data)
-- DELETE FROM incident_logs;
-- DELETE FROM attachments;
-- DELETE FROM incidents;
-- DELETE FROM users WHERE username IN ('admin', 'tech1', 'tech2', 'reporter1', 'reporter2');
-- GO

-- ============================================
-- Insert Test Users
-- ============================================

-- Note: For simplicity in this SQL-only script, ALL test users share the same password:
--   Password: password
-- The hash used below is the bcrypt hash of the string 'password'.
-- For production, use PHP password_hash() to generate per-user hashes.

-- Admin Users
IF NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin')
BEGIN
    INSERT INTO users (username, email, password_hash, role, department, created_at)
    VALUES ('admin', 'admin@naftal.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Groupe Informatique', GETDATE());
    PRINT 'Created user: admin';
END
ELSE
    PRINT 'User admin already exists';

-- Technician Users
IF NOT EXISTS (SELECT 1 FROM users WHERE username = 'tech1')
BEGIN
    INSERT INTO users (username, email, password_hash, role, department, created_at)
    VALUES ('tech1', 'tech1@naftal.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Technician', 'Support IT', GETDATE());
    PRINT 'Created user: tech1';
END
ELSE
    PRINT 'User tech1 already exists';

IF NOT EXISTS (SELECT 1 FROM users WHERE username = 'tech2')
BEGIN
    INSERT INTO users (username, email, password_hash, role, department, created_at)
    VALUES ('tech2', 'tech2@naftal.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Technician', 'Support IT', GETDATE());
    PRINT 'Created user: tech2';
END
ELSE
    PRINT 'User tech2 already exists';

-- Reporter Users
IF NOT EXISTS (SELECT 1 FROM users WHERE username = 'reporter1')
BEGIN
    INSERT INTO users (username, email, password_hash, role, department, created_at)
    VALUES ('reporter1', 'reporter1@naftal.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Reporter', 'Branche Carburants', GETDATE());
    PRINT 'Created user: reporter1';
END
ELSE
    PRINT 'User reporter1 already exists';

IF NOT EXISTS (SELECT 1 FROM users WHERE username = 'reporter2')
BEGIN
    INSERT INTO users (username, email, password_hash, role, department, created_at)
    VALUES ('reporter2', 'reporter2@naftal.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Reporter', 'Branche Carburants', GETDATE());
    PRINT 'Created user: reporter2';
END
ELSE
    PRINT 'User reporter2 already exists';

GO

-- ============================================
-- Insert Test Incidents
-- ============================================

DECLARE @user_reporter1 INT = (SELECT id FROM users WHERE username = 'reporter1');
DECLARE @user_reporter2 INT = (SELECT id FROM users WHERE username = 'reporter2');
DECLARE @user_tech1 INT = (SELECT id FROM users WHERE username = 'tech1');
DECLARE @user_tech2 INT = (SELECT id FROM users WHERE username = 'tech2');

-- Incident 1: Imprimante HP ne fonctionne pas
IF NOT EXISTS (SELECT 1 FROM incidents WHERE title = 'Imprimante HP ne fonctionne pas')
BEGIN
    INSERT INTO incidents (user_id, assigned_to, title, description, category, priority, status, created_at, updated_at)
    VALUES (@user_reporter1, @user_tech1, 'Imprimante HP ne fonctionne pas', 
            'L''imprimante HP LaserJet dans le bureau 205 ne répond plus. Le voyant d''erreur clignote en rouge. Déjà essayé de redémarrer l''imprimante mais le problème persiste.',
            'Hardware', 'Major', 'Diagnostic', DATEADD(day, -2, GETDATE()), DATEADD(day, -1, GETDATE()));
    PRINT 'Created incident: Imprimante HP ne fonctionne pas';
END

-- Incident 2: Accès refusé au système de gestion
IF NOT EXISTS (SELECT 1 FROM incidents WHERE title = 'Accès refusé au système de gestion')
BEGIN
    INSERT INTO incidents (user_id, assigned_to, title, description, category, priority, status, created_at)
    VALUES (@user_reporter2, NULL, 'Accès refusé au système de gestion',
            'Je ne peux plus accéder au système de gestion des stocks. Message d''erreur: "Accès refusé - Contactez l''administrateur". Mon compte fonctionnait hier.',
            'Access', 'Critical', 'Open', DATEADD(day, -1, GETDATE()));
    PRINT 'Created incident: Accès refusé au système de gestion';
END

-- Incident 3: Réseau WiFi lent
IF NOT EXISTS (SELECT 1 FROM incidents WHERE title = 'Réseau WiFi lent dans le bâtiment A')
BEGIN
    INSERT INTO incidents (user_id, assigned_to, title, description, category, priority, status, created_at, updated_at)
    VALUES (@user_reporter1, @user_tech2, 'Réseau WiFi lent dans le bâtiment A',
            'La connexion WiFi est très lente depuis ce matin dans le bâtiment A. Les pages web mettent beaucoup de temps à charger. Problème intermittent.',
            'Network', 'Major', 'Assigned', DATEADD(hour, -5, GETDATE()), DATEADD(hour, -4, GETDATE()));
    PRINT 'Created incident: Réseau WiFi lent dans le bâtiment A';
END

-- Incident 4: Erreur Excel
IF NOT EXISTS (SELECT 1 FROM incidents WHERE title = 'Erreur dans l''application Excel')
BEGIN
    INSERT INTO incidents (user_id, assigned_to, title, description, category, priority, status, created_at, updated_at, closed_at)
    VALUES (@user_reporter2, @user_tech1, 'Erreur dans l''application Excel',
            'L''application Excel plante régulièrement lors de l''ouverture de fichiers volumineux. Message d''erreur: "Excel a rencontré un problème et doit fermer".',
            'Software', 'Minor', 'Resolved', DATEADD(day, -3, GETDATE()), DATEADD(day, -1, GETDATE()), DATEADD(day, -1, GETDATE()));
    PRINT 'Created incident: Erreur dans l''application Excel';
END

-- Incident 5: Écran noir
IF NOT EXISTS (SELECT 1 FROM incidents WHERE title = 'Écran d''ordinateur noir')
BEGIN
    INSERT INTO incidents (user_id, assigned_to, title, description, category, priority, status, created_at)
    VALUES (@user_reporter1, NULL, 'Écran d''ordinateur noir',
            'L''écran de mon ordinateur reste noir au démarrage. L''ordinateur semble démarrer (voyants allumés) mais l''écran ne s''allume pas.',
            'Hardware', 'Critical', 'Open', DATEADD(hour, -2, GETDATE()));
    PRINT 'Created incident: Écran d''ordinateur noir';
END

-- Incident 6: Problème PDF
IF NOT EXISTS (SELECT 1 FROM incidents WHERE title = 'Problème d''impression PDF')
BEGIN
    INSERT INTO incidents (user_id, assigned_to, title, description, category, priority, status, created_at, updated_at)
    VALUES (@user_reporter2, @user_tech2, 'Problème d''impression PDF',
            'Impossible d''imprimer des fichiers PDF depuis Adobe Reader. L''option d''impression est grisée. Fonctionne avec d''autres types de fichiers.',
            'Software', 'Minor', 'Diagnostic', DATEADD(day, -1, GETDATE()), DATEADD(hour, -12, GETDATE()));
    PRINT 'Created incident: Problème d''impression PDF';
END

-- Incident 7: Serveur inaccessible
IF NOT EXISTS (SELECT 1 FROM incidents WHERE title = 'Serveur de fichiers inaccessible')
BEGIN
    INSERT INTO incidents (user_id, assigned_to, title, description, category, priority, status, created_at, updated_at, closed_at)
    VALUES (@user_reporter1, @user_tech1, 'Serveur de fichiers inaccessible',
            'Le serveur de fichiers partagés (\\fileserver\departement) n''est plus accessible depuis ce matin. Erreur: "Le chemin réseau est introuvable".',
            'Network', 'Critical', 'Failed/Blocked', DATEADD(day, -7, GETDATE()), DATEADD(day, -5, GETDATE()), DATEADD(day, -5, GETDATE()));
    PRINT 'Created incident: Serveur de fichiers inaccessible';
END

-- Incident 8: Mise à jour Windows
IF NOT EXISTS (SELECT 1 FROM incidents WHERE title = 'Mise à jour Windows nécessaire')
BEGIN
    INSERT INTO incidents (user_id, assigned_to, title, description, category, priority, status, created_at, updated_at, closed_at)
    VALUES (@user_reporter2, @user_tech1, 'Mise à jour Windows nécessaire',
            'Mon ordinateur demande constamment de redémarrer pour installer des mises à jour Windows, mais je ne peux pas le faire maintenant car j''ai un travail urgent.',
            'Software', 'Minor', 'Closed', DATEADD(day, -7, GETDATE()), DATEADD(day, -6, GETDATE()), DATEADD(day, -6, GETDATE()));
    PRINT 'Created incident: Mise à jour Windows nécessaire';
END

GO

-- ============================================
-- Insert Test Attachments
-- ============================================

DECLARE @incident1 INT = (SELECT id FROM incidents WHERE title = 'Imprimante HP ne fonctionne pas');
DECLARE @incident2 INT = (SELECT id FROM incidents WHERE title = 'Accès refusé au système de gestion');
DECLARE @incident3 INT = (SELECT id FROM incidents WHERE title = 'Réseau WiFi lent dans le bâtiment A');

IF @incident1 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM attachments WHERE incident_id = @incident1)
BEGIN
    INSERT INTO attachments (incident_id, file_path, file_name, uploaded_at)
    VALUES (@incident1, 'uploads/incident_' + CAST(@incident1 AS VARCHAR) + '_screenshot1.png', 'screenshot_erreur_imprimante.png', DATEADD(day, -2, GETDATE()));
    PRINT 'Created attachment for incident 1';
END

IF @incident2 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM attachments WHERE incident_id = @incident2)
BEGIN
    INSERT INTO attachments (incident_id, file_path, file_name, uploaded_at)
    VALUES (@incident2, 'uploads/incident_' + CAST(@incident2 AS VARCHAR) + '_error_log.txt', 'error_log_access_denied.txt', DATEADD(day, -1, GETDATE()));
    PRINT 'Created attachment for incident 2';
END

IF @incident3 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM attachments WHERE incident_id = @incident3)
BEGIN
    INSERT INTO attachments (incident_id, file_path, file_name, uploaded_at)
    VALUES (@incident3, 'uploads/incident_' + CAST(@incident3 AS VARCHAR) + '_network_test.png', 'network_speed_test.png', DATEADD(hour, -5, GETDATE()));
    PRINT 'Created attachment for incident 3';
END

GO

-- ============================================
-- Insert Test Incident Logs
-- ============================================

DECLARE @incident1_log INT = (SELECT id FROM incidents WHERE title = 'Imprimante HP ne fonctionne pas');
DECLARE @incident3_log INT = (SELECT id FROM incidents WHERE title = 'Réseau WiFi lent dans le bâtiment A');
DECLARE @incident4_log INT = (SELECT id FROM incidents WHERE title = 'Erreur dans l''application Excel');
DECLARE @incident7_log INT = (SELECT id FROM incidents WHERE title = 'Serveur de fichiers inaccessible');

IF @incident1_log IS NOT NULL
BEGIN
    IF NOT EXISTS (SELECT 1 FROM incident_logs WHERE incident_id = @incident1_log AND action_type = 'Assignment')
    BEGIN
        INSERT INTO incident_logs (incident_id, user_id, action_type, message, timestamp)
        VALUES (@incident1_log, @user_tech1, 'Assignment', 'Ticket assigné à tech1', DATEADD(day, -2, GETDATE()));
    END
    
    IF NOT EXISTS (SELECT 1 FROM incident_logs WHERE incident_id = @incident1_log AND action_type = 'Status Change')
    BEGIN
        INSERT INTO incident_logs (incident_id, user_id, action_type, message, timestamp)
        VALUES (@incident1_log, @user_tech1, 'Status Change', 'Statut changé de "Assigned" à "Diagnostic"', DATEADD(day, -1, GETDATE()));
    END
    
    IF NOT EXISTS (SELECT 1 FROM incident_logs WHERE incident_id = @incident1_log AND action_type = 'Comment')
    BEGIN
        INSERT INTO incident_logs (incident_id, user_id, action_type, message, timestamp)
        VALUES (@incident1_log, @user_tech1, 'Comment', 'Vérifié les câbles et redémarré l''imprimante. Le problème persiste. En attente de pièce de rechange.', DATEADD(hour, -12, GETDATE()));
    END
END

IF @incident3_log IS NOT NULL AND NOT EXISTS (SELECT 1 FROM incident_logs WHERE incident_id = @incident3_log)
BEGIN
    INSERT INTO incident_logs (incident_id, user_id, action_type, message, timestamp)
    VALUES (@incident3_log, @user_tech2, 'Assignment', 'Ticket assigné à tech2', DATEADD(hour, -4, GETDATE()));
END

IF @incident4_log IS NOT NULL
BEGIN
    IF NOT EXISTS (SELECT 1 FROM incident_logs WHERE incident_id = @incident4_log AND action_type = 'Status Change' AND message LIKE '%Resolved%')
    BEGIN
        INSERT INTO incident_logs (incident_id, user_id, action_type, message, timestamp)
        VALUES (@incident4_log, @user_tech1, 'Status Change', 'Statut changé de "Diagnostic" à "Resolved"', DATEADD(day, -1, GETDATE()));
    END
    
    IF NOT EXISTS (SELECT 1 FROM incident_logs WHERE incident_id = @incident4_log AND action_type = 'Status Change' AND message LIKE '%Closed%')
    BEGIN
        INSERT INTO incident_logs (incident_id, user_id, action_type, message, timestamp)
        VALUES (@incident4_log, @user_reporter2, 'Status Change', 'Statut changé de "Resolved" à "Closed" - Problème résolu', DATEADD(day, -1, GETDATE()));
    END
END

IF @incident7_log IS NOT NULL AND NOT EXISTS (SELECT 1 FROM incident_logs WHERE incident_id = @incident7_log)
BEGIN
    INSERT INTO incident_logs (incident_id, user_id, action_type, message, timestamp)
    VALUES (@incident7_log, @user_tech1, 'Status Change', 'Statut changé à "Failed/Blocked" - Problème nécessite intervention externe', DATEADD(day, -5, GETDATE()));
END

GO

PRINT '';
PRINT '=== Test Data Insertion Complete ===';
PRINT '';
PRINT 'Summary:';
PRINT '- Users: Check with: SELECT COUNT(*) FROM users';
PRINT '- Incidents: Check with: SELECT COUNT(*) FROM incidents';
PRINT '- Attachments: Check with: SELECT COUNT(*) FROM attachments';
PRINT '- Logs: Check with: SELECT COUNT(*) FROM incident_logs';
PRINT '';
PRINT 'Test Credentials (all users): password';
PRINT '  admin, tech1, tech2, reporter1, reporter2';
PRINT '';
PRINT 'NOTE: These passwords use a test hash. For production, use PHP password_hash()';
PRINT '';
