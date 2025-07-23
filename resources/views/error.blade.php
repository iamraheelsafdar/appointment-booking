@extends('backend.layouts.app')
@section('title', 'Error')
@section('backend')
    <div class="row">
        <div class="col-12">
            <div class="bg-white p-5 rounded shadow ">
                <h1 class="display-4 text-danger mb-3">Oops! Something went wrong.</h1>
                <p class="lead text-muted mb-4">An unexpected error has occurred. Please try again later.</p>

                @if(app()->environment('local') && isset($error))
                    <div class="alert alert-warning text-start overflow-auto" style="max-height: 300px;">
                        <h6><strong>Error Message:</strong></h6>
                        <pre class="mb-2 text-danger">{{ $error->getMessage() }}</pre>
                        <hr>
                        <h6><strong>Location:</strong></h6>
                        <pre class="mb-0 text-muted">{{ $error->getFile() }} on line number: {{ $error->getLine() }}</pre>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
