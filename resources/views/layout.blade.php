<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="e=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
{{--    <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">--}}
    <title>Запись в бассейн "Запсибовец"</title>
    <style>
        body {
            font-family: Roboto, "Helvetica Neue", Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr;
        }
        main {
            background-image: url({{ asset('images/swimmer-butterfly-stroke.webp') }}), url({{ asset('images/swimmer-butterfly-stroke.jpg') }});
            background-repeat: no-repeat;
            background-position: center top;
            background-size: cover;
        }
        .pad {
            background: rgba(255 255 255 / 0.8);
            min-height: 100%;
        }
        .row > * { margin: 0 }
    </style>

</head>
<body>
    <header class="container-fluid p-2 bg-dark text-white">
        <div class="container-lg d-flex flex-wrap align-items-center justify-content-between">
            <h2 class="me-3">Запись в бассейн &laquo;Запсибовец&raquo;</h2>

            @if(is_null($person_id))
                <form method="post" action="/login" class="d-flex gap-1">
                    <input type="text" name="code" id="code" class="form-control form-control-dark"
                           autofocus autocomplete="off" value="{{ old('code') }}"
                           placeholder="Индивидуальный код..." title="Введите свой код для входа" aria-label="Login">
                    <button type="submit" class="btn btn-success">Войти</button>
                    @csrf
                </form>
            @else
                @php
                $person = \App\Models\Person::select(['f','i','o'])
                    ->addSelect(['org' => \App\Models\Organization::select('name_full')
                        ->whereColumn('org_id', 'person.org_id')
                        ->limit(1)
                    ])->where('person_id', $person_id)->first();//->ddRawSql();
                @endphp
                <div class="d-flex align-items-center gap-2">
                    <h6 class="text-info mb-0">{{ $person->f }} {{ $person->i }} {{ $person->o }} ({{ $person->org }})</h6>
                    <form method="post" action="/logout">
                        <button type="submit" class="btn btn-info">Выход</button>
                        @csrf
                    </form>
                </div>
            @endif
        </div>
    </header>
    <main class="container-fluid p-0">
        <div class="pad">
            @yield('main')
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script type="text/javascript">
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl) })
    </script>
</body>
</html>
