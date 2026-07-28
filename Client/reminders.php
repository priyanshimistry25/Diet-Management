<?php
session_start();
include("../connection.php");

if(!isset($_SESSION["user_id"])){
    header("Location: ../login.php");
    exit();
}
include("header.php");

$user_id = $_SESSION["user_id"];

// Fetch client data
$stmt = $conn->prepare("SELECT * FROM client WHERE user_id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$goal = $data["goal"];
$tdee = $data["tdee"];

if($goal == "weight_loss"){
    $targetCalories = $tdee - 500;
} elseif($goal == "weight_gain"){
    $targetCalories = $tdee + 500;
} else {
    $targetCalories = $tdee;
}

$today = date("Y-m-d");

// Total eaten
$mealQuery = $conn->prepare("SELECT calories as total FROM meals WHERE user_id=? AND date=?");
$mealQuery->bind_param("ss", $user_id, $today);
$mealQuery->execute();
$mealData = $mealQuery->get_result()->fetch_assoc();
$caloriesEaten = $mealData['total'] ?? 0;

// Total burned
$exerciseQuery = $conn->prepare("SELECT calories_burned as total FROM exercise WHERE user_id=? AND date=?");
$exerciseQuery->bind_param("ss", $user_id, $today);
$exerciseQuery->execute();
$exerciseData = $exerciseQuery->get_result()->fetch_assoc();
$caloriesBurned = $exerciseData['total'] ?? 0;

$netCalories = $caloriesEaten - $caloriesBurned;
$calorieAlert = $netCalories > $targetCalories;
$calorieDiff = abs($netCalories - $targetCalories);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminders — NutriTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:          #f0faf5;
            --surface:     #ffffff;
            --surface2:    #e8f7ef;
            --border:      rgba(34,139,80,0.15);
            --accent:      #1e9e5e;
            --accent2:     #157a47;
            --accent-soft: rgba(30,158,94,0.10);
            --warn:        #d97706;
            --warn-soft:   rgba(217,119,6,0.10);
            --danger:      #dc2626;
            --danger-soft: rgba(220,38,38,0.10);
            --text:        #0f2d1e;
            --text-muted:  #4d7a62;
            --card-shadow: 0 4px 24px rgba(0,80,40,0.09);
            --radius:      18px;
        }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            padding: 0 0 60px;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient background blobs */
        body::before {
            content: '';
            position: fixed;
            top: -120px; left: -120px;
            width: 480px; height: 480px;
            background: radial-gradient(circle, rgba(78,203,141,0.10) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -100px; right: -80px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(245,158,66,0.08) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
        }

        /* ── Page header ── */
        .page-header {
            position: relative; z-index: 1;
            padding: 44px 28px 0;
            display: flex;
            align-items: flex-start;
            gap: 18px;
        }
        .header-icon {
            width: 52px; height: 52px;
            background: var(--accent-soft);
            border: 1.5px solid var(--accent);
            border-radius: 14px;
            display: grid; place-items: center;
            font-size: 22px; color: var(--accent);
            flex-shrink: 0;
            box-shadow: 0 0 18px rgba(78,203,141,0.18);
        }
        .header-text h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.6rem, 5vw, 2.1rem);
            font-weight: 700;
            line-height: 1.15;
            letter-spacing: -0.3px;
        }
        .header-text p {
            color: var(--text-muted);
            font-size: 0.88rem;
            margin-top: 4px;
        }

        /* ── Calorie alert banner ── */
        .calorie-banner {
            position: relative; z-index: 1;
            margin: 28px 20px 0;
            border-radius: var(--radius);
            padding: 18px 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            animation: slideDown 0.55s cubic-bezier(.22,1,.36,1) both;
        }
        .calorie-banner.over  { background: var(--danger-soft);  border: 1.5px solid var(--danger);  }
        .calorie-banner.under { background: var(--accent-soft);  border: 1.5px solid var(--accent);  }
        .calorie-banner.exact { background: var(--accent-soft);  border: 1.5px solid var(--accent);  }

        .banner-icon {
            font-size: 26px;
            flex-shrink: 0;
        }
        .calorie-banner.over  .banner-icon { color: var(--danger); }
        .calorie-banner.under .banner-icon { color: var(--accent); }
        .calorie-banner.exact .banner-icon { color: var(--accent); }

        .banner-body strong {
            display: block;
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem;
            margin-bottom: 3px;
        }
        .calorie-banner.over  .banner-body strong { color: var(--danger); }
        .calorie-banner.under .banner-body strong { color: var(--accent); }
        .banner-body span { font-size: 0.83rem; color: var(--text-muted); }

        .calorie-stats {
            margin-left: auto;
            text-align: right;
            flex-shrink: 0;
        }
        .calorie-stats .val {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .calorie-banner.over  .calorie-stats .val { color: var(--danger); }
        .calorie-banner.under .calorie-stats .val { color: var(--accent); }
        .calorie-stats .lbl  { font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; }

        /* ── Section label ── */
        .section-label {
            position: relative; z-index: 1;
            margin: 30px 24px 12px;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            display: flex; align-items: center; gap: 8px;
        }
        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── Meal reminder cards ── */
        .cards {
            position: relative; z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 0 20px;
        }

        .meal-card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 22px;
            display: flex;
            align-items: center;
            gap: 18px;
            box-shadow: var(--card-shadow);
            cursor: pointer;
            transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
            animation: fadeUp 0.5s cubic-bezier(.22,1,.36,1) both;
        }
        .meal-card:nth-child(1) { animation-delay: 0.08s; }
        .meal-card:nth-child(2) { animation-delay: 0.16s; }
        .meal-card:nth-child(3) { animation-delay: 0.24s; }

        .meal-card:hover {
            transform: translateY(-3px);
            border-color: var(--accent);
            background: var(--surface2);
        }
        .meal-card.active {
            border-color: var(--accent);
            background: var(--surface2);
        }

        .meal-icon-wrap {
            width: 54px; height: 54px;
            border-radius: 14px;
            display: grid; place-items: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .breakfast .meal-icon-wrap { background: rgba(245,195,66,0.13); color: #f5c342; }
        .lunch     .meal-icon-wrap { background: rgba(78,203,141,0.13);  color: var(--accent); }
        .dinner    .meal-icon-wrap { background: rgba(138,100,220,0.14); color: #9b72e8; }

        .meal-info { flex: 1; min-width: 0; }
        .meal-info h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 3px;
        }
        .meal-info .time-tag {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 0.78rem; color: var(--text-muted);
        }
        .meal-info .time-tag i { font-size: 0.72rem; }

        .meal-tip {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 6px;
            line-height: 1.45;
        }

        /* toggle switch */
        .toggle-wrap { display: flex; flex-direction: column; align-items: center; gap: 5px; flex-shrink: 0; }
        .toggle {
            position: relative;
            width: 46px; height: 26px;
        }
        .toggle input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute; inset: 0;
            background: rgba(255,255,255,0.08);
            border: 1px solid var(--border);
            border-radius: 26px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .slider::before {
            content: '';
            position: absolute;
            width: 18px; height: 18px;
            left: 3px; bottom: 3px;
            background: var(--text-muted);
            border-radius: 50%;
            transition: transform 0.3s, background 0.3s;
        }
        .toggle input:checked + .slider { background: var(--accent2); border-color: var(--accent); }
        .toggle input:checked + .slider::before { transform: translateX(20px); background: #fff; }
        .toggle-lbl { font-size: 0.66rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.6px; }

        /* live clock */
        #live-clock {
            font-family: 'Playfair Display', serif;
            font-size: 0.78rem;
            color: var(--accent);
            margin-top: 5px;
        }

        /* ── Calorie summary row ── */
        .summary-row {
            position: relative; z-index: 1;
            display: flex;
            gap: 12px;
            padding: 0 20px;
            margin-top: 14px;
        }
        .summary-pill {
            flex: 1;
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 14px;
            padding: 14px 16px;
            text-align: center;
            animation: fadeUp 0.5s 0.35s cubic-bezier(.22,1,.36,1) both;
        }
        .summary-pill .s-val {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--accent);
        }
        .summary-pill .s-lbl {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.7px;
            margin-top: 2px;
        }

        /* ── Progress bar ── */
        .progress-section {
            position: relative; z-index: 1;
            margin: 18px 20px 0;
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 22px;
            animation: fadeUp 0.5s 0.42s cubic-bezier(.22,1,.36,1) both;
        }
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .progress-header span {
            font-size: 0.78rem;
            color: var(--text-muted);
        }
        .progress-header strong {
            font-family: 'Playfair Display', serif;
            font-size: 0.9rem;
        }
        .progress-bar-bg {
            height: 10px;
            background: rgba(255,255,255,0.06);
            border-radius: 999px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 999px;
            transition: width 1s cubic-bezier(.22,1,.36,1);
        }
        .fill-ok      { background: linear-gradient(90deg, var(--accent2), var(--accent)); }
        .fill-warning { background: linear-gradient(90deg, var(--warn), #f7c06e); }
        .fill-danger  { background: linear-gradient(90deg, var(--danger), #f47c7c); }
        .progress-note {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 8px;
        }

        /* ── Notification toast ── */
        .toast {
            position: fixed;
            bottom: 24px; left: 50%;
            transform: translateX(-50%) translateY(80px);
            background: var(--surface2);
            border: 1.5px solid var(--accent);
            color: var(--text);
            padding: 12px 22px;
            border-radius: 999px;
            font-size: 0.85rem;
            display: flex; align-items: center; gap: 8px;
            z-index: 999;
            opacity: 0;
            transition: transform 0.4s cubic-bezier(.22,1,.36,1), opacity 0.4s;
            box-shadow: 0 4px 24px rgba(78,203,141,0.2);
        }
        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        .toast i { color: var(--accent); }

        /* ── Animations ── */
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Back button ── */
        .back-btn {
            position: relative; z-index: 1;
            display: inline-flex; align-items: center; gap: 8px;
            margin: 20px 24px 0;
            color: var(--text-muted);
            font-size: 0.83rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-btn:hover { color: var(--accent); }
    </style>
</head>
<body>

<!-- Back -->
<a href="dashboard.php" class="back-btn"><i class="fas fa-chevron-left"></i> Dashboard</a>

<!-- Page Header -->
<div class="page-header">
    <div class="header-icon"><i class="fas fa-bell"></i></div>
    <div class="header-text">
        <h1>Reminders</h1>
        <p id="live-clock"></p>
    </div>
</div>

<!-- ── Calorie Alert Banner ── -->
<?php
$pct = $targetCalories > 0 ? round(($netCalories / $targetCalories) * 100) : 0;
$pct = max(0, $pct);
$barClass = $pct <= 85 ? 'fill-ok' : ($pct <= 100 ? 'fill-warning' : 'fill-danger');
$barWidth = min($pct, 100);
?>
<div class="calorie-banner <?= $calorieAlert ? 'over' : ($netCalories == $targetCalories ? 'exact' : 'under') ?>">
    <i class="fas <?= $calorieAlert ? 'fa-triangle-exclamation' : 'fa-circle-check' ?> banner-icon"></i>
    <div class="banner-body">
        <?php if($calorieAlert): ?>
            <strong>Calorie Target Exceeded!</strong>
            <span>You've gone <?= number_format($calorieDiff) ?> kcal over your daily goal.</span>
        <?php elseif($netCalories == $targetCalories): ?>
            <strong>Perfect Balance!</strong>
            <span>You've hit your calorie target exactly today.</span>
        <?php else: ?>
            <strong>On Track Today</strong>
            <span><?= number_format($calorieDiff) ?> kcal remaining to reach your goal.</span>
        <?php endif; ?>
    </div>
    <div class="calorie-stats">
        <div class="val"><?= number_format($netCalories) ?></div>
        <div class="lbl">net kcal</div>
    </div>
</div>

<!-- ── Summary Pills ── -->
<div class="summary-row">
    <div class="summary-pill">
        <div class="s-val"><?= number_format($targetCalories) ?></div>
        <div class="s-lbl">Target</div>
    </div>
    <div class="summary-pill">
        <div class="s-val"><?= number_format($caloriesEaten) ?></div>
        <div class="s-lbl">Eaten</div>
    </div>
    <div class="summary-pill">
        <div class="s-val"><?= number_format($caloriesBurned) ?></div>
        <div class="s-lbl">Burned</div>
    </div>
</div>

<!-- ── Progress Bar ── -->
<div class="progress-section">
    <div class="progress-header">
        <strong>Daily Calorie Progress</strong>
        <span><?= $pct ?>% of target</span>
    </div>
    <div class="progress-bar-bg">
        <div class="progress-bar-fill <?= $barClass ?>" id="calBar" style="width:0%"></div>
    </div>
    <p class="progress-note">
        <?php if($calorieAlert): ?>
            ⚠️ Consider lighter activity or a smaller snack for the rest of the day.
        <?php else: ?>
            ✅ Keep it up — stay mindful of your remaining <?= number_format($calorieDiff) ?> kcal.
        <?php endif; ?>
    </p>
</div>

<!-- ── Meal Reminders ── -->
<div class="section-label"><i class="fas fa-utensils"></i> Meal Reminders</div>

<div class="cards">
    <!-- Breakfast -->
    <div class="meal-card breakfast" onclick="toggleCard(this)">
        <div class="meal-icon-wrap"><i class="fas fa-sun"></i></div>
        <div class="meal-info">
            <h3>Breakfast</h3>
            <div class="time-tag"><i class="fas fa-clock"></i> 7:30 AM – 9:00 AM</div>
            <p class="meal-tip">Start your day with a balanced meal rich in protein &amp; fibre.</p>
        </div>
        <div class="toggle-wrap">
            <label class="toggle" onclick="event.stopPropagation()">
                <input type="checkbox" id="tog-breakfast" onchange="onToggle(this,'Breakfast')">
                <span class="slider"></span>
            </label>
            <span class="toggle-lbl">Alert</span>
        </div>
    </div>

    <!-- Lunch -->
    <div class="meal-card lunch" onclick="toggleCard(this)">
        <div class="meal-icon-wrap"><i class="fas fa-bowl-rice"></i></div>
        <div class="meal-info">
            <h3>Lunch</h3>
            <div class="time-tag"><i class="fas fa-clock"></i> 12:30 PM – 1:30 PM</div>
            <p class="meal-tip">Keep it light to moderate — avoid heavy carbs if your goal is weight loss.</p>
        </div>
        <div class="toggle-wrap">
            <label class="toggle" onclick="event.stopPropagation()">
                <input type="checkbox" id="tog-lunch" onchange="onToggle(this,'Lunch')">
                <span class="slider"></span>
            </label>
            <span class="toggle-lbl">Alert</span>
        </div>
    </div>

    <!-- Dinner -->
    <div class="meal-card dinner" onclick="toggleCard(this)">
        <div class="meal-icon-wrap"><i class="fas fa-moon"></i></div>
        <div class="meal-info">
            <h3>Dinner</h3>
            <div class="time-tag"><i class="fas fa-clock"></i> 7:00 PM – 8:30 PM</div>
            <p class="meal-tip">Opt for lighter portions in the evening to aid digestion &amp; sleep quality.</p>
        </div>
        <div class="toggle-wrap">
            <label class="toggle" onclick="event.stopPropagation()">
                <input type="checkbox" id="tog-dinner" onchange="onToggle(this,'Dinner')">
                <span class="slider"></span>
            </label>
            <span class="toggle-lbl">Alert</span>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"><i class="fas fa-bell"></i> <span id="toast-msg"></span></div>

<script>
// Live clock
function updateClock(){
    const now = new Date();
    document.getElementById('live-clock').textContent =
        now.toLocaleDateString('en-IN',{weekday:'long',day:'numeric',month:'long'}) +
        ' · ' + now.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'});
}
updateClock(); setInterval(updateClock, 1000);

// Animate progress bar on load
window.addEventListener('load', () => {
    setTimeout(() => {
        document.getElementById('calBar').style.width = '<?= $barWidth ?>%';
    }, 300);
});

// Toggle card highlight
function toggleCard(card){
    card.classList.toggle('active');
}

// Toggle switch handler
function onToggle(input, meal){
    const msg = input.checked
        ? `${meal} reminder enabled`
        : `${meal} reminder disabled`;
    showToast(msg);
    // Persist state via localStorage
    localStorage.setItem('remind_' + meal.toLowerCase(), input.checked ? '1' : '0');
}

// Toast helper
function showToast(msg){
    const t = document.getElementById('toast');
    document.getElementById('toast-msg').textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2800);
}

// Restore toggle states from localStorage on load
['breakfast','lunch','dinner'].forEach(m => {
    const saved = localStorage.getItem('remind_' + m);
    if(saved === '1'){
        const inp = document.getElementById('tog-' + m);
        if(inp) inp.checked = true;
    }
});

// Browser Notification API for meal-time alerts
function scheduleMealNotification(label, hour, minute){
    if(!('Notification' in window)) return;
    const now = new Date();
    const target = new Date();
    target.setHours(hour, minute, 0, 0);
    let delay = target - now;
    if(delay < 0) return; // already passed today
    setTimeout(() => {
        if(Notification.permission === 'granted'){
            new Notification('🍽 Time for ' + label + '!', {
                body: 'Don\'t forget to log your meal in NutriTrack.',
                icon: '../favicon.png'
            });
        }
    }, delay);
}

function requestAndSchedule(){
    if(!('Notification' in window)){ showToast('Notifications not supported'); return; }
    Notification.requestPermission().then(p => {
        if(p === 'granted'){
            const meals = [
                {id:'breakfast', label:'Breakfast', h:7,  m:30},
                {id:'lunch',     label:'Lunch',     h:12, m:30},
                {id:'dinner',    label:'Dinner',    h:19, m:0},
            ];
            meals.forEach(ml => {
                const inp = document.getElementById('tog-' + ml.id);
                if(inp && inp.checked) scheduleMealNotification(ml.label, ml.h, ml.m);
            });
            showToast('Notifications scheduled!');
        } else {
            showToast('Permission denied for notifications');
        }
    });
}

// Auto-request when any toggle is turned on
document.querySelectorAll('.toggle input').forEach(inp => {
    inp.addEventListener('change', () => {
        if(inp.checked) requestAndSchedule();
    });
});

// Calorie over-target browser notification (fire once per session)
<?php if($calorieAlert): ?>
if(!sessionStorage.getItem('calorie_alerted')){
    sessionStorage.setItem('calorie_alerted','1');
    if('Notification' in window && Notification.permission === 'granted'){
        new Notification('⚠️ Calorie Target Exceeded', {
            body: 'Your net calories (<?= number_format($netCalories) ?> kcal) exceed your daily target (<?= number_format($targetCalories) ?> kcal).'
        });
    }
}
<?php endif; ?>
</script>
</body>
</html>