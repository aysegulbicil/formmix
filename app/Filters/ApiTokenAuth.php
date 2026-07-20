<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Authentication\Authenticators\AccessTokens;

final class ApiTokenAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) return null;
        /** @var AccessTokens $authenticator */
        $authenticator=auth('tokens')->getAuthenticator();
        $result=$authenticator->attempt(['token'=>$request->getHeaderLine(setting('AuthToken.authenticatorHeader')['tokens']??'Authorization')]);
        if(! $result->isOK()||(!empty($arguments)&&$result->extraInfo()->tokenCant($arguments[0])))return $this->error('UNAUTHENTICATED','Erisim anahtari gecersiz.',Response::HTTP_UNAUTHORIZED);
        if(setting('Auth.recordActiveDate'))$authenticator->recordActiveDate();
        $user=$authenticator->getUser();
        if($user!==null&&!$user->isActivated()){$authenticator->logout();return $this->error('ACCOUNT_INACTIVE','Kullanici hesabi etkin degil.',Response::HTTP_FORBIDDEN);}
        return null;
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void {}
    private function error(string $code,string $message,int $status):ResponseInterface{return service('response')->setStatusCode($status)->setJSON(['error'=>['code'=>$code,'message'=>$message,'fields'=>(object)[]]]);}
}
