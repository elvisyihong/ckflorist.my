SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE admin_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('owner', 'manager', 'editor', 'viewer') NOT NULL DEFAULT 'editor',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admin_users_email (email),
    KEY idx_admin_users_active_role (is_active, role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identity_hash CHAR(64) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    was_successful TINYINT(1) NOT NULL DEFAULT 0,
    KEY idx_login_attempts_window (identity_hash, ip_hash, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shop_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value LONGTEXT NULL,
    value_type ENUM('string', 'text', 'json', 'boolean', 'integer') NOT NULL DEFAULT 'string',
    is_public TINYINT(1) NOT NULL DEFAULT 0,
    updated_by BIGINT UNSIGNED NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_shop_settings_user FOREIGN KEY (updated_by) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    disk VARCHAR(30) NOT NULL DEFAULT 'public',
    path VARCHAR(255) NOT NULL,
    thumbnail_path VARCHAR(255) NULL,
    responsive_paths JSON NULL,
    mime_type VARCHAR(80) NOT NULL,
    width SMALLINT UNSIGNED NULL,
    height SMALLINT UNSIGNED NULL,
    size_bytes INT UNSIGNED NOT NULL,
    alt_text VARCHAR(255) NOT NULL DEFAULT '',
    uploaded_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_media_path (path),
    CONSTRAINT fk_media_user FOREIGN KEY (uploaded_by) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE flower_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    description TEXT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_flower_categories_slug (slug),
    KEY idx_flower_categories_active_order (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE arrangement_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    description TEXT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_arrangement_types_slug (slug),
    KEY idx_arrangement_types_active_order (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE colour_themes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    hex_value CHAR(7) NULL,
    description TEXT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_colour_themes_slug (slug),
    KEY idx_colour_themes_active_order (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE occasions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    description TEXT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_occasions_slug (slug),
    KEY idx_occasions_active_order (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bouquet_sizes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    description TEXT NULL,
    price_adjustment DECIMAL(10,2) NOT NULL DEFAULT 0,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_bouquet_sizes_slug (slug),
    KEY idx_bouquet_sizes_active_order (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE wrapping_papers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    description TEXT NULL,
    image_id BIGINT UNSIGNED NULL,
    price_adjustment DECIMAL(10,2) NOT NULL DEFAULT 0,
    is_florist_choice TINYINT(1) NOT NULL DEFAULT 0,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wrapping_papers_slug (slug),
    KEY idx_wrapping_papers_active_order (is_active, display_order),
    CONSTRAINT fk_wrapping_papers_image FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE decorations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    description TEXT NULL,
    image_id BIGINT UNSIGNED NULL,
    price_adjustment DECIMAL(10,2) NOT NULL DEFAULT 0,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_decorations_slug (slug),
    KEY idx_decorations_active_order (is_active, display_order),
    CONSTRAINT fk_decorations_image FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE florist_samples (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    arrangement_type_id BIGINT UNSIGNED NULL,
    cover_image_id BIGINT UNSIGNED NULL,
    name VARCHAR(160) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    dominance_weight TINYINT UNSIGNED NOT NULL DEFAULT 50,
    estimated_price_min DECIMAL(10,2) NULL,
    estimated_price_max DECIMAL(10,2) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_florist_samples_slug (slug),
    KEY idx_florist_samples_listing (is_active, is_featured, display_order, created_at),
    KEY idx_florist_samples_arrangement (arrangement_type_id),
    CONSTRAINT fk_florist_samples_arrangement FOREIGN KEY (arrangement_type_id) REFERENCES arrangement_types(id) ON DELETE SET NULL,
    CONSTRAINT fk_florist_samples_cover FOREIGN KEY (cover_image_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE florist_sample_flowers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sample_id BIGINT UNSIGNED NOT NULL,
    flower_category_id BIGINT UNSIGNED NOT NULL,
    is_main TINYINT(1) NOT NULL DEFAULT 0,
    dominance_weight TINYINT UNSIGNED NOT NULL DEFAULT 50,
    display_order INT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_sample_flower (sample_id, flower_category_id),
    KEY idx_sample_flowers_match (flower_category_id, sample_id, is_main),
    CONSTRAINT fk_sample_flowers_sample FOREIGN KEY (sample_id) REFERENCES florist_samples(id) ON DELETE CASCADE,
    CONSTRAINT fk_sample_flowers_flower FOREIGN KEY (flower_category_id) REFERENCES flower_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE florist_sample_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sample_id BIGINT UNSIGNED NOT NULL,
    media_id BIGINT UNSIGNED NOT NULL,
    is_cover TINYINT(1) NOT NULL DEFAULT 0,
    display_order INT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_sample_image (sample_id, media_id),
    KEY idx_sample_images_order (sample_id, is_cover, display_order),
    CONSTRAINT fk_sample_images_sample FOREIGN KEY (sample_id) REFERENCES florist_samples(id) ON DELETE CASCADE,
    CONSTRAINT fk_sample_images_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE florist_sample_colours (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sample_id BIGINT UNSIGNED NOT NULL,
    colour_theme_id BIGINT UNSIGNED NOT NULL,
    UNIQUE KEY uq_sample_colour (sample_id, colour_theme_id),
    KEY idx_sample_colours_colour (colour_theme_id, sample_id),
    CONSTRAINT fk_sample_colours_sample FOREIGN KEY (sample_id) REFERENCES florist_samples(id) ON DELETE CASCADE,
    CONSTRAINT fk_sample_colours_colour FOREIGN KEY (colour_theme_id) REFERENCES colour_themes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE florist_sample_occasions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sample_id BIGINT UNSIGNED NOT NULL,
    occasion_id BIGINT UNSIGNED NOT NULL,
    UNIQUE KEY uq_sample_occasion (sample_id, occasion_id),
    KEY idx_sample_occasions_occasion (occasion_id, sample_id),
    CONSTRAINT fk_sample_occasions_sample FOREIGN KEY (sample_id) REFERENCES florist_samples(id) ON DELETE CASCADE,
    CONSTRAINT fk_sample_occasions_occasion FOREIGN KEY (occasion_id) REFERENCES occasions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE florist_sample_wrappings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sample_id BIGINT UNSIGNED NOT NULL,
    wrapping_paper_id BIGINT UNSIGNED NOT NULL,
    UNIQUE KEY uq_sample_wrapping (sample_id, wrapping_paper_id),
    CONSTRAINT fk_sample_wrappings_sample FOREIGN KEY (sample_id) REFERENCES florist_samples(id) ON DELETE CASCADE,
    CONSTRAINT fk_sample_wrappings_wrap FOREIGN KEY (wrapping_paper_id) REFERENCES wrapping_papers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE florist_sample_decorations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sample_id BIGINT UNSIGNED NOT NULL,
    decoration_id BIGINT UNSIGNED NOT NULL,
    UNIQUE KEY uq_sample_decoration (sample_id, decoration_id),
    CONSTRAINT fk_sample_decorations_sample FOREIGN KEY (sample_id) REFERENCES florist_samples(id) ON DELETE CASCADE,
    CONSTRAINT fk_sample_decorations_decoration FOREIGN KEY (decoration_id) REFERENCES decorations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cafe_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    description TEXT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cafe_categories_slug (slug),
    KEY idx_cafe_categories_active_order (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cafe_products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    cover_image_id BIGINT UNSIGNED NULL,
    name VARCHAR(160) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    regular_price DECIMAL(10,2) NOT NULL,
    promotional_price DECIMAL(10,2) NULL,
    dietary_labels JSON NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    display_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cafe_products_slug (slug),
    KEY idx_cafe_products_listing (category_id, is_available, is_featured, display_order),
    CONSTRAINT fk_cafe_products_category FOREIGN KEY (category_id) REFERENCES cafe_categories(id),
    CONSTRAINT fk_cafe_products_cover FOREIGN KEY (cover_image_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cafe_product_options (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    option_group ENUM('size', 'temperature', 'addon') NOT NULL,
    name VARCHAR(100) NOT NULL,
    price_adjustment DECIMAL(10,2) NOT NULL DEFAULT 0,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    display_order INT NOT NULL DEFAULT 0,
    KEY idx_cafe_options_product (product_id, option_group, is_available, display_order),
    CONSTRAINT fk_cafe_options_product FOREIGN KEY (product_id) REFERENCES cafe_products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE promotional_banners (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    body TEXT NULL,
    image_id BIGINT UNSIGNED NULL,
    link_url VARCHAR(500) NULL,
    link_label VARCHAR(80) NULL,
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_banners_schedule (is_active, starts_at, ends_at, display_order),
    CONSTRAINT fk_banners_image FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE gallery_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    media_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(160) NULL,
    caption TEXT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_gallery_listing (is_active, display_order, created_at),
    CONSTRAINT fk_gallery_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE policies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    body LONGTEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_policies_slug (slug),
    KEY idx_policies_active (is_active, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE enquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(24) NOT NULL,
    status ENUM('New','Contacted','Awaiting Confirmation','Confirmed','Deposit Pending','In Preparation','Ready','Completed','Cancelled') NOT NULL DEFAULT 'New',
    customer_name VARCHAR(160) NOT NULL,
    customer_phone VARCHAR(40) NOT NULL,
    customer_email VARCHAR(190) NULL,
    fulfilment_method ENUM('delivery', 'pickup') NOT NULL,
    requested_date DATE NOT NULL,
    requested_time VARCHAR(50) NULL,
    delivery_address TEXT NULL,
    occasion_id BIGINT UNSIGNED NULL,
    bouquet_snapshot JSON NULL,
    cafe_snapshot JSON NULL,
    estimated_total_min DECIMAL(10,2) NULL,
    estimated_total_max DECIMAL(10,2) NULL,
    customer_notes TEXT NULL,
    consented_at DATETIME NOT NULL,
    whatsapp_opened_at DATETIME NULL,
    source_ip_hash CHAR(64) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_enquiries_reference (reference),
    KEY idx_enquiries_status_created (status, created_at),
    KEY idx_enquiries_phone (customer_phone),
    KEY idx_enquiries_requested (requested_date, fulfilment_method),
    CONSTRAINT fk_enquiries_occasion FOREIGN KEY (occasion_id) REFERENCES occasions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE enquiry_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enquiry_id BIGINT UNSIGNED NOT NULL,
    event_type VARCHAR(80) NOT NULL,
    from_status VARCHAR(40) NULL,
    to_status VARCHAR(40) NULL,
    note TEXT NULL,
    actor_admin_id BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_enquiry_events_timeline (enquiry_id, created_at),
    CONSTRAINT fk_enquiry_events_enquiry FOREIGN KEY (enquiry_id) REFERENCES enquiries(id) ON DELETE CASCADE,
    CONSTRAINT fk_enquiry_events_actor FOREIGN KEY (actor_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id VARCHAR(100) NULL,
    before_json JSON NULL,
    after_json JSON NULL,
    ip_hash CHAR(64) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_audit_entity (entity_type, entity_id, created_at),
    KEY idx_audit_actor (admin_user_id, created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
