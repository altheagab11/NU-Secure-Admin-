<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Enrollee Pass Not Found</title>
	<style>
		:root {
			--bg: #eef1f6;
			--blue: #1e3a8a;
			--muted: #64748b;
		}
		* { box-sizing: border-box; }
		body {
			margin: 0;
			min-height: 100vh;
			display: grid;
			place-items: center;
			padding: 24px;
			background: var(--bg);
			font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
			color: #0f172a;
		}
		.card {
			width: min(440px, 100%);
			background: #fff;
			border: 1px solid #e2e8f0;
			border-radius: 18px;
			box-shadow: 0 8px 28px rgba(15, 23, 42, 0.06);
			padding: 28px 24px;
			text-align: center;
		}
		.mark {
			width: 52px;
			height: 52px;
			margin: 0 auto 14px;
			border-radius: 14px;
			background: var(--blue);
			color: #fff;
			display: grid;
			place-items: center;
			font-weight: 800;
		}
		h1 {
			margin: 0 0 8px;
			font-size: 1.25rem;
			color: var(--blue);
		}
		p {
			margin: 0;
			color: var(--muted);
			line-height: 1.5;
			font-size: 0.95rem;
		}
	</style>
</head>
<body>
	<div class="card">
		<div class="mark" aria-hidden="true">NU</div>
		<h1>Pass not found</h1>
		<p>{{ $message ?? 'No enrollee progress page is available for this QR code.' }}</p>
	</div>
</body>
</html>
