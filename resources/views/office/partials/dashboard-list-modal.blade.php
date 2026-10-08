<div class="modal fade dashboard-list-modal" id="dashboardListModal" tabindex="-1" aria-labelledby="dashboardListTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><div><h2 class="modal-title fs-5" id="dashboardListTitle">Visitors</h2><p class="card-muted small mb-0" id="dashboardListSubtitle"></p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <form id="dashboardListFilters" class="dashboard-modal-filters">
                    <div><label for="dashboardListSearch" class="form-label">Search visitor</label><input type="search" id="dashboardListSearch" class="form-control" placeholder="Name, control number, or purpose" maxlength="255"></div>
                    <div><label for="dashboardListStatus" class="form-label">Status</label><select id="dashboardListStatus" class="form-select"><option value="">All statuses</option></select></div>
                    <div id="dashboardListOfficeField"><label for="dashboardListOffice" class="form-label">Previous office</label><select id="dashboardListOffice" class="form-select"><option value="">All previous offices</option></select></div>
                    <div class="dashboard-modal-filter-actions"><button type="submit" class="btn btn-nu-primary">Apply</button><button type="reset" class="btn btn-nu-outline">Reset</button></div>
                </form>
                <div id="dashboardListError" class="alert alert-danger d-none" role="alert"></div>
                <div id="dashboardListRecords" class="dashboard-records" aria-live="polite"></div>
            </div>
            <div class="modal-footer dashboard-modal-footer">
                <div id="dashboardListPagination" class="w-100" hidden>
                    @include('admin.partials.table-pagination', ['paginator' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1), 'perPageParam' => 'dashboard_list_per_page', 'ariaLabel' => 'Full visitor list pagination'])
                </div>
                <button type="button" class="btn btn-nu-outline" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
