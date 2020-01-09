<?php

// ROTAS API -------------------------------------------------------------

// Route::get('categories', 'Api\CategoryController@index');
// Route::post('categories', 'Api\CategoryController@store');
// Route::put('categories/{id}', 'Api\CategoryController@update');
// Route::delete('categories/{id}', 'Api\CategoryController@destroy');


// Rota API Simplificada (index, store, update, destroy). ----------------

Route::apiResource('categories', 'Api\CategoryController');

Route::apiResource('products', 'Api\ProductController');
