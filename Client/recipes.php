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

// ── Fetch all foods from food_database grouped by category ─────────────────
$foods_result = $conn->query("SELECT * FROM food_database ORDER BY category, name");
$foods_by_category = [];
while ($row = $foods_result->fetch_assoc()) {
    $foods_by_category[$row["category"]][] = $row;
}

// ── Handle form submission ──────────────────────────────────────────────────
$save_success  = false;
$save_error    = "";
$total_calories = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["meals"])) {
    $log_date = date("Y-m-d");
    $meals    = $_POST["meals"];

    foreach ($meals as $meal) {
        $cal = floatval($meal["calories"] ?? 0);
        if ($cal > 0) $total_calories += $cal;
    }

    if ($total_calories > 0) {
        $check = $conn->prepare("SELECT id FROM meals WHERE user_id = ? AND date = ?");
        $check->bind_param("ss", $user_id, $log_date);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $update = $conn->prepare("UPDATE meals SET calories = calories + ? WHERE user_id = ? AND date = ?");
            $update->bind_param("dss", $total_calories, $user_id, $log_date);
            $save_success = $update->execute();
            if (!$save_success) $save_error = "Failed to update meal record.";
            $update->close();
        } else {
            $insert = $conn->prepare("INSERT INTO meals (user_id, calories, date) VALUES (?, ?, ?)");
            $insert->bind_param("sds", $user_id, $total_calories, $log_date);
            $save_success = $insert->execute();
            if (!$save_success) $save_error = "Failed to save meal record.";
            $insert->close();
        }
        $check->close();
    } else {
        $save_error = "Please select at least one food item.";
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

// Category icons & display order
$category_meta = [
    "breakfast" => ["icon" => "🌅", "label" => "Breakfast"],
    "lunch"     => ["icon" => "☀️",  "label" => "Lunch"],
    "dinner"    => ["icon" => "🌙", "label" => "Dinner"],
    "snack"     => ["icon" => "🍿", "label" => "Snacks"],
    "sweet"     => ["icon" => "🍬", "label" => "Sweets"],
    "beverage"  => ["icon" => "🥤", "label" => "Beverages"],
    "staple"    => ["icon" => "🧂", "label" => "Staples & Dairy"],
];
$display_order = array_keys($category_meta);

// Pass food data to JS
$foods_js = [];
foreach ($foods_by_category as $cat => $items) {
    foreach ($items as $item) {
        $foods_js[$item["id"]] = [
            "cal_per_100" => $item["calories"],
            "serving"     => $item["typical_serving_g"],
            "unit"        => $item["unit_label"],
            "name"        => $item["name"],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recipes &amp; Food Log</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:           #f5f3ef;
    --surface:      #ffffff;
    --border:       #e2dfd8;
    --text:         #1a1916;
    --muted:        #8a8780;
    --accent:       #5a3d2d;
    --accent-light: #f0ebe8;
    --green:        #2d5a3d;
    --mono:         'DM Mono', monospace;
    --sans:         'DM Sans', sans-serif;
  }

  body {
    font-family: var(--sans);
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    padding: 2rem 1rem 5rem;
  }

  header {
    max-width: 900px;
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

  .container { max-width: 900px; margin: 0 auto; }

  /* ── Alerts ── */
  .alert {
    padding: 12px 16px;
    border-radius: 8px;
    font-family: var(--mono);
    font-size: 0.8rem;
    margin-bottom: 1.25rem;
  }
  .alert-success { background: #e8f0eb; color: #2d5a3d; border: 1px solid #b5d4be; }
  .alert-error   { background: #fdecea; color: #c0392b; border: 1px solid #f5c0bb; }

  /* ── Summary card ── */
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

  /* ── Search bar ── */
  .search-wrap {
    position: relative;
    margin-bottom: 1.5rem;
  }

  .search-wrap input {
    width: 100%;
    padding: 11px 16px 11px 40px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--surface);
    font-family: var(--mono);
    font-size: 0.82rem;
    color: var(--text);
    outline: none;
    transition: border-color 0.15s;
  }

  .search-wrap input:focus { border-color: var(--accent); }

  .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted);
    font-size: 1rem;
    pointer-events: none;
  }

  .search-count {
    font-family: var(--mono);
    font-size: 0.7rem;
    color: var(--muted);
    margin-top: 6px;
    padding-left: 2px;
  }

  /* ── Category sections ── */
  .category-section {
    margin-bottom: 1.5rem;
  }

  .category-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    cursor: pointer;
    user-select: none;
  }

  .category-icon { font-size: 1rem; }

  .category-title {
    font-family: var(--mono);
    font-size: 0.72rem;
    color: var(--muted);
    text-transform: lowercase;
    letter-spacing: 0.04em;
    flex: 1;
  }

  .category-count {
    font-family: var(--mono);
    font-size: 0.65rem;
    color: var(--muted);
    background: var(--accent-light);
    padding: 2px 7px;
    border-radius: 10px;
  }

  .category-selected-count {
    font-family: var(--mono);
    font-size: 0.65rem;
    color: var(--accent);
    font-weight: 500;
    display: none;
  }

  .category-toggle-btn {
    font-size: 0.7rem;
    color: var(--muted);
    transition: transform 0.2s;
  }

  .category-toggle-btn.collapsed { transform: rotate(-90deg); }

  /* ── Food grid ── */
  .food-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 8px;
  }

  .food-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: border-color 0.15s, box-shadow 0.15s;
  }

  .food-card.selected {
    border-color: var(--accent);
    box-shadow: 0 0 0 1px var(--accent);
  }

  .food-card.hidden { display: none; }

  .food-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
  }

  .food-name {
    font-size: 0.85rem;
    font-weight: 500;
    line-height: 1.3;
    flex: 1;
  }

  .food-region {
    font-family: var(--mono);
    font-size: 0.6rem;
    color: var(--muted);
    background: var(--bg);
    border: 1px solid var(--border);
    padding: 2px 6px;
    border-radius: 8px;
    white-space: nowrap;
    flex-shrink: 0;
  }

  .food-macros {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .macro {
    font-family: var(--mono);
    font-size: 0.62rem;
    color: var(--muted);
  }

  .macro span {
    color: var(--text);
    font-weight: 500;
  }

  /* ── Serving + qty row ── */
  .food-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .qty-wrap {
    display: flex;
    align-items: center;
    border: 1px solid var(--border);
    border-radius: 6px;
    overflow: hidden;
    flex-shrink: 0;
  }

  .qty-btn {
    width: 28px;
    height: 28px;
    background: var(--bg);
    border: none;
    cursor: pointer;
    font-size: 1rem;
    color: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.1s;
  }
  .qty-btn:hover { background: var(--accent-light); }

  .qty-input {
    width: 54px;
    height: 28px;
    border: none;
    border-left: 1px solid var(--border);
    border-right: 1px solid var(--border);
    text-align: center;
    font-family: var(--mono);
    font-size: 0.78rem;
    background: var(--surface);
    color: var(--text);
    outline: none;
  }

  .qty-unit {
    font-family: var(--mono);
    font-size: 0.68rem;
    color: var(--muted);
    padding: 0 6px;
    background: var(--bg);
    height: 28px;
    display: flex;
    align-items: center;
    border-left: 1px solid var(--border);
  }

  .food-kcal-badge {
    margin-left: auto;
    font-family: var(--mono);
    font-size: 0.72rem;
    color: var(--accent);
    font-weight: 500;
    white-space: nowrap;
  }

  .food-checkbox {
    display: none; /* hidden, controlled via JS */
  }

  /* ── Floating selected panel ── */
  .selected-panel {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--accent);
    color: #fff;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    z-index: 100;
    transform: translateY(100%);
    transition: transform 0.25s ease;
  }

  .selected-panel.visible { transform: translateY(0); }

  .panel-left .label {
    font-family: var(--mono);
    font-size: 0.72rem;
    opacity: 0.7;
  }

  .panel-left .value {
    font-family: var(--mono);
    font-size: 1.5rem;
    font-weight: 500;
    letter-spacing: -0.03em;
    line-height: 1.1;
  }

  .panel-left .items-list {
    font-size: 0.7rem;
    opacity: 0.7;
    margin-top: 2px;
    font-family: var(--mono);
    max-width: 500px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .panel-right { display: flex; gap: 8px; flex-shrink: 0; }

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

  .clear-btn {
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
  .clear-btn:hover { background: rgba(255,255,255,0.15); }

  /* ── Nutrition mini-summary bar ── */
  .nutrition-bar {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px 18px;
  }

  .nutr-stat .nutr-val {
    font-family: var(--mono);
    font-size: 1rem;
    font-weight: 500;
    letter-spacing: -0.02em;
  }

  .nutr-stat .nutr-lbl {
    font-family: var(--mono);
    font-size: 0.6rem;
    color: var(--muted);
    text-transform: lowercase;
  }

  @media (max-width: 600px) {
    .food-grid { grid-template-columns: 1fr; }
    .selected-panel { padding: 10px 14px; }
  }
</style>
</head>
<body>

<header>
  <h1>recipes &amp; food log</h1>
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

  <!-- Live nutrition bar (updates as foods are selected) -->
  <div class="nutrition-bar" id="nutrition-bar" style="display:none;">
    <div class="nutr-stat">
      <div class="nutr-val" id="nb-cal">0</div>
      <div class="nutr-lbl">kcal</div>
    </div>
    <div class="nutr-stat">
      <div class="nutr-val" id="nb-prot">0g</div>
      <div class="nutr-lbl">protein</div>
    </div>
    <div class="nutr-stat">
      <div class="nutr-val" id="nb-carb">0g</div>
      <div class="nutr-lbl">carbs</div>
    </div>
    <div class="nutr-stat">
      <div class="nutr-val" id="nb-fat">0g</div>
      <div class="nutr-lbl">fat</div>
    </div>
    <div class="nutr-stat">
      <div class="nutr-val" id="nb-fiber">0g</div>
      <div class="nutr-lbl">fiber</div>
    </div>
  </div>

  <!-- Search -->
  <div class="search-wrap">
    <span class="search-icon">🔍</span>
    <input type="text" id="food-search" placeholder="search foods… e.g. idli, dal, chai" autocomplete="off">
  </div>
  <div class="search-count" id="search-count"></div>

  <form method="POST" action="" id="meal-form">

    <?php
    // Render categories in display order, then any leftover
    $rendered = [];
    foreach ($display_order as $cat) {
        if (!isset($foods_by_category[$cat])) continue;
        $rendered[] = $cat;
        $meta = $category_meta[$cat];
        $items = $foods_by_category[$cat];
        ?>
    <div class="category-section" data-category="<?= htmlspecialchars($cat) ?>">
      <div class="category-header" onclick="toggleCategory(this)">
        <span class="category-icon"><?= $meta["icon"] ?></span>
        <span class="category-title"><?= htmlspecialchars($meta["label"]) ?></span>
        <span class="category-count"><?= count($items) ?></span>
        <span class="category-selected-count" id="sel-count-<?= $cat ?>">0 selected</span>
        <span class="category-toggle-btn">▾</span>
      </div>
      <div class="food-grid category-grid">
        <?php foreach ($items as $idx => $food):
              $field_id = "food_" . $food["id"];
        ?>
        <div class="food-card"
             id="card-<?= $food["id"] ?>"
             data-name="<?= strtolower(htmlspecialchars($food["name"])) ?>"
             data-region="<?= strtolower(htmlspecialchars($food["region"])) ?>"
             data-category="<?= strtolower(htmlspecialchars($food["category"])) ?>"
             data-id="<?= $food["id"] ?>">

          <div class="food-top">
            <div class="food-name"><?= htmlspecialchars($food["name"]) ?></div>
            <?php if ($food["region"] !== "pan-india"): ?>
            <span class="food-region"><?= htmlspecialchars($food["region"]) ?></span>
            <?php endif; ?>
          </div>

          <div class="food-macros">
            <div class="macro">cal <span><?= $food["calories"] ?></span>/100<?= $food["unit_label"] ?></div>
            <div class="macro">P <span><?= $food["protein_g"] ?>g</span></div>
            <div class="macro">C <span><?= $food["carbs_g"] ?>g</span></div>
            <div class="macro">F <span><?= $food["fat_g"] ?>g</span></div>
            <?php if ($food["fiber_g"] > 0): ?>
            <div class="macro">fi <span><?= $food["fiber_g"] ?>g</span></div>
            <?php endif; ?>
          </div>

          <div class="food-controls">
            <div class="qty-wrap">
              <button type="button" class="qty-btn" onclick="changeQty(<?= $food["id"] ?>, -1)">−</button>
              <input  type="number"
                      class="qty-input"
                      id="qty-<?= $food["id"] ?>"
                      value="<?= $food["typical_serving_g"] ?>"
                      min="1" max="9999" step="1"
                      oninput="recalcCard(<?= $food["id"] ?>)">
              <span class="qty-unit"><?= htmlspecialchars($food["unit_label"]) ?></span>
              <button type="button" class="qty-btn" onclick="changeQty(<?= $food["id"] ?>, 1)">+</button>
            </div>
            <span class="food-kcal-badge" id="kcal-<?= $food["id"] ?>">
              <?= round($food["calories"] * $food["typical_serving_g"] / 100) ?> kcal
            </span>
          </div>

          <!-- Hidden checkbox that drives form submission -->
          <input type="checkbox"
                 class="food-checkbox"
                 id="<?= $field_id ?>"
                 name="meals[<?= $food["id"] ?>][calories]"
                 value="<?= round($food["calories"] * $food["typical_serving_g"] / 100) ?>"
                 data-id="<?= $food["id"] ?>">
          <input type="hidden"
                 name="meals[<?= $food["id"] ?>][name]"
                 value="<?= htmlspecialchars($food["name"]) ?>">

        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php } // end foreach display_order

    // Render any categories not in display_order
    foreach ($foods_by_category as $cat => $items) {
        if (in_array($cat, $rendered)) continue;
        ?>
    <div class="category-section" data-category="<?= htmlspecialchars($cat) ?>">
      <div class="category-header" onclick="toggleCategory(this)">
        <span class="category-icon">🍽️</span>
        <span class="category-title"><?= htmlspecialchars(ucfirst($cat)) ?></span>
        <span class="category-count"><?= count($items) ?></span>
        <span class="category-selected-count" id="sel-count-<?= $cat ?>">0 selected</span>
        <span class="category-toggle-btn">▾</span>
      </div>
      <div class="food-grid category-grid">
        <?php foreach ($items as $food):
              $field_id = "food_" . $food["id"];
        ?>
        <div class="food-card"
             id="card-<?= $food["id"] ?>"
             data-name="<?= strtolower(htmlspecialchars($food["name"])) ?>"
             data-region="<?= strtolower(htmlspecialchars($food["region"])) ?>"
             data-category="<?= strtolower(htmlspecialchars($food["category"])) ?>"
             data-id="<?= $food["id"] ?>">

          <div class="food-top">
            <div class="food-name"><?= htmlspecialchars($food["name"]) ?></div>
            <?php if ($food["region"] !== "pan-india"): ?>
            <span class="food-region"><?= htmlspecialchars($food["region"]) ?></span>
            <?php endif; ?>
          </div>

          <div class="food-macros">
            <div class="macro">cal <span><?= $food["calories"] ?></span>/100<?= $food["unit_label"] ?></div>
            <div class="macro">P <span><?= $food["protein_g"] ?>g</span></div>
            <div class="macro">C <span><?= $food["carbs_g"] ?>g</span></div>
            <div class="macro">F <span><?= $food["fat_g"] ?>g</span></div>
            <?php if ($food["fiber_g"] > 0): ?>
            <div class="macro">fi <span><?= $food["fiber_g"] ?>g</span></div>
            <?php endif; ?>
          </div>

          <div class="food-controls">
            <div class="qty-wrap">
              <button type="button" class="qty-btn" onclick="changeQty(<?= $food["id"] ?>, -1)">−</button>
              <input  type="number"
                      class="qty-input"
                      id="qty-<?= $food["id"] ?>"
                      value="<?= $food["typical_serving_g"] ?>"
                      min="1" max="9999" step="1"
                      oninput="recalcCard(<?= $food["id"] ?>)">
              <span class="qty-unit"><?= htmlspecialchars($food["unit_label"]) ?></span>
              <button type="button" class="qty-btn" onclick="changeQty(<?= $food["id"] ?>, 1)">+</button>
            </div>
            <span class="food-kcal-badge" id="kcal-<?= $food["id"] ?>">
              <?= round($food["calories"] * $food["typical_serving_g"] / 100) ?> kcal
            </span>
          </div>

          <input type="checkbox"
                 class="food-checkbox"
                 id="<?= $field_id ?>"
                 name="meals[<?= $food["id"] ?>][calories]"
                 value="<?= round($food["calories"] * $food["typical_serving_g"] / 100) ?>"
                 data-id="<?= $food["id"] ?>">
          <input type="hidden"
                 name="meals[<?= $food["id"] ?>][name]"
                 value="<?= htmlspecialchars($food["name"]) ?>">

        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php } ?>

  </form><!-- #meal-form -->

</div><!-- .container -->

<!-- Floating save panel -->
<div class="selected-panel" id="selected-panel">
  <div class="panel-left">
    <div class="label">selected foods</div>
    <div class="value" id="panel-total">0 kcal</div>
    <div class="items-list" id="panel-items">none</div>
  </div>
  <div class="panel-right">
    <button type="button" class="save-btn"  onclick="submitForm()">save to log ↑</button>
    <button type="button" class="clear-btn" onclick="clearAll()">clear</button>
  </div>
</div>

<!-- Food data for JS -->
<script>
const FOODS = <?= json_encode($foods_js, JSON_UNESCAPED_UNICODE) ?>;

// Selected state: { foodId: { name, calories, protein, carbs, fat, fiber } }
const selected = {};

/* ── Toggle card selection on click ── */
document.querySelectorAll('.food-card').forEach(card => {
  card.addEventListener('click', e => {
    // Don't fire if clicking qty inputs/buttons
    if (e.target.closest('.qty-wrap') || e.target.tagName === 'INPUT') return;
    toggleCard(parseInt(card.dataset.id));
  });
});

function toggleCard(id) {
  const card     = document.getElementById('card-' + id);
  const checkbox = card.querySelector('.food-checkbox');
  const isNowSelected = !card.classList.contains('selected');

  card.classList.toggle('selected', isNowSelected);
  checkbox.checked = isNowSelected;

  if (isNowSelected) {
    updateSelected(id);
  } else {
    delete selected[id];
  }

  refreshPanel();
  refreshCategoryCount(card.dataset.category);
}

function updateSelected(id) {
  const food    = FOODS[id];
  if (!food) return;
  const qty     = parseFloat(document.getElementById('qty-' + id).value) || 0;
  const factor  = qty / 100;

  // Fetch macro data embedded in card HTML
  const card    = document.getElementById('card-' + id);
  const macros  = card.querySelectorAll('.macro span');

  selected[id] = {
    name:    food.name,
    cal:     food.cal_per_100 * factor,
    protein: parseFloat(macros[0]?.closest('.macro')?.textContent.match(/P\s*([\d.]+)/)?.[1] || 0) * factor,
    carbs:   parseFloat(macros[0]?.closest('.macro')?.textContent.match(/C\s*([\d.]+)/)?.[1] || 0) * factor,
    fat:     parseFloat(macros[0]?.closest('.macro')?.textContent.match(/F\s*([\d.]+)/)?.[1] || 0) * factor,
  };

  // Update checkbox value
  const cb = card.querySelector('.food-checkbox');
  cb.value = Math.round(selected[id].cal);
}

/* ── Recalculate kcal badge when qty changes ── */
function recalcCard(id) {
  const food   = FOODS[id];
  if (!food) return;
  const qty    = parseFloat(document.getElementById('qty-' + id).value) || 0;
  const kcal   = Math.round(food.cal_per_100 * qty / 100);

  document.getElementById('kcal-' + id).textContent = kcal + ' kcal';

  // If already selected, update totals
  if (selected[id]) {
    updateSelected(id);
    refreshPanel();
  }
}

function changeQty(id, delta) {
  const input = document.getElementById('qty-' + id);
  let val = parseFloat(input.value) || 0;
  val = Math.max(1, val + delta * 10);
  input.value = val;
  recalcCard(id);
}

/* ── Refresh floating panel ── */
function refreshPanel() {
  const ids = Object.keys(selected);
  const panel = document.getElementById('selected-panel');

  if (ids.length === 0) {
    panel.classList.remove('visible');
    document.getElementById('nutrition-bar').style.display = 'none';
    return;
  }

  panel.classList.add('visible');
  document.getElementById('nutrition-bar').style.display = 'flex';

  let totalCal = 0, totalProt = 0, totalCarb = 0, totalFat = 0, totalFib = 0;
  const names = [];

  ids.forEach(id => {
    const s = selected[id];
    totalCal  += s.cal;
    names.push(s.name);
  });

  document.getElementById('panel-total').textContent = Math.round(totalCal) + ' kcal';
  document.getElementById('panel-items').textContent  = names.join(' · ');

  // Nutrition bar — read fresh macros from DB data
  ids.forEach(id => {
    const food   = FOODS[id];
    const qty    = parseFloat(document.getElementById('qty-' + id).value) || 0;
    const factor = qty / 100;

    // Macro data is in the card DOM
    const card = document.getElementById('card-' + id);
    const macroEls = card.querySelectorAll('.macro');
    macroEls.forEach(m => {
      const txt = m.textContent;
      const val = parseFloat(m.querySelector('span')?.textContent) || 0;
      if (txt.startsWith('P '))  totalProt += val * factor;
      if (txt.startsWith('C '))  totalCarb += val * factor;
      if (txt.startsWith('F '))  totalFat  += val * factor;
      if (txt.startsWith('fi ')) totalFib  += val * factor;
    });
  });

  document.getElementById('nb-cal').textContent   = Math.round(totalCal);
  document.getElementById('nb-prot').textContent  = Math.round(totalProt) + 'g';
  document.getElementById('nb-carb').textContent  = Math.round(totalCarb) + 'g';
  document.getElementById('nb-fat').textContent   = Math.round(totalFat)  + 'g';
  document.getElementById('nb-fiber').textContent = Math.round(totalFib)  + 'g';
}

function refreshCategoryCount(cat) {
  const catEl = document.getElementById('sel-count-' + cat);
  if (!catEl) return;
  const n = document.querySelectorAll(`.food-card[data-category="${cat}"].selected`).length;
  catEl.style.display = n > 0 ? 'inline' : 'none';
  catEl.textContent   = n + ' selected';
}

/* ── Category collapse/expand ── */
function toggleCategory(header) {
  const grid   = header.nextElementSibling;
  const toggle = header.querySelector('.category-toggle-btn');
  const isOpen = grid.style.display !== 'none';
  grid.style.display = isOpen ? 'none' : 'grid';
  toggle.classList.toggle('collapsed', isOpen);
}

/* ── Search ── */
document.getElementById('food-search').addEventListener('input', function () {
  const q     = this.value.toLowerCase().trim();
  let visible = 0;

  document.querySelectorAll('.food-card').forEach(card => {
    const match = !q
      || card.dataset.name.includes(q)
      || card.dataset.region.includes(q)
      || card.dataset.category.includes(q);

    card.classList.toggle('hidden', !match);
    if (match) visible++;
  });

  // Show all category grids when searching
  document.querySelectorAll('.category-grid').forEach(g => {
    g.style.display = 'grid';
  });
  document.querySelectorAll('.category-toggle-btn').forEach(t => {
    t.classList.remove('collapsed');
  });

  document.getElementById('search-count').textContent =
    q ? `${visible} result${visible !== 1 ? 's' : ''} for "${q}"` : '';
});

/* ── Submit form ── */
function submitForm() {
  // Ensure all selected checkboxes have updated values
  Object.keys(selected).forEach(id => {
    const card = document.getElementById('card-' + id);
    const cb   = card.querySelector('.food-checkbox');
    cb.checked = true;
    cb.value   = Math.round(selected[id].cal);
  });
  document.getElementById('meal-form').submit();
}

/* ── Clear all ── */
function clearAll() {
  document.querySelectorAll('.food-card.selected').forEach(card => {
    card.classList.remove('selected');
    card.querySelector('.food-checkbox').checked = false;
  });
  Object.keys(selected).forEach(k => delete selected[k]);
  refreshPanel();
  document.querySelectorAll('.category-selected-count').forEach(el => {
    el.style.display = 'none';
  });
}
</script>

</body>
</html>