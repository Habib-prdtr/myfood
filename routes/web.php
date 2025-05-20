<?php

use Illuminate\Support\Facades\Route;

use Livewire\Livewire;
use App\Livewire\Pages\HomePage;

// Livewire::route('/', HomePage::class);

Route::get('/', function () {
    return view('welcome');
});

