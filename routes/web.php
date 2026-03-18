<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{MainController, ApiController};
use Laravel\Fortify\Features;

//Route::redirect('/', '/test');
//Route::match(['get','post'], '/', [MainController::class,'index'])->name('root');
//Route::get('/{visit_date}', [MainController::class,'index'])->name('root')->where('visit_date', '[0-9]{4}-[0-9]{2}-[0-9]{2}');;
Route::get('/', [MainController::class,'index'])->name('root');
Route::post('/login', [MainController::class,'login']);
Route::post('/logout', [MainController::class,'logout']);
Route::post('/search', [MainController::class,'search']);
Route::post('/store', [MainController::class,'store']);
Route::post('/back/toggle_reservation', [ApiController::class,'toggleReservation']);
Route::post('/back/delete_person', [ApiController::class,'deletePerson']);
Route::post('/back/toggle_visit', [ApiController::class,'toggleVisit']);

/*Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});*/

require __DIR__.'/settings.php';
