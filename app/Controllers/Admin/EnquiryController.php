<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\EnquiryRepository;

final class EnquiryController
{
    public function __construct(private readonly EnquiryRepository $enquiries) {}
    public function index(Request $request): void
    {
        View::render('admin/enquiries', ['title' => 'Enquiries', 'enquiries' => $this->enquiries->recent(100)], 'layouts/admin');
    }
    public function show(Request $request): void
    {
        $enquiry = $this->enquiries->find((int) $request->param('id'));
        if (!$enquiry) { http_response_code(404); View::render('pages/not-found', [], 'layouts/admin'); return; }
        View::render('admin/enquiry', ['title' => $enquiry['reference'], 'enquiry' => $enquiry, 'statuses' => app_config('statuses', [])], 'layouts/admin');
    }
    public function status(Request $request): void
    {
        $status = (string) $request->input('status'); $statuses = app_config('statuses', []);
        if (!in_array($status, $statuses, true)) { Session::flash('error', 'Choose a valid status.'); Response::redirect('/admin/enquiries/' . (int) $request->param('id')); }
        $user = Auth::user(); $this->enquiries->updateStatus((int) $request->param('id'), $status, (int) $user['id'], (string) $request->input('note', ''));
        Session::flash('success', 'Enquiry status updated.'); Response::redirect('/admin/enquiries/' . (int) $request->param('id'));
    }
}
