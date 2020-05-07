<?php

namespace Kompo\Core;

use Kompo\Core\KompoAction;
use Kompo\Exceptions\KomposerMethodNotFoundException;
use Kompo\Exceptions\KomposerNotDirectMethodException;
use Kompo\Exceptions\UnauthorizedException;
use Kompo\Komposers\KomposerManager;
use Kompo\Routing\Dispatcher;
use ReflectionClass;

class AuthorizationGuard
{
    public static function checkBoot($komposer)
    {
        if(!$komposer->authorizeBoot())
            throw new UnauthorizedException( get_class($komposer), 'boot' );

        if(KompoAction::is(['eloquent-submit', 'handle-submit']))
            static::checkPreventSubmit($komposer);

        KomposerManager::created($komposer);
    }

    public static function mainGate($komposer)
    {
    	if(method_exists($komposer, 'authorize') && !$komposer->authorize())
    		throw new UnauthorizedException( get_class($komposer), 'main' );

        return true;
    }

    public static function selfMethodGate($komposer, $method)
    {
        static::checkMethodExists($komposer, $method);

        $komposerType = 'Kompo\\'.Dispatcher::getKomposerType($komposer);

        $baseMethodNames = collect((new ReflectionClass($komposerType))->getMethods())->pluck('name')->all();

        if (in_array($method, $baseMethodNames))
            throw new KomposerNotDirectMethodException($method, get_class($komposer));
    }

    

    /**** PRIVATE / PROTECTED ****/

    protected static function checkMethodExists($komposer, $method)
    {
        if(!method_exists($komposer, $method))
            throw new KomposerMethodNotFoundException($method);
    }

    protected static function checkPreventSubmit($komposer)
    {
        if($komposer->_kompo('options', 'preventSubmit'))
            throw new UnauthorizedException( get_class($komposer), 'submit' );
    }
}