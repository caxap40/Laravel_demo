<style>
    /*.visit { overflow-x: auto; }*/
    .visit { width: 100%; }
    @media screen and (max-width: 576px){
        .visit {
            overflow-x: scroll;
            width: 500px;
        }
    }
</style>
<script type="text/javascript">
    function delPerson(person_id, index)
    {
        console.log('person_id', person_id);

        document.getElementById('alert-box')?.remove();
        const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
        fetch('/back/delete_person', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: person_id
            // body: JSON.stringify({'person_id': person_id})
        })
        .then((response) => {
            console.log(response);
            if (response.ok) return response.json();
            else alert(response.statusText);
        })
        .then(data => {
            console.log('data:', data);
            const form = document.getElementById('form-person_'+index);
            form?.remove();
            const section = document.getElementById('manager_person');
            if (data?.successful) {
                section.insertAdjacentHTML('beforeend', "<div id='alert-box' class='alert alert-success mt-1'>Сотрудник удален.</div>")
                // document.getElementById('form-person').querySelectorAll('input').forEach(i => { if (i.name !== '_token') i.value = ''})
            }
            else  section.insertAdjacentHTML('beforeend', "<div id='alert-box' class='alert alert-danger mt-1'>Ошибка удаления сотрудника.</div>")
        })
        .catch(err => {
            console.log(err);
            alert(err);
        });
    }

    function newWeek(date)
    {
        console.log('date', date);
        const url = new URL(window.location.href);
        url.searchParams.set('visit_date', date);
        window.location.href = url.toString();
    }

    function toggleVisit(date, sched, person_id)
    {
        console.log('date:', date, 'schedule_id:', sched, 'person_id:', person_id);
        const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
        fetch('/back/toggle_visit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({'date':date, 'schedule_id':sched, 'person_id':person_id})
        })
            .then((response) => {
                console.log(response);
                // if (!response.ok) alert(response.statusText);
                if (response.ok) return response.json();
                else alert(response.statusText);
            })
            .then(data => {
                console.log('data: ', data);
                if (data?.checked  === null) {
                    if (data?.message) alert(data.message);
                    else alert('Пустой ответ сервера!');
                }
                else location.reload();
            })
            .catch(err => {
                console.log(err);
                alert(err);
            });
    }
</script>
@php
    $blank_person = [[
            'person_id' => null,
            'f' => null,
            'i' => null,
            'o' => null,
            'org_id' => null,
            'secret' => null,
        ]];
    $persons = old('persons');
    if (!is_null($persons)) {
        if ($persons[0]['person_id'] !== null) $persons = array_merge($persons, $blank_person); // это поиск - добавляем пустую форму
    }
    else $persons = $blank_person;  // если это ошибка валидации, то берем последние значения, иначе пустую форму

    $orgs = \App\Models\Organization::select(['org_id', 'name_full'])->get();
@endphp
<section class="container mt-5">
    <form class="row justify-content-center bg-body-secondary g-2 py-2 rounded-pill" method="POST" action="/search">
        <div class="col-6 col-lg-3 px-1">
            <input type="text" class="form-control" id="search" name="search" placeholder="Поиск по фамилии" value="{{ old('search') }}">
        </div>
        <div class="col-auto px-1">
            <button type="submit" class="btn btn-outline-primary">Найти</button>
        </div>
        @csrf
    </form>
    @foreach($persons as $i=>$p)
    <form @class(['row', 'justify-content-center', 'g-2', 'mt-2', 'py-2', 'rounded-3', 'bg-body-secondary'=>!$p['person_id']]) method="POST" action="/store">
        <input type="hidden" name="person_id" value="{{ $p['person_id'] }}">
        <div class="col-12 col-md-4 col-xl-2 px-1"><label for="f_{{ $i }}" class="form-label mb-0">Фамилия</label>
            <input type="text" class="form-control" id="f_{{ $i }}" name="f" value="{{ $p['f'] }}">
        </div>
        <div class="col-12 col-md-4 col-xl-2 px-1"><label for="i_{{ $i }}" class="form-label mb-0">Имя</label>
            <input type="text" class="form-control" id="i_{{ $i }}" name="i" value="{{ $p['i'] }}">
        </div>
        <div class="col-12 col-md-4 col-xl-2 px-1"><label for="o_{{ $i }}" class="form-label mb-0">Отчество</label>
            <input type="text" class="form-control" id="o_{{ $i }}" name="o" value="{{ $p['o'] }}">
        </div>
        <div class="col-12 col-md-6 col-xl-3 px-1"><label for="org_id_{{ $i }}" class="form-label mb-0">Предприятие</label>
            <select class="form-select" id="org_id_{{ $i }}" aria-label="Пример выбора по умолчанию" name="org_id">
                @isset($orgs)
                    <option @selected(is_null($p['org_id'])) value="">{{ '<нет>' }}</option>
                    @foreach($orgs as $o)
                        <option @selected($p['org_id'] == $o->org_id) value="{{ $o->org_id }}">{{ $o->name_full }}</option>
                    @endforeach
                @endisset
            </select>
        </div>
        <div class="col-1 px-1" style="min-width:7rem"><label for="secret_{{ $i }}" class="form-label mb-0">Код доступа</label>
            <input type="text" class="person form-control" id="secret_{{ $i }}" name="secret" value="{{ $p['secret'] }}" autocomplete="off">
        </div>
        <div class="col-auto align-content-end px-1">
            <button type="submit" class="btn btn-primary">{{ $p['person_id'] ? 'Сохранить' : 'Добавить' }}</button>
            @if($p['person_id'])
                <button type="button" class="btn btn-warning" onclick="delPerson({{ $p['person_id'] }}, {{ $i }})">Удалить</button>
            @endif
        </div>
        @csrf
    </form>
    @endforeach
    @empty($orgs)
        <div class="alert alert-danger mt-1">Ошибка получения списка предприятий</div>
    @endempty
    @if($errors->any())
        <div class='alert alert-danger mt-1'>
            <ul class='mb-0'>
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif
    @if(session('success'))
        <div id="alert-box" class="alert alert-success mt-1">{{ session('success') }}</div>
    @endif
</section>

<section class="container mt-5 mb-1">
    <div class="row justify-content-between px-3">
        <h4 class="col text-primary fst-italic align-self-end">Посещаемость</h4>
        <div class="col-auto">
            <button type="button" class="btn btn-secondary mx-1" title="Предыдущая неделя"
                    style="min-width:170px" onclick="newWeek('{{ $dates[0]->format('Y-m-d') }}')">
                < &nbsp{{ $formatter_week->format($dates[0]) }}
            </button>
{{--            <span class="mx-4">{!! $dates[1] !!}</span>--}}
            <button type="button" class="btn btn-secondary mx-1" title="Следующая неделя"
                    style="min-width:170px" onclick="newWeek('{{ $dates[2]->format('Y-m-d') }}')">
                {{ $formatter_week->format($dates[2]) }}&nbsp >
            </button>
        </div>
    </div>
</section>
<section class="visit container-md p-0">
    <table class="w-100 table-bordered border-secondary-subtle bg-body-secondary">
        <thead>
        <tr>
            <th rowspan="2" class="text-center text-nowrap">Плавцы</th>
            @foreach($visit_data['data'] as $date)
            <th colspan="{{ count($date)-1 }}" class="text-center text-nowrap">
                {!! $date['date_str'] !!}
            </th>
            @endforeach
        </tr>
        <tr>
            @foreach($visit_data['data'] as $date)
            @foreach($date as $s)
                @if(is_array($s))
                    <th class="th_sched text-center text-nowrap" title="Время начала">
                        {{ $s['hour_start'] }}:00
                    </th>
                @endif
            @endforeach
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($visit_data['person'] as $p_id=>$p)
            <tr>
                <th class="text-nowrap px-2">
                    {{ $p['f'].' '.$p['i'].' '.$p['o'].' ('.$p['org']['org_name_short'].')' }}
                </th>
                @foreach($visit_data['data'] as $d=>$date)
                @foreach($date as $s_id=>$s)
                    @if(is_array($s))
                        <td @class(['text-center', 'bg-primary-subtle'=>array_key_exists($p_id, $s)])>
                            <input class="cb_reserv form-check-input mb-1" type="checkbox" title="Отметить/Снять посещение"
                                   @checked($s[$p_id] ?? 0)
                                   @disabled(!array_key_exists($p_id, $s))
                                   onclick="toggleVisit('{{$d}}', {{$s_id}}, {{$p_id}}); return false;"
                            >
                        </td>
                    @endif
                @endforeach
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</section>
