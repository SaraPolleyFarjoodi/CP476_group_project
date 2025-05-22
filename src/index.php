<?php 
$greeting = "Hi There! Updates work!";
?>

<p><?php echo htmlspecialchars($greeting); ?></p>

<?php
$dsn = 'mysql:host=db;dbname=myapp;charset=utf8';
$user = 'appuser';
$pass = 'apppass';

try {
    $pdo = new PDO($dsn, $user, $pass);
    echo "<p>✅ Database connected!</p>";

    // Run a real query
    $stmt = $pdo->query("SELECT NOW()");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>🕒 DB Time: " . htmlspecialchars($result['NOW()']) . "</p>";

} catch (PDOException $e) {
    echo "<p>❌ Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
