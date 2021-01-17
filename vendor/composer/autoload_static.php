<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit289a0449b3f03b26947975233d47852c
{
    public static $prefixLengthsPsr4 = array (
        'm' => 
        array (
            'mediaburst\\ClockworkSMS\\' => 24,
        ),
        'l' => 
        array (
            'libphonenumber\\' => 15,
        ),
        'g' => 
        array (
            'geekpop\\' => 8,
        ),
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'G' => 
        array (
            'Giggsey\\Locale\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'mediaburst\\ClockworkSMS\\' => 
        array (
            0 => __DIR__ . '/..' . '/mediaburst/clockworksms/src',
        ),
        'libphonenumber\\' => 
        array (
            0 => __DIR__ . '/..' . '/20steps/libphonenumber-for-php/src',
        ),
        'geekpop\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'Giggsey\\Locale\\' => 
        array (
            0 => __DIR__ . '/..' . '/giggsey/locale/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'S' => 
        array (
            'SecurityLib' => 
            array (
                0 => __DIR__ . '/..' . '/ircmaxell/security-lib/lib',
            ),
        ),
        'R' => 
        array (
            'RandomLib' => 
            array (
                0 => __DIR__ . '/..' . '/ircmaxell/random-lib/lib',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit289a0449b3f03b26947975233d47852c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit289a0449b3f03b26947975233d47852c::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit289a0449b3f03b26947975233d47852c::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit289a0449b3f03b26947975233d47852c::$classMap;

        }, null, ClassLoader::class);
    }
}