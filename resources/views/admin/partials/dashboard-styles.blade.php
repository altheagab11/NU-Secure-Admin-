/* Dashboard-specific styles; shared navigation remains unchanged. */
.dashboard-main { background: #f5f7ff; }
.dashboard-main .container-fluid { padding-inline: 0; }
.dashboard-main .admin-topbar { border: 1px solid #e7ecf6; box-shadow: 0 2px 8px #243c9608; }
.dashboard-meta { margin: -4px 0 12px; font-size: 12px; }
.dashboard-main .card, .dash-chart-card, .dashboard-filter-bar {
    background: #fff; border: 1px solid #e7ecf6 !important; border-radius: 12px !important;
    box-shadow: 0 2px 8px #243c9608 !important; min-width: 0;
}
.dashboard-main .card-body, .dash-chart-card .card-body { padding: 16px; }
.dashboard-main .row.g-4 { --bs-gutter-x: 16px; --bs-gutter-y: 16px; margin-bottom: 16px !important; }
.dashboard-main h5.fw-semibold, .dash-chart-title { font-size: 16px; font-weight: 700 !important; color: #13235b; }
.dashboard-main h2 { font-size: 28px; color: #13235b; letter-spacing: -.03em; }
.metric-body { display: flex; gap: 14px; align-items: center; min-width: 0; }
.metric-body > div { min-width: 0; }
.metric-icon { display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; flex-shrink: 0; border-radius: 14px; font-size: 24px; background: var(--tone-bg); color: var(--tone); }
.tone-blue { --tone: #075cff; --tone-bg: #eaf1ff; }
.tone-green { --tone: #059c56; --tone-bg: #e5f8ee; }
.tone-purple { --tone: #8a38ef; --tone-bg: #f1eaff; }
.tone-amber { --tone: #e98a00; --tone-bg: #fff4e0; }
.tone-red { --tone: #ee2946; --tone-bg: #ffeaee; }
.tone-gray { --tone: #64748b; --tone-bg: #edf0f5; }
.dashboard-filter-bar { padding: 14px 16px; }
.dashboard-filter-form { display: grid; grid-template-columns: auto repeat(4, minmax(0, 1fr)) auto; align-items: end; gap: 14px; }
.filter-heading { display: flex; align-items: center; gap: 10px; align-self: center; color: #13235b; font-size: 14px; }
.filter-heading .metric-icon { width: 36px; height: 36px; border-radius: 10px; font-size: 18px; }
.filter-field { min-width: 0; }
.filter-field .form-label { font-size: 12px; color: #42547d; margin-bottom: 5px; }
.filter-field .form-select { font-size: 13px; min-height: 40px; border-color: #dfe6f3; }
.filter-actions { display: flex; gap: 8px; }
.filter-actions .btn { min-height: 40px; font-size: 13px; white-space: nowrap; }
.active-filter-note { color: #42547d; font-size: 12px; margin-top: 12px; }
.severity-grid, .dashboard-insights { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.severity-card { display: block; padding: 14px; background: #f7f9fd; border-radius: 10px; text-decoration: none; color: var(--tone); }
.severity-count { font-size: 26px; font-weight: 700; line-height: 1.2; }
.severity-share { font-size: 12px; margin: 10px 0 4px; }
.severity-progress { width: 100%; height: 6px; display: block; border: 0; border-radius: 10px; overflow: hidden; appearance: none; background: #e1e7f0; color: var(--tone); }
.severity-progress::-webkit-progress-bar { background: #e1e7f0; }
.severity-progress::-webkit-progress-value { background: var(--tone); border-radius: 10px; }
.severity-progress::-moz-progress-bar { background: var(--tone); }
.mini-summary-box { background: #f7f9fd; padding: 12px; border-radius: 10px; height: 100%; }
.mini-summary-box .metric-icon { width: 40px; height: 40px; font-size: 20px; }
.mini-summary-box .fs-4 { font-size: 20px !important; }
.dash-chart-card { height: auto; }
.dash-chart-title { margin: 0 0 12px; }
.dash-chart-canvas { position: relative; height: 220px; min-width: 0; }
.dash-chart-canvas.is-tall { height: 240px; }
.chart-empty { position: absolute; top: 0; right: 0; font-size: 12px; color: #64748b; background: #ffffffed; padding: 4px 8px; border-radius: 6px; pointer-events: none; }
.dashboard-main .table { font-size: 12px; margin-bottom: 0; border-color: #e7ecf6; }
.dashboard-main .table th { color: #13235b; font-weight: 700; background: #f5f7fc; }
.dashboard-main .table td, .dashboard-main .table th { padding: 9px 8px; }
.dashboard-main .table td { overflow-wrap: anywhere; }
.dashboard-main .table td:first-child, .dashboard-main .table td:last-child { white-space: nowrap; }
.dashboard-main .table .btn { padding: 2px 12px; font-size: 12px; }
.dashboard-main .badge { font-weight: 600; }
.dashboard-main .btn-outline-primary { border-color: #b7caff; }
.insight-tile { display: flex; align-items: flex-start; gap: 12px; padding: 12px; border: 1px solid #e4eaf5; border-radius: 10px; min-width: 0; }
.insight-tile h5 { font-size: 12px; margin: 0 0 5px; color: #42547d; }
.insight-value { font-size: 13px; color: #1743a6; font-weight: 600; overflow-wrap: anywhere; }
.insight-tile p { font-size: 11px; color: #64748b; margin: 5px 0 0; }
.insight-tile .metric-icon { width: 40px; height: 40px; font-size: 20px; }
.dashboard-main a:focus-visible, .dashboard-main button:focus-visible, .dashboard-main select:focus-visible { outline: 3px solid #8faeff; outline-offset: 3px; }
@media (max-width: 1199.98px) {
    .dashboard-filter-form { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .filter-heading { grid-column: 1 / -1; }
    .filter-actions { grid-column: 1 / -1; justify-content: flex-end; }
    .severity-grid, .dashboard-insights { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 767.98px) {
    .dashboard-filter-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .dashboard-main .card-body, .dash-chart-card .card-body { padding: 14px; }
    .dashboard-main .admin-topbar { flex-wrap: wrap; gap: 10px; }
    .dashboard-main .table { min-width: 460px; }
}
@media (max-width: 575.98px) {
    .dashboard-filter-form, .severity-grid, .dashboard-insights { grid-template-columns: minmax(0, 1fr); }
    .filter-actions .btn { flex: 1; }
    .dashboard-main .d-flex.justify-content-between { flex-wrap: wrap; gap: 10px; }
    .dash-chart-canvas.is-tall { height: 260px; }
}
