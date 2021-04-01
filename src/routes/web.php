<?php



Route::group(['prefix'=>'fileuploader','as'=>'fileuploader','namespace'=>'Avglukh\Fileuploader\Http\Controllers'],function (){
   Route::post('/','FileUploaderController@index')->name('index');
});