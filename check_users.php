<?php
/**
 * Check users in database
 */
require_once __DIR__ . '/config/db_connect.php';

echo "<h2>Users in Database</h2>";

$result = $conn->query("SELECT id, username, email FROM users ORDER BY id");

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Username</th><th>Email</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h3>Test Email Lookup</h3>";
echo "<form method='GET'>";
echo "<input type='email' name='test_email' placeholder='Enter email to test' required>";
echo "<button type='submit'>Check</button>";
echo "</form>";

if (isset($_GET['test_email'])) {
    $email = $_GET['test_email'];
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "<p style='color:green'>✅ FOUND: " . htmlspecialchars($user['email']) . " (ID: " . $user['id'] . ")</p>";
    } else {
        echo "<p style='color:red'>❌ NOT FOUND: " . htmlspecialchars($email) . "</p>";
        
        // Try case-insensitive search
        $stmt2 = $conn->prepare("SELECT id, username, email FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt2->bind_param("s", $email);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        
        if ($result2->num_rows > 0) {
            $user = $result2->fetch_assoc();
            echo "<p style='color:orange'>⚠️ But found with different case: " . htmlspecialchars($user['email']) . "</p>";
        }
    }
}
?>
