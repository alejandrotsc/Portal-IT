@extends('layouts.app')


@section('title', 'Portal TI')


@section('content')

    @include('partials.chatbot')

    @include('partials.servicios')

    @include('partials.informacion')

    @include('partials.support-widget')

@endsection