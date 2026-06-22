@extends('layouts.layout')

@section('title', 404)
@section('description', "Такой страницы не существует!")

@section('content')
    <section class="not-found">
        <div class="not-found__container container">

            <h1 class="not-found__title">404</h1>
            <a href="/" class="not-found__link">
                < Вернуться на главную</a>

        </div>
    </section>

@endsection
