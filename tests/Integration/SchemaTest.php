<?php

test('schema contains required normalized relationships and indexes', function (): void {
    $sql = file_get_contents(BASE_PATH . '/database/migrations/001_initial.sql');
    foreach (['florist_sample_flowers','florist_sample_colours','florist_sample_occasions','florist_sample_wrappings','florist_sample_decorations','cafe_product_options','enquiries','enquiry_events','audit_logs','login_attempts'] as $table) {
        assert_true(str_contains($sql, "CREATE TABLE {$table}"), "Missing {$table}");
    }
    assert_true(!preg_match('/flower_categories[^;]*comma-separated/is', $sql));
    assert_true(str_contains($sql, 'idx_sample_flowers_match'));
    assert_true(str_contains($sql, 'idx_enquiries_status_created'));
});

