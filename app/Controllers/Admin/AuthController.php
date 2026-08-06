<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Repositories\AuthRepository;

final class AuthController
{
    public function __construct(private readonly AuthRepository $users) {}
    public function form(Request $request): void
    {
        if (Auth::user()) Response::redirect('/admin');
        View::render('admin/login', ['title' => 'Admin sign in'], 'layouts/auth');
    }
    public function login(Request $request): void
    {
        $errors = Validator::login($request->all());
        if ($errors !== []) {
            Session::flash('errors', $errors); Response::redirect('/admin/login');
        }
        try { $user = $this->users->attempt((string) $request->input('email'), (string) $request->input('password'), $request->ip()); }
        catch (\RuntimeException $exception) { Session::flash('error', $exception->getMessage()); Response::redirect('/admin/login'); }
        if (!$user) { Session::flash('error', 'The email or password is incorrect.'); Response::redirect('/admin/login'); }
        Auth::login($user); Response::redirect('/admin');
    }
    public function logout(Request $request): void { Auth::logout(); Response::redirect('/admin/login'); }
}

