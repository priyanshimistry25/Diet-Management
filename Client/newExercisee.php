<?php
include("../connection.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}
//this is a demo file it is not used in website
$user_id = $_SESSION["user_id"];

// Modified to join with user table to get the full name as per your SQL schema
$stmt = $conn->prepare("SELECT c.*, u.full_name FROM client c JOIN user u ON c.user_id = u.user_id WHERE c.user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($result->num_rows !== 1) {
    header("Location: login.php");
    exit();
}

$save_success = false;
$save_error   = "";
$total_kcal   = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["exercises"])) {
    $weight    = floatval($_POST["weight"] ?? 70);
    $log_date  = date("Y-m-d");
    $exercises = $_POST["exercises"]; 
    
    // MET table 
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

        $met      = $met_table[$name][$intensity];
        $calories = round($met * 3.5 * $weight / 200 * $minutes);
        $total_kcal += $calories;
    }

    if ($total_kcal > 0) {
        // Updated to match your SQL table 'exercise' (user_id, calories_burned, date)
        $insert = $conn->prepare("INSERT INTO exercise (user_id, calories_burned, date) VALUES (?, ?, ?)");
        $insert->bind_param("sds", $user_id, $total_kcal, $log_date);
        
        if ($insert->execute()) {
            $save_success = true;
        } else {
            $save_error = "Database error: Unable to save workout.";
        }
        $insert->close();
    } else {
        $save_error = "Please select at least one exercise with valid duration.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exercise Log</title>
    <style>
        /* ... [Keep your existing CSS here] ... */
    </style>
</head>
<body>

<header>
  <h1>exercise log</h1>
  <span class="user-info">
    <?= htmlspecialchars($data["full_name"] ?? "User") ?> &nbsp;·&nbsp;
    <?= date("D, d M Y") ?>
  </span>
</header>

<div class="container">
    <?php if ($save_success): ?>
        <div class="alert alert-success">✓ workout saved successfully — <?= $total_kcal ?> kcal logged for today.</div>
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
    /* ... [Keep your existing JavaScript here] ... */
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
  const cb    = row.querySelector('.ex-cb');
  const sel   = row.querySelector('.ex-sel');
  const num   = row.querySelector('.ex-min');
  const kcalEl= row.querySelector('.ex-kcal');
  const name  = row.querySelector('.ex-label').textContent.trim();

  // Disable form fields when unchecked so they don't submit
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
    const cb    = row.querySelector('.ex-cb');
    const sel   = row.querySelector('.ex-sel');
    const num   = row.querySelector('.ex-min');
    const kcalEl= row.querySelector('.ex-kcal');
    const name  = row.querySelector('.ex-label').textContent.trim();
    const w     = parseFloat(document.getElementById('weight').value) || 70;
    const met   = metTable[name]?.[sel.value];
    const t     = parseFloat(num.value);
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
    const cb    = row.querySelector('.ex-cb');
    const sel   = row.querySelector('.ex-sel');
    const num   = row.querySelector('.ex-min');
    const kcalEl= row.querySelector('.ex-kcal');
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