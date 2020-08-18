<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function acp()
    {
        if (Gate::denies('view-acp')) {
            toast('Permission Denied', 'warning');
            return redirect()->route('home');
        }

        $users = $this->getLatestUsers();
        $userCount = $this->getUserCount();

        $roles = $this->getGlobalRoles();

        return view('acp.index')->with([
            'users' => $users,
            'userCount' => $userCount,
            'roles' => $roles,
        ]);
    }

    public function sendTestEmail()
    {
        $user = $this->getUser(4);
        $this->mailAdminNewUser($user);

        return redirect('/test_email');
    }

    public function forge()
    {

        $user = $this->getUser(Auth::id());
        if (env("APP_ENV") != "forge" && env("APP_ENV") != "local") {
            Log::channel('app')->alert("Attempt to access Forge Test Page by " . json_encode($user));
            $this->sendAdminNotification("alert", $user);
            return redirect()->route('home');
        }
        
        $global = $this->getGlobalRoles();

        return view('acp.forge')->with([
            'roles' => $global,
        ]);
    }

    public function forgePowerUp($id)
    {
        $user = $this->getUser(Auth::id());

        $role = $this->getRole($id);

        Log::channel('app')->alert("Granting " . $role->name . " Powers to " . json_encode($user));
        $this->sendAdminNotification("alert", $user);
        
        $user->roles()->attach($role->id);

        toast("Granted " . $role->name . " powers", "success");

        return redirect()->route('profile', $user->id);
    }
}
