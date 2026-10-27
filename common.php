<?php
// Shared settings and helper functions used by every page.

const DB_HOST = "localhost";
const DB_USER = "root";
const DB_PASS = "";
const DB_NAME = "smart_flow_db";

// Free key from developer.tomtom.com (replace with your own).
const TOMTOM_KEY = "0dTtRIuzMV1VQoaE9EwfV1kPFInnfEON";

// Added to every junction name before looking it up, to keep results in your city.
const GEOCODE_SUFFIX = ", Delhi, India";

const CONGESTION_LEVELS = [
    "Normal Flow",
    "Moderate Delay",
    "Severe Gridlock",
    "Emergency Priority Route",
];

function db(): mysqli {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } catch (mysqli_sql_exception $ex) {
        http_response_code(500);
        die("Database connection failed. Is MySQL running and is the database created?");
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function escapeHtml($value): string {
    return e($value);
}

function statusClass(string $level): string {
    return match ($level) {
        "Normal Flow"              => "normal",
        "Moderate Delay"           => "moderate",
        "Severe Gridlock"          => "severe",
        "Emergency Priority Route" => "emergency",
        default                    => "other",
    };
}

// Turns a place name into [latitude, longitude] using OpenStreetMap's free
// Nominatim service. Returns [null, null] if nothing is found or it is offline.
function geocode(string $place): array {
    $url = "https://nominatim.openstreetmap.org/search?" . http_build_query([
        "q"            => $place . GEOCODE_SUFFIX,
        "format"       => "json",
        "limit"        => 1,
        "countrycodes" => "in",
    ]);

    $context = stream_context_create(["http" => [
        "header"  => "User-Agent: SmartTrafficGroupProject/1.0 (student project)\r\n",
        "timeout" => 5,
    ]]);

    $json = @file_get_contents($url, false, $context);
    if ($json === false) {
        return [null, null];
    }

    $data = json_decode($json, true);
    if (!is_array($data) || empty($data[0]["lat"]) || empty($data[0]["lon"])) {
        return [null, null];
    }

    return [(float)$data[0]["lat"], (float)$data[0]["lon"]];
}
