@php
	/** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
	$pageSizes = [5, 10, 25, 50, 75, 100];
	$from = $paginator->firstItem() ?? 0;
	$to = $paginator->lastItem() ?? 0;
	$total = $paginator->total();
	$currentPage = $paginator->currentPage();
	$lastPage = max(1, $paginator->lastPage());
	$onFirst = $paginator->onFirstPage();
	$onLast = $currentPage >= $lastPage;
@endphp

<div class="table-pagination-bar" role="navigation" aria-label="{{ $ariaLabel ?? 'Table pagination' }}">
	<div class="table-pagination-left">
		<label class="table-pagination-label" for="{{ $perPageParam }}">Page size:</label>
		<select
			id="{{ $perPageParam }}"
			class="table-page-size"
			data-per-page-param="{{ $perPageParam }}"
			data-page-param="{{ $paginator->getPageName() }}"
			aria-label="Page size"
		>
			@foreach($pageSizes as $size)
				<option value="{{ $size }}" @selected((int) $paginator->perPage() === $size)>{{ $size }}</option>
			@endforeach
		</select>
		<span class="table-pagination-range">{{ $from }} to {{ $to }} of {{ $total }}</span>
	</div>

	<div class="table-pagination-right">
		<a
			href="{{ $onFirst ? '#' : $paginator->url(1) }}"
			class="table-pagination-nav {{ $onFirst ? 'is-disabled' : '' }}"
			aria-label="First page"
			@if($onFirst) aria-disabled="true" tabindex="-1" @endif
		>
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 6L5 12l6 6M19 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</a>
		<a
			href="{{ $onFirst ? '#' : $paginator->previousPageUrl() }}"
			class="table-pagination-nav {{ $onFirst ? 'is-disabled' : '' }}"
			aria-label="Previous page"
			@if($onFirst) aria-disabled="true" tabindex="-1" @endif
		>
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</a>

		<span class="table-pagination-page">
			Page <strong>{{ $currentPage }}</strong> of {{ $lastPage }}
		</span>

		<a
			href="{{ $onLast ? '#' : $paginator->nextPageUrl() }}"
			class="table-pagination-nav {{ $onLast ? 'is-disabled' : '' }}"
			aria-label="Next page"
			@if($onLast) aria-disabled="true" tabindex="-1" @endif
		>
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</a>
		<a
			href="{{ $onLast ? '#' : $paginator->url($lastPage) }}"
			class="table-pagination-nav {{ $onLast ? 'is-disabled' : '' }}"
			aria-label="Last page"
			@if($onLast) aria-disabled="true" tabindex="-1" @endif
		>
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6l6 6-6 6M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</a>
	</div>
</div>
