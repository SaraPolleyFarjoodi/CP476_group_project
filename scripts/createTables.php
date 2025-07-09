<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Access denied.');
}

$dsn = 'mysql:host=db;dbname=myapp;charset=utf8';
$user = $_ENV['MYSQL_USER'] ?? '';
$pass = $_ENV['MYSQL_PASSWORD'] ?? '';
$pdo = new PDO($dsn, $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("
    CREATE TABLE IF NOT EXISTS Supplier (
        SupplierID INT PRIMARY KEY,
        SupplierName VARCHAR(255),
        Address VARCHAR(255),
        Phone VARCHAR(50),
        Email VARCHAR(255)
    );
    
    CREATE TABLE IF NOT EXISTS Product (
        ProductID INT,
        ProductName VARCHAR(255),
        Description VARCHAR(255),
        Price DECIMAL(10,2),
        Quantity INT,
        Status CHAR(1),
        SupplierID INT,
        PRIMARY KEY (ProductID, SupplierID),
        FOREIGN KEY (SupplierID) REFERENCES Supplier(SupplierID)
    );

    CREATE TABLE IF NOT EXISTS Inventory (
        ProductID INT,
        ProductName VARCHAR(255),
        Quantity INT,
        Price DECIMAL(10,2),
        Status CHAR(1),
        SupplierName VARCHAR(255)
    );
");

$supplierFile = __DIR__ . '/data/Supplier.txt';
if (!file_exists($supplierFile)) exit("Missing Supplier.txt\n");

$stmt = $pdo->prepare("INSERT INTO Supplier VALUES (?, ?, ?, ?, ?)");
foreach (file($supplierFile) as $line) {
    $fields = array_map('trim', explode(',', $line));
    if (count($fields) === 5) $stmt->execute($fields);
}

$productFile = __DIR__ . '/data/Product.txt';
if (!file_exists($productFile)) exit("Missing Product.txt\n");

$prodStmt = $pdo->prepare("
    INSERT INTO Product (ProductID, ProductName, Description, Price, Quantity, Status, SupplierID)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$invStmt = $pdo->prepare("
    INSERT INTO Inventory (ProductID, ProductName, Quantity, Price, Status, SupplierName)
    VALUES (?, ?, ?, ?, ?, ?)
");

$getSupplierName = $pdo->prepare("SELECT SupplierName FROM Supplier WHERE SupplierID = ?");

foreach (file($productFile) as $line) {
    $fields = array_map('trim', explode(',', $line));
    if (count($fields) !== 7) continue;

    [$pid, $name, $desc, $price, $qty, $status, $sid] = $fields;
    $prodStmt->execute([$pid, $name, $desc, $price, $qty, $status, $sid]);

    $getSupplierName->execute([$sid]);
    $supplierName = $getSupplierName->fetchColumn();

    if ($supplierName) {
        $invStmt->execute([$pid, $name, $qty, $price, $status, $supplierName]);
    }
}

echo "✅ Tables created and data inserted.\n";
