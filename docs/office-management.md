# Office Management

The existing `/admin/user/offices` page now has an Office Directory above the Office User Accounts card. The sidebar and top navigation remain in place. Directory records and staff accounts use separate routes and forms.

## Features

- Office name search, Active/Inactive filtering, independent pagination and page size.
- Add/edit Bootstrap modals for office name, room number, existing floor field, and status.
- Case-insensitive duplicate-name validation, including trimmed case variants; PostgreSQL advisory locking serializes directory name checks for concurrent directory writes.
- Required room and floor for new offices. Existing offices may retain null locations when edited.
- Soft deactivation/reactivation using `office.is_active`; no directory delete route exists.
- Deactivation is blocked while any visitor is still inside with that office as a primary or route destination, or any non-recycled staff account remains assigned. This also applies when the status is changed in the Edit modal. Reassign/recycle accounts and complete visits before deactivating.
- Audits in `activity_logs` include the admin user, affected office, old/new values, action, and timestamp. Audit failures roll back the directory write.
- Existing staff account creation, editing, filtering, pagination, recycling, and restoration remain available. Existing assignments to inactive offices can be retained while editing other account details. New assignments require an active office.
- New active offices appear in both staff assignment choices and Guard visitor destinations. Registration refreshes the directory after ID identification and uses only database office names, floors, and room numbers. Offices with no saved room number do not display an invented room number. Location details are also carried into the selected destinations and QR ticket.
- Existing visit and scan foreign keys are preserved. Office adjustments continue checking active offices through their existing workflow.

Directory actions return to the current page URL, preserving filters. The directory and account filter forms carry the other section's query parameters. Existing notifications, reports, QR scanning, account email onboarding, and account recycle behavior keep their current workflows.

## Migration

`2026_10_19_010000_add_room_number_to_office_table.php` adds only nullable `office.room_number VARCHAR(30)`. It preserves existing rows and uses the existing `floor` and `is_active` columns. No `floor_number` column is added.

The migration was applied successfully to the configured PostgreSQL database on October 19, 2026.

For another deployment:

```powershell
php artisan migrate --path=database/migrations/2026_10_19_010000_add_room_number_to_office_table.php
```

## Files modified

- `app/Http/Controllers/OfficeController.php`: existing account page data, independent directory pagination, active-office assignment validation, legacy inactive assignment support.
- `app/Http/Controllers/GuardVisitorController.php`: return saved room numbers with active destinations.
- `resources/views/admin/user.blade.php`: Office Management heading and separated cards; existing account controls retained.
- `resources/views/admin/partials/admin-topbar.blade.php`: optional title icon and subtitle; other pages retain their defaults.
- `resources/views/guard/register.blade.php`: show saved room numbers in destination choices.
- `routes/web.php`: administrator-only directory create, update, and status routes, separate from account routes.

## Files created

- `app/Models/Office.php`
- `app/Http/Controllers/AdminOfficeDirectoryController.php`
- `database/migrations/2026_10_19_010000_add_room_number_to_office_table.php`
- `resources/views/admin/partials/office-directory.blade.php`
- `resources/views/admin/partials/office-directory-modals.blade.php`
- `resources/views/admin/partials/office-directory-scripts.blade.php`
- `resources/views/admin/partials/office-directory-styles.blade.php`
- `resources/views/admin/partials/office-account-filters.blade.php`
- `tests/Feature/OfficeDirectoryTest.php`
- `tests/frontend/registration-office-locations.test.mjs`
- `docs/office-management.md`

## Tests

```powershell
php artisan test --compact --filter=OfficeDirectoryTest
php artisan test --compact --filter=OfficeAdjustmentTest
node --test tests/frontend/registration-office-locations.test.mjs
php artisan view:cache
```

The 12 feature tests passed (131 assertions): creation, duplicate/required-field validation, editing, deactivation/reactivation, protection of active visits and assigned staff, independent filters/pagination, destination/assignment integration, role restrictions, existing account create/edit/recycle/restore, legacy null locations, inactive assignments, migration rollback, and registration consistency after admin location edits. Four frontend tests verify directory-only locations for kiosk/Guard registration, selected destinations and QR tickets, enrollee locations, and protection against stale responses.

The full PHP suite returned 156 passed, 26 skipped, and the previously observed activity-log guest redirect failure: the API responds with 401 while the test expects a login redirect. Office adjustment and existing scanner tests passed in this run.

Nine Chrome checks used rendered Blade HTML and isolated test data at desktop, tablet, and phone widths, including 320×480. Add/edit/status modals, saving-state recovery on back navigation, and existing account controls were exercised without JavaScript errors or page overflow. Browser form writes were intercepted; mutation behavior was verified by the Laravel tests. No live office, staff, visitor, or scan records were created or changed by these checks.

For manual acceptance: create an office with `G-03` / `Ground Floor`, verify its directory row and assignment/destination choices, edit its location, deactivate and reactivate an unassigned office, then confirm a staffed office or an office with an active visitor cannot be deactivated. Test long office names, status filtering, both pagination controls, and mobile table scrolling.

Physical scanners/cameras and simultaneous PostgreSQL transactions were not exercised. Existing generated report files remain historical artifacts; new report generation continues using its existing office queries.
