<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Создание процедуры
        $sql = <<<BODY
CREATE PROCEDURE `get_visit`(
	IN `start_date` DATE,
	IN `end_date` DATE
)
    SQL SECURITY INVOKER
    COMMENT 'Получение данных для фиксации посещений бассейна'
BEGIN
WITH RECURSIVE DateRange AS (
    SELECT start_date AS curr_date
    UNION ALL
    SELECT DATE_ADD(curr_date, INTERVAL 1 DAY)
    FROM DateRange
    WHERE curr_date < end_date
)
SELECT d.curr_date,
       s.schedule_id, s.day_of_week, s.hour_start, s.schedule_name, r.person_id,
       p.f, p.i, p.o, p.org_id,
       o.name_short, o.name_full,
       !ISNULL((SELECT 1 FROM `visit` WHERE visit_date=d.curr_date AND schedule_id=s.schedule_id AND person_id=r.person_id)) visit_yes
FROM DateRange d
  JOIN `schedule` s ON s.day_of_week=WEEKDAY(d.curr_date)
    LEFT JOIN `reservation` r ON r.reserv_date=d.curr_date AND r.schedule_id=s.schedule_id
    LEFT JOIN `person` p ON p.person_id=r.person_id
    LEFT JOIN `organization` o ON o.org_id=p.org_id
WHERE s.enabled OR r.schedule_id=s.schedule_id
ORDER BY curr_date, day_of_week, hour_start, f, i, o;
END
BODY;

        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS get_visit');
    }
};
