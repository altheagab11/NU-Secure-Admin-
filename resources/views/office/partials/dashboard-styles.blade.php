.dashboard-date { margin: 3px 0 0; font-size: 12px; color: #64748b; }
.office-dashboard { color: #13234b; }
.office-dashboard .office-card { padding: 20px; border: 1px solid #e6ebf7; border-radius: 16px; box-shadow: 0 3px 12px #213c960a; min-width: 0; }
.dashboard-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)) minmax(180px, .8fr); gap: 16px; margin-bottom: 20px; }
.dashboard-stat { display: flex; align-items: center; gap: 16px; }
.dashboard-icon { width: 54px; height: 54px; display: inline-flex; align-items: center; justify-content: center; border-radius: 18px; flex-shrink: 0; font-size: 27px; }
.tone-amber { color: #e99500; background: #fff1d0; }
.tone-blue { color: #0755ed; background: #e9eeff; }
.tone-green { color: #00a767; background: #e0f8ee; }
.stat-label { font-size: 13px; color: #42547d; }
.stat-value { font-size: 30px; font-weight: 800; line-height: 1.25; }
.dashboard-scanner-action { display: flex; align-items: center; justify-content: center; }
.dashboard-scanner-action .btn { width: 100%; min-height: 54px; display: inline-flex; align-items: center; justify-content: center; gap: 10px; }
.dashboard-columns { display: grid; grid-template-columns: minmax(0, .85fr) minmax(0, 1.15fr); gap: 18px; align-items: start; }
.dashboard-column { display: grid; gap: 18px; align-items: start; min-width: 0; }
.dashboard-card-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
.dashboard-card-heading h2 { margin-bottom: 4px; }
.dashboard-heading-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; justify-content: flex-end; }
.dashboard-card-heading .btn { white-space: nowrap; }
.office-dashboard .card-muted { font-size: 13px; }
.office-dashboard .scan-promo-panel { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; min-height: 0; padding: 28px 16px; border-radius: 14px; background: linear-gradient(135deg, #0646b5, #1260df); margin: 18px 0 14px; }
.office-dashboard .scan-promo-icon { font-size: 54px; line-height: 1.2; }
.scan-promo-panel strong { font-size: 22px; }
.scanner-buttons { display: grid; gap: 10px; }
.scanner-buttons .btn { min-height: 46px; }
.dashboard-records { display: grid; gap: 8px; }
.dashboard-record { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 12px; border: 1px solid #e5eaf6; border-radius: 12px; min-width: 0; background: #fff; }
.dashboard-record-main { display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1; }
.dashboard-record-copy { min-width: 0; overflow-wrap: anywhere; }
.dashboard-avatar, .dashboard-visitor-photo { width: 42px; height: 42px; border-radius: 13px; flex-shrink: 0; }
.dashboard-avatar { display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; background: #e8eeff; color: #0755dd; }
.dashboard-visitor-photo { object-fit: cover; }
.dashboard-record-name { font-weight: 700; font-size: 14px; color: #14264c; }
.dashboard-record-meta { font-size: 12px; color: #5b6d8f; margin-top: 2px; }
.dashboard-record-actions { display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; gap: 8px; flex-shrink: 0; max-width: 40%; }
.dashboard-record-actions .btn { min-height: 36px; padding-inline: 16px; }
.record-detail-link { font-size: 12px; color: #0755dd; text-decoration: none; }
.dashboard-empty { padding: 28px 12px; min-height: 0; }
.latest-scan-note { font-size: 12px; color: #64748b; margin: 12px 0 0; }
.latest-scan-note:empty { display: none; }
.dashboard-list-modal .modal-content { border-radius: 18px; overflow: hidden; border: 1px solid #e5eaf6; }
.dashboard-list-modal .modal-body { padding: 20px; }
.dashboard-modal-filters { display: grid; grid-template-columns: minmax(180px, 2fr) minmax(120px, 1fr) minmax(140px, 1fr) auto; gap: 12px; align-items: end; margin-bottom: 20px; }
.dashboard-modal-filters .form-label { font-size: 12px; color: #42547d; margin-bottom: 5px; }
.dashboard-modal-filter-actions { display: flex; gap: 8px; }
.dashboard-modal-footer .table-pagination-bar { margin: 0; flex-wrap: wrap; gap: 12px; }
.dashboard-list-modal .dashboard-record { align-items: flex-start; }
.dashboard-list-modal .dashboard-record-meta { line-height: 1.5; }
.office-dashboard a:focus-visible, .office-dashboard button:focus-visible { outline: 3px solid #a4baff; outline-offset: 3px; }
@media(max-width: 1199.98px) {
    .dashboard-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .dashboard-columns { display: flex; flex-direction: column; gap: 18px; }
    .dashboard-column { display: contents; }
    .dashboard-columns > .dashboard-column > section { width: 100%; }
    section[aria-labelledby="quickScanHeading"] { order: 1; }
    section[aria-labelledby="readyHeading"] { order: 2; }
    section[aria-labelledby="recentHeading"] { order: 3; }
    section[aria-labelledby="expectedHeading"] { order: 4; }
    .dashboard-modal-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media(max-width: 991.98px) and (min-width: 768px) {
    .dashboard-columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .dashboard-column { display: grid; }
}
@media(max-width: 575.98px) {
    .dashboard-stats, .dashboard-modal-filters { grid-template-columns: minmax(0, 1fr); }
    .office-dashboard .office-card { padding: 16px; }
    .dashboard-card-heading { flex-wrap: wrap; }
    .dashboard-record { padding: 10px; gap: 8px; }
    .dashboard-record-main { gap: 8px; }
    .dashboard-record-actions { max-width: 32%; flex-direction: column; align-items: flex-end; }
    .dashboard-record-actions .badge-status { white-space: normal; text-align: center; }
    .dashboard-list-modal .modal-dialog { margin: 8px; }
    .dashboard-list-modal .modal-body { padding: 14px; }
    .dashboard-modal-footer .table-pagination-left, .dashboard-modal-footer .table-pagination-right { flex-wrap: wrap; }
    .scanner-buttons .btn, .dashboard-record-actions .btn { min-height: 44px; }
}
