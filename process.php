<?php
require __DIR__ . "/common.php";

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $junction    = trim($_POST["junction_name"] ?? "");
    $congestion  = trim($_POST["congestion_level"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if (
        mb_strlen($junction) < 3 ||
        mb_strlen($junction) > 100 ||
        mb_strlen($description) > 1000 ||
        !in_array($congestion, CONGESTION_LEVELS, true)
    ) {
        http_response_code(400);
        $message = "Please enter a junction name (3-100 characters), a valid congestion level, and a description under 1000 characters.";
    } else {
        $conn = db();
        [$lat, $lng] = geocode($junction);

        try {
            $stmt = $conn->prepare(
                "INSERT INTO traffic_reports
                 (junction_name, congestion_level, description, latitude, longitude)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssdd", $junction, $congestion, $description, $lat, $lng);
            $stmt->execute();
            $stmt->close();

            $success = true;
            $message = $lat !== null
                ? "Your report was saved and placed on the live map."
                : "Your report was saved, but the location could not be found, so it has no map pin.";
        } catch (mysqli_sql_exception $ex) {
            http_response_code(500);
            $message = "Unable to save your report. Did you run upgrade.sql / schema.sql?";
        }

        $conn->close();
    }
} else {
    $message = "Please submit a report using the reporting form.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Report Status | Smart Traffic</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    padding: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: Arial, sans-serif;
    color: #eaf1ff;
    background: #0b1220;
}

.card {
    width: 100%;
    max-width: 560px;
    overflow: hidden;
    background: #151f32;
    border: 1px solid #293750;
    border-radius: 22px;
    box-shadow: 0 24px 70px rgba(0, 0, 0, .35);
}

.header {
    padding: 28px;
    background: linear-gradient(135deg, #0878f9, #1745bc);
}

.brand {
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    opacity: .9;
}

.header h1 {
    margin: 16px 0 8px;
    font-size: 27px;
}

.header p {
    margin: 0;
    line-height: 1.6;
    color: #e0eaff;
}

.content {
    padding: 32px 28px;
    text-align: center;
}

.icon {
    width: 76px;
    height: 76px;
    display: grid;
    place-items: center;
    margin: 0 auto 20px;
    border-radius: 50%;
    font-size: 38px;
    background: <?= $success ? '#123d35' : '#48272d' ?>;
    color: <?= $success ? '#55e6b0' : '#ff9a9a' ?>;
}

.content h2 {
    margin: 0 0 12px;
    font-size: 25px;
}

.message {
    color: #b9c6db;
    line-height: 1.7;
    overflow-wrap: anywhere;
}

.note {
    margin: 24px 0;
    padding: 14px;
    border: 1px solid #2c3b55;
    border-radius: 12px;
    color: #aebdd3;
    background: #10192a;
    font-size: 13px;
    line-height: 1.6;
}

.actions {
    display: grid;
    gap: 12px;
    margin-top: 24px;
}

.btn {
    display: block;
    padding: 14px 18px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: bold;
    transition: .2s;
}

.btn-primary {
    color: white;
    background: #1677f9;
}

.btn-primary:hover {
    background: #075dd2;
    transform: translateY(-1px);
}

.btn-secondary {
    color: #dce8ff;
    background: #24324a;
    border: 1px solid #3a4b67;
}

.btn-secondary:hover {
    background: #30415d;
}

.footer {
    padding: 16px;
    text-align: center;
    border-top: 1px solid #293750;
    color: #8091aa;
    font-size: 12px;
}

@media (max-width: 480px) {
    body { padding: 12px; }
    .header, .content { padding: 24px 20px; }
    .header h1 { font-size: 23px; }
}
</style>
</head>

<body>
<div class="card">
    <header class="header">
        <div class="brand">Smart Traffic Management System</div>
        <h1>Traffic Incident Report</h1>
        <p>Helping make our roads smarter and safer.</p>
    </header>

    <main class="content">
        <div class="icon" aria-hidden="true">
            <?= $success ? '&#10003;' : '&#9888;' ?>
        </div>

        <h2>
            <?= $success ? 'Report Submitted!' : 'Report Status' ?>
        </h2>

        <p class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>

        <?php if ($success): ?>
            <div class="note">
                Your report is now stored in the database.
                Thank you for helping improve traffic awareness.
            </div>
        <?php endif; ?>

        <div class="actions">
            <a class="btn btn-primary" href="index.html">
                + Submit Another Report
            </a>

            <a class="btn btn-secondary" href="view_reports.php">
                View Traffic Reports →
            </a>
        </div>
    </main>

    <footer class="footer">
        Smart Traffic Management System · Group Project
    </footer>
</div>
</body>
</html>
