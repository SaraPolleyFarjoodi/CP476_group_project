<?php
include("viewTables.php");
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $table = $_POST['table'] ?? '';
    $whereColumn = $_POST['whereColumn'] ?? '';
    $operator = $_POST['sqlOperator'] ?? '';
    $whereValue = $_POST['columnValue'] ?? '';
    $user = $_SESSION['db_user'];
    $pass = $_SESSION['db_pass'];

    try {
        $conn = new PDO('mysql:host=db;dbname=myapp;charset=utf8', $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($action === 'delete') {
            $sql = "DELETE FROM `$table` WHERE `$whereColumn` $operator :val";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':val', $whereValue);
            $stmt->execute();
        } elseif ($action === 'update') {
            $updateColumn = $_POST['updateColumn'] ?? '';
            $updateValue = $_POST['updateValue'] ?? '';

            $sql = "UPDATE `$table` SET `$updateColumn` = :newVal WHERE `$whereColumn` $operator :val";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':newVal', $updateValue);
            $stmt->bindParam(':val', $whereValue);
            $stmt->execute();
        }

        // Refresh the page to show updates
        echo "<meta http-equiv='refresh' content='0'>";
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<h2>Delete</h2>
<?php if($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error)?></p>
<?php endif; ?>
<form method="post" action="userDashboard.php">
    <input type="hidden" name="action" value="delete">
    
    <label for="table">DELETE FROM</label>
    <input type="text" id="table" name="table" required><br><br>
    
    <label for="whereColumn">WHERE</label>
    <input type="text" id="whereColumn" name="whereColumn" required>
    
    <select name="sqlOperator" required>
        <option value="=">=</option>
        <option value="!=">!=</option>
        <option value=">">&gt;</option>
        <option value="<">&lt;</option>
        <option value=">=">&gt;=</option>
        <option value="<=">&lt;=</option>
        <option value="LIKE">LIKE</option>
        <option value="NOT LIKE">NOT LIKE</option>
    </select>
    
    <input type="text" id="columnValue" name="columnValue" required><br><br>
    
    <input type="submit" value="Delete">
</form>


<hr>

<h2>Update</h2>
<form method="post" action="userDashboard.php">
    <input type="hidden" name="action" value="update">

    <label for="table">UPDATE</label>
    <input type="text" id="table" name="table" required><br><br>

    <label for="updateColumn">SET</label>
    <input type="text" id="updateColumn" name="updateColumn" required>
    =
    <input type="text" id="updateValue" name="updateValue" required><br><br>

    <label for="whereColumn">WHERE</label>
    <input type="text" id="whereColumn" name="whereColumn" required>

    <select name="sqlOperator" required>
        <option value="=">=</option>
        <option value="!=">!=</option>
        <option value=">">&gt;</option>
        <option value="<">&lt;</option>
        <option value=">=">&gt;=</option>
        <option value="<=">&lt;=</option>
        <option value="LIKE">LIKE</option>
        <option value="NOT LIKE">NOT LIKE</option>
    </select>

    <input type="text" id="columnValue" name="columnValue" required><br><br>

    <input type="submit" value="Update">
</form>
