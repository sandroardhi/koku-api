<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // if (config('app.debug')) {
        //     DB::listen(function ($query) {
        //         // Get the backtrace
        //         $backtrace = debug_backtrace();

        //         // Extract file paths and line numbers from the backtrace
        //         $backtraceInfo = array_map(function ($trace) {
        //             return isset($trace['file'], $trace['line']) ? $trace['file'] . ':' . $trace['line'] : 'Unknown';
        //         }, $backtrace);

        //         // Find the controller method
        //         $controllerMethod = $this->findControllerMethod($backtrace);

        //         Log::info([
        //             'sql' => $query->sql,
        //             'bindings' => $query->bindings,
        //             'time' => $query->time,
        //             'controller_method' => $controllerMethod ?: 'Unknown',
        //             'backtrace' => $backtraceInfo // Log the extracted backtrace information
        //         ]);
        //     });
        // }
    }

    // protected function findControllerMethod($backtrace)
    // {
    //     foreach ($backtrace as $trace) {
    //         if (isset($trace['class']) && strpos($trace['class'], 'App\\Http\\Controllers\\') !== false) {
    //             return $trace['class'] . '@' . $trace['function'];
    //         }
    //     }

    //     return null;
    // }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
