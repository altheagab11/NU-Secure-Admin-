.table-pagination-bar {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 16px;
	flex-wrap: wrap;
	margin-top: 12px;
	padding: 10px 14px;
	border: 1px solid #e5e7eb;
	border-radius: 10px;
	background: #fff;
}

.table-pagination-left,
.table-pagination-right {
	display: flex;
	align-items: center;
	gap: 10px;
}

.table-pagination-label,
.table-pagination-range,
.table-pagination-page {
	font-size: 13px;
	color: #6b7280;
	white-space: nowrap;
}

.table-pagination-page strong {
	color: #111827;
	font-weight: 700;
}

.table-page-size {
	min-width: 56px;
	height: 32px;
	padding: 4px 28px 4px 10px;
	border: 1px solid #7c3aed;
	border-radius: 6px;
	background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none'%3E%3Cpath d='M6 9l6 6 6-6' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") no-repeat right 8px center;
	appearance: none;
	-webkit-appearance: none;
	font-size: 13px;
	font-weight: 600;
	color: #111827;
	cursor: pointer;
}

.table-page-size:focus {
	outline: 2px solid rgba(124, 58, 237, 0.25);
	outline-offset: 1px;
}

.table-pagination-nav {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 28px;
	height: 28px;
	padding: 0;
	border: 0;
	background: transparent;
	color: #9ca3af;
	text-decoration: none;
	border-radius: 4px;
	cursor: pointer;
}

.table-pagination-nav svg {
	width: 16px;
	height: 16px;
}

.table-pagination-nav:hover:not(.is-disabled) {
	color: #4b5563;
	background: #f3f4f6;
}

.table-pagination-nav.is-disabled {
	opacity: 0.45;
	pointer-events: none;
	cursor: default;
}

@media (max-width: 576px) {
	.table-pagination-bar {
		flex-direction: column;
		align-items: stretch;
	}

	.table-pagination-left,
	.table-pagination-right {
		justify-content: space-between;
	}
}
