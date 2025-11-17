<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

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
            $plan = null;
            $limitReached = false;
            $productLimitReached = null;
            $staffReached = false;
            $staffLimitReached = null;

            if (Auth::guard('owner')->check()) {
                $owner_id = Auth::guard('owner')->user()->owner_id;

                // $plan_type = collect(DB::select("
                //     SELECT plan_id
                //     FROM subscriptions
                //     WHERE owner_id = ?
                //     ORDER BY subscription_id DESC
                //     LIMIT 1
                // ", [$owner_id]))->pluck('plan_id')->first();

                $owner = collect(DB::select("
                    SELECT o.owner_id, s.subscription_end, plan_id
                    FROM owners o
                    JOIN subscriptions s ON o.owner_id = s.owner_id
                    WHERE o.owner_id = ?
                    ORDER BY s.subscription_id DESC
                    LIMIT 1
                ", [$owner_id]))->first();

                $inventoryCount = collect(DB::select("
                    select count(prod_code) as product_count
                    from products
                    where owner_id = ?
                    LIMIT 1
                ", [$owner_id]))->first();

                $productCount = DB::table('products')->where('owner_id', $owner_id)->count();
                $staffCount = DB::table('staff')->where('owner_id', $owner_id)->count();

                if (!$owner || !$owner->plan_id) {
                    $expired = false;
                    $plan = null;
                    $invenCount = 0;
                }
                else if ($owner && $owner->subscription_end <= date('Y-m-d')) {
                    $expired = true;
                    
                } else {

                    if ($owner->plan_id == 3 || $owner->plan_id === null) {      // basic
                        $plan = 3;

                        if((int)$productCount >= 50) {
                            $limitReached = true;
                            $productLimitReached = "Your current Basic plan allows up to 50 products only. Upgrade your plan to add more items.";
                        }
                        
                    } elseif ($owner->plan_id == 1) { // standard
                        $plan = 1;

                        if((int)$productCount >= 200) {
                            $limitReached = true;
                            $productLimitReached = "Your current Standard plan allows up to 200 products only. Upgrade your plan to add more items.";
                        }

                        if((int)$staffCount >= 1) {
                            $staffReached = true;
                            $staffLimitReached = "Your subscription could only hold 1 staff account. Upgrade your plan to add more staff.";
                        }

                    } elseif ($owner->plan_id == 2) { // premium
                        $plan = 2;
                    } 
                }
            }

            $view->with(compact('expired', 'plan', 'productLimitReached', 'limitReached', 'staffReached', 'staffLimitReached'));
            App::instance('expired', $expired);
            App::instance('plan', $plan);
            App::instance('productLimitReached', $productLimitReached);
            App::instance('limitReached', $limitReached);
            App::instance('staffReached', $staffReached);
            App::instance('staffLimitReached', $staffLimitReached);
        });
    }

}
