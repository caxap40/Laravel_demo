<style>
    .custom-wide-tooltip.tooltip > .tooltip-inner {
        max-width: 400px; /* Нужная ширина */
        text-align: left;
        white-space: normal;
    }
    .form-check-input {
        width: 1.3rem;
        height: 1.3rem;
    }
    .form-check-input:not(:checked) { border: 2px solid; }
    .th_sched {
        padding: 2px 5px;
        font-weight: 500;
    }
</style>
<script type="text/javascript">
    /*window.addEventListener('load', () => {
        const check_boxes = document.querySelectorAll('.cb_reserv')

        check_boxes.forEach(cb => {
            cb.addEventListener('click', _e => {
                console.log(_e.target.dataset);

                const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
                fetch('/back/toggle_reservation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(cb.dataset)})
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
                _e.preventDefault();
            });
        });
    })*/

    function toggleReservation(date, sched)
    {
        console.log('date:', date, 'schedule_id:', sched);
        const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
        fetch('/back/toggle_reservation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({'date':date, 'schedule_id':sched})
        })
            .then((response) => {
                console.log(response);
                if (response.ok) return response.json();
                else alert(response.statusText);
            })
            .then(data => {
                console.log('data: ', data);
                if (data?.success) location.reload();
                else {
                    if (data?.message) alert(data.message);
                    else alert('Неизвестная ошибка сервера!');
                }
            })
            .catch(err => {
                console.log(err);
                alert(err);
            });
    }
</script>

<section class="d-flex flex-wrap justify-content-center pt-2">
    @foreach($reserv_data as $d=>$sched)
    <table class="m-1 table-bordered border-secondary-subtle bg-body-secondary">
        <thead>
            <tr>
                <th colspan="{{ count($sched) }}" class="text-center text-nowrap">
                    {!! $sched['date_str'] !!}
                </th>
            </tr>
            <tr>
                @foreach($sched as $s)
                    @if(is_array($s))
                        <th class="th_sched text-center text-nowrap">
                            {{ $s['schedule_name'] }}
                        </th>
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach($sched as $s_id=>$s)
                    @if(is_array($s))
                    <td class="text-center">
                        <input class="cb_reserv form-check-input mb-1" type="checkbox" title="Записаться/Отменить запись"
                            @checked(array_key_exists($person_id, $s))
                            @disabled(new \DateTime($d) < new \DateTime()->setTime(0,0))
                            onclick="toggleReservation('{{$d}}', {{$s_id}}); return false;">
                    </td>
                    @endif
                @endforeach
            </tr>
            <tr>
                @foreach($sched as $s)
                    @if(is_array($s))
                    <td class="text-center text-nowrap text-danger" data-bs-toggle="{{ count($s)>1 ? 'tooltip' : null }}" data-bs-placement="bottom" data-bs-html="true" data-bs-tigger="hover click" data-bs-custom-class="custom-wide-tooltip"
                        title="@foreach($s as $p)
                         {{ is_array($p) ? $p['f'].' '.$p['i'].' '.$p['o'].' ('.$p['org']['org_name_short'].')<br>' : null }}
                        @endforeach">
                        <h3 class="mb-0">{{ count($s)-1 }}</h3>
                    </td>
                    @endif
                @endforeach
            </tr>
        </tbody>
    </table>
    @endforeach
</section>
