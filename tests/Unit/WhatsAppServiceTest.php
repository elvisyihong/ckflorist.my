<?php

use App\Services\WhatsAppService;

test('WhatsApp message includes reference, selection and non-confirmation notice', function (): void {
    $enquiry = ['reference'=>'CKF-20260806-ABCDE','customer_name'=>'Aminah','customer_phone'=>'6731234567','fulfilment_method'=>'pickup','requested_date'=>'2026-08-10','requested_time'=>'2 PM','delivery_address'=>'','bouquet'=>['occasion_name'=>'Birthday','flower_names'=>['Rose','Tulip'],'sample_name'=>'Quiet Garden','colour_names'=>['Blush & Cream'],'size_name'=>'Signature','wrapping_name'=>'Soft Ivory','decoration_names'=>['Message Card'],'budget_min'=>80,'budget_max'=>120,'instructions'=>'No lilies'],'cafe'=>[['quantity'=>2,'name'=>'Rose Latte','options'=>['Iced']]],'customer_notes'=>'Please message first'];
    $message = (new WhatsAppService())->message($enquiry);
    assert_true(str_contains($message, 'CKF-20260806-ABCDE'));
    assert_true(str_contains($message, 'Rose, Tulip'));
    assert_true(str_contains($message, '2 × Rose Latte'));
    assert_true(str_contains($message, 'does not confirm'));
});

test('WhatsApp URL strips formatting from configured number', function (): void {
    $service = new WhatsAppService();
    $url = $service->url('+673 123 4567', ['reference'=>'CKF-20260806-ABCDE','customer_name'=>'A','customer_phone'=>'1','fulfilment_method'=>'pickup','requested_date'=>'2026-08-10','requested_time'=>'','delivery_address'=>'','bouquet'=>null,'cafe'=>[],'customer_notes'=>'']);
    assert_true(str_starts_with($url, 'https://wa.me/6731234567?text='));
});

