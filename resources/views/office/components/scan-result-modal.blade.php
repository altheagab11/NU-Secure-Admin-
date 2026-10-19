{{-- Confirmation / result modal shared by dashboard & scanner --}}
<div class="modal fade" id="scanResultModal" tabindex="-1" aria-labelledby="scanResultModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable verification-dialog">
		<div class="modal-content scan-modal-content">
			<div class="modal-header verification-header">
				<div class="verification-heading">
					<i class="bi bi-shield-check" aria-hidden="true"></i>
					<h2 class="modal-title" id="scanResultModalLabel">Visitor Verification</h2>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body verification-body">
				<div id="scanModalAlert" class="alert verification-alert d-none" role="alert">
					<i id="scanModalAlertIcon" class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
					<span id="scanModalAlertText"></span>
				</div>
				<div class="verification-info">
					<div class="verification-photo-column">
						<img id="scanVisitorPhoto" src="" alt="Visitor photo" class="img-fluid rounded-4 mb-2 d-none scan-visitor-photo">
						<div id="scanVisitorPhotoFallback" class="rounded-4 d-flex align-items-center justify-content-center mb-2 scan-visitor-photo-fallback">
							<i class="bi bi-person-bounding-box scan-photo-icon" aria-hidden="true"></i>
						</div>
						<div id="scanAuthBadge" class="verification-auth-badge is-authorized">Correct destination</div>
					</div>
					<dl class="verification-details">
						<dt><i class="bi bi-person" aria-hidden="true"></i>Full name</dt><dd class="verification-name" id="scanVisitorName">—</dd>
						<dt><i class="bi bi-person-vcard" aria-hidden="true"></i>Control number</dt><dd id="scanControlNumber">—</dd>
						<dt><i class="bi bi-card-text" aria-hidden="true"></i>Pass number</dt><dd id="scanPassNumber">—</dd>
						<dt><i class="bi bi-chat-square-text" aria-hidden="true"></i>Purpose</dt><dd id="scanPurpose">—</dd>
						<dt><i class="bi bi-shield-check" aria-hidden="true"></i>Destination (Registered)</dt><dd class="verification-office" id="scanDestination">—</dd>
						<dt><i class="bi bi-building" aria-hidden="true"></i>Previous office</dt><dd id="scanPreviousOffice">—</dd>
						<dt><i class="bi bi-geo-alt" aria-hidden="true"></i>Expected office</dt><dd class="verification-office" id="scanCurrentOffice">—</dd>
						<dt id="scanStaffOfficeLabel"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i>Scanned office</dt><dd id="scanStaffOffice">—</dd>
						<dt><i class="bi bi-calendar3" aria-hidden="true"></i>Visit date</dt><dd id="scanVisitDate">—</dd>
						<dt><i class="bi bi-patch-check" aria-hidden="true"></i>Status</dt><dd><span class="verification-status" id="scanVisitStatus">—</span></dd>
					</dl>
					<aside class="verification-route" aria-labelledby="scanRouteHeading">
						<h3 id="scanRouteHeading"><i class="bi bi-signpost-split" aria-hidden="true"></i>Remaining route</h3>
						<ol id="scanRemainingRoute" class="verification-route-list"></ol>
					</aside>
				</div>
				<section id="officeVerificationActions" class="verification-actions d-none" aria-labelledby="officeVerificationHeading">
					<div class="verification-section-heading">
						<i class="bi bi-buildings" aria-hidden="true"></i>
						<div>
							<h3 id="officeVerificationHeading">Office Visit Verification</h3>
							<p>This visitor is not registered to visit your office. Choose an action below.</p>
						</div>
					</div>
					<div class="verification-action-grid">
						<button type="button" class="verification-action verification-action-add" id="showAddOffice" aria-controls="addOfficePanel" aria-expanded="false">
							<span class="verification-action-icon"><i class="bi bi-plus-circle-fill" aria-hidden="true"></i></span>
							<span class="verification-action-copy"><strong>Add This Office</strong><span>Add your office to the visitor's route and record the reason for this visit.</span></span>
							<i class="bi bi-chevron-right verification-action-chevron" aria-hidden="true"></i>
							<span class="verification-action-helper">Use this if the visitor needs to visit your office.</span>
						</button>
						<button type="button" class="verification-action verification-action-wrong" id="showWrongOffice" aria-controls="wrongOfficePanel" aria-expanded="false">
							<span class="verification-action-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
							<span class="verification-action-copy"><strong>Mark as Wrong Office</strong><span>Log a note/remarks for this scan without adding your office to the visitor's route.</span></span>
							<i class="bi bi-chevron-right verification-action-chevron" aria-hidden="true"></i>
							<span class="verification-action-helper">Use this if the visitor scanned at the wrong office.</span>
						</button>
					</div>
				</section>
				<section id="addOfficePanel" class="border rounded-4 p-3 mt-3 d-none" aria-labelledby="addOfficeHeading">
					<h3 id="addOfficeHeading" class="fs-6 fw-bold">Add Additional Office</h3>
					<form id="addOfficeForm">
						<div class="mb-3">
							<label for="officeToAdd" class="form-label fw-semibold">Office to Add</label>
							<input id="officeToAdd" class="form-control" readonly>
						</div>
						<div class="mb-3">
							<label for="officeAdditionReason" class="form-label fw-semibold">Reason for Visit <span class="text-danger">*</span></label>
							<textarea id="officeAdditionReason" class="form-control" rows="3" required maxlength="255" placeholder="Enter the reason for visiting this office..."></textarea>
							<div class="form-text">Maximum 255 characters.</div>
						</div>
						<div class="form-check mb-2">
							<input id="officeAutomaticCheckIn" class="form-check-input" type="checkbox" checked aria-describedby="officeCheckInHelp">
							<label for="officeAutomaticCheckIn" class="form-check-label">Proceed with office check-in after adding</label>
						</div>
						<p id="officeCheckInHelp" class="small text-muted">The visitor will automatically be checked in after successful confirmation when selected.</p>
						<div id="addOfficeError" class="alert alert-danger d-none" role="alert" aria-live="polite"></div>
						<div class="d-flex flex-wrap justify-content-end gap-2">
							<button type="button" class="btn btn-secondary" data-cancel-adjustment>Cancel</button>
							<button type="submit" class="btn btn-nu-primary" id="addOfficeSubmit">Add Office and Check-in</button>
						</div>
					</form>
				</section>
				<section id="wrongOfficePanel" class="border rounded-4 p-3 mt-3 d-none" aria-labelledby="wrongOfficeHeading">
					<h3 id="wrongOfficeHeading" class="fs-6 fw-bold">Log Wrong Office Note</h3>
					<form id="wrongOfficeForm">
						<div class="mb-3">
							<label for="wrongCurrentOffice" class="form-label fw-semibold">Current Office</label>
							<input id="wrongCurrentOffice" class="form-control" readonly>
						</div>
						<div class="mb-3">
							<label for="wrongExpectedOffice" class="form-label fw-semibold">Expected Destination</label>
							<input id="wrongExpectedOffice" class="form-control" readonly>
						</div>
						<div class="mb-3">
							<label for="wrongOfficeRemarks" class="form-label fw-semibold">Note / Remarks <span class="text-danger">*</span></label>
							<textarea id="wrongOfficeRemarks" class="form-control" rows="3" required maxlength="500" placeholder="Explain why the visitor scanned at the wrong office..."></textarea>
							<div class="form-text">Maximum 500 characters.</div>
						</div>
						<div id="wrongOfficeError" class="alert alert-danger d-none" role="alert" aria-live="polite"></div>
						<div class="d-flex flex-wrap justify-content-end gap-2">
							<button type="button" class="btn btn-secondary" data-cancel-adjustment>Cancel</button>
							<button type="submit" class="btn btn-danger">Save Remarks</button>
						</div>
					</form>
				</section>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="manualPayloadModal" tabindex="-1" aria-labelledby="manualPayloadModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content scan-modal-content">
			<div class="modal-header border-0">
				<h2 class="modal-title fs-5 fw-bold" id="manualPayloadModalLabel">Enter QR Payload Manually</h2>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id="manualPayloadForm">
				<div class="modal-body">
					<p class="text-muted small">Enter the QR JSON payload, control number, pass number, or QR token. The same server validation applies as camera scanning.</p>
					<div class="mb-3">
						<label for="manualQrPayload" class="form-label fw-semibold">QR payload / token</label>
						<textarea id="manualQrPayload" class="form-control" rows="3" placeholder='{"control_number":"...","qr_token":"..."}' aria-describedby="manualHelp"></textarea>
						<div id="manualHelp" class="form-text">You may also paste an enrollee progress URL.</div>
					</div>
					<div class="row g-2">
						<div class="col-md-6">
							<label for="manualControlNumber" class="form-label fw-semibold">Control number</label>
							<input type="text" id="manualControlNumber" class="form-control" autocomplete="off">
						</div>
						<div class="col-md-6">
							<label for="manualPassNumber" class="form-label fw-semibold">Pass number</label>
							<input type="text" id="manualPassNumber" class="form-control" autocomplete="off">
						</div>
					</div>
					<div id="manualError" class="alert alert-danger mt-3 d-none" role="alert"></div>
				</div>
				<div class="modal-footer border-0">
					<button type="button" class="btn btn-nu-outline" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-nu-primary" id="manualSubmitBtn">Verify</button>
				</div>
			</form>
		</div>
	</div>
</div>
