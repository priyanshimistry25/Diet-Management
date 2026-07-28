<?php
/**
 * header.php — Shared Navigation Header
 * ──────────────────────────────────────────────────────────────────────────────
 * USAGE: Include at the top of every page AFTER session_start() and DB queries.
 *
 *   <?php
 *   session_start();
 *   include("../connection.php");
 *   // ... your page's PHP logic ...
 *   $current_page = 'dashboard';   // ← set this before including header
 *   include("header.php");
 *   ?>
 *
 * VALID $current_page values:
 *   'dashboard' | 'exercise' | 'device' | 'barcode' | 'meal' | 'recipe' | 'reminders'
 * ──────────────────────────────────────────────────────────────────────────────
 */

// ── Guard: session must already be started by the including page ──────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Resolve active page (fallback: auto-detect from filename) ─────────────────
if (!isset($current_page)) {
    $file = basename($_SERVER['PHP_SELF'], '.php');
    $map  = [
        'dashboard'         => 'dashboard',
        'newExercise'       => 'exercise',
        'linkFitnessDevice' => 'device',
        'Barcode_scan'      => 'barcode',
        'barcode_scan'      => 'barcode',
        'newLogmeal'        => 'meal',
        'recipeSearch'      => 'recipe',
        'reminders'         => 'reminders',
    ];
    $current_page = $map[$file] ?? '';
}

// ── Resolve user display name from session / passed variable ──────────────────
$_header_name   = $user['fullname'] ?? $_SESSION['fullname'] ?? 'User';
$_header_avatar = strtoupper(substr($_header_name, 0, 1));

// ── Nav items definition ──────────────────────────────────────────────────────
$_nav_items = [
    ['key' => 'dashboard',  'href' => 'dashboard.php',          'icon' => '⊞',  'label' => 'Dashboard'],
    ['key' => 'exercise',   'href' => 'newExercise.php',         'icon' => '🏋',  'label' => 'Exercise'],
    ['key' => 'device',     'href' => 'linkFitnessDevice.php',   'icon' => '⚡',  'label' => 'Device'],
    ['key' => 'barcode',    'href' => 'Barcode scan.php',        'icon' => '📷',  'label' => 'Barcode'],
    ['key' => 'meal',       'href' => 'newLogmeal.php',          'icon' => '🍽',  'label' => 'Log Meal'],
    ['key' => 'recipe',     'href' => 'recipes.php',        'icon' => '🔍',  'label' => 'Recipes'],
    ['key' => 'reminders',  'href' => 'reminders.php',           'icon' => '🔔',  'label' => 'Reminders'],
];
?>
<!-- ═══════════════════════════════════════════════════════════════════════════
     SHARED HEADER — NutriTrack Diet Management (Light Theme)
     ═══════════════════════════════════════════════════════════════════════════ -->

<!-- Google Fonts (load only if not already loaded by the page) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet">

<style>
/* ── Light Theme CSS variables ──────────────────────────────────────────────── */
:root {
  --bg:      #f5f7fa;
  --surface: #ffffff;
  --card:    #f0f4f8;
  --border:  #dde3ec;
  --accent:  #2e8b57;      /* forest green — primary action */
  --accent2: #1a6fad;      /* ocean blue — secondary accent */
  --warn:    #d94f2b;
  --text:    #1c2333;
  --muted:   #6b7a99;
  --radius:  12px;
}

/* ── Top header bar ──────────────────────────────────────────────────────────── */
#nt-header {
  position: sticky;
  top: 0;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border);
  font-family: 'Syne', sans-serif;
  box-shadow: 0 1px 4px rgba(30, 50, 90, 0.07);
}

.nt-header-inner {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 24px;
  height: 64px;
  display: flex;
  align-items: center;
  gap: 20px;
}

/* Logo */
.nt-logo {
  font-weight: 800;
  font-size: 1.2rem;
  letter-spacing: -0.03em;
  background: linear-gradient(135deg, var(--accent), var(--accent2));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  white-space: nowrap;
  text-decoration: none;
  flex-shrink: 0;
}

/* Desktop nav links */
.nt-nav {
  display: flex;
  align-items: center;
  gap: 2px;
  margin: 0 auto;
  flex-wrap: nowrap;
  overflow-x: auto;
  scrollbar-width: none;
}
.nt-nav::-webkit-scrollbar { display: none; }

.nt-nav-link {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--muted);
  text-decoration: none;
  white-space: nowrap;
  transition: background 0.2s, color 0.2s;
  border: 1px solid transparent;
}

.nt-nav-link:hover {
  background: var(--card);
  color: var(--text);
  border-color: var(--border);
}

.nt-nav-link.active {
  background: rgba(46, 139, 87, 0.1);
  color: var(--accent);
  border-color: rgba(46, 139, 87, 0.28);
}

.nt-nav-icon { font-size: 1rem; line-height: 1; }

/* User badge */
.nt-user-badge {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 50px;
  padding: 6px 16px 6px 6px;
  flex-shrink: 0;
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s;
  position: relative;
}
.nt-user-badge:hover {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(46, 139, 87, 0.1);
}

.nt-avatar {
  width: 32px; height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), var(--accent2));
  display: flex; align-items: center; justify-content: center;
  font-weight: 800; font-size: 0.85rem; color: #fff;
  flex-shrink: 0;
}

.nt-user-name {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--text);
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Dropdown menu */
.nt-dropdown {
  display: none;
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  min-width: 160px;
  overflow: hidden;
  box-shadow: 0 8px 24px rgba(30, 50, 90, 0.12);
  z-index: 999;
}
.nt-user-badge:hover .nt-dropdown { display: block; }

.nt-dropdown a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 16px;
  font-size: 0.82rem;
  color: var(--muted);
  text-decoration: none;
  transition: background 0.15s, color 0.15s;
  font-family: 'Syne', sans-serif;
}
.nt-dropdown a:hover { background: rgba(46, 139, 87, 0.07); color: var(--accent); }
.nt-dropdown hr { border: none; border-top: 1px solid var(--border); margin: 2px 0; }

/* Hamburger (mobile) */
.nt-hamburger {
  display: none;
  background: none;
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 6px 10px;
  cursor: pointer;
  color: var(--muted);
  font-size: 1.1rem;
  margin-left: auto;
}
.nt-hamburger:hover {
  background: var(--card);
  color: var(--text);
}

/* Mobile drawer */
.nt-mobile-nav {
  display: none;
  flex-direction: column;
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  padding: 8px 16px 16px;
}
.nt-mobile-nav.open { display: flex; }

.nt-mobile-nav .nt-nav-link {
  padding: 11px 16px;
  width: 100%;
  font-size: 0.9rem;
  border-radius: 10px;
}

/* ── Page title breadcrumb strip ─────────────────────────────────────────────── */
.nt-breadcrumb {
  background: var(--card);
  border-bottom: 1px solid var(--border);
  font-family: 'Space Mono', monospace;
  font-size: 0.72rem;
  color: var(--muted);
  letter-spacing: 0.05em;
}
.nt-breadcrumb-inner {
  max-width: 1100px;
  margin: 0 auto;
  padding: 8px 24px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.nt-breadcrumb a { color: var(--accent2); text-decoration: none; }
.nt-breadcrumb a:hover { text-decoration: underline; color: var(--accent); }
.nt-breadcrumb span { color: var(--border); }

/* ── Responsive ──────────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
  .nt-nav         { display: none; }
  .nt-hamburger   { display: block; }
  .nt-user-name   { display: none; }
  .nt-logo        { font-size: 1.05rem; }
}

@media (max-width: 480px) {
  .nt-header-inner { padding: 0 14px; gap: 10px; }
}
</style>

<!-- ── HTML ─────────────────────────────────────────────────────────────────── -->
<div id="nt-header">
  <div class="nt-header-inner">

    <!-- Logo -->
    <a class="nt-logo" href="dashboard.php">Nutri<span style="-webkit-text-fill-color:var(--accent2)">Track</span></a>

    <!-- Desktop nav -->
    <nav class="nt-nav" aria-label="Main Navigation">
      <?php foreach ($_nav_items as $_item): ?>
      <a href="<?= htmlspecialchars($_item['href']) ?>"
         class="nt-nav-link<?= ($current_page === $_item['key']) ? ' active' : '' ?>"
         <?= ($current_page === $_item['key']) ? 'aria-current="page"' : '' ?>>
        <span class="nt-nav-icon"><?= $_item['icon'] ?></span>
        <?= htmlspecialchars($_item['label']) ?>
      </a>
      <?php endforeach; ?>
    </nav>

    <!-- User badge + dropdown -->
    <div class="nt-user-badge" role="button" aria-haspopup="true" aria-label="User menu">
      <div class="nt-avatar"><?= htmlspecialchars($_header_avatar) ?></div>
      <span class="nt-user-name"><?= htmlspecialchars($_header_name) ?></span>

      <div class="nt-dropdown" role="menu">
        <a href="profile.php"   role="menuitem">👤&nbsp; My Profile</a>
        <a href="../goal_selection.php"     role="menuitem">🎯&nbsp; My Goals</a>
        <!-- <a href="settings.php"  role="menuitem">⚙️&nbsp; Settings</a> -->
        <hr>
        <a href="logout.php" role="menuitem" style="color:var(--warn)">⏏&nbsp; Logout</a>
      </div>
    </div>

    <!-- Hamburger (mobile) -->
    <button class="nt-hamburger" id="ntHamburger" aria-label="Toggle navigation" aria-expanded="false">☰</button>

  </div><!-- /inner -->

  <!-- Mobile drawer nav -->
  <nav class="nt-mobile-nav" id="ntMobileNav" aria-label="Mobile Navigation">
    <?php foreach ($_nav_items as $_item): ?>
    <a href="<?= htmlspecialchars($_item['href']) ?>"
       class="nt-nav-link<?= ($current_page === $_item['key']) ? ' active' : '' ?>">
      <span class="nt-nav-icon"><?= $_item['icon'] ?></span>
      <?= htmlspecialchars($_item['label']) ?>
    </a>
    <?php endforeach; ?>
    <hr style="border-color:var(--border);margin:8px 0">
    <a href="profile.php"  class="nt-nav-link">👤 My Profile</a>
    <!-- <a href="settings.php" class="nt-nav-link">⚙️ Settings</a> -->
    <a href="../logout.php" class="nt-nav-link" style="color:var(--warn)">⏏ Logout</a>
  </nav>

</div><!-- /#nt-header -->

<!-- ── Breadcrumb strip ──────────────────────────────────────────────────────── -->
<?php
$_page_labels = [
    'dashboard' => 'Dashboard',
    'exercise'  => 'Exercise Log',
    'device'    => 'Link Fitness Device',
    'barcode'   => 'Barcode Scanner',
    'meal'      => 'Log Meal',
    'recipe'    => 'Recipe Search',
    'reminders' => 'Reminders',
];
$_page_label = $_page_labels[$current_page] ?? ucfirst($current_page);
?>
<div class="nt-breadcrumb">
  <div class="nt-breadcrumb-inner">
    <a href="dashboard.php">Home</a>
    <span>›</span>
    <span><?= htmlspecialchars($_page_label) ?></span>
  </div>
</div>

<!-- ── Hamburger JS ──────────────────────────────────────────────────────────── -->
<script>
(function () {
  const btn = document.getElementById('ntHamburger');
  const nav = document.getElementById('ntMobileNav');
  if (!btn || !nav) return;
  btn.addEventListener('click', function () {
    const open = nav.classList.toggle('open');
    btn.setAttribute('aria-expanded', open);
    btn.textContent = open ? '✕' : '☰';
  });
  // Close drawer when a link inside it is clicked
  nav.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', function () {
      nav.classList.remove('open');
      btn.setAttribute('aria-expanded', false);
      btn.textContent = '☰';
    });
  });
})();
</script>