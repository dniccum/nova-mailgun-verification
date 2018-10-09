<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. You're free to add
| as many additional routes to this file as your tool may require.
|
*/

const NO_COLUMN_ERROR = 'The column that you have defined in your Nova resource does not exist.';

Route::group(['middleware' => 'web'], function() {

    Route::get('/domain', function (Request $request) {
        $id = $request->get('id');
        $model = $request->get('model');

        if ($request->has('attribute')) {
            $service = new \Dniccum\MailgunDomainVerification\MailgunService($model, $request->get('attribute'));
        } else {
            $service = new \Dniccum\MailgunDomainVerification\MailgunService($model);
        }

        $domain = $service->getDomainStatus($id);

        if ($domain) {
            return response()
                ->json([
                    'domain' => $domain
                ], 200);
        }

        return response()
            ->json([
                'message' => NO_COLUMN_ERROR
            ], 500);
    });

    Route::put('/domain', function (Request $request) {
        $id = $request->get('id');
        $model = $request->get('model');

        if ($request->has('attribute')) {
            $service = new \Dniccum\MailgunDomainVerification\MailgunService($model, $request->get('attribute'));
        } else {
            $service = new \Dniccum\MailgunDomainVerification\MailgunService($model);
        }

        $domain = $service->verifyDomainStatus($id);

        if ($domain) {
            return response()
                ->json([
                    'domain' => $domain
                ], 200);
        }

        return response()
            ->json([
                'message' => NO_COLUMN_ERROR
            ], 500);
    });

    Route::post('/domain', function (Request $request) {
        $id = $request->get('id');
        $model = $request->get('model');

        if ($request->has('attribute')) {
            $service = new \Dniccum\MailgunDomainVerification\MailgunService($model, $request->get('attribute'));
        } else {
            $service = new \Dniccum\MailgunDomainVerification\MailgunService($model);
        }

        $domain = $service->addDomain($id);

        if ($domain) {
            return response()
                ->json([
                    'domain' => $domain
                ], 200);
        }

        return response()
            ->json([
                'message' => NO_COLUMN_ERROR
            ], 500);
    });

});
