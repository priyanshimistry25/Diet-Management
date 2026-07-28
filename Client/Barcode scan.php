<?php
session_start();
if(!isset($_SESSION["user_id"])){
    header("location:login.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "diet_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_id = $_SESSION['user_id'] ?? 'Krupa20260426222757';
include("header.php");

// Fetch client data
$client = null;
$stmt = $conn->prepare("SELECT * FROM client WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) $client = $result->fetch_assoc();
$stmt->close();

// Fetch user
$user = null;
$stmt2 = $conn->prepare("SELECT full_name FROM user WHERE user_id = ?");
$stmt2->bind_param("s", $user_id);
$stmt2->execute();
$r2 = $stmt2->get_result();
if ($r2->num_rows > 0) $user = $r2->fetch_assoc();
$stmt2->close();

// Handle meal log submission
$msg = ''; $msg_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'log_meal') {
        $calories = floatval($_POST['calories'] ?? 0);
        $meal_date = $_POST['meal_date'] ?? date('Y-m-d');
        $meal_type = $_POST['meal_type'] ?? 'lunch';
        $food_name = htmlspecialchars(trim($_POST['food_name'] ?? ''));

        if ($calories > 0) {
            // Insert/update meals table
            $check = $conn->prepare("SELECT id, calories FROM meals WHERE user_id = ? AND date = ?");
            $check->bind_param("ss", $user_id, $meal_date);
            $check->execute();
            $check_r = $check->get_result();

            if ($check_r->num_rows > 0) {
                $existing = $check_r->fetch_assoc();
                $new_cal = $existing['calories'] + $calories;
                $upd = $conn->prepare("UPDATE meals SET calories = ? WHERE user_id = ? AND date = ?");
                $upd->bind_param("dss", $new_cal, $user_id, $meal_date);
                $upd->execute(); $upd->close();
            } else {
                $ins = $conn->prepare("INSERT INTO meals (calories, date, user_id) VALUES (?, ?, ?)");
                $ins->bind_param("dss", $calories, $meal_date, $user_id);
                $ins->execute();
                $ins->close();
            }
            $check->close();

            // Update calorie_data table (breakfast/lunch/dinner field)
            
                
            

            $msg = "✓ {$food_name} ({$calories} kcal) logged as {$meal_type} on {$meal_date}";
            $msg_type = 'success';
        } else {
            $msg = "Please enter valid calorie information.";
            $msg_type = 'error';
        }
    }
}

// Fetch today's meals total
$today_cal = 0;
$t_stmt = $conn->prepare("SELECT calories FROM meals WHERE user_id = ? AND date = ?");
$today = date('Y-m-d');
$t_stmt->bind_param("ss", $user_id, $today);
$t_stmt->execute();
$t_r = $t_stmt->get_result();
if ($t_r->num_rows > 0) $today_cal = $t_r->fetch_assoc()['calories'];
$t_stmt->close();

// Fetch recent meal logs (last 7)
$recent = [];
$r_stmt = $conn->prepare("SELECT * FROM meals WHERE user_id = ? ORDER BY date DESC LIMIT 7");
$r_stmt->bind_param("s", $user_id);
$r_stmt->execute();
$r_result = $r_stmt->get_result();
while ($row = $r_result->fetch_assoc()) $recent[] = $row;
$r_stmt->close();

$conn->close();

$tdee = $client['tdee'] ?? 0;
$goal = $client['goal'] ?? '';
$remaining = max(0, $tdee - $today_cal);
$cal_pct = $tdee > 0 ? min(100, round(($today_cal / $tdee) * 100)) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Barcode Scanner — DietManager</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet">
<!-- ZXing barcode library -->
<script src="https://unpkg.com/@zxing/library@0.18.6/umd/index.min.js"></script>
<style>
  :root {
    --bg:      #f7f5ff;   /* was #08090f — soft lavender page bg */
    --surface: #ffffff;   /* was #0f1019 — clean white surface */
    --card:    #f0ebff;   /* was #13141e — light purple card bg */
    --border:  #e2daf5;   /* was #1e1f2e — lilac border */
    --accent:  #c77dff;   /* unchanged — your primary purple */
    --accent2: #7b2fff;   /* unchanged — your deep violet */
    --green:   #0d9e6e;   /* was #00f5a0 — deeper teal-green for light bg */
    --warn:    #e85c1a;   /* was #ff6b35 — slightly deepened for contrast */
    --text:    #1a1035;   /* was #e8e8f0 — deep violet-black for readability */
    --muted:   #7a6e99;   /* was #6b6b88 — warm purple-grey */
    --radius:  12px;      /* unchanged */
}

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Syne', sans-serif;
    min-height: 100vh;
    overflow-x: hidden;
  }

  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
      radial-gradient(ellipse 600px 400px at 80% 10%, rgba(199,125,255,0.06), transparent),
      radial-gradient(ellipse 400px 300px at 10% 80%, rgba(123,47,255,0.05), transparent);
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

  .header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 40px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--border);
  }

  .header h1 {
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .header p {
    color: var(--muted);
    font-size: 0.82rem;
    font-family: 'Space Mono', monospace;
    margin-top: 4px;
  }

  .user-badge {
    display: flex; align-items: center; gap: 10px;
    background: var(--card); border: 1px solid var(--border);
    border-radius: 50px; padding: 8px 18px 8px 8px;
  }

  .user-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 0.85rem; color: #fff;
  }

  .user-name { font-size: 0.82rem; font-weight: 600; }

  /* Alert */
  .alert { padding: 14px 18px; border-radius: 8px; font-size: 0.82rem;
    font-family: 'Space Mono', monospace; margin-bottom: 24px; }
  .alert-success { background: rgba(0,245,160,0.08); border: 1px solid rgba(0,245,160,0.3); color: var(--green); }
  .alert-error { background: rgba(255,107,53,0.08); border: 1px solid rgba(255,107,53,0.3); color: var(--warn); }

  /* Main grid */
  .main-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 24px;
    margin-bottom: 28px;
  }
  @media(max-width:720px){ .main-grid { grid-template-columns: 1fr; } }

  .panel {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 28px;
    transition: border-color 0.3s;
  }
  .panel:hover { border-color: rgba(199,125,255,0.25); }

  .panel-title {
    font-size: 0.68rem;
    font-family: 'Space Mono', monospace;
    color: var(--accent);
    text-transform: uppercase;
    letter-spacing: 0.14em;
    margin-bottom: 20px;
    display: flex; align-items: center; gap: 8px;
  }
  .panel-title::before {
    content: '';
    display: inline-block; width: 8px; height: 8px; border-radius: 50%;
    background: var(--accent); box-shadow: 0 0 10px var(--accent);
    animation: glow 2s ease-in-out infinite;
  }
  @keyframes glow {
    0%,100% { box-shadow: 0 0 6px var(--accent); }
    50% { box-shadow: 0 0 16px var(--accent), 0 0 28px rgba(199,125,255,0.4); }
  }

  /* Scanner box */
  .scanner-container {
    position: relative;
    width: 100%;
    border-radius: 10px;
    overflow: hidden;
    background: #000;
    aspect-ratio: 4/3;
    border: 2px solid var(--border);
  }

  #video {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
  }

  .scanner-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
  }

  .scan-frame {
    width: 60%; aspect-ratio: 1;
    border: 2px solid var(--accent);
    border-radius: 8px;
    box-shadow: 0 0 0 2000px rgba(0,0,0,0.35);
    position: relative;
  }

  .scan-frame::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--accent), transparent);
    animation: scan-line 2s ease-in-out infinite;
  }
  @keyframes scan-line {
    0% { top: 0; opacity: 1; }
    100% { top: 100%; opacity: 0; }
  }

  /* Corner accents */
  .scan-frame::after {
    content: '';
    position: absolute;
    inset: -4px;
    border: 2px solid transparent;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--accent), transparent, var(--accent2), transparent) border-box;
    -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: destination-out;
    mask-composite: exclude;
  }

  .scanner-status {
    position: absolute;
    bottom: 12px; left: 0; right: 0;
    text-align: center;
    font-family: 'Space Mono', monospace;
    font-size: 0.7rem;
    color: rgba(255,255,255,0.7);
  }

  .scanner-placeholder {
    width: 100%; aspect-ratio: 4/3;
    background: var(--surface);
    border: 2px dashed var(--border);
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    color: var(--muted);
    font-family: 'Space Mono', monospace;
    font-size: 0.8rem;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.3s;
  }
  .scanner-placeholder:hover { border-color: var(--accent); }
  .scanner-placeholder .scan-icon { font-size: 3rem; opacity: 0.5; }

  .scanner-btns { display: flex; gap: 10px; margin-top: 14px; }

  .btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    padding: 12px 22px; border-radius: 8px;
    font-family: 'Space Mono', monospace; font-size: 0.78rem; font-weight: 700;
    letter-spacing: 0.04em; border: none; cursor: pointer;
    transition: all 0.2s; text-transform: uppercase;
  }
  .btn-primary { background: linear-gradient(135deg, var(--accent), var(--accent2)); color: #fff; flex: 1; }
  .btn-primary:hover { opacity: 0.85; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(199,125,255,0.3); }
  .btn-secondary { background: var(--surface); border: 1px solid var(--border); color: var(--muted); flex: 1; }
  .btn-secondary:hover { border-color: var(--accent); color: var(--accent); }
  .btn-outline { background: transparent; color: var(--accent); border: 1px solid var(--accent); }
  .btn-outline:hover { background: rgba(199,125,255,0.08); }
  .btn-full { width: 100%; }

  /* Barcode result card */
  #result-card {
    display: none;
    margin-top: 14px;
    background: var(--surface);
    border: 1px solid rgba(199,125,255,0.3);
    border-radius: 10px;
    padding: 16px;
  }
  #result-card.visible { display: block; animation: fadeIn 0.3s ease; }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

  .result-label {
    font-size: 0.65rem; font-family: 'Space Mono', monospace;
    color: var(--accent); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;
  }
  .result-barcode {
    font-family: 'Space Mono', monospace; font-size: 1.1rem;
    font-weight: 700; color: var(--text); word-break: break-all;
  }

  /* Form */
  .form-group { margin-bottom: 16px; }
  label {
    display: block; font-size: 0.68rem; font-family: 'Space Mono', monospace;
    color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 7px;
  }
  input[type="text"], input[type="number"], input[type="date"], select, textarea {
    width: 100%; background: var(--surface); border: 1px solid var(--border);
    border-radius: 8px; color: var(--text); font-family: 'Space Mono', monospace;
    font-size: 0.88rem; padding: 11px 14px; outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  input:focus, select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(199,125,255,0.12); }

  .input-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

  /* Calorie progress */
  .cal-progress { margin-bottom: 20px; }
  .cal-numbers { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px; }
  .cal-total { font-size: 1.5rem; font-weight: 800; color: var(--accent); }
  .cal-target { font-size: 0.75rem; font-family: 'Space Mono', monospace; color: var(--muted); }
  .progress-bar { height: 6px; background: var(--border); border-radius: 10px; overflow: hidden; }
  .progress-fill { height: 100%; border-radius: 10px; background: linear-gradient(90deg, var(--accent), var(--accent2)); transition: width 1s ease; }
  .progress-labels { display: flex; justify-content: space-between; margin-top: 5px; font-size: 0.65rem; font-family: 'Space Mono', monospace; color: var(--muted); }

  /* Meal type tabs */
  .meal-tabs { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
  .meal-tab {
    padding: 7px 16px; border-radius: 50px; font-size: 0.72rem;
    font-family: 'Space Mono', monospace; border: 1px solid var(--border);
    background: transparent; color: var(--muted); cursor: pointer; transition: all 0.2s;
  }
  .meal-tab.active { background: rgba(199,125,255,0.15); border-color: var(--accent); color: var(--accent); }
  .meal-tab:hover { border-color: var(--accent); color: var(--accent); }

  /* Log table */
  .log-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
  .log-table th {
    text-align: left; padding: 9px 10px; font-family: 'Space Mono', monospace;
    font-size: 0.62rem; color: var(--muted); text-transform: uppercase;
    letter-spacing: 0.1em; border-bottom: 1px solid var(--border);
  }
  .log-table td { padding: 11px 10px; border-bottom: 1px solid rgba(30,31,46,0.8); font-family: 'Space Mono', monospace; }
  .log-table tr:last-child td { border-bottom: none; }
  .log-table tr:hover td { background: rgba(255,255,255,0.02); }

  .kcal-pill {
    display: inline-block; padding: 3px 10px; border-radius: 50px; font-size: 0.7rem; font-weight: 700;
    background: rgba(199,125,255,0.12); color: var(--accent); border: 1px solid rgba(199,125,255,0.2);
  }

  .empty-state { text-align: center; padding: 28px; color: var(--muted); font-family: 'Space Mono', monospace; font-size: 0.78rem; }

  .full-col { grid-column: 1 / -1; }

  /* Manual barcode entry */
  .manual-toggle { font-size: 0.72rem; font-family: 'Space Mono', monospace; color: var(--muted); cursor: pointer; text-decoration: underline; margin-top: 10px; display: inline-block; }
  .manual-toggle:hover { color: var(--accent); }

  #manual-entry { display: none; margin-top: 14px; }
  #manual-entry.visible { display: block; }

  /* Mock food database result */
  .food-suggestion {
    display: none;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px 14px;
    margin-top: 10px;
  }
  .food-suggestion.visible { display: flex; align-items: center; justify-content: space-between; }
  .food-sug-name { font-weight: 600; font-size: 0.88rem; }
  .food-sug-cal { font-family: 'Space Mono', monospace; font-size: 0.8rem; color: var(--accent); }

  .nav-bar {
    display: flex; gap: 12px; justify-content: center;
    padding-top: 20px; border-top: 1px solid var(--border); margin-top: 12px;
  }
</style>
</head>
<body>
<div class="page-wrap">

  <!-- Header -->
  <div class="header">
    <div>
      <h1>📷 Barcode Scanner</h1>
      <p>// scan · identify · log calories</p>
    </div>
    <div class="user-badge">
      <div class="user-avatar"><?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?></div>
      <span class="user-name"><?= htmlspecialchars($user['full_name'] ?? 'User') ?></span>
    </div>
  </div>

  <?php if ($msg): ?>
  <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
  <?php endif; ?>

  <!-- Main grid -->
  <div class="main-grid">

    <!-- Left: Scanner -->
    <div class="panel">
      <div class="panel-title">Live Camera Scanner</div>

      <div id="scanner-wrap">
        <div class="scanner-placeholder" id="start-placeholder" onclick="startScanner()">
          <div class="scan-icon">▦</div>
          <div>Click to activate camera</div>
          <div style="font-size:0.68rem;opacity:0.6">Supports EAN-13, EAN-8, QR Code, UPC-A/E</div>
        </div>
      </div>

      <div class="scanner-btns" id="scanner-btns" style="display:none">
        <button class="btn btn-primary" onclick="startScanner()" id="btn-start">▶ Start Scan</button>
        <button class="btn btn-secondary" onclick="stopScanner()" id="btn-stop">■ Stop</button>
      </div>

      <div id="result-card">
        <div class="result-label">Scanned Barcode</div>
        <div class="result-barcode" id="barcode-result">—</div>
      </div>

      <!-- Food lookup from barcode -->
      <div class="food-suggestion" id="food-suggestion">
        <div>
          <div class="food-sug-name" id="food-name-sug">—</div>
          <div style="font-size:0.72rem;color:var(--muted);font-family:'Space Mono',monospace;margin-top:3px" id="food-brand-sug"></div>
        </div>
        <div class="food-sug-cal" id="food-cal-sug">— kcal</div>
      </div>

      <span class="manual-toggle" onclick="toggleManual()">↳ Enter barcode manually</span>

      <div id="manual-entry">
        <input type="text" id="manual-barcode" placeholder="e.g. 8901491500021" maxlength="20">
        <button class="btn btn-outline btn-full" style="margin-top:10px" onclick="lookupBarcode(document.getElementById('manual-barcode').value)">
          🔍 Lookup
        </button>
      </div>
    </div>

    <!-- Right: Log Meal -->
    <div class="panel">
      <div class="panel-title">Log Meal</div>

      <!-- Calorie progress -->
      <div class="cal-progress">
        <div class="cal-numbers">
          <span class="cal-total"><?= number_format($today_cal) ?> kcal</span>
          <span class="cal-target">of <?= number_format($tdee) ?> target</span>
        </div>
        <div class="progress-bar">
          <div class="progress-fill" style="width: <?= $cal_pct ?>%"></div>
        </div>
        <div class="progress-labels">
          <span><?= $cal_pct ?>% consumed</span>
          <span><?= number_format($remaining) ?> kcal remaining</span>
        </div>
      </div>

      <!-- Meal type tabs -->
      <div class="meal-tabs">
        <button class="meal-tab active" onclick="setMealType('breakfast', this)">🌅 Breakfast</button>
        <button class="meal-tab" onclick="setMealType('lunch', this)">☀️ Lunch</button>
        <button class="meal-tab" onclick="setMealType('dinner', this)">🌙 Dinner</button>
        <button class="meal-tab" onclick="setMealType('snack', this)">🍎 Snack</button>
      </div>
      <input type="hidden" id="meal-type-input" name="meal_type" value="breakfast">

      <form method="POST" id="meal-form">
        <input type="hidden" name="action" value="log_meal">
        <input type="hidden" name="meal_type" id="meal_type_field" value="breakfast">

        <div class="form-group">
          <label>Food Name / Product</label>
          <input type="text" name="food_name" id="food-name-field" placeholder="e.g. Whole Wheat Bread" required>
        </div>

        <div class="input-row">
          <div class="form-group" style="margin-bottom:0">
            <label>Calories (kcal)</label>
            <input type="number" name="calories" id="cal-field" placeholder="e.g. 240" min="1" max="5000" step="0.5" required>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label>Date</label>
            <input type="date" name="meal_date" value="<?= date('Y-m-d') ?>">
          </div>
        </div>

        <div class="form-group" style="margin-top:16px">
          <label>Barcode (optional)</label>
          <input type="text" name="barcode" id="barcode-field" placeholder="Auto-filled from scan">
        </div>

        <button type="submit" class="btn btn-primary btn-full">+ Log Meal</button>
      </form>
    </div>

  </div>

  <!-- Recent Meal Logs -->
  <div class="panel">
    <div class="panel-title">Recent Meal Logs (Last 7 Days)</div>
    <?php if (empty($recent)): ?>
      <div class="empty-state">No meal logs found yet.<br>Scan a product to start logging.</div>
    <?php else: ?>
    <table class="log-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Total Calories</th>
          <th>vs TDEE (<?= number_format($tdee) ?> kcal)</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent as $r):
          $pct = $tdee > 0 ? round(($r['calories'] / $tdee) * 100) : 0;
          $status = $pct > 110 ? 'Over' : ($pct < 70 ? 'Under' : 'On Track');
          $status_color = $pct > 110 ? 'var(--warn)' : ($pct < 70 ? 'var(--accent2)' : 'var(--green)');
        ?>
        <tr>
          <td><?= htmlspecialchars($r['date']) ?></td>
          <td><span class="kcal-pill"><?= number_format($r['calories']) ?> kcal</span></td>
          <td style="color:var(--muted)"><?= $pct ?>%</td>
          <td style="color:<?= $status_color ?>;font-size:0.72rem;font-weight:700"><?= $status ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- Nav -->
  <div class="nav-bar">
    <a href="linkFitnessDevice.php" class="btn btn-outline" style="text-decoration:none">⚡ Fitness Device</a>
    <a href="dashboard.php" style="text-decoration:none" class="btn" style="background:transparent;border:1px solid var(--border);color:var(--muted)">← Dashboard</a>
  </div>

</div>

<script>
let codeReader = null;
let scanning = false;

// Mock food database (barcode → food info)
const foodDB = {
  '8901491500021': { name: 'Parle-G Biscuits', brand: 'Parle', calories: 447 },
  '8901058004132': { name: 'Good Day Butter Cookies', brand: 'Britannia', calories: 502 },
  '8901058012373': { name: 'Marie Lite Biscuits', brand: 'Britannia', calories: 420 },
  '8902080000012': { name: 'Lay\'s Classic Salted', brand: 'PepsiCo', calories: 536 },
  '8901030736841': { name: 'Maggi 2-Minute Noodles', brand: 'Nestlé', calories: 360 },
  '8901764107001': { name: 'Amul Butter', brand: 'Amul', calories: 717 },
  '8906012640013': { name: 'Oats Granola Bar', brand: 'True Elements', calories: 380 },
  '4006040191802': { name: 'Kinder Bueno', brand: 'Ferrero', calories: 566 },
  '0012000161155': { name: 'Tropicana Orange Juice', brand: 'PepsiCo', calories: 42 },
  '4890008100309': { name: 'Kit Kat Milk Chocolate', brand: 'Nestlé', calories: 518 },
};

function lookupBarcode(code) {
  if (!code) return;
  document.getElementById('barcode-result').textContent = code;
  document.getElementById('result-card').classList.add('visible');
  document.getElementById('barcode-field').value = code;

  const food = foodDB[code];
  if (food) {
    document.getElementById('food-name-sug').textContent = food.name;
    document.getElementById('food-brand-sug').textContent = food.brand;
    document.getElementById('food-cal-sug').textContent = food.calories + ' kcal / 100g';
    document.getElementById('food-suggestion').classList.add('visible');
    document.getElementById('food-name-field').value = food.name;
    document.getElementById('cal-field').value = food.calories;
  } else {
    document.getElementById('food-suggestion').classList.remove('visible');
    // Try Open Food Facts API
    fetch(`https://world.openfoodfacts.org/api/v0/product/${code}.json`)
      .then(r => r.json())
      .then(data => {
        if (data.status === 1 && data.product) {
          const p = data.product;
          const name = p.product_name || p.generic_name || 'Unknown Product';
          const brand = p.brands || '';
          const cal = p.nutriments?.['energy-kcal_100g'] || p.nutriments?.['energy_100g'] || 0;
          document.getElementById('food-name-sug').textContent = name;
          document.getElementById('food-brand-sug').textContent = brand;
          document.getElementById('food-cal-sug').textContent = cal ? cal + ' kcal / 100g' : 'Calories N/A';
          document.getElementById('food-suggestion').classList.add('visible');
          document.getElementById('food-name-field').value = name;
          if (cal) document.getElementById('cal-field').value = cal;
        } else {
          document.getElementById('food-name-sug').textContent = 'Product not found';
          document.getElementById('food-brand-sug').textContent = 'Enter details manually';
          document.getElementById('food-cal-sug').textContent = '';
          document.getElementById('food-suggestion').classList.add('visible');
        }
      }).catch(() => {});
  }
}

async function startScanner() {
  if (scanning) return;
  if (!window.ZXing) { alert('Barcode library not loaded.'); return; }

  // Replace placeholder with video
  const wrap = document.getElementById('scanner-wrap');
  wrap.innerHTML = `
    <div class="scanner-container">
      <video id="video" autoplay muted playsinline></video>
      <div class="scanner-overlay">
        <div class="scan-frame"></div>
      </div>
      <div class="scanner-status" id="scan-status">Initializing camera…</div>
    </div>
  `;

  document.getElementById('scanner-btns').style.display = 'flex';
  scanning = true;

  try {
    codeReader = new ZXing.BrowserMultiFormatReader();
    const devices = await codeReader.listVideoInputDevices();
    const deviceId = devices.find(d => d.label.toLowerCase().includes('back'))?.deviceId || devices[0]?.deviceId;

    await codeReader.decodeFromVideoDevice(deviceId, 'video', (result, err) => {
      if (result) {
        document.getElementById('scan-status').textContent = '✓ Scanned: ' + result.getText();
        lookupBarcode(result.getText());
        stopScanner();
      }
      if (err && !(err instanceof ZXing.NotFoundException)) {
        document.getElementById('scan-status').textContent = 'Scanning… point at barcode';
      } else if (!err) {
        document.getElementById('scan-status').textContent = 'Scanning… point at barcode';
      }
    });
  } catch(e) {
    document.getElementById('scan-status').textContent = 'Camera error: ' + e.message;
    scanning = false;
  }
}

function stopScanner() {
  if (codeReader) { codeReader.reset(); codeReader = null; }
  scanning = false;
  const wrap = document.getElementById('scanner-wrap');
  wrap.innerHTML = `
    <div class="scanner-placeholder" id="start-placeholder" onclick="startScanner()">
      <div class="scan-icon">▦</div>
      <div>Click to activate camera</div>
      <div style="font-size:0.68rem;opacity:0.6">Supports EAN-13, EAN-8, QR Code, UPC-A/E</div>
    </div>
  `;
}

function toggleManual() {
  const m = document.getElementById('manual-entry');
  m.classList.toggle('visible');
}

function setMealType(type, btn) {
  document.querySelectorAll('.meal-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('meal_type_field').value = type;
}
</script>
</body>
</html>