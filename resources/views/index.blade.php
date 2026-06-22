@extends('layouts.layout')

@section('title', $page->subtitle)
@section('description', $page->description)

@if ($page->image)
    @section('imagePage', $page->imageAttachment->url)
@endif

@section('content')
    @include('sections.main.main')
@endsection
