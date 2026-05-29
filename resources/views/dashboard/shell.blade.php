@php
  $user = auth()->user();
  $roleNames = [
    'super_admin' => 'Super Admin',
    'admin' => 'Admin',
    'pkk' => 'PKK',
    'akk' => 'AKK',
  ];
  $accountStatus = $user->is_active ? 'Aktif' : 'Nonaktif';
  $kampusName = $user->kampus?->nama_kampus ?: 'Belum terhubung kampus';
  $kampusShort = $user->kampus?->singkatan ?: '-';
  $activePage = $activePage ?? 'dashboard';
  $maxRoleCount = max(array_values($roleCounts));
  $oldFormRole = old('_form_role');
@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $dashboard['title'] }} - Sistem KTB</title>
  <style>
    :root {
      --bg: #f6f7f4;
      --panel: #ffffff;
      --text: #13201d;
      --muted: #60706b;
      --line: #dfe7e2;
      --navy: #0f2544;
      --navy-soft: #e8eef7;
      --gold: #f5a623;
      --gold-soft: #fff4dd;
      --green: #0f766e;
      --green-soft: #e7f3ef;
      --blue: #2563eb;
      --blue-soft: #e9efff;
      --danger: #b42318;
      --shadow: 0 14px 40px rgba(19, 32, 29, 0.08);
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      min-height: 100vh;
      font-family: Inter, Manrope, "Segoe UI", Arial, sans-serif;
      color: var(--text);
      background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.86), rgba(255, 255, 255, 0.96)),
        linear-gradient(135deg, #edf4fb 0%, #f8f0df 48%, #eef5f0 100%);
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    button,
    input,
    select {
      font: inherit;
    }

    .app-shell {
      min-height: 100vh;
      display: grid;
      grid-template-columns: 272px minmax(0, 1fr);
    }

    .sidebar {
      min-height: 100vh;
      padding: 22px;
      border-right: 1px solid var(--line);
      background: rgba(255, 255, 255, 0.82);
      position: sticky;
      top: 0;
      align-self: start;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 26px;
    }

    .brand img {
      width: 44px;
      height: 44px;
      object-fit: contain;
    }

    .brand strong,
    .brand small {
      display: block;
    }

    .brand strong {
      color: var(--navy);
      font-size: 16px;
      line-height: 1.1;
    }

    .brand small,
    .eyebrow,
    .metric-card span,
    .detail-label,
    .table th {
      color: var(--muted);
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0;
      text-transform: uppercase;
    }

    .nav {
      display: grid;
      gap: 6px;
      margin-bottom: 22px;
    }

    .nav a {
      display: block;
      padding: 10px 12px;
      border-radius: 8px;
      color: #33413d;
      font-weight: 800;
    }

    .nav a:hover,
    .nav a.active {
      background: var(--green-soft);
      color: var(--green);
    }

    .sidebar-card,
    .panel,
    .metric-card {
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 8px;
      box-shadow: var(--shadow);
    }

    .sidebar-card {
      padding: 14px;
      margin-bottom: 18px;
    }

    .sidebar-card strong {
      display: block;
      margin-top: 6px;
      color: var(--navy);
    }

    .sidebar-card small {
      display: block;
      margin-top: 4px;
      color: var(--muted);
      line-height: 1.5;
    }

    .logout-form {
      margin: 0;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      min-height: 40px;
      padding: 9px 14px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: #fff;
      color: #33413d;
      font-weight: 800;
      cursor: pointer;
    }

    .btn:hover {
      border-color: var(--gold);
      color: var(--navy);
      background: var(--gold-soft);
    }

    .btn.is-compact {
      width: auto;
      min-height: 34px;
      padding: 7px 10px;
      font-size: 13px;
    }

    .btn.is-danger:hover {
      border-color: #f3b1aa;
      color: var(--danger);
      background: #fff1f0;
    }

    .main {
      min-width: 0;
      padding: 26px;
    }

    .topbar {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      align-items: flex-start;
      margin-bottom: 20px;
    }

    .topbar h1 {
      margin: 4px 0 8px;
      color: var(--navy);
      font-size: clamp(1.8rem, 3vw, 2.5rem);
      line-height: 1.1;
    }

    .topbar p {
      margin: 0;
      color: var(--muted);
      line-height: 1.6;
    }

    .user-chip {
      min-width: 220px;
      padding: 12px 14px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.76);
      text-align: right;
    }

    .user-chip strong,
    .user-chip span {
      display: block;
    }

    .user-chip span {
      margin-top: 4px;
      color: var(--muted);
      font-size: 13px;
    }

    .metric-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 14px;
      margin-bottom: 18px;
    }

    .metric-card {
      padding: 18px;
      border-top: 4px solid var(--navy);
    }

    .metric-card strong {
      display: block;
      margin-top: 8px;
      color: var(--navy);
      font-size: clamp(1.45rem, 3vw, 2rem);
      line-height: 1.1;
      overflow-wrap: anywhere;
    }

    .metric-card small {
      display: block;
      margin-top: 8px;
      color: var(--muted);
      line-height: 1.45;
    }

    .metric-card.tone-success {
      border-top-color: var(--green);
    }

    .metric-card.tone-info {
      border-top-color: var(--blue);
    }

    .metric-card.tone-warning {
      border-top-color: var(--gold);
    }

    .content-grid {
      display: grid;
      grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
      gap: 18px;
      margin-bottom: 18px;
    }

    .panel {
      padding: 18px;
      margin-bottom: 18px;
    }

    .panel-head {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      align-items: center;
      margin-bottom: 14px;
    }

    .panel h2 {
      margin: 4px 0 0;
      color: var(--navy);
      font-size: 1.15rem;
      line-height: 1.25;
    }

    .search {
      width: min(360px, 100%);
      padding: 10px 12px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: #fff;
      color: var(--text);
      outline: none;
    }

    .search:focus {
      border-color: var(--green);
      box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
    }

    .alert {
      margin-bottom: 16px;
      padding: 12px 14px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: var(--green-soft);
      color: var(--green);
      font-weight: 700;
      line-height: 1.5;
    }

    .alert.is-danger {
      background: #fff1f0;
      color: var(--danger);
      border-color: #f3b1aa;
    }

    .alert ul {
      margin: 6px 0 0;
      padding-left: 18px;
      font-weight: 600;
    }

    .modal {
      position: fixed;
      inset: 0;
      z-index: 1000;
      display: grid;
      place-items: center;
      padding: 22px;
      background: rgba(15, 37, 68, 0.42);
      overflow: auto;
    }

    .modal[hidden] {
      display: none;
    }

    .modal-panel {
      width: min(760px, 100%);
      max-height: calc(100vh - 44px);
      overflow: auto;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 28px 70px rgba(15, 37, 68, 0.24);
    }

    .modal-panel.is-small {
      width: min(460px, 100%);
    }

    .modal-head {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      align-items: flex-start;
      padding: 18px;
      border-bottom: 1px solid var(--line);
      background: #f8fbf9;
    }

    .modal-head h2 {
      margin: 4px 0 0;
      color: var(--navy);
      font-size: 1.15rem;
      line-height: 1.25;
    }

    .modal-body {
      padding: 18px;
    }

    .modal-close {
      width: auto;
      min-width: 38px;
      min-height: 38px;
      padding: 6px 10px;
      font-size: 20px;
      line-height: 1;
    }

    body.has-open-modal {
      overflow: hidden;
    }

    .management-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
      margin-bottom: 18px;
    }

    .table-toolbar {
      display: grid;
      grid-template-columns: minmax(240px, 1.4fr) repeat(6, minmax(150px, 1fr));
      gap: 10px;
      margin-bottom: 14px;
      align-items: end;
    }

    .table-toolbar .field {
      gap: 5px;
    }

    .table-toolbar .search {
      width: 100%;
    }

    .table-counter {
      margin: 0 0 12px;
      color: var(--muted);
      font-size: 13px;
      font-weight: 700;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px;
    }

    .form-grid .field.is-full {
      grid-column: 1 / -1;
    }

    .field {
      display: grid;
      gap: 6px;
    }

    .field label,
    .checkbox-field {
      color: #344540;
      font-size: 13px;
      font-weight: 800;
    }

    .field input,
    .field select {
      width: 100%;
      min-height: 40px;
      padding: 9px 10px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: #fff;
      color: var(--text);
      outline: none;
    }

    .field input:focus,
    .field select:focus {
      border-color: var(--green);
      box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
    }

    .checkbox-field {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      min-height: 40px;
    }

    .checkbox-field input {
      width: 16px;
      height: 16px;
      margin: 0;
    }

    .form-actions,
    .crud-actions {
      display: flex;
      gap: 8px;
      align-items: center;
      flex-wrap: wrap;
      margin-top: 12px;
    }

    .form-actions .btn {
      width: auto;
    }

    .inline-delete {
      margin: 0;
    }

    .compact-edit-form {
      margin: 0;
    }

    .role-stack,
    .detail-list {
      display: grid;
      gap: 12px;
    }

    .role-row {
      display: grid;
      gap: 7px;
    }

    .role-row header,
    .detail-row {
      display: flex;
      justify-content: space-between;
      gap: 14px;
      align-items: center;
    }

    .role-row strong,
    .detail-value {
      color: var(--navy);
      font-weight: 800;
      text-align: right;
    }

    .role-track {
      height: 9px;
      overflow: hidden;
      border-radius: 999px;
      background: #edf2ef;
    }

    .role-fill {
      display: block;
      height: 100%;
      border-radius: inherit;
      background: linear-gradient(90deg, var(--green), var(--blue));
    }

    .detail-row {
      padding: 10px 0;
      border-bottom: 1px solid var(--line);
    }

    .detail-row:last-child {
      border-bottom: 0;
    }

    .table-wrap {
      width: 100%;
      overflow: auto;
      border: 1px solid var(--line);
      border-radius: 8px;
    }

    .table {
      width: 100%;
      min-width: 760px;
      border-collapse: collapse;
    }

    .table th,
    .table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid var(--line);
      vertical-align: top;
    }

    .table th {
      background: #f3f7f5;
      color: #344540;
    }

    .table.is-wide {
      min-width: 1180px;
    }

    .table .cell-main {
      display: grid;
      gap: 4px;
    }

    .row-actions {
      display: flex;
      gap: 8px;
      align-items: flex-start;
      flex-wrap: wrap;
    }

    .table tr:last-child td {
      border-bottom: 0;
    }

    .muted {
      color: var(--muted);
    }

    .badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 26px;
      padding: 4px 9px;
      border-radius: 999px;
      background: var(--green-soft);
      color: var(--green);
      font-size: 12px;
      font-weight: 800;
      white-space: nowrap;
    }

    .badge.warning {
      background: var(--gold-soft);
      color: #925d00;
    }

    .badge.neutral {
      background: var(--navy-soft);
      color: var(--navy);
    }

    .empty-state {
      padding: 20px;
      border: 1px dashed var(--line);
      border-radius: 8px;
      color: var(--muted);
      background: rgba(255, 255, 255, 0.62);
    }

    .page-actions {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 16px;
    }

    .campus-role-grid {
      display: grid;
      gap: 16px;
    }

    .campus-role-card {
      padding: 18px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: var(--panel);
      box-shadow: var(--shadow);
    }

    .campus-role-head {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      align-items: flex-start;
      margin-bottom: 16px;
    }

    .campus-role-head h2 {
      margin: 4px 0;
      color: var(--navy);
      font-size: 1.1rem;
      line-height: 1.25;
    }

    .campus-role-counts {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      justify-content: flex-end;
    }

    .role-columns {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
    }

    .role-column {
      min-width: 0;
    }

    .role-column h3 {
      margin: 0 0 10px;
      color: var(--navy);
      font-size: 0.95rem;
    }

    .member-list {
      display: grid;
      gap: 8px;
      margin: 0;
      padding: 0;
      list-style: none;
    }

    .member-list li {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      align-items: flex-start;
      padding: 10px 0;
      border-top: 1px solid var(--line);
    }

    .member-entry {
      flex: 1;
      min-width: 0;
    }

    .member-list .crud-actions {
      justify-content: flex-end;
      margin-top: 0;
    }

    .member-list strong,
    .member-list span {
      display: block;
    }

    .member-list strong {
      color: var(--text);
    }

    .member-list span {
      margin-top: 3px;
      color: var(--muted);
      font-size: 12px;
    }

    .member-list .badge {
      display: inline-flex;
      margin-top: 0;
      color: var(--green);
    }

    .member-list .badge.neutral {
      color: var(--navy);
    }

    .tree-v2-surface {
      min-height: 620px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.82);
      box-shadow: var(--shadow);
      overflow: hidden;
    }

    .tree-v2-toolbar {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      align-items: center;
      padding: 14px;
      border-bottom: 1px solid var(--line);
      background: rgba(255, 255, 255, 0.86);
    }

    .tree-v2-search {
      display: flex;
      width: min(460px, 100%);
      gap: 8px;
    }

    .tree-v2-search input {
      flex: 1;
      min-width: 0;
    }

    .tree-v2-search button,
    .tree-v2-toolbar button {
      width: auto;
      min-height: 38px;
    }

    .zoom-controls {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .zoom-value {
      min-width: 48px;
      color: var(--muted);
      font-size: 13px;
      font-weight: 800;
      text-align: center;
    }

    .tree-v2-scroll {
      height: 620px;
      overflow: auto;
      cursor: grab;
    }

    .tree-v2-scroll.is-dragging {
      cursor: grabbing;
      user-select: none;
    }

    .tree-v2-zoom {
      width: max-content;
      min-width: 100%;
      transform-origin: top center;
    }

    .tree-v2-graph {
      --tree-v2-node-width: 210px;
      --tree-v2-node-height: 92px;
      padding: 30px;
    }

    .tree-v2-root,
    .tree-v2-children {
      margin: 0;
      padding-left: 0;
      list-style: none;
    }

    .tree-v2-root {
      display: flex;
      align-items: flex-start;
      justify-content: center;
      gap: 24px;
    }

    .tree-v2-item {
      position: relative;
      list-style: none;
      padding: 0;
      text-align: center;
    }

    .tree-v2-node {
      position: relative;
      z-index: 1;
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 7px;
      width: var(--tree-v2-node-width);
      min-width: var(--tree-v2-node-width);
      max-width: var(--tree-v2-node-width);
      height: var(--tree-v2-node-height);
      padding: 10px;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      background: linear-gradient(150deg, #ffffff, #f8fafc);
      box-shadow: 0 16px 30px rgba(15, 23, 42, 0.1);
      overflow: hidden;
      text-align: left;
      transition: border-color 0.16s ease, box-shadow 0.16s ease, transform 0.16s ease;
    }

    .tree-v2-node:focus-visible {
      outline: 2px solid var(--blue);
      outline-offset: 3px;
    }

    .tree-v2-node:hover {
      transform: translateY(-1px);
      box-shadow: 0 18px 32px rgba(15, 23, 42, 0.14);
    }

    .tree-v2-node.is-search-hit {
      border-color: var(--gold);
      box-shadow: 0 0 0 3px rgba(245, 166, 35, 0.28), 0 18px 32px rgba(245, 166, 35, 0.18);
    }

    .tree-v2-campus {
      background: linear-gradient(150deg, #ecfdf5, #d1fae5);
      border-color: #6ee7b7;
    }

    .tree-v2-group {
      background: linear-gradient(150deg, #fff7ed, #ffedd5);
      border-color: #fdba74;
    }

    .tree-v2-person.is-pkk {
      background: linear-gradient(150deg, #eff6ff, #dbeafe);
      border-color: #93c5fd;
    }

    .tree-v2-person.is-akk {
      background: linear-gradient(150deg, #f7faf9, #e7f3ef);
      border-color: #9bd7cd;
    }

    .tree-v2-node-head {
      display: grid;
      width: 100%;
      grid-template-columns: minmax(0, 1fr) auto;
      align-items: start;
      gap: 8px;
    }

    .tree-v2-name {
      display: block;
      width: 100%;
      min-width: 0;
      overflow: hidden;
      color: #16231f;
      font-size: 13px;
      font-weight: 800;
      line-height: 1.3;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .tree-v2-meta {
      width: 100%;
      overflow: hidden;
      color: #5f6c7b;
      font-size: 11px;
      line-height: 1.35;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .tree-v2-children {
      --tree-v2-gap: 16px;
      --tree-v2-connector: 22px;
      position: relative;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      gap: var(--tree-v2-gap);
      margin-top: 18px;
      padding-top: var(--tree-v2-connector);
    }

    .tree-v2-children::before {
      content: "";
      position: absolute;
      top: calc(var(--tree-v2-connector) * -1);
      left: 50%;
      height: var(--tree-v2-connector);
      border-left: 2px solid #c7d3e0;
      transform: translateX(-50%);
    }

    .tree-v2-children > .tree-v2-item::before,
    .tree-v2-children > .tree-v2-item::after {
      content: "";
      position: absolute;
      top: calc(var(--tree-v2-connector) * -1);
    }

    .tree-v2-children > .tree-v2-item::before {
      left: 50%;
      height: var(--tree-v2-connector);
      border-left: 2px solid #c7d3e0;
      transform: translateX(-50%);
    }

    .tree-v2-children > .tree-v2-item::after {
      left: calc(var(--tree-v2-gap) * -0.5);
      right: calc(var(--tree-v2-gap) * -0.5);
      border-top: 2px solid #c7d3e0;
    }

    .tree-v2-children > .tree-v2-item:first-child::after {
      left: 50%;
    }

    .tree-v2-children > .tree-v2-item:last-child::after {
      right: 50%;
    }

    .tree-v2-children > .tree-v2-item:only-child::after {
      display: none;
    }

    .tree-v2-level-members {
      --tree-v2-gap: 12px;
    }

    @media (max-width: 1040px) {
      .metric-grid,
      .content-grid,
      .management-grid,
      .table-toolbar {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 760px) {
      .app-shell {
        grid-template-columns: 1fr;
      }

      .sidebar {
        position: relative;
        min-height: auto;
      }

      .nav {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .main {
        padding: 18px;
      }

      .topbar,
      .panel-head {
        flex-direction: column;
        align-items: stretch;
      }

      .user-chip {
        min-width: 0;
        text-align: left;
      }

      .metric-grid,
      .content-grid,
      .management-grid,
      .table-toolbar,
      .form-grid {
        grid-template-columns: 1fr;
      }

      .page-actions,
      .campus-role-head,
      .tree-v2-toolbar {
        flex-direction: column;
        align-items: stretch;
      }

      .role-columns {
        grid-template-columns: 1fr;
      }

      .campus-role-counts {
        justify-content: flex-start;
      }

      .tree-v2-scroll {
        height: 520px;
      }

      .tree-v2-graph {
        --tree-v2-node-width: 186px;
        --tree-v2-node-height: 88px;
        padding: 22px;
      }

      .tree-v2-root,
      .tree-v2-children {
        gap: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar">
      <a class="brand" href="{{ route('dashboard') }}">
        <img src="{{ asset('images/ktb_logo.png') }}" alt="Logo KTB">
        <span>
          <strong>Sistem KTB</strong>
          <small>Perkantas Surabaya</small>
        </span>
      </a>

      <nav class="nav" aria-label="Navigasi dashboard">
        <a class="{{ $activePage === 'dashboard' ? 'active' : '' }}" href="{{ route($dashboard['route']) }}">Dashboard</a>
        @if ($canSeeAdminData)
          <a class="{{ $activePage === 'kampus' ? 'active' : '' }}" href="{{ route('dashboard.kampus') }}">Kampus</a>
          <a class="{{ $activePage === 'pengguna' ? 'active' : '' }}" href="{{ route('dashboard.pengguna') }}">Pengguna</a>
          <a class="{{ $activePage === 'pohon' ? 'active' : '' }}" href="{{ route('dashboard.pohon') }}">Pohon</a>
        @else
          <a href="{{ route($dashboard['route']) }}#profil-akun">Profil Akun</a>
        @endif
      </nav>

      <section class="sidebar-card">
        <span class="eyebrow">Akses Login</span>
        <strong>{{ $dashboard['roleLabel'] }}</strong>
        <small>{{ $user->username }} - {{ $accountStatus }}</small>
      </section>

      <form method="POST" action="{{ route('logout') }}" class="logout-form">
        @csrf
        <button type="submit" class="btn">Logout</button>
      </form>
    </aside>

    <main class="main">
      <header class="topbar">
        <div>
          <span class="eyebrow">{{ $dashboard['eyebrow'] }}</span>
          <h1>{{ $dashboard['title'] }}</h1>
          <p>{{ $dashboard['subtitle'] }}</p>
        </div>
        <div class="user-chip">
          <strong>{{ $user->nama_lengkap }}</strong>
          <span>{{ $roleNames[$user->role] ?? strtoupper($user->role) }} - {{ $kampusShort }}</span>
        </div>
      </header>

      @if (session('success'))
        <div class="alert">{{ session('success') }}</div>
      @endif

      @if ($errors->any())
        <div class="alert is-danger">
          <strong>Data belum bisa disimpan.</strong>
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @if ($activePage === 'dashboard')
        <section class="metric-grid" aria-label="Ringkasan dashboard">
          @foreach ($metrics as $metric)
            <article class="metric-card tone-{{ $metric['tone'] }}">
              <span>{{ $metric['label'] }}</span>
              <strong>{{ $metric['value'] }}</strong>
              <small>{{ $metric['hint'] }}</small>
            </article>
          @endforeach
        </section>

        <section class="content-grid">
          @if ($canSeeAdminData)
            <article class="panel">
              <div class="panel-head">
                <div>
                  <span class="eyebrow">Distribusi</span>
                  <h2>Akun Berdasarkan Role</h2>
                </div>
              </div>
              <div class="role-stack">
                @foreach ($roleCounts as $role => $count)
                  @php
                    $width = $maxRoleCount > 0 ? round(($count / $maxRoleCount) * 100) : 0;
                  @endphp
                  <div class="role-row">
                    <header>
                      <span>{{ $roleNames[$role] ?? strtoupper($role) }}</span>
                      <strong>{{ number_format($count, 0, ',', '.') }}</strong>
                    </header>
                    <div class="role-track">
                      <span class="role-fill" style="width: {{ $width }}%"></span>
                    </div>
                  </div>
                @endforeach
              </div>
            </article>
          @endif

          <article class="panel" id="profil-akun">
            <div class="panel-head">
              <div>
                <span class="eyebrow">Profil</span>
                <h2>Informasi Akun</h2>
              </div>
              <span class="badge {{ $user->is_active ? '' : 'warning' }}">{{ $accountStatus }}</span>
            </div>
            <div class="detail-list">
              <div class="detail-row">
                <span class="detail-label">Nama</span>
                <span class="detail-value">{{ $user->nama_lengkap }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Username</span>
                <span class="detail-value">{{ $user->username }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Kampus</span>
                <span class="detail-value">{{ $kampusName }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Angkatan</span>
                <span class="detail-value">{{ $user->angkatan ?: '-' }}</span>
              </div>
            </div>
          </article>
        </section>
      @elseif ($activePage === 'kampus' && $canSeeAdminData)
        <section class="panel" id="ringkasan-kampus">
          <div class="panel-head">
            <div>
              <span class="eyebrow">Ringkasan</span>
              <h2>Pengguna per Kampus</h2>
            </div>
            <div class="row-actions">
              <input class="search" type="search" placeholder="Cari kampus..." data-filter-table="campus-table" aria-label="Cari kampus">
              @if ($canManageData)
                <button class="btn is-compact" type="button" data-modal-open="modal-kampus-create">Tambah Kampus</button>
              @endif
            </div>
          </div>

          @if ($canManageData)
            <div class="modal" id="modal-kampus-create" hidden>
              <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-kampus-create-title">
                <div class="modal-head">
                  <div>
                    <span class="eyebrow">Tambah</span>
                    <h2 id="modal-kampus-create-title">Kampus</h2>
                  </div>
                  <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                </div>
                <div class="modal-body">
                  @include('dashboard.partials.kampus-form')
                </div>
              </div>
            </div>
          @endif

          @if ($campusSummaries->isEmpty())
            <div class="empty-state">Belum ada data kampus.</div>
          @else
            <div class="table-wrap">
              <table class="table" id="campus-table">
                <thead>
                  <tr>
                    <th>Kampus</th>
                    <th>Status</th>
                    <th>Total User</th>
                    <th>Aktif</th>
                    <th>PKK</th>
                    <th>AKK</th>
                    @if ($canManageData)
                      <th>Aksi</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                  @foreach ($campusSummaries as $kampus)
                    <tr>
                      <td>
                        <strong>{{ $kampus->nama_kampus }}</strong>
                        <div class="muted">{{ $kampus->singkatan ?: '-' }}</div>
                      </td>
                      <td>
                        <span class="badge {{ $kampus->is_active ? '' : 'warning' }}">
                          {{ $kampus->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                      </td>
                      <td>{{ number_format($kampus->total_users, 0, ',', '.') }}</td>
                      <td>{{ number_format($kampus->active_users, 0, ',', '.') }}</td>
                      <td>{{ number_format($kampus->pkk_users, 0, ',', '.') }}</td>
                      <td>{{ number_format($kampus->akk_users, 0, ',', '.') }}</td>
                      @if ($canManageData)
                        <td>
                          <div class="row-actions">
                            <button class="btn is-compact" type="button" data-modal-open="modal-kampus-edit-{{ $kampus->kampus_id }}">Edit</button>
                            <button class="btn is-compact is-danger" type="button" data-modal-open="modal-kampus-delete-{{ $kampus->kampus_id }}">Hapus</button>
                          </div>

                          <div class="modal" id="modal-kampus-edit-{{ $kampus->kampus_id }}" hidden>
                            <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-kampus-edit-title-{{ $kampus->kampus_id }}">
                              <div class="modal-head">
                                <div>
                                  <span class="eyebrow">Edit</span>
                                  <h2 id="modal-kampus-edit-title-{{ $kampus->kampus_id }}">{{ $kampus->nama_kampus }}</h2>
                                </div>
                                <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                              </div>
                              <div class="modal-body">
                                @include('dashboard.partials.kampus-form', ['kampus' => $kampus])
                              </div>
                            </div>
                          </div>

                          <div class="modal" id="modal-kampus-delete-{{ $kampus->kampus_id }}" hidden>
                            <div class="modal-panel is-small" role="dialog" aria-modal="true" aria-labelledby="modal-kampus-delete-title-{{ $kampus->kampus_id }}">
                              <div class="modal-head">
                                <div>
                                  <span class="eyebrow">Hapus</span>
                                  <h2 id="modal-kampus-delete-title-{{ $kampus->kampus_id }}">Kampus</h2>
                                </div>
                                <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                              </div>
                              <div class="modal-body">
                                <p class="muted">Hapus {{ $kampus->nama_kampus }}? Pengguna terkait akan menjadi tanpa kampus.</p>
                                <form method="POST" action="{{ route('dashboard.kampus.destroy', $kampus) }}" class="inline-delete">
                                  @csrf
                                  @method('DELETE')
                                  <div class="form-actions">
                                    <button class="btn is-compact is-danger" type="submit">Hapus Kampus</button>
                                    <button class="btn is-compact" type="button" data-modal-close>Batal</button>
                                  </div>
                                </form>
                              </div>
                            </div>
                          </div>
                        </td>
                      @endif
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </section>
      @elseif ($activePage === 'pengguna' && $canSeeAdminData)
        <section class="panel" id="daftar-pengguna">
          <div class="panel-head">
            <div>
              <span class="eyebrow">Direktori</span>
              <h2>Daftar Pengguna</h2>
            </div>
            <input class="search" type="search" placeholder="Cari pengguna..." data-filter-table="user-table" aria-label="Cari pengguna">
          </div>

          @if ($userRows->isEmpty())
            <div class="empty-state">Belum ada data pengguna.</div>
          @else
            <div class="table-wrap">
              <table class="table" id="user-table">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Kampus</th>
                    <th>Angkatan</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($userRows as $row)
                    <tr>
                      <td>
                        <strong>{{ $row->nama_lengkap }}</strong>
                        <div class="muted">Dibuat {{ $row->created_at?->format('d M Y') ?: '-' }}</div>
                      </td>
                      <td>{{ $row->username }}</td>
                      <td><span class="badge neutral">{{ $roleNames[$row->role] ?? strtoupper($row->role) }}</span></td>
                      <td>
                        <strong>{{ $row->kampus?->singkatan ?: '-' }}</strong>
                        <div class="muted">{{ $row->kampus?->nama_kampus ?: 'Belum ada kampus' }}</div>
                      </td>
                      <td>{{ $row->angkatan ?: '-' }}</td>
                      <td>
                        <span class="badge {{ $row->is_active ? '' : 'warning' }}">
                          {{ $row->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </section>
      @elseif ($activePage === 'pohon' && $canSeeAdminData)
        <section class="tree-v2-surface">
          @if ($treeGroups->isEmpty())
            <div class="empty-state">Belum ada data PKK atau AKK untuk ditampilkan.</div>
          @else
            <div class="tree-v2-toolbar">
              <div class="tree-v2-search">
                <input class="search" type="search" list="tree-search-list" placeholder="Cari nama atau kampus..." data-tree-search-input aria-label="Cari nama atau kampus di pohon">
                <button class="btn" type="button" data-tree-search-submit>Cari</button>
                <datalist id="tree-search-list">
                  @foreach ($treeSearchNames as $name)
                    <option value="{{ $name }}"></option>
                  @endforeach
                </datalist>
              </div>
              <div class="zoom-controls" data-zoom-controls>
                <button class="btn" type="button" data-zoom-out aria-label="Perkecil zoom">-</button>
                <span class="zoom-value" data-zoom-value>90%</span>
                <button class="btn" type="button" data-zoom-in aria-label="Perbesar zoom">+</button>
              </div>
            </div>

            <div class="tree-v2-scroll" data-drag-scroll>
              <div class="tree-v2-zoom" data-tree-zoom>
                <div class="tree-v2-graph" role="tree" aria-label="Grafik pohon pemuridan">
                  <ul class="tree-v2-root">
                    @foreach ($treeGroups as $group)
                      <li class="tree-v2-item">
                        <article class="tree-v2-node tree-v2-campus" data-search-name="{{ $group['name'] }} {{ $group['short'] }}" tabindex="0" aria-label="{{ $group['name'] }}">
                          <div class="tree-v2-node-head">
                            <div class="tree-v2-name" title="{{ $group['name'] }}">{{ $group['name'] }}</div>
                            <span class="badge neutral">Kampus</span>
                          </div>
                          <div class="tree-v2-meta">{{ $group['pkk']->count() }} PKK - {{ $group['akk']->count() }} AKK</div>
                        </article>

                        <ul class="tree-v2-children">
                          @foreach ($group['branches'] as $branch)
                            <li class="tree-v2-item">
                              <article class="tree-v2-node tree-v2-person is-pkk" data-search-name="{{ $branch['pkk']->nama_lengkap }}" tabindex="0" aria-label="{{ $branch['pkk']->nama_lengkap }}">
                                <div class="tree-v2-node-head">
                                  <div class="tree-v2-name" title="{{ $branch['pkk']->nama_lengkap }}">{{ $branch['pkk']->nama_lengkap }}</div>
                                  <span class="badge neutral">PKK</span>
                                </div>
                                <div class="tree-v2-meta">{{ $branch['pkk']->username }} - {{ $branch['akk']->count() }} AKK</div>
                              </article>

                              @if ($branch['akk']->isNotEmpty())
                                <ul class="tree-v2-children tree-v2-level-members">
                                  @foreach ($branch['akk'] as $row)
                                    <li class="tree-v2-item">
                                      <article class="tree-v2-node tree-v2-person is-akk" data-search-name="{{ $row->nama_lengkap }}" tabindex="0" aria-label="{{ $row->nama_lengkap }}">
                                        <div class="tree-v2-node-head">
                                          <div class="tree-v2-name" title="{{ $row->nama_lengkap }}">{{ $row->nama_lengkap }}</div>
                                          <span class="badge">AKK</span>
                                        </div>
                                        <div class="tree-v2-meta">{{ $row->username }}{{ $row->angkatan ? ' - '.$row->angkatan : '' }}</div>
                                      </article>
                                    </li>
                                  @endforeach
                                </ul>
                              @endif
                            </li>
                          @endforeach

                          @if ($group['unassigned_akk']->isNotEmpty())
                            <li class="tree-v2-item">
                              <article class="tree-v2-node tree-v2-group" data-search-name="AKK tanpa PKK {{ $group['name'] }}" tabindex="0" aria-label="AKK tanpa PKK {{ $group['name'] }}">
                                <div class="tree-v2-node-head">
                                  <div class="tree-v2-name">AKK tanpa PKK</div>
                                  <span class="badge">{{ $group['unassigned_akk']->count() }}</span>
                                </div>
                                <div class="tree-v2-meta">{{ $group['name'] }}</div>
                              </article>
                              <ul class="tree-v2-children tree-v2-level-members">
                                @foreach ($group['unassigned_akk'] as $row)
                                  <li class="tree-v2-item">
                                    <article class="tree-v2-node tree-v2-person is-akk" data-search-name="{{ $row->nama_lengkap }}" tabindex="0" aria-label="{{ $row->nama_lengkap }}">
                                      <div class="tree-v2-node-head">
                                        <div class="tree-v2-name" title="{{ $row->nama_lengkap }}">{{ $row->nama_lengkap }}</div>
                                        <span class="badge">AKK</span>
                                      </div>
                                      <div class="tree-v2-meta">{{ $row->username }}{{ $row->angkatan ? ' - '.$row->angkatan : '' }}</div>
                                    </article>
                                  </li>
                                @endforeach
                              </ul>
                            </li>
                          @endif
                        </ul>
                      </li>
                    @endforeach
                  </ul>
                </div>
              </div>
            </div>
          @endif
        </section>
      @endif
    </main>
  </div>

  <script>
    var activeModal = null;
    var lastModalTrigger = null;

    function openModal(modal, trigger) {
      if (!modal) return;
      lastModalTrigger = trigger || document.activeElement;
      modal.hidden = false;
      activeModal = modal;
      document.body.classList.add('has-open-modal');

      var firstInput = modal.querySelector('.modal-body input:not([type="hidden"]), .modal-body select, .modal-body textarea, .modal-body button');
      if (firstInput) {
        firstInput.focus({ preventScroll: true });
      }
    }

    function closeModal(modal) {
      if (!modal) return;
      modal.hidden = true;
      if (activeModal === modal) {
        activeModal = null;
      }
      if (!document.querySelector('.modal:not([hidden])')) {
        document.body.classList.remove('has-open-modal');
      }
      if (lastModalTrigger && typeof lastModalTrigger.focus === 'function') {
        lastModalTrigger.focus({ preventScroll: true });
      }
    }

    document.querySelectorAll('[data-modal-open]').forEach(function (button) {
      button.addEventListener('click', function () {
        openModal(document.getElementById(button.getAttribute('data-modal-open')), button);
      });
    });

    document.querySelectorAll('[data-modal-close]').forEach(function (button) {
      button.addEventListener('click', function () {
        closeModal(button.closest('.modal'));
      });
    });

    document.querySelectorAll('.modal').forEach(function (modal) {
      modal.addEventListener('mousedown', function (event) {
        if (event.target === modal) {
          closeModal(modal);
        }
      });
    });

    window.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && activeModal) {
        closeModal(activeModal);
      }
    });

    @if ($errors->any() && old('_modal_id'))
      openModal(document.getElementById(@json(old('_modal_id'))));
    @endif

    document.querySelectorAll('[data-filter-table]').forEach(function (input) {
      input.addEventListener('input', function () {
        var table = document.getElementById(input.getAttribute('data-filter-table'));
        var query = input.value.toLowerCase();
        if (!table) return;

        table.querySelectorAll('tbody tr').forEach(function (row) {
          row.hidden = query !== '' && !row.textContent.toLowerCase().includes(query);
        });
      });
    });

    document.querySelectorAll('[data-filter-panel]').forEach(function (panel) {
      var table = document.getElementById(panel.getAttribute('data-filter-panel'));
      if (!table) return;

      var controls = Array.prototype.slice.call(panel.querySelectorAll('[data-column-filter]'));
      var rows = Array.prototype.slice.call(table.querySelectorAll('tbody tr[data-filter-row]'));
      var emptyRow = table.querySelector('[data-filter-empty-row]');
      var counter = document.querySelector('[data-filter-counter="' + table.id + '"]');

      function applyColumnFilters() {
        var visibleRows = 0;

        rows.forEach(function (row) {
          var isVisible = controls.every(function (control) {
            var key = control.getAttribute('data-column-filter');
            var value = control.value.trim().toLowerCase();

            if (!value) return true;

            if (key === 'search') {
              return (row.getAttribute('data-search-text') || '').toLowerCase().includes(value);
            }

            return (row.getAttribute('data-' + key) || '').toLowerCase() === value;
          });

          row.hidden = !isVisible;
          if (isVisible) visibleRows++;
        });

        if (emptyRow) {
          emptyRow.hidden = visibleRows > 0;
        }

        if (counter) {
          counter.textContent = 'Menampilkan ' + visibleRows.toLocaleString('id-ID') + ' dari ' + rows.length.toLocaleString('id-ID') + ' data.';
        }
      }

      controls.forEach(function (control) {
        control.addEventListener('input', applyColumnFilters);
        control.addEventListener('change', applyColumnFilters);
      });

      applyColumnFilters();
    });

    document.querySelectorAll('[data-filter-block]').forEach(function (input) {
      input.addEventListener('input', function () {
        var selector = input.getAttribute('data-filter-block');
        var query = input.value.toLowerCase();
        if (!selector) return;

        document.querySelectorAll(selector).forEach(function (block) {
          var text = (block.getAttribute('data-filter-text') || block.textContent || '').toLowerCase();
          block.hidden = query !== '' && !text.includes(query);
        });
      });
    });

    var treeScrollArea = document.querySelector('[data-drag-scroll]');
    if (treeScrollArea) {
      var isDragging = false;
      var dragStartX = 0;
      var dragStartY = 0;
      var startLeft = 0;
      var startTop = 0;

      treeScrollArea.addEventListener('mousedown', function (event) {
        if (event.button !== 0 || event.target.closest('button, input, a')) return;
        isDragging = true;
        dragStartX = event.clientX;
        dragStartY = event.clientY;
        startLeft = treeScrollArea.scrollLeft;
        startTop = treeScrollArea.scrollTop;
        treeScrollArea.classList.add('is-dragging');
      });

      window.addEventListener('mousemove', function (event) {
        if (!isDragging) return;
        treeScrollArea.scrollLeft = startLeft - (event.clientX - dragStartX);
        treeScrollArea.scrollTop = startTop - (event.clientY - dragStartY);
      });

      window.addEventListener('mouseup', function () {
        isDragging = false;
        treeScrollArea.classList.remove('is-dragging');
      });
    }

    var zoomTarget = document.querySelector('[data-tree-zoom]');
    var zoomValue = document.querySelector('[data-zoom-value]');
    var zoomIn = document.querySelector('[data-zoom-in]');
    var zoomOut = document.querySelector('[data-zoom-out]');
    var treeScale = 0.9;

    function applyTreeZoom() {
      if (!zoomTarget) return;
      treeScale = Math.min(1.4, Math.max(0.45, Number(treeScale.toFixed(2))));
      zoomTarget.style.transform = 'scale(' + treeScale + ')';
      if (zoomValue) {
        zoomValue.textContent = Math.round(treeScale * 100) + '%';
      }
    }

    if (zoomTarget) {
      applyTreeZoom();
    }

    if (zoomIn) {
      zoomIn.addEventListener('click', function () {
        treeScale += 0.1;
        applyTreeZoom();
      });
    }

    if (zoomOut) {
      zoomOut.addEventListener('click', function () {
        treeScale -= 0.1;
        applyTreeZoom();
      });
    }

    var treeSearchInput = document.querySelector('[data-tree-search-input]');
    var treeSearchButton = document.querySelector('[data-tree-search-submit]');

    function runTreeSearch() {
      if (!treeSearchInput || !treeScrollArea) return;
      var query = treeSearchInput.value.trim().toLowerCase();
      if (!query) return;

      var nodes = Array.prototype.slice.call(document.querySelectorAll('.tree-v2-node[data-search-name]'));
      var target = nodes.find(function (node) {
        return (node.getAttribute('data-search-name') || '').toLowerCase() === query;
      }) || nodes.find(function (node) {
        return (node.getAttribute('data-search-name') || '').toLowerCase().includes(query);
      });

      document.querySelectorAll('.tree-v2-node.is-search-hit').forEach(function (node) {
        node.classList.remove('is-search-hit');
      });

      if (!target) return;

      var areaRect = treeScrollArea.getBoundingClientRect();
      var targetRect = target.getBoundingClientRect();
      treeScrollArea.scrollTo({
        left: treeScrollArea.scrollLeft + (targetRect.left - areaRect.left) - ((areaRect.width - targetRect.width) / 2),
        top: treeScrollArea.scrollTop + (targetRect.top - areaRect.top) - ((areaRect.height - targetRect.height) / 2),
        behavior: 'smooth'
      });
      target.classList.add('is-search-hit');
      target.focus({ preventScroll: true });
    }

    if (treeSearchButton) {
      treeSearchButton.addEventListener('click', runTreeSearch);
    }

    if (treeSearchInput) {
      treeSearchInput.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        runTreeSearch();
      });
    }
  </script>
</body>
</html>
