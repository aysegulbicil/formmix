<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\MobileAppReleaseModel;

final class Mobile extends BaseController
{
    public function index(): string
    {
        $release=(new MobileAppReleaseModel())->where('platform','android')->where('is_active',1)->where('published_at <=',date('Y-m-d H:i:s'))->orderBy('version_code','DESC')->first();
        return view('pages/mobile',[
            'title'=>'FORMMIX Mobil | Android Uygulamasi',
            'description'=>'FORMMIX Android uygulamasinin guncel ve imzali APK surumunu indirin.',
            'bodyClass'=>'page-mobile',
            'release'=>$release,
        ]);
    }
}
