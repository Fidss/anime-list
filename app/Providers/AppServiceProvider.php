<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define mb_strimwidth polyfill in the Illuminate\Support namespace at runtime
        if (! function_exists('Illuminate\\Support\\mb_strimwidth')) {
            $code = <<<'PHP'
namespace Illuminate\Support;

function mb_strimwidth(string $str, int $start, int $width, string $trimmarker = "", string $encoding = 'UTF-8') : string
{
    $chars = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);

    if ($chars === false) {
        $substr = substr($str, $start, $width);
        if (strlen($str) > $start + $width) {
            $substr .= $trimmarker;
        }
        return $substr;
    }

    $slice = array_slice($chars, $start, $width);
    $out = implode('', $slice);

    if (count($chars) > $start + $width) {
        $out .= $trimmarker;
    }

    return $out;
}
PHP;
            eval($code);
        }
    }
}
