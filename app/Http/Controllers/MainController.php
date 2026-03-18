<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log, Session, Validator};

class MainController extends Controller
{
    public function index(Request $request)
    {
        $person_id = session('person_id');
        $level = Person::where('person_id', $person_id)->value('level');

        $error_array = null;
        $errors = session('errors');
        if ($errors) {
            foreach ($errors->keys() as $k)
                if (!in_array($k, ['search', 'f', 'i', 'secret', 'fail_add', 'fail_del'])) {
                    $error_array[] = $errors->first($k);
                    $errors->forget($k);
                }
        }

        $visit_date = $request->input('visit_date');
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $visit_date))  $visit_date = null;
        $formatter = new \IntlDateFormatter(
            'ru_RU',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE
        );
        $formatter->setPattern('d MMMM yyyy');

        $date = new \DateTime($visit_date);
        $date->modify('monday this week');
        $prev_date = clone $date; $prev_date->modify('-1 week');
        $next_date = clone $date; $next_date->modify('+1 week');
        return view('main', [
            'person_id' => $person_id,
            'level' => $level,
            'error_array' => $error_array,
            'reserv_data' => $this->generateReservationData(),
            'visit_data' => $this->generateVisitData($date),
            'dates' => [$prev_date, $date, $next_date],
            'formatter_week' => $formatter,
        ]);
    }

    public function search(Request $request)
    {
        $str = $request->input('search');
        Log::channel('customLog')->info('Поиск сотрудников по строке.', array_merge(['ip' => $request->ip()], ['str' => $str]));

        $level = Person::find(session('person_id'))->level;
        $persons = Person::where('f', 'LIKE', "%$str%")->where('level', '>=', $level)->orderByRaw('f, i, o')->get();
        if ($persons->count() > 5) {
            return back()->withErrors(['search' => 'Показаны первые 5 сотрудников (найдено ' . $persons->count() . ').'])
                ->withInput(['persons' => $persons->slice(0, 5)->toArray()]);
        }
        elseif ($persons->count() == 0) {
            return back()->withErrors(['search' => 'Никого не нашли. Измените запрос.'])->withInput();
        }
        else return back()->withInput(['persons' => $persons->toArray()]);
    }

    public function store(Request $request)
    {
        $all = $request->all();

        Log::channel('customLog')->info('Обновление/добавление сотрудника...', array_merge(['ip' => $request->ip()], $all));
        unset($all['_token']);

        $validator = Validator::make($all,
            ['f' => 'required', 'i' => 'required', 'secret' => 'required|min:8|max:8'], [],
            ['f' => 'Фамилия', 'i' => 'Имя', 'secret' => 'Код доступа'],
        );
        if ($validator->fails()) {
            Log::channel('customLog')->info('Ошибка валидации при обновлении/добавлении сотрудника.', array_merge(['ip' => $request->ip()], $all));
            return back()->withErrors($validator)->withInput(['persons' => [$all]]);
        }

        if ($all['person_id']) {  // сохранить данные сотрудника
            if (Person::find($all['person_id'])->update($all)) {
                Log::channel('customLog')->info('Данные сотрудника сохранены.', array_merge(['ip' => $request->ip()], $all));
                return back()->with('success', 'Данные сотрудника сохранены.')->withInput(['persons' => [$all]]);
            }
        }
        else {             // добавить сотрудника
            if ($request->host() == 'lavademo.ru') {
                $persons = Person::count();
                if ($persons > 20) {
                    return back()->withErrors('Максимально 20 сотрудников');
                }
            }

            try { $p = Person::create($all); }
            catch (UniqueConstraintViolationException $e) {
                Log::channel('customLog')->info('Ошибка сохранения данных сотрудника.', $e->errorInfo);
                return back()->withErrors(['fail_add' => 'Код доступа должен быть уникальным.'])->withInput(['persons' => [$all]]);
            }
            catch (\Throwable $th) {
                Log::channel('customLog')->info('Ошибка сохранения данных сотрудника.', $th->errorInfo);
                return back()->withErrors(['fail_add' => $th->getMessage()])->withInput(['persons' => [$all]]);
            }
            Log::channel('customLog')->info('Сотрудник добавлен.', array_merge(['ip' => $request->ip()], $all));
            return back()->with('success', 'Сотрудник добавлен.')->withInput(['persons' => [$p->toArray()]]);
        }
        Log::channel('customLog')->info('Ошибка сохранения/добавления данных сотрудника.', array_merge(['ip' => $request->ip()], $all));
        return back()->withErrors(['fail_add' => 'Ошибка сохранения/добавления данных сотрудника.'])->withInput(['persons' => [$all]]);
    }

    public function login(Request $request)
    {
        Log::channel('customLog')->info('Попытка авторизации сотрудника.', array_merge(['ip' => $request->ip()], ['code' => $request->input('code')]));
        $valid = $request->validate([
            'code' => 'required',
//            'code' => 'required|min:8|max:8',
            ]);

        $person_id = Person::where('secret', $valid['code'])->value('person_id');
        if (!$person_id) {
            Log::channel('customLog')->info('Ошибка авторизации сотрудника.', array_merge(['ip' => $request->ip()], ['code' => $valid['code']]));
            return redirect()->route('root')
                ->withErrors(['person' => 'Сотрудник с таким кодом доступа не найден. Обратитесь к организатору.'])->withInput();
        }
        else Log::channel('customLog')->info('Сотрудник авторизован.', array_merge(['ip' => $request->ip()], ['person_id' => $person_id]));

        Session::put('person_id', $person_id);
        return redirect()->route('root');
    }

    public function logout(Request $request)
    {
        Log::channel('customLog')->info('Сотрудник вышел.', array_merge(['ip' => $request->ip()], ['person_id' => session('person_id')]));
        Session::forget('person_id');
        return redirect()->route('root');
    }

    private function generateReservationData(): array
    {
        // Генерим данные для формирования дат резервирования посещений
        $date_mon = new \DateTime('monday this week');
        $date_sun = new \DateTime('this thursday')->add(new \DateInterval('P3D'));
        $formatter = new \IntlDateFormatter(
            'ru_RU',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE
        );
        $formatter->setPattern('d MMMM (eeee)');

        $records = DB::select('call get_reservation(?,?)',[$date_mon, $date_sun]);
        $data = [];
        foreach($records as $r)
        {
            $data[$r->curr_date]['date_str'] = str_replace(' (', '<br>(', $formatter->format(new \DateTime($r->curr_date)));
            $data[$r->curr_date][$r->schedule_id]['schedule_name'] = $r->schedule_name;
            if ($r->person_id)
                $data[$r->curr_date][$r->schedule_id][$r->person_id] = [
                    'f' => $r->f, 'i' => $r->i, 'o' => $r->o, 'org' => ['org_id' => $r->org_id, 'org_name_short' => $r->name_short, 'org_name' => $r->name_full]
                ];
        }
        return $data;
    }

    private function generateVisitData(\DateTime $date): array
    {
        // Генерим данные для формирования дат резервирования посещений
        $date_mon = clone $date;
        $date_sun = clone $date;
        $date_mon->modify('monday this week');
        $date_sun->modify('sunday this week');
        $formatter = new \IntlDateFormatter(
            'ru_RU',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE
        );
        $formatter->setPattern('d MMMM (eeee)');

        $records = DB::select('call get_visit(?,?)',[$date_mon, $date_sun]);
        $data = [];
        $person = [];
        foreach($records as $r)
        {
            $data[$r->curr_date]['date_str'] = str_replace(' (', '<br>(', $formatter->format(new \DateTime($r->curr_date)));
//            $data[$r->curr_date][$r->schedule_id]['schedule_name'] = $r->schedule_name;
            $data[$r->curr_date][$r->schedule_id]['hour_start'] = $r->hour_start;
            if ($r->person_id) {
                $data[$r->curr_date][$r->schedule_id][$r->person_id] = $r->visit_yes;
                $person[$r->person_id] = [
                    'f' => $r->f, 'i' => $r->i, 'o' => $r->o, 'org' => [
                        'org_id' => $r->org_id, 'org_name_short' => $r->name_short, 'org_name' => $r->name_full
                    ],
                ];
            }
        }
        return ['data' => $data, 'person' => $person];
    }
}
