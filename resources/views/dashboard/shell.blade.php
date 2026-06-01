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
  $profilePhoto = $user->foto_profil
      ? (filter_var($user->foto_profil, FILTER_VALIDATE_URL) ? $user->foto_profil : asset(ltrim($user->foto_profil, '/')))
      : asset('images/default-user-profile.svg');
  $activePage = $activePage ?? 'dashboard';
  $selectedKampus = $selectedKampus ?? null;
  $maxRoleCount = max(array_values($roleCounts));
  $isSwitchingAccess = session()->has('impersonator_super_admin_id');
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

    .nav-group {
      display: grid;
      gap: 6px;
    }

    .nav-group summary {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      align-items: center;
      padding: 10px 12px;
      border-radius: 8px;
      color: #33413d;
      font-weight: 800;
      cursor: pointer;
      list-style: none;
    }

    .nav-group summary::-webkit-details-marker {
      display: none;
    }

    .nav-group summary:hover,
    .nav-group summary.active {
      background: var(--green-soft);
      color: var(--green);
    }

    .nav-chevron {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex: 0 0 auto;
      width: 24px;
      height: 24px;
      border-radius: 999px;
      background: rgba(15, 118, 110, 0.1);
      transition: background 0.16s ease;
    }

    .nav-chevron::before {
      content: "";
      width: 7px;
      height: 7px;
      margin-top: -3px;
      border-right: 2px solid currentColor;
      border-bottom: 2px solid currentColor;
      transform: rotate(45deg);
      transition: transform 0.16s ease, margin 0.16s ease;
    }

    .nav-group[open] .nav-chevron {
      background: rgba(15, 118, 110, 0.16);
    }

    .nav-group[open] .nav-chevron::before {
      margin-top: 3px;
      transform: rotate(225deg);
    }

    .nav-submenu {
      display: grid;
      gap: 4px;
      margin: 2px 0 4px 10px;
      padding: 4px 0 4px 12px;
      border-left: 1px solid var(--line);
    }

    .nav-submenu a {
      min-width: 0;
      padding: 8px 10px;
      font-size: 13px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .nav-empty {
      padding: 8px 10px;
      color: var(--muted);
      font-size: 13px;
      font-weight: 700;
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

    .btn.icon-btn {
      width: 34px;
      min-width: 34px;
      height: 34px;
      min-height: 34px;
      padding: 0;
      border-radius: 8px;
    }

    .btn.icon-btn svg {
      display: block;
      width: 17px;
      height: 17px;
      fill: none;
      stroke: currentColor;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .btn.icon-btn.is-danger {
      color: var(--danger);
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

    .user-menu {
      position: relative;
      width: 225px;
      flex: 0 0 auto;
    }

    .user-chip {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: 9px;
      width: 100%;
      min-height: 65px;
      padding: 9px 12px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.76);
      text-align: left;
      overflow: hidden;
      cursor: pointer;
    }

    .user-chip:hover,
    .user-chip:focus-visible {
      border-color: var(--green);
      box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
    }

    .user-chip-avatar {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex: 0 0 39px;
      width: 39px;
      height: 39px;
      border: 2px solid var(--line);
      border-radius: 50%;
      background: linear-gradient(180deg, #ffffff, #f3f7f5);
      box-shadow: inset 0 0 0 3px rgba(255, 255, 255, 0.92);
      overflow: hidden;
    }

    .user-chip-avatar img {
      display: block;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .user-chip-body {
      flex: 1;
      min-width: 0;
    }

    .user-chip-body strong,
    .user-chip-body span {
      display: block;
    }

    .user-chip-body strong {
      color: var(--navy);
      font-size: 16px;
      line-height: 1.2;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .user-chip-body span {
      margin-top: 4px;
      color: var(--muted);
      font-size: 12px;
      line-height: 1.2;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .user-dropdown {
      position: absolute;
      top: calc(100% + 8px);
      right: 0;
      z-index: 20;
      width: 100%;
      padding: 6px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: #fff;
      box-shadow: var(--shadow);
    }

    .user-dropdown[hidden] {
      display: none;
    }

    .user-dropdown a,
    .user-dropdown button {
      display: flex;
      align-items: center;
      width: 100%;
      min-height: 38px;
      padding: 8px 10px;
      border: 0;
      border-radius: 7px;
      background: transparent;
      color: #33413d;
      font-weight: 800;
      text-align: left;
      cursor: pointer;
    }

    .user-dropdown a:hover,
    .user-dropdown button:hover {
      background: var(--green-soft);
      color: var(--green);
    }

    .user-dropdown form {
      margin: 0;
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

    .table-panel-head {
      align-items: flex-end;
      padding-bottom: 4px;
    }

    .table-panel-title {
      min-width: 0;
    }

    .table-panel-actions {
      flex: 1 1 auto;
      justify-content: flex-end;
      align-items: center;
      flex-wrap: nowrap;
      min-width: 0;
    }

    .table-panel-actions .search {
      flex: 1 1 340px;
      width: min(440px, 100%);
      max-width: 440px;
      min-width: 240px;
      height: 42px;
      min-height: 42px;
    }

    .table-panel-actions .btn {
      flex: 0 0 auto;
      height: 42px;
      min-height: 42px;
      padding-top: 0;
      padding-bottom: 0;
      white-space: nowrap;
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

    .toast-stack {
      position: fixed;
      right: 20px;
      bottom: 20px;
      z-index: 1600;
      display: grid;
      gap: 10px;
      width: min(420px, calc(100vw - 32px));
      pointer-events: none;
    }

    .toast-alert {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      align-items: flex-start;
      margin: 0;
      box-shadow: 0 18px 48px rgba(15, 37, 68, 0.18);
      pointer-events: auto;
      animation: toast-slide-in 0.28s ease-out both;
      transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .toast-alert.is-hiding {
      opacity: 0;
      transform: translateY(8px);
    }

    .toast-close {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex: 0 0 auto;
      width: 28px;
      height: 28px;
      padding: 0 0 2px;
      border: 0;
      border-radius: 8px;
      background: rgba(15, 118, 110, 0.1);
      color: var(--green);
      font-family: Arial, sans-serif;
      font-size: 20px;
      font-weight: 900;
      line-height: 20px;
      cursor: pointer;
    }

    .toast-close:hover,
    .toast-close:focus-visible {
      background: rgba(15, 118, 110, 0.18);
      outline: none;
    }

    @keyframes toast-slide-in {
      from {
        opacity: 0;
        transform: translateX(28px);
      }

      to {
        opacity: 1;
        transform: translateX(0);
      }
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

    .panel-actions {
      margin-top: 14px;
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

    .tree-v2-node.is-actionable {
      cursor: pointer;
    }

    .tree-v2-node.is-actionable:hover {
      border-color: var(--gold);
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

    .tree-v2-empty-node {
      align-items: center;
      justify-content: center;
      background: #f8fafc;
      border-style: dashed;
      color: var(--muted);
      text-align: center;
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

    .tree-v2-level-groups {
      --tree-v2-gap: 14px;
    }

    .tree-action-buttons {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
      margin-top: 0;
    }

    .tree-action-buttons [hidden] {
      display: none !important;
    }

    .tree-action-detail {
      margin-top: 14px;
      padding: 12px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: #f8fbf9;
      color: var(--muted);
      line-height: 1.55;
    }

    .tree-action-detail strong {
      display: block;
      margin-bottom: 4px;
      color: var(--navy);
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

      .table-panel-head {
        gap: 12px;
      }

      .table-panel-actions {
        flex-direction: row;
      }

      .table-panel-actions .search {
        max-width: none;
        min-width: 0;
      }

      .user-chip {
        justify-content: flex-start;
      }

      .user-menu {
        align-self: flex-start;
        width: min(225px, 100%);
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

      .tree-action-buttons {
        grid-template-columns: 1fr;
      }
    }

    .sidebar-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 18px;
    }

    .sidebar-head .brand {
      min-width: 0;
      margin-bottom: 0;
    }

    .sidebar-toggle,
    .sidebar-close,
    .sidebar-backdrop {
      display: none;
    }

    .sidebar-toggle,
    .sidebar-close {
      align-items: center;
      justify-content: center;
      flex: 0 0 auto;
      width: 42px;
      height: 42px;
      border: 1px solid rgba(15, 118, 110, 0.22);
      border-radius: 8px;
      background: #fff;
      color: var(--green);
      box-shadow: var(--shadow);
      cursor: pointer;
    }

    .sidebar-toggle:hover,
    .sidebar-close:hover,
    .sidebar-toggle:focus-visible,
    .sidebar-close:focus-visible {
      border-color: rgba(15, 118, 110, 0.42);
      background: var(--green-soft);
      outline: none;
    }

    .sidebar-toggle-bars {
      display: grid;
      gap: 4px;
      width: 18px;
    }

    .sidebar-toggle-bars span {
      display: block;
      height: 2px;
      border-radius: 999px;
      background: currentColor;
    }

    .sidebar-close {
      font-size: 20px;
      font-weight: 900;
      line-height: 1;
    }

    @media (max-width: 900px) {
      .app-shell {
        display: block;
      }

      .sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        z-index: 1200;
        width: min(320px, calc(100vw - 44px));
        min-height: 100dvh;
        max-height: 100dvh;
        overflow-y: auto;
        transform: translateX(-105%);
        transition: transform 0.2s ease;
      }

      body.sidebar-open .sidebar {
        transform: translateX(0);
      }

      body.sidebar-open {
        overflow: hidden;
      }

      .sidebar-toggle,
      .sidebar-close {
        display: inline-flex;
      }

      .sidebar-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1190;
        width: 100%;
        height: 100%;
        border: 0;
        background: rgba(15, 37, 68, 0.42);
        cursor: pointer;
      }

      .sidebar-backdrop:not([hidden]) {
        display: block;
      }

      .main {
        width: 100%;
      }

      .topbar {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        padding-top: 10px;
      }

      .topbar-title {
        grid-column: 1 / -1;
        grid-row: 2;
        min-width: 0;
      }

      .user-menu {
        grid-column: 3;
        grid-row: 1;
        justify-self: end;
        width: auto;
      }

      .user-chip {
        width: 46px;
        min-height: 46px;
        padding: 3px;
        border-radius: 999px;
        gap: 0;
      }

      .user-chip-avatar {
        flex-basis: 38px;
        width: 38px;
        height: 38px;
      }

      .user-chip-body {
        display: none;
      }

      .user-dropdown {
        width: 180px;
      }
    }

    @media (min-width: 901px) {
      .sidebar {
        transform: none !important;
      }
    }

    .sidebar .nav {
      grid-template-columns: 1fr !important;
    }

    @media (max-width: 560px) {
      .topbar {
        grid-template-columns: 42px minmax(0, 1fr) 46px;
        gap: 10px;
        margin-bottom: 18px;
        padding: 8px 0 16px;
      }

      .sidebar-toggle {
        width: 42px;
        height: 42px;
      }

      .topbar h1 {
        margin-top: 4px;
        font-size: clamp(1.75rem, 10vw, 2.55rem);
        line-height: 1.05;
      }

      .topbar p {
        font-size: 15px;
        line-height: 1.55;
      }

      .topbar .eyebrow {
        font-size: 11px;
      }

      .nav {
        grid-template-columns: 1fr;
      }

      .toast-stack {
        right: 12px;
        bottom: 12px;
        width: calc(100vw - 24px);
      }
    }
  </style>
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar" id="dashboard-sidebar" data-sidebar>
      <div class="sidebar-head">
        <a class="brand" href="{{ route('dashboard') }}">
          <img src="{{ asset('images/ktb_logo.png') }}" alt="Logo KTB">
          <span>
            <strong>Sistem KTB</strong>
            <small>Perkantas Surabaya</small>
          </span>
        </a>
        <button class="sidebar-close" type="button" data-sidebar-close aria-label="Tutup navigasi">x</button>
      </div>

      <nav class="nav" aria-label="Navigasi dashboard">
        <a class="{{ $activePage === 'dashboard' ? 'active' : '' }}" href="{{ route($dashboard['route']) }}">Dashboard</a>
        @if ($canSeeAdminData)
          @if ($user->isSuperAdmin())
            <a class="{{ $activePage === 'pengguna' ? 'active' : '' }}" href="{{ route('dashboard.pengguna') }}">Pengguna</a>
            <a class="{{ $activePage === 'regio' ? 'active' : '' }}" href="{{ route('dashboard.regio') }}">Regio</a>
          @else
            @php
              $isCampusNavOpen = in_array($activePage, ['kampus', 'kampus-detail'], true);
            @endphp
            <details class="nav-group" {{ $isCampusNavOpen ? 'open' : '' }}>
              <summary class="{{ $isCampusNavOpen ? 'active' : '' }}">
                <span>Kampus</span>
                <span class="nav-chevron" aria-hidden="true"></span>
              </summary>
              <div class="nav-submenu">
                @forelse ($campusOptions as $kampusOption)
                  <a
                    class="{{ $activePage === 'kampus-detail' && $selectedKampus && (int) $selectedKampus->kampus_id === (int) $kampusOption->kampus_id ? 'active' : '' }}"
                    href="{{ route('dashboard.kampus.show', $kampusOption) }}"
                    title="{{ $kampusOption->nama_kampus }}"
                  >
                    {{ $kampusOption->singkatan ?: $kampusOption->nama_kampus }}
                  </a>
                @empty
                  <span class="nav-empty">Belum ada kampus</span>
                @endforelse
              </div>
            </details>
            <a class="{{ $activePage === 'anggota-ktb' ? 'active' : '' }}" href="{{ route('dashboard.anggota-ktb') }}">Anggota KTB</a>
            <a class="{{ $activePage === 'pohon' ? 'active' : '' }}" href="{{ route('dashboard.pohon') }}">Pohon</a>
          @endif
        @else
          <a href="{{ route($dashboard['route']) }}#profil-akun">Profil Akun</a>
        @endif
      </nav>

    </aside>
    <button class="sidebar-backdrop" type="button" data-sidebar-backdrop hidden aria-label="Tutup navigasi"></button>

    <main class="main">
      <header class="topbar">
        <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="dashboard-sidebar" aria-expanded="false" aria-label="Buka navigasi">
          <span class="sidebar-toggle-bars" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
          </span>
        </button>
        <div class="topbar-title">
          <span class="eyebrow">{{ $dashboard['eyebrow'] }}</span>
          <h1>{{ $dashboard['title'] }}</h1>
          <p>{{ $dashboard['subtitle'] }}</p>
        </div>
        <div class="user-menu" data-user-menu>
          <button class="user-chip" type="button" data-user-menu-toggle aria-haspopup="true" aria-expanded="false">
            <span class="user-chip-avatar">
              <img src="{{ $profilePhoto }}" alt="Foto profil {{ $user->nama_lengkap }}">
            </span>
            <span class="user-chip-body">
              <strong>{{ $user->nama_lengkap }}</strong>
              <span>{{ $roleNames[$user->role] ?? strtoupper($user->role) }} - {{ $kampusShort }}</span>
            </span>
          </button>
          <div class="user-dropdown" data-user-menu-dropdown hidden>
            <a href="{{ route($dashboard['route']) }}#profil-akun">Profil</a>
            @if ($isSwitchingAccess)
              <form method="POST" action="{{ route('dashboard.access.return') }}">
                @csrf
                <button type="submit">Kembali</button>
              </form>
            @endif
            @unless ($isSwitchingAccess)
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Logout</button>
              </form>
            @endunless
          </div>
        </div>
      </header>

      @if (session('success'))
        <div class="toast-stack" aria-live="polite" aria-atomic="true">
          <div class="alert toast-alert" role="status" data-auto-dismiss="5000">
            <span>{{ session('success') }}</span>
            <button class="toast-close" type="button" data-toast-close aria-label="Tutup notifikasi">x</button>
          </div>
        </div>
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
          @if ($user->isSuperAdmin())
            @php
              $adminPreviewRows = $userRows->where('role', 'admin')->take(5);
              $regioPreviewRows = $regioRows->take(5);
            @endphp
            <article class="panel">
              <div class="panel-head">
                <div>
                  <span class="eyebrow">Akses Sistem</span>
                  <h2>Admin Regio Terbaru</h2>
                </div>
                <a class="btn is-compact" href="{{ route('dashboard.pengguna') }}">Kelola Pengguna</a>
              </div>
              @if ($adminPreviewRows->isEmpty())
                <div class="empty-state">Belum ada akun admin regio.</div>
              @else
                <ul class="member-list">
                  @foreach ($adminPreviewRows as $adminRow)
                    <li>
                      <div class="member-entry">
                        <strong>{{ $adminRow->nama_lengkap }}</strong>
                        <span>{{ $adminRow->username }} - {{ $adminRow->regio?->nama_regio ?: 'Tanpa regio' }}</span>
                      </div>
                      <span class="badge neutral">{{ ucfirst($adminRow->admin_tipe ?: 'admin') }}</span>
                    </li>
                  @endforeach
                </ul>
              @endif
            </article>

            <article class="panel">
              <div class="panel-head">
                <div>
                  <span class="eyebrow">Wilayah</span>
                  <h2>Regio Pelayanan</h2>
                </div>
                <a class="btn is-compact" href="{{ route('dashboard.regio') }}">Kelola Regio</a>
              </div>
              @if ($regioPreviewRows->isEmpty())
                <div class="empty-state">Belum ada data regio.</div>
              @else
                <div class="detail-list">
                  @foreach ($regioPreviewRows as $regio)
                    <div class="detail-row">
                      <span class="detail-label">{{ $regio->nama_regio }}</span>
                      <span class="detail-value">{{ number_format($regio->active_users, 0, ',', '.') }} aktif / {{ number_format($regio->total_users, 0, ',', '.') }} akun</span>
                    </div>
                  @endforeach
                </div>
              @endif
            </article>
          @elseif ($user->isAdmin())
            @php
              $memberRoleCounts = [
                'pkk' => $roleCounts['pkk'] ?? 0,
                'akk' => $roleCounts['akk'] ?? 0,
              ];
              $maxMemberRoleCount = max(1, ...array_values($memberRoleCounts));
              $activeCampusCount = $campusSummaries->where('is_active', true)->count();
              $activeGroupCount = $treeGroups->sum('groups_count');
            @endphp
            <article class="panel">
              <div class="panel-head">
                <div>
                  <span class="eyebrow">Komposisi KTB</span>
                  <h2>PKK dan AKK di Regio Ini</h2>
                </div>
              </div>
              <div class="role-stack">
                @foreach ($memberRoleCounts as $role => $count)
                  @php
                    $width = round(($count / $maxMemberRoleCount) * 100);
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

            <article class="panel">
              <div class="panel-head">
                <div>
                  <span class="eyebrow">Operasional</span>
                  <h2>Status Regio {{ $user->regio?->nama_regio ?: '' }}</h2>
                </div>
                <span class="badge neutral">{{ ucfirst($user->admin_tipe ?: 'admin') }}</span>
              </div>
              <div class="detail-list">
                <div class="detail-row">
                  <span class="detail-label">Regio</span>
                  <span class="detail-value">{{ $user->regio?->nama_regio ?: 'Belum ada regio' }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Kampus Aktif</span>
                  <span class="detail-value">{{ number_format($activeCampusCount, 0, ',', '.') }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Kelompok</span>
                  <span class="detail-value">{{ number_format($activeGroupCount, 0, ',', '.') }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Akses Data</span>
                  <span class="detail-value">Regio sendiri</span>
                </div>
              </div>
              <div class="row-actions panel-actions">
                <a class="btn is-compact" href="#ringkasan-kampus">Kampus</a>
                <a class="btn is-compact" href="{{ route('dashboard.anggota-ktb') }}">Anggota KTB</a>
                <a class="btn is-compact" href="{{ route('dashboard.pohon') }}">Pohon</a>
              </div>
            </article>
          @endif
        </section>

        <section class="content-grid">
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
              @if ($user->isSuperAdmin())
                <div class="detail-row">
                  <span class="detail-label">Cakupan</span>
                  <span class="detail-value">Seluruh regio</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Role</span>
                  <span class="detail-value">{{ $roleNames[$user->role] ?? strtoupper($user->role) }}</span>
                </div>
              @elseif ($user->isAdmin())
                <div class="detail-row">
                  <span class="detail-label">Regio</span>
                  <span class="detail-value">{{ $user->regio?->nama_regio ?: 'Belum ada regio' }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Tipe Admin</span>
                  <span class="detail-value">{{ ucfirst($user->admin_tipe ?: '-') }}</span>
                </div>
              @else
                <div class="detail-row">
                  <span class="detail-label">Kampus</span>
                  <span class="detail-value">{{ $kampusName }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Angkatan</span>
                  <span class="detail-value">{{ $user->angkatan ?: '-' }}</span>
                </div>
              @endif
            </div>
          </article>

          @if ($user->isSuperAdmin())
            <article class="panel">
              <div class="panel-head">
                <div>
                  <span class="eyebrow">Ruang Kerja</span>
                  <h2>Kontrol Akses Pusat</h2>
                </div>
              </div>
              <div class="detail-list">
                <div class="detail-row">
                  <span class="detail-label">Fokus</span>
                  <span class="detail-value">Admin dan regio</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Akses Operasional</span>
                  <span class="detail-value">Melalui pindah akses admin</span>
                </div>
              </div>
              <div class="row-actions panel-actions">
                <a class="btn is-compact" href="{{ route('dashboard.pengguna') }}">Pengguna</a>
                <a class="btn is-compact" href="{{ route('dashboard.regio') }}">Regio</a>
              </div>
            </article>
          @elseif ($user->isAdmin())
            <article class="panel">
              <div class="panel-head">
                <div>
                  <span class="eyebrow">Ruang Kerja</span>
                  <h2>Kelola Data Pemuridan</h2>
                </div>
              </div>
              <div class="detail-list">
                <div class="detail-row">
                  <span class="detail-label">Mode</span>
                  <span class="detail-value">{{ $user->isAdminEditor() ? 'Editor' : 'Pelihat' }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Batas Data</span>
                  <span class="detail-value">{{ $user->regio?->nama_regio ?: 'Regio sendiri' }}</span>
                </div>
              </div>
              <div class="row-actions panel-actions">
                <a class="btn is-compact" href="{{ route('dashboard.anggota-ktb') }}">Lihat Anggota</a>
                <a class="btn is-compact" href="{{ route('dashboard.pohon') }}">Lihat Pohon</a>
              </div>
            </article>
          @endif
        </section>

        @if ($user->isAdmin())
          @include('dashboard.partials.campus-summary-table')
        @endif
      @elseif ($activePage === 'kampus' && $canSeeAdminData)
        <section class="panel" id="ringkasan-kampus">
          <div class="panel-head table-panel-head">
            <div class="table-panel-title">
              <span class="eyebrow">Ringkasan</span>
              <h2>Pengguna per Kampus</h2>
            </div>
            <div class="row-actions table-panel-actions">
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
                    <th>Regio</th>
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
                      <td>{{ $kampus->regio?->nama_regio ?: '-' }}</td>
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
                            <button class="btn icon-btn" type="button" data-modal-open="modal-kampus-edit-{{ $kampus->kampus_id }}" title="Edit" aria-label="Edit {{ $kampus->nama_kampus }}">
                              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                              </svg>
                            </button>
                            <button class="btn icon-btn is-danger" type="button" data-modal-open="modal-kampus-delete-{{ $kampus->kampus_id }}" title="Hapus" aria-label="Hapus {{ $kampus->nama_kampus }}">
                              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M3 6h18"></path>
                                <path d="M8 6V4h8v2"></path>
                                <path d="m19 6-1 14H6L5 6"></path>
                                <path d="M10 11v6"></path>
                                <path d="M14 11v6"></path>
                              </svg>
                            </button>
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
      @elseif ($activePage === 'kampus-detail' && $canSeeAdminData && $selectedKampus)
        <section class="metric-grid" aria-label="Ringkasan {{ $selectedKampus->nama_kampus }}">
          <article class="metric-card tone-primary">
            <span>Total Anggota</span>
            <strong>{{ number_format($selectedKampus->total_users, 0, ',', '.') }}</strong>
            <small>{{ $selectedKampus->nama_kampus }}</small>
          </article>
          <article class="metric-card tone-success">
            <span>Anggota Aktif</span>
            <strong>{{ number_format($selectedKampus->active_users, 0, ',', '.') }}</strong>
            <small>Akun aktif di kampus ini</small>
          </article>
          <article class="metric-card tone-info">
            <span>PKK</span>
            <strong>{{ number_format($selectedKampus->pkk_users, 0, ',', '.') }}</strong>
            <small>Pemimpin kelompok</small>
          </article>
          <article class="metric-card tone-warning">
            <span>Kelompok</span>
            <strong>{{ number_format($selectedKampus->groups_count, 0, ',', '.') }}</strong>
            <small>{{ number_format($selectedKampus->active_groups_count, 0, ',', '.') }} kelompok aktif</small>
          </article>
        </section>

        <section class="content-grid">
          <article class="panel">
            <div class="panel-head">
              <div>
                <span class="eyebrow">Profil Kampus</span>
                <h2>{{ $selectedKampus->nama_kampus }}</h2>
              </div>
              <span class="badge {{ $selectedKampus->is_active ? '' : 'warning' }}">
                {{ $selectedKampus->is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>
            <div class="detail-list">
              <div class="detail-row">
                <span class="detail-label">Singkatan</span>
                <span class="detail-value">{{ $selectedKampus->singkatan ?: '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Regio</span>
                <span class="detail-value">{{ $selectedKampus->regio?->nama_regio ?: '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">AKK</span>
                <span class="detail-value">{{ number_format($selectedKampus->akk_users, 0, ',', '.') }}</span>
              </div>
            </div>
          </article>

          <article class="panel">
            <div class="panel-head">
              <div>
                <span class="eyebrow">Kelompok</span>
                <h2>Kelompok Pemuridan</h2>
              </div>
              <span class="badge neutral">{{ number_format($selectedCampusGroups->count(), 0, ',', '.') }}</span>
            </div>
            @if ($selectedCampusGroups->isEmpty())
              <div class="empty-state">Belum ada kelompok di kampus ini.</div>
            @else
              <ul class="member-list">
                @foreach ($selectedCampusGroups as $group)
                  <li>
                    <div class="member-entry">
                      <strong>{{ $group->nama_kelompok }}</strong>
                      <span>Pemimpin {{ $group->pemimpin?->nama_lengkap ?: '-' }}</span>
                    </div>
                    <span class="badge {{ $group->is_active ? '' : 'warning' }}">
                      {{ number_format($group->anggota_count, 0, ',', '.') }} anggota
                    </span>
                  </li>
                @endforeach
              </ul>
            @endif
          </article>
        </section>

        <section class="panel" id="anggota-kampus">
          <div class="panel-head table-panel-head">
            <div class="table-panel-title">
              <span class="eyebrow">Direktori Kampus</span>
              <h2>Anggota {{ $selectedKampus->singkatan ?: $selectedKampus->nama_kampus }}</h2>
            </div>
            <div class="row-actions table-panel-actions">
              <input class="search" type="search" placeholder="Cari anggota kampus..." data-filter-table="campus-member-table" aria-label="Cari anggota kampus">
            </div>
          </div>

          @if ($selectedCampusMembers->isEmpty())
            <div class="empty-state">Belum ada anggota di kampus ini.</div>
          @else
            <div class="table-wrap">
              <table class="table" id="campus-member-table">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Angkatan</th>
                    <th>PKK</th>
                    <th>Kelompok</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($selectedCampusMembers as $member)
                    <tr>
                      <td><strong>{{ $member->nama_lengkap }}</strong></td>
                      <td>{{ $member->username }}</td>
                      <td><span class="badge neutral">{{ $roleNames[$member->role] ?? strtoupper($member->role) }}</span></td>
                      <td>{{ $member->angkatan ?: '-' }}</td>
                      <td>{{ $member->pkkLeader?->nama_lengkap ?: '-' }}</td>
                      <td>{{ $member->kelompokPemuridan?->nama_kelompok ?: '-' }}</td>
                      <td>
                        <span class="badge {{ $member->is_active ? '' : 'warning' }}">
                          {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </section>
      @elseif ($activePage === 'regio' && $canSeeAdminData)
        <section class="panel" id="daftar-regio">
          <div class="panel-head table-panel-head">
            <div class="table-panel-title">
              <span class="eyebrow">Wilayah</span>
              <h2>Daftar Regio</h2>
            </div>
            <div class="row-actions table-panel-actions">
              <input class="search" type="search" placeholder="Cari regio..." data-filter-table="regio-table" aria-label="Cari regio">
              @if ($canManageData)
                <button class="btn is-compact" type="button" data-modal-open="modal-regio-create">Tambah Regio</button>
              @endif
            </div>
          </div>

          @if ($canManageData)
            <div class="modal" id="modal-regio-create" hidden>
              <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-regio-create-title">
                <div class="modal-head">
                  <div>
                    <span class="eyebrow">Tambah</span>
                    <h2 id="modal-regio-create-title">Regio</h2>
                  </div>
                  <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                </div>
                <div class="modal-body">
                  @include('dashboard.partials.regio-form')
                </div>
              </div>
            </div>
          @endif

          @if ($regioRows->isEmpty())
            <div class="empty-state">Belum ada data regio.</div>
          @else
            <div class="table-wrap">
              <table class="table" id="regio-table">
                <thead>
                  <tr>
                    <th>Regio</th>
                    <th>Keterangan</th>
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
                  @foreach ($regioRows as $regio)
                    <tr>
                      <td><strong>{{ $regio->nama_regio }}</strong></td>
                      <td>{{ $regio->keterangan ?: '-' }}</td>
                      <td>
                        <span class="badge {{ $regio->is_active ? '' : 'warning' }}">
                          {{ $regio->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                      </td>
                      <td>{{ number_format($regio->total_users, 0, ',', '.') }}</td>
                      <td>{{ number_format($regio->active_users, 0, ',', '.') }}</td>
                      <td>{{ number_format($regio->pkk_users, 0, ',', '.') }}</td>
                      <td>{{ number_format($regio->akk_users, 0, ',', '.') }}</td>
                      @if ($canManageData)
                        <td>
                          <button class="btn icon-btn" type="button" data-modal-open="modal-regio-edit-{{ $regio->regio_id }}" title="Edit" aria-label="Edit {{ $regio->nama_regio }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                              <path d="M12 20h9"></path>
                              <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                            </svg>
                          </button>

                          <div class="modal" id="modal-regio-edit-{{ $regio->regio_id }}" hidden>
                            <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-regio-edit-title-{{ $regio->regio_id }}">
                              <div class="modal-head">
                                <div>
                                  <span class="eyebrow">Edit</span>
                                  <h2 id="modal-regio-edit-title-{{ $regio->regio_id }}">{{ $regio->nama_regio }}</h2>
                                </div>
                                <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                              </div>
                              <div class="modal-body">
                                @include('dashboard.partials.regio-form', ['regio' => $regio])
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
      @elseif (in_array($activePage, ['pengguna', 'anggota-ktb'], true) && $canSeeAdminData)
        @php
          $directoryRows = $activePage === 'anggota-ktb' ? $memberRows : $userRows;
          $directoryTitle = $activePage === 'anggota-ktb' ? 'Anggota KTB' : 'Daftar Pengguna';
          $directoryEyebrow = $activePage === 'anggota-ktb' ? 'Direktori KTB' : 'Direktori Admin';
          $directoryEmpty = $activePage === 'anggota-ktb' ? 'Belum ada data anggota KTB.' : 'Belum ada data pengguna admin.';
          $directorySearch = $activePage === 'anggota-ktb' ? 'Cari anggota KTB...' : 'Cari pengguna admin...';
          $directoryTableId = $activePage === 'anggota-ktb' ? 'member-table' : 'user-table';
          $directoryCanManageAdmins = $activePage === 'pengguna' && $canManageData;
        @endphp

        <section class="panel" id="{{ $activePage === 'anggota-ktb' ? 'daftar-anggota-ktb' : 'daftar-pengguna' }}">
          <div class="panel-head table-panel-head">
            <div class="table-panel-title">
              <span class="eyebrow">{{ $directoryEyebrow }}</span>
              <h2>{{ $directoryTitle }}</h2>
            </div>
            <div class="row-actions table-panel-actions">
              <input class="search" type="search" placeholder="{{ $directorySearch }}" data-filter-table="{{ $directoryTableId }}" aria-label="{{ $directorySearch }}">
              @if ($directoryCanManageAdmins)
                <button class="btn is-compact" type="button" data-modal-open="modal-admin-user-create">Tambah Admin</button>
              @endif
            </div>
          </div>

          @if ($directoryCanManageAdmins)
            <div class="modal" id="modal-admin-user-create" hidden>
              <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-admin-user-create-title">
                <div class="modal-head">
                  <div>
                    <span class="eyebrow">Tambah</span>
                    <h2 id="modal-admin-user-create-title">Pengguna Admin</h2>
                  </div>
                  <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                </div>
                <div class="modal-body">
                  @include('dashboard.partials.admin-user-form')
                </div>
              </div>
            </div>
          @endif

          @if ($directoryRows->isEmpty())
            <div class="empty-state">{{ $directoryEmpty }}</div>
          @else
            <div class="table-wrap">
              <table class="table" id="{{ $directoryTableId }}">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Regio</th>
                    <th>Kampus</th>
                    <th>Angkatan</th>
                    <th>Status</th>
                    @if ($directoryCanManageAdmins)
                      <th>Aksi</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                  @foreach ($directoryRows as $row)
                    <tr>
                      <td>
                        <strong>{{ $row->nama_lengkap }}</strong>
                        <div class="muted">Dibuat {{ $row->created_at?->format('d M Y') ?: '-' }}</div>
                      </td>
                      <td>{{ $row->username }}</td>
                      <td><span class="badge neutral">{{ $roleNames[$row->role] ?? strtoupper($row->role) }}</span></td>
                      <td>{{ $row->regio?->nama_regio ?: '-' }}</td>
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
                      @if ($directoryCanManageAdmins)
                        <td>
                          @if ($row->role === 'admin')
                            <div class="row-actions">
                              <form method="POST" action="{{ route('dashboard.pengguna.switch-access', $row) }}" class="inline-delete">
                                @csrf
                                <button class="btn icon-btn" type="submit" title="Pindah akses" aria-label="Pindah akses ke {{ $row->nama_lengkap }}">
                                  <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                    <path d="m10 17 5-5-5-5"></path>
                                    <path d="M15 12H3"></path>
                                  </svg>
                                </button>
                              </form>
                              <button class="btn icon-btn" type="button" data-modal-open="modal-admin-user-edit-{{ $row->user_id }}" title="Edit" aria-label="Edit {{ $row->nama_lengkap }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                  <path d="M12 20h9"></path>
                                  <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                </svg>
                              </button>
                              @if ($row->user_id !== $user->user_id)
                                <button class="btn icon-btn is-danger" type="button" data-modal-open="modal-admin-user-delete-{{ $row->user_id }}" title="Hapus" aria-label="Hapus {{ $row->nama_lengkap }}">
                                  <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M3 6h18"></path>
                                    <path d="M8 6V4h8v2"></path>
                                    <path d="m19 6-1 14H6L5 6"></path>
                                    <path d="M10 11v6"></path>
                                    <path d="M14 11v6"></path>
                                  </svg>
                                </button>
                              @endif
                            </div>

                            <div class="modal" id="modal-admin-user-edit-{{ $row->user_id }}" hidden>
                              <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-admin-user-edit-title-{{ $row->user_id }}">
                                <div class="modal-head">
                                  <div>
                                    <span class="eyebrow">Edit</span>
                                    <h2 id="modal-admin-user-edit-title-{{ $row->user_id }}">{{ $row->nama_lengkap }}</h2>
                                  </div>
                                  <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                                </div>
                                <div class="modal-body">
                                  @include('dashboard.partials.admin-user-form', ['adminUser' => $row])
                                </div>
                              </div>
                            </div>

                            @if ($row->user_id !== $user->user_id)
                              <div class="modal" id="modal-admin-user-delete-{{ $row->user_id }}" hidden>
                                <div class="modal-panel is-small" role="dialog" aria-modal="true" aria-labelledby="modal-admin-user-delete-title-{{ $row->user_id }}">
                                  <div class="modal-head">
                                    <div>
                                      <span class="eyebrow">Hapus</span>
                                      <h2 id="modal-admin-user-delete-title-{{ $row->user_id }}">Pengguna Admin</h2>
                                    </div>
                                    <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                                  </div>
                                  <div class="modal-body">
                                    <p class="muted">Hapus akun admin {{ $row->nama_lengkap }}? Akses login akun ini akan hilang.</p>
                                    <form method="POST" action="{{ route('dashboard.pengguna.destroy', $row) }}" class="inline-delete">
                                      @csrf
                                      @method('DELETE')
                                      <div class="form-actions">
                                        <button class="btn is-compact is-danger" type="submit">Hapus Pengguna</button>
                                        <button class="btn is-compact" type="button" data-modal-close>Batal</button>
                                      </div>
                                    </form>
                                  </div>
                                </div>
                              </div>
                            @endif
                          @else
                            <span class="muted">-</span>
                          @endif
                        </td>
                      @endif
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
                      @php
                        $groupCampusId = $group['campus_id'] ?? null;
                        $groupCampusLabel = $group['short'] !== '-' ? $group['short'] : $group['name'];
                        $groupMeta = $group['total'].' anggota - '.($group['groups_count'] ?? 0).' kelompok';
                      @endphp
                      <li class="tree-v2-item">
                        <article
                          class="tree-v2-node tree-v2-campus is-actionable"
                          data-tree-v2-node-action="campus"
                          data-node-name="{{ $groupCampusLabel }}"
                          data-node-meta="{{ $groupMeta }}"
                          data-campus-id="{{ $groupCampusId ?: '' }}"
                          data-campus-name="{{ $group['name'] }}"
                          data-search-name="{{ $group['name'] }} {{ $group['short'] }}"
                          tabindex="0"
                          role="button"
                          aria-label="Aksi untuk {{ $group['name'] }}"
                        >
                          <div class="tree-v2-node-head">
                            <div class="tree-v2-name" title="{{ $group['name'] }}">{{ $groupCampusLabel }}</div>
                            <span class="badge neutral">Kampus</span>
                          </div>
                          <div class="tree-v2-meta">{{ $groupMeta }}</div>
                        </article>

                        <ul class="tree-v2-children">
                          @if ($group['branches']->isEmpty() && $group['unassigned_akk']->isEmpty())
                            <li class="tree-v2-item">
                              <article
                                class="tree-v2-node tree-v2-empty-node is-actionable"
                                data-tree-v2-node-action="empty-campus"
                                data-node-name="Belum ada anggota"
                                data-node-meta="{{ $group['name'] }}"
                                data-campus-id="{{ $groupCampusId ?: '' }}"
                                data-campus-name="{{ $group['name'] }}"
                                data-search-name="{{ $group['name'] }} belum ada anggota"
                                tabindex="0"
                                role="button"
                                aria-label="Tambah anggota untuk {{ $group['name'] }}"
                              >
                                <div class="tree-v2-name">Belum ada anggota</div>
                                <div class="tree-v2-meta">Klik kampus untuk tambah anggota</div>
                              </article>
                            </li>
                          @endif

                          @foreach ($group['branches'] as $node)
                            @include('dashboard.partials.tree-person-node', [
                              'node' => $node,
                              'campusId' => $groupCampusId,
                              'campusName' => $group['name'],
                            ])
                          @endforeach

                          @if ($group['unassigned_akk']->isNotEmpty())
                            <li class="tree-v2-item">
                              <article
                                class="tree-v2-node tree-v2-group is-actionable"
                                data-tree-v2-node-action="unassigned-akk"
                                data-node-name="AKK tanpa PKK"
                                data-node-meta="{{ $group['unassigned_akk']->count() }} AKK - {{ $group['name'] }}"
                                data-campus-id="{{ $groupCampusId ?: '' }}"
                                data-campus-name="{{ $group['name'] }}"
                                data-search-name="AKK tanpa PKK {{ $group['name'] }}"
                                tabindex="0"
                                role="button"
                                aria-label="Aksi untuk AKK tanpa PKK {{ $group['name'] }}"
                              >
                                <div class="tree-v2-node-head">
                                  <div class="tree-v2-name">AKK tanpa PKK</div>
                                  <span class="badge">{{ $group['unassigned_akk']->count() }}</span>
                                </div>
                                <div class="tree-v2-meta">{{ $group['name'] }}</div>
                              </article>
                              <ul class="tree-v2-children tree-v2-level-members">
                                @foreach ($group['unassigned_akk'] as $row)
                                  <li class="tree-v2-item">
                                    <article
                                      class="tree-v2-node tree-v2-person is-akk is-actionable"
                                      data-tree-v2-node-action="akk"
                                      data-node-name="{{ $row->nama_lengkap }}"
                                      data-node-meta="{{ $row->username }}{{ $row->angkatan ? ' - '.$row->angkatan : '' }}"
                                      data-campus-id="{{ $row->kampus_id ?: $groupCampusId ?: '' }}"
                                      data-campus-name="{{ $row->kampus?->nama_kampus ?: $group['name'] }}"
                                      data-person-id="{{ $row->user_id }}"
                                      data-search-name="{{ $row->nama_lengkap }}"
                                      tabindex="0"
                                      role="button"
                                      aria-label="Aksi untuk {{ $row->nama_lengkap }}"
                                    >
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

        <div class="modal" id="tree-node-action-modal" data-tree-v2-action-modal hidden>
          <div class="modal-panel is-small" role="dialog" aria-modal="true" aria-labelledby="tree-node-action-title">
            <div class="modal-head">
              <div>
                <span class="eyebrow" data-tree-v2-action-type>Node</span>
                <h2 id="tree-node-action-title" data-tree-v2-action-title>Aksi Pohon</h2>
              </div>
              <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
            </div>
            <div class="modal-body">
              <div class="tree-action-buttons">
                @if ($canManageData)
                  <button class="btn is-compact" type="button" data-tree-v2-action-do="add_group" hidden>Tambah Kelompok</button>
                  <button class="btn is-compact" type="button" data-tree-v2-action-do="add_member" hidden>Tambah Anggota</button>
                @endif
                <button class="btn is-compact" type="button" data-tree-v2-action-do="view_detail">Lihat Detail</button>
              </div>
              <div class="tree-action-detail" data-tree-v2-action-detail hidden></div>
            </div>
          </div>
        </div>

        @if ($canManageData)
          <div class="modal" id="tree-group-create-modal" data-tree-group-create-modal hidden>
            <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="tree-group-create-title">
              <div class="modal-head">
                <div>
                  <span class="eyebrow">Tambah Kelompok</span>
                  <h2 id="tree-group-create-title">Kelompok Baru</h2>
                </div>
                <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
              </div>
              <div class="modal-body">
                <p class="muted" data-tree-group-context>{{ old('_tree_node_title', 'Pilih AKK/PKK di pohon untuk mengisi pemimpin kelompok.') }}</p>
                <form method="POST" action="{{ route('dashboard.pohon.kelompok.store') }}" class="compact-edit-form">
                  @csrf
                  <input type="hidden" name="_modal_id" value="tree-group-create-modal">
                  <input type="hidden" name="_tree_node_title" value="{{ old('_tree_node_title') }}" data-tree-group-context-input>
                  <input type="hidden" name="pemimpin_id" value="{{ old('pemimpin_id') }}" data-tree-group-leader-input>
                  <div class="form-grid">
                    <div class="field is-full">
                      <label for="tree-group-name">Nama Kelompok</label>
                      <input id="tree-group-name" type="text" name="nama_kelompok" value="{{ old('nama_kelompok') }}" maxlength="256" placeholder="Kosongkan untuk memakai nama pemimpin">
                    </div>
                    <label class="checkbox-field">
                      <input type="hidden" name="is_active" value="0">
                      <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                      Aktif
                    </label>
                  </div>
                  <div class="form-actions">
                    <button class="btn is-compact" type="submit">Simpan Kelompok</button>
                    <button class="btn is-compact" type="button" data-modal-close>Batal</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <div class="modal" id="tree-member-create-modal" data-tree-member-create-modal hidden>
            <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="tree-member-create-title">
              <div class="modal-head">
                <div>
                  <span class="eyebrow">Tambah Anggota</span>
                  <h2 id="tree-member-create-title">Anggota Baru</h2>
                </div>
                <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
              </div>
              <div class="modal-body">
                <p class="muted" data-tree-member-context>{{ old('_tree_node_title', 'Pilih kelompok di pohon untuk mengisi konteks anggota.') }}</p>
                <form method="POST" action="{{ route('dashboard.pohon.anggota.store') }}" class="compact-edit-form">
                  @csrf
                  <input type="hidden" name="_modal_id" value="tree-member-create-modal">
                  <input type="hidden" name="_tree_node_title" value="{{ old('_tree_node_title') }}" data-tree-member-context-input>
                  <input type="hidden" name="kelompok_id" value="{{ old('kelompok_id') }}" data-tree-member-group-input>
                  <div class="form-grid">
                    <div class="field">
                      <label for="tree-member-campus">Kampus</label>
                      <select id="tree-member-campus" name="kampus_id" data-tree-member-campus-select>
                        <option value="">Tanpa kampus</option>
                        @foreach ($campusOptions as $option)
                          <option value="{{ $option->kampus_id }}" {{ (string) old('kampus_id') === (string) $option->kampus_id ? 'selected' : '' }}>
                            {{ $option->nama_kampus }}{{ $option->singkatan ? ' ('.$option->singkatan.')' : '' }}{{ $user->isSuperAdmin() && $option->regio?->nama_regio ? ' - '.$option->regio->nama_regio : '' }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="field">
                      <label for="tree-member-angkatan">Angkatan</label>
                      <input id="tree-member-angkatan" type="number" name="angkatan" value="{{ old('angkatan') }}" min="1900" max="{{ date('Y') + 1 }}">
                    </div>
                    <div class="field is-full">
                      <label for="tree-member-name">Nama Anggota</label>
                      <input id="tree-member-name" type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" maxlength="256" required>
                    </div>
                    <label class="checkbox-field">
                      <input type="hidden" name="is_active" value="0">
                      <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                      Aktif
                    </label>
                  </div>
                  <div class="form-actions">
                    <button class="btn is-compact" type="submit">Simpan Anggota</button>
                    <button class="btn is-compact" type="button" data-modal-close>Batal</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        @endif
      @endif
    </main>
  </div>

  <script>
    var activeModal = null;
    var lastModalTrigger = null;
    var userMenu = document.querySelector('[data-user-menu]');
    var userMenuToggle = userMenu ? userMenu.querySelector('[data-user-menu-toggle]') : null;
    var userMenuDropdown = userMenu ? userMenu.querySelector('[data-user-menu-dropdown]') : null;
    var sidebar = document.querySelector('[data-sidebar]');
    var sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    var sidebarClose = document.querySelector('[data-sidebar-close]');
    var sidebarBackdrop = document.querySelector('[data-sidebar-backdrop]');

    function setSidebarOpen(isOpen) {
      document.body.classList.toggle('sidebar-open', isOpen);

      if (sidebarToggle) {
        sidebarToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      }

      if (sidebarBackdrop) {
        sidebarBackdrop.hidden = !isOpen;
      }
    }

    if (sidebarToggle && sidebar) {
      sidebarToggle.addEventListener('click', function () {
        setSidebarOpen(!document.body.classList.contains('sidebar-open'));
      });
    }

    if (sidebarClose) {
      sidebarClose.addEventListener('click', function () {
        setSidebarOpen(false);
      });
    }

    if (sidebarBackdrop) {
      sidebarBackdrop.addEventListener('click', function () {
        setSidebarOpen(false);
      });
    }

    if (sidebar) {
      sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
          if (window.matchMedia('(max-width: 900px)').matches) {
            setSidebarOpen(false);
          }
        });
      });
    }

    window.addEventListener('resize', function () {
      if (!window.matchMedia('(max-width: 900px)').matches) {
        setSidebarOpen(false);
      }
    });

    function closeUserMenu() {
      if (!userMenuDropdown || !userMenuToggle) return;
      userMenuDropdown.hidden = true;
      userMenuToggle.setAttribute('aria-expanded', 'false');
    }

    if (userMenuToggle && userMenuDropdown) {
      userMenuToggle.addEventListener('click', function () {
        var willOpen = userMenuDropdown.hidden;
        userMenuDropdown.hidden = !willOpen;
        userMenuToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      });

      document.addEventListener('click', function (event) {
        if (!userMenu.contains(event.target)) {
          closeUserMenu();
        }
      });

      window.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          closeUserMenu();
          setSidebarOpen(false);
        }
      });
    }

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

    var activeTreeNodeData = null;
    var treeActionModal = document.querySelector('[data-tree-v2-action-modal]');
    var treeActionTitle = treeActionModal ? treeActionModal.querySelector('[data-tree-v2-action-title]') : null;
    var treeActionType = treeActionModal ? treeActionModal.querySelector('[data-tree-v2-action-type]') : null;
    var treeActionDetail = treeActionModal ? treeActionModal.querySelector('[data-tree-v2-action-detail]') : null;
    var treeActionButtons = treeActionModal ? treeActionModal.querySelectorAll('[data-tree-v2-action-do]') : [];
    var treeGroupModal = document.querySelector('[data-tree-group-create-modal]');
    var treeGroupContext = treeGroupModal ? treeGroupModal.querySelector('[data-tree-group-context]') : null;
    var treeGroupContextInput = treeGroupModal ? treeGroupModal.querySelector('[data-tree-group-context-input]') : null;
    var treeGroupLeaderInput = treeGroupModal ? treeGroupModal.querySelector('[data-tree-group-leader-input]') : null;
    var treeMemberModal = document.querySelector('[data-tree-member-create-modal]');
    var treeMemberContext = treeMemberModal ? treeMemberModal.querySelector('[data-tree-member-context]') : null;
    var treeMemberContextInput = treeMemberModal ? treeMemberModal.querySelector('[data-tree-member-context-input]') : null;
    var treeMemberGroupInput = treeMemberModal ? treeMemberModal.querySelector('[data-tree-member-group-input]') : null;
    var treeMemberCampusSelect = treeMemberModal ? treeMemberModal.querySelector('[data-tree-member-campus-select]') : null;

    function treeNodeLabel(type) {
      var labels = {
        campus: 'Kampus',
        'empty-campus': 'Kampus',
        person: 'PKK',
        group: 'Kelompok',
        'legacy-group': 'Kelompok',
        akk: 'AKK',
        'unassigned-akk': 'Anggota tanpa PKK'
      };

      return labels[type] || 'Node';
    }

    function getTreeNodeData(node) {
      var data = node.dataset || {};

      return {
        type: data.treeV2NodeAction || '',
        name: data.nodeName || data.searchName || 'Node',
        meta: data.nodeMeta || '',
        campusId: data.campusId || '',
        campusName: data.campusName || '',
        personId: data.personId || '',
        groupId: data.groupId || '',
        leaderId: data.leaderId || '',
        leaderName: data.leaderName || ''
      };
    }

    function setTreeActionVisibility(action, visible) {
      Array.prototype.forEach.call(treeActionButtons, function (button) {
        if (button.getAttribute('data-tree-v2-action-do') === action) {
          button.hidden = !visible;
        }
      });
    }

    function renderTreeActionDetail(data) {
      if (!treeActionDetail) return;

      treeActionDetail.innerHTML = '';

      var title = document.createElement('strong');
      title.textContent = data.name || 'Node';
      treeActionDetail.appendChild(title);

      var typeLine = document.createElement('div');
      typeLine.textContent = 'Tipe: ' + treeNodeLabel(data.type);
      treeActionDetail.appendChild(typeLine);

      if (data.meta) {
        var metaLine = document.createElement('div');
        metaLine.textContent = data.meta;
        treeActionDetail.appendChild(metaLine);
      }

      var campusLine = document.createElement('div');
      campusLine.textContent = 'Kampus: ' + (data.campusName || 'Tanpa kampus');
      treeActionDetail.appendChild(campusLine);

      if (data.personId) {
        var idLine = document.createElement('div');
        idLine.textContent = 'ID pengguna: ' + data.personId;
        treeActionDetail.appendChild(idLine);
      }

      if (data.leaderName) {
        var leaderLine = document.createElement('div');
        leaderLine.textContent = 'Pemimpin: ' + data.leaderName;
        treeActionDetail.appendChild(leaderLine);
      }

      if (data.groupId) {
        var groupLine = document.createElement('div');
        groupLine.textContent = 'ID kelompok: ' + data.groupId;
        treeActionDetail.appendChild(groupLine);
      }
    }

    function openTreeActionModal(node) {
      if (!treeActionModal || !node) return;

      activeTreeNodeData = getTreeNodeData(node);

      if (treeActionTitle) {
        treeActionTitle.textContent = activeTreeNodeData.name;
      }

      if (treeActionType) {
        treeActionType.textContent = treeNodeLabel(activeTreeNodeData.type);
      }

      if (treeActionDetail) {
        treeActionDetail.hidden = true;
        renderTreeActionDetail(activeTreeNodeData);
      }

      var canAddGroup = (activeTreeNodeData.type === 'person' || activeTreeNodeData.type === 'akk') && Boolean(activeTreeNodeData.personId);
      var canAddMember = activeTreeNodeData.type === 'campus'
        || activeTreeNodeData.type === 'empty-campus'
        || (activeTreeNodeData.type === 'group' && Boolean(activeTreeNodeData.groupId));

      setTreeActionVisibility('add_group', canAddGroup);
      setTreeActionVisibility('add_member', canAddMember);
      setTreeActionVisibility('view_detail', true);

      openModal(treeActionModal, node);
    }

    function openTreeGroupModal() {
      if (!treeGroupModal || !activeTreeNodeData) return;

      var campusName = activeTreeNodeData.campusName || 'Tanpa kampus';
      var contextText = 'Pemimpin: ' + activeTreeNodeData.name + ' / ' + campusName;

      if (treeGroupContext) {
        treeGroupContext.textContent = contextText;
      }

      if (treeGroupContextInput) {
        treeGroupContextInput.value = contextText;
      }

      if (treeGroupLeaderInput) {
        treeGroupLeaderInput.value = activeTreeNodeData.personId || '';
      }

      closeModal(treeActionModal);
      openModal(treeGroupModal);
    }

    function openTreeMemberModal() {
      if (!treeMemberModal || !activeTreeNodeData) return;

      var campusId = activeTreeNodeData.campusId || '';
      var campusName = activeTreeNodeData.campusName || 'Tanpa kampus';
      var leaderName = activeTreeNodeData.leaderName || 'Tanpa pemimpin';
      var contextText = activeTreeNodeData.groupId
        ? 'Kelompok: ' + activeTreeNodeData.name + ' / Pemimpin: ' + leaderName + ' / ' + campusName
        : 'Kampus: ' + campusName;

      if (treeMemberContext) {
        treeMemberContext.textContent = contextText;
      }

      if (treeMemberContextInput) {
        treeMemberContextInput.value = contextText;
      }

      if (treeMemberGroupInput) {
        treeMemberGroupInput.value = activeTreeNodeData.groupId || '';
      }

      if (treeMemberCampusSelect) {
        treeMemberCampusSelect.value = campusId;
      }

      closeModal(treeActionModal);
      openModal(treeMemberModal);
    }

    document.querySelectorAll('[data-tree-v2-node-action]').forEach(function (node) {
      node.addEventListener('click', function (event) {
        event.preventDefault();
        openTreeActionModal(node);
      });

      node.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        event.preventDefault();
        openTreeActionModal(node);
      });
    });

    document.querySelectorAll('[data-tree-v2-action-do]').forEach(function (button) {
      button.addEventListener('click', function () {
        var action = button.getAttribute('data-tree-v2-action-do');

        if (action === 'add_group') {
          openTreeGroupModal();
          return;
        }

        if (action === 'add_member') {
          openTreeMemberModal();
          return;
        }

        if (action === 'view_detail' && treeActionDetail) {
          treeActionDetail.hidden = !treeActionDetail.hidden;
        }
      });
    });

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
        if (event.button !== 0 || event.target.closest('button, input, a, [data-tree-v2-node-action]')) return;
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

    function dismissToast(toast) {
      if (!toast || toast.classList.contains('is-hiding')) return;

        toast.classList.add('is-hiding');

        window.setTimeout(function () {
          var stack = toast.closest('.toast-stack');
          toast.remove();

          if (stack && !stack.children.length) {
            stack.remove();
          }
        }, 220);
    }

    document.querySelectorAll('[data-auto-dismiss]').forEach(function (toast) {
      var delay = Number(toast.getAttribute('data-auto-dismiss')) || 5000;

      window.setTimeout(function () {
        dismissToast(toast);
      }, delay);
    });

    document.querySelectorAll('[data-toast-close]').forEach(function (button) {
      button.addEventListener('click', function () {
        dismissToast(button.closest('[data-auto-dismiss]'));
      });
    });
  </script>
</body>
</html>
