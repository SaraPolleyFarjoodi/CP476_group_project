<?php
session_start();

if (!isset($_SESSION['db_user']) || !isset($_SESSION['db_pass'])) {
    die("Access denied. Please <a href='index.php'>login</a> first.");
}

$user = $_SESSION['db_user'];
$pass = $_SESSION['db_pass'];
$host = 'db';
$db   = 'myapp';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle POST actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add'])) {
            $stmt = $pdo->prepare("INSERT INTO Product (ProductID, ProductName, Description, Price, Quantity, Status, SupplierID) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['pid'], $_POST['name'], $_POST['desc'], $_POST['price'], $_POST['qty'], $_POST['status'], $_POST['sid']]);

            $supplierName = $pdo->prepare("SELECT SupplierName FROM Supplier WHERE SupplierID = ?");
            $supplierName->execute([$_POST['sid']]);
            $name = $supplierName->fetchColumn();
            if ($name) {
                $stmt = $pdo->prepare("INSERT INTO Inventory (ProductID, ProductName, Quantity, Price, Status, SupplierName) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_POST['pid'], $_POST['name'], $_POST['qty'], $_POST['price'], $_POST['status'], $name]);
            }
        } elseif (isset($_POST['update'])) {
            $stmt = $pdo->prepare("UPDATE Product SET ProductName=?, Description=?, Price=?, Quantity=?, Status=?, SupplierID=? WHERE ProductID=?");
            $stmt->execute([$_POST['name'], $_POST['desc'], $_POST['price'], $_POST['qty'], $_POST['status'], $_POST['sid'], $_POST['pid']]);

            $supplierName = $pdo->prepare("SELECT SupplierName FROM Supplier WHERE SupplierID = ?");
            $supplierName->execute([$_POST['sid']]);
            $name = $supplierName->fetchColumn();
            if ($name) {
                $stmt = $pdo->prepare("UPDATE Inventory SET ProductName=?, Quantity=?, Price=?, Status=?, SupplierName=? WHERE ProductID=?");
                $stmt->execute([$_POST['name'], $_POST['qty'], $_POST['price'], $_POST['status'], $name, $_POST['pid']]);
            }
        } elseif (isset($_POST['delete'])) {
            $pdo->prepare("DELETE FROM Product WHERE ProductID = ?")->execute([$_POST['pid']]);
            $pdo->prepare("DELETE FROM Inventory WHERE ProductID = ?")->execute([$_POST['pid']]);
        }
    }

    // Load tables for display
    $tableData = [];
    foreach (['Supplier', 'Product', 'Inventory'] as $table) {
        $stmt = $pdo->query("SELECT * FROM $table");
        $tableData[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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
        <legend>Add Product</legend>
        ID: <input name="pid" required>
        Name: <input name="name" required>
        Desc: <input name="desc" required>
        Price: <input name="price" required>
        Qty: <input name="qty" required>
        Status: <input name="status" required>
        Supplier ID: <input name="sid" required>
        <button name="add">Add</button>
    </fieldset>
</form>

<form method="post">
    <fieldset>
        <legend>Update Product</legend>
        ID (to update): <input name="pid" required>
        New Name: <input name="name" required>
        New Desc: <input name="desc" required>
        New Price: <input name="price" required>
        New Qty: <input name="qty" required>
        New Status: <input name="status" required>
        New Supplier ID: <input name="sid" required>
        <button name="update">Update</button>
    </fieldset>
</form>

<form method="post">
    <fieldset>
        <legend>Delete Product</legend>
        ID: <input name="pid" required>
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

</body>
</html>
