<?php
include("../connection.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}
include("header.php");

$user_id = $_SESSION["user_id"];

// Fetch client + user info
$stmt = $conn->prepare("SELECT c.*, u.full_name FROM client c JOIN user u ON c.user_id = u.user_id WHERE c.user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: login.php");
    exit();
}

$data = $result->fetch_assoc();

// ── Handle form submission ──────────────────────────────────────────────────
$save_success  = false;
$save_error    = "";
$total_calories = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["meals"])) {
    $log_date = date("Y-m-d");
    $meals    = $_POST["meals"]; // array of {name, calories}

    foreach ($meals as $meal) {
        $cal = floatval($meal["calories"] ?? 0);
        if ($cal > 0) $total_calories += $cal;
    }

    if ($total_calories > 0) {
        // Check if a record exists for this user today
        $check = $conn->prepare("SELECT id FROM meals WHERE user_id = ? AND date = ?");
        $check->bind_param("ss", $user_id, $log_date);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            // Update: accumulate calories for the day
            $update = $conn->prepare("UPDATE meals SET calories = calories + ? WHERE user_id = ? AND date = ?");
            $update->bind_param("dss", $total_calories, $user_id, $log_date);
            $save_success = $update->execute();
            if (!$save_success) $save_error = "Failed to update meal record.";
            $update->close();
        } else {
            // Insert new record
            $insert = $conn->prepare("INSERT INTO meals (user_id, calories, date) VALUES (?, ?, ?)");
            $insert->bind_param("sds", $user_id, $total_calories, $log_date);
            $save_success = $insert->execute();
            if (!$save_success) $save_error = "Failed to save meal record.";
            $insert->close();
        }
        $check->close();
    } else {
        $save_error = "Please enter calories for at least one meal.";
    }
}

// ── Fetch today's existing meal log ────────────────────────────────────────
$today_stmt = $conn->prepare("SELECT calories FROM meals WHERE user_id = ? AND date = ?");
$today_date = date("Y-m-d");
$today_stmt->bind_param("ss", $user_id, $today_date);
$today_stmt->execute();
$today_result = $today_stmt->get_result();
$today_row    = $today_result->fetch_assoc();
$logged_today = $today_row ? floatval($today_row["calories"]) : 0;

$tdee = floatval($data["tdee"] ?? 0);
$goal = $data["goal"] ?? "";

// Calorie targets based on goal
if ($goal === "weight_loss") {
    $target = $tdee - 500;
    $goal_label = "weight loss";
} elseif ($goal === "weight_gain") {
    $target = $tdee + 500;
    $goal_label = "weight gain";
} else {
    $target = $tdee;
    $goal_label = "maintenance";
}

$remaining = max(0, $target - $logged_today);
$percent   = $target > 0 ? min(100, round(($logged_today / $target) * 100)) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meal Log</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #f5f3ef;
    --surface: #ffffff;
    --border: #e2dfd8;
    --text: #1a1916;
    --muted: #8a8780;
    --accent: #5a3d2d;
    --accent-light: #f0ebe8;
    --green: #2d5a3d;
    --mono: 'DM Mono', monospace;
    --sans: 'DM Sans', sans-serif;
  }

  body {
    font-family: var(--sans);
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    padding: 2rem 1rem 4rem;
  }

  header {
    max-width: 780px;
    margin: 0 auto 2.5rem;
    display: flex;
    align-items: baseline;
    gap: 1rem;
    border-bottom: 1px solid var(--border);
    padding-bottom: 1.25rem;
    flex-wrap: wrap;
  }

  header h1 {
    font-family: var(--mono);
    font-size: 1.15rem;
    font-weight: 500;
    letter-spacing: -0.01em;
    text-transform: lowercase;
  }

  .user-info {
    font-size: 0.78rem;
    color: var(--muted);
    font-family: var(--mono);
    margin-left: auto;
  }

  .container { max-width: 780px; margin: 0 auto; }

  .alert {
    padding: 12px 16px;
    border-radius: 8px;
    font-family: var(--mono);
    font-size: 0.8rem;
    margin-bottom: 1.25rem;
  }
  .alert-success { background: #e8f0eb; color: #2d5a3d; border: 1px solid #b5d4be; }
  .alert-error   { background: #fdecea; color: #c0392b; border: 1px solid #f5c0bb; }

  /* ── Daily summary card ── */
  .summary-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 20px 22px;
    margin-bottom: 1.75rem;
  }

  .summary-card .row {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
  }

  .summary-card .stat { text-align: center; }
  .summary-card .stat .val {
    font-family: var(--mono);
    font-size: 1.5rem;
    font-weight: 500;
    letter-spacing: -0.03em;
    line-height: 1;
  }
  .summary-card .stat .lbl {
    font-family: var(--mono);
    font-size: 0.65rem;
    color: var(--muted);
    margin-top: 3px;
    text-transform: lowercase;
  }

  .progress-track {
    height: 8px;
    background: var(--border);
    border-radius: 4px;
    overflow: hidden;
  }

  .progress-fill {
    height: 100%;
    border-radius: 4px;
    background: var(--accent);
    transition: width 0.4s ease;
  }

  .progress-fill.over { background: #c0392b; }

  .goal-badge {
    display: inline-block;
    font-family: var(--mono);
    font-size: 0.65rem;
    padding: 3px 9px;
    border-radius: 20px;
    background: var(--accent-light);
    color: var(--accent);
    margin-top: 8px;
    text-transform: lowercase;
  }

  /* ── Meal rows ── */
  .section-title {
    font-family: var(--mono);
    font-size: 0.72rem;
    color: var(--muted);
    text-transform: lowercase;
    margin-bottom: 10px;
    letter-spacing: 0.04em;
  }

  .meal-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 1.5rem;
  }

  .meal-row {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px 18px;
    transition: border-color 0.15s, transform 0.1s;
  }

  .meal-row:hover { transform: translateX(2px); }
  .meal-row.has-value { border-color: var(--accent); }

  .meal-icon {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--accent-light);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
  }

  .meal-info { flex: 1; }

  .meal-info .meal-name {
    font-size: 0.9rem;
    font-weight: 500;
  }

  .meal-info .meal-hint {
    font-family: var(--mono);
    font-size: 0.68rem;
    color: var(--muted);
    margin-top: 2px;
  }

  .meal-input-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .meal-input-wrap input[type=number] {
    width: 90px;
    padding: 7px 10px;
    border: 1px solid var(--border);
    border-radius: 5px;
    font-family: var(--mono);
    font-size: 0.9rem;
    background: var(--bg);
    color: var(--text);
    outline: none;
    transition: border-color 0.15s;
    text-align: right;
  }

  .meal-input-wrap input[type=number]:focus { border-color: var(--accent); }

  .meal-input-wrap .unit {
    font-family: var(--mono);
    font-size: 0.72rem;
    color: var(--muted);
  }

  /* ── Snack adder ── */
  .snack-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 16px 18px;
    margin-bottom: 1.75rem;
  }

  .snack-section .snack-title {
    font-family: var(--mono);
    font-size: 0.72rem;
    color: var(--muted);
    text-transform: lowercase;
    margin-bottom: 12px;
  }

  .snack-rows { display: flex; flex-direction: column; gap: 8px; }

  .snack-row {
    display: flex;
    gap: 8px;
    align-items: center;
  }

  .snack-row input[type=text] {
    flex: 1;
    padding: 7px 10px;
    border: 1px solid var(--border);
    border-radius: 5px;
    font-family: var(--sans);
    font-size: 0.85rem;
    background: var(--bg);
    color: var(--text);
    outline: none;
    transition: border-color 0.15s;
  }

  .snack-row input[type=text]:focus { border-color: var(--accent); }

  .snack-row input[type=number] {
    width: 90px;
    padding: 7px 10px;
    border: 1px solid var(--border);
    border-radius: 5px;
    font-family: var(--mono);
    font-size: 0.85rem;
    background: var(--bg);
    color: var(--text);
    outline: none;
    transition: border-color 0.15s;
    text-align: right;
  }

  .snack-row input[type=number]:focus { border-color: var(--accent); }

  .snack-row .unit {
    font-family: var(--mono);
    font-size: 0.72rem;
    color: var(--muted);
    width: 26px;
  }

  .snack-row .del-btn {
    width: 28px; height: 28px;
    border: 1px solid var(--border);
    border-radius: 5px;
    background: var(--bg);
    color: var(--muted);
    cursor: pointer;
    font-size: 0.9rem;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.15s;
    flex-shrink: 0;
  }

  .snack-row .del-btn:hover { background: #fdecea; border-color: #f5c0bb; color: #c0392b; }

  .add-snack-btn {
    margin-top: 10px;
    font-family: var(--mono);
    font-size: 0.72rem;
    padding: 6px 14px;
    border: 1px dashed var(--border);
    border-radius: 5px;
    background: transparent;
    color: var(--muted);
    cursor: pointer;
    transition: all 0.15s;
    text-transform: lowercase;
  }

  .add-snack-btn:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }

  /* ── Total bar ── */
  .total-bar {
    margin-top: 0;
    background: var(--accent);
    color: #fff;
    border-radius: 8px;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .total-bar .label   { font-family: var(--mono); font-size: 0.78rem; opacity: 0.75; }
  .total-bar .value   { font-family: var(--mono); font-size: 1.6rem; font-weight: 500; letter-spacing: -0.03em; }
  .total-bar .sub     { font-size: 0.72rem; opacity: 0.7; font-family: var(--mono); margin-top: 2px; }

  .btn-row { display: flex; gap: 10px; flex-wrap: wrap; }

  .save-btn {
    font-family: var(--mono);
    font-size: 0.78rem;
    padding: 9px 20px;
    border: 1px solid rgba(255,255,255,0.5);
    border-radius: 5px;
    background: rgba(255,255,255,0.15);
    color: #fff;
    cursor: pointer;
    transition: background 0.15s;
  }

  .save-btn:hover { background: rgba(255,255,255,0.28); }
  .save-btn:disabled { opacity: 0.4; cursor: not-allowed; }

  .reset-btn {
    font-family: var(--mono);
    font-size: 0.72rem;
    padding: 7px 14px;
    border: 1px solid rgba(255,255,255,0.35);
    border-radius: 5px;
    background: transparent;
    color: #fff;
    cursor: pointer;
    transition: background 0.15s;
  }

  .reset-btn:hover { background: rgba(255,255,255,0.15); }

  @media (max-width: 560px) {
    .meal-row { flex-wrap: wrap; }
    .meal-input-wrap { margin-left: auto; }
    .snack-row { flex-wrap: wrap; }
    .snack-row input[type=text] { min-width: 100%; }
  }
</style>
</head>
<body>

<header>
  <h1>meal log</h1>
  <span class="user-info">
    <?= htmlspecialchars($data["full_name"] ?? "user") ?> &nbsp;·&nbsp;
    <?= date("D, d M Y") ?>
  </span>
</header>

<div class="container">

  <?php if ($save_success): ?>
    <div class="alert alert-success">✓ meal saved — <?= number_format($total_calories) ?> kcal logged. Total today: <?= number_format($logged_today) ?> kcal.</div>
  <?php endif; ?>

  <?php if ($save_error): ?>
    <div class="alert alert-error">✗ <?= htmlspecialchars($save_error) ?></div>
  <?php endif; ?>

  <!-- Daily summary -->
  <div class="summary-card">
    <div class="row">
      <div class="stat">
        <div class="val"><?= number_format($logged_today) ?></div>
        <div class="lbl">consumed today</div>
      </div>
      <div class="stat">
        <div class="val"><?= number_format($target) ?></div>
        <div class="lbl">daily target</div>
      </div>
      <div class="stat">
        <div class="val" style="color:<?= $logged_today > $target ? '#c0392b' : 'var(--green)' ?>">
          <?= $logged_today > $target ? '+' . number_format($logged_today - $target) : number_format($remaining) ?>
        </div>
        <div class="lbl"><?= $logged_today > $target ? 'over target' : 'remaining' ?></div>
      </div>
    </div>
    <div class="progress-track">
      <div class="progress-fill <?= $percent >= 100 ? 'over' : '' ?>" style="width:<?= $percent ?>%"></div>
    </div>
    <span class="goal-badge">goal: <?= htmlspecialchars($goal_label) ?> · tdee <?= number_format($tdee) ?> kcal</span>
  </div>

  <form method="POST" action="" id="meal-form">

    <p class="section-title">main meals</p>
    <div class="meal-list">
      <?php
      $main_meals = [
        ["breakfast", "🌅", "e.g. oats, eggs, fruit", 0],
        ["lunch",     "☀️", "e.g. rice, dal, salad",  1],
        ["dinner",    "🌙", "e.g. roti, sabzi, soup",  2],
      ];
      foreach ($main_meals as [$mname, $icon, $hint, $i]): ?>
        <div class="meal-row" id="meal-row-<?= $i ?>">
          <div class="meal-icon"><?= $icon ?></div>
          <div class="meal-info">
            <div class="meal-name"><?= $mname ?></div>
            <div class="meal-hint"><?= $hint ?></div>
          </div>
          <div class="meal-input-wrap">
            <input type="number"
                   name="meals[<?= $i ?>][calories]"
                   id="meal-cal-<?= $i ?>"
                   class="meal-cal"
                   placeholder="0"
                   min="0" max="9999" step="1"
                   data-name="<?= $mname ?>">
            <span class="unit">kcal</span>
          </div>
          <input type="hidden" name="meals[<?= $i ?>][name]" value="<?= $mname ?>">
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Snacks / extras -->
    <div class="snack-section">
      <div class="snack-title">snacks &amp; extras</div>
      <div class="snack-rows" id="snack-rows">
        <!-- dynamic rows added by JS -->
      </div>
      <button type="button" class="add-snack-btn" id="add-snack">+ add snack / drink</button>
    </div>

    <div class="total-bar">
      <div>
        <div class="label">total calories this entry</div>
        <div class="value" id="total-kcal">0 kcal</div>
        <div class="sub" id="meal-breakdown">no meals entered</div>
      </div>
      <div class="btn-row">
        <button type="submit" class="save-btn" id="save-btn" disabled>save meals ↑</button>
        <button type="button" class="reset-btn" id="reset-btn">reset</button>
      </div>
    </div>

  </form>
</div>

<script>
let snackCount = 0;

function updateTotal() {
  let sum = 0;
  const parts = [];

  // Main meals
  document.querySelectorAll('.meal-cal').forEach(input => {
    const v = parseFloat(input.value) || 0;
    if (v > 0) {
      sum += v;
      parts.push(input.dataset.name + ' ' + Math.round(v));
    }
    input.closest('.meal-row').classList.toggle('has-value', v > 0);
  });

  // Snacks
  document.querySelectorAll('.snack-cal').forEach(input => {
    const v = parseFloat(input.value) || 0;
    if (v > 0) {
      sum += v;
      const nameInput = input.closest('.snack-row').querySelector('.snack-name');
      const label = nameInput?.value.trim() || 'snack';
      parts.push(label + ' ' + Math.round(v));
    }
  });

  document.getElementById('total-kcal').textContent = Math.round(sum) + ' kcal';
  document.getElementById('meal-breakdown').textContent =
    parts.length ? parts.join(' · ') : 'no meals entered';
  document.getElementById('save-btn').disabled = sum === 0;
}

// Attach listeners to main meal inputs
document.querySelectorAll('.meal-cal').forEach(input => {
  input.addEventListener('input', updateTotal);
});

// Add snack row
document.getElementById('add-snack').addEventListener('click', () => {
  snackCount++;
  const idx = 100 + snackCount; // offset to avoid collision with main meal indexes
  const row = document.createElement('div');
  row.className = 'snack-row';
  row.id = 'snack-row-' + snackCount;
  row.innerHTML = `
    <input type="text"
           class="snack-name"
           name="meals[${idx}][name]"
           placeholder="snack name"
           maxlength="80">
    <input type="number"
           class="snack-cal"
           name="meals[${idx}][calories]"
           placeholder="0"
           min="0" max="9999" step="1">
    <span class="unit">kcal</span>
    <button type="button" class="del-btn" onclick="removeSnack(${snackCount})">×</button>
  `;
  document.getElementById('snack-rows').appendChild(row);
  row.querySelector('.snack-cal').addEventListener('input', updateTotal);
  row.querySelector('.snack-name').addEventListener('input', updateTotal);
});

function removeSnack(id) {
  document.getElementById('snack-row-' + id)?.remove();
  updateTotal();
}

// Reset
document.getElementById('reset-btn').addEventListener('click', () => {
  document.querySelectorAll('.meal-cal').forEach(input => {
    input.value = '';
    input.closest('.meal-row').classList.remove('has-value');
  });
  document.getElementById('snack-rows').innerHTML = '';
  snackCount = 0;
  updateTotal();
});
</script>
</body>
</html>