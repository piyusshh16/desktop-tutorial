<?php
require __DIR__ . "/common.php";

$conn = db();
$result = $conn->query(
    "SELECT id, junction_name, congestion_level, description,
            report_time, latitude, longitude
     FROM traffic_reports
     ORDER BY report_time DESC, id DESC"
);

$reports = [];
$pins = [];

while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
    if ($row["latitude"] !== null && $row["longitude"] !== null) {
        $pins[] = [
            "name"  => $row["junction_name"],
            "level" => $row["congestion_level"],
            "desc"  => $row["description"] ?: "No description provided.",
            "time"  => $row["report_time"],
            "lat"   => (float)$row["latitude"],
            "lng"   => (float)$row["longitude"],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Live Traffic Map | Smart Traffic Management</title>

<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 30px 18px;
    min-height: 100vh;
    font-family: Arial, sans-serif;
    color: #eaf1ff;
    background: #0b1220;
}

.container {
    max-width: 1100px;
    margin: auto;
    padding: 28px;
    background: #151f32;
    border: 1px solid #293750;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(0,0,0,.3);
}

.header {
    padding: 26px;
    margin-bottom: 24px;
    border-radius: 15px;
    background: linear-gradient(135deg, #1168df, #263fa7);
}

.brand {
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #dce9ff;
}

h1 {
    margin: 13px 0 9px;
    font-size: 28px;
}

.header p {
    margin: 0;
    color: #dce9ff;
    line-height: 1.6;
}

.info {
    padding: 16px;
    margin-bottom: 22px;
    border: 1px solid #354761;
    border-radius: 12px;
    background: #0d1728;
    color: #b9c9e1;
    font-size: 14px;
    line-height: 1.7;
}

.section-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 15px;
}

h2 {
    margin: 0;
    font-size: 21px;
}

.count {
    padding: 7px 12px;
    border-radius: 20px;
    background: #173a50;
    color: #83d9ff;
    font-size: 12px;
    font-weight: bold;
}

#map {
    width: 100%;
    height: 430px;
    border: 1px solid #354761;
    border-radius: 14px;
    overflow: hidden;
    background: #0d1728;
}

.reports-section {
    margin-top: 30px;
}

.report {
    padding: 20px;
    margin-top: 14px;
    border: 1px solid #293750;
    border-radius: 13px;
    background: #0d1728;
    transition: border-color .2s, transform .2s;
}

.report:hover {
    border-color: #1677f9;
    transform: translateY(-2px);
}

.report-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
}

.report h3 {
    margin: 0;
    font-size: 17px;
    overflow-wrap: anywhere;
}

.report p {
    color: #b9c9e1;
    font-size: 14px;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.status {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
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

.report-time {
    color: #8292aa !important;
    font-size: 12px !important;
}

.empty {
    padding: 28px;
    border: 1px dashed #405371;
    border-radius: 12px;
    text-align: center;
    color: #aebdd3;
    line-height: 1.6;
}

.buttons {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 28px;
}

.button {
    display: inline-block;
    padding: 13px 18px;
    border: 0;
    border-radius: 10px;
    background: linear-gradient(135deg, #1677f9, #2454d8);
    color: white;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
}

.button.secondary {
    background: #263650;
    border: 1px solid #405371;
}

.button:hover {
    filter: brightness(1.15);
}

.footer {
    margin-top: 25px;
    padding-top: 18px;
    border-top: 1px solid #293750;
    text-align: center;
    color: #8292aa;
    font-size: 12px;
}

.leaflet-popup-content-wrapper,
.leaflet-popup-tip {
    background: #151f32;
    color: #eaf1ff;
}

.leaflet-popup-content {
    line-height: 1.6;
}

@media (max-width: 600px) {
    body {
        padding: 12px 8px;
    }

    .container {
        padding: 16px;
    }

    .header {
        padding: 20px;
    }

    h1 {
        font-size: 24px;
    }

    #map {
        height: 340px;
    }
}
</style>
</head>

<body>
<div class="container">

    <header class="header">
        <div class="brand">Smart Traffic Management System</div>
        <h1>Live Traffic Map</h1>
        <p>Explore traffic reports and review submitted incidents.</p>
    </header>

    <div class="info">
        <strong>Traffic overview:</strong>
        <?= count($reports) ?> saved report(s) found in the database.
        The report details below are loaded directly from MySQL.
        <?= count($pins) ?> of them have a map location and appear as pins.
    </div>

    <section>
        <div class="section-heading">
            <h2>Map Overview</h2>
            <span class="count">New Delhi</span>
        </div>

        <div id="map"></div>
    </section>

    <section class="reports-section">
        <div class="section-heading">
            <h2>Recent Traffic Reports</h2>
            <span class="count">
                <?= count($reports) ?> reports
            </span>
        </div>

        <?php if (empty($reports)): ?>

            <div class="empty">
                <h3>No traffic reports yet</h3>
                <p>Submit a report using the form to see it here.</p>
            </div>

        <?php else: ?>

            <?php foreach ($reports as $report): ?>
                <article class="report">

                    <div class="report-top">
                        <h3>
                            <?= e($report["junction_name"]) ?>
                        </h3>

                        <span class="status <?= statusClass($report["congestion_level"]) ?>">
                            <?= e($report["congestion_level"]) ?>
                        </span>
                    </div>

                    <p>
                        <strong>Description:</strong><br>
                        <?= e(
                            $report["description"] ?: "No description provided."
                        ) ?>
                    </p>

                    <p class="report-time">
                        Report ID: <?= e($report["id"]) ?>
                        &nbsp; | &nbsp;
                        Report time: <?= e($report["report_time"]) ?>
                    </p>

                </article>
            <?php endforeach; ?>

        <?php endif; ?>
    </section>

    <div class="buttons">
        <a class="button secondary" href="index.html">
            ← Back to Home
        </a>

        <a class="button" href="view_reports.php">
            View All Reports
        </a>

        <a class="button secondary" href="live_map.php">
            ↻ Refresh Reports
        </a>
    </div>

    <footer class="footer">
        Smart Traffic Management System · Group Project
    </footer>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
const map = L.map("map").setView([28.6139, 77.2090], 11);

// 1. Normal map: roads, streets and place names
L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution: "&copy; OpenStreetMap contributors"
}).addTo(map);

// 2. Live traffic: coloured traffic conditions
L.tileLayer(
    "https://api.tomtom.com/traffic/map/4/tile/flow/relative0/{z}/{x}/{y}.png?key=<?= TOMTOM_KEY ?>",
    {
        maxZoom: 19,
        opacity: 0.8,
        attribution: "&copy; TomTom Traffic"
    }
).addTo(map);

const pins = <?= json_encode($pins, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;

const colors = {
    "Normal Flow": "#2ecc8f",
    "Moderate Delay": "#f5b942",
    "Severe Gridlock": "#ff5d6c",
    "Emergency Priority Route": "#3bb8ff"
};

const bounds = [];

pins.forEach(p => {
    const box = document.createElement("div");

    const title = document.createElement("strong");
    title.textContent = p.name;
    box.appendChild(title);
    box.appendChild(document.createElement("br"));

    const level = document.createElement("span");
    level.textContent = p.level;
    level.style.color = colors[p.level] || "#ffffff";
    level.style.fontWeight = "bold";
    box.appendChild(level);
    box.appendChild(document.createElement("br"));

    const desc = document.createElement("span");
    desc.textContent = p.desc;
    box.appendChild(desc);
    box.appendChild(document.createElement("br"));

    const time = document.createElement("small");
    time.textContent = p.time;
    box.appendChild(time);

    L.circleMarker([p.lat, p.lng], {
        radius: 10,
        color: "#ffffff",
        weight: 2,
        fillColor: colors[p.level] || "#999999",
        fillOpacity: 0.95
    }).addTo(map).bindPopup(box);

    bounds.push([p.lat, p.lng]);
});

if (bounds.length > 0) {
    map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
}
</script>

</body>
</html>

<?php
$result->free();
$conn->close();
?>
