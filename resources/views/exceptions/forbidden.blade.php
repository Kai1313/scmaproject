@extends('layouts.exception')

@section('header')
<section class="content-header">
    <h1>
        Forbidden
        <small>You don't have access to this URL</small>
    </h1>
    <a href="{{ route('welcome') }}">Back to Welcome Page</a>
</section>
@endsection

@section('main-section')
<section class="content container-fluid">

</section>
@endsection