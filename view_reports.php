<?php
require __DIR__ . "/common.php";

$conn = db();
$result = $conn->query(
    "SELECT id, junction_name, congestion_level, description, report_time
     FROM traffic_reports
     ORDER BY report_time DESC, id DESC"
);

$totalReports = $result->num_rows;
$severeCount = 0;

while ($row = $result->fetch_assoc()) {
    if ($row["congestion_level"] === "Severe Gridlock") {
        $severeCount++;
    }
}

$result->data_seek(0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Traffic Reports Dashboard</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 30px 20px;
    min-height: 100vh;
    font-family: Arial, sans-serif;
    color: #eaf1ff;
    background: #0b1220;
}

.container {
    max-width: 1150px;
    margin: auto;
}

.header {
    padding: 30px;
    margin-bottom: 24px;
    border: 1px solid #2a3a54;
    border-radius: 20px;
    background: linear-gradient(135deg, #1168df, #263fa7);
    box-shadow: 0 12px 30px rgba(0,0,0,.2);
}

.brand {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #dce9ff;
}

.header h1 {
    margin: 14px 0 8px;
    font-size: 30px;
}

.header p {
    margin: 0;
    color: #dce9ff;
    line-height: 1.6;
}

.stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}

.stat-card {
    padding: 24px;
    border: 1px solid #293750;
    border-radius: 16px;
    background: #151f32;
}

.stat-label {
    margin: 0 0 12px;
    color: #aebdd3;
    font-size: 14px;
}

.stat-number {
    color: #64a7ff;
    font-size: 34px;
    font-weight: bold;
}

.table-card {
    padding: 24px;
    border: 1px solid #293750;
    border-radius: 18px;
    background: #151f32;
    box-shadow: 0 12px 30px rgba(0,0,0,.15);
}

.table-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 22px;
}

.table-heading h2 {
    margin: 0;
    font-size: 21px;
}

.button {
    display: inline-block;
    padding: 12px 16px;
    border-radius: 10px;
    background: #1677f9;
    color: white;
    font-size: 14px;
    font-weight: bold;
    text-decoration: none;
    transition: background .2s, transform .2s;
}

.button:hover {
    background: #075dd2;
    transform: translateY(-1px);
}

.button.secondary {
    background: #263650;
    border: 1px solid #405371;
    color: #eaf1ff;
}

.button.secondary:hover {
    background: #344764;
}

.table-wrap {
    overflow-x: auto;
    border: 1px solid #2b3a52;
    border-radius: 12px;
}

table {
    width: 100%;
    min-width: 760px;
    border-collapse: collapse;
}

th, td {
    padding: 16px;
    text-align: left;
    border-bottom: 1px solid #293750;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

th {
    color: #b9c9e1;
    background: #1c2940;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .5px;
}

td {
    color: #e3ebf8;
    font-size: 14px;
}

tbody tr:hover td {
    background: #1b2940;
}

tbody tr:last-child td {
    border-bottom: none;
}

.status {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    white-space: nowrap;
}

.normal {
    color: #69e2ae;
    background: #153c32;
}

.moderate {
    color: #ffd16a;
    background: #45381b;
}

.severe {
    color: #ff9da5;
    background: #48252e;
}

.emergency {
    color: #83d9ff;
    background: #173a50;
}

.other {
    color: #d1d9e6;
    background: #303b4d;
}

.empty {
    padding: 36px;
    text-align: center;
    color: #aebdd3;
}

.footer {
    display: flex;
    justify-content: center;
    margin-top: 24px;
}

@media (max-width: 600px) {
    body {
        padding: 16px 10px;
    }

    .header, .table-card, .stat-card {
        padding: 20px;
    }

    .header h1 {
        font-size: 25px;
    }

    .stats {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>
<div class="container">

    <header class="header">
        <div class="brand">Smart Traffic Management System</div>
        <h1>Traffic Reports Dashboard</h1>
        <p>Monitor submitted traffic incidents and congestion reports.</p>
    </header>

    <div style="background: #151f32; border: 1px solid #293750; border-left: 5px solid #64a7ff; padding: 20px; margin-bottom: 24px; border-radius: 16px;">
        <h3 style="margin: 0 0 8px 0; color: #64a7ff; font-size: 16px;">System Reliability Scorecard (SRGM Metric, demo values)</h3>
        <p style="margin: 0; font-size: 14px; color: #aebdd3;">
            Server Uptime Status: <b style="color: #69e2ae;">99.98% Stable</b> | Active Faults: <b style="color: #eaf1ff;">0</b> | Target: <b style="color: #83d9ff;">Viksit Bharat 2047 Intelligent Grid</b>
        </p>
    </div>
    
    <section class="stats">
        <div class="stat-card">
            <p class="stat-label">Total Traffic Reports</p>
            <div class="stat-number"><?= $totalReports ?></div>
        </div>

        <div class="stat-card">
            <p class="stat-label">Severe Gridlock Reports</p>
            <div class="stat-number"><?= $severeCount ?></div>
        </div>
    </section>

    <section class="table-card">
        <div class="table-heading">
            <h2>Submitted Reports</h2>
            <a class="button" href="index.html">
                + Submit New Report
            </a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Junction / Area</th>
                        <th>Congestion Level</th>
                        <th>Description</th>
                        <th>Report Time</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($totalReports > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        $level = $row["congestion_level"];

                        $statusClass = statusClass($level);
                        ?>
                        <tr>
                            <td><?= escapeHtml($row["id"]) ?></td>
                            <td><?= escapeHtml($row["junction_name"]) ?></td>
                            <td>
                                <span class="status <?= $statusClass ?>">
                                    <?= escapeHtml($level) ?>
                                </span>
                            </td>
                            <td>
                                <?= escapeHtml(
                                    $row["description"] ?: "No description"
                                ) ?>
                            </td>
                            <td><?= escapeHtml($row["report_time"]) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty">
                            No reports yet. Submit your first traffic report.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <div class="footer">
        <a class="button secondary" href="index.html">← Back to Home</a>
    </div>

</div>
</body>
</html>

<?php
$result->free();
$conn->close();
?>

