<nav class="d-flex justify-items-center justify-content-between">
  <div class="d-flex justify-content-between flex-fill d-sm-none nextPreviousButton">
    <ul class="pagination" >
      {{-- Previous Page Link --}}
      @if ($paginator->onFirstPage())
        <li class="page-item disabled" aria-disabled="true">
          <span class="page-link">@lang('pagination.previous')</span>
        </li>
      @else
        <li class="page-item">
          <a class="page-link nextButton" href="{{ $paginator->previousPageUrl() }}" rel="prev">@lang('pagination.previous')</a>
        </li>
      @endif

      {{-- Next Page Link --}}
      @if ($paginator->hasMorePages())
        <li class="page-item">
          <a class="page-link prevoiusButton" href="{{ $paginator->nextPageUrl() }}" rel="next">@lang('pagination.next')</a>
        </li>
      @else
        <li class="page-item disabled" aria-disabled="true">
          <span class="page-link">@lang('pagination.next')</span>
        </li>
      @endif
    </ul>
  </div>

  <div class="flex-fill d-flex align-items-center gap-2 mb-2">
    <span class="small text-muted showingResults">
      {!! __('pagination.showing') !!}
      <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
      {!! __('pagination.to') !!}
      <span class="fw-semibold">{{ $paginator->lastItem() }}</span>
      {!! __('pagination.of') !!}
      <span class="fw-semibold">{{ $paginator->total() }}</span>
      {!! __('pagination.results') !!}
    </span>

    <div class="btn-group pageLinkButtons">
      {{-- Previous Page Link --}}
      @if ($paginator->onFirstPage())
        <a class="btn btn-outline-primary disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">&lsaquo;</a>
      @else
        <a class="btn btn-outline-primary" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')" hx-boost="true">&lsaquo;</a>
      @endif

      {{-- Pagination Elements --}}
      @foreach ($elements as $element)
        {{-- "Three Dots" Separator --}}
        @if (is_string($element))
          <span class="btn btn-outline-primary disabled" aria-disabled="true">{{ $element }}</span>
        @endif

        {{-- Array Of Links --}}
        @if (is_array($element))
          @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
              <a class="btn btn-outline-primary active" aria-current="page">{{ $page }}</a>
            @else
              <a class="btn btn-outline-primary" href="{{ $url }}" hx-boost="true">{{ $page }}</a>
            @endif
          @endforeach
        @endif
      @endforeach

      {{-- Next Page Link --}}
      @if ($paginator->hasMorePages())
        <a class="btn btn-outline-primary" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')" hx-boost="true">&rsaquo;</a>
      @else
        <a class="btn btn-outline-primary disabled" aria-disabled="true" aria-hidden="true" aria-label="@lang('pagination.next')">&rsaquo;</a>
      @endif
    </div>

    <span class="small text-muted ms-auto perPageText">
      @lang('pagination.items-per-page')
    </span>
    <div class="btn-group itemSelect">
      @php
          use function Prometa\Sleek\array_merge_recursive_distinct;
          $pageSizeName ??= 'page-size'
      @endphp
      @foreach([10, 20, 50, 100] as $pageSize)
        @php
            $queryParams = [];
            Arr::set($queryParams, $pageSizeName, $pageSize);

            $query = request()->query();
            $query = array_merge_recursive_distinct($query, $queryParams);
            Arr::forget($query, $pageName ?? 'page');
        @endphp
        <a
          @if($pageSize !== $paginator->perPage())
            href="{{ request()->fullUrlWithQuery($query) }}"
          @else
            aria-current="page"
          @endif
          class="btn btn-outline-primary @if($pageSize === $paginator->perPage()) active @endif"
        >
          {{ $pageSize }}
        </a>
      @endforeach
    </div>
  </div>
</nav>
<style>
    @media screen and (max-width: 600px) {
        .pageLinkButtons, .itemSelect, .perPageText {
            display: none !important;
        }

        .showingResults {
            justify-content: flex-end !important;
            align-items: flex-end !important;
            width: 100%;
        }

        .nextPreviousButton {
            justify-content: flex-start !important;
            align-items: flex-start !important;
            width: 100%;
        }

        .flex-fill.d-flex.align-items-center.gap-2.mb-2 {
            flex-direction: column !important;
            align-items: center !important;
            justify-content: space-between !important;
        }
    }
</style>
