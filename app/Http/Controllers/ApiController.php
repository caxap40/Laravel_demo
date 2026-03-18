<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Reservation;
use App\Models\Visit;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function toggleReservation(Request $request)
    {
        $person_id = session('person_id', null);
        if ($person_id == null) return redirect('/');

        $date = $request->input('date');
        $schedule_id = $request->input('schedule_id');

        $success = false;
        $msg = null;
        $reservation = Reservation::where(['reserv_date' => $date, 'schedule_id' => $schedule_id, 'person_id' => $person_id])->first();//->ddRawSql();
        if ($reservation) {
            if ($reservation->delete()) $success = true;
        } else {
            if (Reservation::where(['reserv_date' => $date, 'schedule_id' => $schedule_id])->count() >= 15)
                $msg = 'К сожалению, есть ограничение в 15 человек.';
            else {
                $r = new Reservation;
                $r->reserv_date = $date;
                $r->schedule_id = $schedule_id;
                $r->person_id = $person_id;
                if ($r->save()) $success = true;
            }
        }
        return response()->json(['success' => $success, 'message' => $msg]);
    }

    public function toggleVisit(Request $request)
    {
        if (session('person_id') == null) return redirect('/');

        $date = $request->input('date');
        $schedule_id = $request->input('schedule_id');
        $person_id = $request->input('person_id');

        $success = false;
        $msg = null;
        $visit = Visit::where(['visit_date' => $date, 'schedule_id' => $schedule_id, 'person_id' => $person_id])->first();//->ddRawSql();
        if ($visit) {
            if ($visit->delete()) $success = true;
        } else {
            $r = new Visit;
            $r->visit_date = $date;
            $r->schedule_id = $schedule_id;
            $r->person_id = $person_id;
            if ($r->save()) $success = true;
        }
        return response()->json(['success' => $success, 'message' => $msg]);
    }

    public function deletePerson(Request $request)
    {
        $person_id_del = $request->getContent();
        $destroyed = Person::destroy($person_id_del);
        return response()->json(['successful' => $destroyed > 0 ? true : false]);
    }
}
