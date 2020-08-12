<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    private $cache_time = 1440;

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('view-acp', function ($user) {
            if ($this->isAdmin($user)) { return true; }
            return false;
        });
    }

    private function isAdmin($user) 
    {
        return Cache::remember('user:' . $user->id . ':is:Admin', $this->cache_time, function() use($user) {
            $admin = Role::where('name', '=', 'Admin')->first();
            return $user->roles->contains($admin);
        });
    }

    private function isGameMaster($user) {
        return Cache::remember('user:' . $user->id . ':is:GameMaster', $this->cache_time, function() use($user) {
            $gm = Role::where('name', '=', 'Game Master')->first();
            return $user->roles->contains($gm);    
        });
    }

    private function isCommunityManager($user) {
        return Cache::remember('user:' . $user->id . ':is:CommunityManager', $this->cache_time, function() use($user) {
            $cm = Role::where('name', '=', 'Community Manager')->first();
            return $user->roles->contains($cm);    
        });
    }
}
