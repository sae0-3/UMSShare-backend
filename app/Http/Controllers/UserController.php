<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getProfile(Request $request): JsonResponse
    {
        return $request->user();
    }
}
