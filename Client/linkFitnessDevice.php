<?php
session_start();
if(!isset($_SESSION["user_id"])){
    header("location:../login.php");
    exit();
}
// DB connection
$conn = new mysqli("localhost", "root", "", "diet_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
include("header.php");

$user_id = $_SESSION['user_id'] ?? 'Krupa20260426222757'; // fallback for demo

// Fetch client data
$client = null;
$stmt = $conn->prepare("SELECT * FROM client WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $client = $result->fetch_assoc();
}
$stmt->close();

// Fetch user data
$user = null;
$stmt2 = $conn->prepare("SELECT full_name, email FROM user WHERE user_id = ?");
$stmt2->bind_param("s", $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
if ($result2->num_rows > 0) {
    $user = $result2->fetch_assoc();
}
$stmt2->close();

// Handle sync form submission (simulated device sync)
$sync_message = '';
$sync_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'sync') {
        $calories_burned = floatval($_POST['calories_burned'] ?? 0);
        $sync_date = $_POST['sync_date'] ?? date('Y-m-d');

        // Upsert into exercise table
        $check = $conn->prepare("SELECT id FROM exercise WHERE user_id = ? AND date = ?");
        $check->bind_param("ss", $user_id, $sync_date);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $update = $conn->prepare("UPDATE exercise SET calories_burned = ? WHERE user_id = ? AND date = ?");
            $update->bind_param("dss", $calories_burned, $user_id, $sync_date);
            $update->execute();
            $update->close();
        } else {
            $insert = $conn->prepare("INSERT INTO exercise (user_id, calories_burned, date) VALUES (?, ?, ?)");
            $insert->bind_param("sds", $user_id, $calories_burned, $sync_date);
            $insert->execute();
            $insert->close();
        }
        $check->close();
        $sync_message = "Device synced successfully! {$calories_burned} kcal recorded for {$sync_date}.";
    }
}

// Fetch recent exercise logs
$logs = [];
$log_stmt = $conn->prepare("SELECT * FROM exercise WHERE user_id = ? ORDER BY date DESC LIMIT 7");
$log_stmt->bind_param("s", $user_id);
$log_stmt->execute();
$log_result = $log_stmt->get_result();
while ($row = $log_result->fetch_assoc()) {
    $logs[] = $row;
}
$log_stmt->close();

$conn->close();

$tdee = $client['tdee'] ?? 0;
$goal = $client['goal'] ?? 'N/A';
$bmi = $client['bmi'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Link Fitness Device — DietManager</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #eeeef9;
    --surface: #d6eaf7;
    --card: #eaeaf7e4;
    --border: #252535;
    --accent: #078559;
    --accent2: #00d4ff;
    --warn: #ff6b35;
    --text: #0e0e0f;
    --muted: #6b6b88;
    --radius: 12px;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Syne', sans-serif;
    min-height: 100vh;
    overflow-x: hidden;
  }

  /* Animated background grid */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(rgba(0,245,160,0.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(0,245,160,0.03) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
    z-index: 0;
  }

  .page-wrap {
    position: relative;
    z-index: 1;
    max-width: 1100px;
    margin: 0 auto;
    padding: 40px 24px;
  }

  /* Header */
  .header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 48px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--border);
  }

  .header-left h1 {
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .header-left p {
    color: var(--muted);
    font-size: 0.85rem;
    margin-top: 4px;
    font-family: 'Space Mono', monospace;
  }

  .user-badge {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 50px;
    padding: 8px 18px 8px 8px;
  }

  .user-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 0.9rem; color: #000;
  }

  .user-name { font-size: 0.85rem; font-weight: 600; }

  /* Stats bar */
  .stats-bar {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 40px;
  }

  .stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    position: relative;
    overflow: hidden;
    transition: border-color 0.3s, transform 0.2s;
  }

  .stat-card:hover { border-color: var(--accent); transform: translateY(-2px); }

  .stat-card::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 60px; height: 60px;
    background: radial-gradient(circle at top right, rgba(0,245,160,0.08), transparent 70%);
  }

  .stat-label {
    font-size: 0.7rem;
    font-family: 'Space Mono', monospace;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 8px;
  }

  .stat-value {
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -0.03em;
  }

  .stat-value.green { color: var(--accent); }
  .stat-value.blue { color: var(--accent2); }
  .stat-value.orange { color: var(--warn); }

  .stat-sub {
    font-size: 0.72rem;
    color: var(--muted);
    margin-top: 4px;
    font-family: 'Space Mono', monospace;
  }

  /* Main grid */
  .main-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 40px;
  }

  @media (max-width: 700px) { .main-grid { grid-template-columns: 1fr; } }

  .panel {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 28px;
    transition: border-color 0.3s;
  }

  .panel:hover { border-color: rgba(0,245,160,0.3); }

  .panel-title {
    font-size: 0.7rem;
    font-family: 'Space Mono', monospace;
    color: var(--accent);
    text-transform: uppercase;
    letter-spacing: 0.12em;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .panel-title::before {
    content: '';
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--accent);
    box-shadow: 0 0 8px var(--accent);
    animation: pulse 2s ease-in-out infinite;
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; box-shadow: 0 0 8px var(--accent); }
    50% { opacity: 0.5; box-shadow: 0 0 16px var(--accent); }
  }

  /* Device cards */
  .device-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .device-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    transition: all 0.25s;
    cursor: pointer;
  }

  .device-item:hover { border-color: var(--accent2); background: rgba(0,212,255,0.04); }

  .device-item.connected { border-color: rgba(0,245,160,0.4); background: rgba(0,245,160,0.04); }

  .device-info { display: flex; align-items: center; gap: 12px; }

  .device-icon {
    width: 40px; height: 40px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border);
  }

  .device-name { font-weight: 600; font-size: 0.9rem; }
  .device-type { font-size: 0.72rem; color: var(--muted); font-family: 'Space Mono', monospace; margin-top: 2px; }

  .status-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--muted);
  }

  .status-dot.on {
    background: var(--accent);
    box-shadow: 0 0 6px var(--accent);
    animation: pulse 2s ease-in-out infinite;
  }

  /* Form */
  .form-group { margin-bottom: 18px; }

  label {
    display: block;
    font-size: 0.72rem;
    font-family: 'Space Mono', monospace;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 8px;
  }

  input[type="number"], input[type="date"], select {
    width: 100%;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-family: 'Space Mono', monospace;
    font-size: 0.9rem;
    padding: 12px 14px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
  }

  input:focus, select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(0,245,160,0.1);
  }

  .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 28px;
    border-radius: 8px;
    font-family: 'Space Mono', monospace;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    text-transform: uppercase;
    width: 100%;
  }

  .btn-primary {
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    color: #000;
  }

  .btn-primary:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(0,245,160,0.25);
  }

  .btn-outline {
    background: transparent;
    color: var(--accent2);
    border: 1px solid var(--accent2);
    width: auto;
  }

  .btn-outline:hover { background: rgba(0,212,255,0.08); }

  /* Alert */
  .alert {
    padding: 14px 18px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-family: 'Space Mono', monospace;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .alert-success {
    background: rgba(0,245,160,0.08);
    border: 1px solid rgba(0,245,160,0.3);
    color: var(--accent);
  }

  .alert-error {
    background: rgba(255,107,53,0.08);
    border: 1px solid rgba(255,107,53,0.3);
    color: var(--warn);
  }

  /* Logs table */
  .log-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
  }

  .log-table th {
    text-align: left;
    padding: 10px 12px;
    font-family: 'Space Mono', monospace;
    font-size: 0.65rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    border-bottom: 1px solid var(--border);
  }

  .log-table td {
    padding: 12px 12px;
    border-bottom: 1px solid rgba(37,37,53,0.6);
    font-family: 'Space Mono', monospace;
  }

  .log-table tr:last-child td { border-bottom: none; }

  .log-table tr:hover td { background: rgba(255,255,255,0.02); }

  .kcal-pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 50px;
    font-size: 0.72rem;
    font-weight: 700;
    background: rgba(0,245,160,0.12);
    color: var(--accent);
    border: 1px solid rgba(0,245,160,0.2);
  }

  /* Goal badge */
  .goal-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.68rem;
    font-family: 'Space Mono', monospace;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 700;
  }

  .goal-loss { background: rgba(255,107,53,0.15); color: var(--warn); border: 1px solid rgba(255,107,53,0.3); }
  .goal-gain { background: rgba(0,212,255,0.15); color: var(--accent2); border: 1px solid rgba(0,212,255,0.3); }
  .goal-maintain { background: rgba(0,245,160,0.15); color: var(--accent); border: 1px solid rgba(0,245,160,0.3); }

  /* Calorie ring visual */
  .ring-wrap {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-top: 8px;
  }

  .ring-svg { width: 80px; height: 80px; flex-shrink: 0; }

  .ring-info h3 { font-size: 1.1rem; font-weight: 800; margin-bottom: 4px; }
  .ring-info p { font-size: 0.72rem; color: var(--muted); font-family: 'Space Mono', monospace; }

  /* Progress bar */
  .progress-wrap { margin-top: 16px; }
  .progress-label {
    display: flex;
    justify-content: space-between;
    font-size: 0.7rem;
    font-family: 'Space Mono', monospace;
    color: var(--muted);
    margin-bottom: 6px;
  }
  .progress-bar {
    height: 6px;
    background: var(--border);
    border-radius: 10px;
    overflow: hidden;
  }
  .progress-fill {
    height: 100%;
    border-radius: 10px;
    background: linear-gradient(90deg, var(--accent), var(--accent2));
    transition: width 1s ease;
  }

  .empty-state {
    text-align: center;
    padding: 32px;
    color: var(--muted);
    font-family: 'Space Mono', monospace;
    font-size: 0.8rem;
  }

  .full-panel {
    grid-column: 1 / -1;
  }
</style>userbadge
</head>
<body>
<div class="page-wrap">

  <!-- Header -->
  <div class="header">
    <div class="header-left">
      <h1>⚡ Fitness Device</h1>
      <p>// sync · monitor · track</p>
    </div>
    <!-- <div class="user-badge">
      <div class="user-avatar"><?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?></div>
      <span class="user-name"><?= htmlspecialchars($user['full_name'] ?? 'User') ?></span>
    </div> -->
  </div>

  <?php if ($sync_message): ?>
  <div class="alert alert-success">✓ <?= htmlspecialchars($sync_message) ?></div>
  <?php endif; ?>

  <!-- Stats Bar -->
  <div class="stats-bar">
    <div class="stat-card">
      <div class="stat-label">BMI</div>
      <div class="stat-value <?= ($bmi < 18.5 || $bmi > 24.9) ? 'orange' : 'green' ?>"><?= number_format($bmi, 1) ?></div>
      <div class="stat-sub"><?= $bmi < 18.5 ? 'Underweight' : ($bmi > 24.9 ? 'Overweight' : 'Normal') ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">BMR</div>
      <div class="stat-value blue"><?= number_format($client['bmr'] ?? 0) ?></div>
      <div class="stat-sub">kcal / day (base)</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">TDEE</div>
      <div class="stat-value green"><?= number_format($tdee) ?></div>
      <div class="stat-sub">kcal / day (active)</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Goal</div>
      <div style="margin-top:8px">
        <span class="goal-badge <?= str_contains($goal,'loss') ? 'goal-loss' : (str_contains($goal,'gain') ? 'goal-gain' : 'goal-maintain') ?>">
          <?= htmlspecialchars(str_replace('_', ' ', $goal)) ?>
        </span>
      </div>
      <div class="stat-sub" style="margin-top:8px"><?= htmlspecialchars($client['physical_activeness'] ?? '') ?></div>
    </div>
  </div>

  <!-- Main Grid -->
  <div class="main-grid">

    <!-- Available Devices -->
    <div class="panel">
      <div class="panel-title">Available Devices</div>
      <div class="device-list">
        <div class="device-item connected">
          <div class="device-info">
            <div class="device-icon">⌚</div>
            <div>
              <div class="device-name">Smartwatch Pro</div>
              <div class="device-type">BLE · Heart Rate · Steps</div>
            </div>
          </div>
          <div class="status-dot on"></div>
        </div>
        <div class="device-item">
          <div class="device-info">
            <div class="device-icon">📱</div>
            <div>
              <div class="device-name">Mobile Health App</div>
              <div class="device-type">Steps · Sleep · GPS</div>
            </div>
          </div>
          <div class="status-dot"></div>
        </div>
        <div class="device-item">
          <div class="device-info">
            <div class="device-icon">⚖️</div>
            <div>
              <div class="device-name">Smart Scale</div>
              <div class="device-type">Weight · Body Fat · BMI</div>
            </div>
          </div>
          <div class="status-dot"></div>
        </div>
        <div class="device-item">
          <div class="device-info">
            <div class="device-icon">🏃</div>
            <div>
              <div class="device-name">Fitness Tracker Band</div>
              <div class="device-type">Steps · Calories · SpO2</div>
            </div>
          </div>
          <div class="status-dot"></div>
        </div>
      </div>
    </div>

    <!-- Sync Data -->
    <div class="panel">
      <div class="panel-title">Sync Exercise Data</div>
      <form method="POST">
        <input type="hidden" name="action" value="sync">
        <div class="form-group">
          <label>Calories Burned (kcal)</label>
          <input type="number" name="calories_burned" placeholder="e.g. 320" min="0" max="5000" step="0.1" required>
        </div>
        <div class="form-group">
          <label>Activity Date</label>
          <input type="date" name="sync_date" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
          <label>Device Source</label>
          <select name="device_source">
            <option value="smartwatch">Smartwatch Pro</option>
            <option value="mobile">Mobile Health App</option>
            <option value="band">Fitness Tracker Band</option>
            <option value="manual">Manual Entry</option>
          </select>
        </div>

        <!-- Calorie target visual -->
        <?php if ($tdee > 0): ?>
        <div class="progress-wrap" style="margin-bottom:18px">
          <div class="progress-label">
            <span>Daily Target</span>
            <span><?= number_format($tdee) ?> kcal</span>
          </div>
          <div class="progress-bar">
            <div class="progress-fill" style="width: 65%"></div>
          </div>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">⚡ Sync Now</button>
      </form>
    </div>

    <!-- Calorie Summary -->
    <div class="panel">
      <div class="panel-title">Calorie Balance</div>
      <?php
        $total_burned = array_sum(array_column($logs, 'calories_burned'));
        $today_log = array_filter($logs, fn($l) => $l['date'] === date('Y-m-d'));
        $today_burned = !empty($today_log) ? array_values($today_log)[0]['calories_burned'] : 0;
        $ring_pct = $tdee > 0 ? min(100, round(($today_burned / $tdee) * 100)) : 0;
        $circumference = 2 * M_PI * 30;
        $dash = $circumference - ($ring_pct / 100) * $circumference;
      ?>
      <div class="ring-wrap">
        <svg class="ring-svg" viewBox="0 0 80 80">
          <circle cx="40" cy="40" r="30" fill="none" stroke="#252535" stroke-width="7"/>
          <circle cx="40" cy="40" r="30" fill="none" stroke="url(#ringGrad)" stroke-width="7"
            stroke-dasharray="<?= number_format($circumference, 2) ?>"
            stroke-dashoffset="<?= number_format($dash, 2) ?>"
            stroke-linecap="round"
            transform="rotate(-90 40 40)"/>
          <defs>
            <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stop-color="#00f5a0"/>
              <stop offset="100%" stop-color="#00d4ff"/>
            </linearGradient>
          </defs>
          <text x="40" y="37" text-anchor="middle" font-size="9" fill="#e8e8f0" font-family="Space Mono"><?= $ring_pct ?>%</text>
          <text x="40" y="48" text-anchor="middle" font-size="6" fill="#6b6b88" font-family="Space Mono">today</text>
        </svg>
        <div class="ring-info">
          <h3 style="color:var(--accent)"><?= number_format($today_burned) ?> kcal</h3>
          <p>burned today</p>
          <p style="margin-top:6px">Target: <?= number_format($tdee) ?> kcal</p>
          <p>Week total: <?= number_format($total_burned) ?> kcal</p>
        </div>
      </div>
    </div>

    <!-- Recent Logs -->
    <div class="panel">
      <div class="panel-title">Recent Activity Logs</div>
      <?php if (empty($logs)): ?>
        <div class="empty-state">No activity data synced yet.<br>Connect a device to get started.</div>
      <?php else: ?>
      <table class="log-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Calories Burned</th>
            <th>vs Target</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logs as $log):
            $pct = $tdee > 0 ? round(($log['calories_burned'] / $tdee) * 100) : 0;
          ?>
          <tr>
            <td><?= htmlspecialchars($log['date']) ?></td>
            <td><span class="kcal-pill"><?= number_format($log['calories_burned']) ?> kcal</span></td>
            <td style="color:<?= $pct >= 80 ? 'var(--accent)' : 'var(--muted)' ?>;font-size:0.75rem"><?= $pct ?>%</td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

  </div><!-- /main-grid -->

  <!-- Nav bar -->
  <div style="display:flex;gap:12px;justify-content:center;padding-top:8px;border-top:1px solid var(--border)">
    <a href="barcode_scan.php" class="btn btn-outline" style="text-decoration:none">📷 Barcode Scanner</a>
    <a href="dashboard.php" class="btn btn-outline" style="text-decoration:none;color:var(--muted);border-color:var(--muted)">← Dashboard</a>
  </div>

</div>
</body>
</html>