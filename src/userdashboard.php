<?php
session_start();

if (!isset($_SESSION['db_user']) || !isset($_SESSION['db_pass'])) {
    die("Access denied. Please <a href='index.php'>login</a> first.");
}

// Prevent frontend caching
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

$user = $_SESSION['db_user'];
$pass = $_SESSION['db_pass'];
$host = 'db';
$db   = 'myapp';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle POST actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update'])) {
            // Update Product table
            $stmt = $pdo->prepare("UPDATE Product SET ProductName=?, Description=?, Price=?, Quantity=?, Status=? WHERE ProductID=? AND SupplierID=?");
            $stmt->execute([
                $_POST['name'],
                $_POST['desc'],
                $_POST['price'],
                $_POST['qty'],
                $_POST['status'],
                $_POST['pid'],
                $_POST['sid']
            ]);

            // Get Supplier Name
            $supplierName = $pdo->prepare("SELECT SupplierName FROM Supplier WHERE SupplierID = ?");
            $supplierName->execute([$_POST['sid']]);
            $name = $supplierName->fetchColumn();

            // Update Inventory table
            if ($name) {
                $stmt = $pdo->prepare("UPDATE Inventory SET ProductName=?, Quantity=?, Price=?, Status=? WHERE ProductID=? AND SupplierName=?");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['qty'],
                    $_POST['price'],
                    $_POST['status'],
                    $_POST['pid'],
                    $name
                ]);
            }

        } elseif (isset($_POST['delete'])) {
            // Delete from Product table
            $pdo->prepare("DELETE FROM Product WHERE ProductID = ? AND SupplierID = ?")
                ->execute([$_POST['pid'], $_POST['sid']]);

            // Get Supplier Name for delete from Inventory
            $supplierName = $pdo->prepare("SELECT SupplierName FROM Supplier WHERE SupplierID = ?");
            $supplierName->execute([$_POST['sid']]);
            $name = $supplierName->fetchColumn();

            if ($name) {
                $stmt = $pdo->prepare("DELETE FROM Inventory WHERE ProductID = ? AND SupplierName = ?");
                $stmt->execute([$_POST['pid'], $name]);
            }
        }
    }

    // Load tables for display
    $tableData = [];
    foreach (['Supplier', 'Product'] as $table) {
        $stmt = $pdo->query("SELECT * FROM $table");
        $tableData[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $stmt = $pdo->query("SELECT * FROM Inventory ORDER BY ProductID");
    $tableData['Inventory'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle product search
    $searchResults = [];
    if (isset($_GET['search_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM Product WHERE ProductID = ?");
        $stmt->execute([$_GET['search_id']]);
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("<p style='color:red'>Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .tables { display: flex; gap: 20px; flex-wrap: wrap; }
    table { border-collapse: collapse; min-width: 300px; }
    th, td { border: 1px solid #ccc; padding: 5px; }
    th { background-color: #f2f2f2; }
    form { margin-top: 20px; }
    fieldset { margin-bottom: 20px; }
</style>

<div style="text-align: center;"><h1>CP476 Group Project</h1></div>

<h2>Tables</h2>
<div class="tables">
    <?php foreach ($tableData as $tableName => $rows): ?>
        <div>
            <h3><?= htmlspecialchars($tableName) ?></h3>
            <?php if (count($rows) === 0): ?>
                <p><i>No data found.</i></p>
            <?php else: ?>
                <table>
                    <tr>
                        <?php foreach (array_keys($rows[0]) as $col): ?>
                            <th><?= htmlspecialchars($col) ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($row as $cell): ?>
                                <td><?= htmlspecialchars($cell) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<h2>Manage Data</h2>

<form method="post">
    <fieldset>
        <legend>Update Product</legend>
        Product ID: <input name="pid" required>
        Supplier ID: <input name="sid" required>
        New Name: <input name="name" required>
        New Desc: <input name="desc" required>
        New Price: <input name="price" required>
        New Qty: <input name="qty" required>
        New Status: <input name="status" required>
        <button name="update">Update</button>
    </fieldset>
</form>

<form method="post">
    <fieldset>
        <legend>Delete Product</legend>
        Product ID: <input name="pid" required>
        Supplier ID: <input name="sid" required>
        <button name="delete">Delete</button>
    </fieldset>
</form>

<h2>Search Product</h2>
<form method="get">
    <label for="search_id">Product ID:</label>
    <input name="search_id" required>
    <button type="submit">Search</button>
</form>

<?php if (!empty($searchResults)): ?>
    <h3>Search Results</h3>
    <table>
        <tr>
            <?php foreach (array_keys($searchResults[0]) as $col): ?>
                <th><?= htmlspecialchars($col) ?></th>
            <?php endforeach; ?>
        </tr>
        <?php foreach ($searchResults as $row): ?>
            <tr>
                <?php foreach ($row as $cell): ?>
                    <td><?= htmlspecialchars($cell) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>
<?php elseif (isset($_GET['search_id'])): ?>
    <p><i>No product found with that ID.</i></p>
<?php endif; ?>
