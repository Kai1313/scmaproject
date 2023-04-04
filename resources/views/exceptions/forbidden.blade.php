@extends('layouts.main')
@section('header')
    <section class="content-header">
        <h1>
            Forbidden
            <small>You don't have access to this URL</small>
        </h1>
        <a href="{{ env('OLD_URL_ROOT') }}" class="btn btn-default">Back to Welcome Page</a>
    </section>
@endsection
