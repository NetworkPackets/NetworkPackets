<?php
header('Content-Type: application/json');

$logfile = __DIR__ . '/packet_stats_all_interfaces.txt';
$dateFormat = 'Y-m-d H:i:s';

// --- SECURITY: Check if log file is readable before processing ---
if (!is_readable($logfile)) {
    http_response_code(500);
    echo json_encode(['error' => 'Log file not readable']);
    exit;
}

// --- PERFORMANCE: Read file lines ignoring empty lines for faster parsing ---
$lines = file($logfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Validate we have enough data to process (at least header + 1 line)
if (!$lines || count($lines) < 2) {
    echo json_encode(['error' => 'No data found']);
    exit;
}

// Remove header line (assumed first line)
array_shift($lines);

// Initialize arrays and variables for stats tracking
$data = [];
$maxRx = ['value' => 0, 'ts' => 0];
$maxTx = ['value' => 0, 'ts' => 0];

// --- PARSE each line into structured data ---
// Use strict validation on expected columns count (security + stability)
foreach ($lines as $line) {
    $cols = explode('|', trim($line));
    if (count($cols) < 12) continue; // Skip invalid/malformed lines

    // Destructure fields, cast numeric values to int for safety & performance
    [$id, $ts, $iface, $rx, $tx, $totRx, $totTx, $udp_rx, $udp_tx, $icmp_rx, $icmp_tx] = $cols;
    $rx = (int)$rx;
    $tx = (int)$tx;

    // Track max RX and TX values with timestamps (fast comparison)
    if ($rx > $maxRx['value']) $maxRx = ['value' => $rx, 'ts' => (int)$ts];
    if ($tx > $maxTx['value']) $maxTx = ['value' => $tx, 'ts' => (int)$ts];

    // Store structured record for response
    $data[] = [
        'id'       => (int)$id,
        'ts'       => (int)$ts,
        'time'     => date($dateFormat, (int)$ts),
        'iface'    => $iface,
        'rx'       => $rx,
        'tx'       => $tx,
        'totRx'    => (int)$totRx,
        'totTx'    => (int)$totTx,
        'udp_rx'   => (int)$udp_rx,
        'udp_tx'   => (int)$udp_tx,
        'icmp_rx'  => (int)$icmp_rx,
        'icmp_tx'  => (int)$icmp_tx,
        'total_packets' => (int)$totRx + (int)$totTx
    ];
}

// --- Extract total packets from last record for summary ---
$last = end($data);
$totalPackets = $last['total_packets'] ?? null;

// --- Handle query param: ?id=123 for single record lookup ---
// Cast to int to prevent injection attacks, respond 404 if not found
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $record = array_filter($data, fn($row) => $row['id'] === $id);
    if (empty($record)) {
        http_response_code(404);
        echo json_encode(['error' => "ID $id not found"]);
    } else {
        echo json_encode(array_values($record)[0], JSON_PRETTY_PRINT);
    }
    exit;
}

// --- Handle query param: ?summary=1 for overall summary ---
// Calculate total RX and TX packets per second (pps) efficiently
if (isset($_GET['summary'])) {
    $totalRxPps = 0;
    $totalTxPps = 0;
    foreach ($data as $row) {
//        $totalRxPps += $row['rx'];
//        $totalTxPps += $row['tx'];
    }

    echo json_encode([
        'max_rx' => [
            'value' => $maxRx['value'],
            'time'  => date($dateFormat, $maxRx['ts'])
        ],
        'max_tx' => [
            'value' => $maxTx['value'],
            'time'  => date($dateFormat, $maxTx['ts'])
        ],
        'rx_pps' => $row['rx'],   // fixed from previous bug
        'tx_pps' => $row['tx'],
        'latest_total_packets' => $totalPackets,
        'record_count'  => count($data),
        'db_size_bytes' => filesize($logfile)
    ], JSON_PRETTY_PRINT);
    exit;
}

// --- Default: return list with optional limit ---
// Sanitize limit param (1 to 500) to prevent abuse
$limit = isset($_GET['limit']) ? max(1, min((int)$_GET['limit'], 500)) : 20;

// Sort data chronologically for slicing recent records
usort($data, fn($a, $b) => $a['ts'] <=> $b['ts']);

// Slice to last $limit records, reverse to have newest first
$data = array_slice($data, -$limit);
$data = array_reverse($data);

// Calculate total RX and TX pps for sliced records
$totalRxPps = 0;
$totalTxPps = 0;
foreach ($data as $row) {
    $totalRxPps += $row['rx'];
    $totalTxPps += $row['tx'];
}

// Output JSON with meta and records, pretty-printed for readability
echo json_encode([
    'limit' => $limit,
    'latest_total_packets' => $totalPackets,
    'total_rx_pps' => $totalRxPps,
    'total_tx_pps' => $totalTxPps,
    'records' => $data
], JSON_PRETTY_PRINT);
