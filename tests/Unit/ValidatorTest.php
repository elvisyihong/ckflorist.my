<?php

use App\Core\Validator;

test('enquiry validation requires consent and delivery address', function (): void {
    $errors = Validator::enquiry(['customer_name' => 'Aminah', 'customer_phone' => '6731234567', 'fulfilment_method' => 'delivery', 'requested_date' => date('Y-m-d', strtotime('+1 day'))]);
    assert_true(isset($errors['delivery_address']));
    assert_true(isset($errors['consent']));
});

test('valid pickup enquiry passes validation', function (): void {
    $errors = Validator::enquiry(['customer_name' => 'Aminah', 'customer_phone' => '6731234567', 'customer_email' => 'a@example.com', 'fulfilment_method' => 'pickup', 'requested_date' => date('Y-m-d', strtotime('+1 day')), 'consent' => '1']);
    assert_same([], $errors);
});

