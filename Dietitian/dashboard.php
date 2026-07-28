<?php session_start();
if(!isset($_SESSION["user_id"])){
    header("location:../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">log
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dietitian Dashboard – NutriTrack</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<style>
/* ── Reset & Tokens ─────────────────────────────── */
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --g50:#f0faf4;--g100:#d1f0dc;--g200:#a3e0b9;--g500:#2aab62;--g600:#1d8a4e;--g700:#14673a;
  --t50:#e8f8f4;--t100:#9fe1cb;--t500:#1d9e75;
  --a50:#fef9ec;--a100:#fce8b2;--a500:#d4972a;--a600:#b8601a;
  --r50:#fff0ec;--r100:#ffd0c2;--r500:#d86040;--r600:#b84020;
  --b50:#e8f2fc;--b100:#b8d4f5;--b500:#2d7dd2;
  --n50:#f9f9f7;--n100:#f0eeea;--n200:#e2dfd8;--n300:#cac7bf;--n400:#a8a59e;--n600:#6b6864;--n800:#3a3835;--n900:#1e1d1b;
  --white:#fff;
  --fd:'DM Serif Display',Georgia,serif;
  --fb:'DM Sans',system-ui,sans-serif;
  --r-sm:8px;--r-md:12px;--r-lg:18px;--r-xl:28px;
  --sh:0 2px 16px rgba(0,0,0,.07),0 0 0 1px rgba(0,0,0,.04);
  --sidebar-w:260px;
}
html{scroll-behavior:smooth}
body{font-family:var(--fb);background:var(--n50);color:var(--n900);line-height:1.6;-webkit-font-smoothing:antialiased;min-height:100vh}
a{text-decoration:none;color:inherit}
button{cursor:pointer;font-family:var(--fb)}
input,select,textarea{font-family:var(--fb)}

/* ── Sidebar ────────────────────────────────────── */
.sidebar{
  position:fixed;top:0;left:0;width:var(--sidebar-w);height:100vh;
  background:var(--n900);display:flex;flex-direction:column;z-index:200;
  overflow-y:auto;
}
.sidebar-brand{
  display:flex;align-items:center;gap:10px;padding:24px 20px 20px;
  border-bottom:1px solid rgba(255,255,255,.08);
}
.brand-icon{font-size:22px}
.brand-name{font-family:var(--fd);font-size:20px;color:var(--white);letter-spacing:-.02em}

.dietitian-profile{
  padding:20px;border-bottom:1px solid rgba(255,255,255,.08);
  display:flex;flex-direction:column;align-items:center;gap:10px;text-align:center;
}
.dp-avatar{
  width:64px;height:64px;border-radius:50%;background:var(--g600);
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fd);font-size:22px;color:var(--white);border:3px solid rgba(255,255,255,.15);
}
.dp-name{font-size:15px;font-weight:600;color:var(--white)}
.dp-role{font-size:12px;color:rgba(255,255,255,.45);letter-spacing:.04em;text-transform:uppercase}
.dp-badge{
  background:var(--g600);color:var(--white);font-size:11px;font-weight:500;
  padding:3px 10px;border-radius:100px;
}

.nav-section-label{
  font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;
  color:rgba(255,255,255,.3);padding:18px 20px 6px;
}
.nav-item{
  display:flex;align-items:center;gap:12px;padding:11px 20px;
  font-size:14px;font-weight:500;color:rgba(255,255,255,.6);
  border-left:3px solid transparent;transition:all .15s;cursor:pointer;
  border:none;background:none;width:100%;text-align:left;
}
.nav-item:hover{background:rgba(255,255,255,.06);color:var(--white)}
.nav-item.active{
  background:rgba(45,170,98,.15);color:var(--g200);
  border-left:3px solid var(--g500);
}
.nav-item .nav-icon{font-size:16px;width:20px;text-align:center;flex-shrink:0}
.nav-badge{
  margin-left:auto;background:var(--r500);color:var(--white);
  font-size:10px;font-weight:600;padding:2px 7px;border-radius:100px;
}

.sidebar-footer{margin-top:auto;padding:16px 20px;border-top:1px solid rgba(255,255,255,.08)}
.btn-logout{
  display:flex;align-items:center;gap:10px;width:100%;padding:10px 12px;
  background:rgba(216,96,64,.12);border:1px solid rgba(216,96,64,.25);
  border-radius:var(--r-sm);color:#f09070;font-size:13px;font-weight:500;
  transition:background .15s;
}
.btn-logout:hover{background:rgba(216,96,64,.2)}

/* ── Main Content ───────────────────────────────── */
.main{margin-left:var(--sidebar-w);min-height:100vh;display:flex;flex-direction:column}

.topbar{
  position:sticky;top:0;z-index:100;background:rgba(249,249,247,.94);
  backdrop-filter:blur(12px);border-bottom:1px solid var(--n200);
  display:flex;align-items:center;justify-content:space-between;
  padding:0 32px;height:64px;
}
.topbar-title{font-family:var(--fd);font-size:22px;color:var(--n900)}
.topbar-actions{display:flex;align-items:center;gap:12px}
.topbar-date{font-size:13px;color:var(--n400)}

.content{padding:32px;flex:1}

/* ── Stat Cards ─────────────────────────────────── */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px}
.stat-card{
  background:var(--white);border:1px solid var(--n200);border-radius:var(--r-lg);
  padding:20px 24px;display:flex;flex-direction:column;gap:6px;
}
.stat-card-icon{font-size:24px;margin-bottom:4px}
.stat-card-value{font-family:var(--fd);font-size:32px;color:var(--n900);line-height:1}
.stat-card-label{font-size:12px;color:var(--n400);font-weight:500;text-transform:uppercase;letter-spacing:.06em}
.stat-card-delta{font-size:12px;font-weight:500;margin-top:2px}
.delta-up{color:var(--g600)}
.delta-down{color:var(--r500)}

/* ── Section Header ─────────────────────────────── */
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.section-title{font-family:var(--fd);font-size:20px;font-weight:400;color:var(--n900)}
.section-subtitle{font-size:13px;color:var(--n400);margin-top:2px}

/* ── Tabs ───────────────────────────────────────── */
.tabs{display:flex;gap:4px;background:var(--n100);border-radius:var(--r-sm);padding:4px;margin-bottom:20px;width:fit-content}
.tab-btn{
  padding:8px 18px;font-size:13px;font-weight:500;color:var(--n600);
  background:none;border:none;border-radius:6px;transition:all .15s;
}
.tab-btn.active{background:var(--white);color:var(--n900);box-shadow:0 1px 4px rgba(0,0,0,.1)}
.tab-btn:hover:not(.active){color:var(--n900)}

/* ── Request Cards ──────────────────────────────── */
.requests-grid{display:flex;flex-direction:column;gap:12px}
.request-card{
  background:var(--white);border:1px solid var(--n200);border-radius:var(--r-lg);
  padding:20px 24px;display:flex;align-items:center;gap:16px;
  transition:box-shadow .2s;
}
.request-card:hover{box-shadow:var(--sh)}

.client-avatar{
  width:48px;height:48px;border-radius:50%;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fd);font-size:17px;font-weight:400;
}
.av-green{background:var(--g50);color:var(--g700);border:1px solid var(--g100)}
.av-teal{background:var(--t50);color:var(--t500);border:1px solid var(--t100)}
.av-amber{background:var(--a50);color:var(--a600);border:1px solid var(--a100)}
.av-blue{background:var(--b50);color:var(--b500);border:1px solid var(--b100)}
.av-coral{background:var(--r50);color:var(--r600);border:1px solid var(--r100)}

.request-info{flex:1;min-width:0}
.request-name{font-size:15px;font-weight:600;color:var(--n900)}
.request-meta{font-size:13px;color:var(--n400);margin-top:2px}
.request-goal{
  display:inline-flex;align-items:center;gap:5px;margin-top:6px;
  font-size:12px;font-weight:500;padding:3px 10px;border-radius:100px;
}
.goal-weight{background:var(--b50);color:var(--b500)}
.goal-muscle{background:var(--g50);color:var(--g600)}
.goal-diabetes{background:var(--a50);color:var(--a600)}
.goal-wellness{background:var(--t50);color:var(--t500)}
.goal-custom{background:var(--n100);color:var(--n600)}

.request-date{font-size:12px;color:var(--n400);white-space:nowrap;margin-right:8px}

.request-actions{display:flex;gap:8px;align-items:center}
.btn-accept{
  background:var(--g600);color:var(--white);border:none;
  padding:8px 18px;border-radius:var(--r-sm);font-size:13px;font-weight:500;
  transition:background .15s;
}
.btn-accept:hover{background:var(--g700)}
.btn-reject{
  background:var(--white);color:var(--r500);border:1px solid var(--r100);
  padding:8px 18px;border-radius:var(--r-sm);font-size:13px;font-weight:500;
  transition:all .15s;
}
.btn-reject:hover{background:var(--r50);border-color:var(--r500)}
.btn-view{
  background:var(--n50);color:var(--n600);border:1px solid var(--n200);
  padding:8px 14px;border-radius:var(--r-sm);font-size:13px;font-weight:500;
  transition:all .15s;
}
.btn-view:hover{background:var(--n100)}

/* Status badges */
.status-badge{
  display:inline-flex;align-items:center;gap:5px;
  font-size:12px;font-weight:600;padding:4px 10px;border-radius:100px;
}
.status-pending{background:var(--a50);color:var(--a600)}
.status-accepted{background:var(--g50);color:var(--g600)}
.status-rejected{background:var(--r50);color:var(--r600)}
.status-dot{width:6px;height:6px;border-radius:50%;background:currentColor}

/* ── Clients Table (Accepted) ───────────────────── */
.clients-table{
  width:100%;border-collapse:collapse;background:var(--white);
  border:1px solid var(--n200);border-radius:var(--r-lg);overflow:hidden;
}
.clients-table thead tr{background:var(--n50);border-bottom:1px solid var(--n200)}
.clients-table th{
  padding:12px 16px;font-size:11px;font-weight:600;letter-spacing:.08em;
  text-transform:uppercase;color:var(--n400);text-align:left;
}
.clients-table td{padding:14px 16px;border-bottom:1px solid var(--n100);font-size:14px;color:var(--n600)}
.clients-table tr:last-child td{border-bottom:none}
.clients-table tr:hover td{background:var(--n50)}
.client-name-cell{display:flex;align-items:center;gap:10px}
.client-name-cell span{font-weight:500;color:var(--n900)}

.btn-plan{
  background:var(--g50);color:var(--g600);border:1px solid var(--g100);
  padding:6px 14px;border-radius:var(--r-sm);font-size:12px;font-weight:500;
  transition:all .15s;
}
.btn-plan:hover{background:var(--g100)}
.btn-feedback{
  background:var(--b50);color:var(--b500);border:1px solid var(--b100);
  padding:6px 14px;border-radius:var(--r-sm);font-size:12px;font-weight:500;
  transition:all .15s;margin-left:6px;
}
.btn-feedback:hover{background:var(--b100)}

/* ── Feedback Cards ─────────────────────────────── */
.feedback-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.feedback-card{
  background:var(--white);border:1px solid var(--n200);border-radius:var(--r-lg);
  padding:20px;
}
.feedback-header{display:flex;align-items:center;gap:12px;margin-bottom:12px}
.feedback-meta{flex:1}
.feedback-client{font-size:14px;font-weight:600;color:var(--n900)}
.feedback-date{font-size:12px;color:var(--n400)}
.feedback-stars{color:#f0b429;font-size:15px;letter-spacing:2px;margin-bottom:8px}
.feedback-text{font-size:14px;color:var(--n600);line-height:1.65}
.feedback-tag{
  display:inline-block;margin-top:10px;font-size:11px;font-weight:500;
  padding:3px 10px;border-radius:100px;background:var(--g50);color:var(--g600);
}

/* ── Modals ─────────────────────────────────────── */
.modal-overlay{
  display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);
  z-index:500;align-items:center;justify-content:center;padding:20px;
}
.modal-overlay.open{display:flex}
.modal{
  background:var(--white);border-radius:var(--r-xl);
  width:100%;max-width:600px;max-height:90vh;overflow-y:auto;
  box-shadow:0 24px 64px rgba(0,0,0,.18);
}
.modal-lg{max-width:760px}
.modal-header{
  display:flex;align-items:center;justify-content:space-between;
  padding:24px 28px 0;
}
.modal-title{font-family:var(--fd);font-size:22px;font-weight:400;color:var(--n900)}
.modal-close{
  width:32px;height:32px;border-radius:50%;background:var(--n100);border:none;
  font-size:18px;color:var(--n400);display:flex;align-items:center;justify-content:center;
  transition:background .15s;
}
.modal-close:hover{background:var(--n200);color:var(--n900)}
.modal-body{padding:20px 28px 28px}

/* Form elements in modal */
.form-group{display:flex;flex-direction:column;gap:6px;margin-bottom:16px}
.form-label{font-size:13px;font-weight:500;color:var(--n900)}
.form-input,.form-select,.form-textarea{
  font-family:var(--fb);font-size:14px;color:var(--n900);
  background:var(--white);border:1px solid var(--n200);border-radius:var(--r-sm);
  padding:10px 14px;outline:none;transition:border-color .2s,box-shadow .2s;width:100%;
}
.form-input:focus,.form-select:focus,.form-textarea:focus{
  border-color:var(--g500);box-shadow:0 0 0 3px var(--g50);
}
.form-textarea{resize:vertical;min-height:90px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-section-title{
  font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;
  color:var(--n400);margin:20px 0 12px;padding-top:16px;border-top:1px solid var(--n100);
}
.form-section-title:first-child{border-top:none;margin-top:0;padding-top:0}

/* Diet plan day block */
.day-block{
  border:1px solid var(--n200);border-radius:var(--r-md);padding:16px;margin-bottom:12px;
  background:var(--n50);
}
.day-label{font-size:12px;font-weight:600;color:var(--g600);letter-spacing:.06em;text-transform:uppercase;margin-bottom:10px}
.meal-row{display:grid;grid-template-columns:90px 1fr;gap:10px;margin-bottom:8px;align-items:start}
.meal-label{font-size:12px;font-weight:500;color:var(--n600);padding-top:10px}

.modal-footer{
  padding:16px 28px 24px;border-top:1px solid var(--n100);
  display:flex;align-items:center;justify-content:flex-end;gap:10px;
}
.btn-primary{
  background:var(--g600);color:var(--white);border:none;
  padding:10px 24px;border-radius:var(--r-sm);font-size:14px;font-weight:500;
  transition:background .15s;
}
.btn-primary:hover{background:var(--g700)}
.btn-secondary{
  background:var(--white);color:var(--n600);border:1px solid var(--n200);
  padding:10px 24px;border-radius:var(--r-sm);font-size:14px;font-weight:500;
  transition:all .15s;
}
.btn-secondary:hover{background:var(--n50)}

/* Client detail in modal */
.client-detail-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
.detail-chip{
  background:var(--n50);border:1px solid var(--n200);border-radius:var(--r-sm);
  padding:12px 16px;
}
.detail-chip-label{font-size:11px;font-weight:500;color:var(--n400);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px}
.detail-chip-val{font-size:15px;font-weight:600;color:var(--n900)}

/* Toast */
.toast{
  position:fixed;bottom:32px;right:32px;z-index:9999;
  background:var(--n900);color:var(--white);padding:14px 20px;
  border-radius:var(--r-md);font-size:14px;font-weight:500;
  box-shadow:0 8px 24px rgba(0,0,0,.2);
  transform:translateY(80px);opacity:0;transition:all .3s;pointer-events:none;
  display:flex;align-items:center;gap:10px;
}
.toast.show{transform:translateY(0);opacity:1}
.toast-icon{font-size:16px}

/* Edit Profile avatar */
.profile-avatar-edit{
  width:80px;height:80px;border-radius:50%;background:var(--g600);
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fd);font-size:28px;color:var(--white);
  margin:0 auto 16px;position:relative;
}
.avatar-upload-btn{
  position:absolute;bottom:0;right:0;width:26px;height:26px;
  background:var(--n900);border-radius:50%;border:2px solid var(--white);
  display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--white);
}

/* Empty state */
.empty-state{
  text-align:center;padding:56px 24px;color:var(--n400);
}
.empty-icon{font-size:40px;margin-bottom:12px}
.empty-text{font-size:15px}

/* Responsive */
@media(max-width:900px){
  .stats-row{grid-template-columns:1fr 1fr}
  .feedback-grid{grid-template-columns:1fr}
}
@media(max-width:700px){
  :root{--sidebar-w:0px}
  .sidebar{transform:translateX(-260px)}
  .main{margin-left:0}
  .stats-row{grid-template-columns:1fr 1fr}
  .content{padding:20px}
  .form-row{grid-template-columns:1fr}
  .client-detail-row{grid-template-columns:1fr}
}
</style>
</head>
<body>

<!-- ═══════════════ SIDEBAR ═══════════════ -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <span class="brand-icon">🌿</span>
    <span class="brand-name">NutriTrack</span>
  </div>

  <div class="dietitian-profile">
    <div class="dp-avatar">DR</div>
    <div class="dp-name">Dr. Riya Mehta</div>
    <div class="dp-role">Registered Dietitian</div>
    <div class="dp-badge">RD · 8 yrs exp</div>
  </div>

  <p class="nav-section-label">Overview</p>
  <button class="nav-item active" onclick="showView('dashboard')">
    <span class="nav-icon">📊</span> Dashboard
  </button>

  <p class="nav-section-label">Clients</p>
  <button class="nav-item" onclick="showView('requests')">
    <span class="nav-icon">📥</span> Client Requests
    <span class="nav-badge" id="req-badge">3</span>
  </button>
  <button class="nav-item" onclick="showView('clients')">
    <span class="nav-icon">👥</span> My Clients
  </button>
  <button class="nav-item" onclick="showView('feedback')">
    <span class="nav-icon">⭐</span> Feedback
  </button>

  <p class="nav-section-label">Account</p>
  <button class="nav-item" onclick="openEditProfile()">
    <span class="nav-icon">✏️</span> Edit Profile
  </button>

  <div class="sidebar-footer">
    <form action="logout.php" method="post">
        <button type="submit" name="logout">Logout</button>
    </form>
</div>
</aside>

<!-- ═══════════════ MAIN ═══════════════ -->
<main class="main">

  <!-- Top bar -->
  <div class="topbar">
    <div>
      <div class="topbar-title" id="topbar-title">Dashboard</div>
    </div>
    <div class="topbar-actions">
      <span class="topbar-date" id="today-date"></span>
      <button class="btn-view" style="font-size:13px" onclick="openEditProfile()">✏️ Edit Profile</button>
      <button class="btn-reject" style="padding:8px 16px;font-size:13px" onclick="handleLogout()">🚪 Logout</button>
    </div>
  </div>

  <div class="content">

    <!-- ── DASHBOARD VIEW ── -->
    <div id="view-dashboard">
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-card-icon">📥</div>
          <div class="stat-card-value" id="stat-pending">3</div>
          <div class="stat-card-label">Pending Requests</div>
          <div class="stat-card-delta delta-up">↑ 2 new today</div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon">👥</div>
          <div class="stat-card-value" id="stat-clients">5</div>
          <div class="stat-card-label">Active Clients</div>
          <div class="stat-card-delta delta-up">↑ 1 this week</div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon">📋</div>
          <div class="stat-card-value">8</div>
          <div class="stat-card-label">Diet Plans Made</div>
          <div class="stat-card-delta delta-up">↑ 2 this month</div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon">⭐</div>
          <div class="stat-card-value">4.8</div>
          <div class="stat-card-label">Avg. Rating</div>
          <div class="stat-card-delta delta-up">from 12 reviews</div>
        </div>
      </div>

      <div class="section-header">
        <div>
          <div class="section-title">Recent Requests</div>
          <div class="section-subtitle">Latest client consultation requests</div>
        </div>
        <button class="btn-view" onclick="showView('requests')">View all →</button>
      </div>
      <div class="requests-grid" id="dashboard-recent-requests"></div>
    </div>

    <!-- ── REQUESTS VIEW ── -->
    <div id="view-requests" style="display:none">
      <div class="section-header">
        <div>
          <div class="section-title">Client Requests</div>
          <div class="section-subtitle">Accept or reject incoming consultation requests</div>
        </div>
      </div>
      <div class="tabs">
        <button class="tab-btn active" onclick="filterRequests('pending',this)">Pending</button>
        <button class="tab-btn" onclick="filterRequests('accepted',this)">Accepted</button>
        <button class="tab-btn" onclick="filterRequests('rejected',this)">Rejected</button>
        <button class="tab-btn" onclick="filterRequests('all',this)">All</button>
      </div>
      <div class="requests-grid" id="requests-list"></div>
    </div>

    <!-- ── CLIENTS VIEW ── -->
    <div id="view-clients" style="display:none">
      <div class="section-header">
        <div>
          <div class="section-title">My Clients</div>
          <div class="section-subtitle">Accepted clients — create and manage diet plans</div>
        </div>
      </div>
      <table class="clients-table">
        <thead>
          <tr>
            <th>Client</th>
            <th>Goal</th>
            <th>Age / BMI</th>
            <th>Plan Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="clients-tbody"></tbody>
      </table>
    </div>

    <!-- ── FEEDBACK VIEW ── -->
    <div id="view-feedback" style="display:none">
      <div class="section-header">
        <div>
          <div class="section-title">Client Feedback</div>
          <div class="section-subtitle">Reviews and responses from your clients</div>
        </div>
      </div>
      <div class="feedback-grid" id="feedback-grid"></div>
    </div>

  </div><!-- /content -->
</main>

<!-- ═══════════════ MODALS ═══════════════ -->

<!-- Client Detail Modal -->
<div class="modal-overlay" id="modal-client">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="modal-client-title">Client Details</div>
      <button class="modal-close" onclick="closeModal('modal-client')">✕</button>
    </div>
    <div class="modal-body" id="modal-client-body"></div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('modal-client')">Close</button>
      <button class="btn-reject" id="modal-reject-btn" style="padding:10px 20px;font-size:14px">✕ Reject</button>
      <button class="btn-accept" id="modal-accept-btn">✓ Accept Request</button>
    </div>
  </div>
</div>

<!-- Diet Plan Modal -->
<div class="modal-overlay" id="modal-plan">
  <div class="modal modal-lg">
    <div class="modal-header">
      <div class="modal-title" id="modal-plan-title">Create Diet Plan</div>
      <button class="modal-close" onclick="closeModal('modal-plan')">✕</button>
    </div>
    <div class="modal-body">
      <div id="modal-plan-client-info"></div>
      <p class="form-section-title">Plan Details</p>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Plan Name</label>
          <input class="form-input" id="plan-name" placeholder="e.g. 7-Day Weight Loss Plan">
        </div>
        <div class="form-group">
          <label class="form-label">Duration</label>
          <select class="form-select" id="plan-duration">
            <option>7 days</option><option>14 days</option><option>21 days</option><option>30 days</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Daily Calorie Target (kcal)</label>
          <input class="form-input" id="plan-calories" placeholder="e.g. 1800" type="number">
        </div>
        <div class="form-group">
          <label class="form-label">Water Intake (litres/day)</label>
          <input class="form-input" id="plan-water" placeholder="e.g. 2.5" type="number" step="0.1">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Dietary Restrictions / Allergies</label>
        <input class="form-input" id="plan-restrictions" placeholder="e.g. No gluten, lactose-free, no nuts">
      </div>

      <p class="form-section-title">Sample Daily Meal Plan</p>
      <div class="day-block">
        <div class="day-label">🌅 Day 1 (Repeat pattern)</div>
        <div class="meal-row">
          <span class="meal-label">Breakfast</span>
          <input class="form-input" id="meal-breakfast" placeholder="e.g. Oats with banana, green tea">
        </div>
        <div class="meal-row">
          <span class="meal-label">Mid-morning</span>
          <input class="form-input" id="meal-snack1" placeholder="e.g. Apple, a handful of almonds">
        </div>
        <div class="meal-row">
          <span class="meal-label">Lunch</span>
          <input class="form-input" id="meal-lunch" placeholder="e.g. Brown rice, dal, sabzi, salad">
        </div>
        <div class="meal-row">
          <span class="meal-label">Evening</span>
          <input class="form-input" id="meal-snack2" placeholder="e.g. Buttermilk, sprouts chaat">
        </div>
        <div class="meal-row">
          <span class="meal-label">Dinner</span>
          <input class="form-input" id="meal-dinner" placeholder="e.g. 2 rotis, paneer, steamed vegetables">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Special Notes / Exercise Recommendations</label>
        <textarea class="form-textarea" id="plan-notes" placeholder="Add any additional guidance, supplements, or lifestyle tips..."></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('modal-plan')">Cancel</button>
      <button class="btn-primary" onclick="savePlan()">💾 Save Diet Plan</button>
    </div>
  </div>
</div>

<!-- Feedback Modal -->
<div class="modal-overlay" id="modal-feedback">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="modal-feedback-title">Client Feedback</div>
      <button class="modal-close" onclick="closeModal('modal-feedback')">✕</button>
    </div>
    <div class="modal-body" id="modal-feedback-body"></div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('modal-feedback')">Close</button>
    </div>
  </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal-overlay" id="modal-profile">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Edit Profile</div>
      <button class="modal-close" onclick="closeModal('modal-profile')">✕</button>
    </div>
    <div class="modal-body">
      <div class="profile-avatar-edit">
        DR <span class="avatar-upload-btn">📷</span>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">First Name</label>
          <input class="form-input" value="Riya">
        </div>
        <div class="form-group">
          <label class="form-label">Last Name</label>
          <input class="form-input" value="Mehta">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input class="form-input" type="email" value="riya.mehta@NutriTrack.com">
      </div>
      <div class="form-group">
        <label class="form-label">Phone Number</label>
        <input class="form-input" type="tel" value="+91 98765 43210">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Specialisation</label>
          <select class="form-select">
            <option selected>Weight Management</option>
            <option>Sports Nutrition</option>
            <option>Diabetes Care</option>
            <option>Paediatric Nutrition</option>
            <option>Renal Nutrition</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Years of Experience</label>
          <input class="form-input" type="number" value="8">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Bio / About</label>
        <textarea class="form-textarea">Registered Dietitian with 8 years of experience in clinical nutrition and wellness coaching. Specialising in weight management, PCOS, and metabolic health.</textarea>
      </div>
      <p class="form-section-title">Change Password</p>
      <div class="form-group">
        <label class="form-label">Current Password</label>
        <input class="form-input" type="password" placeholder="Enter current password">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input class="form-input" type="password" placeholder="New password">
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password</label>
          <input class="form-input" type="password" placeholder="Repeat new password">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('modal-profile')">Cancel</button>
      <button class="btn-primary" onclick="saveProfile()">Save Changes</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast">
  <span class="toast-icon" id="toast-icon">✅</span>
  <span id="toast-msg">Done!</span>
</div>

<script>
// ═══════════════════════════════════════════════
//  DATA
// ═══════════════════════════════════════════════
const avatarColors = ['av-green','av-teal','av-amber','av-blue','av-coral'];
const goalClasses  = {
  'Weight Loss':'goal-weight','Muscle Gain':'goal-muscle',
  'Diabetes Management':'goal-diabetes','General Wellness':'goal-wellness','Custom Plan':'goal-custom'
};

let requests = [
  {id:1,name:'Ananya Desai',age:28,gender:'Female',weight:'72 kg',height:'163 cm',bmi:'27.1',goal:'Weight Loss',note:'Looking to lose 8 kg before wedding in 3 months.',date:'Today, 9:14 AM',status:'pending',av:'av-green',planSaved:false,planData:null,feedback:null},
  {id:2,name:'Rohan Kapoor',age:34,gender:'Male',weight:'88 kg',height:'178 cm',bmi:'27.8',goal:'Diabetes Management',note:'Type 2 diabetic, want a diet plan to control sugar levels naturally.',date:'Today, 8:02 AM',status:'pending',av:'av-amber',planSaved:false,planData:null,feedback:null},
  {id:3,name:'Simran Batra',age:22,gender:'Female',weight:'54 kg',height:'160 cm',bmi:'21.1',goal:'Muscle Gain',note:'Gym 5 days a week, need a high-protein plan for lean muscle.',date:'Yesterday',status:'pending',av:'av-blue',planSaved:false,planData:null,feedback:null},
  {id:4,name:'Nikhil Joshi',age:41,gender:'Male',weight:'94 kg',height:'175 cm',bmi:'30.7',goal:'Weight Loss',note:'Doctor suggested I lose weight due to high BP.',date:'2 days ago',status:'accepted',av:'av-coral',planSaved:true,planData:{name:'30-Day Low-Carb Plan',calories:'1700',duration:'30 days'},feedback:{stars:5,text:'Excellent plan! Already lost 3 kg in 3 weeks. Dr. Riya explains everything clearly.',tag:'Weight Loss'}},
  {id:5,name:'Priya Sharma',age:29,gender:'Female',weight:'61 kg',height:'162 cm',bmi:'23.2',goal:'General Wellness',note:'Want to improve gut health and energy levels.',date:'3 days ago',status:'accepted',av:'av-teal',planSaved:true,planData:{name:'Gut Health Boost Plan',calories:'1900',duration:'21 days'},feedback:{stars:4,text:'Very informative and easy to follow. Could use more variety in dinner options.',tag:'General Wellness'}},
  {id:6,name:'Arjun Malhotra',age:19,gender:'Male',weight:'65 kg',height:'180 cm',bmi:'20.1',goal:'Muscle Gain',note:'College athlete, wants performance nutrition.',date:'4 days ago',status:'rejected',av:'av-green',planSaved:false,planData:null,feedback:null},
  {id:7,name:'Kavya Reddy',age:35,gender:'Female',weight:'78 kg',height:'165 cm',bmi:'28.7',goal:'Weight Loss',note:'PCOS diagnosis. Need a diet to help manage symptoms.',date:'5 days ago',status:'accepted',av:'av-amber',planSaved:false,planData:null,feedback:null},
];

let currentRequestId = null;
let currentPlanClientId = null;

// ═══════════════════════════════════════════════
//  INIT
// ═══════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('today-date').textContent =
    new Date().toLocaleDateString('en-IN',{weekday:'long',day:'numeric',month:'long',year:'numeric'});
  renderDashboard();
  renderRequests('pending');
  renderClients();
  renderFeedback();
});

function updateBadge(){
  const count = requests.filter(r=>r.status==='pending').length;
  document.getElementById('req-badge').textContent = count;
  document.getElementById('stat-pending').textContent = count;
  document.getElementById('stat-clients').textContent = requests.filter(r=>r.status==='accepted').length;
}

// ═══════════════════════════════════════════════
//  NAVIGATION
// ═══════════════════════════════════════════════
function showView(view){
  ['dashboard','requests','clients','feedback'].forEach(v=>{
    document.getElementById('view-'+v).style.display = v===view?'block':'none';
  });
  document.querySelectorAll('.nav-item').forEach(el=>el.classList.remove('active'));
  event.currentTarget && event.currentTarget.classList.add('active');
  const titles={dashboard:'Dashboard',requests:'Client Requests',clients:'My Clients',feedback:'Feedback'};
  document.getElementById('topbar-title').textContent = titles[view]||'Dashboard';
}

// ═══════════════════════════════════════════════
//  RENDER DASHBOARD
// ═══════════════════════════════════════════════
function renderDashboard(){
  const el = document.getElementById('dashboard-recent-requests');
  const recent = requests.filter(r=>r.status==='pending').slice(0,3);
  if(!recent.length){ el.innerHTML='<div class="empty-state"><div class="empty-icon">🎉</div><div class="empty-text">No pending requests right now!</div></div>'; return; }
  el.innerHTML = recent.map(r=>requestCardHTML(r,true)).join('');
  updateBadge();
}

// ═══════════════════════════════════════════════
//  RENDER REQUESTS
// ═══════════════════════════════════════════════
function filterRequests(status, btn){
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  renderRequests(status);
}

function renderRequests(filter='pending'){
  const list = filter==='all' ? requests : requests.filter(r=>r.status===filter);
  const el = document.getElementById('requests-list');
  if(!list.length){
    el.innerHTML='<div class="empty-state"><div class="empty-icon">📭</div><div class="empty-text">No '+filter+' requests.</div></div>';
    return;
  }
  el.innerHTML = list.map(r=>requestCardHTML(r,false)).join('');
  updateBadge();
}

function requestCardHTML(r, compact){
  const badgeClass = {pending:'status-pending',accepted:'status-accepted',rejected:'status-rejected'}[r.status];
  const goalClass  = goalClasses[r.goal]||'goal-custom';
  let actions = '';
  if(r.status==='pending'){
    actions = `<button class="btn-accept" onclick="acceptRequest(${r.id})">✓ Accept</button>
               <button class="btn-reject" onclick="rejectRequest(${r.id})">✕ Reject</button>`;
  } else if(r.status==='accepted'){
    actions = `<button class="btn-plan" onclick="openPlanModal(${r.id})">${r.planSaved?'✏️ Edit Plan':'📋 Make Plan'}</button>
               ${r.feedback?`<button class="btn-feedback" onclick="viewFeedback(${r.id})">⭐ Feedback</button>`:''}`;
  }
  return `
  <div class="request-card" id="req-card-${r.id}">
    <div class="client-avatar ${r.av}">${r.name.split(' ').map(n=>n[0]).join('')}</div>
    <div class="request-info">
      <div class="request-name">${r.name}</div>
      <div class="request-meta">${r.age} yrs · ${r.gender} · BMI ${r.bmi} · ${r.weight}</div>
      <span class="request-goal ${goalClass}">${r.goal}</span>
    </div>
    <span class="request-date">${r.date}</span>
    <span class="status-badge ${badgeClass}"><span class="status-dot"></span>${cap(r.status)}</span>
    <div class="request-actions">
      <button class="btn-view" onclick="viewClient(${r.id})">View</button>
      ${actions}
    </div>
  </div>`;
}

// ═══════════════════════════════════════════════
//  RENDER CLIENTS TABLE
// ═══════════════════════════════════════════════
function renderClients(){
  const accepted = requests.filter(r=>r.status==='accepted');
  const tbody = document.getElementById('clients-tbody');
  if(!accepted.length){ tbody.innerHTML='<tr><td colspan="5"><div class="empty-state"><div class="empty-icon">👥</div><div class="empty-text">No accepted clients yet.</div></div></td></tr>'; return; }
  tbody.innerHTML = accepted.map(r=>`
    <tr>
      <td><div class="client-name-cell">
        <div class="client-avatar ${r.av}" style="width:36px;height:36px;font-size:13px">${r.name.split(' ').map(n=>n[0]).join('')}</div>
        <span>${r.name}</span>
      </div></td>
      <td><span class="request-goal ${goalClasses[r.goal]||'goal-custom'}" style="font-size:11px">${r.goal}</span></td>
      <td>${r.age} yrs · BMI ${r.bmi}</td>
      <td><span class="status-badge ${r.planSaved?'status-accepted':'status-pending'}"><span class="status-dot"></span>${r.planSaved?'Plan Created':'No Plan Yet'}</span></td>
      <td>
        <button class="btn-plan" onclick="openPlanModal(${r.id})">${r.planSaved?'✏️ Edit Plan':'📋 Make Plan'}</button>
        ${r.feedback?`<button class="btn-feedback" onclick="viewFeedback(${r.id})">⭐ Feedback</button>`:''}
      </td>
    </tr>`).join('');
}

// ═══════════════════════════════════════════════
//  RENDER FEEDBACK
// ═══════════════════════════════════════════════
function renderFeedback(){
  const withFeedback = requests.filter(r=>r.feedback);
  const el = document.getElementById('feedback-grid');
  if(!withFeedback.length){
    el.innerHTML='<div class="empty-state" style="grid-column:1/-1"><div class="empty-icon">💬</div><div class="empty-text">No feedback received yet.</div></div>';
    return;
  }
  el.innerHTML = withFeedback.map(r=>`
    <div class="feedback-card">
      <div class="feedback-header">
        <div class="client-avatar ${r.av}" style="width:40px;height:40px;font-size:14px">${r.name.split(' ').map(n=>n[0]).join('')}</div>
        <div class="feedback-meta">
          <div class="feedback-client">${r.name}</div>
          <div class="feedback-date">${r.date}</div>
        </div>
      </div>
      <div class="feedback-stars">${'★'.repeat(r.feedback.stars)}${'☆'.repeat(5-r.feedback.stars)}</div>
      <div class="feedback-text">"${r.feedback.text}"</div>
      <span class="feedback-tag">${r.feedback.tag}</span>
    </div>`).join('');
}

// ═══════════════════════════════════════════════
//  CLIENT DETAIL MODAL
// ═══════════════════════════════════════════════
function viewClient(id){
  const r = requests.find(x=>x.id===id);
  currentRequestId = id;
  document.getElementById('modal-client-title').textContent = r.name;
  document.getElementById('modal-client-body').innerHTML = `
    <div class="client-detail-row">
      <div class="detail-chip"><div class="detail-chip-label">Age</div><div class="detail-chip-val">${r.age} years</div></div>
      <div class="detail-chip"><div class="detail-chip-label">Gender</div><div class="detail-chip-val">${r.gender}</div></div>
      <div class="detail-chip"><div class="detail-chip-label">Weight</div><div class="detail-chip-val">${r.weight}</div></div>
      <div class="detail-chip"><div class="detail-chip-label">Height</div><div class="detail-chip-val">${r.height}</div></div>
      <div class="detail-chip"><div class="detail-chip-label">BMI</div><div class="detail-chip-val">${r.bmi}</div></div>
      <div class="detail-chip"><div class="detail-chip-label">Health Goal</div><div class="detail-chip-val">${r.goal}</div></div>
    </div>
    <div class="form-group">
      <label class="form-label">Client's Note</label>
      <div style="background:var(--n50);border:1px solid var(--n200);border-radius:var(--r-sm);padding:14px 16px;font-size:14px;color:var(--n600);line-height:1.65">${r.note}</div>
    </div>
    <div style="margin-top:8px">
      <span class="status-badge ${r.status==='pending'?'status-pending':r.status==='accepted'?'status-accepted':'status-rejected'}">
        <span class="status-dot"></span>${cap(r.status)}
      </span>
      <span style="font-size:12px;color:var(--n400);margin-left:10px">Received: ${r.date}</span>
    </div>`;

  const acceptBtn = document.getElementById('modal-accept-btn');
  const rejectBtn = document.getElementById('modal-reject-btn');
  if(r.status==='pending'){
    acceptBtn.style.display='inline-flex'; rejectBtn.style.display='inline-flex';
    acceptBtn.onclick=()=>{ acceptRequest(id); closeModal('modal-client'); };
    rejectBtn.onclick=()=>{ rejectRequest(id); closeModal('modal-client'); };
  } else {
    acceptBtn.style.display='none'; rejectBtn.style.display='none';
  }
  openModal('modal-client');
}

// ═══════════════════════════════════════════════
//  ACCEPT / REJECT
// ═══════════════════════════════════════════════
function acceptRequest(id){
  requests.find(r=>r.id===id).status='accepted';
  refresh(); toast('✅','Request accepted! Client moved to My Clients.');
}
function rejectRequest(id){
  requests.find(r=>r.id===id).status='rejected';
  refresh(); toast('❌','Request rejected.');
}
function refresh(){
  renderDashboard();
  renderRequests('pending');
  renderClients();
  renderFeedback();
  updateBadge();
  // re-apply current tab filter
  const activeTab = document.querySelector('#view-requests .tab-btn.active');
  if(activeTab) renderRequests(activeTab.textContent.toLowerCase());
}

// ═══════════════════════════════════════════════
//  DIET PLAN MODAL
// ═══════════════════════════════════════════════
function openPlanModal(id){
  const r = requests.find(x=>x.id===id);
  currentPlanClientId = id;
  document.getElementById('modal-plan-title').textContent =
    (r.planSaved?'Edit':'Create')+' Diet Plan — '+r.name;
  document.getElementById('modal-plan-client-info').innerHTML = `
    <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;background:var(--g50);border:1px solid var(--g100);border-radius:var(--r-md);margin-bottom:8px">
      <div class="client-avatar ${r.av}" style="width:40px;height:40px;font-size:14px">${r.name.split(' ').map(n=>n[0]).join('')}</div>
      <div>
        <div style="font-weight:600;font-size:14px">${r.name}</div>
        <div style="font-size:12px;color:var(--n400)">${r.age} yrs · ${r.weight} · BMI ${r.bmi} · Goal: ${r.goal}</div>
      </div>
    </div>`;
  // Pre-fill if plan exists
  if(r.planData){
    document.getElementById('plan-name').value=r.planData.name||'';
    document.getElementById('plan-calories').value=r.planData.calories||'';
    document.getElementById('plan-duration').value=r.planData.duration||'7 days';
    document.getElementById('meal-breakfast').value=r.planData.breakfast||'';
    document.getElementById('meal-snack1').value=r.planData.snack1||'';
    document.getElementById('meal-lunch').value=r.planData.lunch||'';
    document.getElementById('meal-snack2').value=r.planData.snack2||'';
    document.getElementById('meal-dinner').value=r.planData.dinner||'';
    document.getElementById('plan-notes').value=r.planData.notes||'';
    document.getElementById('plan-water').value=r.planData.water||'';
    document.getElementById('plan-restrictions').value=r.planData.restrictions||'';
  } else {
    ['plan-name','plan-calories','plan-water','plan-restrictions',
     'meal-breakfast','meal-snack1','meal-lunch','meal-snack2','meal-dinner','plan-notes']
    .forEach(id=>document.getElementById(id).value='');
  }
  openModal('modal-plan');
}

function savePlan(){
  const r = requests.find(x=>x.id===currentPlanClientId);
  r.planSaved = true;
  r.planData = {
    name: document.getElementById('plan-name').value||'Custom Plan',
    calories: document.getElementById('plan-calories').value,
    duration: document.getElementById('plan-duration').value,
    water: document.getElementById('plan-water').value,
    restrictions: document.getElementById('plan-restrictions').value,
    breakfast: document.getElementById('meal-breakfast').value,
    snack1: document.getElementById('meal-snack1').value,
    lunch: document.getElementById('meal-lunch').value,
    snack2: document.getElementById('meal-snack2').value,
    dinner: document.getElementById('meal-dinner').value,
    notes: document.getElementById('plan-notes').value,
  };
  closeModal('modal-plan');
  renderClients();
  toast('📋','Diet plan saved for '+r.name+'!');
}

// ═══════════════════════════════════════════════
//  FEEDBACK DETAIL MODAL
// ═══════════════════════════════════════════════
function viewFeedback(id){
  const r = requests.find(x=>x.id===id);
  document.getElementById('modal-feedback-title').textContent = 'Feedback from '+r.name;
  document.getElementById('modal-feedback-body').innerHTML = `
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
      <div class="client-avatar ${r.av}" style="width:48px;height:48px;font-size:16px">${r.name.split(' ').map(n=>n[0]).join('')}</div>
      <div>
        <div style="font-weight:600;font-size:15px">${r.name}</div>
        <div style="font-size:12px;color:var(--n400)">${r.goal} · ${r.date}</div>
      </div>
    </div>
    <div style="font-size:28px;color:#f0b429;letter-spacing:3px;margin-bottom:12px">${'★'.repeat(r.feedback.stars)}${'☆'.repeat(5-r.feedback.stars)}</div>
    <div style="background:var(--n50);border:1px solid var(--n200);border-radius:var(--r-md);padding:16px 20px;font-size:15px;color:var(--n600);line-height:1.7;font-style:italic">
      "${r.feedback.text}"
    </div>
    <div style="margin-top:12px">
      <span class="feedback-tag">${r.feedback.tag}</span>
    </div>`;
  openModal('modal-feedback');
}

// ═══════════════════════════════════════════════
//  PROFILE
// ═══════════════════════════════════════════════
function openEditProfile(){ openModal('modal-profile') }
function saveProfile(){ closeModal('modal-profile'); toast('✅','Profile updated successfully!') }

// ═══════════════════════════════════════════════
//  LOGOUT
// ═══════════════════════════════════════════════
// function handleLogout(){
//   if(confirm('Are you sure you want to logout?')){
//     toast('👋','Logging out...');
//     //setTimeout(()=>{ alert('You have been logged out. Redirecting to login page...'); },1200);

//   }
// }

// ═══════════════════════════════════════════════
//  MODAL HELPERS
// ═══════════════════════════════════════════════
function openModal(id){ document.getElementById(id).classList.add('open') }
function closeModal(id){ document.getElementById(id).classList.remove('open') }
document.querySelectorAll('.modal-overlay').forEach(m=>{
  m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('open') })
});

// ═══════════════════════════════════════════════
//  TOAST
// ═══════════════════════════════════════════════
function toast(icon, msg){
  const t=document.getElementById('toast');
  document.getElementById('toast-icon').textContent=icon;
  document.getElementById('toast-msg').textContent=msg;
  t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),3000);
}

// ═══════════════════════════════════════════════
//  UTILS
// ═══════════════════════════════════════════════
function cap(s){ return s.charAt(0).toUpperCase()+s.slice(1) }
</script>
</body>
</html>