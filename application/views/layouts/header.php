<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? htmlspecialchars($title) . ' — SuratSmart' : 'SuratSmart' ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    body { background: #f5f6fa; }
    .sidebar {
      min-height: 100vh;
      background: #1e293b;
      width: 240px;
      position: fixed;
      top: 0; left: 0;
      z-index: 100;
      padding-top: 0;
    }
    .sidebar-brand {
      background: #0f172a;
      padding: 1rem 1.25rem;
      font-weight: 700;
      font-size: 1.1rem;
      color: #fff;
      letter-spacing: .5px;
    }
    .sidebar .nav-link {
      color: #94a3b8;
      padding: .55rem 1.25rem;
      font-size: .9rem;
      border-radius: 0;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      color: #fff;
      background: rgba(255,255,255,.08);
    }
    .sidebar .nav-section {
      color: #475569;
      font-size: .7rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      padding: 1rem 1.25rem .25rem;
    }
    .main-content {
      margin-left: 240px;
      min-height: 100vh;
    }
    .topbar {
      background: #fff;
      border-bottom: 1px solid #e2e8f0;
      padding: .6rem 1.5rem;
    }
    .page-content { padding: 1.5rem; }
  </style>
</head>
<body>
<div class="sidebar d-flex flex-column">
  <div class="sidebar-brand">
    <i class="bi bi-file-earmark-check-fill me-2"></i>SuratSmart
  </div>
  <nav class="nav flex-column mt-2">
