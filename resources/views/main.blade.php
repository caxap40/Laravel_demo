@extends('layout')
@section('main')

    @if($error_array)
        <div class='alert alert-danger'>
            <ul class='mb-0'>
                @foreach($error_array as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    @php $host = request()->host(); @endphp

    @if($person_id)
        @include('main_reserve')
        @if($host =='resurs-bass.ru')
            <div class='alert alert-danger mt-2'>
                <ul class='mb-0'><li>Internal server error!</li></ul>
            </div>
        @else
            @includeWhen(in_array($level, [0,1]), 'main_manager')
            @includeWhen($level == 0, 'main_admin')
        @endif
    @endif

@endsection
