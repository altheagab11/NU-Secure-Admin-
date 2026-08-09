.admin-topbar {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 16px;
	background: #ffffff;
	border-radius: 20px;
	padding: 16px 20px;
	box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
	margin-bottom: 20px;
	border: 1px solid rgba(57, 69, 154, 0.08);
}

.admin-topbar-left {
	display: flex;
	align-items: center;
	gap: 12px;
	min-width: 0;
}

.admin-topbar-copy {
	min-width: 0;
}

.admin-topbar-title {
	margin: 0;
	font-size: 1.45rem;
	font-weight: 800;
	color: #39459a;
	line-height: 1.2;
}

.admin-topbar-date {
	margin: 4px 0 0;
	color: #6b7280;
	font-size: 0.92rem;
}

.admin-topbar-right {
	display: flex;
	align-items: center;
	gap: 14px;
	flex-wrap: wrap;
	justify-content: flex-end;
	flex-shrink: 0;
}

.admin-icon-btn {
	width: 42px;
	height: 42px;
	border-radius: 14px;
	border: 1px solid #e4ebf7;
	background: #f8fbff;
	color: #39459a;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	position: relative;
	text-decoration: none;
	flex-shrink: 0;
}

.admin-icon-btn:hover {
	background: #eef4ff;
	color: #39459a;
}

.admin-icon-btn__dot {
	position: absolute;
	top: 8px;
	right: 8px;
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: #ef4444;
}

.admin-avatar-chip {
	width: 42px;
	height: 42px;
	border-radius: 14px;
	background: linear-gradient(135deg, #39459a, #4b5cd1);
	color: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 700;
	font-size: 0.85rem;
	flex-shrink: 0;
}

.admin-logout-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	border: 1.5px solid #39459a;
	color: #39459a;
	background: #fff;
	border-radius: 999px;
	font-weight: 600;
	font-size: 0.9rem;
	padding: 0.45rem 1.1rem;
	line-height: 1.2;
	cursor: pointer;
	transition: background 0.15s ease, color 0.15s ease;
}

.admin-logout-btn:hover {
	background: #eef4ff;
	color: #39459a;
}

@media (max-width: 576px) {
	.admin-topbar {
		padding: 12px 14px;
		border-radius: 16px;
		margin-bottom: 16px;
	}

	.admin-topbar-title {
		font-size: 1.15rem;
	}

	.admin-topbar-date {
		font-size: 0.8rem;
	}

	.admin-avatar-chip {
		display: none;
	}
}
