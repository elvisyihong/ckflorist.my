<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\AdminResourceRepository;

final class ResourceController
{
    public function __construct(private readonly AdminResourceRepository $resources) {}
    public function index(Request $request): void
    {
        $resource = (string) $request->param('resource'); $config = $this->resources->config($resource);
        View::render('admin/resource-index', ['title' => $config['label'], 'resource' => $resource, 'config' => $config, 'pagination' => $this->resources->paginate($resource, (int) $request->query('page', 1))], 'layouts/admin');
    }
    public function create(Request $request): void { $this->form($request, null); }
    public function edit(Request $request): void { $this->form($request, $this->resources->find((string) $request->param('resource'), (int) $request->param('id'))); }
    public function store(Request $request): void
    {
        $resource = (string) $request->param('resource'); $user = Auth::user();
        try { $this->resources->create($resource, $request->all(), (int) $user['id'], $request->ip()); Session::flash('success', 'Record created.'); }
        catch (\Throwable $exception) { Session::flash('error', $exception->getMessage()); Session::flash('old', $request->all()); }
        Response::redirect('/admin/' . rawurlencode($resource));
    }
    public function update(Request $request): void
    {
        $resource = (string) $request->param('resource'); $id = (int) $request->param('id'); $user = Auth::user();
        try { $this->resources->update($resource, $id, $request->all(), (int) $user['id'], $request->ip()); Session::flash('success', 'Record saved.'); }
        catch (\Throwable $exception) { Session::flash('error', $exception->getMessage()); }
        Response::redirect('/admin/' . rawurlencode($resource) . '/' . $id . '/edit');
    }
    public function delete(Request $request): void
    {
        $resource = (string) $request->param('resource'); $user = Auth::user();
        $this->resources->delete($resource, (int) $request->param('id'), (int) $user['id'], $request->ip());
        Session::flash('success', 'Record removed.'); Response::redirect('/admin/' . rawurlencode($resource));
    }
    private function form(Request $request, ?array $record): void
    {
        $resource = (string) $request->param('resource'); $config = $this->resources->config($resource);
        View::render('admin/resource-form', ['title' => ($record ? 'Edit ' : 'New ') . rtrim($config['label'], 's'), 'resource' => $resource, 'config' => $config, 'record' => $record], 'layouts/admin');
    }
}

