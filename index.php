<?php
$start_time = microtime(true);

// ┌──────────── Config ─────────────┐
$logfile     = __DIR__ . '/packet_stats_all_interfaces.txt';
$limit       = 20;
$dateFormat  = 'Y-m-d H:i:s';

$bgColor     = '#000';
$textColor   = '#0f0';
$headerBg    = '#050';
$rowAltBg    = '#020';
// └─────────────────────────────────┘

// Read log file once
$lines = is_readable($logfile) ? file($logfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
if (!$lines) {
    die("Error: Unable to read from log file.");
}
$header = array_shift($lines); // Remove header if present

// Read version
$version = file_get_contents("version");

// Parse and sanitize all lines
$data = [];
$maxRx = ['value' => 0, 'ts' => 0];
$maxTx = ['value' => 0, 'ts' => 0];

foreach ($lines as $line) {
    $cols = explode('|', trim($line));
    if (count($cols) < 12) continue;

    [$id, $ts, $iface, $rx, $tx, $totRx, $totTx, $udp_rx, $udp_tx, $icmp_rx, $icmp_tx] = $cols;

    // Typecasting
    $rx = (int)$rx;
    $tx = (int)$tx;

    // Max RX/TX tracker
    if ($rx > $maxRx['value']) $maxRx = ['value' => $rx, 'ts' => (int)$ts];
    if ($tx > $maxTx['value']) $maxTx = ['value' => $tx, 'ts' => (int)$ts];

    $data[] = [
        'id' => (int)$id,
        'ts' => (int)$ts,
        'time' => date($dateFormat, (int)$ts),
        'iface' => $iface,
        'rx' => $rx,
        'tx' => $tx,
        'totRx' => (int)$totRx,
        'totTx' => (int)$totTx,
        'udp_rx' => (int)$udp_rx,
        'udp_tx' => (int)$udp_tx,
        'icmp_rx' => (int)$icmp_rx,
        'icmp_tx' => (int)$icmp_tx
    ];
}

// Sum last RX + TX totals
$lastSum = null;
if (!empty($data)) {
    $last = end($data);
    $lastSum = number_format($last['totRx'] + $last['totTx']);
}

// Sort by timestamp
usort($data, fn($a, $b) => $a['ts'] <=> $b['ts']);
$data = array_slice($data, -$limit);
$data = array_reverse($data);

// Helper to read a specific line number
function readLineByNumber(array $lines, int $lineNumber): ?string {
    return $lines[$lineNumber - 2] ?? null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>🌍 NetworkPackets – Latest <?= $limit ?></title>
  <style>
    body { background: <?= $bgColor ?>; color: <?= $textColor ?>; font-family: monospace; padding: 20px; }
    a { text-decoration: none; color: #fff; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    th, td { padding: 4px 8px; border: 1px solid <?= $headerBg ?>; text-align: center; }
    th { background: <?= $headerBg ?>; }
    tr:nth-child(even) td { background: <?= $rowAltBg ?>; }
    caption { margin-bottom: 10px; font-size: 1.2em; }
  </style>
</head>
<body>
  <table><center><pre><?php echo file_get_contents("logo");?></pre></center>
    <center><pre>
Max RX Pps: <?= number_format($maxRx['value']) ?> (<?= date($dateFormat, $maxRx['ts']) ?>)
Max TX Pps: <?= number_format($maxTx['value']) ?> (<?= date($dateFormat, $maxTx['ts']) ?>)
Total packets: <?= $lastSum ?? 'N/A' ?>
</pre></center>

<?php if (isset($_GET['id'])):
    $line = readLineByNumber($lines, (int)$_GET['id']);
    if ($line):
        [$id, $ts, $iface, $rx, $tx, $totRx, $totTx, $udp_rx, $udp_tx, $icmp_rx, $icmp_tx] = explode('|', trim($line));
        $time = date($dateFormat, (int)$ts);
?>
  <thead>
    <tr>
      <th>#️ ID</th><th>📅 Date Time</th><th>⚙️ Interface</th><th>⬇️ RX pps</th><th>⬆️ TX pps</th>
      <th>⬇️ Total RX</th><th>⬆️ Total TX</th><th>⬇️ Total RX UDP</th><th>⬆️ Total TX UDP</th>
      <th>⬇️ Total RX ICMP</th><th>⬆️ Total TX ICMP</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><?= htmlspecialchars($id) ?></td>
      <td><?= htmlspecialchars($time) ?></td>
      <td><?= htmlspecialchars($iface) ?></td>
      <td><?= htmlspecialchars(number_format($rx)) ?></td>
      <td><?= htmlspecialchars(number_format($tx)) ?></td>
      <td><?= htmlspecialchars(number_format($totRx)) ?></td>
      <td><?= htmlspecialchars(number_format($totTx)) ?></td>
      <td><?= htmlspecialchars(number_format($udp_rx)) ?></td>
      <td><?= htmlspecialchars(number_format($udp_tx)) ?></td>
      <td><?= htmlspecialchars(number_format($icmp_rx)) ?></td>
      <td><?= htmlspecialchars(number_format($icmp_tx)) ?></td>
    </tr>
  </tbody>
<?php else: ?>
  <p>No record found for ID <?= htmlspecialchars($_GET['id']) ?></p>
<?php endif; else: ?>
  <thead>
    <tr>
      <th>#️ ID</th><th>📅 Date Time</th><th>⚙️ Interface</th><th>⬇️ RX pps</th><th>⬆️ TX pps</th>
      <th>⬇️ Total RX</th><th>⬆️ Total TX</th><th>⬇️ Total RX UDP</th><th>⬆️ Total TX UDP</th>
      <th>⬇️ Total RX ICMP</th><th>⬆️ Total TX ICMP</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($data as $row): ?>
      <tr>
        <td><a href="./id/<?= $row['id'] ?>"><?= $row['id'] ?></a></td>
        <td><?= htmlspecialchars($row['time']) ?></td>
        <td><?= htmlspecialchars($row['iface']) ?></td>
        <td><?= htmlspecialchars(number_format($row['rx'])) ?></td>
        <td><?= htmlspecialchars(number_format($row['tx'])) ?></td>
        <td><?= htmlspecialchars(number_format($row['totRx'])) ?></td>
        <td><?= htmlspecialchars(number_format($row['totTx'])) ?></td>
        <td><?= htmlspecialchars(number_format($row['udp_rx'])) ?></td>
        <td><?= htmlspecialchars(number_format($row['udp_tx'])) ?></td>
        <td><?= htmlspecialchars(number_format($row['icmp_rx'])) ?></td>
        <td><?= htmlspecialchars(number_format($row['icmp_tx'])) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
<?php endif; ?>
</table>
<center>
<?php
echo "<br>Records in DB: " . count($lines);
echo "<br>DB size: " . number_format(filesize($logfile)) . " bytes";
echo "<br>Executed in: " . round(microtime(true) - $start_time, 4) . " seconds";
echo "<br>RAM usage: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB";
?>
<br>Made with ❤
<br>v<?php echo $version;?>
</center>
</body>
</html>
