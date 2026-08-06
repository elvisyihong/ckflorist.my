<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Services\UploadService;

final class UploadController
{
    public function __construct(private readonly UploadService $uploads) {}
    public function store(Request $request): void
    {
        try { $media = $this->uploads->store($request->file('image') ?? [], (int) Auth::user()['id'], (string) $request->input('alt_text', '')); Response::json(['ok' => true, 'data' => $media], 201); }
        catch (\Throwable $exception) { Response::json(['ok' => false, 'error' => $exception->getMessage()], 422); }
    }
}

