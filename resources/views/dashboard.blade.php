<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="user-id" content="{{ session('user_id') }}">
  <meta name="type-id" content="{{ session('type_id') }}">
  <meta name="user-name" content="{{ session('user_name') }}">
  <meta name="user-email" content="{{ session('user_email') }}">
  <meta name="user-type" content="{{ session('user_type') }}">
  <title>Dashboard — UNSCollab</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css">
  <style>
    :root {
      --brand:   #1A56DB;
      --brand-l: #3B82F6;
      --bg:      #F8FAFC;
      --surface: #FFFFFF;
      --surface2:#F1F5F9;
      --border:  #E2E8F0;
      --text:    #0F172A;
      --muted:   #64748B;
      --success: #16A34A;
      --warn:    #D97706;
      --danger:  #DC2626;
      --purple:  #6366F1;
      --radius:  12px;
      --radius-sm: 8px;
      --shadow:  0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
      --shadow-md: 0 4px 16px rgba(0,0,0,.08);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      font-size: 14px;
      line-height: 1.5;
    }

    /* ── LAYOUT ───────────────────────────────── */
    .wrapper { display: flex; min-height: 100vh; }

    /* ── SIDEBAR ──────────────────────────────── */
    .sidebar {
      width: 248px;
      min-height: 100vh;
      background: var(--surface);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; left: 0;
      z-index: 200;
      transition: transform .25s ease;
    }

    .sidebar-brand {
      padding: 20px 18px 16px;
      display: flex;
      align-items: center;
      gap: 10px;
      border-bottom: 1px solid var(--border);
    }
    .brand-icon {
      width: 34px; height: 34px;
      background: var(--brand);
      border-radius: 9px;
      display: grid; place-items: center;
      color: #fff;
      font-weight: 800;
      font-size: 14px;
      flex-shrink: 0;
    }
    .brand-name {
      font-weight: 800;
      font-size: 16px;
      letter-spacing: -.3px;
      color: var(--text);
    }
    .brand-name span { color: var(--brand); }

    .nav-label {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      color: var(--muted);
      padding: 14px 18px 5px;
    }
    .nav-link-item {
      display: flex;
      align-items: center;
      gap: 9px;
      padding: 9px 12px;
      margin: 2px 8px;
      border-radius: var(--radius-sm);
      color: var(--muted);
      font-size: 13.5px;
      font-weight: 500;
      cursor: pointer;
      text-decoration: none;
      transition: all .15s;
      position: relative;
    }
    .nav-link-item i { font-size: 15px; flex-shrink: 0; }
    .nav-link-item:hover { background: var(--surface2); color: var(--text); }
    .nav-link-item.active { background: #EEF3FF; color: var(--brand); font-weight: 600; }
    .nav-link-item.active::before {
      content: '';
      position: absolute;
      left: -8px; top: 25%; bottom: 25%;
      width: 3px; background: var(--brand);
      border-radius: 0 3px 3px 0;
    }
    .nav-badge {
      margin-left: auto;
      font-size: 10px;
      font-weight: 700;
      padding: 1px 6px;
      border-radius: 20px;
    }
    .pill-blue  { background:#DBEAFE; color:#1D4ED8; }
    .pill-red   { background:#FEE2E2; color:#B91C1C; }
    .pill-green { background:#DCFCE7; color:#15803D; }
    .pill-yellow{ background:#FEF9C3; color:#A16207; }
    .pill-purple{ background:#EDE9FE; color:#6D28D9; }
    .pill { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:20px; font-size:11px; font-weight:600; }

    .sidebar-bottom {
      margin-top: auto;
      padding: 12px 8px;
      border-top: 1px solid var(--border);
    }

    /* ── TOPBAR ───────────────────────────────── */
    .main-content { margin-left: 248px; display: flex; flex-direction: column; min-height: 100vh; }

    #topbar {
      height: 60px;
      background: var(--surface);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      position: sticky; top: 0; z-index: 100;
      gap: 12px;
    }
    .topbar-search {
      flex: 1;
      max-width: 320px;
      display: flex;
      align-items: center;
      gap: 8px;
      background: var(--surface2);
      border: 1px solid var(--border);
      border-radius: 9px;
      padding: 7px 12px;
      color: var(--muted);
    }
    .topbar-search input {
      border: none; background: transparent;
      color: var(--text); font-size: 13px;
      font-family: inherit; outline: none; width: 100%;
    }
    .topbar-search input::placeholder { color: var(--muted); }
    .topbar-right { display: flex; align-items: center; gap: 10px; position: relative; }
    .icon-btn {
      width: 36px; height: 36px;
      border-radius: 9px;
      border: 1px solid var(--border);
      display: grid; place-items: center;
      color: var(--muted);
      font-size: 16px;
      cursor: pointer;
      transition: all .15s;
      position: relative;
    }
    .icon-btn:hover { color: var(--text); border-color: var(--brand); }
    .notif-dot {
      position: absolute;
      top: 7px; right: 7px;
      width: 7px; height: 7px;
      background: var(--danger);
      border-radius: 50%;
      border: 1.5px solid var(--surface);
    }
    .avatar-btn {
      width: 34px; height: 34px;
      border-radius: 9px;
      background: var(--brand);
      color: #fff;
      font-weight: 700;
      font-size: 12px;
      display: grid; place-items: center;
      cursor: pointer;
    }
    /* notif dropdown */
    .notif-dd {
      display: none;
      position: absolute;
      top: 48px; right: 40px;
      width: 300px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow-md);
      z-index: 300;
    }
    .notif-dd.open { display: block; }
    .notif-header { padding: 12px 16px; font-weight: 700; font-size: 13px; border-bottom: 1px solid var(--border); }
    .notif-item { display: flex; gap: 10px; padding: 12px 16px; border-bottom: 1px solid var(--border); }
    .notif-item:last-child { border-bottom: none; }
    .notif-ico { width: 8px; height: 8px; border-radius: 50%; margin-top: 5px; flex-shrink: 0; }
    .notif-text { font-size: 12.5px; color: var(--text); }
    .notif-time { font-size: 11px; color: var(--muted); margin-top: 2px; }

    /* ── PAGES ────────────────────────────────── */
    #content { padding: 24px; flex: 1; scroll-behavior: smooth; }
    .page { display: none; }
    .page.active { display: block; }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .pg-header {
      display: flex; align-items: flex-start; justify-content: space-between;
      flex-wrap: wrap; gap: 12px;
      margin-bottom: 22px;
    }
    .pg-header h4 { font-size: 20px; font-weight: 800; letter-spacing: -.3px; }
    .pg-header p  { color: var(--muted); font-size: 13px; margin-top: 2px; }

    /* ── CARDS ────────────────────────────────── */
    .ui-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 18px;
      box-shadow: var(--shadow);
    }
    .card-head {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 14px;
    }
    .card-head h6 { font-size: 13px; font-weight: 700; margin: 0; }

    /* ── STAT CARDS ─────────────────────���─────── */
    .stat-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 18px;
      box-shadow: var(--shadow);
      transition: transform .15s, box-shadow .15s;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
    .stat-icon {
      width: 40px; height: 40px; border-radius: 10px;
      display: grid; place-items: center; font-size: 18px;
      margin-bottom: 10px;
    }
    .stat-val { font-size: 26px; font-weight: 800; line-height: 1; margin-bottom: 3px; }
    .stat-label { font-size: 12px; color: var(--muted); margin-bottom: 5px; }
    .stat-trend { font-size: 11px; font-weight: 600; }

    /* ── BAR CHART ────────────────────────────── */
    .bar-group {
      display: flex; align-items: flex-end; gap: 10px;
      height: 130px; padding: 0 4px;
    }
    .bar-item { display: flex; flex-direction: column; align-items: center; flex: 1; height: 100%; justify-content: flex-end; gap: 4px; }
    .bar-fill { width: 100%; border-radius: 5px 5px 0 0; transition: height .7s cubic-bezier(.34,1.56,.64,1); min-width: 20px; }
    .bar-lbl { font-size: 10px; color: var(--muted); text-align: center; white-space: nowrap; }

    /* ── PROGRESS ─────────────────────────────── */
    .prog-row { margin-bottom: 12px; }
    .prog-top { display: flex; justify-content: space-between; margin-bottom: 5px; }
    .prog-bar { height: 6px; background: var(--surface2); border-radius: 10px; overflow: hidden; }
    .prog-fill { height: 100%; border-radius: 10px; transition: width .7s ease; }

    /* ── TIMELINE ─────────────────────────────── */
    .tl-item { display: flex; gap: 12px; padding-bottom: 14px; }
    .tl-left { display: flex; flex-direction: column; align-items: center; }
    .tl-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 3px; }
    .tl-line { flex: 1; width: 1px; background: var(--border); margin-top: 3px; }
    .tl-title { font-size: 13px; color: var(--text); }
    .tl-time  { font-size: 11px; color: var(--muted); margin-top: 2px; }

    /* ── LOWONGAN CARDS ───────────────────────── */
    .lw-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 16px;
      cursor: pointer;
      transition: all .15s;
      height: 100%;
    }
    .lw-card:hover { border-color: var(--brand-l); box-shadow: 0 0 0 3px rgba(26,86,219,.06); transform: translateY(-2px); }
    .lw-card.pending-card { opacity: .85; cursor: default; }
    .lw-card.pending-card:hover { transform: none; border-color: var(--border); box-shadow: none; }
    .lw-icon { width: 36px; height: 36px; border-radius: 9px; display: grid; place-items: center; font-size: 16px; flex-shrink: 0; }
    .float-label { display: inline-block; font-size: 11px; font-weight: 500; background: var(--surface2); color: var(--muted); border-radius: 6px; padding: 2px 8px; margin: 0 3px 4px 0; }
    .lw-footer { display: flex; align-items: center; justify-content: space-between; }

    /* status filter tabs */
    .nav-pill-tab {
      display: inline-block;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      color: var(--muted);
      cursor: pointer;
      transition: all .15s;
      text-decoration: none;
    }
    .nav-pill-tab:hover { background: var(--surface2); color: var(--text); }
    .nav-pill-tab.active { background: var(--brand); color: #fff; }

    /* pending banner */
    .pending-banner {
      background: #FFFBEB;
      border: 1px solid #FDE68A;
      border-radius: var(--radius-sm);
      padding: 10px 14px;
      font-size: 12px;
      color: #92400E;
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 10px;
    }

    /* ── TABLE ────────────────────────────────── */
    .ui-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .ui-table th {
      font-size: 11px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase;
      color: var(--muted); padding: 10px 14px; border-bottom: 1px solid var(--border);
      background: var(--surface2); white-space: nowrap;
    }
    .ui-table th:first-child { border-radius: var(--radius-sm) 0 0 0; }
    .ui-table th:last-child  { border-radius: 0 var(--radius-sm) 0 0; }
    .ui-table td { padding: 12px 14px; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .ui-table tbody tr { cursor: pointer; transition: background .12s; }
    .ui-table tbody tr:hover td { background: #F8FAFF; }
    .ui-table tbody tr:last-child td { border-bottom: none; }
    .mini-avatar {
      width: 30px; height: 30px; border-radius: 8px;
      display: grid; place-items: center; font-size: 11px; font-weight: 700; flex-shrink: 0;
    }

    /* ── DETAIL PAGE ──────────────────────────── */
    .profile-hero {
      background: linear-gradient(135deg, var(--brand) 0%, #6366F1 100%);
      border-radius: var(--radius);
      padding: 24px;
      color: #fff;
    }
    .big-avatar {
      width: 60px; height: 60px; border-radius: 14px;
      background: rgba(255,255,255,.25);
      display: grid; place-items: center;
      font-size: 20px; font-weight: 800; flex-shrink: 0;
    }

    /* flow steps */
    .flow-step { display: flex; flex-direction: column; align-items: center; flex: 1; position: relative; }
    .flow-step:not(:last-child)::after {
      content: ''; position: absolute; top: 13px; left: 50%; width: 100%; height: 2px;
      background: var(--border); z-index: 0;
    }
    .flow-dot {
      width: 26px; height: 26px; border-radius: 50%;
      display: grid; place-items: center;
      font-size: 11px; font-weight: 700;
      border: 2px solid var(--border);
      background: var(--surface); color: var(--muted);
      position: relative; z-index: 1;
    }
    .flow-dot.done { background: var(--success); border-color: var(--success); color: #fff; }
    .flow-dot.current { background: var(--brand); border-color: var(--brand); color: #fff; }
    .flow-label { font-size: 10px; color: var(--muted); margin-top: 5px; text-align: center; }

    .info-box { background: var(--surface2); border-radius: var(--radius-sm); padding: 10px 12px; }
    .info-key { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); margin-bottom: 3px; }
    .info-val { font-size: 13px; font-weight: 600; }
    .skill-tag { display: inline-block; padding: 4px 10px; background: #EEF3FF; color: var(--brand); border-radius: 20px; font-size: 11px; font-weight: 600; margin: 3px 3px 3px 0; }
    .doc-row { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border); }

    /* ── FORM / TAMBAH ────────────────────────── */
    .step-num {
      width: 24px; height: 24px; border-radius: 50%; border: 2px solid var(--border);
      display: grid; place-items: center; font-size: 11px; font-weight: 700; color: var(--muted);
    }
    .step-num.active-step { background: var(--brand); border-color: var(--brand); color: #fff; }
    .step-num.done-step { background: var(--success); border-color: var(--success); color: #fff; }

    /* ── EMPTY STATE ──────────────────────────── */
    .empty-state {
      text-align: center;
      padding: 60px 30px;
      border: 2px dashed var(--border);
      border-radius: var(--radius);
      background: var(--surface2);
    }
    .empty-state i { font-size: 48px; color: var(--muted); margin-bottom: 12px; }
    .empty-state h5 { color: var(--text); font-weight: 700; margin: 12px 0 6px; }
    .empty-state p { color: var(--muted); font-size: 13px; margin: 0; }

    /* ── PROFIL ───────────────────────────────── */
    /* (reuses ui-card) */

    /* ── PENGATURAN ───────────────────────────── */
    .toggle-switch {
      width: 38px; height: 20px; border-radius: 20px;
      background: var(--border); cursor: pointer; position: relative;
      transition: background .2s; flex-shrink: 0;
    }
    .toggle-switch::after {
      content: ''; position: absolute;
      width: 14px; height: 14px; background: #fff;
      border-radius: 50%; top: 3px; left: 3px;
      transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2);
    }
    .toggle-switch.active { background: var(--brand); }
    .toggle-switch.active::after { transform: translateX(18px); }

    /* ── BUTTONS ────────────────────────��─────── */
    .btn-brand {
      display: inline-flex; align-items: center; gap: 6px;
      background: var(--brand); color: #fff;
      border: none; border-radius: 9px;
      padding: 8px 16px; font-size: 13px; font-weight: 600;
      cursor: pointer; font-family: inherit;
      transition: opacity .15s, transform .1s;
    }
    .btn-brand:hover { opacity: .88; transform: translateY(-1px); }
    .btn-brand.w-100 { width: 100%; justify-content: center; }
    .btn-outline {
      display: inline-flex; align-items: center; gap: 6px;
      background: var(--surface); color: var(--text);
      border: 1px solid var(--border); border-radius: 9px;
      padding: 8px 14px; font-size: 13px; font-weight: 600;
      cursor: pointer; font-family: inherit;
      transition: all .15s;
    }
    .btn-outline:hover { border-color: var(--brand); color: var(--brand); }
    .btn-outline.w-100 { width: 100%; justify-content: center; }
    .btn-sm { padding: 5px 10px !important; font-size: 12px !important; }
    .btn-success-soft {
      display: inline-flex; align-items: center; gap: 6px;
      background: #DCFCE7; color: var(--success); border: 1px solid #BBF7D0;
      border-radius: 9px; padding: 8px 14px; font-size: 13px; font-weight: 600;
      cursor: pointer; font-family: inherit;
    }
    .btn-danger-soft {
      display: inline-flex; align-items: center; gap: 6px;
      background: #FEE2E2; color: var(--danger); border: 1px solid #FECACA;
      border-radius: 9px; padding: 8px 14px; font-size: 13px; font-weight: 600;
      cursor: pointer; font-family: inherit;
    }
    .btn-danger-soft.w-100 { width: 100%; justify-content: center; }

    /* ── TOAST ────────────────────────────────── */
    #toast {
      position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(80px);
      background: #1E293B; color: #fff;
      padding: 12px 20px; border-radius: 10px;
      font-size: 13px; font-weight: 600;
      display: flex; align-items: center; gap: 8px;
      box-shadow: 0 8px 24px rgba(0,0,0,.2);
      transition: transform .3s ease;
      z-index: 9999; white-space: nowrap;
    }
    #toast.show { transform: translateX(-50%) translateY(0); }

    /* ── MOBILE ───────────────────────────────── */
    #mob-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,.4); z-index: 190;
    }
    @media (max-width: 576px) {
      .sidebar { transform: translateX(-100%); }
      .sidebar.open { transform: translateX(0); }
      .main-content { margin-left: 0; }
      #mob-overlay.show { display: block; }
      .topbar-search { display: none; }
      #menu-toggle { display: grid !important; }
    }
    @media (min-width: 769px) {
      #menu-toggle { display: none !important; }
    }

    /* Document upload section */
    .doc-upload-area {
      border: 2px dashed var(--border);
      border-radius: var(--radius-sm);
      padding: 20px;
      text-align: center;
      cursor: pointer;
      transition: all .15s;
      background: var(--surface2);
    }
    .doc-upload-area:hover {
      border-color: var(--brand);
      background: rgba(26, 86, 219, 0.02);
    }
    .doc-upload-area i { font-size: 32px; color: var(--brand); margin-bottom: 10px; }

    /* Map container for location */
    #company-map {
      height: 350px;
      border-radius: var(--radius);
      border: 1px solid var(--border);
    }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<nav id="sidebar" class="sidebar">
  <div class="sidebar-brand">
    <img src="{{ asset('img/logo_unscollab2.png') }}" alt="UNSCollab Logo" style="width: 180px;">
  </div>

  <div class="nav-label">Menu Utama</div>
  <a class="nav-link-item active" onclick="showPage('dashboard', this)">
    <i class="bi bi-grid-1x2"></i> Dashboard
  </a>
  <a class="nav-link-item" onclick="showPage('lowongan', this)">
    <i class="bi bi-briefcase"></i> Kelola Lowongan
    <span class="nav-badge pill-blue" id="lowonganBadge">0</span>
  </a>
  <a class="nav-link-item" onclick="showPage('pelamar', this)">
    <i class="bi bi-people"></i> Kelola Pelamar
    <span class="nav-badge pill-red" id="pelamarBadge">0</span>
  </a>
  <a class="nav-link-item" onclick="showPage('tambah', this)">
    <i class="bi bi-plus-circle"></i> Buat Lowongan
  </a>

  <div class="nav-label" style="margin-top:8px">Pengaturan</div>
  <a class="nav-link-item" onclick="showPage('profil', this); loadProfile()">
      <i class="bi bi-person-circle"></i> Profil Perusahaan
  </a>
  <a class="nav-link-item" onclick="showPage('pengaturan', this)">
    <i class="bi bi-gear"></i> Pengaturan Akun
  </a>

  <div class="sidebar-bottom">
    <a class="nav-link-item" style="color:var(--danger)" onclick="doLogout()">
      <i class="bi bi-box-arrow-right"></i> Keluar
    </a>
  </div>
</nav>

<div id="mob-overlay" onclick="closeSidebar()"></div>

<!-- MAIN -->
<div class="main-content">
  <!-- TOPBAR -->
  <header id="topbar">
    <div style="display:flex;align-items:center;gap:10px">
      <button class="icon-btn" id="menu-toggle" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
      </button>
    </div>
    <div class="topbar-search">
      <i class="bi bi-search" style="font-size:14px"></i>
      <input type="text" placeholder="Cari lowongan, pelamar...">
    </div>
    <div class="topbar-right">
      <div class="icon-btn" onclick="toggleNotif()" id="notif-trigger">
        <i class="bi bi-bell"></i>
        <span class="notif-dot" id="ndot"></span>
      </div>
      <div class="notif-dd" id="notif-dd">
        <div class="notif-header">Notifikasi</div>
        <div id="notif-list"></div>
      </div>
      <div class="avatar-btn" id="topbarAvatar" onclick="showPage('profil', null); loadProfile()" style="cursor:pointer" title="Profil Perusahaan">--</div>
    </div>
  </header>

  <main id="content">

    <!-- ═══════════════ DASHBOARD ═══════════════ -->
    <div class="page active" id="page-dashboard" style="display:block">
      <div class="pg-header">
        <div>
          <h4>Dashboard</h4>
          <p>Selamat datang kembali, <strong id="greetName">Perusahaan</strong> 👋</p>
        </div>
        <button class="btn-brand" onclick="showPage('tambah', null)">
          <i class="bi bi-plus-lg"></i> Buat Lowongan
        </button>
      </div>

      <!-- Empty state untuk perusahaan baru -->
      <div id="empty-state-dashboard" style="display:none;">
        <div class="empty-state mb-4">
          <i class="bi bi-inbox"></i>
          <h5>Belum ada lowongan</h5>
          <p style="margin-bottom:12px;">Mulai buat lowongan pertama Anda untuk menerima aplikasi dari mahasiswa</p>
          <button class="btn-brand" onclick="showPage('tambah', null)">
            <i class="bi bi-plus-lg"></i> Buat Lowongan Pertama
          </button>
        </div>
      </div>

      <!-- Stat cards -->
      <div class="row g-3 mb-4" id="stat-container">
        <div class="col-6 col-md-3">
          <div class="stat-card">
            <div class="stat-icon" style="background:#DBEAFE"><i class="bi bi-briefcase-fill" style="color:#1D4ED8"></i></div>
            <div class="stat-val" style="color:#1D4ED8" id="sv1">0</div>
            <div class="stat-label">Lowongan Aktif</div>
            <div class="stat-trend" style="color:var(--success)"><i class="bi bi-arrow-up-short"></i> <span id="trend1">0</span> bulan ini</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card">
            <div class="stat-icon" style="background:#FEF3C7"><i class="bi bi-people-fill" style="color:#D97706"></i></div>
            <div class="stat-val" id="sv2">0</div>
            <div class="stat-label">Total Pelamar</div>
            <div class="stat-trend" style="color:var(--success)"><i class="bi bi-arrow-up-short"></i> <span id="trend2">0</span> minggu ini</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card">
            <div class="stat-icon" style="background:#FEE2E2"><i class="bi bi-clock-fill" style="color:#A16207"></i></div>
            <div class="stat-val" style="color:#A16207" id="sv3">0</div>
            <div class="stat-label">Menunggu Verifikasi</div>
            <div class="stat-trend" style="color:var(--danger)"><i class="bi bi-exclamation-circle"></i> Perlu tindakan</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card">
            <div class="stat-icon" style="background:#F0FDF4"><i class="bi bi-check-circle-fill" style="color:#16A34A"></i></div>
            <div class="stat-val" style="color:#16A34A" id="sv4">0</div>
            <div class="stat-label">Pelamar Diterima</div>
            <div class="stat-trend" style="color:var(--muted)">Bulan ini</div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="ui-card h-100">
            <div class="card-head">
              <h6><i class="bi bi-bar-chart-fill me-2" style="color:var(--brand)"></i>Pelamar per Posisi</h6>
              <span style="font-size:11px;color:var(--muted)">7 hari terakhir</span>
            </div>
            <div class="bar-group" id="bar-chart">
              <div class="bar-item"><div class="bar-fill" style="height:0;background:var(--brand)"></div><div class="bar-lbl">-</div></div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="ui-card h-100">
            <div class="card-head">
              <h6><i class="bi bi-pie-chart-fill me-2" style="color:#6366F1"></i>Status Pelamar</h6>
            </div>
            <div id="progress-container"></div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-7">
          <div class="ui-card">
            <div class="card-head">
              <h6><i class="bi bi-activity me-2" style="color:#6366F1"></i>Aktivitas Terbaru</h6>
              <a style="font-size:12px;color:var(--brand);cursor:pointer" onclick="showPage('pelamar',null)">Lihat semua</a>
            </div>
            <div id="activity-timeline"></div>
          </div>
        </div>
        <div class="col-md-5">
          <div class="ui-card">
            <div class="card-head"><h6><i class="bi bi-lightning-fill me-2" style="color:#EAB308"></i>Aksi Cepat</h6></div>
            <div class="d-flex flex-column gap-2">
              <button class="btn-brand w-100" onclick="showPage('tambah',null)"><i class="bi bi-plus-circle"></i> Buat Lowongan Baru</button>
              <button class="btn-outline w-100" onclick="showPage('pelamar',null)"><i class="bi bi-people"></i> Lihat Semua Pelamar</button>
              <button class="btn-outline w-100" onclick="showPage('lowongan',null)"><i class="bi bi-briefcase"></i> Kelola Lowongan</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════ LOWONGAN ═══════════════ -->
    <div class="page" id="page-lowongan" style="display:none">
      <div class="pg-header">
        <div>
          <h4>Kelola Lowongan</h4>
          <p>Semua lowongan yang kamu buat</p>
        </div>
        <button class="btn-brand" onclick="showPage('tambah',null)"><i class="bi bi-plus-lg"></i> Buat Lowongan</button>
      </div>

      <!-- pending info banner -->
      <div class="pending-banner" id="pending-banner" style="display:none;">
        <i class="bi bi-info-circle-fill" style="color:#D97706;font-size:15px;flex-shrink:0"></i>
        <span><strong id="pending-count">0</strong> lowongan sedang menunggu verifikasi admin. Lowongan baru hanya bisa dilihat mahasiswa setelah disetujui.</span>
      </div>

      <!-- Empty state -->
      <div id="empty-lowongan" class="empty-state" style="display:none;">
        <i class="bi bi-briefcase"></i>
        <h5>Belum ada lowongan</h5>
        <p>Mulai dengan membuat lowongan pertama Anda</p>
        <button class="btn-brand mt-3" onclick="showPage('tambah', null)"><i class="bi bi-plus-lg"></i> Buat Lowongan</button>
      </div>

      <ul class="nav nav-pills mb-3" style="gap:6px" id="lw-filter-tabs">
        <li class="nav-item"><a class="nav-pill-tab active" onclick="filterLw('all',this)" data-filter="all">Semua (<span id="count-all">0</span>)</a></li>
        <li class="nav-item"><a class="nav-pill-tab" onclick="filterLw('active',this)" data-filter="active">Aktif (<span id="count-aktif">0</span>)</a></li>
        <li class="nav-item"><a class="nav-pill-tab" onclick="filterLw('pending',this)" data-filter="pending">Pending (<span id="count-pending">0</span>)</a></li>
        <li class="nav-item"><a class="nav-pill-tab" onclick="filterLw('closed',this)" data-filter="closed">Ditutup (<span id="count-ditutup">0</span>)</a></li>
      </ul>

      <div class="row g-3" id="lw-grid">
        <!-- lowongan akan di-generate oleh JavaScript -->
      </div>
    </div>

    <!-- ═══════════════ PELAMAR ═══════════════ -->
    <div class="page" id="page-pelamar" style="display:none">
      <div class="pg-header">
        <div>
          <h4>Kelola Pelamar</h4>
          <p id="pelamar-subtitle">Semua posisi — <strong id="total-pelamar">0</strong> pelamar</p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
          <select class="form-select" style="width:auto;font-size:13px" id="filter-posisi">
            <option value="">Semua Posisi</option>
          </select>
          <select class="form-select" style="width:auto;font-size:13px" id="filter-status">
            <option value="">Semua Status</option>
            <option value="pending">Menunggu</option>
            <option value="review">Sedang Diproses</option>
            <option value="accepted">Diterima</option>
            <option value="rejected">Ditolak</option>
          </select>
          <input type="text" class="form-control" placeholder="Cari nama..." style="width:160px;font-size:13px" id="search-pelamar">
        </div>
      </div>

      <!-- Empty state -->
      <div id="empty-pelamar" class="empty-state" style="display:none;">
        <i class="bi bi-inbox"></i>
        <h5>Belum ada pelamar</h5>
        <p>Pelamar akan muncul di sini setelah membuat lowongan</p>
      </div>

      <div class="ui-card p-0 overflow-hidden" id="pelamar-table-container">
        <div class="table-responsive">
          <table class="ui-table" id="pelamar-table">
            <thead>
              <tr>
                <th>Nama Pelamar</th>
                <th>Posisi Dilamar</th>
                <th>Jurusan</th>
                <th>Tanggal Lamar</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="pelamar-tbody">
              <!-- pelamar akan di-generate oleh JavaScript -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ═══════════════ DETAIL PELAMAR ═══════════════ -->
    <div class="page" id="page-detail" style="display:none">
      <input type="hidden" id="det-id-student">
      <input type="hidden" id="det-id-internship">
      <div class="pg-header" style="margin-bottom:16px">
        <button class="btn-outline" onclick="showPage('pelamar',null)"><i class="bi bi-arrow-left"></i> Kembali ke Daftar Pelamar</button>
      </div>

      <div class="profile-hero mb-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <div class="big-avatar" id="det-avatar">--</div>
          <div class="flex-grow-1">
            <div style="font-size:20px;font-weight:700" id="det-name">-</div>
            <div style="font-size:13px;opacity:.8;margin-top:3px">Melamar: <span id="det-posisi">-</span> &nbsp;·&nbsp; <span id="det-date">-</span></div>
            <div style="margin-top:10px" id="det-status-wrap"></div>
          </div>
          <div class="d-flex gap-2 flex-wrap" id="det-aksi-wrap">
            {{-- diisi dinamis oleh showDetailPelamar() berdasarkan status --}}
          </div>
        </div>
      </div>

      <div class="ui-card mb-3">
        <div class="card-head"><h6>Alur Seleksi</h6></div>
        <div class="d-flex" style="padding:8px 0">
          <div class="flow-step"><div class="flow-dot done"><i class="bi bi-check-lg"></i></div><div class="flow-label">Lamar</div></div>
          <div class="flow-step"><div class="flow-dot current">2</div><div class="flow-label">Review CV</div></div>
          <div class="flow-step"><div class="flow-dot">3</div><div class="flow-label">Wawancara</div></div>
          <div class="flow-step"><div class="flow-dot" style="border-color:var(--border)">4</div><div class="flow-label">Keputusan</div></div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <div class="ui-card h-100">
            <div class="card-head"><h6><i class="bi bi-person-fill me-2" style="color:var(--brand)"></i>Data Pribadi</h6></div>
            <div class="row g-2">
              <div class="col-6"><div class="info-box"><div class="info-key">Universitas</div><div class="info-val">Universitas Sebelas Maret</div></div></div>
              <div class="col-6"><div class="info-box"><div class="info-key">Jurusan</div><div class="info-val" id="det-jurusan">-</div></div></div>
              <div class="col-6"><div class="info-box"><div class="info-key">Angkatan</div><div class="info-val" id="det-angkatan">-</div></div></div>
              <div class="col-6"><div class="info-box"><div class="info-key">Email</div><div class="info-val" style="font-size:12px;word-break:break-all" id="det-email">-</div></div></div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="ui-card mb-3">
            <div class="card-head"><h6><i class="bi bi-lightning-fill me-2" style="color:#D97706"></i>Keahlian</h6></div>
            <div id="det-skills" style="font-size:13px;color:var(--muted)">-</div>
          </div>
          <div class="ui-card mb-3">
            <div class="card-head"><h6><i class="bi bi-briefcase me-2" style="color:#6366F1"></i>Pengalaman</h6></div>
            <div id="det-experience" style="font-size:13px;color:var(--muted)">-</div>
          </div>
          <div class="ui-card">
            <div class="card-head"><h6><i class="bi bi-file-earmark-text me-2" style="color:#6366F1"></i>Dokumen</h6></div>
            <div id="det-documents">-</div>
          </div>
        </div>
      </div>

      <!-- Bio -->
      <div class="ui-card mb-3">
        <div class="card-head"><h6><i class="bi bi-person-lines-fill me-2" style="color:var(--brand)"></i>Bio</h6></div>
        <p style="font-size:13.5px;color:var(--muted);line-height:1.8;margin:0" id="det-bio">-</p>
      </div>

      <!-- Portofolio -->
      <div class="ui-card mb-3">
        <div class="card-head"><h6><i class="bi bi-folder2-open me-2" style="color:#D97706"></i>Portofolio</h6></div>
        <div id="det-portofolio" style="font-size:13px;color:var(--muted)">-</div>
      </div>

      <div class="ui-card">
        <div class="card-head"><h6><i class="bi bi-envelope-open me-2" style="color:var(--brand)"></i>Surat Lamaran</h6></div>
        <p style="font-size:13.5px;color:var(--muted);line-height:1.8;margin:0" id="det-surat">-</p>
      </div>
    </div>

    <!-- ═══════════════ BUAT LOWONGAN ═══════════════ -->
    <div class="page" id="page-tambah" style="display:none">
      <div class="pg-header">
        <div>
          <h4>Buat Lowongan Baru</h4>
          <p>Lowongan akan diverifikasi admin sebelum tampil ke mahasiswa</p>
        </div>
      </div>

      <div class="ui-card mb-3">
        <div class="d-flex align-items-center gap-3 flex-wrap" style="font-size:12px">
          <div class="d-flex align-items-center gap-2"><div class="step-num active-step">1</div><span style="font-weight:600">Isi Formulir</span></div>
          <i class="bi bi-chevron-right" style="color:var(--muted)"></i>
          <div class="d-flex align-items-center gap-2"><div class="step-num">2</div><span style="color:var(--muted)">Review Admin</span></div>
          <i class="bi bi-chevron-right" style="color:var(--muted)"></i>
          <div class="d-flex align-items-center gap-2"><div class="step-num">3</div><span style="color:var(--muted)">Tayang ke Mahasiswa</span></div>
        </div>
        <div style="margin-top:12px;padding:10px 12px;background:#EEF3FF;border-radius:var(--radius-sm);font-size:12px;color:var(--brand);display:flex;gap:8px;align-items:flex-start">
          <i class="bi bi-info-circle-fill" style="flex-shrink:0;margin-top:1px"></i>
          <span>Setelah kamu kirim, lowongan ini masuk ke antrian review admin. Proses verifikasi biasanya memakan waktu <strong>1×24 jam</strong>.</span>
        </div>
      </div>

      <form id="form-lowongan">
        <div class="ui-card mb-3">
          <div class="card-head"><h6>Informasi Dasar</h6></div>
            <div class="row g-3">
              <!-- Baris 1: Judul Posisi full width -->
              <div class="col-12">
                <label class="form-label">Judul Posisi <span style="color:#DC2626">*</span></label>
                <input type="text" class="form-control" name="title" placeholder="cth. Backend Developer Intern" required>
              </div>

              <!-- Baris 2: Tipe Pekerjaan | Tipe Pembayaran | Lokasi -->
              <div class="col-md-4">
                <label class="form-label">Tipe Pekerjaan</label>
                <select class="form-select" name="work_mode" required>
                  <option value="">Pilih Tipe</option>
                  <option value="onsite">Onsite</option>
                  <option value="hybrid">Hybrid</option>
                  <option value="remote">Remote</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Tipe Pembayaran</label>
                <select class="form-select" name="payment_status" required>
                  <option value="">Pilih Tipe</option>
                  <option value="paid">Paid</option>
                  <option value="unpaid">Unpaid</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Lokasi</label>
                <select class="form-select" name="location" required>
                  <option value="">Pilih Provinsi</option>
                  <option>Aceh</option><option>Sumatera Utara</option><option>Sumatera Barat</option>
                  <option>Riau</option><option>Kepulauan Riau</option><option>Jambi</option>
                  <option>Sumatera Selatan</option><option>Kepulauan Bangka Belitung</option><option>Bengkulu</option>
                  <option>Lampung</option><option>DKI Jakarta</option><option>Jawa Barat</option>
                  <option>Jawa Tengah</option><option>DI Yogyakarta</option><option>Jawa Timur</option>
                  <option>Banten</option><option>Bali</option><option>Nusa Tenggara Barat</option>
                  <option>Nusa Tenggara Timur</option><option>Kalimantan Barat</option><option>Kalimantan Tengah</option>
                  <option>Kalimantan Selatan</option><option>Kalimantan Timur</option><option>Kalimantan Utara</option>
                  <option>Sulawesi Utara</option><option>Gorontalo</option><option>Sulawesi Tengah</option>
                  <option>Sulawesi Barat</option><option>Sulawesi Selatan</option><option>Sulawesi Tenggara</option>
                  <option>Maluku</option><option>Maluku Utara</option><option>Papua</option>
                  <option>Papua Barat</option><option>Papua Selatan</option><option>Papua Tengah</option>
                  <option>Papua Pegunungan</option><option>Papua Barat Daya</option>
                </select>
              </div>

              <!-- Baris 3: Kuota | Durasi | Deadline -->
              <div class="col-md-4">
                <label class="form-label">Kuota <span style="color:#DC2626">*</span></label>
                <input type="number" class="form-control" name="quota" placeholder="cth. 5" min="1" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Durasi <span style="color:#DC2626">*</span></label>
                <input type="text" class="form-control" name="duration" placeholder="cth. 3 bulan" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Batas Lamaran</label>
                <input type="date" class="form-control" name="deadline">
              </div>

              <!-- Baris 4-6: full width -->
              <div class="col-12">
                <label class="form-label">Deskripsi Pekerjaan <span style="color:#DC2626">*</span></label>
                <textarea class="form-control" name="description" rows="4" placeholder="Jelaskan tanggung jawab dan tugas utama posisi ini..." required></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Kualifikasi <span style="color:#DC2626">*</span></label>
                <textarea class="form-control" name="requirements" rows="4" placeholder="Persyaratan pendidikan, keahlian, dan pengalaman yang dibutuhkan..." required></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Benefit <span style="color:#DC2626">*</span></label>
                <textarea class="form-control" name="benefits" rows="4" placeholder="Benefit yang akan didapatkan..." required></textarea>
              </div>
            </div>
        </div>

        <!-- Banner Lowongan -->
        <div class="ui-card mb-3">
          <div class="card-head"><h6><i class="bi bi-image me-2" style="color:var(--brand)"></i>Banner Lowongan <span style="font-size:11px;font-weight:400;color:var(--muted)">(Opsional)</span></h6></div>
          <p style="font-size:12px;color:var(--muted);margin-bottom:14px">Upload gambar banner untuk lowongan ini. Format: JPG, PNG, WebP. Maks 2MB.</p>
          <div class="doc-upload-area" onclick="document.getElementById('image-input').click()" id="image-upload-area">
            <i class="bi bi-image" style="font-size:32px;color:var(--brand)"></i>
            <p style="margin:8px 0 0 0;font-size:12px">Klik untuk pilih gambar banner</p>
            <p style="font-size:11px;color:var(--muted);margin:4px 0 0 0">JPG, PNG, WebP — Maks 2MB</p>
            <input type="file" id="image-input" name="image" style="display:none" accept=".jpg,.jpeg,.png,.webp" onchange="handleImagePreview(event)">
          </div>
          <div id="image-preview-wrap" style="display:none;margin-top:12px">
            <img id="image-preview" style="max-width:100%;border-radius:var(--radius);border:1px solid var(--border)" src="">
            <button type="button" class="btn-danger-soft mt-2" onclick="clearImagePreview()"><i class="bi bi-x"></i> Hapus Gambar</button>
          </div>
        </div>

        <!-- Dokumen Pendukung -->
        <div class="ui-card mb-3">
          <div class="card-head"><h6><i class="bi bi-file-earmark-pdf me-2"></i>Dokumen Pendukung <span style="font-size:11px;font-weight:400;color:var(--muted)">(Opsional)</span></h6></div>
          <p style="font-size:12px;color:var(--muted);margin-bottom:14px">Upload dokumen seperti MoU, Proposal, atau Surat Kerjasama. Format: PDF, DOC, DOCX. Maks 10MB.</p>
          <div class="doc-upload-area" onclick="document.getElementById('doc-input').click()">
            <i class="bi bi-cloud-arrow-up"></i>
            <p style="margin:8px 0 0 0;font-size:12px">Klik untuk pilih dokumen</p>
            <p style="font-size:11px;color:var(--muted);margin:4px 0 0 0">PDF, DOC, DOCX — Maks 10MB</p>
            <input type="file" id="doc-input" name="supporting_document" style="display:none" accept=".pdf,.doc,.docx" onchange="handleDocumentUpload(event)">
          </div>
          <div id="doc-list" style="margin-top:16px"></div>
        </div>
      </form>

      <div class="d-flex justify-content-end gap-2">
        <button class="btn-outline" onclick="showPage('lowongan',null)">Batal</button>
        <button class="btn-brand" onclick="handleSubmitLowongan()"><i class="bi bi-send"></i> Kirim untuk Review Admin</button>
      </div>
    </div>

    <!-- ═══════════════ PROFIL ═══════════════ -->
<div class="page" id="page-profil" style="display:none">
  <div class="pg-header">
    <div>
      <h4>Profil Perusahaan</h4>
      <p>Kelola informasi publik perusahaan Anda</p>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-8">
      <div class="ui-card mb-3">
        <div class="card-head"><h6><i class="bi bi-info-circle-fill me-2" style="color:var(--brand)"></i>Informasi Umum</h6></div>
        <form id="form-profile" onsubmit="saveProfile(event)">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label font-weight-600">Nama Perusahaan</label>
              <input type="text" class="form-control" id="p-name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label font-weight-600">Industri / Sektor</label>
              <input type="text" class="form-control" id="p-industry" placeholder="Contoh: Teknologi, Finansial">
            </div>
            <div class="col-12">
              <label class="form-label font-weight-600">Deskripsi Singkat</label>
              <textarea class="form-control" id="p-desc" rows="3" placeholder="Gambarkan perusahaan Anda..."></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label font-weight-600">Kontak / No. Telepon Perusahaan</label>
              <input type="text" class="form-control" id="p-contact" placeholder="Contoh: 08123456xxx">
            </div>
            <div class="col-md-6">
              <label class="form-label font-weight-600">Email Akun (Read-only)</label>
              <input type="email" class="form-control" id="p-email" readonly style="background-color: #f1f3f5; cursor: not-allowed;">
            </div>
          </div>
          <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn-brand">
              <i class="bi bi-save me-1"></i> Simpan Profil
            </button>
          </div>
        </form>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="ui-card">
        <div class="card-head"><h6>Logos & Media</h6></div>
        <div class="text-center p-3">
          <div class="big-avatar mx-auto mb-3" style="width:80px; height:80px; font-size:28px; position:relative; overflow:hidden; display:grid; place-items:center;">
            <span id="logo-placeholder">--</span>
            <img id="logo-img" src="" alt="Logo" style="display:none; width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0;">
          </div>
          <button type="button" class="btn-outline btn-sm w-100" onclick="document.getElementById('p-avatar').click()">
            <i class="bi bi-camera me-1"></i> Ubah Logo
          </button>
          <input type="file" id="p-avatar" accept="image/*" style="display:none" onchange="openCropModal(event)">
        </div>
      </div>
    </div>
  </div>
</div>
          <!-- ═══════════════ PENGATURAN ═══════════════ -->
    <div class="page" id="page-pengaturan" style="display:none">
      <div class="pg-header">
        <div>
          <h4>Pengaturan Akun</h4>
          <p>Kelola keamanan, preferensi, dan data akun perusahaan Anda</p>
        </div>
        <button class="btn-brand" onclick="handleSaveSettings()"><i class="bi bi-check-lg"></i> Simpan Pengaturan</button>
      </div>
      <div class="row g-3">
        <div class="col-lg-9">
          <!-- Account Information -->
          <div class="ui-card mb-3">
            <div class="card-head"><h6><i class="bi bi-person-circle me-2" style="color:var(--brand)"></i>Informasi Akun</h6></div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nama Pengguna (Admin)</label>
                <input type="text" class="form-control" id="set-username" placeholder="Nama admin perusahaan">
              </div>
              <div class="col-md-6">
                <label class="form-label">Email Akun <span style="color:#DC2626">*</span></label>
                <input type="email" class="form-control" id="set-email" readonly style="background:var(--surface2);cursor:not-allowed">
              </div>
              <div class="col-md-6">
                <label class="form-label">No. Telepon</label>
                <input type="tel" class="form-control" id="set-phone" placeholder="+62-8xx-xxxx-xxxx">
              </div>
              <div class="col-md-6">
                <label class="form-label">Zona Waktu</label>
                <select class="form-select">
                  <option>WIB (UTC+7)</option>
                  <option>WITA (UTC+8)</option>
                  <option>WIT (UTC+9)</option>
                </select>
              </div>
            </div>
            <small style="color:var(--muted);display:block;margin-top:12px">
              <i class="bi bi-info-circle"></i> Email tidak dapat diubah. Hubungi admin untuk mengubah email akun.
            </small>
          </div>

          <!-- Security -->
          <div class="ui-card mb-3">
            <div class="card-head"><h6><i class="bi bi-shield-lock me-2" style="color:#6366F1"></i>Keamanan Akun</h6></div>
            <form id="form-password" onsubmit="handleSavePassword(event)">
            {{-- Hidden username field for browser password manager accessibility --}}
            <input type="text" id="form-password-username" autocomplete="username"
                   style="display:none" aria-hidden="true"
                   value="{{ session('user_email') }}">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Password Saat Ini</label>
                <div class="input-group mb-2">
                  <input type="password" class="form-control" id="set-current-pass" placeholder="Masukkan password lama" autocomplete="current-password">
                  <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('set-current-pass')">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </div>
              <div class="col-md-6"></div>
              <div class="col-md-6">
                <label class="form-label">Password Baru</label>
                <div class="input-group mb-2">
                  <input type="password" class="form-control" id="set-new-pass" placeholder="Minimal 8 karakter" autocomplete="new-password">
                  <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('set-new-pass')">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Konfirmasi Password Baru</label>
                <div class="input-group mb-2">
                  <input type="password" class="form-control" id="set-confirm-pass" placeholder="Ulangi password baru" autocomplete="new-password">
                  <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('set-confirm-pass')">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </div>
            </div>
            <small style="color:var(--muted);display:block;margin-top:12px">
              <i class="bi bi-info-circle"></i> Password harus minimal 8 karakter, kombinasi huruf besar, kecil, angka, dan simbol.
            </small>
            <div class="d-flex justify-content-end mt-3">
              <button type="submit" class="btn-brand"><i class="bi bi-shield-check me-1"></i> Ganti Password</button>
            </div>
            </form>
          </div>

          <!-- Notifications -->
          <div class="ui-card mb-3">
            <div class="card-head"><h6><i class="bi bi-bell me-2" style="color:#D97706"></i>Preferensi Notifikasi</h6></div>
            <div style="display:flex;flex-direction:column;gap:16px">
              <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:16px;border-bottom:1px solid var(--border)">
                <div>
                  <div style="font-size:13px;font-weight:500">Pelamar Baru</div>
                  <div style="font-size:12px;color:var(--muted);margin-top:2px">Notifikasi ketika ada mahasiswa melamar posisi</div>
                </div>
                <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
              </div>
              <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:16px;border-bottom:1px solid var(--border)">
                <div>
                  <div style="font-size:13px;font-weight:500">Status Lowongan Diperbarui</div>
                  <div style="font-size:12px;color:var(--muted);margin-top:2px">Notifikasi saat lowongan disetujui atau ditolak admin</div>
                </div>
                <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
              </div>
              <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:16px;border-bottom:1px solid var(--border)">
                <div>
                  <div style="font-size:13px;font-weight:500">Pengingat Batas Lamaran</div>
                  <div style="font-size:12px;color:var(--muted);margin-top:2px">Ingatkan 3 hari sebelum lowongan ditutup</div>
                </div>
                <div class="toggle-switch" onclick="toggleSwitch(this)"></div>
              </div>
              <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:16px;border-bottom:1px solid var(--border)">
                <div>
                  <div style="font-size:13px;font-weight:500">Laporan Mingguan</div>
                  <div style="font-size:12px;color:var(--muted);margin-top:2px">Ringkasan performa lowongan setiap Senin</div>
                </div>
                <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
              </div>
              <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                  <div style="font-size:13px;font-weight:500">Update Fitur & Promo</div>
                  <div style="font-size:12px;color:var(--muted);margin-top:2px">Info tentang fitur baru dan penawaran menarik</div>
                </div>
                <div class="toggle-switch" onclick="toggleSwitch(this)"></div>
              </div>
            </div>
          </div>

          <!-- Privacy & Data -->
          <div class="ui-card">
            <div class="card-head"><h6><i class="bi bi-shield-check me-2" style="color:#16A34A"></i>Privasi & Data</h6></div>
            <div style="display:flex;flex-direction:column;gap:14px;font-size:12px">
              <div style="padding:12px;background:var(--surface2);border-radius:var(--radius-sm)">
                <div style="font-weight:600;margin-bottom:4px"><i class="bi bi-check-circle me-2" style="color:var(--success)"></i>Data Perusahaan Tersimpan Aman</div>
                <div style="color:var(--muted)">Informasi perusahaan Anda dienkripsi dan disimpan dengan standar keamanan internasional.</div>
              </div>
              <div style="padding:12px;background:var(--surface2);border-radius:var(--radius-sm)">
                <div style="font-weight:600;margin-bottom:4px"><i class="bi bi-file-earmark-text me-2" style="color:var(--brand)"></i>Kebijakan Privasi</div>
                <div style="color:var(--muted)">Baca <a href="#" style="color:var(--brand);text-decoration:none">kebijakan privasi kami</a> untuk informasi lengkap tentang bagaimana kami mengelola data Anda.</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3">
          <!-- Activity Log -->
          <div class="ui-card mb-3">
            <div class="card-head"><h6><i class="bi bi-clock-history me-2"></i>Aktivitas Terakhir</h6></div>
            <div id="activity-log-container" style="font-size:12px;color:var(--muted)">
              <p style="font-size:12px;color:var(--muted)">Memuat aktivitas...</p>
            </div>
          </div>

          <!-- Danger Zone -->
          <div class="ui-card" style="border-color:#FECACA">
            <div class="card-head"><h6><i class="bi bi-exclamation-triangle-fill me-2" style="color:#DC2626"></i>Zona Berbahaya</h6></div>
            <p style="font-size:12px;color:var(--muted);margin-bottom:12px">Tindakan berikut bersifat permanen dan tidak dapat dibatalkan.</p>
            <div class="d-flex flex-column gap-2">
              <button class="btn-danger-soft w-100" onclick="showToast('⚠ Fitur ini memerlukan konfirmasi lebih lanjut.')">
                <i class="bi bi-trash3"></i> Hapus Semua Data Lowongan
              </button>
              <button class="btn-danger-soft w-100" onclick="showToast('⚠ Hubungi admin untuk menonaktifkan akun')">
                <i class="bi bi-x-octagon"></i> Nonaktifkan Akun
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Modal Edit Lowongan -->
<div class="modal fade" id="editLowonganModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Lowongan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit-id-internship">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Judul Posisi <span style="color:#DC2626">*</span></label>
            <input type="text" class="form-control" id="edit-title" placeholder="cth. Backend Developer Intern">
          </div>
          <div class="col-md-4">
            <label class="form-label">Tipe Pekerjaan</label>
            <select class="form-select" id="edit-work-mode">
              <option value="onsite">Onsite</option>
              <option value="hybrid">Hybrid</option>
              <option value="remote">Remote</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Tipe Pembayaran</label>
            <select class="form-select" id="edit-payment">
              <option value="paid">Paid</option>
              <option value="unpaid">Unpaid</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Lokasi</label>
            <input type="text" class="form-control" id="edit-location" placeholder="Kota/Provinsi">
          </div>
          <div class="col-md-4">
            <label class="form-label">Kuota</label>
            <input type="number" class="form-control" id="edit-quota" min="1">
          </div>
          <div class="col-md-4">
            <label class="form-label">Durasi</label>
            <input type="text" class="form-control" id="edit-duration" placeholder="cth. 3 bulan">
          </div>
          <div class="col-md-4">
            <label class="form-label">Deadline</label>
            <input type="date" class="form-control" id="edit-deadline">
          </div>
          <div class="col-12">
            <label class="form-label">Deskripsi <span style="color:#DC2626">*</span></label>
            <textarea class="form-control" id="edit-description" rows="3"></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Kualifikasi <span style="color:#DC2626">*</span></label>
            <textarea class="form-control" id="edit-requirement" rows="3"></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Benefit</label>
            <textarea class="form-control" id="edit-benefit" rows="3"></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Ganti Banner <span style="font-size:11px;color:var(--muted)">(Opsional)</span></label>
            <input type="file" class="form-control" id="edit-image" accept=".jpg,.jpeg,.png,.webp">
          </div>
          <div class="col-12">
            <label class="form-label">Ganti Dokumen Pendukung <span style="font-size:11px;color:var(--muted)">(Opsional)</span></label>
            <div style="font-size:12px;color:var(--muted);margin-bottom:6px" id="edit-doc-existing"></div>
            <input type="file" class="form-control" id="edit-doc" accept=".pdf,.doc,.docx">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="edit-submit-btn" onclick="handleEditLowongan()">
          <i class="bi bi-check-lg"></i> Simpan Perubahan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Document Upload Modal -->
<div class="modal fade" id="documentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload Dokumen Perusahaan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Jenis Dokumen <span style="color:#DC2626">*</span></label>
            <select class="form-select" id="doc-type">
              <option value="">-- Pilih Jenis --</option>
              <option value="mou">MoU (Memorandum of Understanding)</option>
              <option value="proposal">Proposal Kerja Sama</option>
              <option value="agreement">Surat Kerjasama</option>
              <option value="other">Dokumen Lainnya</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">File Dokumen <span style="color:#DC2626">*</span></label>
            <div class="doc-upload-area" onclick="document.getElementById('modal-doc-input').click()">
              <i class="bi bi-cloud-arrow-up"></i>
              <p style="margin:8px 0 0 0;font-size:12px" id="upload-status">Klik atau drag file ke sini</p>
              <p style="font-size:11px;color:var(--muted);margin:4px 0 0 0">Format: PDF, DOC, DOCX (Maks 10MB)</p>
              <input type="file" id="modal-doc-input" style="display:none" accept=".pdf,.doc,.docx" onchange="handleModalDocumentUpload(event)">
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" onclick="submitDocument()">Upload</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast Notification -->
<div id="toast">
  <span id="toast-msg"></span>
</div>

<script>
    // ── Inject session data SEBELUM dashboard.js load ──
    window.userData = {
        name: "{{ session('user_name') }}",
        email: "{{ session('user_email') }}",
        type: "{{ session('user_type') }}",
        id: "{{ session('user_id') }}",
        type_id: "{{ session('type_id') }}"
    };
    // Pastikan tersedia global
    window._userData = window.userData;

    // ── showPage: handle navigasi sidebar ──
    window.showPage = function(page, el) {
        document.querySelectorAll('.page').forEach(section => {
            section.style.display = 'none';
            section.classList.remove('active');
        });
        const pageElement = document.getElementById('page-' + page);
        if (pageElement) {
            pageElement.style.display = 'block';
            pageElement.classList.add('active');
        }
        document.querySelectorAll('.nav-link-item').forEach(link => {
            link.classList.remove('active');
        });
        if (el) el.classList.add('active');

        if (page === 'profil')      loadProfile();
        if (page === 'pengaturan')  { loadSettings(); loadActivities(); }
    }

    // alias lokal tetap jalan untuk onclick di blade
    function showPage(page, el) { window.showPage(page, el); }

    // ── toggleNotif: buka/tutup dropdown notifikasi ──
    window.toggleNotif = function() {
        const notifDd = document.getElementById('notif-dd');
        if (notifDd) notifDd.classList.toggle('open');
    }
    function toggleNotif() { window.toggleNotif(); }

    // Tutup notif kalau klik di luar
    document.addEventListener('click', function(e) {
        const trigger = document.getElementById('notif-trigger');
        const dd = document.getElementById('notif-dd');
        if (dd && trigger && !trigger.contains(e.target) && !dd.contains(e.target)) {
            dd.classList.remove('open');
        }
    });

    // ── doLogout ──
    function doLogout() {
        logout();
    }

    // ── toggleSidebar mobile ──
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mob-overlay');
        const toggleSidebar = document.getElementById('menu-toggle');
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
        toggleSidebar.classList.toggle('open');
    }

    function closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mob-overlay');
        const toggleSidebar = document.getElementById('menu-toggle');
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        toggleSidebar.classList.remove('open');
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>

</body>
</html>