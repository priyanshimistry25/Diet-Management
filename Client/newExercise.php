<?php
include("../connection.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}
include("header.php");

$user_id = $_SESSION["user_id"];

// Fetch client profile
$stmt = $conn->prepare("SELECT c.*, u.full_name FROM client c JOIN user u ON c.user_id = u.user_id WHERE c.user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: login.php");
    exit();
}

$data = $result->fetch_assoc();

// Handle form submission
$save_success = false;
$save_error   = "";
$total_kcal   = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["exercises"])) {
    $weight    = floatval($_POST["weight"] ?? $data["weight"] ?? 70);
    $log_date  = date("Y-m-d");
    $exercises = $_POST["exercises"];

    $met_table = [
        "Walking"                => ["low"=>2.5,"moderate"=>3.5,"high"=>4.5,"very high"=>5.0],
        "Running"                => ["low"=>6.0,"moderate"=>8.0,"high"=>10.0,"very high"=>12.0],
        "Cycling"                => ["low"=>4.0,"moderate"=>6.0,"high"=>8.0,"very high"=>10.0],
        "Skipping / Jump Rope"   => ["low"=>8.0,"moderate"=>10.0,"high"=>12.0,"very high"=>14.0],
        "HIIT"                   => ["low"=>7.0,"moderate"=>9.0,"high"=>11.0,"very high"=>14.0],
        "Stair Climbing"         => ["low"=>4.0,"moderate"=>6.0,"high"=>8.0,"very high"=>10.0],
        "Dancing"                => ["low"=>3.0,"moderate"=>4.5,"high"=>6.0,"very high"=>8.0],
        "Swimming"               => ["low"=>5.0,"moderate"=>7.0,"high"=>9.0,"very high"=>11.0],
        "Weight Training (Light)"=> ["low"=>3.0,"moderate"=>3.5,"high"=>4.0,"very high"=>5.0],
        "Weight Training (Heavy)"=> ["low"=>4.0,"moderate"=>5.0,"high"=>6.0,"very high"=>7.0],
        "Push-ups"               => ["low"=>3.5,"moderate"=>4.5,"high"=>5.5,"very high"=>7.0],
        "Squats"                 => ["low"=>3.5,"moderate"=>4.5,"high"=>5.5,"very high"=>7.0],
        "Yoga"                   => ["low"=>2.0,"moderate"=>3.0,"high"=>4.0,"very high"=>5.0],
        "Stretching"             => ["low"=>2.0,"moderate"=>2.5,"high"=>3.0,"very high"=>3.5],
        "Pilates"                => ["low"=>2.5,"moderate"=>3.5,"high"=>4.5,"very high"=>5.5],
        "Football (Soccer)"      => ["low"=>5.0,"moderate"=>7.0,"high"=>9.0,"very high"=>11.0],
        "Cricket"                => ["low"=>4.0,"moderate"=>5.0,"high"=>6.0,"very high"=>7.0],
        "Badminton"              => ["low"=>4.5,"moderate"=>5.5,"high"=>7.0,"very high"=>9.0],
        "Basketball"             => ["low"=>5.0,"moderate"=>6.5,"high"=>8.0,"very high"=>10.0],
    ];

    foreach ($exercises as $ex) {
        $name      = trim($ex["name"]      ?? "");
        $intensity = trim($ex["intensity"] ?? "");
        $minutes   = intval($ex["minutes"] ?? 0);

        if ($name === "" || $intensity === "" || $minutes <= 0) continue;
        if (!isset($met_table[$name][$intensity])) continue;

        $met         = $met_table[$name][$intensity];
        $calories    = round($met * 3.5 * $weight / 200 * $minutes);
        $total_kcal += $calories;
    }

    if ($total_kcal > 0) {
        // Check if a record exists for this user today, update or insert
        $check = $conn->prepare("SELECT id FROM exercise WHERE user_id = ? AND date = ?");
        $check->bind_param("ss", $user_id, $log_date);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            // Update: add to existing calories_burned
            $existing = $check_result->fetch_assoc();
            $update = $conn->prepare("UPDATE exercise SET calories_burned = calories_burned + ? WHERE user_id = ? AND date = ?");
            $update->bind_param("dss", $total_kcal, $user_id, $log_date);
            if ($update->execute()) {
                $save_success = true;
            } else {
                $save_error = "Failed to update exercise record.";
            }
            $update->close();
        } else {
            // Insert new record
            $insert = $conn->prepare("INSERT INTO exercise (user_id, calories_burned, date) VALUES (?, ?, ?)");
            $insert->bind_param("sds", $user_id, $total_kcal, $log_date);
            if ($insert->execute()) {
                $save_success = true;
            } else {
                $save_error = "Failed to save exercise record.";
            }
            $insert->close();
        }
        $check->close();
    } else {
        $save_error = "No valid exercises selected. Please check intensity and duration.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Exercise Log</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #f5f3ef;
    --surface: #ffffff;
    --border: #e2dfd8;
    --text: #1a1916;
    --muted: #8a8780;
    --accent: #2d5a3d;
    --accent-light: #e8f0eb;
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

  header .user-info {
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

  .weight-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 1.75rem;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px 18px;
    flex-wrap: wrap;
  }

  .weight-row label {
    font-size: 0.8rem;
    color: var(--muted);
    font-family: var(--mono);
    text-transform: lowercase;
  }

  .weight-row input[type=number] {
    width: 90px;
    padding: 6px 10px;
    border: 1px solid var(--border);
    border-radius: 5px;
    font-family: var(--mono);
    font-size: 0.9rem;
    background: var(--bg);
    color: var(--text);
    outline: none;
    transition: border-color 0.15s;
  }

  .weight-row input[type=number]:focus { border-color: var(--accent); }

  .categories {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 1.5rem;
  }

  .cat-btn {
    font-family: var(--mono);
    font-size: 0.72rem;
    padding: 5px 12px;
    border: 1px solid var(--border);
    border-radius: 20px;
    background: var(--surface);
    color: var(--muted);
    cursor: pointer;
    transition: all 0.15s;
    text-transform: lowercase;
  }

  .cat-btn:hover, .cat-btn.active {
    background: var(--accent);
    color: #fff;
    border-color: var(--accent);
  }

  .exercise-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .ex-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    opacity: 0.6;
    transition: opacity 0.15s, border-color 0.15s, transform 0.1s;
  }

  .ex-row.active { opacity: 1; border-color: var(--accent); }
  .ex-row:hover  { transform: translateX(2px); }

  .ex-row input[type=checkbox] {
    width: 16px; height: 16px;
    cursor: pointer;
    accent-color: var(--accent);
    flex-shrink: 0;
  }

  .ex-label {
    font-size: 0.88rem;
    flex: 1;
    min-width: 130px;
    cursor: pointer;
    user-select: none;
  }

  .ex-row select {
    padding: 5px 8px;
    border: 1px solid var(--border);
    border-radius: 5px;
    font-family: var(--mono);
    font-size: 0.75rem;
    background: var(--bg);
    color: var(--text);
    outline: none;
    cursor: pointer;
    width: 118px;
    transition: border-color 0.15s;
  }

  .ex-row select:focus { border-color: var(--accent); }

  .ex-row input[type=number] {
    width: 68px;
    padding: 5px 8px;
    border: 1px solid var(--border);
    border-radius: 5px;
    font-family: var(--mono);
    font-size: 0.85rem;
    background: var(--bg);
    color: var(--text);
    outline: none;
    transition: border-color 0.15s;
  }

  .ex-row input[type=number]:focus { border-color: var(--accent); }

  .ex-unit {
    font-family: var(--mono);
    font-size: 0.72rem;
    color: var(--muted);
    width: 22px;
  }

  .ex-kcal {
    font-family: var(--mono);
    font-size: 0.78rem;
    color: var(--muted);
    min-width: 72px;
    text-align: right;
  }

  .ex-kcal.has-value { color: var(--accent); font-weight: 500; }

  .total-bar {
    margin-top: 1.75rem;
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
  .total-bar .breakdown { font-size: 0.75rem; opacity: 0.7; font-family: var(--mono); margin-top: 2px; }

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

  .hidden { display: none !important; }

  @media (max-width: 560px) {
    .ex-row { flex-wrap: wrap; gap: 8px; }
    .ex-label { min-width: 100%; }
    .ex-kcal { margin-left: auto; }
  }
</style>
</head>
<body>

<header>
  <h1>exercise log</h1>
  <span class="user-info">
    <?= htmlspecialchars($data["full_name"] ?? "user") ?> &nbsp;·&nbsp;
    <?= date("D, d M Y") ?>
  </span>
</header>

<div class="container">

  <?php if ($save_success): ?>
    <div class="alert alert-success">✓ workout saved — <?= $total_kcal ?> kcal burned logged for today.</div>
  <?php endif; ?>

  <?php if ($save_error): ?>
    <div class="alert alert-error">✗ <?= htmlspecialchars($save_error) ?></div>
  <?php endif; ?>

  <form method="POST" action="" id="log-form">

    <div class="weight-row">
      <label for="weight">body weight</label>
      <input type="number" name="weight" id="weight"
             value="<?= htmlspecialchars($data["weight"] ?? 70) ?>"
             min="1" max="300" step="0.5">
      <span style="font-family:var(--mono);font-size:0.8rem;color:var(--muted)">kg</span>
      <span style="font-family:var(--mono);font-size:0.72rem;color:var(--muted);margin-left:auto">
        calories = MET × 3.5 × kg ÷ 200 × min
      </span>
    </div>

    <div class="categories" id="cat-filters">
      <button type="button" class="cat-btn active" data-cat="all">all</button>
      <button type="button" class="cat-btn" data-cat="cardio">cardio</button>
      <button type="button" class="cat-btn" data-cat="strength">strength</button>
      <button type="button" class="cat-btn" data-cat="flexibility">flexibility</button>
      <button type="button" class="cat-btn" data-cat="sports">sports</button>
    </div>

    <div class="exercise-list" id="rows">
      <?php
      $exercise_list = [
        ["Walking",                "cardio"],
        ["Running",                "cardio"],
        ["Cycling",                "cardio"],
        ["Skipping / Jump Rope",   "cardio"],
        ["HIIT",                   "cardio"],
        ["Stair Climbing",         "cardio"],
        ["Dancing",                "cardio"],
        ["Swimming",               "cardio"],
        ["Weight Training (Light)","strength"],
        ["Weight Training (Heavy)","strength"],
        ["Push-ups",               "strength"],
        ["Squats",                 "strength"],
        ["Yoga",                   "flexibility"],
        ["Stretching",             "flexibility"],
        ["Pilates",                "flexibility"],
        ["Football (Soccer)",      "sports"],
        ["Cricket",                "sports"],
        ["Badminton",              "sports"],
        ["Basketball",             "sports"],
      ];

      foreach ($exercise_list as $i => [$name, $cat]): ?>
        <div class="ex-row" data-cat="<?= $cat ?>" data-i="<?= $i ?>">
          <input type="checkbox" id="cb<?= $i ?>" class="ex-cb">
          <label class="ex-label" for="cb<?= $i ?>"><?= htmlspecialchars($name) ?></label>

          <select class="ex-sel" name="exercises[<?= $i ?>][intensity]">
            <option value="" disabled selected>intensity</option>
            <option value="low">low</option>
            <option value="moderate">moderate</option>
            <option value="high">high</option>
            <option value="very high">very high</option>
          </select>

          <input type="hidden" name="exercises[<?= $i ?>][name]" value="<?= htmlspecialchars($name) ?>">

          <input type="number" class="ex-min"
                 name="exercises[<?= $i ?>][minutes]"
                 placeholder="min" min="0" max="999">
          <span class="ex-unit">min</span>
          <span class="ex-kcal">— kcal</span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="total-bar">
      <div>
        <div class="label">total calories burned</div>
        <div class="value" id="total-kcal">0 kcal</div>
        <div class="breakdown" id="breakdown">no exercises selected</div>
      </div>
      <div class="btn-row">
        <button type="submit" class="save-btn" id="save-btn" disabled>save workout ↑</button>
        <button type="button" class="reset-btn" id="reset-btn">reset</button>
      </div>
    </div>

  </form>
</div>

<script>
const metTable = {
  "Walking":                {low:2.5,moderate:3.5,high:4.5,"very high":5.0},
  "Running":                {low:6.0,moderate:8.0,high:10.0,"very high":12.0},
  "Cycling":                {low:4.0,moderate:6.0,high:8.0,"very high":10.0},
  "Skipping / Jump Rope":   {low:8.0,moderate:10.0,high:12.0,"very high":14.0},
  "HIIT":                   {low:7.0,moderate:9.0,high:11.0,"very high":14.0},
  "Stair Climbing":         {low:4.0,moderate:6.0,high:8.0,"very high":10.0},
  "Dancing":                {low:3.0,moderate:4.5,high:6.0,"very high":8.0},
  "Swimming":               {low:5.0,moderate:7.0,high:9.0,"very high":11.0},
  "Weight Training (Light)":{low:3.0,moderate:3.5,high:4.0,"very high":5.0},
  "Weight Training (Heavy)":{low:4.0,moderate:5.0,high:6.0,"very high":7.0},
  "Push-ups":               {low:3.5,moderate:4.5,high:5.5,"very high":7.0},
  "Squats":                 {low:3.5,moderate:4.5,high:5.5,"very high":7.0},
  "Yoga":                   {low:2.0,moderate:3.0,high:4.0,"very high":5.0},
  "Stretching":             {low:2.0,moderate:2.5,high:3.0,"very high":3.5},
  "Pilates":                {low:2.5,moderate:3.5,high:4.5,"very high":5.5},
  "Football (Soccer)":      {low:5.0,moderate:7.0,high:9.0,"very high":11.0},
  "Cricket":                {low:4.0,moderate:5.0,high:6.0,"very high":7.0},
  "Badminton":              {low:4.5,moderate:5.5,high:7.0,"very high":9.0},
  "Basketball":             {low:5.0,moderate:6.5,high:8.0,"very high":10.0},
};

document.querySelectorAll('.ex-row').forEach(row => {
  const cb     = row.querySelector('.ex-cb');
  const sel    = row.querySelector('.ex-sel');
  const num    = row.querySelector('.ex-min');
  const kcalEl = row.querySelector('.ex-kcal');
  const name   = row.querySelector('.ex-label').textContent.trim();

  sel.disabled = true;
  num.disabled = true;

  const calc = () => {
    const on = cb.checked;
    row.classList.toggle('active', on);
    sel.disabled = !on;
    num.disabled = !on;

    const w   = parseFloat(document.getElementById('weight').value) || 70;
    const met = metTable[name]?.[sel.value];
    const t   = parseFloat(num.value);

    if (on && met && t > 0) {
      const c = Math.round(met * 3.5 * w / 200 * t);
      kcalEl.textContent = c + ' kcal';
      kcalEl.classList.add('has-value');
    } else {
      kcalEl.textContent = on ? '— kcal' : '';
      kcalEl.classList.remove('has-value');
    }
    updateTotal();
  };

  cb.addEventListener('change', calc);
  sel.addEventListener('change', calc);
  num.addEventListener('input', calc);
});

document.getElementById('weight').addEventListener('input', () => {
  document.querySelectorAll('.ex-row').forEach(row => {
    const cb     = row.querySelector('.ex-cb');
    const sel    = row.querySelector('.ex-sel');
    const num    = row.querySelector('.ex-min');
    const kcalEl = row.querySelector('.ex-kcal');
    const name   = row.querySelector('.ex-label').textContent.trim();
    const w      = parseFloat(document.getElementById('weight').value) || 70;
    const met    = metTable[name]?.[sel.value];
    const t      = parseFloat(num.value);
    if (cb.checked && met && t > 0) {
      kcalEl.textContent = Math.round(met * 3.5 * w / 200 * t) + ' kcal';
      kcalEl.classList.add('has-value');
    }
  });
  updateTotal();
});

function updateTotal() {
  let sum = 0;
  const active = [];
  const w = parseFloat(document.getElementById('weight').value) || 70;

  document.querySelectorAll('.ex-row').forEach(row => {
    const cb   = row.querySelector('.ex-cb');
    const sel  = row.querySelector('.ex-sel');
    const num  = row.querySelector('.ex-min');
    const name = row.querySelector('.ex-label').textContent.trim();
    const met  = metTable[name]?.[sel.value];
    const t    = parseFloat(num.value);
    if (cb.checked && met && t > 0) {
      sum += met * 3.5 * w / 200 * t;
      active.push(name.split(' ')[0]);
    }
  });

  document.getElementById('total-kcal').textContent = Math.round(sum) + ' kcal';
  document.getElementById('breakdown').textContent =
    active.length ? active.join(' · ') : 'no exercises selected';
  document.getElementById('save-btn').disabled = active.length === 0;
}

document.querySelectorAll('.cat-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const cat = btn.dataset.cat;
    document.querySelectorAll('.ex-row').forEach(row => {
      row.classList.toggle('hidden', cat !== 'all' && row.dataset.cat !== cat);
    });
  });
});

document.getElementById('reset-btn').addEventListener('click', () => {
  document.querySelectorAll('.ex-row').forEach(row => {
    const cb     = row.querySelector('.ex-cb');
    const sel    = row.querySelector('.ex-sel');
    const num    = row.querySelector('.ex-min');
    const kcalEl = row.querySelector('.ex-kcal');
    cb.checked = false;
    sel.selectedIndex = 0;
    sel.disabled = true;
    num.value = '';
    num.disabled = true;
    kcalEl.textContent = '';
    kcalEl.classList.remove('has-value');
    row.classList.remove('active');
  });
  updateTotal();
});
</script>
</body>
</html>