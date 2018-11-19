<?php

Route::group([
    'middleware' => ['web']
], function () {
    Route::any('/authentication', 'Progforce\User\Controllers\RoutesControllers\Account@authenticate');
});