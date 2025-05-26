<?php

require_once __DIR__ . '/config/db.php';

// Connect to DB
$conn = (new Database())->connection;

// --- Helper functions ---
function createUser($conn, $name, $email, $password, $role = 'agent') {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $name, $email, $hash, $role);
    $stmt->execute();
    return $conn->insert_id;
}

function createDepartment($conn, $name) {
    $stmt = $conn->prepare("INSERT INTO departments (name) VALUES (?)");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    return $conn->insert_id;
}

function createTicket($conn, $title, $description, $status, $userId, $departmentId, $filePath = null) {
    $stmt = $conn->prepare("INSERT INTO tickets (title, description, status, user_id, department_id, file_path)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssiss', $title, $description, $status, $userId, $departmentId, $filePath);
    $stmt->execute();
    return $conn->insert_id;
}

function createNote($conn, $ticketId, $userId, $note) {
    $stmt = $conn->prepare("INSERT INTO ticket_notes (ticket_id, user_id, note) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $ticketId, $userId, $note);
    $stmt->execute();
}


$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE ticket_notes");
$conn->query("TRUNCATE tickets");
$conn->query("TRUNCATE departments");
$conn->query("TRUNCATE users");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "Seeding data...\n";

$adminId = createUser($conn, 'Admin User', 'admin@example.com', 'admin123', 'admin');
$agentId1 = createUser($conn, 'Agent One', 'agent1@example.com', 'secret123');
$agentId2 = createUser($conn, 'Agent Two', 'agent2@example.com', 'secret123');

$dept1 = createDepartment($conn, 'Support');
$dept2 = createDepartment($conn, 'Billing');

$ticket1 = createTicket($conn, 'Login Issue', 'Cannot log into account.', 'open', $agentId1, $dept1);
$ticket2 = createTicket($conn, 'Invoice Problem', 'Incorrect charge on invoice.', 'in_progress', $agentId2, $dept2);

createNote($conn, $ticket1, $adminId, 'Escalated to tech team.');
createNote($conn, $ticket2, $agentId2, 'Requested additional invoice details.');

echo "Seeding complete!\n";
