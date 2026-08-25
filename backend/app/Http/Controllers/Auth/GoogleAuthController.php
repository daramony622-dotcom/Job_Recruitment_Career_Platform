<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    public function conn(Request $request){
        $user = $request->user();
        if(empty($user)){
            return redirect()->back()->with("error","Invalid Email");
        }
        return redirect()->back()->with("success","");
    }
}
