<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $expired = false;

            if (Auth::guard('owner')->check()) {
                $owner_id = Auth::guard('owner')->user()->owner_id;

                $owner = collect(DB::select("
                    select o.owner_id, s.subscription_end 
                    from owners o
                    join subscriptions s on o.owner_id = s.owner_id
                    where o.owner_id = ?
                    order by subscription_id desc
                    limit 1", [$owner_id]))->first();

                if ($owner && $owner->subscription_end <= date('Y-m-d')) {
                    $expired = true;
                }
            }

            $view->with('expired', $expired);
        });
    }
}
