<?php
session_start();

//prevent people from typing the URL to the dashbaord page directly before logging in
if (!isset($_SESSION['db_user']) || !isset($_SESSION['db_pass'])) { //check if username and password are delcared and different than NULL
    die("Access denied. Please <a href='index.php'>login</a> first."); //provide message to user and link to the login page
}

//login process should have saved the credentials in the session using the super global variable so we can access them here
$user = $_SESSION['db_user'];
$pass = $_SESSION['db_pass'];

//access the database and fetch data from the tables
try {
    $pdo = new PDO('mysql:host=db;dbname=myapp;charset=utf8', $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //sample code to fetch data from MySQL database
    $tables = ['NameTable', 'CourseTable', 'FinalGradeTable'];

    foreach ($tables as $table) {
        echo "<h3>$table</h3>";
        $stmt = $pdo->query("SELECT * FROM $table");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) === 0) {
            echo "<p><i>No data found.</i></p>";
        } else {
            echo "<table border='1' cellpadding='5'><tr>";
            foreach (array_keys($rows[0]) as $col) {
                echo "<th>$col</th>";
            }
            echo "</tr>";
            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    }

} catch (PDOException $e) {
    echo "Failed to connect or fetch data.";
}
?>