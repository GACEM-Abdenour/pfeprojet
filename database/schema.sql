-- =============================================
-- GIA Incident Management Platform Database Schema
-- SQL Server Express
-- Created: 2025-02-12
-- =============================================

-- Use the database (create it first if it doesn't exist)
-- CREATE DATABASE GIA_IncidentDB;
-- GO
-- USE GIA_IncidentDB;
-- GO

-- =============================================
-- Table: users
-- Purpose: Store user accounts and authentication data
-- =============================================
IF OBJECT_ID('dbo.users', 'U') IS NOT NULL
    DROP TABLE dbo.users;
GO

CREATE TABLE dbo.users (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username NVARCHAR(100) NOT NULL UNIQUE,
    email NVARCHAR(255) NOT NULL UNIQUE,
    password_hash NVARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL CHECK (role IN ('Reporter', 'Technician', 'Admin')),
    department NVARCHAR(100) NULL,
    created_at DATETIME NOT NULL DEFAULT GETDATE()
);
GO

-- Create index on username for faster login lookups
CREATE INDEX IX_users_username ON dbo.users(username);
GO

-- Create index on email
CREATE INDEX IX_users_email ON dbo.users(email);
GO

-- =============================================
-- Table: incidents
-- Purpose: Store incident tickets
-- =============================================
IF OBJECT_ID('dbo.incidents', 'U') IS NOT NULL
    DROP TABLE dbo.incidents;
GO

CREATE TABLE dbo.incidents (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    assigned_to INT NULL,
    title NVARCHAR(255) NOT NULL,
    description NTEXT NOT NULL,
    category NVARCHAR(50) NOT NULL,
    priority VARCHAR(20) NOT NULL CHECK (priority IN ('Critical', 'Major', 'Minor')),
    status VARCHAR(20) NOT NULL DEFAULT 'Open' CHECK (status IN ('Open', 'Assigned', 'Diagnostic', 'Resolved', 'Closed', 'Failed/Blocked')),
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME NULL,
    closed_at DATETIME NULL,
    
    -- Foreign Key Constraints
    CONSTRAINT FK_incidents_user_id FOREIGN KEY (user_id) 
        REFERENCES dbo.users(id) ON DELETE NO ACTION,
    CONSTRAINT FK_incidents_assigned_to FOREIGN KEY (assigned_to) 
        REFERENCES dbo.users(id) ON DELETE SET NULL
);
GO

-- Create indexes for better query performance
CREATE INDEX IX_incidents_user_id ON dbo.incidents(user_id);
GO

CREATE INDEX IX_incidents_assigned_to ON dbo.incidents(assigned_to);
GO

CREATE INDEX IX_incidents_status ON dbo.incidents(status);
GO

CREATE INDEX IX_incidents_created_at ON dbo.incidents(created_at);
GO

-- =============================================
-- Table: attachments
-- Purpose: Store file attachments for incidents
-- =============================================
IF OBJECT_ID('dbo.attachments', 'U') IS NOT NULL
    DROP TABLE dbo.attachments;
GO

CREATE TABLE dbo.attachments (
    id INT IDENTITY(1,1) PRIMARY KEY,
    incident_id INT NOT NULL,
    file_path NVARCHAR(500) NOT NULL,
    file_name NVARCHAR(255) NOT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT GETDATE(),
    
    -- Foreign Key Constraint
    CONSTRAINT FK_attachments_incident_id FOREIGN KEY (incident_id) 
        REFERENCES dbo.incidents(id) ON DELETE CASCADE
);
GO

-- Create index for faster lookups
CREATE INDEX IX_attachments_incident_id ON dbo.attachments(incident_id);
GO

-- =============================================
-- Table: incident_logs
-- Purpose: Complete traceability of all incident actions
-- =============================================
IF OBJECT_ID('dbo.incident_logs', 'U') IS NOT NULL
    DROP TABLE dbo.incident_logs;
GO

CREATE TABLE dbo.incident_logs (
    id INT IDENTITY(1,1) PRIMARY KEY,
    incident_id INT NOT NULL,
    user_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    message NVARCHAR(500) NULL,
    timestamp DATETIME NOT NULL DEFAULT GETDATE(),
    
    -- Foreign Key Constraints
    CONSTRAINT FK_incident_logs_incident_id FOREIGN KEY (incident_id) 
        REFERENCES dbo.incidents(id) ON DELETE CASCADE,
    CONSTRAINT FK_incident_logs_user_id FOREIGN KEY (user_id) 
        REFERENCES dbo.users(id) ON DELETE NO ACTION
);
GO

-- Create indexes for better query performance
CREATE INDEX IX_incident_logs_incident_id ON dbo.incident_logs(incident_id);
GO

CREATE INDEX IX_incident_logs_user_id ON dbo.incident_logs(user_id);
GO

CREATE INDEX IX_incident_logs_timestamp ON dbo.incident_logs(timestamp);
GO

-- =============================================
-- Optional: Create a trigger to automatically update updated_at in incidents table
-- =============================================
IF OBJECT_ID('dbo.TR_incidents_updated_at', 'TR') IS NOT NULL
    DROP TRIGGER dbo.TR_incidents_updated_at;
GO

CREATE TRIGGER TR_incidents_updated_at
ON dbo.incidents
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE dbo.incidents
    SET updated_at = GETDATE()
    WHERE id IN (SELECT id FROM inserted);
END;
GO

-- =============================================
-- Database Schema Creation Complete
-- =============================================
PRINT 'Database schema created successfully!';
GO
