@if ($paginator->hasPages() || request('pageSize') >= $paginator->total())
    <nav class="d-flex justify-items-center justify-content-between">
        <div class="d-flex justify-content-between flex-fill d-sm-none">
            <ul class="pagination">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">@lang('pagination.previous')</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">@lang('pagination.previous')</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">@lang('pagination.next')</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">@lang('pagination.next')</span>
                    </li>
                @endif
            </ul>
        </div>

        <div class="d-none flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between">
            <div class="d-inline-flex">
                <p class="small text-muted">
                    {!! __('pagination.showing') !!}
                    <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
                    {!! __('pagination.to') !!}
                    <span class="fw-semibold">{{ $paginator->lastItem() }}</span>
                    {!! __('pagination.of') !!}
                    <span class="fw-semibold">{{ $paginator->total() }}</span>
                    {!! __('pagination.results') !!}
                </p>
                <div class="mx-2">
                    <select id="pageSize" name="pageSize" class="form-select-sm" onchange="changePageSize()">
                        <option value="10" {{ request('pageSize') == 5 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ request('pageSize') == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('pageSize') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('pageSize') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('pageSize') == 100 ? 'selected' : '' }}>100</option>
                        <option value="{{$paginator->total()}}" {{ request('pageSize') == $paginator->total() ? 'selected' : '' }}>Alle</option>
                    </select>
                </div>
            </div>

            <div>
                <ul class="pagination">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link" aria-hidden="true">&lsaquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')" hx-boost="true">&lsaquo;</a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}" hx-boost="true">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')" hx-boost="true">&rsaquo;</a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link" aria-hidden="true">&rsaquo;</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
<script>
    function changePageSize() {
        const pageSize = document.getElementById('pageSize').value;
        const url = new URL(window.location.href);
        url.searchParams.set('pageSize', pageSize);
        window.location.href = url.toString();
    }
</script>
