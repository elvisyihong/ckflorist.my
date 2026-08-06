<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\EnquiryRepository;
use App\Repositories\SettingsRepository;
use App\Services\EnquiryService;
use App\Services\ValidationException;

final class EnquiryController
{
    public function __construct(private readonly EnquiryService $service, private readonly EnquiryRepository $enquiries, private readonly SettingsRepository $settings) {}

    public function store(Request $request): void
    {
        try {
            $enquiry = $this->service->submit($request->all(), $request->ip());
            Session::put('last_enquiry', ['reference' => $enquiry['reference'], 'whatsapp_url' => $enquiry['whatsapp_url']]);
            if ($request->isJson()) Response::json(['ok' => true, 'data' => ['reference' => $enquiry['reference'], 'whatsapp_url' => $enquiry['whatsapp_url']]], 201);
            Response::redirect('/enquiry/' . rawurlencode($enquiry['reference']));
        } catch (ValidationException $exception) {
            if ($request->isJson()) Response::json(['ok' => false, 'error' => $exception->getMessage(), 'errors' => $exception->errors], 422);
            Session::flash('errors', $exception->errors);
            Session::flash('old', $request->all());
            Response::redirect('/selection');
        } catch (\Throwable $exception) {
            if ($request->isJson()) Response::json(['ok' => false, 'error' => $exception->getMessage()], 503);
            Session::flash('error', $exception->getMessage());
            Response::redirect('/selection');
        }
    }

    public function show(Request $request): void
    {
        $reference = (string) $request->param('reference');
        $record = $this->enquiries->findByReference($reference);
        $last = Session::get('last_enquiry', []);
        if (!$record || ($last['reference'] ?? '') !== $reference) {
            http_response_code(404);
            View::render('pages/not-found', [], 'layouts/public');
            return;
        }
        View::render('pages/enquiry', ['title' => 'Enquiry ' . $reference, 'settings' => $this->settings->all(), 'enquiry' => $record, 'whatsappUrl' => $last['whatsapp_url']], 'layouts/public');
    }
}

