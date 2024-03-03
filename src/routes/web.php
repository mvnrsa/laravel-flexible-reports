<?php

Route::group([
				'prefix' => 'admin',
				'as' => 'admin.',
				'namespace' => '\mvnrsa\FlexibleReports',
				'middleware' => ['web','auth'],
			], function ()
{
    Route::delete('reports/destroy', 'FlexibleReportsController@massDestroy')->name('reports.massDestroy');
    Route::resource('reports', 'FlexibleReportsController');
	Route::get('reports/{report}/run','FlexibleReportsController@showForm')->name('reports.form');
	Route::post('reports/{report}/run/{format}','FlexibleReportsController@run')->name('reports.run');
});

