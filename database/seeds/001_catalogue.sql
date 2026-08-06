INSERT INTO shop_settings (setting_key, setting_value, value_type, is_public) VALUES
('business_name', 'CK Florist', 'string', 1),
('whatsapp_number', '6730000000', 'string', 1),
('telephone', '+673 000 0000', 'string', 1),
('email', 'hello@ckflorist.my', 'string', 1),
('address', 'Brunei Darussalam', 'text', 1),
('currency', 'BND', 'string', 1),
('allow_combined_enquiries', '1', 'boolean', 1),
('maintenance_mode', '0', 'boolean', 1),
('maintenance_title', 'Our menu is available', 'string', 1),
('maintenance_message', 'Browse our current menu while we prepare the full website.', 'text', 1),
('maintenance_menu_images', '[]', 'json', 1),
('business_hours', '{"Monday–Saturday":"9:00 AM–6:00 PM","Sunday":"By appointment"}', 'json', 1),
('florist_disclaimer', 'Sample photos are references. Flower availability and exact shades may vary, and final pricing requires confirmation.', 'text', 1),
('delivery_information', 'Delivery availability and fees are confirmed after reviewing your enquiry.', 'text', 1),
('pickup_information', 'Pickup time is confirmed by our team through WhatsApp.', 'text', 1);

INSERT INTO flower_categories (name, slug, description, display_order) VALUES
('Rose', 'rose', 'Classic garden and premium imported roses.', 10),
('Tulip', 'tulip', 'Clean sculptural stems with seasonal colour.', 20),
('Lily', 'lily', 'Fragrant statement flowers.', 30),
('Hydrangea', 'hydrangea', 'Cloud-like volume and soft colour.', 40),
('Baby’s Breath', 'babys-breath', 'Airy texture for delicate arrangements.', 50),
('Sunflower', 'sunflower', 'Bright, expressive seasonal stems.', 60);

INSERT INTO arrangement_types (name, slug, description, display_order) VALUES
('Hand-tied Bouquet', 'hand-tied-bouquet', 'A naturally gathered bouquet finished with wrapping.', 10),
('Flower Box', 'flower-box', 'Structured flowers arranged in a keepsake box.', 20),
('Vase Arrangement', 'vase-arrangement', 'Ready-to-display flowers in a vessel.', 30);

INSERT INTO colour_themes (name, slug, hex_value, display_order) VALUES
('Blush & Cream', 'blush-cream', '#E8C8BE', 10),
('White & Green', 'white-green', '#DDE3D5', 20),
('Sunset', 'sunset', '#D98768', 30),
('Pastel Garden', 'pastel-garden', '#D9CBE4', 40),
('Florist’s Choice', 'florists-choice', '#70866F', 50);

INSERT INTO occasions (name, slug, description, display_order) VALUES
('Birthday', 'birthday', 'A joyful bouquet for their day.', 10),
('Anniversary', 'anniversary', 'A romantic marker of time together.', 20),
('Graduation', 'graduation', 'Fresh flowers for a proud milestone.', 30),
('Thank You', 'thank-you', 'A thoughtful expression of gratitude.', 40),
('Just Because', 'just-because', 'No occasion required.', 50);

INSERT INTO bouquet_sizes (name, slug, description, price_adjustment, display_order) VALUES
('Petite', 'petite', 'A considered, compact gesture.', 0, 10),
('Signature', 'signature', 'Our balanced everyday bouquet.', 25, 20),
('Statement', 'statement', 'Abundant volume and premium stems.', 60, 30);

INSERT INTO wrapping_papers (name, slug, description, price_adjustment, is_florist_choice, display_order) VALUES
('Natural Kraft', 'natural-kraft', 'Warm textured paper with a clean fold.', 0, 0, 10),
('Soft Ivory', 'soft-ivory', 'Layered matte ivory wrapping.', 3, 0, 20),
('Florist’s Choice', 'florists-choice', 'Let our florist select the best finish.', 0, 1, 30);

INSERT INTO decorations (name, slug, description, price_adjustment, display_order) VALUES
('Satin Ribbon', 'satin-ribbon', 'A soft tonal ribbon finish.', 2, 10),
('Message Card', 'message-card', 'A handwritten card with your message.', 1, 20),
('Fairy Lights', 'fairy-lights', 'Fine warm lights for evening gifting.', 8, 30);

INSERT INTO media (path, thumbnail_path, responsive_paths, mime_type, width, height, size_bytes, alt_text) VALUES
('public/assets/images/rose-bouquet-900.webp', 'public/assets/images/rose-bouquet-450.webp', JSON_OBJECT('450', 'public/assets/images/rose-bouquet-450.webp', '900', 'public/assets/images/rose-bouquet-900.webp'), 'image/webp', 900, 900, 77824, 'Red and blush rose hand-tied bouquet in ivory wrapping'),
('public/assets/images/pastel-bouquet-900.webp', 'public/assets/images/pastel-bouquet-450.webp', JSON_OBJECT('450', 'public/assets/images/pastel-bouquet-450.webp', '900', 'public/assets/images/pastel-bouquet-900.webp'), 'image/webp', 900, 900, 41984, 'Blush tulip, cream rose and white hydrangea bouquet'),
('public/assets/images/cafe-900.webp', 'public/assets/images/cafe-450.webp', JSON_OBJECT('450', 'public/assets/images/cafe-450.webp', '900', 'public/assets/images/cafe-900.webp'), 'image/webp', 900, 900, 56320, 'Rose latte and pistachio financier on a café table');

INSERT INTO florist_samples (arrangement_type_id, cover_image_id, name, slug, description, dominance_weight, estimated_price_min, estimated_price_max, is_featured, display_order, published_at)
SELECT a.id, m.id, 'Velvet Rose Study', 'velvet-rose-study', 'An abundant romantic study led by deep red and blush roses with a quiet veil of baby’s breath.', 90, 88, 138, 1, 10, NOW()
FROM arrangement_types a JOIN media m ON m.path = 'public/assets/images/rose-bouquet-900.webp' WHERE a.slug = 'hand-tied-bouquet';
INSERT INTO florist_samples (arrangement_type_id, cover_image_id, name, slug, description, dominance_weight, estimated_price_min, estimated_price_max, is_featured, display_order, published_at)
SELECT a.id, m.id, 'Quiet Garden', 'quiet-garden', 'A light, architectural arrangement of blush tulips, cream roses and cloud-like hydrangea.', 75, 78, 128, 1, 20, NOW()
FROM arrangement_types a JOIN media m ON m.path = 'public/assets/images/pastel-bouquet-900.webp' WHERE a.slug = 'hand-tied-bouquet';

INSERT INTO florist_sample_flowers (sample_id, flower_category_id, is_main, dominance_weight, display_order)
SELECT s.id, f.id, 1, 95, 10 FROM florist_samples s JOIN flower_categories f ON f.slug = 'rose' WHERE s.slug = 'velvet-rose-study';
INSERT INTO florist_sample_flowers (sample_id, flower_category_id, is_main, dominance_weight, display_order)
SELECT s.id, f.id, 0, 25, 20 FROM florist_samples s JOIN flower_categories f ON f.slug = 'babys-breath' WHERE s.slug = 'velvet-rose-study';
INSERT INTO florist_sample_flowers (sample_id, flower_category_id, is_main, dominance_weight, display_order)
SELECT s.id, f.id, 1, 80, 10 FROM florist_samples s JOIN flower_categories f ON f.slug = 'tulip' WHERE s.slug = 'quiet-garden';
INSERT INTO florist_sample_flowers (sample_id, flower_category_id, is_main, dominance_weight, display_order)
SELECT s.id, f.id, 0, 55, 20 FROM florist_samples s JOIN flower_categories f ON f.slug = 'rose' WHERE s.slug = 'quiet-garden';
INSERT INTO florist_sample_flowers (sample_id, flower_category_id, is_main, dominance_weight, display_order)
SELECT s.id, f.id, 0, 50, 30 FROM florist_samples s JOIN flower_categories f ON f.slug = 'hydrangea' WHERE s.slug = 'quiet-garden';

INSERT INTO florist_sample_colours (sample_id, colour_theme_id)
SELECT s.id, c.id FROM florist_samples s JOIN colour_themes c ON c.slug = 'blush-cream' WHERE s.slug IN ('velvet-rose-study', 'quiet-garden');
INSERT INTO florist_sample_colours (sample_id, colour_theme_id)
SELECT s.id, c.id FROM florist_samples s JOIN colour_themes c ON c.slug = 'white-green' WHERE s.slug = 'quiet-garden';
INSERT INTO florist_sample_occasions (sample_id, occasion_id)
SELECT s.id, o.id FROM florist_samples s JOIN occasions o ON o.slug IN ('birthday', 'anniversary') WHERE s.slug = 'velvet-rose-study';
INSERT INTO florist_sample_occasions (sample_id, occasion_id)
SELECT s.id, o.id FROM florist_samples s JOIN occasions o ON o.slug IN ('birthday', 'graduation', 'thank-you', 'just-because') WHERE s.slug = 'quiet-garden';
INSERT INTO florist_sample_wrappings (sample_id, wrapping_paper_id)
SELECT s.id, w.id FROM florist_samples s JOIN wrapping_papers w ON w.slug = 'soft-ivory' WHERE s.slug IN ('velvet-rose-study', 'quiet-garden');
INSERT INTO florist_sample_decorations (sample_id, decoration_id)
SELECT s.id, d.id FROM florist_samples s JOIN decorations d ON d.slug IN ('satin-ribbon', 'message-card') WHERE s.slug IN ('velvet-rose-study', 'quiet-garden');

INSERT INTO florist_sample_images (sample_id, media_id, is_cover, display_order)
SELECT s.id, s.cover_image_id, 1, 10 FROM florist_samples s;

INSERT INTO cafe_categories (name, slug, description, display_order) VALUES
('Coffee', 'coffee', 'Espresso-led classics and house signatures.', 10),
('Tea & Botanical', 'tea-botanical', 'Tea, florals and refreshing infusions.', 20),
('Bakes', 'bakes', 'Small-batch treats for the café table.', 30);

INSERT INTO cafe_products (category_id, name, slug, description, regular_price, promotional_price, dietary_labels, is_featured, display_order)
SELECT id, 'Rose Latte', 'rose-latte', 'Espresso, fresh milk and a restrained rose finish.', 6.50, NULL, JSON_ARRAY('Contains dairy'), 1, 10 FROM cafe_categories WHERE slug = 'coffee';
INSERT INTO cafe_products (category_id, name, slug, description, regular_price, promotional_price, dietary_labels, is_featured, display_order)
SELECT id, 'Botanical Iced Tea', 'botanical-iced-tea', 'A fragrant chilled infusion with citrus and garden herbs.', 5.50, 4.80, JSON_ARRAY('Vegan'), 1, 10 FROM cafe_categories WHERE slug = 'tea-botanical';
INSERT INTO cafe_products (category_id, name, slug, description, regular_price, promotional_price, dietary_labels, is_featured, display_order)
SELECT id, 'Pistachio Financier', 'pistachio-financier', 'A tender almond cake with pistachio and browned butter.', 4.20, NULL, JSON_ARRAY('Contains nuts', 'Contains dairy', 'Contains egg'), 0, 10 FROM cafe_categories WHERE slug = 'bakes';

INSERT INTO cafe_product_options (product_id, option_group, name, price_adjustment, is_default, display_order)
SELECT id, 'size', 'Regular', 0, 1, 10 FROM cafe_products WHERE slug IN ('rose-latte', 'botanical-iced-tea');
INSERT INTO cafe_product_options (product_id, option_group, name, price_adjustment, is_default, display_order)
SELECT id, 'size', 'Large', 1, 0, 20 FROM cafe_products WHERE slug IN ('rose-latte', 'botanical-iced-tea');
INSERT INTO cafe_product_options (product_id, option_group, name, price_adjustment, is_default, display_order)
SELECT id, 'temperature', 'Hot', 0, 1, 10 FROM cafe_products WHERE slug = 'rose-latte';
INSERT INTO cafe_product_options (product_id, option_group, name, price_adjustment, is_default, display_order)
SELECT id, 'temperature', 'Iced', 0.50, 0, 20 FROM cafe_products WHERE slug = 'rose-latte';

INSERT INTO policies (title, slug, body, is_active, published_at) VALUES
('Cancellation Policy', 'cancellation', 'Please contact CK Florist as soon as possible. Cancellations and changes are reviewed according to preparation status and perishable materials already purchased.', 1, NOW()),
('Terms of Enquiry', 'terms', 'Submitting an enquiry does not confirm an order. Availability, final design, fulfilment details and price must be confirmed by CK Florist through WhatsApp.', 1, NOW()),
('Privacy Policy', 'privacy', 'We use the information submitted to respond to your enquiry and coordinate fulfilment. We do not sell personal information.', 1, NOW());

INSERT INTO gallery_items (media_id, title, caption, display_order)
SELECT id, 'Velvet Rose Study', 'A deep rose palette for a romantic brief.', 10 FROM media WHERE path = 'public/assets/images/rose-bouquet-900.webp';
INSERT INTO gallery_items (media_id, title, caption, display_order)
SELECT id, 'Quiet Garden', 'Soft seasonal stems with sculptural movement.', 20 FROM media WHERE path = 'public/assets/images/pastel-bouquet-900.webp';
INSERT INTO gallery_items (media_id, title, caption, display_order)
SELECT id, 'At the café table', 'Botanical drinks and small-batch bakes.', 30 FROM media WHERE path = 'public/assets/images/cafe-900.webp';
