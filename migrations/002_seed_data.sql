-- EventQR Platform — Seed Data (PostgreSQL)
-- Migration 002: Default plans, event types, payment providers, settings

-- Default Plans
INSERT INTO plans (name, slug, description, price_monthly, price_quarterly, price_yearly, price_lifetime, trial_days, storage_limit_mb, event_limit, user_limit, qr_code_limit, monthly_scan_limit, max_file_size_mb, api_access, white_label, analytics, custom_domain, is_active, is_default, sort_order) VALUES
('Free', 'free', 'Get started with basic features', 0, 0, 0, 0, 0, 50, 3, 1, 5, 500, 5, 0, 0, 0, 0, 1, 1, 1),
('Starter', 'starter', 'For individuals and small businesses', 29.99, 79.99, 299.99, 0, 14, 500, 15, 3, 30, 5000, 20, 0, 0, 1, 0, 1, 0, 2),
('Professional', 'professional', 'For growing businesses and organizations', 79.99, 209.99, 799.99, 0, 14, 2000, 50, 10, 100, 25000, 50, 1, 0, 1, 1, 1, 0, 3),
('Enterprise', 'enterprise', 'For large organizations with custom needs', 199.99, 529.99, 1999.99, 4999.99, 30, 10000, -1, -1, -1, -1, 100, 1, 1, 1, 1, 1, 0, 4)
ON CONFLICT (slug) DO NOTHING;

-- System Event Types
INSERT INTO event_types (tenant_id, name, slug, icon, description, default_fields, is_system, is_active) VALUES
(NULL, 'Funeral', 'funeral', 'bi-flower1', 'Funeral and memorial services', '{"deceased_name":"text","funeral_date":"date","burial_location":"text","family_contact":"text","obituary":"textarea","order_of_service":"textarea"}', 1, 1),
(NULL, 'Wedding', 'wedding', 'bi-heart', 'Wedding ceremonies and receptions', '{"bride":"text","groom":"text","reception_venue":"text","theme":"text","dress_code":"text","registry_url":"url"}', 1, 1),
(NULL, 'Engagement', 'engagement', 'bi-ring', 'Engagement parties and announcements', '{"partner_1":"text","partner_2":"text","theme":"text"}', 1, 1),
(NULL, 'Birthday', 'birthday', 'bi-cake2', 'Birthday celebrations', '{"celebrant":"text","age":"number","theme":"text","dress_code":"text"}', 1, 1),
(NULL, 'Naming Ceremony', 'naming-ceremony', 'bi-baby', 'Baby naming ceremonies', '{"child_name":"text","parents":"text","godparents":"text"}', 1, 1),
(NULL, 'Graduation', 'graduation', 'bi-mortarboard', 'Graduation ceremonies', '{"institution":"text","degree":"text","graduating_class":"text"}', 1, 1),
(NULL, 'Church Service', 'church-service', 'bi-church', 'Church services and programs', '{"speaker":"text","theme":"text","bible_verse":"text","offering_details":"textarea"}', 1, 1),
(NULL, 'Conference', 'conference', 'bi-mic', 'Conferences and summits', '{"speakers":"textarea","sponsors":"textarea","agenda":"textarea","registration_url":"url"}', 1, 1),
(NULL, 'Seminar', 'seminar', 'bi-easel3', 'Seminars and educational events', '{"facilitator":"text","topics":"textarea","materials_url":"url"}', 1, 1),
(NULL, 'Workshop', 'workshop', 'bi-tools', 'Hands-on workshops', '{"instructor":"text","prerequisites":"textarea","materials_needed":"textarea"}', 1, 1),
(NULL, 'Anniversary', 'anniversary', 'bi-calendar-heart', 'Anniversary celebrations', '{"couple":"text","years":"number","theme":"text"}', 1, 1),
(NULL, 'Memorial', 'memorial', 'bi-bookmark-star', 'Memorial and remembrance services', '{"honoree":"text","tribute":"textarea"}', 1, 1),
(NULL, 'Corporate Event', 'corporate-event', 'bi-building', 'Corporate meetings and launches', '{"company":"text","department":"text","agenda":"textarea"}', 1, 1),
(NULL, 'School Event', 'school-event', 'bi-backpack', 'School functions and ceremonies', '{"school":"text","academic_year":"text","program":"textarea"}', 1, 1),
(NULL, 'Festival', 'festival', 'bi-music-note-beamed', 'Festivals and cultural events', '{"performers":"textarea","schedule":"textarea","ticket_info":"textarea"}', 1, 1),
(NULL, 'Exhibition', 'exhibition', 'bi-easel', 'Exhibitions and showcases', '{"exhibitors":"textarea","categories":"textarea"}', 1, 1),
(NULL, 'Sports Event', 'sports-event', 'bi-trophy', 'Sports events and tournaments', '{"sport":"text","teams":"textarea","schedule":"textarea"}', 1, 1),
(NULL, 'Party', 'party', 'bi-balloon', 'General parties and social gatherings', '{"host":"text","theme":"text","dress_code":"text"}', 1, 1),
(NULL, 'Other', 'other', 'bi-calendar-event', 'Custom event type', '{}', 1, 1)
ON CONFLICT DO NOTHING;

-- Payment Providers
INSERT INTO payment_providers (name, slug, class_name, is_active, supports_subscription, supports_event_payment, sort_order) VALUES
('Paystack', 'paystack', 'PaystackProvider', 0, 1, 1, 1),
('Hubtel', 'hubtel', 'HubtelProvider', 0, 1, 1, 2),
('Flutterwave', 'flutterwave', 'FlutterwaveProvider', 0, 1, 1, 3),
('Stripe', 'stripe', 'StripeProvider', 0, 1, 1, 4),
('PayPal', 'paypal', 'PayPalProvider', 0, 1, 1, 5),
('MTN Mobile Money', 'mtn-momo', 'MomoProvider', 0, 0, 1, 6),
('Telecel Cash', 'telecel-cash', 'MomoProvider', 0, 0, 1, 7),
('AirtelTigo Money', 'airteltigo', 'MomoProvider', 0, 0, 1, 8),
('Bank Transfer', 'bank-transfer', 'ManualProvider', 0, 1, 1, 9),
('Manual Payment', 'manual', 'ManualProvider', 0, 1, 1, 10)
ON CONFLICT (slug) DO NOTHING;

-- Platform Settings
INSERT INTO platform_settings (setting_key, setting_value) VALUES
('platform_name', 'EventQR'),
('platform_url', ''),
('platform_email', ''),
('smtp_host', ''),
('smtp_port', '587'),
('smtp_user', ''),
('smtp_pass', ''),
('smtp_from_name', 'EventQR'),
('smtp_encryption', 'tls'),
('maintenance_mode', '0'),
('commission_type', 'none'),
('commission_value', '0'),
('default_currency', 'GHS'),
('referral_enabled', '1'),
('referral_reward_type', 'free_days'),
('referral_reward_value', '30'),
('registration_enabled', '1')
ON CONFLICT (setting_key) DO NOTHING;
