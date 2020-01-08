<?php

// ROTAS API

Route::get('categories', 'Api\CategoryController@index');
Route::post('categories', 'Api\CategoryController@store');
