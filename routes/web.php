<?php

use Illuminate\Support\Facades\Route;

Route::get('/index', 'InboxController@index')->name('inbox.index');
Route::post('/index', 'InboxController@store')->name('inbox.store');
Route::post('/index/{thread}/reply', 'InboxController@reply')->name('inbox.reply');
Route::get('/{thread}', 'InboxController@show')->name('inbox.show');
Route::get('/{thread}/destroy', 'InboxController@destroy')->name('inbox.destroy');