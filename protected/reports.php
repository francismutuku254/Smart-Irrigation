<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    echo "<script>
            alert('Access denied. Please log in first.');
            window.location.href = 'login.html';
          </script>";
    exit();
}

$host = "localhost";
$user = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "irrigation_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Data Export</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="main/main.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .filter-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-section select, .filter-section input {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin: 5px;
        }
        .filter-section button {
            padding: 10px 15px;
            font-size: 16px;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: 0.3s;
        }
        .filter-section button:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        .export-buttons {
            text-align: right;
            margin-top: 10px;
        }
        .export-buttons button {
            padding: 10px 15px;
            font-size: 14px;
            margin: 5px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: 0.3s;
        }
        .export-csv {
            background-color: #007bff;
            color: white;
        }
        .export-csv:hover {
            background-color: #0056b3;
        }
        .export-pdf {
            background-color: #dc3545;
            color: white;
        }
        .export-pdf:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2><i class="fas fa-file-alt"></i> Reports & Data Export</h2>

        <div class="filter-section">
            <select id="reportType">
                <option value="soil_moisture">Soil Moisture Report</option>
                <option value="pump_activity">Pump Activity Report</option>
                <option value="login_history">Login History Report</option>
            </select>

            <input type="text" id="dateRange" placeholder="Select Date Range">

            <button onclick="fetchReport()">Generate Report</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data Type</th>
                    <th>Value</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody id="reportBody">
                <tr><td colspan="4">Select a report type and date range to view data.</td></tr>
            </tbody>
        </table>

        <div class="export-buttons">
            <button class="export-csv" onclick="exportData('csv')"><i class="fas fa-file-csv"></i> Export CSV</button>
            <button class="export-pdf" onclick="exportData('pdf')"><i class="fas fa-file-pdf"></i> Export PDF</button>
        </div>
    </div>

    <script>
        $(function() {
            $('#dateRange').daterangepicker({
                opens: 'left'
            });
        });

        function fetchReport() {
            let reportType = $('#reportType').val();
            let dateRange = $('#dateRange').val();

            $.ajax({
                url: "fetch_report.php",
                type: "GET",
                data: { type: reportType, range: dateRange },
                dataType: "json",
                success: function (data) {
                    let rows = "";
                    if (data.length > 0) {
                        data.forEach(item => {
                            rows += `<tr>
                                        <td>${item.id}</td>
                                        <td>${item.type}</td>
                                        <td>${item.value}</td>
                                        <td>${item.date}</td>
                                     </tr>`;
                        });
                    } else {
                        rows = "<tr><td colspan='4'>No data found for the selected range.</td></tr>";
                    }
                    $("#reportBody").html(rows);
                },
                error: function () {
                    alert("Error fetching report data.");
                }
            });
        }

        function exportData(format) {
            let reportType = $('#reportType').val();
            let dateRange = $('#dateRange').val();
            window.location.href = `export_report.php?type=${reportType}&format=${format}&range=${dateRange}`;
        }
    </script>

</body>
</html>

<?php $conn->close(); ?>
