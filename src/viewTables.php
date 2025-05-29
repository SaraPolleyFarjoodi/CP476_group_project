<?php
session_start();

if (!isset($_SESSION['db_user']) || !isset($_SESSION['db_pass'])) {
    die("Access denied. Please <a href='index.php'>login</a> first.");
}

$user = $_SESSION['db_user'];
$pass = $_SESSION['db_pass'];

try {
    $pdo = new PDO('mysql:host=db;dbname=myapp;charset=utf8', $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT nt.StudentID, nt.StudentName, ct.CourseCode, ct.Test1, ct.Test2, ct.Test3, ct.FinalExam FROM NameTable AS nt JOIN CourseTable AS ct ON nt.StudentID = ct.StudentID");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <h2>Table View</h2>

    <style>
      input[type="radio"] {
        display: none;
      }

      label {
        border: 1px solid black;
        padding: 5px 10px;
        margin-right: 5px;
        cursor: pointer;
      }

      #tab1:checked ~ #content1,
      #tab2:checked ~ #content2 {
        display: block;
      }

      .content {
        display: none;
        margin-top: 10px;
      }

      .table-row {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
      }

      .table-row table {
        border-collapse: collapse;
      }
    </style>

    <div class="tabs">
      <input type="radio" name="tab" id="tab1" checked>
      <label for="tab1">Main View</label>

      <input type="radio" name="tab" id="tab2">
      <label for="tab2">All Tables</label>

      <div id="content1" class="content">
        <h3>Students and Their Course Grades</h3>
        <?php
        if (count($rows) === 0) {
            echo "<p><i>No data found.</i></p>";
        } else {
            echo "<table border='1' cellpadding='5'><tr>";
            foreach (array_keys($rows[0]) as $col) {
                echo "<th>" . htmlspecialchars($col) . "</th>";
            }
            echo "<th>FinalGrade</th></tr>";

            foreach ($rows as $row) {
                echo "<tr>";
                $finalCourseGrade = 0;
                $cellCounter = 0;
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell) . "</td>";
                    if ($cellCounter >= 3 && $cellCounter <= 5) {
                        $finalCourseGrade += ((float)$cell * 0.2);
                    }
                    if ($cellCounter == 6) {
                        $finalCourseGrade += ((float)$cell * 0.4);
                        $finalCourseGrade = round($finalCourseGrade, 1);
                    }
                    $cellCounter++;
                }
                echo "<td>" . htmlspecialchars($finalCourseGrade) . "</td></tr>";
            }
            echo "</table>";
        }
        ?>
      </div>

      <div id="content2" class="content">
        <div class="table-row">
          <?php 
          $tables = ['NameTable', 'CourseTable', 'FinalGradeTable'];

          foreach ($tables as $table) {
              echo "<div>";
              echo "<h3>" . htmlspecialchars($table) . "</h3>";

              $stmt = $pdo->query("SELECT * FROM $table");
              $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

              if (count($rows) === 0) {
                  echo "<p><i>No data found.</i></p>";
              } else {
                  echo "<table border='1' cellpadding='5'><tr>";
                  foreach (array_keys($rows[0]) as $col) {
                      echo "<th>" . htmlspecialchars($col) . "</th>";
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
              echo "</div>";
          }
          ?>
        </div>
      </div>
    </div>

    <?php

} catch (PDOException $e) {
    echo "Failed to connect or fetch data.";
}
?>
