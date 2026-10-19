.office-management-page .admin-topbar-title { display: flex; align-items: center; gap: 12px; }
.office-management-page .admin-topbar-title i { color: #0056b3; }
.office-management-page .office-management-card,
.office-management-page #officeAccounts { margin-bottom: 22px; background: #fff; border: 1px solid #e0e8f4; border-radius: 17px; box-shadow: 0 5px 18px rgba(15,31,80,.045); overflow: hidden; padding: 0; }
.office-management-page .office-management-card-head { display: flex; justify-content: space-between; align-items: center; gap: 20px; padding: 20px; background: linear-gradient(110deg,#f5f9ff,#fff); border-bottom: 1px solid #edf2fa; }
.office-management-page .office-management-heading { display: flex; align-items: flex-start; gap: 12px; min-width: 0; }
.office-management-page .office-management-heading > i { color: #0056b3; font-size: 24px; line-height: 1.3; }
.office-management-page .office-management-heading h2 { font-size: 18px; color: #0f1f50; font-weight: 750; margin: 0 0 5px; }
.office-management-page .office-management-heading p { font-size: 12px; color: #526586; margin: 0; line-height: 1.6; }
.office-management-page .office-management-card-body { padding: 16px 20px 4px; }
.office-management-page .directory-primary { background: #0056b3; border-color: #0056b3; font-size: 13px; font-weight: 650; border-radius: 8px; padding: 10px 16px; white-space: nowrap; }
.office-management-page .directory-filters { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
.office-management-page .directory-search { position: relative; flex: 1 1 260px; }
.office-management-page .directory-search > i { position: absolute; top: 50%; left: 12px; transform: translateY(-50%); color: #6c82a3; }
.office-management-page .directory-search input { padding-left: 36px; }
.office-management-page .directory-filters .form-select { flex: 0 1 180px; width: auto; }
.office-management-page .directory-filters .form-control,
.office-management-page .directory-filters .form-select,
.office-management-page .directory-filters .btn { font-size: 12px; min-height: 39px; border-radius: 7px; }
.office-management-page .office-management-table-wrap { max-width: 100%; overflow-x: auto; border: 1px solid #e9eef6; border-radius: 9px; }
.office-management-page .office-management-table-wrap table { width: 100%; min-width: 680px; margin: 0; border-collapse: collapse; }
.office-management-page .office-management-table-wrap th { background: #f5f8fd; color: #0f1f50; font-size: 12px; font-weight: 700; white-space: nowrap; }
.office-management-page .office-management-table-wrap td { font-size: 12px; padding-top: 11px; padding-bottom: 11px; }
.office-management-page .directory-office-name { min-width: 170px; overflow-wrap: anywhere; }
.office-management-page .directory-row-actions { display: flex; gap: 14px; }
.office-management-page .directory-icon-action { display: inline-grid; place-items: center; width: 30px; height: 30px; border: 0; background: #edf5ff; color: #0056b3; border-radius: 6px; cursor: pointer; transition: background 160ms ease; }
.office-management-page .directory-icon-action:hover { background: #dcecff; }
.office-management-page .directory-icon-action.is-danger { background: #fff0f1; color: #dc3545; }
.office-management-page .directory-icon-action.is-danger:hover { background: #ffe0e4; }
.office-management-page .directory-icon-action.is-success { background: #e5f7ed; color: #187545; }
.office-management-page .directory-status { display: inline-block; padding: 4px 10px; border-radius: 30px; font-size: 11px; font-weight: 700; white-space: nowrap; }
.office-management-page .directory-status.is-active { background: #dcfce8; color: #166745; }
.office-management-page .directory-status.is-inactive { background: #ffe1e5; color: #ae293a; }
.office-management-page #officeAccounts .office-filters-form { margin: 16px 20px; }
.office-management-page #officeAccounts .filters-card { box-shadow: none; border: 0; padding: 0; background: #fff; margin: 0; }
.office-management-page #officeAccounts .office-management-table-wrap { margin: 0 20px; }
.office-management-page #officeAccounts .table-pagination-bar { margin: 0 20px; }
.office-management-page .office-directory-modal .modal-content { border: 0; border-radius: 16px; box-shadow: 0 20px 65px rgba(15,31,80,.22); overflow: hidden; }
.office-management-page .office-directory-modal .modal-header { padding: 19px 20px; background: #f9fbff; border-bottom-color: #edf2fa; }
.office-management-page .office-directory-modal .modal-title { font-size: 18px !important; font-weight: 750; color: #0f1f50; }
.office-management-page .office-directory-modal .modal-title i { color: #0056b3; }
.office-management-page .office-directory-modal .modal-body { padding: 20px; }
.office-management-page .office-directory-modal form { display: flex; flex-direction: column; min-height: 0; overflow: hidden; }
.office-management-page .office-directory-modal .modal-body { overflow-y: auto; }
.office-management-page .office-directory-modal .form-label { font-size: 13px; font-weight: 650; }
.office-management-page .office-directory-modal .form-control { font-size: 13px; min-height: 42px; border-color: #d6e0ef; }
.office-management-page .office-directory-modal .modal-footer { padding: 8px 20px 20px; border: 0; gap: 6px; }
.office-management-page .office-directory-modal .btn-secondary { background: #edf0f5; color: #0f1f50; border-color: #edf0f5; }
.office-management-page .office-directory-modal .form-switch { display: flex; align-items: center; gap: 9px; }
.office-management-page .office-directory-modal .form-switch .form-check-input { width: 42px; height: 24px; margin-top: 0; }
.office-management-page .office-directory-modal .form-switch .form-check-label { font-size: 13px; color: #0056b3; font-weight: 650; }
.office-management-page button:focus-visible { outline: 3px solid #8dbaff; outline-offset: 3px; }
@media(max-width:767.98px) {
 .office-management-page .office-management-card-head { align-items: flex-start; flex-direction: column; gap: 14px; padding: 16px; }
 .office-management-page .office-management-card-head .header-actions { flex-wrap: wrap; }
 .office-management-page .office-management-card-body { padding: 14px; }
 .office-management-page #officeAccounts .office-filters-form { margin: 14px; }
 .office-management-page #officeAccounts .office-management-table-wrap,
 .office-management-page #officeAccounts .table-pagination-bar { margin-left: 14px; margin-right: 14px; }
}
@media(max-width:575.98px) {
 .office-management-page .directory-filters { display: grid; grid-template-columns: minmax(0,1fr); }
 .office-management-page .directory-filters .form-select { width: 100%; }
 .office-management-page .office-management-heading h2 { font-size: 17px; }
}
