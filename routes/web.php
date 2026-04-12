<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard.index')->name('dashboard');
    Route::livewire('/patients', 'pages::patients.index')->name('patients.index');
    Route::livewire('/patients/register', 'pages::patients.register')->name('patients.register');
    Route::livewire('/patients/visit', 'pages::patients.visit')->name('patients.visit');
    Route::livewire('/appointments', 'pages::appointments.index')->name('appointments.index');

    // Rawat Jalan
    Route::livewire('/out-patients', 'pages::out-patients.index')->name('out-patients.index');
});

require __DIR__ . '/settings.php';
