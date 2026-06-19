<?php
require_once 'api/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$uid = (int)$_SESSION['user_id'];
$res = $conn->query("SELECT * FROM users WHERE id=$uid");
$user = $res->fetch_assoc();

if (!$user) {
    session_destroy();
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Account | PetPew</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --green-deep:  #2d6a4f;
      --green-mid:   #40916c;
      --green-main:  #52b788;
      --green-light: #b7e4c7;
      --green-pale:  #d8f3dc;
      --gold:        #f4a522;
      --gold-dark:   #d4880a;
      --cream:       #faf7f2;
      --charcoal:    #1e2a23;
      --text-body:   #3d4f44;
      --text-muted:  #748a7c;
      --white:       #ffffff;
      --danger:      #e63946;
      --shadow-sm:   0 2px 12px rgba(40,100,60,0.08);
      --shadow-md:   0 8px 32px rgba(40,100,60,0.13);
      --shadow-lg:   0 20px 60px rgba(40,100,60,0.18);
      --radius-sm:   8px;
      --radius-md:   16px;
      --radius-lg:   28px;
      --radius-pill: 100px;
      --transition:  all 0.35s cubic-bezier(0.4,0,0.2,1);
    }

    *,*::before,*::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--cream);
      color: var(--text-body);
      line-height: 1.7;
      min-height: 100vh;
    }

    a { text-decoration: none; color: inherit; }

    /* ── Announcement Bar ── */
    .announcement-bar {
      background: var(--green-deep);
      color: var(--green-pale);
      text-align: center;
      padding: 9px 16px;
      font-size: 0.82rem;
      font-weight: 500;
      letter-spacing: 0.04em;
    }
    .announcement-bar span { color: var(--gold); font-weight: 700; }

    /* ── Navbar ── */
    .navbar {
      background: var(--white);
      position: sticky;
      top: 0; z-index: 1000;
      border-bottom: 1px solid var(--green-pale);
      box-shadow: var(--shadow-sm);
    }

    .navbar-inner {
      max-width: 1200px; margin: 0 auto;
      padding: 0 24px;
      display: flex; justify-content: space-between; align-items: center;
      height: 72px; gap: 16px;
    }

    .navbar-brand {
      display: flex; align-items: center; gap: 10px;
      font-family: 'Playfair Display', serif;
      font-size: 1.9rem; font-weight: 900;
      color: var(--green-deep); letter-spacing: -0.02em;
      flex-shrink: 0;
    }

    .logo-icon {
      width: 40px; height: 40px;
      background: linear-gradient(135deg, var(--green-main), var(--green-deep));
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.2rem; color: var(--white);
      box-shadow: 0 4px 12px rgba(82,183,136,0.35);
    }

    .brand-dot { color: var(--gold); }

    .nav-actions { display: flex; align-items: center; gap: 8px; }

    .nav-btn {
      display: inline-flex; align-items: center; gap: 7px;
      padding: 8px 16px; border-radius: var(--radius-pill);
      font-size: 0.88rem; font-weight: 500;
      transition: var(--transition); cursor: pointer;
      border: none; font-family: 'DM Sans', sans-serif;
    }

    .nav-btn-ghost {
      background: transparent; color: var(--text-body);
      border: 1.5px solid var(--green-light);
    }
    .nav-btn-ghost:hover { background: var(--green-pale); color: var(--green-deep); }

    .nav-btn-primary {
      background: var(--green-deep); color: var(--white);
      box-shadow: 0 4px 14px rgba(45,106,79,0.28);
    }
    .nav-btn-primary:hover { background: var(--green-mid); transform: translateY(-1px); }

    .nav-btn-danger {
      background: transparent; color: var(--danger);
      border: 1.5px solid rgba(230,57,70,0.3);
    }
    .nav-btn-danger:hover { background: #fef2f2; border-color: var(--danger); }

    /* ── Page Header ── */
    .page-header {
      background: linear-gradient(135deg, var(--green-deep) 0%, var(--green-mid) 60%, var(--green-main) 100%);
      padding: 48px 24px 40px;
      position: relative; overflow: hidden;
    }

    .page-header::before {
      content: ''; position: absolute; inset: 0;
      background:
        radial-gradient(ellipse 55% 65% at 85% 50%, rgba(255,255,255,0.05) 0%, transparent 70%),
        radial-gradient(ellipse 35% 45% at 10% 80%, rgba(244,165,34,0.1) 0%, transparent 60%);
      pointer-events: none;
    }

    .page-header-pattern {
      position: absolute; inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cellipse cx='30' cy='30' rx='8' ry='10'/%3E%3Cellipse cx='17' cy='20' rx='4' ry='5'/%3E%3Cellipse cx='43' cy='20' rx='4' ry='5'/%3E%3Cellipse cx='14' cy='36' rx='4' ry='5'/%3E%3Cellipse cx='46' cy='36' rx='4' ry='5'/%3E%3C/g%3E%3C/svg%3E");
      pointer-events: none;
    }

    .page-header-inner {
      max-width: 900px; margin: 0 auto;
      position: relative; z-index: 1;
      display: flex; align-items: center; gap: 28px;
      flex-wrap: wrap;
    }

    /* Avatar in header */
    .header-avatar-wrap { position: relative; flex-shrink: 0; }

    .header-avatar {
      width: 88px; height: 88px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid rgba(255,255,255,0.4);
      box-shadow: 0 6px 20px rgba(0,0,0,0.2);
      background: var(--green-pale);
      display: block;
    }

    .header-avatar-badge {
      position: absolute; bottom: 2px; right: 2px;
      width: 24px; height: 24px;
      background: var(--gold);
      border-radius: 50%;
      border: 2px solid var(--white);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.65rem; color: var(--charcoal);
    }

    .header-text {}

    .header-greeting {
      font-size: 0.78rem; font-weight: 700;
      letter-spacing: 0.1em; text-transform: uppercase;
      color: rgba(255,255,255,0.6);
      margin-bottom: 6px;
      display: flex; align-items: center; gap: 8px;
    }

    .header-greeting::before {
      content: ''; display: block;
      width: 24px; height: 2px;
      background: rgba(255,255,255,0.4); border-radius: 2px;
    }

    .header-text h1 {
      font-family: 'Playfair Display', serif;
      font-size: clamp(1.6rem, 3vw, 2.2rem);
      font-weight: 900; color: var(--white);
      letter-spacing: -0.02em; margin-bottom: 4px;
    }

    .header-text h1 em { font-style: normal; color: var(--gold); }

    .header-text p { color: rgba(255,255,255,0.62); font-size: 0.88rem; }

    /* ── Main Content ── */
    .main-content {
      max-width: 900px; margin: 0 auto;
      padding: 40px 24px 80px;
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 28px;
      align-items: start;
    }

    /* ── Card ── */
    .card {
      background: var(--white);
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-sm);
      border: 1.5px solid transparent;
      overflow: hidden;
      transition: var(--transition);
    }

    .card:hover { border-color: var(--green-light); }

    .card-head {
      padding: 20px 24px 16px;
      border-bottom: 1px solid var(--green-pale);
      display: flex; align-items: center; gap: 12px;
    }

    .card-head-icon {
      width: 38px; height: 38px;
      background: var(--green-pale);
      border-radius: var(--radius-sm);
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem; color: var(--green-deep);
      flex-shrink: 0;
    }

    .card-head h3 {
      font-family: 'Playfair Display', serif;
      font-size: 1.05rem; font-weight: 700;
      color: var(--charcoal);
    }

    .card-head p {
      font-size: 0.78rem; color: var(--text-muted); margin-top: 1px;
    }

    .card-body { padding: 24px; }

    /* ── Avatar uploader ── */
    .avatar-uploader {
      display: flex; align-items: center; gap: 20px;
      margin-bottom: 24px;
      padding-bottom: 24px;
      border-bottom: 1px solid var(--green-pale);
    }

    .avatar-preview {
      width: 80px; height: 80px;
      border-radius: 50%;
      object-fit: cover;
      border: 2.5px solid var(--green-light);
      box-shadow: var(--shadow-sm);
      background: var(--green-pale);
      flex-shrink: 0;
      transition: var(--transition);
    }

    .avatar-preview:hover { border-color: var(--green-main); }

    .avatar-upload-info { flex: 1; }

    .avatar-upload-info p {
      font-size: 0.82rem; color: var(--text-muted);
      margin-bottom: 10px; line-height: 1.5;
    }

    .file-btn-label {
      display: inline-flex; align-items: center; gap: 7px;
      padding: 8px 16px;
      background: var(--green-pale);
      border: 1.5px solid var(--green-light);
      border-radius: var(--radius-pill);
      font-size: 0.82rem; font-weight: 600;
      color: var(--green-deep);
      cursor: pointer;
      transition: var(--transition);
    }

    .file-btn-label:hover {
      background: var(--green-light);
      border-color: var(--green-main);
    }

    #avatar { display: none; }

    /* ── Form fields ── */
    .form-group { margin-bottom: 18px; }

    .form-group label {
      display: block;
      font-size: 0.82rem; font-weight: 600;
      color: var(--text-body);
      margin-bottom: 6px; letter-spacing: 0.02em;
    }

    .input-wrap { position: relative; }

    .input-wrap i {
      position: absolute; left: 14px; top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted); font-size: 0.88rem;
      pointer-events: none; transition: color 0.25s;
    }

    .input-wrap input {
      width: 100%;
      padding: 11px 14px 11px 38px;
      border: 1.5px solid var(--green-light);
      border-radius: var(--radius-sm);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.92rem; color: var(--charcoal);
      background: var(--cream); outline: none;
      transition: var(--transition);
    }

    .input-wrap input:focus {
      border-color: var(--green-main);
      background: var(--white);
      box-shadow: 0 0 0 3px rgba(82,183,136,0.15);
    }

    .input-wrap input:focus ~ i { color: var(--green-main); }

    /* ── Buttons ── */
    .btn-save {
      width: 100%;
      padding: 13px;
      background: linear-gradient(135deg, var(--green-deep), var(--green-mid));
      color: var(--white); border: none;
      border-radius: var(--radius-pill);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.95rem; font-weight: 700;
      cursor: pointer; transition: var(--transition);
      box-shadow: 0 6px 20px rgba(45,106,79,0.28);
      display: flex; align-items: center; justify-content: center; gap: 8px;
      margin-top: 4px;
    }

    .btn-save:hover {
      background: linear-gradient(135deg, var(--green-mid), var(--green-main));
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(45,106,79,0.35);
    }

    .btn-save:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }

    /* ── Alert ── */
    .alert {
      padding: 10px 14px;
      border-radius: var(--radius-sm);
      font-size: 0.83rem; font-weight: 500;
      margin-top: 14px;
      display: none; align-items: center; gap: 8px;
    }

    .alert.show { display: flex; }
    .alert-success { background: var(--green-pale); color: var(--green-deep); border: 1.5px solid var(--green-light); }
    .alert-error   { background: #fef2f2; color: #c0392b; border: 1.5px solid #fca5a5; }

    /* ── Sidebar cards ── */
    .sidebar { display: flex; flex-direction: column; gap: 20px; }

    /* Quick links */
    .quick-link {
      display: flex; align-items: center; gap: 12px;
      padding: 12px 16px;
      border-radius: var(--radius-sm);
      transition: var(--transition);
      cursor: pointer; color: var(--text-body);
      font-size: 0.88rem; font-weight: 500;
      border: 1.5px solid transparent;
    }

    .quick-link:hover {
      background: var(--green-pale);
      border-color: var(--green-light);
      color: var(--green-deep);
    }

    .quick-link-icon {
      width: 36px; height: 36px;
      background: var(--green-pale);
      border-radius: var(--radius-sm);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.9rem; color: var(--green-deep);
      flex-shrink: 0; transition: var(--transition);
    }

    .quick-link:hover .quick-link-icon {
      background: var(--green-deep); color: var(--white);
    }

    .quick-link-text { flex: 1; }
    .quick-link-text strong { display: block; font-size: 0.88rem; }
    .quick-link-text span { font-size: 0.75rem; color: var(--text-muted); }

    /* Danger zone */
    .danger-zone-card { border-color: rgba(230,57,70,0.15) !important; }

    .danger-zone-card .card-head { border-bottom-color: rgba(230,57,70,0.1); }

    .danger-zone-card .card-head-icon {
      background: #fff0f1;
      color: var(--danger);
    }

    .btn-danger {
      width: 100%;
      padding: 11px;
      background: transparent;
      color: var(--danger);
      border: 1.5px solid rgba(230,57,70,0.35);
      border-radius: var(--radius-pill);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.88rem; font-weight: 600;
      cursor: pointer; transition: var(--transition);
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }

    .btn-danger:hover {
      background: var(--danger);
      color: var(--white);
      border-color: var(--danger);
      box-shadow: 0 6px 18px rgba(230,57,70,0.28);
      transform: translateY(-1px);
    }

    /* Member since badge */
    .member-badge {
      display: inline-flex; align-items: center; gap: 6px;
      background: var(--green-pale);
      border: 1.5px solid var(--green-light);
      color: var(--green-deep);
      padding: 5px 12px;
      border-radius: var(--radius-pill);
      font-size: 0.75rem; font-weight: 600;
    }

    /* Reveal */
    .reveal { opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease, transform 0.6s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }

    /* ── Responsive ── */
    @media(max-width:768px) {
      .main-content { grid-template-columns: 1fr; }
      .navbar-inner { height: 60px; padding: 0 16px; }
      .navbar-brand { font-size: 1.5rem; }
      .nav-btn span { display: none; }
      .page-header { padding: 36px 16px 28px; }
      .header-avatar { width: 68px; height: 68px; }
    }

    @media(max-width:480px) {
      .avatar-uploader { flex-direction: column; align-items: flex-start; }
    }
  </style>
</head>
<body>

  <!-- Announcement Bar -->
  <div class="announcement-bar">
    🐾 Free island-wide delivery on orders over <span>Rs.5,000</span> &nbsp;|&nbsp; Use code <span>PETPEW10</span> for 10% off
  </div>

  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-inner">
      <a href="homepage.html" class="navbar-brand">
        <div class="logo-icon">🐾</div>
        Pet<span class="brand-dot">Pew</span>
      </a>
      <div class="nav-actions">
        <a href="shopnow.html" class="nav-btn nav-btn-ghost">
          <i class="fas fa-store"></i>
          <span>Shop Now</span>
        </a>
        <a href="my_orders.html" class="nav-btn nav-btn-ghost">
          <i class="fas fa-box"></i>
          <span>My Orders</span>
        </a>
        <button id="logoutBtn" class="nav-btn nav-btn-primary">
          <i class="fas fa-sign-out-alt"></i>
          <span>Sign Out</span>
        </button>
      </div>
    </div>
  </nav>

  <!-- Page Header -->
  <div class="page-header">
    <div class="page-header-pattern"></div>
    <div class="page-header-inner">
      <div class="header-avatar-wrap">
        <img
          src="<?=htmlspecialchars($user['avatar'] ?? 'uploads/avatars/default.png')?>"
          alt="Avatar"
          class="header-avatar"
          id="headerAvatar"
        >
        <div class="header-avatar-badge"><i class="fas fa-pen"></i></div>
      </div>
      <div class="header-text">
        <div class="header-greeting">My Account</div>
        <h1>Hello, <em><?=htmlspecialchars($user['username'] ?? 'Pet Lover')?></em> 👋</h1>
        <p>Manage your profile, orders and preferences from here.</p>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">

    <!-- ── Left: Profile Form ── -->
    <div>
      <div class="card reveal">
        <div class="card-head">
          <div class="card-head-icon"><i class="fas fa-user-edit"></i></div>
          <div>
            <h3>Edit Profile</h3>
            <p>Update your personal information</p>
          </div>
        </div>
        <div class="card-body">

          <form id="profileForm" enctype="multipart/form-data">

            <!-- Avatar Uploader -->
            <div class="avatar-uploader">
              <img
                src="<?=htmlspecialchars($user['avatar'] ?? 'uploads/avatars/default.png')?>"
                alt="Preview"
                class="avatar-preview"
                id="avatarPreview"
              >
              <div class="avatar-upload-info">
                <p>Upload a photo (JPG, PNG). Max 2MB. A square image works best.</p>
                <label for="avatar" class="file-btn-label">
                  <i class="fas fa-upload"></i> Choose Photo
                </label>
                <input type="file" name="avatar" id="avatar" accept="image/*">
              </div>
            </div>

            <!-- Full Name -->
            <div class="form-group">
              <label for="full_name">Full Name</label>
              <div class="input-wrap">
                <input
                  type="text"
                  name="full_name"
                  id="full_name"
                  placeholder="Your full name"
                  value="<?=htmlspecialchars($user['full_name'] ?? '')?>"
                >
                <i class="fas fa-id-card"></i>
              </div>
            </div>

            <!-- Age -->
            <div class="form-group">
              <label for="age">Age</label>
              <div class="input-wrap">
                <input
                  type="number"
                  name="age"
                  id="age"
                  placeholder="Your age"
                  min="1" max="120"
                  value="<?=htmlspecialchars($user['age'] ?? '')?>"
                >
                <i class="fas fa-birthday-cake"></i>
              </div>
            </div>

            <button type="submit" class="btn-save" id="saveBtn">
              <i class="fas fa-save"></i> Save Changes
            </button>

          </form>

          <div class="alert alert-success" id="successMsg">
            <i class="fas fa-check-circle"></i>
            <span id="successText">Profile updated successfully!</span>
          </div>
          <div class="alert alert-error" id="errorMsg">
            <i class="fas fa-exclamation-circle"></i>
            <span id="errorText">Something went wrong.</span>
          </div>

        </div>
      </div>
    </div>

    <!-- ── Right: Sidebar ── -->
    <div class="sidebar">

      <!-- Quick Links -->
      <div class="card reveal">
        <div class="card-head">
          <div class="card-head-icon"><i class="fas fa-bolt"></i></div>
          <div>
            <h3>Quick Links</h3>
            <p>Jump to a section</p>
          </div>
        </div>
        <div class="card-body" style="padding: 12px 16px;">
          <a href="shopnow.html" class="quick-link">
            <div class="quick-link-icon"><i class="fas fa-store"></i></div>
            <div class="quick-link-text">
              <strong>Browse Shop</strong>
              <span>Discover new products</span>
            </div>
            <i class="fas fa-chevron-right" style="font-size:0.7rem;color:var(--text-muted)"></i>
          </a>
          <a href="my_orders.html" class="quick-link">
            <div class="quick-link-icon"><i class="fas fa-box"></i></div>
            <div class="quick-link-text">
              <strong>My Orders</strong>
              <span>Track your purchases</span>
            </div>
            <i class="fas fa-chevron-right" style="font-size:0.7rem;color:var(--text-muted)"></i>
          </a>
          <a href="cart.html" class="quick-link">
            <div class="quick-link-icon"><i class="fas fa-shopping-bag"></i></div>
            <div class="quick-link-text">
              <strong>My Cart</strong>
              <span>Review items in cart</span>
            </div>
            <i class="fas fa-chevron-right" style="font-size:0.7rem;color:var(--text-muted)"></i>
          </a>
          <a href="Contact.html" class="quick-link">
            <div class="quick-link-icon"><i class="fas fa-headset"></i></div>
            <div class="quick-link-text">
              <strong>Support</strong>
              <span>Get help from our team</span>
            </div>
            <i class="fas fa-chevron-right" style="font-size:0.7rem;color:var(--text-muted)"></i>
          </a>
        </div>
      </div>

      <!-- Account Info -->
      <div class="card reveal">
        <div class="card-head">
          <div class="card-head-icon"><i class="fas fa-info-circle"></i></div>
          <div>
            <h3>Account Info</h3>
            <p>Your membership details</p>
          </div>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:12px;">
          <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.85rem;">
            <span style="color:var(--text-muted);font-weight:500;">Username</span>
            <strong style="color:var(--charcoal)"><?=htmlspecialchars($user['username'] ?? '—')?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.85rem;">
            <span style="color:var(--text-muted);font-weight:500;">Email</span>
            <strong style="color:var(--charcoal);font-size:0.8rem;"><?=htmlspecialchars($user['email'] ?? '—')?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.85rem;">
            <span style="color:var(--text-muted);font-weight:500;">Member Since</span>
            <span class="member-badge">
              <i class="fas fa-star" style="font-size:0.65rem"></i>
              <?=isset($user['created_at']) ? date('M Y', strtotime($user['created_at'])) : 'Active'?>
            </span>
          </div>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="card danger-zone-card reveal">
        <div class="card-head">
          <div class="card-head-icon"><i class="fas fa-shield-alt"></i></div>
          <div>
            <h3>Danger Zone</h3>
            <p>Irreversible actions</p>
          </div>
        </div>
        <div class="card-body">
          <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:14px;line-height:1.6;">
            Permanently delete your account and all associated data. This cannot be undone.
          </p>
          <button class="btn-danger" id="deleteBtn">
            <i class="fas fa-trash-alt"></i> Delete My Account
          </button>
        </div>
      </div>

    </div>
  </div><!-- /.main-content -->

  <script>
    // ── Avatar live preview ──────────────────────────────────
    document.getElementById('avatar').addEventListener('change', function() {
      const file = this.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => {
        document.getElementById('avatarPreview').src  = e.target.result;
        document.getElementById('headerAvatar').src   = e.target.result;
      };
      reader.readAsDataURL(file);
    });

    // ── Alert helpers ────────────────────────────────────────
    function showAlert(type, msg) {
      const id  = type === 'success' ? 'successMsg' : 'errorMsg';
      const tid = type === 'success' ? 'successText' : 'errorText';
      const other = type === 'success' ? 'errorMsg' : 'successMsg';
      document.getElementById(other).classList.remove('show');
      document.getElementById(tid).textContent = msg;
      document.getElementById(id).classList.add('show');
      setTimeout(() => document.getElementById(id).classList.remove('show'), 4500);
    }

    // ── Save profile ─────────────────────────────────────────
    document.getElementById('profileForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const btn = document.getElementById('saveBtn');
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

      try {
        const res = await fetch('api/update_profile.php', {
          method: 'POST',
          body: new FormData(this)
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Update failed');
        showAlert('success', data.message || 'Profile updated successfully!');
      } catch(err) {
        showAlert('error', err.message || 'An error occurred. Please try again.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
      }
    });

    // ── Logout ───────────────────────────────────────────────
    document.getElementById('logoutBtn').addEventListener('click', async function() {
      try {
        await fetch('api/logout.php');
        window.location.href = 'login.html';
      } catch {
        window.location.href = 'login.html';
      }
    });

    // ── Delete account ───────────────────────────────────────
    document.getElementById('deleteBtn').addEventListener('click', async function() {
      const confirmed = confirm('⚠️ Are you sure you want to permanently delete your account?\n\nThis action cannot be undone.');
      if (!confirmed) return;

      this.disabled = true;
      this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting…';

      try {
        const res = await fetch('api/delete_account.php', { method: 'DELETE' });
        if (!res.ok) {
          const data = await res.json();
          throw new Error(data.error || 'Delete failed');
        }
        window.location.href = 'login.html';
      } catch(err) {
        alert(err.message || 'Could not delete account. Please try again.');
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-trash-alt"></i> Delete My Account';
      }
    });

    // ── Scroll reveal ─────────────────────────────────────────
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry, i) => {
        if (entry.isIntersecting) {
          setTimeout(() => entry.target.classList.add('visible'), i * 100);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
  </script>

</body>
</html>