<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportExportController;

Route::view('/', 'home')->name('home');
Route::view('/scan', 'dashboard')->name('scan');
Route::view('/scan/history', 'history')->name('scan.history');
Route::view('/scan/report', 'report')->name('scan.report');
Route::post('/scan/report/docx', [ReportExportController::class, 'docx'])->name('scan.report.docx');
Route::view('/docs', 'docs')->name('docs');
