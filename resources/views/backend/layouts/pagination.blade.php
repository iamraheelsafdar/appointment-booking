<div class="d-flex justify-content-center align-items-baseline" style="position: absolute; bottom:100px; left: 0; right: 0">
    {{--    <h5 class="position-absolute bottom-0" style="left:0;">Total Record: {{$dataToPaginate['total']}}</h5>--}}
    @if ($dataToPaginate['current_page'] > 1)
        <a href="{{ $dataToPaginate['path'] . '?page=1' . '&' . http_build_query(request()->except('page')) }}"
           class="btn btn-primary button me-2 ">
            <i class="fa fa-caret-left"></i>
        </a>
    @else
        <button class="btn btn-secondary me-2 button" disabled>
            <i class="fa fa-caret-left"></i>
        </button>
    @endif

    @if ($dataToPaginate['prev_page_url'])
        <a href="{{ $dataToPaginate['prev_page_url'] . '&' . http_build_query(request()->except('page')) }}"
           class="btn btn-primary button me-2">
            <i class="fa fa-backward"></i>
        </a>
    @else
        <button class="btn btn-secondary me-2 button" disabled>
            <i class="bi bi-chevron-left"></i> <i class="fa fa-backward"></i>
        </button>
    @endif
    @if ($dataToPaginate['has_more_pages'])
        <a href="{{ $dataToPaginate['next_page_url'] . '&' . http_build_query(request()->except('page')) }}"
           class="btn btn-primary button me-2">
            <i class="fa fa-forward"></i> <i class="bi bi-chevron-right"></i>
        </a>
    @else
        <button class="btn btn-secondary me-2 button" disabled>
            <i class="fa fa-forward"></i>
        </button>
    @endif

    @if ($dataToPaginate['has_more_pages'])
        <a href="{{ $dataToPaginate['path'] . '?page=' . ceil($dataToPaginate['total'] / $dataToPaginate['per_page']) . '&' . http_build_query(request()->except('page')) }}"
           class="btn btn-primary button">
            <i class="fa fa-caret-right"></i>
        </a>
    @else
        <button class="btn btn-secondary button" disabled>
            <i class="fa fa-caret-right"></i>
        </button>
    @endif

</div>
