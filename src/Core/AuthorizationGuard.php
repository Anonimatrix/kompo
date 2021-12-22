<?php

namespace Kompo\Core;

use Illuminate\Auth\Access\AuthorizationException;
use Kompo\Exceptions\KomponentMethodNotFoundException;
use Kompo\Exceptions\KomponentNotDirectMethodException;
use Kompo\Komponents\KomponentManager;
use Kompo\Routing\Dispatcher;
use ReflectionClass;

class AuthorizationGuard
{
    public static function checkBoot($komponent, $stage)
    {
        if (!$komponent->authorizeBoot()) {
            return static::throwUnauthorizedException($komponent, 'boot');
        }

        if (KompoAction::is(['eloquent-submit', 'handle-submit'])) {
            static::checkPreventSubmit($komponent);
        }

        KomponentManager::created($komponent, $stage);
    }

    public static function mainGate($komponent, $stage = null)
    {
        if (method_exists($komponent, 'authorize') && !$komponent->authorize()) {
            return static::throwUnauthorizedException($komponent, $stage);
        }

        return true;
    }

    public static function selfMethodGate($komponent, $method)
    {
        static::checkMethodExists($komponent, $method);

        $komponentType = 'Kompo\\'.Dispatcher::getKomponentType($komponent);

        $baseMethodNames = collect((new ReflectionClass($komponentType))->getMethods())->pluck('name')->all();

        if (in_array($method, $baseMethodNames)) {
            throw new KomponentNotDirectMethodException($method, get_class($komponent));
        }
    }

    /**** PRIVATE / PROTECTED ****/

    protected static function throwUnauthorizedException($komponent, $stage = null)
    {
        $message = method_exists($komponent, 'failedAuthorization') ?
            $komponent->failedAuthorization() :
            $komponent->getFailedAuthorizationMessage();

        throw new AuthorizationException(
            $message ?:
            ('The '.($stage ?: 'called').' functionality is unauthorized on this class.')
        );
    }

    protected static function checkMethodExists($komponent, $method)
    {
        if (!method_exists($komponent, $method)) {
            throw new KomponentMethodNotFoundException($method, $komponent);
        }
    }

    protected static function checkPreventSubmit($komponent)
    {
        if ($komponent->_kompo('options', 'preventSubmit')) {
            return static::throwUnauthorizedException($komponent, 'submit');
        }
    }
}
