<?php
$dsn = 'mysql:host=db;dbname=myapp;charset=utf8';
$user = 'appuser';
$pass = 'apppass';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Drop tables in proper order
    $tables = ['FinalGradeTable', 'CourseTable', 'NameTable'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
    }

    // Create tables
    $pdo->exec("
        CREATE TABLE NameTable (
            StudentID BIGINT PRIMARY KEY,
            StudentName VARCHAR(100)
        )
    ");

    $pdo->exec("
        CREATE TABLE CourseTable (
            StudentID BIGINT,
            CourseCode VARCHAR(20),
            Test1 DECIMAL(5,2),
            Test2 DECIMAL(5,2),
            Test3 DECIMAL(5,2),
            FinalExam DECIMAL(5,2),
            PRIMARY KEY (StudentID, CourseCode)
        )
    ");

    $pdo->exec("
        CREATE TABLE FinalGradeTable (
            StudentID BIGINT,
            StudentName VARCHAR(100),
            CourseCode VARCHAR(20),
            FinalGrade DECIMAL(5,1),
            PRIMARY KEY (StudentID, CourseCode)
        )
    ");

    // Insert into NameTable using prepared statement
    $insertName = $pdo->prepare("INSERT INTO NameTable (StudentID, StudentName) VALUES (?, ?)");
    $nameData = [
        [123456789, 'John Hay'],
        [223456789, 'Mary Smith'],
        [323456789, 'Alex Kim']
    ];
    foreach ($nameData as $row) {
        $insertName->execute($row);
    }

    // Insert into CourseTable using prepared statement
    $insertCourse = $pdo->prepare("
        INSERT INTO CourseTable (StudentID, CourseCode, Test1, Test2, Test3, FinalExam)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $courseData = [
        [123456789, 'CP460', 60.5, 70.6, 80.6, 80.6],
        [223456789, 'CP414', 80.2, 90.5, 50.4, 75.6],
        [323456789, 'CP317', 90.0, 85.0, 88.0, 92.0]
    ];
    foreach ($courseData as $row) {
        $insertCourse->execute($row);
    }

    // Query final grades
    $stmt = $pdo->query("
        SELECT 
            c.StudentID,
            n.StudentName,
            c.CourseCode,
            ROUND((c.Test1 + c.Test2 + c.Test3) * 0.2 + c.FinalExam * 0.4, 1) AS FinalGrade
        FROM CourseTable c
        JOIN NameTable n ON c.StudentID = n.StudentID
    ");

    // Insert into FinalGradeTable using prepared statement
    $insertFinal = $pdo->prepare("
        INSERT INTO FinalGradeTable (StudentID, StudentName, CourseCode, FinalGrade)
        VALUES (?, ?, ?, ?)
    ");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $insertFinal->execute($row);
    }

    echo "<p>✅ Tables created and data inserted using prepared statements!</p>";

} catch (PDOException $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
