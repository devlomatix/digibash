
<?php

Route::get('/','Client\ClientController@home');


//Route::get('/', function () { 
	//app('log')->debug(app_setting('app_name'));
	//return setting('app.name');
	//return $this->digizigs();
	//return menu('Main Menu');
	//return url()->current();
	//return route('mail.index');

	// //Digizigs::test();
	// //digizigs()->test();
	// //$value = Cache::forever('item', 'wola');
	// return Cache::get('item');;
	// // dd(config('settings.app_name'));
	// // return '<h1>Home Page</h1>';
	// // //$name = AppConfig::get_value('app_description');//get_value('app_name');//get_value('app_name');
	// // //return $name;
	// // //AppConfig::set_setting('app_name','digizigs');

	// // $setting = Settings::get('app_description');
	// // dd($setting);
	// // config(['TEST' => 'NEW_VALUE']);
	// // return config('TEST');
//})->name('app.home');



Route::post('/subscribe','Admin\SubscriptionController@store')->name('app.web.subscribe');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


//Admin Routes
Route::group(['prefix' => 'appadmin','middleware'=>['auth']],function(){

    //Admin
	Route::get('/', 'Admin\DashboardController@index')->name('app.admin.home');

	//Posts
	Route::resource('/post','Admin\PostController');

	//Pages
	Route::resource('/page','Admin\PageController');

	//Categoty
	Route::resource('/category','Admin\CategoryController');

	//Menu
	Route::resource('/menu','Admin\MenuController');
	Route::get('/menu/{menu}/builder','Admin\MenuController@builder')->name('menu.builder');
	Route::post('/menu/{menu}/builder/order','Admin\MenuController@order_item')->name('menu.builder.order.item');
	Route::get('/menu/{menu}/builder/create','Admin\MenuController@addMenuItem_create')->name('menu.item.create');
	Route::post('/menu/{menu}/builder/create','Admin\MenuController@addMenuItem')->name('menu.item.add');
	Route::get('/menu/{menu}/builder/{item}/edit','Admin\MenuController@editMenuItem')->name('menu.item.edit');
	Route::put('/menu/{menu}/builder/{item}/edit','Admin\MenuController@updateMenuItem')->name('menu.item.update');
	Route::delete('/menu/{menu}/builder/{item}/delete','Admin\MenuController@deleteMenuItem')->name('menu.item.delete');


	//Media
	Route::get('/media','Admin\MediaController@index')->name('media.index');

	//Themes
	Route::resource('/theme','Admin\ThemeController');


	//Calender
	Route::get('/calendar', 'Admin\CalendarController@index')->name('app.admin.calendar');


    //Mail
    Route::resource('/mail','Admin\MailController');

	 //Settings
	 Route::resource('/setting','Admin\SettingController');
	 
	 Route::resource('/test','Admin\TestController');

    //Logs
    Route::get('/log','Admin\LogController@index')->name('app.admin.log');    

    
});
