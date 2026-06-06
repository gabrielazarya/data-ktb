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
  $profileRoleLabel = $user->isAdmin()
      ? ucfirst($user->admin_tipe ?: 'admin')
      : ($roleNames[$user->role] ?? strtoupper($user->role));
  $profileScopeLabel = $user->isAdmin()
      ? ($user->regio?->nama_regio ?: '-')
      : ($user->role === 'super_admin' ? 'Developer' : $kampusShort);
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
  <title>JejakMurid</title>
  <style>
    :root {
      --bg: #0b0f14;
      --panel: #151a21;
      --panel-strong: #1b222b;
      --text: #e7edf3;
      --muted: #95a3b4;
      --line: #2a3441;
      --navy: #f5f8fc;
      --navy-soft: #202a36;
      --gold: #f7b84b;
      --gold-soft: #372812;
      --green: #5eead4;
      --green-soft: #123832;
      --blue: #7ab7ff;
      --blue-soft: #172c45;
      --danger: #fb7185;
      --danger-soft: #391923;
      --shadow: 0 18px 48px rgba(0, 0, 0, 0.35);
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      min-height: 100vh;
      overflow-x: hidden;
      font-family: Inter, Manrope, "Segoe UI", Arial, sans-serif;
      color: var(--text);
      background:
        radial-gradient(circle at top left, rgba(94, 234, 212, 0.14), transparent 34vw),
        radial-gradient(circle at top right, rgba(122, 183, 255, 0.12), transparent 32vw),
        linear-gradient(135deg, #0b0f14 0%, #111820 48%, #0d1117 100%);
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
      width: 100%;
      max-width: 100vw;
      min-width: 0;
      min-height: 100vh;
      display: grid;
      grid-template-columns: 272px minmax(0, 1fr);
    }

    .sidebar {
      min-height: 100vh;
      height: 100vh;
      padding: 22px;
      border-right: 1px solid var(--line);
      background: rgba(255, 255, 255, 0.82);
      position: sticky;
      top: 0;
      align-self: start;
      overflow-y: auto;
      overscroll-behavior: contain;
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
      width: 100%;
      max-width: 100%;
      min-width: 0;
      overflow-x: hidden;
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

    .campus-tab-panel {
      display: grid;
      gap: 0;
      width: 100%;
      max-width: 100%;
      min-width: 0;
      padding: 0;
      overflow: visible;
    }

    .campus-tab-list {
      position: relative;
      display: flex;
      gap: 2px;
      align-items: flex-end;
      min-height: 50px;
      min-width: 0;
      max-width: 100%;
      padding: 8px 10px 0;
      overflow-x: auto;
      border: 1px solid #202a34;
      border-bottom: 0;
      border-radius: 10px 10px 0 0;
      background: linear-gradient(180deg, #242b33 0%, #161b20 100%);
      box-shadow: 0 14px 34px rgba(15, 37, 68, 0.13);
      scrollbar-width: none;
    }

    .campus-tab-list::-webkit-scrollbar {
      display: none;
    }

    .campus-tab-button {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      flex: 0 0 auto;
      width: auto;
      min-width: 150px;
      min-height: 42px;
      padding: 10px 18px 11px;
      border: 0;
      border-radius: 11px 11px 0 0;
      background: transparent;
      color: #cfd8dc;
      font-size: 13px;
      font-weight: 800;
      white-space: nowrap;
      cursor: pointer;
      transition: background 0.16s ease, color 0.16s ease, transform 0.16s ease;
    }

    .campus-tab-button::before {
      content: "";
      flex: 0 0 auto;
      width: 8px;
      height: 8px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.42);
      box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.06);
    }

    .campus-tab-button:not(.is-active)::after {
      content: "";
      position: absolute;
      top: 12px;
      right: -1px;
      bottom: 10px;
      width: 1px;
      background: rgba(255, 255, 255, 0.14);
    }

    .campus-tab-button:last-child:not(.is-active)::after {
      display: none;
    }

    .campus-tab-button:hover {
      background: rgba(255, 255, 255, 0.08);
      color: #ffffff;
      transform: translateY(-1px);
    }

    .campus-tab-button.is-active {
      z-index: 1;
      background: var(--panel);
      color: var(--navy);
      box-shadow: 0 -1px 0 rgba(255, 255, 255, 0.7), 0 10px 24px rgba(0, 0, 0, 0.18);
      transform: translateY(0);
    }

    .campus-tab-button.is-active::before {
      background: linear-gradient(135deg, var(--green), var(--gold));
      box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
    }

    .campus-tab-button.is-active::after {
      content: "";
      position: absolute;
      right: 0;
      bottom: -1px;
      left: 0;
      height: 2px;
      background: var(--panel);
    }

    .campus-tab-button:disabled {
      cursor: wait;
      opacity: 0.72;
    }

    .campus-tab-content {
      width: 100%;
      max-width: 100%;
      min-width: 0;
      min-height: 420px;
      overflow: hidden;
    }

    .campus-tab-panel .tree-v2-surface,
    .campus-tab-panel .campus-members-panel,
    .campus-tab-panel .campus-groups-panel {
      border-top-left-radius: 0;
      border-top-right-radius: 0;
    }

    .campus-tab-content.is-loading {
      opacity: 0.55;
      pointer-events: none;
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
      position: sticky;
      top: 0;
      z-index: 2;
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
    .field select,
    .field textarea {
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
    .field select:focus,
    .field textarea:focus {
      border-color: var(--green);
      box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
    }

    .field textarea {
      min-height: 88px;
      resize: vertical;
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

    .checkbox-stack {
      display: grid;
      gap: 8px;
      max-height: 160px;
      overflow: auto;
      padding: 10px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.76);
    }

    .report-form {
      display: grid;
      gap: 14px;
    }

    .report-form .form-grid {
      gap: 14px;
    }

    .report-form textarea {
      min-height: 74px;
    }

    .report-form .checkbox-stack {
      max-height: 150px;
    }

    .report-form .form-actions {
      position: sticky;
      bottom: -18px;
      margin: 0 -18px -18px;
      padding: 14px 18px 18px;
      border-top: 1px solid var(--line);
      background: linear-gradient(180deg, rgba(17, 24, 32, 0.92), #111820);
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

    .profile-summary {
      display: flex;
      gap: 14px;
      align-items: center;
      margin-bottom: 16px;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--line);
    }

    .profile-summary-avatar {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex: 0 0 74px;
      width: 74px;
      height: 74px;
      border: 2px solid var(--line);
      border-radius: 50%;
      background: linear-gradient(180deg, #ffffff, #f3f7f5);
      overflow: hidden;
    }

    .profile-summary-avatar img {
      display: block;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .profile-summary h3 {
      margin: 0;
      color: var(--navy);
      font-size: 1.2rem;
      line-height: 1.25;
    }

    .profile-summary p {
      margin: 5px 0 0;
      color: var(--muted);
      line-height: 1.4;
    }

    .table-wrap {
      width: 100%;
      max-width: 100%;
      min-width: 0;
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

    .pkk-group-list {
      display: grid;
      grid-template-columns: 1fr;
      gap: 18px;
    }

    .pkk-group-card {
      padding: 22px;
      margin-bottom: 0;
    }

    .pkk-group-card .panel-head {
      align-items: flex-start;
      padding-bottom: 14px;
      border-bottom: 1px solid var(--line);
    }

    .pkk-group-title {
      min-width: 0;
    }

    .pkk-group-title h2 {
      margin-top: 6px;
      font-size: clamp(1.35rem, 2vw, 1.9rem);
    }

    .pkk-summary-grid {
      display: grid;
      grid-template-columns: minmax(260px, 1.5fr) repeat(2, minmax(120px, 0.5fr));
      gap: 12px;
      margin: 16px 0;
    }

    .pkk-summary-item {
      min-width: 0;
      padding: 14px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.04);
    }

    .pkk-summary-item span,
    .pkk-section-label {
      display: block;
      margin-bottom: 6px;
      color: var(--muted);
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0;
      text-transform: uppercase;
    }

    .pkk-summary-item strong {
      display: block;
      color: var(--text);
      font-size: 1.05rem;
      line-height: 1.35;
      overflow-wrap: anywhere;
    }

    .pkk-summary-item.is-number strong {
      font-size: 1.55rem;
      line-height: 1;
    }

    .pkk-group-body {
      display: grid;
      grid-template-columns: minmax(280px, 0.85fr) minmax(0, 1.45fr);
      gap: 18px;
      align-items: start;
      margin-top: 18px;
    }

    .pkk-subpanel {
      min-width: 0;
      padding: 14px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.035);
    }

    .pkk-subpanel .member-list li:first-child {
      border-top: 0;
    }

    .pkk-member-list {
      max-height: 440px;
      overflow: auto;
      padding-right: 4px;
    }

    .pkk-report-empty {
      min-height: 160px;
      display: grid;
      align-items: center;
    }

    .pkk-report-table-wrap {
      border-radius: 8px;
    }

    .pkk-report-table {
      min-width: 680px;
      table-layout: fixed;
    }

    .pkk-report-table th,
    .pkk-report-table td {
      padding: 14px 16px;
    }

    .pkk-report-table th {
      white-space: nowrap;
    }

    .pkk-report-table .report-date-col {
      width: 140px;
    }

    .pkk-report-table .report-material-col {
      width: 38%;
    }

    .pkk-report-table .report-attendance-col {
      width: 34%;
    }

    .pkk-report-table .report-action-col {
      width: 104px;
    }

    .pkk-report-table .cell-main,
    .pkk-report-table .muted {
      overflow-wrap: anywhere;
    }

    .pkk-report-actions {
      flex-wrap: nowrap;
      align-items: center;
      gap: 6px;
    }

    .pkk-report-actions .icon-btn {
      width: 38px;
      min-width: 38px;
      height: 38px;
      min-height: 38px;
    }

    .campus-group-grid {
      display: grid;
      gap: 16px;
    }

    .campus-group-card {
      min-width: 0;
      padding: 16px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.035);
    }

    .campus-group-head {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      align-items: flex-start;
      padding-bottom: 14px;
      border-bottom: 1px solid var(--line);
    }

    .campus-group-head h3 {
      margin: 4px 0;
      color: var(--text);
      font-size: 1.18rem;
      line-height: 1.3;
    }

    .campus-group-head p {
      margin: 0;
      color: var(--muted);
      line-height: 1.45;
    }

    .campus-group-stats {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
      margin: 14px 0;
    }

    .campus-group-stats div {
      padding: 12px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.04);
    }

    .campus-group-stats span {
      display: block;
      margin-bottom: 5px;
      color: var(--muted);
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
    }

    .campus-group-stats strong {
      color: var(--text);
      font-size: 1.35rem;
      line-height: 1;
    }

    .campus-group-empty {
      margin-top: 12px;
    }

    .campus-report-table {
      min-width: 860px;
    }

    .campus-report-table th,
    .campus-report-table td {
      padding: 13px 14px;
    }

    .tree-v2-surface {
      width: 100%;
      max-width: 100%;
      min-width: 0;
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
      width: 100%;
      max-width: 100%;
      min-width: 0;
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

    body,
    .main {
      color-scheme: dark;
    }

    .sidebar {
      background: linear-gradient(180deg, rgba(21, 26, 33, 0.96), rgba(13, 17, 23, 0.98));
      box-shadow: 18px 0 44px rgba(0, 0, 0, 0.22);
    }

    .brand strong,
    .topbar h1,
    .panel h2,
    .metric-card strong,
    .sidebar-card strong,
    .role-row strong,
    .detail-value,
    .campus-role-head h2,
    .role-column h3,
    .profile-summary h3 {
      color: var(--navy);
    }

    .brand small,
    .eyebrow,
    .metric-card span,
    .detail-label,
    .table th,
    .topbar p,
    .metric-card small,
    .muted,
    .sidebar-card small,
    .member-list span,
    .profile-summary p {
      color: var(--muted);
    }

    .nav a,
    .nav-group summary,
    .btn,
    .user-dropdown a,
    .user-dropdown button {
      color: #cfd8e3;
    }

    .nav a:hover,
    .nav a.active,
    .nav-group summary:hover,
    .nav-group summary.active,
    .user-dropdown a:hover,
    .user-dropdown button:hover {
      background: linear-gradient(135deg, rgba(94, 234, 212, 0.16), rgba(122, 183, 255, 0.1));
      color: var(--green);
    }

    .nav-chevron,
    .toast-close {
      background: rgba(94, 234, 212, 0.12);
    }

    .sidebar-card,
    .panel,
    .metric-card,
    .campus-role-card,
    .user-chip,
    .user-dropdown,
    .modal-panel,
    .tree-v2-surface {
      background: linear-gradient(180deg, rgba(21, 26, 33, 0.96), rgba(17, 24, 32, 0.96));
      border-color: var(--line);
      box-shadow: var(--shadow);
    }

    .metric-card,
    .panel,
    .campus-role-card {
      border-top-color: rgba(94, 234, 212, 0.25);
    }

    .metric-card {
      background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.035), rgba(255, 255, 255, 0)),
        var(--panel);
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

    .btn,
    .search,
    .field input,
    .field select,
    .field textarea,
    .campus-tab-button,
    .user-chip {
      background: #111820;
      border-color: var(--line);
      color: var(--text);
    }

    .btn:hover,
    .btn:focus-visible {
      border-color: rgba(94, 234, 212, 0.52);
      background: #17242d;
      color: var(--green);
      outline: none;
      box-shadow: 0 0 0 3px rgba(94, 234, 212, 0.1);
    }

    .btn.is-danger:hover,
    .btn.icon-btn.is-danger:hover {
      border-color: rgba(251, 113, 133, 0.52);
      background: var(--danger-soft);
      color: var(--danger);
    }

    .search::placeholder,
    .field input::placeholder,
    .field textarea::placeholder {
      color: #748296;
    }

    .search:focus,
    .field input:focus,
    .field select:focus,
    .field textarea:focus,
    .user-chip:hover,
    .user-chip:focus-visible {
      border-color: rgba(94, 234, 212, 0.62);
      box-shadow: 0 0 0 3px rgba(94, 234, 212, 0.12);
    }

    .field label,
    .checkbox-field {
      color: #c7d2df;
    }

    .checkbox-stack {
      background: rgba(17, 24, 32, 0.82);
      border-color: var(--line);
      scrollbar-color: #516072 #111820;
    }

    .checkbox-stack .checkbox-field {
      min-height: 34px;
      padding: 7px 8px;
      border-radius: 6px;
      color: var(--text);
    }

    .checkbox-stack .checkbox-field:hover {
      background: rgba(94, 234, 212, 0.08);
      color: var(--green);
    }

    .checkbox-field input {
      accent-color: var(--green);
      flex: 0 0 auto;
    }

    option {
      background: #111820;
      color: var(--text);
    }

    .user-chip-avatar,
    .profile-summary-avatar {
      border-color: #3a4654;
      background: linear-gradient(180deg, #eef4f7, #d7e0e6);
      box-shadow: inset 0 0 0 3px rgba(255, 255, 255, 0.58);
    }

    .user-chip-body strong {
      color: var(--navy);
    }

    .user-chip-body span {
      color: var(--muted);
    }

    .user-dropdown {
      background: #111820;
    }

    .alert {
      background: rgba(94, 234, 212, 0.12);
      border-color: rgba(94, 234, 212, 0.24);
      color: var(--green);
    }

    .alert.is-danger {
      background: var(--danger-soft);
      border-color: rgba(251, 113, 133, 0.32);
      color: var(--danger);
    }

    .toast-alert {
      background: #111820;
      box-shadow: 0 18px 48px rgba(0, 0, 0, 0.42);
    }

    .modal {
      background: rgba(2, 6, 12, 0.68);
      backdrop-filter: blur(8px);
    }

    .modal-head,
    .tree-v2-toolbar {
      background: rgba(17, 24, 32, 0.96);
      border-color: var(--line);
    }

    .modal-head h2,
    .modal-close {
      color: var(--navy);
    }

    .table-wrap {
      background: rgba(17, 24, 32, 0.78);
      border-color: var(--line);
    }

    .table th {
      background: #111820;
      color: #c7d2df;
    }

    .table td {
      color: var(--text);
      border-color: var(--line);
    }

    .table tbody tr {
      background: rgba(21, 26, 33, 0.72);
    }

    .table tbody tr:hover {
      background: rgba(94, 234, 212, 0.055);
    }

    .badge {
      background: rgba(94, 234, 212, 0.12);
      color: var(--green);
      border: 1px solid rgba(94, 234, 212, 0.18);
    }

    .badge.warning {
      background: rgba(247, 184, 75, 0.12);
      color: var(--gold);
      border-color: rgba(247, 184, 75, 0.22);
    }

    .badge.neutral {
      background: rgba(122, 183, 255, 0.12);
      color: #b9d8ff;
      border-color: rgba(122, 183, 255, 0.2);
    }

    .empty-state,
    .tree-action-detail {
      background: rgba(17, 24, 32, 0.72);
      border-color: var(--line);
      color: var(--muted);
    }

    .role-track {
      background: #273241;
    }

    .detail-row,
    .member-list li,
    .profile-summary {
      border-color: var(--line);
    }

    .member-list strong,
    .member-list .badge.neutral {
      color: var(--navy);
    }

    .campus-tab-list {
      border-color: #202a34;
      background: linear-gradient(180deg, #242b33 0%, #161b20 100%);
    }

    .campus-tab-button {
      color: #cfd8dc;
    }

    .campus-tab-button:hover {
      background: rgba(255, 255, 255, 0.08);
      color: #ffffff;
      box-shadow: none;
    }

    .campus-tab-button.is-active {
      background: var(--panel);
      color: var(--navy);
    }

    .campus-tab-button.is-active::after {
      background: var(--panel);
    }

    .tree-v2-scroll {
      background:
        linear-gradient(rgba(255, 255, 255, 0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.025) 1px, transparent 1px),
        #0f141b;
      background-size: 32px 32px;
    }

    .tree-v2-node {
      border-color: #344254;
      background: linear-gradient(150deg, #1b2430, #141b24);
      box-shadow: 0 18px 34px rgba(0, 0, 0, 0.28);
    }

    .tree-v2-node:hover {
      box-shadow: 0 20px 38px rgba(0, 0, 0, 0.36);
    }

    .tree-v2-campus {
      background: linear-gradient(150deg, rgba(94, 234, 212, 0.18), rgba(20, 72, 65, 0.38));
      border-color: rgba(94, 234, 212, 0.45);
    }

    .tree-v2-group {
      background: linear-gradient(150deg, rgba(247, 184, 75, 0.17), rgba(84, 57, 18, 0.38));
      border-color: rgba(247, 184, 75, 0.45);
    }

    .tree-v2-person.is-pkk {
      background: linear-gradient(150deg, rgba(122, 183, 255, 0.18), rgba(24, 57, 96, 0.42));
      border-color: rgba(122, 183, 255, 0.5);
    }

    .tree-v2-person.is-akk {
      background: linear-gradient(150deg, rgba(94, 234, 212, 0.11), rgba(41, 58, 68, 0.46));
      border-color: rgba(94, 234, 212, 0.32);
    }

    .tree-v2-empty-node {
      background: #111820;
      color: var(--muted);
    }

    .tree-v2-name {
      color: var(--navy);
    }

    .tree-v2-meta {
      color: var(--muted);
    }

    .tree-v2-children::before,
    .tree-v2-children > .tree-v2-item::before,
    .tree-v2-children > .tree-v2-item::after {
      border-color: #3a4858;
    }

    ::selection {
      background: rgba(94, 234, 212, 0.28);
      color: #ffffff;
    }

    * {
      scrollbar-color: #3a4858 #0d1117;
    }

    *::-webkit-scrollbar {
      width: 10px;
      height: 10px;
    }

    *::-webkit-scrollbar-track {
      background: #0d1117;
    }

    *::-webkit-scrollbar-thumb {
      border: 2px solid #0d1117;
      border-radius: 999px;
      background: #3a4858;
    }

    *::-webkit-scrollbar-thumb:hover {
      background: #536174;
    }

    @media (max-width: 1040px) {
      .metric-grid,
      .content-grid,
      .management-grid,
      .table-toolbar {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .pkk-group-body {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 760px) {
      .app-shell {
        grid-template-columns: 1fr;
      }

      .sidebar {
        position: relative;
        min-height: auto;
        height: auto;
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

      .campus-tab-list {
        min-height: 48px;
        padding: 7px 8px 0;
      }

      .campus-tab-button {
        min-width: 142px;
        padding-right: 14px;
        padding-left: 14px;
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
      .form-grid,
      .pkk-summary-grid {
        grid-template-columns: 1fr;
      }

      .pkk-group-card {
        padding: 16px;
      }

      .pkk-member-list {
        max-height: none;
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
      border: 1px solid rgba(94, 234, 212, 0.22);
      border-radius: 8px;
      background: #111820;
      color: var(--green);
      box-shadow: var(--shadow);
      cursor: pointer;
    }

    .sidebar-toggle:hover,
    .sidebar-close:hover,
    .sidebar-toggle:focus-visible,
    .sidebar-close:focus-visible {
      border-color: rgba(94, 234, 212, 0.42);
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
            <strong>JejakMurid</strong>
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
            <a class="{{ $activePage === 'pohon' ? 'active' : '' }}" href="{{ route('dashboard.pohon') }}">Pohon Pemuridan</a>
            <a class="{{ $activePage === 'anggota-ktb' ? 'active' : '' }}" href="{{ route('dashboard.anggota-ktb') }}">Anggota KTB</a>
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
          @endif
        @endif
        @if ($user->isPKK())
          <a class="{{ $activePage === 'pkk-kelompok' ? 'active' : '' }}" href="{{ route('pkk.kelompok') }}">Kelompok Saya</a>
          <a class="{{ $activePage === 'pkk-pohon' ? 'active' : '' }}" href="{{ route('pkk.pohon') }}">Pohon Saya</a>
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
              <span>{{ $profileRoleLabel }} - {{ $profileScopeLabel }}</span>
            </span>
          </button>
          <div class="user-dropdown" data-user-menu-dropdown hidden>
            <a href="{{ route('dashboard.profile') }}">Profil</a>
            <a href="{{ route('dashboard.profile') }}#ubah-password">Ubah Password</a>
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

        @unless ($user->isSuperAdmin() || $user->isAdmin())
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
            @if ($user->isPKK())
              <article class="panel">
                <div class="panel-head">
                  <div>
                    <span class="eyebrow">Kelompok</span>
                    <h2>Kelompok Dipimpin</h2>
                  </div>
                  <a class="btn is-compact" href="{{ route('pkk.kelompok') }}">Lihat Semua</a>
                </div>
                @if ($pkkGroups->isEmpty())
                  <div class="empty-state">Belum ada kelompok KTB yang terhubung ke akun ini.</div>
                @else
                  <ul class="member-list">
                    @foreach ($pkkGroups->take(4) as $pkkGroup)
                      @php
                        $dashboardReportModalId = 'pkk-report-dashboard-create-'.$pkkGroup->kelompok_id;
                      @endphp
                      <li>
                        <div class="member-entry">
                          <strong>{{ $pkkGroup->nama_kelompok }}</strong>
                          <span>{{ number_format($pkkGroup->anggota->count(), 0, ',', '.') }} anggota - {{ $pkkGroup->laporanPertemuan->count() }} laporan</span>
                        </div>
                        <div class="crud-actions">
                          <span class="badge {{ $pkkGroup->is_active ? '' : 'warning' }}">{{ $pkkGroup->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                          <button class="btn is-compact" type="button" data-modal-open="{{ $dashboardReportModalId }}">Input Laporan</button>
                        </div>
                      </li>
                    @endforeach
                  </ul>
                  @foreach ($pkkGroups->take(4) as $pkkGroup)
                    @php
                      $dashboardReportModalId = 'pkk-report-dashboard-create-'.$pkkGroup->kelompok_id;
                    @endphp
                    <div class="modal" id="{{ $dashboardReportModalId }}" hidden>
                      <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="{{ $dashboardReportModalId }}-title">
                        <div class="modal-head">
                          <div>
                            <span class="eyebrow">Laporan Pertemuan</span>
                            <h2 id="{{ $dashboardReportModalId }}-title">{{ $pkkGroup->nama_kelompok }}</h2>
                          </div>
                          <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                        </div>
                        <div class="modal-body">
                          @include('dashboard.partials.pkk-report-form', [
                            'group' => $pkkGroup,
                            'action' => route('pkk.kelompok.laporan.store', $pkkGroup),
                            'modalId' => $dashboardReportModalId,
                          ])
                        </div>
                      </div>
                    </div>
                  @endforeach
                  <div class="row-actions panel-actions">
                    <a class="btn is-compact" href="{{ route('pkk.pohon') }}">Pohon Saya</a>
                    <a class="btn is-compact" href="{{ route('pkk.kelompok') }}">Semua Laporan</a>
                  </div>
                @endif
              </article>
            @endif
          </section>
        @endunless

        @if ($user->isAdmin())
          @include('dashboard.partials.campus-summary-table')
        @endif
      @elseif ($activePage === 'profil')
        <section class="content-grid">
          <article class="panel">
            <div class="panel-head">
              <div>
                <span class="eyebrow">Profil</span>
                <h2>Detail Akun</h2>
              </div>
              <span class="badge {{ $user->is_active ? '' : 'warning' }}">{{ $accountStatus }}</span>
            </div>
            <div class="profile-summary">
              <span class="profile-summary-avatar">
                <img src="{{ $profilePhoto }}" alt="Foto profil {{ $user->nama_lengkap }}">
              </span>
              <div>
                <h3>{{ $user->nama_lengkap }}</h3>
                <p>{{ $profileRoleLabel }} - {{ $profileScopeLabel }}</p>
              </div>
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
                <span class="detail-label">Role</span>
                <span class="detail-value">{{ $roleNames[$user->role] ?? strtoupper($user->role) }}</span>
              </div>
              @if ($user->isAdmin())
                <div class="detail-row">
                  <span class="detail-label">Tipe Admin</span>
                  <span class="detail-value">{{ ucfirst($user->admin_tipe ?: '-') }}</span>
                </div>
              @endif
              <div class="detail-row">
                <span class="detail-label">Regio</span>
                <span class="detail-value">{{ $user->regio?->nama_regio ?: ($user->isSuperAdmin() ? 'Developer' : '-') }}</span>
              </div>
              @unless ($user->isSuperAdmin() || $user->isAdmin())
                <div class="detail-row">
                  <span class="detail-label">Kampus</span>
                  <span class="detail-value">{{ $user->kampus?->nama_kampus ?: '-' }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Tanggal Lahir</span>
                  <span class="detail-value">{{ $user->tanggal_lahir?->format('d M Y') ?: '-' }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Jurusan</span>
                  <span class="detail-value">{{ $user->jurusan ?: '-' }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Kategori Jurusan</span>
                  <span class="detail-value">{{ $user->kategoriJurusan?->nama_kategori ?: '-' }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Angkatan</span>
                  <span class="detail-value">{{ $user->angkatan ?: '-' }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">PKK</span>
                  <span class="detail-value">{{ $user->pkkLeader?->nama_lengkap ?: '-' }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Kelompok KTB</span>
                  <span class="detail-value">{{ $user->kelompokPemuridan?->nama_kelompok ?: '-' }}</span>
                </div>
              @endunless
              <div class="detail-row">
                <span class="detail-label">Foto Profil</span>
                <span class="detail-value">{{ $user->foto_profil ?: 'Default' }}</span>
              </div>
            </div>
          </article>

          <article class="panel">
            <div class="panel-head">
              <div>
                <span class="eyebrow">Edit</span>
                <h2>Data Profil</h2>
              </div>
            </div>
            <form method="POST" action="{{ route('dashboard.profile.update') }}" class="compact-edit-form">
              @csrf
              @method('PUT')
              <div class="form-grid">
                <div class="field is-full">
                  <label for="profile-name">Nama Lengkap</label>
                  <input id="profile-name" type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" maxlength="256" required>
                </div>
                @unless ($user->isSuperAdmin() || $user->isAdmin())
                  <div class="field">
                    <label for="profile-birth-date">Tanggal Lahir</label>
                    <input id="profile-birth-date" type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir?->format('Y-m-d')) }}">
                  </div>
                  <div class="field">
                    <label for="profile-year">Angkatan</label>
                    <input id="profile-year" type="number" name="angkatan" value="{{ old('angkatan', $user->angkatan) }}" min="1900" max="{{ date('Y') + 1 }}">
                  </div>
                  <div class="field is-full">
                    <label for="profile-major">Jurusan</label>
                    <input id="profile-major" type="text" name="jurusan" value="{{ old('jurusan', $user->jurusan) }}" maxlength="255">
                  </div>
                  <div class="field is-full">
                    <label for="profile-major-category">Kategori Jurusan</label>
                    <select id="profile-major-category" name="kategori_jurusan_id">
                      <option value="">Tanpa kategori</option>
                      @foreach ($kategoriJurusanOptions as $option)
                        <option value="{{ $option->kategori_jurusan_id }}" {{ (string) old('kategori_jurusan_id', $user->kategori_jurusan_id) === (string) $option->kategori_jurusan_id ? 'selected' : '' }}>
                          {{ $option->nama_kategori }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                @endunless
                <div class="field is-full">
                  <label for="profile-photo">Foto Profil</label>
                  <input id="profile-photo" type="text" name="foto_profil" value="{{ old('foto_profil', $user->foto_profil) }}" maxlength="256" placeholder="URL atau path gambar">
                </div>
              </div>
              <div class="form-actions">
                <button class="btn is-compact" type="submit">Simpan Profil</button>
              </div>
            </form>
          </article>
        </section>

        <section class="panel" id="ubah-password">
          <div class="panel-head">
            <div>
              <span class="eyebrow">Keamanan</span>
              <h2>Ubah Password</h2>
            </div>
          </div>
          <form method="POST" action="{{ route('dashboard.profile.password') }}" class="compact-edit-form">
            @csrf
            @method('PUT')
            <div class="form-grid">
              <div class="field">
                <label for="profile-current-password">Password Saat Ini</label>
                <input id="profile-current-password" type="password" name="current_password" autocomplete="current-password" required>
              </div>
              <div class="field">
                <label for="profile-new-password">Password Baru</label>
                <input id="profile-new-password" type="password" name="password" autocomplete="new-password" minlength="6" required>
              </div>
              <div class="field">
                <label for="profile-new-password-confirmation">Konfirmasi Password Baru</label>
                <input id="profile-new-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="6" required>
              </div>
            </div>
            <div class="form-actions">
              <button class="btn is-compact" type="submit">Ubah Password</button>
            </div>
          </form>
        </section>
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
                    <th>Total User</th>
                    <th>Admin</th>
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
                      <td>{{ number_format($regio->total_users, 0, ',', '.') }}</td>
                      <td>{{ number_format($regio->admin_users, 0, ',', '.') }}</td>
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
          $directoryEyebrow = $activePage === 'anggota-ktb' ? 'Direktori KTB' : 'Direktori Pengguna';
          $directoryEmpty = $activePage === 'anggota-ktb' ? 'Belum ada data anggota KTB.' : 'Belum ada data pengguna.';
          $directorySearch = $activePage === 'anggota-ktb' ? 'Cari anggota KTB...' : 'Cari pengguna...';
          $directoryTableId = $activePage === 'anggota-ktb' ? 'member-table' : 'user-table';
          $directoryCanManageAdmins = $activePage === 'pengguna' && $canManageData;
          $directoryCanManageGroups = $activePage === 'anggota-ktb' && $canManageData;
          $directoryHasActions = $directoryCanManageAdmins || $directoryCanManageGroups;
          $useDirectoryRoleFilter = in_array($activePage, ['pengguna', 'anggota-ktb'], true);
          $directoryDefaultRole = $activePage === 'pengguna' ? 'admin' : '';
          $directoryRoleFilterOptions = $activePage === 'anggota-ktb'
              ? ['' => 'Semua', 'pkk' => 'PKK', 'akk' => 'AKK']
              : ['admin' => 'Admin', 'pkk' => 'PKK', 'akk' => 'AKK', '' => 'Semua'];
          $directoryColspan = 6 + ($directoryHasActions ? 1 : 0);
        @endphp

        <section class="panel" id="{{ $activePage === 'anggota-ktb' ? 'daftar-anggota-ktb' : 'daftar-pengguna' }}">
          <div class="panel-head table-panel-head">
            <div class="table-panel-title">
              <span class="eyebrow">{{ $directoryEyebrow }}</span>
              <h2>{{ $directoryTitle }}</h2>
            </div>
            <div class="row-actions table-panel-actions" @if ($useDirectoryRoleFilter) data-filter-panel="{{ $directoryTableId }}" @endif>
              <input
                class="search"
                type="search"
                placeholder="{{ $directorySearch }}"
                @if ($useDirectoryRoleFilter)
                  data-column-filter="search"
                @else
                  data-filter-table="{{ $directoryTableId }}"
                @endif
                aria-label="{{ $directorySearch }}"
              >
              @if ($useDirectoryRoleFilter)
                <select class="search" data-column-filter="role" aria-label="Filter role {{ $activePage === 'anggota-ktb' ? 'anggota' : 'pengguna' }}">
                  @foreach ($directoryRoleFilterOptions as $roleValue => $roleLabel)
                    <option value="{{ $roleValue }}"{{ (string) $directoryDefaultRole === (string) $roleValue ? ' selected' : '' }}>{{ $roleLabel }}</option>
                  @endforeach
                </select>
              @endif
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
            @if ($useDirectoryRoleFilter)
              <p class="table-counter" data-filter-counter="{{ $directoryTableId }}"></p>
            @endif
            <div class="table-wrap">
              <table class="table" id="{{ $directoryTableId }}">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Regio</th>
                    <th>Angkatan</th>
                    <th>Status</th>
                    @if ($directoryHasActions)
                      <th>Aksi</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                  @foreach ($directoryRows as $row)
                    @php
                      $memberGroupModalId = 'modal-member-group-create-'.$row->user_id;
                    @endphp
                    <tr
                      @if ($useDirectoryRoleFilter)
                        data-filter-row
                        data-role="{{ $row->role }}"
                        data-search-text="{{ trim($row->nama_lengkap.' '.$row->username.' '.($roleNames[$row->role] ?? strtoupper($row->role)).' '.($row->regio?->nama_regio ?: '').' '.($row->kampus?->singkatan ?: '').' '.($row->kampus?->nama_kampus ?: '').' '.($row->angkatan ?: '')) }}"
                      @endif
                    >
                      <td>
                        <strong>{{ $row->nama_lengkap }}</strong>
                        <div class="muted">{{ $row->kampus?->singkatan ?: '-' }}</div>
                      </td>
                      <td>{{ $row->username }}</td>
                      <td><span class="badge neutral">{{ $roleNames[$row->role] ?? strtoupper($row->role) }}</span></td>
                      <td>{{ $row->regio?->nama_regio ?: '-' }}</td>
                      <td>{{ $row->angkatan ?: '-' }}</td>
                      <td>
                        <span class="badge {{ $row->is_active ? '' : 'warning' }}">
                          {{ $row->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                      </td>
                      @if ($directoryHasActions)
                        <td>
                          <div class="row-actions">
                            @if ($directoryCanManageAdmins)
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
                              @if ($row->role === 'admin')
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
                              @endif
                            @endif
                            @if ($directoryCanManageGroups)
                              <button class="btn icon-btn" type="button" data-modal-open="{{ $memberGroupModalId }}" title="Tambah kelompok" aria-label="Tambah kelompok untuk {{ $row->nama_lengkap }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                  <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                  <circle cx="9" cy="7" r="4"></circle>
                                  <path d="M19 8v6"></path>
                                  <path d="M16 11h6"></path>
                                </svg>
                              </button>
                            @endif
                          </div>

                          @if ($row->role === 'admin')
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
                          @endif
                          @if ($directoryCanManageGroups)
                            <div class="modal" id="{{ $memberGroupModalId }}" hidden>
                              <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-member-group-create-title-{{ $row->user_id }}">
                                <div class="modal-head">
                                  <div>
                                    <span class="eyebrow">Tambah Kelompok</span>
                                    <h2 id="modal-member-group-create-title-{{ $row->user_id }}">{{ $row->nama_lengkap }}</h2>
                                  </div>
                                  <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                                </div>
                                <div class="modal-body">
                                  <p class="muted">Anggota ini akan menjadi pemimpin kelompok. User yang sama tetap dipakai, tidak dibuat ulang.</p>
                                  <form method="POST" action="{{ route('dashboard.pohon.kelompok.store') }}" class="compact-edit-form">
                                    @csrf
                                    <input type="hidden" name="_modal_id" value="{{ $memberGroupModalId }}">
                                    <input type="hidden" name="pemimpin_id" value="{{ $row->user_id }}">
                                    <div class="form-grid">
                                      <div class="field is-full">
                                        <label for="member-group-name-{{ $row->user_id }}">Nama Kelompok</label>
                                        <input id="member-group-name-{{ $row->user_id }}" type="text" name="nama_kelompok" value="{{ old('_modal_id') === $memberGroupModalId ? old('nama_kelompok') : '' }}" maxlength="256" placeholder="Kosongkan untuk memakai nama anggota">
                                      </div>
                                      <div class="field is-full">
                                        <label for="member-group-campus-{{ $row->user_id }}">Kampus Kelompok</label>
                                        <select id="member-group-campus-{{ $row->user_id }}" name="kampus_id">
                                          <option value="">Tanpa kampus</option>
                                          @foreach ($campusOptions as $option)
                                            @php
                                              $selectedCampusId = old('_modal_id') === $memberGroupModalId
                                                  ? old('kampus_id')
                                                  : $row->kampus_id;
                                            @endphp
                                            <option value="{{ $option->kampus_id }}" {{ (string) $selectedCampusId === (string) $option->kampus_id ? 'selected' : '' }}>
                                              {{ $option->nama_kampus }}{{ $option->singkatan ? ' ('.$option->singkatan.')' : '' }}{{ $user->isSuperAdmin() && $option->regio?->nama_regio ? ' - '.$option->regio->nama_regio : '' }}
                                            </option>
                                          @endforeach
                                        </select>
                                      </div>
                                      <label class="checkbox-field">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" {{ old('_modal_id') === $memberGroupModalId ? (old('is_active') ? 'checked' : '') : 'checked' }}>
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
                          @endif
                        </td>
                      @endif
                    </tr>
                  @endforeach
                  @if ($useDirectoryRoleFilter)
                    <tr data-filter-empty-row hidden>
                      <td colspan="{{ $directoryColspan }}">
                        <div class="empty-state">Tidak ada pengguna yang cocok dengan filter.</div>
                      </td>
                    </tr>
                  @endif
                </tbody>
              </table>
            </div>
          @endif
        </section>
      @elseif ($activePage === 'pkk-kelompok' && $user->isPKK())
        <section class="pkk-group-list">
          @forelse ($pkkGroups as $pkkGroup)
            @php
              $createReportModalId = 'pkk-report-create-'.$pkkGroup->kelompok_id;
            @endphp
            <article class="panel pkk-group-card" id="kelompok-{{ $pkkGroup->kelompok_id }}">
              <div class="panel-head">
                <div class="pkk-group-title">
                  <span class="eyebrow">Kelompok KTB</span>
                  <h2>{{ $pkkGroup->nama_kelompok }}</h2>
                </div>
                <span class="badge {{ $pkkGroup->is_active ? '' : 'warning' }}">{{ $pkkGroup->is_active ? 'Aktif' : 'Nonaktif' }}</span>
              </div>
              <div class="pkk-summary-grid">
                <div class="pkk-summary-item">
                  <span>Kampus</span>
                  <strong>{{ $pkkGroup->kampus?->nama_kampus ?: 'Tanpa kampus' }}</strong>
                </div>
                <div class="pkk-summary-item is-number">
                  <span>Anggota</span>
                  <strong>{{ number_format($pkkGroup->anggota->count(), 0, ',', '.') }}</strong>
                </div>
                <div class="pkk-summary-item is-number">
                  <span>Laporan</span>
                  <strong>{{ number_format($pkkGroup->laporanPertemuan->count(), 0, ',', '.') }}</strong>
                </div>
              </div>

              <div class="row-actions panel-actions">
                <button class="btn is-compact" type="button" data-modal-open="{{ $createReportModalId }}">Buat Laporan</button>
                <a class="btn is-compact" href="{{ route('pkk.pohon') }}">Lihat Pohon</a>
              </div>

              <div class="modal" id="{{ $createReportModalId }}" hidden>
                <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="{{ $createReportModalId }}-title">
                  <div class="modal-head">
                    <div>
                      <span class="eyebrow">Laporan Pertemuan</span>
                      <h2 id="{{ $createReportModalId }}-title">{{ $pkkGroup->nama_kelompok }}</h2>
                    </div>
                    <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                  </div>
                  <div class="modal-body">
                    @include('dashboard.partials.pkk-report-form', [
                      'group' => $pkkGroup,
                      'action' => route('pkk.kelompok.laporan.store', $pkkGroup),
                      'modalId' => $createReportModalId,
                    ])
                  </div>
                </div>
              </div>

              <div class="pkk-group-body">
                <section class="pkk-subpanel" aria-labelledby="pkk-members-{{ $pkkGroup->kelompok_id }}">
                  <span class="pkk-section-label" id="pkk-members-{{ $pkkGroup->kelompok_id }}">Anggota Kelompok</span>
                  @if ($pkkGroup->anggota->isEmpty())
                    <div class="empty-state">Belum ada anggota dalam kelompok ini.</div>
                  @else
                    <ul class="member-list pkk-member-list">
                      @foreach ($pkkGroup->anggota as $member)
                        <li>
                          <div class="member-entry">
                            <strong>{{ $member->nama_lengkap }}</strong>
                            <span>{{ $member->username }}{{ $member->angkatan ? ' - '.$member->angkatan : '' }}</span>
                          </div>
                          <span class="badge neutral">{{ $member->role === 'pkk' ? 'PKK' : 'AKK' }}</span>
                        </li>
                      @endforeach
                    </ul>
                  @endif
                </section>

                <section class="pkk-subpanel" aria-labelledby="pkk-reports-{{ $pkkGroup->kelompok_id }}">
                  <span class="pkk-section-label" id="pkk-reports-{{ $pkkGroup->kelompok_id }}">Laporan Pertemuan</span>
                  @if ($pkkGroup->laporanPertemuan->isEmpty())
                    <div class="empty-state pkk-report-empty">Belum ada laporan pertemuan untuk kelompok ini.</div>
                  @else
                    <div class="table-wrap pkk-report-table-wrap">
                      <table class="table pkk-report-table">
                        <thead>
                          <tr>
                            <th class="report-date-col">Tanggal</th>
                            <th class="report-material-col">Bahan</th>
                            <th class="report-attendance-col">Hadir</th>
                            <th class="report-action-col">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($pkkGroup->laporanPertemuan as $report)
                            @php
                              $editReportModalId = 'pkk-report-edit-'.$report->laporan_id;
                              $deleteReportModalId = 'pkk-report-delete-'.$report->laporan_id;
                              $attendanceIds = collect($report->anggota_hadir ?? [])->map(fn ($id) => (int) $id);
                              $attendanceNames = $pkkGroup->anggota
                                  ->whereIn('user_id', $attendanceIds)
                                  ->pluck('nama_lengkap')
                                  ->values();
                            @endphp
                            <tr>
                              <td>
                                <strong>{{ $report->tanggal_pertemuan?->format('d/m/Y') }}</strong>
                                <div class="muted">{{ $report->pertemuan_ke ? 'Pertemuan '.$report->pertemuan_ke : 'Pertemuan' }}</div>
                              </td>
                              <td>
                                <div class="cell-main">{{ $report->bahan }}</div>
                                @if ($report->ringkasan)
                                  <div class="muted">{{ $report->ringkasan }}</div>
                                @endif
                              </td>
                              <td>
                                <strong>{{ number_format($report->jumlah_hadir, 0, ',', '.') }}</strong>
                                <div class="muted">{{ $attendanceNames->isNotEmpty() ? $attendanceNames->join(', ') : 'Tidak ada anggota dicatat hadir' }}</div>
                              </td>
                              <td>
                                <div class="row-actions pkk-report-actions">
                                  <button class="btn icon-btn" type="button" data-modal-open="{{ $editReportModalId }}" title="Edit" aria-label="Edit laporan {{ $report->laporan_id }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                      <path d="M12 20h9"></path>
                                      <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                    </svg>
                                  </button>
                                  <button class="btn icon-btn is-danger" type="button" data-modal-open="{{ $deleteReportModalId }}" title="Hapus" aria-label="Hapus laporan {{ $report->laporan_id }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                      <path d="M3 6h18"></path>
                                      <path d="M8 6V4h8v2"></path>
                                      <path d="m19 6-1 14H6L5 6"></path>
                                      <path d="M10 11v6"></path>
                                      <path d="M14 11v6"></path>
                                    </svg>
                                  </button>
                                </div>

                            <div class="modal" id="{{ $editReportModalId }}" hidden>
                              <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="{{ $editReportModalId }}-title">
                                <div class="modal-head">
                                  <div>
                                    <span class="eyebrow">Edit Laporan</span>
                                    <h2 id="{{ $editReportModalId }}-title">{{ $pkkGroup->nama_kelompok }}</h2>
                                  </div>
                                  <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                                </div>
                                <div class="modal-body">
                                  @include('dashboard.partials.pkk-report-form', [
                                    'group' => $pkkGroup,
                                    'report' => $report,
                                    'action' => route('pkk.laporan.update', $report),
                                    'method' => 'PUT',
                                    'modalId' => $editReportModalId,
                                  ])
                                </div>
                              </div>
                            </div>

                            <div class="modal" id="{{ $deleteReportModalId }}" hidden>
                              <div class="modal-panel is-small" role="dialog" aria-modal="true" aria-labelledby="{{ $deleteReportModalId }}-title">
                                <div class="modal-head">
                                  <div>
                                    <span class="eyebrow">Hapus Laporan</span>
                                    <h2 id="{{ $deleteReportModalId }}-title">{{ $report->tanggal_pertemuan?->format('d/m/Y') }}</h2>
                                  </div>
                                  <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                                </div>
                                <div class="modal-body">
                                  <p class="muted">Hapus laporan pertemuan {{ $pkkGroup->nama_kelompok }}?</p>
                                  <form method="POST" action="{{ route('pkk.laporan.destroy', $report) }}" class="inline-delete">
                                    @csrf
                                    @method('DELETE')
                                    <div class="form-actions">
                                      <button class="btn is-compact is-danger" type="submit">Hapus Laporan</button>
                                      <button class="btn is-compact" type="button" data-modal-close>Batal</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                    </div>
                  @endif
                </section>
              </div>
            </article>
          @empty
            <article class="panel">
              <div class="empty-state">Belum ada kelompok KTB yang Anda pimpin.</div>
            </article>
          @endforelse
        </section>
      @elseif ($activePage === 'pkk-pohon' && $user->isPKK())
        @include('dashboard.partials.campus-tree-tab')
      @elseif (in_array($activePage, ['pohon', 'kampus-detail'], true) && $canSeeAdminData)
        @if ($activePage === 'kampus-detail' && $selectedKampus)
          <section class="campus-tab-panel" data-campus-tabs>
            <div class="campus-tab-list" role="tablist" aria-label="Konten kampus">
              <button
                class="campus-tab-button is-active"
                type="button"
                role="tab"
                aria-selected="true"
                data-campus-tab-button
                data-tab-url="{{ route('dashboard.kampus.tab', ['kampus' => $selectedKampus, 'tab' => 'pohon']) }}"
              >
                Pohon Pemuridan
              </button>
              <button
                class="campus-tab-button"
                type="button"
                role="tab"
                aria-selected="false"
                data-campus-tab-button
                data-tab-url="{{ route('dashboard.kampus.tab', ['kampus' => $selectedKampus, 'tab' => 'anggota']) }}"
              >
                Anggota KTB
              </button>
              <button
                class="campus-tab-button"
                type="button"
                role="tab"
                aria-selected="false"
                data-campus-tab-button
                data-tab-url="{{ route('dashboard.kampus.tab', ['kampus' => $selectedKampus, 'tab' => 'kelompok']) }}"
              >
                Kelompok KTB
              </button>
            </div>
            <div class="campus-tab-content" data-campus-tab-content>
              @include('dashboard.partials.campus-tree-tab')
            </div>
          </section>
        @else
          @include('dashboard.partials.campus-tree-tab')
        @endif

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
                  <button class="btn is-compact" type="button" data-tree-v2-action-do="edit_member" hidden>Edit Anggota</button>
                  <button class="btn is-compact" type="button" data-tree-v2-action-do="edit_group" hidden>Edit Kelompok</button>
                  <button class="btn is-compact is-danger" type="button" data-tree-v2-action-do="delete_member" hidden>Hapus Anggota</button>
                  <button class="btn is-compact is-danger" type="button" data-tree-v2-action-do="delete_group" hidden>Hapus Kelompok</button>
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
                    <div class="field is-full">
                      <label for="tree-group-campus">Kampus Kelompok</label>
                      <select id="tree-group-campus" name="kampus_id" data-tree-group-campus-select>
                        <option value="">Tanpa kampus</option>
                        @foreach ($campusOptions as $option)
                          <option value="{{ $option->kampus_id }}" {{ (string) old('kampus_id') === (string) $option->kampus_id ? 'selected' : '' }}>
                            {{ $option->nama_kampus }}{{ $option->singkatan ? ' ('.$option->singkatan.')' : '' }}{{ $user->isSuperAdmin() && $option->regio?->nama_regio ? ' - '.$option->regio->nama_regio : '' }}
                          </option>
                        @endforeach
                      </select>
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

          @php
            $oldTreeMemberId = old('_tree_person_id');
            $oldTreeGroupId = old('_tree_group_id');
          @endphp

          <div class="modal" id="tree-member-edit-modal" data-tree-member-edit-modal hidden>
            <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="tree-member-edit-title">
              <div class="modal-head">
                <div>
                  <span class="eyebrow">Edit Anggota</span>
                  <h2 id="tree-member-edit-title" data-tree-member-edit-title>Anggota</h2>
                </div>
                <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
              </div>
              <div class="modal-body">
                <form
                  method="POST"
                  action="{{ $oldTreeMemberId ? route('dashboard.pohon.anggota.update', ['anggota' => $oldTreeMemberId]) : '' }}"
                  class="compact-edit-form"
                  data-tree-member-edit-form
                  data-action-template="{{ route('dashboard.pohon.anggota.update', ['anggota' => '__ID__']) }}"
                >
                  @csrf
                  @method('PUT')
                  <input type="hidden" name="_modal_id" value="tree-member-edit-modal">
                  <input type="hidden" name="_tree_person_id" value="{{ old('_tree_person_id') }}" data-tree-member-edit-id>
                  <div class="form-grid">
                    <div class="field">
                      <label for="tree-member-edit-campus">Kampus</label>
                      <select id="tree-member-edit-campus" name="kampus_id" data-tree-member-edit-campus>
                        <option value="">Tanpa kampus</option>
                        @foreach ($campusOptions as $option)
                          <option value="{{ $option->kampus_id }}" {{ (string) old('kampus_id') === (string) $option->kampus_id ? 'selected' : '' }}>
                            {{ $option->nama_kampus }}{{ $option->singkatan ? ' ('.$option->singkatan.')' : '' }}{{ $user->isSuperAdmin() && $option->regio?->nama_regio ? ' - '.$option->regio->nama_regio : '' }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="field">
                      <label for="tree-member-edit-angkatan">Angkatan</label>
                      <input id="tree-member-edit-angkatan" type="number" name="angkatan" value="{{ old('_modal_id') === 'tree-member-edit-modal' ? old('angkatan') : '' }}" min="1900" max="{{ date('Y') + 1 }}" data-tree-member-edit-angkatan>
                    </div>
                    <div class="field is-full">
                      <label for="tree-member-edit-name">Nama Anggota</label>
                      <input id="tree-member-edit-name" type="text" name="nama_lengkap" value="{{ old('_modal_id') === 'tree-member-edit-modal' ? old('nama_lengkap') : '' }}" maxlength="256" required data-tree-member-edit-name>
                    </div>
                    <label class="checkbox-field">
                      <input type="hidden" name="is_active" value="0">
                      <input type="checkbox" name="is_active" value="1" {{ old('_modal_id') === 'tree-member-edit-modal' ? (old('is_active') ? 'checked' : '') : 'checked' }} data-tree-member-edit-active>
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

          <div class="modal" id="tree-member-delete-modal" data-tree-member-delete-modal hidden>
            <div class="modal-panel is-small" role="dialog" aria-modal="true" aria-labelledby="tree-member-delete-title">
              <div class="modal-head">
                <div>
                  <span class="eyebrow">Hapus Anggota</span>
                  <h2 id="tree-member-delete-title" data-tree-member-delete-title>Anggota</h2>
                </div>
                <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
              </div>
              <div class="modal-body">
                <p class="muted" data-tree-member-delete-context>Hapus anggota ini dari pohon pemuridan?</p>
                <form
                  method="POST"
                  action=""
                  class="inline-delete"
                  data-tree-member-delete-form
                  data-action-template="{{ route('dashboard.pohon.anggota.destroy', ['anggota' => '__ID__']) }}"
                >
                  @csrf
                  @method('DELETE')
                  <div class="form-actions">
                    <button class="btn is-compact is-danger" type="submit">Hapus Anggota</button>
                    <button class="btn is-compact" type="button" data-modal-close>Batal</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <div class="modal" id="tree-group-edit-modal" data-tree-group-edit-modal hidden>
            <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="tree-group-edit-title">
              <div class="modal-head">
                <div>
                  <span class="eyebrow">Edit Kelompok</span>
                  <h2 id="tree-group-edit-title" data-tree-group-edit-title>Kelompok</h2>
                </div>
                <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
              </div>
              <div class="modal-body">
                <form
                  method="POST"
                  action="{{ $oldTreeGroupId ? route('dashboard.pohon.kelompok.update', ['kelompok' => $oldTreeGroupId]) : '' }}"
                  class="compact-edit-form"
                  data-tree-group-edit-form
                  data-action-template="{{ route('dashboard.pohon.kelompok.update', ['kelompok' => '__ID__']) }}"
                >
                  @csrf
                  @method('PUT')
                  <input type="hidden" name="_modal_id" value="tree-group-edit-modal">
                  <input type="hidden" name="_tree_group_id" value="{{ old('_tree_group_id') }}" data-tree-group-edit-id>
                  <div class="form-grid">
                    <div class="field is-full">
                      <label for="tree-group-edit-name">Nama Kelompok</label>
                      <input id="tree-group-edit-name" type="text" name="nama_kelompok" value="{{ old('_modal_id') === 'tree-group-edit-modal' ? old('nama_kelompok') : '' }}" maxlength="256" required data-tree-group-edit-name>
                    </div>
                    <div class="field is-full">
                      <label for="tree-group-edit-campus">Kampus Kelompok</label>
                      <select id="tree-group-edit-campus" name="kampus_id" data-tree-group-edit-campus>
                        <option value="">Tanpa kampus</option>
                        @foreach ($campusOptions as $option)
                          <option value="{{ $option->kampus_id }}" {{ (string) old('kampus_id') === (string) $option->kampus_id ? 'selected' : '' }}>
                            {{ $option->nama_kampus }}{{ $option->singkatan ? ' ('.$option->singkatan.')' : '' }}{{ $user->isSuperAdmin() && $option->regio?->nama_regio ? ' - '.$option->regio->nama_regio : '' }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <label class="checkbox-field">
                      <input type="hidden" name="is_active" value="0">
                      <input type="checkbox" name="is_active" value="1" {{ old('_modal_id') === 'tree-group-edit-modal' ? (old('is_active') ? 'checked' : '') : 'checked' }} data-tree-group-edit-active>
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

          <div class="modal" id="tree-group-delete-modal" data-tree-group-delete-modal hidden>
            <div class="modal-panel is-small" role="dialog" aria-modal="true" aria-labelledby="tree-group-delete-title">
              <div class="modal-head">
                <div>
                  <span class="eyebrow">Hapus Kelompok</span>
                  <h2 id="tree-group-delete-title" data-tree-group-delete-title>Kelompok</h2>
                </div>
                <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
              </div>
              <div class="modal-body">
                <p class="muted" data-tree-group-delete-context>Hapus kelompok ini? Anggota di dalamnya akan dilepas dari kelompok.</p>
                <form
                  method="POST"
                  action=""
                  class="inline-delete"
                  data-tree-group-delete-form
                  data-action-template="{{ route('dashboard.pohon.kelompok.destroy', ['kelompok' => '__ID__']) }}"
                >
                  @csrf
                  @method('DELETE')
                  <div class="form-actions">
                    <button class="btn is-compact is-danger" type="submit">Hapus Kelompok</button>
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
    var treeGroupCampusSelect = treeGroupModal ? treeGroupModal.querySelector('[data-tree-group-campus-select]') : null;
    var treeMemberModal = document.querySelector('[data-tree-member-create-modal]');
    var treeMemberContext = treeMemberModal ? treeMemberModal.querySelector('[data-tree-member-context]') : null;
    var treeMemberContextInput = treeMemberModal ? treeMemberModal.querySelector('[data-tree-member-context-input]') : null;
    var treeMemberGroupInput = treeMemberModal ? treeMemberModal.querySelector('[data-tree-member-group-input]') : null;
    var treeMemberCampusSelect = treeMemberModal ? treeMemberModal.querySelector('[data-tree-member-campus-select]') : null;
    var treeMemberEditModal = document.querySelector('[data-tree-member-edit-modal]');
    var treeMemberEditTitle = treeMemberEditModal ? treeMemberEditModal.querySelector('[data-tree-member-edit-title]') : null;
    var treeMemberEditForm = treeMemberEditModal ? treeMemberEditModal.querySelector('[data-tree-member-edit-form]') : null;
    var treeMemberEditId = treeMemberEditModal ? treeMemberEditModal.querySelector('[data-tree-member-edit-id]') : null;
    var treeMemberEditName = treeMemberEditModal ? treeMemberEditModal.querySelector('[data-tree-member-edit-name]') : null;
    var treeMemberEditCampus = treeMemberEditModal ? treeMemberEditModal.querySelector('[data-tree-member-edit-campus]') : null;
    var treeMemberEditAngkatan = treeMemberEditModal ? treeMemberEditModal.querySelector('[data-tree-member-edit-angkatan]') : null;
    var treeMemberEditActive = treeMemberEditModal ? treeMemberEditModal.querySelector('[data-tree-member-edit-active]') : null;
    var treeMemberDeleteModal = document.querySelector('[data-tree-member-delete-modal]');
    var treeMemberDeleteTitle = treeMemberDeleteModal ? treeMemberDeleteModal.querySelector('[data-tree-member-delete-title]') : null;
    var treeMemberDeleteContext = treeMemberDeleteModal ? treeMemberDeleteModal.querySelector('[data-tree-member-delete-context]') : null;
    var treeMemberDeleteForm = treeMemberDeleteModal ? treeMemberDeleteModal.querySelector('[data-tree-member-delete-form]') : null;
    var treeGroupEditModal = document.querySelector('[data-tree-group-edit-modal]');
    var treeGroupEditTitle = treeGroupEditModal ? treeGroupEditModal.querySelector('[data-tree-group-edit-title]') : null;
    var treeGroupEditForm = treeGroupEditModal ? treeGroupEditModal.querySelector('[data-tree-group-edit-form]') : null;
    var treeGroupEditId = treeGroupEditModal ? treeGroupEditModal.querySelector('[data-tree-group-edit-id]') : null;
    var treeGroupEditName = treeGroupEditModal ? treeGroupEditModal.querySelector('[data-tree-group-edit-name]') : null;
    var treeGroupEditCampus = treeGroupEditModal ? treeGroupEditModal.querySelector('[data-tree-group-edit-campus]') : null;
    var treeGroupEditActive = treeGroupEditModal ? treeGroupEditModal.querySelector('[data-tree-group-edit-active]') : null;
    var treeGroupDeleteModal = document.querySelector('[data-tree-group-delete-modal]');
    var treeGroupDeleteTitle = treeGroupDeleteModal ? treeGroupDeleteModal.querySelector('[data-tree-group-delete-title]') : null;
    var treeGroupDeleteContext = treeGroupDeleteModal ? treeGroupDeleteModal.querySelector('[data-tree-group-delete-context]') : null;
    var treeGroupDeleteForm = treeGroupDeleteModal ? treeGroupDeleteModal.querySelector('[data-tree-group-delete-form]') : null;

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
        memberCampusId: data.memberCampusId || data.campusId || '',
        personId: data.personId || '',
        groupId: data.groupId || '',
        leaderId: data.leaderId || '',
        leaderName: data.leaderName || '',
        role: data.role || '',
        angkatan: data.angkatan || '',
        isActive: data.isActive || '1'
      };
    }

    function applyActionTemplate(form, id) {
      if (!form || !id) return false;

      var template = form.getAttribute('data-action-template');
      if (!template) return false;

      form.action = template.replace('__ID__', encodeURIComponent(id));
      return true;
    }

    function setCheckboxValue(checkbox, value) {
      if (!checkbox) return;
      checkbox.checked = value !== '0' && value !== 'false';
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
      var canManageMember = (activeTreeNodeData.type === 'person' || activeTreeNodeData.type === 'akk') && Boolean(activeTreeNodeData.personId);
      var canManageGroup = activeTreeNodeData.type === 'group' && Boolean(activeTreeNodeData.groupId);

      setTreeActionVisibility('add_group', canAddGroup);
      setTreeActionVisibility('add_member', canAddMember);
      setTreeActionVisibility('edit_member', canManageMember);
      setTreeActionVisibility('delete_member', canManageMember);
      setTreeActionVisibility('edit_group', canManageGroup);
      setTreeActionVisibility('delete_group', canManageGroup);
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

      if (treeGroupCampusSelect) {
        treeGroupCampusSelect.value = activeTreeNodeData.campusId || '';
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

    function openTreeMemberEditModal() {
      if (!treeMemberEditModal || !activeTreeNodeData || !activeTreeNodeData.personId) return;
      if (!applyActionTemplate(treeMemberEditForm, activeTreeNodeData.personId)) return;

      if (treeMemberEditTitle) {
        treeMemberEditTitle.textContent = activeTreeNodeData.name;
      }

      if (treeMemberEditId) {
        treeMemberEditId.value = activeTreeNodeData.personId;
      }

      if (treeMemberEditName) {
        treeMemberEditName.value = activeTreeNodeData.name || '';
      }

      if (treeMemberEditCampus) {
        treeMemberEditCampus.value = activeTreeNodeData.memberCampusId || '';
      }

      if (treeMemberEditAngkatan) {
        treeMemberEditAngkatan.value = activeTreeNodeData.angkatan || '';
      }

      setCheckboxValue(treeMemberEditActive, activeTreeNodeData.isActive);

      closeModal(treeActionModal);
      openModal(treeMemberEditModal);
    }

    function openTreeMemberDeleteModal() {
      if (!treeMemberDeleteModal || !activeTreeNodeData || !activeTreeNodeData.personId) return;
      if (!applyActionTemplate(treeMemberDeleteForm, activeTreeNodeData.personId)) return;

      if (treeMemberDeleteTitle) {
        treeMemberDeleteTitle.textContent = activeTreeNodeData.name;
      }

      if (treeMemberDeleteContext) {
        treeMemberDeleteContext.textContent = 'Hapus ' + activeTreeNodeData.name + '? Kelompok yang dipimpin orang ini juga akan dihapus dari pohon.';
      }

      closeModal(treeActionModal);
      openModal(treeMemberDeleteModal);
    }

    function openTreeGroupEditModal() {
      if (!treeGroupEditModal || !activeTreeNodeData || !activeTreeNodeData.groupId) return;
      if (!applyActionTemplate(treeGroupEditForm, activeTreeNodeData.groupId)) return;

      if (treeGroupEditTitle) {
        treeGroupEditTitle.textContent = activeTreeNodeData.name;
      }

      if (treeGroupEditId) {
        treeGroupEditId.value = activeTreeNodeData.groupId;
      }

      if (treeGroupEditName) {
        treeGroupEditName.value = activeTreeNodeData.name || '';
      }

      if (treeGroupEditCampus) {
        treeGroupEditCampus.value = activeTreeNodeData.campusId || '';
      }

      setCheckboxValue(treeGroupEditActive, activeTreeNodeData.isActive);

      closeModal(treeActionModal);
      openModal(treeGroupEditModal);
    }

    function openTreeGroupDeleteModal() {
      if (!treeGroupDeleteModal || !activeTreeNodeData || !activeTreeNodeData.groupId) return;
      if (!applyActionTemplate(treeGroupDeleteForm, activeTreeNodeData.groupId)) return;

      if (treeGroupDeleteTitle) {
        treeGroupDeleteTitle.textContent = activeTreeNodeData.name;
      }

      if (treeGroupDeleteContext) {
        treeGroupDeleteContext.textContent = 'Hapus kelompok ' + activeTreeNodeData.name + '? Anggota di dalamnya akan dilepas dari kelompok.';
      }

      closeModal(treeActionModal);
      openModal(treeGroupDeleteModal);
    }

    function bindTreeNodeActions(root) {
      (root || document).querySelectorAll('[data-tree-v2-node-action]').forEach(function (node) {
        if (node.getAttribute('data-tree-action-bound') === '1') return;

        node.setAttribute('data-tree-action-bound', '1');
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
    }

    bindTreeNodeActions(document);

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

        if (action === 'edit_member') {
          openTreeMemberEditModal();
          return;
        }

        if (action === 'delete_member') {
          openTreeMemberDeleteModal();
          return;
        }

        if (action === 'edit_group') {
          openTreeGroupEditModal();
          return;
        }

        if (action === 'delete_group') {
          openTreeGroupDeleteModal();
          return;
        }

        if (action === 'view_detail' && treeActionDetail) {
          treeActionDetail.hidden = !treeActionDetail.hidden;
        }
      });
    });

    function bindFilterTables(root) {
      (root || document).querySelectorAll('[data-filter-table]').forEach(function (input) {
        if (input.getAttribute('data-filter-table-bound') === '1') return;

        input.setAttribute('data-filter-table-bound', '1');
        input.addEventListener('input', function () {
          var table = document.getElementById(input.getAttribute('data-filter-table'));
          var query = input.value.toLowerCase();
          if (!table) return;

          table.querySelectorAll('tbody tr').forEach(function (row) {
            row.hidden = query !== '' && !row.textContent.toLowerCase().includes(query);
          });
        });
      });
    }

    bindFilterTables(document);

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

    function bindFilterBlocks(root) {
      var scope = root || document;

      scope.querySelectorAll('[data-filter-block]').forEach(function (input) {
        if (input.getAttribute('data-filter-block-bound') === '1') return;

        input.setAttribute('data-filter-block-bound', '1');
        input.addEventListener('input', function () {
          var selector = input.getAttribute('data-filter-block');
          var query = input.value.toLowerCase();
          if (!selector) return;

          scope.querySelectorAll(selector).forEach(function (block) {
            var text = (block.getAttribute('data-filter-text') || block.textContent || '').toLowerCase();
            block.hidden = query !== '' && !text.includes(query);
          });
        });
      });
    }

    bindFilterBlocks(document);

    function bindTreeCanvases(root) {
      (root || document).querySelectorAll('[data-drag-scroll]').forEach(function (treeScrollArea) {
        if (treeScrollArea.getAttribute('data-tree-canvas-bound') === '1') return;

        treeScrollArea.setAttribute('data-tree-canvas-bound', '1');

        var surface = treeScrollArea.closest('.tree-v2-surface') || document;
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

        var zoomTarget = surface.querySelector('[data-tree-zoom]');
        var zoomValue = surface.querySelector('[data-zoom-value]');
        var zoomIn = surface.querySelector('[data-zoom-in]');
        var zoomOut = surface.querySelector('[data-zoom-out]');
        var treeScale = 0.9;

        function applyTreeZoom() {
          if (!zoomTarget) return;
          treeScale = Math.min(1.4, Math.max(0.45, Number(treeScale.toFixed(2))));
          zoomTarget.style.transform = 'scale(' + treeScale + ')';
          if (zoomValue) {
            zoomValue.textContent = Math.round(treeScale * 100) + '%';
          }
        }

        applyTreeZoom();

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

        var treeSearchInput = surface.querySelector('[data-tree-search-input]');
        var treeSearchButton = surface.querySelector('[data-tree-search-submit]');

        function runTreeSearch() {
          if (!treeSearchInput) return;
          var query = treeSearchInput.value.trim().toLowerCase();
          if (!query) return;

          var nodes = Array.prototype.slice.call(surface.querySelectorAll('.tree-v2-node[data-search-name]'));
          var target = nodes.find(function (node) {
            return (node.getAttribute('data-search-name') || '').toLowerCase() === query;
          }) || nodes.find(function (node) {
            return (node.getAttribute('data-search-name') || '').toLowerCase().includes(query);
          });

          surface.querySelectorAll('.tree-v2-node.is-search-hit').forEach(function (node) {
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
      });
    }

    bindTreeCanvases(document);

    function bindCampusTabs(root) {
      (root || document).querySelectorAll('[data-campus-tabs]').forEach(function (tabs) {
        if (tabs.getAttribute('data-campus-tabs-bound') === '1') return;

        tabs.setAttribute('data-campus-tabs-bound', '1');

        var content = tabs.querySelector('[data-campus-tab-content]');
        var buttons = Array.prototype.slice.call(tabs.querySelectorAll('[data-campus-tab-button]'));

        buttons.forEach(function (button) {
          button.addEventListener('click', function () {
            var tabUrl = button.getAttribute('data-tab-url');
            if (!tabUrl || !content || button.classList.contains('is-active')) return;

            buttons.forEach(function (item) {
              item.disabled = true;
            });
            content.classList.add('is-loading');

            fetch(tabUrl, {
              headers: {
                'X-Requested-With': 'XMLHttpRequest'
              }
            })
              .then(function (response) {
                if (!response.ok) {
                  throw new Error('Gagal memuat tab');
                }

                return response.text();
              })
              .then(function (html) {
                content.innerHTML = html;

                buttons.forEach(function (item) {
                  var isActive = item === button;
                  item.classList.toggle('is-active', isActive);
                  item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                bindTreeNodeActions(content);
                bindFilterTables(content);
                bindFilterBlocks(content);
                bindTreeCanvases(content);
              })
              .catch(function () {
                content.innerHTML = '<div class="empty-state">Tab belum bisa dimuat. Coba ulangi lagi.</div>';
              })
              .finally(function () {
                buttons.forEach(function (item) {
                  item.disabled = false;
                });
                content.classList.remove('is-loading');
              });
          });
        });
      });
    }

    bindCampusTabs(document);

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
