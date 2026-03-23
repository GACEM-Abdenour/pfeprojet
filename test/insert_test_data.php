<?php
/**
 * Insert Test Data - GIA Incident Management Platform
 * Fills database with test users, incidents, attachments, and logs
 * 
 * Usage: php test/insert_test_data.php
 */

require_once __DIR__ . '/../includes/db.php';

echo "=== GIA Test Data Insertion ===\n\n";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception("Database connection failed. Please check db.php configuration.");
    }
    
    echo "[OK] Connected to database\n\n";
    
    // Start transaction
    $pdo->beginTransaction();
    
    // ============================================
    // 1. Insert Test Users
    // ============================================
    echo "Step 1: Inserting test users...\n";
    
    $users = [
        [
            'username' => 'admin',
            'email' => 'admin@naftal.dz',
            'password' => 'admin123',
            'role' => 'Admin',
            'department' => 'Groupe Informatique'
        ],
        [
            'username' => 'tech1',
            'email' => 'tech1@naftal.dz',
            'password' => 'tech123',
            'role' => 'Technician',
            'department' => 'Support IT'
        ],
        [
            'username' => 'tech2',
            'email' => 'tech2@naftal.dz',
            'password' => 'tech123',
            'role' => 'Technician',
            'department' => 'Support IT'
        ],
        [
            'username' => 'reporter1',
            'email' => 'reporter1@naftal.dz',
            'password' => 'user123',
            'role' => 'Reporter',
            'department' => 'Branche Carburants'
        ],
        [
            'username' => 'reporter2',
            'email' => 'reporter2@naftal.dz',
            'password' => 'user123',
            'role' => 'Reporter',
            'department' => 'Branche Carburants'
        ],
        [
            'username' => 'manager1',
            'email' => 'manager1@naftal.dz',
            'password' => 'manager123',
            'role' => 'Admin',
            'department' => 'Direction Générale'
        ]
    ];
    
    $user_ids = [];
    $stmt_check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt_insert = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, role, department, created_at) 
        VALUES (?, ?, ?, ?, ?, GETDATE())
    ");
    
    foreach ($users as $user_data) {
        $stmt_check->execute([$user_data['username']]);
        $existing = $stmt_check->fetch();
        
        if ($existing) {
            $user_ids[$user_data['username']] = $existing['id'];
            echo "  [SKIP] User '{$user_data['username']}' already exists (ID: {$existing['id']})\n";
        } else {
            $password_hash = password_hash($user_data['password'], PASSWORD_DEFAULT);
            $stmt_insert->execute([
                $user_data['username'],
                $user_data['email'],
                $password_hash,
                $user_data['role'],
                $user_data['department']
            ]);
            $user_id = $pdo->lastInsertId();
            $user_ids[$user_data['username']] = $user_id;
            echo "  [OK] Created user: {$user_data['username']} (ID: $user_id, Role: {$user_data['role']})\n";
        }
    }
    
    echo "\n";
    
    // ============================================
    // 2. Insert Test Incidents
    // ============================================
    echo "Step 2: Inserting test incidents...\n";
    
    $incidents = [
        [
            'user_id' => $user_ids['reporter1'],
            'assigned_to' => $user_ids['tech1'],
            'title' => 'Imprimante HP ne fonctionne pas',
            'description' => 'L\'imprimante HP LaserJet dans le bureau 205 ne répond plus. Le voyant d\'erreur clignote en rouge. Déjà essayé de redémarrer l\'imprimante mais le problème persiste.',
            'category' => 'Hardware',
            'priority' => 'Major',
            'status' => 'Diagnostic',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'user_id' => $user_ids['reporter2'],
            'assigned_to' => null,
            'title' => 'Accès refusé au système de gestion',
            'description' => 'Je ne peux plus accéder au système de gestion des stocks. Message d\'erreur: "Accès refusé - Contactez l\'administrateur". Mon compte fonctionnait hier.',
            'category' => 'Access',
            'priority' => 'Critical',
            'status' => 'Open',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'updated_at' => null
        ],
        [
            'user_id' => $user_ids['reporter1'],
            'assigned_to' => $user_ids['tech2'],
            'title' => 'Réseau WiFi lent dans le bâtiment A',
            'description' => 'La connexion WiFi est très lente depuis ce matin dans le bâtiment A. Les pages web mettent beaucoup de temps à charger. Problème intermittent.',
            'category' => 'Network',
            'priority' => 'Major',
            'status' => 'Assigned',
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-4 hours'))
        ],
        [
            'user_id' => $user_ids['reporter2'],
            'assigned_to' => $user_ids['tech1'],
            'title' => 'Erreur dans l\'application Excel',
            'description' => 'L\'application Excel plante régulièrement lors de l\'ouverture de fichiers volumineux. Message d\'erreur: "Excel a rencontré un problème et doit fermer".',
            'category' => 'Software',
            'priority' => 'Minor',
            'status' => 'Resolved',
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'closed_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'user_id' => $user_ids['reporter1'],
            'assigned_to' => null,
            'title' => 'Écran d\'ordinateur noir',
            'description' => 'L\'écran de mon ordinateur reste noir au démarrage. L\'ordinateur semble démarrer (voyants allumés) mais l\'écran ne s\'allume pas.',
            'category' => 'Hardware',
            'priority' => 'Critical',
            'status' => 'Open',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'updated_at' => null
        ],
        [
            'user_id' => $user_ids['reporter2'],
            'assigned_to' => $user_ids['tech2'],
            'title' => 'Problème d\'impression PDF',
            'description' => 'Impossible d\'imprimer des fichiers PDF depuis Adobe Reader. L\'option d\'impression est grisée. Fonctionne avec d\'autres types de fichiers.',
            'category' => 'Software',
            'priority' => 'Minor',
            'status' => 'Diagnostic',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-12 hours'))
        ],
        [
            'user_id' => $user_ids['reporter1'],
            'assigned_to' => $user_ids['tech1'],
            'title' => 'Serveur de fichiers inaccessible',
            'description' => 'Le serveur de fichiers partagés (\\\\fileserver\\departement) n\'est plus accessible depuis ce matin. Erreur: "Le chemin réseau est introuvable".',
            'category' => 'Network',
            'priority' => 'Critical',
            'status' => 'Failed/Blocked',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 week')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'closed_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
        ],
        [
            'user_id' => $user_ids['reporter2'],
            'assigned_to' => $user_ids['tech1'],
            'title' => 'Mise à jour Windows nécessaire',
            'description' => 'Mon ordinateur demande constamment de redémarrer pour installer des mises à jour Windows, mais je ne peux pas le faire maintenant car j\'ai un travail urgent.',
            'category' => 'Software',
            'priority' => 'Minor',
            'status' => 'Closed',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 week')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
            'closed_at' => date('Y-m-d H:i:s', strtotime('-6 days'))
        ]
    ];
    
    $incident_ids = [];
    $stmt_incident = $pdo->prepare("
        INSERT INTO incidents (user_id, assigned_to, title, description, category, priority, status, created_at, updated_at, closed_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($incidents as $incident_data) {
        $stmt_incident->execute([
            $incident_data['user_id'],
            $incident_data['assigned_to'],
            $incident_data['title'],
            $incident_data['description'],
            $incident_data['category'],
            $incident_data['priority'],
            $incident_data['status'],
            $incident_data['created_at'],
            $incident_data['updated_at'] ?? null,
            $incident_data['closed_at'] ?? null
        ]);
        $incident_id = $pdo->lastInsertId();
        $incident_ids[] = $incident_id;
        echo "  [OK] Created incident #$incident_id: {$incident_data['title']} (Status: {$incident_data['status']})\n";
    }
    
    echo "\n";
    
    // ============================================
    // 3. Insert Test Attachments
    // ============================================
    echo "Step 3: Inserting test attachments...\n";
    
    $attachments = [
        [
            'incident_id' => $incident_ids[0],
            'file_path' => 'uploads/incident_' . $incident_ids[0] . '_screenshot1.png',
            'file_name' => 'screenshot_erreur_imprimante.png',
            'uploaded_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'incident_id' => $incident_ids[1],
            'file_path' => 'uploads/incident_' . $incident_ids[1] . '_error_log.txt',
            'file_name' => 'error_log_access_denied.txt',
            'uploaded_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'incident_id' => $incident_ids[2],
            'file_path' => 'uploads/incident_' . $incident_ids[2] . '_network_test.png',
            'file_name' => 'network_speed_test.png',
            'uploaded_at' => date('Y-m-d H:i:s', strtotime('-5 hours'))
        ]
    ];
    
    $stmt_attachment = $pdo->prepare("
        INSERT INTO attachments (incident_id, file_path, file_name, uploaded_at)
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($attachments as $attachment_data) {
        $stmt_attachment->execute([
            $attachment_data['incident_id'],
            $attachment_data['file_path'],
            $attachment_data['file_name'],
            $attachment_data['uploaded_at']
        ]);
        echo "  [OK] Created attachment: {$attachment_data['file_name']} for incident #{$attachment_data['incident_id']}\n";
    }
    
    echo "\n";
    
    // ============================================
    // 4. Insert Test Incident Logs
    // ============================================
    echo "Step 4: Inserting test incident logs...\n";
    
    $logs = [
        [
            'incident_id' => $incident_ids[0],
            'user_id' => $user_ids['tech1'],
            'action_type' => 'Assignment',
            'message' => 'Ticket assigné à tech1',
            'timestamp' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'incident_id' => $incident_ids[0],
            'user_id' => $user_ids['tech1'],
            'action_type' => 'Status Change',
            'message' => 'Statut changé de "Assigned" à "Diagnostic"',
            'timestamp' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'incident_id' => $incident_ids[0],
            'user_id' => $user_ids['tech1'],
            'action_type' => 'Comment',
            'message' => 'Vérifié les câbles et redémarré l\'imprimante. Le problème persiste. En attente de pièce de rechange.',
            'timestamp' => date('Y-m-d H:i:s', strtotime('-12 hours'))
        ],
        [
            'incident_id' => $incident_ids[2],
            'user_id' => $user_ids['tech2'],
            'action_type' => 'Assignment',
            'message' => 'Ticket assigné à tech2',
            'timestamp' => date('Y-m-d H:i:s', strtotime('-4 hours'))
        ],
        [
            'incident_id' => $incident_ids[3],
            'user_id' => $user_ids['tech1'],
            'action_type' => 'Status Change',
            'message' => 'Statut changé de "Diagnostic" à "Resolved"',
            'timestamp' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'incident_id' => $incident_ids[3],
            'user_id' => $user_ids['reporter2'],
            'action_type' => 'Status Change',
            'message' => 'Statut changé de "Resolved" à "Closed" - Problème résolu',
            'timestamp' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'incident_id' => $incident_ids[6],
            'user_id' => $user_ids['tech1'],
            'action_type' => 'Status Change',
            'message' => 'Statut changé à "Failed/Blocked" - Problème nécessite intervention externe',
            'timestamp' => date('Y-m-d H:i:s', strtotime('-5 days'))
        ]
    ];
    
    $stmt_log = $pdo->prepare("
        INSERT INTO incident_logs (incident_id, user_id, action_type, message, timestamp)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($logs as $log_data) {
        $stmt_log->execute([
            $log_data['incident_id'],
            $log_data['user_id'],
            $log_data['action_type'],
            $log_data['message'],
            $log_data['timestamp']
        ]);
        echo "  [OK] Created log entry for incident #{$log_data['incident_id']}: {$log_data['action_type']}\n";
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo "\n";
    echo "=== Test Data Insertion Complete ===\n";
    echo "\nSummary:\n";
    echo "- Users: " . count($user_ids) . "\n";
    echo "- Incidents: " . count($incident_ids) . "\n";
    echo "- Attachments: " . count($attachments) . "\n";
    echo "- Logs: " . count($logs) . "\n";
    echo "\n[OK] All test data inserted successfully!\n";
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n[ERROR] Database Error: " . $e->getMessage() . "\n";
    echo "\nPlease ensure:\n";
    echo "1. SQL Server is running\n";
    echo "2. Database 'GIA_IncidentDB' exists\n";
    echo "3. Tables are created (run setup_database.ps1 first)\n";
    exit(1);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
