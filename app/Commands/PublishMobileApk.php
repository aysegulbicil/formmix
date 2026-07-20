<?php

declare(strict_types=1);

namespace App\Commands;

use App\Models\MobileAppReleaseModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

final class PublishMobileApk extends BaseCommand
{
    protected $group='FORMMIX';
    protected $name='formmix:publish-apk';
    protected $description='Imzali Android APK dosyasini /mobil sayfasinda yayinlar.';
    protected $usage='formmix:publish-apk <apk-path> <version-name> <version-code> [minimum-version-code] [release-notes]';
    public function run(array $params):int
    {
        [$source,$versionName,$versionCode]=[$params[0]??'',trim((string)($params[1]??'')),(int)($params[2]??0)];$minimum=max(1,(int)($params[3]??1));$notes=trim(implode(' ',array_slice($params,4)));
        if(!is_file($source)||strtolower(pathinfo($source,PATHINFO_EXTENSION))!=='apk'||$versionName===''||$versionCode<1){CLI::error('APK yolu, surum adi ve pozitif versionCode zorunludur.');CLI::write($this->usage);return EXIT_ERROR;}
        $safeVersion=preg_replace('/[^0-9A-Za-z._-]+/','-',$versionName)?:'release';$directory=FCPATH.'downloads';if(!is_dir($directory)&&!mkdir($directory,0755,true)&&!is_dir($directory)){CLI::error('Download klasoru olusturulamadi.');return EXIT_ERROR;}$fileName='formmix-'.$safeVersion.'.apk';$destination=$directory.DIRECTORY_SEPARATOR.$fileName;if(!copy($source,$destination)){CLI::error('APK public/downloads klasorune kopyalanamadi.');return EXIT_ERROR;}$sha=hash_file('sha256',$destination);if($sha===false){CLI::error('SHA-256 hesaplanamadi.');return EXIT_ERROR;}$data=['platform'=>'android','version_name'=>$versionName,'version_code'=>$versionCode,'minimum_version_code'=>$minimum,'download_url'=>base_url('downloads/'.$fileName),'sha256'=>$sha,'release_notes'=>$notes?:null,'is_active'=>1,'published_at'=>date('Y-m-d H:i:s')];$model=new MobileAppReleaseModel();$existing=$model->where('platform','android')->where('version_code',$versionCode)->first();$ok=$existing?$model->update($existing['id'],$data):$model->insert($data);if(!$ok){CLI::error(implode(' ',$model->errors()));return EXIT_ERROR;}CLI::write('APK yayinlandi: '.$data['download_url'],'green');CLI::write('SHA-256: '.$sha);return EXIT_SUCCESS;
    }
}
