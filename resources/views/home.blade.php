@extends('layouts.app')

@section('content')
<div class="container justify-content-center">
    <div class="row center-block text-center">
    <div class="col-md-3 bg-light">
     @include('layouts.menu')
    </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
