{{-- Confirmation / result modal shared by dashboard & scanner --}}
<div class="modal fade" id="scanResultModal" tabindex="-1" aria-labelledby="scanResultModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content" style="border-radius: 20px; border: 0;">
			<div class="modal-header border-0 pb-0">
				<h2 class="modal-title fs-5 fw-bold" id="scanResultModalLabel">Visitor Verification</h2>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div id="scanModalAlert" class="alert d-none" role="alert"></div>
				<div class="row g-3">
					<div class="col-md-4 text-center">
						<img id="scanVisitorPhoto" src="" alt="Visitor photo" class="img-fluid rounded-4 mb-2 d-none" style="max-height: 180px; object-fit: cover; width: 100%; background: #eef4ff;">
						<div id="scanVisitorPhotoFallback" class="rounded-4 d-flex align-items-center justify-content-center mb-2" style="height: 160px; background: #eef4ff; color: #064A9F;">
							<i class="bi bi-person-bounding-box" style="font-size: 3rem;" aria-hidden="true"></i>
						</div>
						<div id="scanAuthBadge" class="badge-status badge-success">Correct destination</div>
					</div>
					<div class="col-md-8">
						<dl class="row mb-0 small">
							<dt class="col-sm-4 text-muted">Full name</dt><dd class="col-sm-8 fw-semibold" id="scanVisitorName">—</dd>
							<dt class="col-sm-4 text-muted">Control number</dt><dd class="col-sm-8" id="scanControlNumber">—</dd>
							<dt class="col-sm-4 text-muted">Pass number</dt><dd class="col-sm-8" id="scanPassNumber">—</dd>
							<dt class="col-sm-4 text-muted">Purpose</dt><dd class="col-sm-8" id="scanPurpose">—</dd>
							<dt class="col-sm-4 text-muted">Destination</dt><dd class="col-sm-8" id="scanDestination">—</dd>
							<dt class="col-sm-4 text-muted">Previous office</dt><dd class="col-sm-8" id="scanPreviousOffice">—</dd>
							<dt class="col-sm-4 text-muted">Expected office</dt><dd class="col-sm-8" id="scanCurrentOffice">—</dd>
							<dt class="col-sm-4 text-muted" id="scanStaffOfficeLabel">Scanned office</dt><dd class="col-sm-8" id="scanStaffOffice">—</dd>
							<dt class="col-sm-4 text-muted">Visit date</dt><dd class="col-sm-8" id="scanVisitDate">—</dd>
							<dt class="col-sm-4 text-muted">Status</dt><dd class="col-sm-8" id="scanVisitStatus">—</dd>
						</dl>
						<div class="mt-3">
							<div class="text-muted small mb-1">Remaining route</div>
							<div id="scanRemainingRoute" class="d-flex flex-wrap gap-2"></div>
						</div>
						<div id="scanWrongOfficeGuidance" class="alert alert-warning mt-3 mb-0 d-none" role="note">
							<div class="fw-semibold mb-1">What to do:</div>
							<ul class="mb-0 ps-3 small">
								<li>Ask visitor to wait for security</li>
								<li>Do not allow entry without verification</li>
								<li>Provide directions to correct office if needed</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer border-0 pt-0">
				<button type="button" class="btn btn-nu-outline d-none" data-bs-dismiss="modal" id="scanCancelBtn">Cancel</button>
				<button type="button" class="btn btn-nu-primary" id="scanConfirmBtn">
					<i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i>
					Done
				</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="manualPayloadModal" tabindex="-1" aria-labelledby="manualPayloadModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content" style="border-radius: 20px; border: 0;">
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
