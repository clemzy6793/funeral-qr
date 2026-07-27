-- EventQR Platform — Full Schema
-- Migration 001: Initial multi-tenant schema (PostgreSQL)

CREATE TABLE IF NOT EXISTS tenants (
    id SERIAL PRIMARY KEY,
    uuid CHAR(36) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    account_type VARCHAR(50) DEFAULT 'individual',
    contact_person VARCHAR(255),
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(30),
    country VARCHAR(100) DEFAULT 'Ghana',
    logo VARCHAR(255),
    favicon VARCHAR(255),
    brand_color_primary VARCHAR(7) DEFAULT '#212529',
    brand_color_secondary VARCHAR(7) DEFAULT '#6c757d',
    business_name VARCHAR(255),
    footer_text TEXT,
    custom_domain VARCHAR(255),
    status VARCHAR(20) DEFAULT 'pending',
    storage_used BIGINT DEFAULT 0,
    referral_code VARCHAR(20) UNIQUE,
    referred_by INT,
    setup_completed SMALLINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    tenant_id INT REFERENCES tenants(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(30),
    role VARCHAR(30) DEFAULT 'tenant_user',
    email_verified_at TIMESTAMP,
    verification_token VARCHAR(64),
    reset_token VARCHAR(64),
    reset_expires_at TIMESTAMP,
    is_active SMALLINT DEFAULT 1,
    last_login_at TIMESTAMP,
    impersonated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS plans (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    price_monthly DECIMAL(10,2) DEFAULT 0,
    price_quarterly DECIMAL(10,2) DEFAULT 0,
    price_yearly DECIMAL(10,2) DEFAULT 0,
    price_lifetime DECIMAL(10,2) DEFAULT 0,
    trial_days INT DEFAULT 14,
    storage_limit_mb INT DEFAULT 100,
    event_limit INT DEFAULT 5,
    user_limit INT DEFAULT 1,
    qr_code_limit INT DEFAULT 10,
    monthly_scan_limit INT DEFAULT 1000,
    max_file_size_mb INT DEFAULT 10,
    api_access SMALLINT DEFAULT 0,
    white_label SMALLINT DEFAULT 0,
    analytics SMALLINT DEFAULT 0,
    custom_domain SMALLINT DEFAULT 0,
    is_active SMALLINT DEFAULT 1,
    is_default SMALLINT DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS subscriptions (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    plan_id INT NOT NULL REFERENCES plans(id),
    billing_cycle VARCHAR(20) DEFAULT 'monthly',
    status VARCHAR(20) DEFAULT 'trial',
    trial_ends_at TIMESTAMP,
    starts_at TIMESTAMP,
    ends_at TIMESTAMP,
    grace_ends_at TIMESTAMP,
    auto_renew SMALLINT DEFAULT 1,
    cancelled_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS event_types (
    id SERIAL PRIMARY KEY,
    tenant_id INT REFERENCES tenants(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    description TEXT,
    default_fields JSONB,
    is_system SMALLINT DEFAULT 0,
    is_active SMALLINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (tenant_id, slug)
);
CREATE UNIQUE INDEX IF NOT EXISTS idx_event_types_system_slug ON event_types (slug) WHERE tenant_id IS NULL;

CREATE TABLE IF NOT EXISTS events (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    event_type_id INT NOT NULL REFERENCES event_types(id),
    slug VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    organizer VARCHAR(255),
    contact_person VARCHAR(255),
    phone VARCHAR(30),
    whatsapp VARCHAR(30),
    email VARCHAR(255),
    website VARCHAR(500),
    venue VARCHAR(500),
    digital_address VARCHAR(50),
    google_maps_url VARCHAR(500),
    gps_lat DECIMAL(10,7),
    gps_lng DECIMAL(10,7),
    city VARCHAR(100),
    region VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Ghana',
    start_date DATE,
    end_date DATE,
    start_time TIME,
    end_time TIME,
    dynamic_fields JSONB,
    cover_image VARCHAR(255),
    banner_image VARCHAR(255),
    payment_required SMALLINT DEFAULT 0,
    payment_type VARCHAR(30) DEFAULT 'free',
    payment_amount DECIMAL(10,2),
    qr_destination VARCHAR(30) DEFAULT 'event_page',
    qr_custom_url VARCHAR(500),
    status VARCHAR(20) DEFAULT 'draft',
    total_scans INT DEFAULT 0,
    legacy_brochure_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (tenant_id, slug)
);

CREATE TABLE IF NOT EXISTS qr_codes (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    event_id INT NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    code CHAR(36) UNIQUE NOT NULL,
    filename VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    file_path VARCHAR(500),
    format VARCHAR(10) DEFAULT 'png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS media (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    event_id INT REFERENCES events(id) ON DELETE SET NULL,
    type VARCHAR(30) DEFAULT 'other',
    filename VARCHAR(255),
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100),
    file_size BIGINT DEFAULT 0,
    file_path VARCHAR(500) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS qr_scans (
    id BIGSERIAL PRIMARY KEY,
    tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    event_id INT NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    qr_code_id INT NOT NULL REFERENCES qr_codes(id) ON DELETE CASCADE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    device VARCHAR(50),
    browser VARCHAR(50),
    os VARCHAR(50),
    country VARCHAR(100),
    city VARCHAR(100),
    referrer VARCHAR(500),
    scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_scans_event ON qr_scans (event_id, scanned_at);
CREATE INDEX IF NOT EXISTS idx_scans_tenant ON qr_scans (tenant_id, scanned_at);

CREATE TABLE IF NOT EXISTS payment_providers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    class_name VARCHAR(100) NOT NULL,
    credentials JSONB,
    is_active SMALLINT DEFAULT 0,
    is_default SMALLINT DEFAULT 0,
    supports_subscription SMALLINT DEFAULT 1,
    supports_event_payment SMALLINT DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tenant_payment_providers (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    provider_id INT NOT NULL REFERENCES payment_providers(id),
    credentials JSONB,
    is_active SMALLINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (tenant_id, provider_id)
);

CREATE TABLE IF NOT EXISTS platform_payments (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    subscription_id INT REFERENCES subscriptions(id),
    provider_id INT,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'GHS',
    reference VARCHAR(100) UNIQUE,
    provider_reference VARCHAR(255),
    status VARCHAR(20) DEFAULT 'pending',
    billing_cycle VARCHAR(20),
    coupon_id INT,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    metadata JSONB,
    paid_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS event_payments (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    event_id INT NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    provider_id INT,
    visitor_name VARCHAR(255),
    visitor_email VARCHAR(255),
    visitor_phone VARCHAR(30),
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'GHS',
    reference VARCHAR(100) UNIQUE,
    provider_reference VARCHAR(255),
    payment_type VARCHAR(30),
    status VARCHAR(20) DEFAULT 'pending',
    commission_amount DECIMAL(10,2) DEFAULT 0,
    receipt_number VARCHAR(50),
    metadata JSONB,
    paid_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS invoices (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    type VARCHAR(20) DEFAULT 'subscription',
    amount DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'GHS',
    status VARCHAR(20) DEFAULT 'draft',
    due_date DATE,
    paid_at TIMESTAMP,
    items JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS coupons (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    type VARCHAR(20) DEFAULT 'percentage',
    value DECIMAL(10,2) NOT NULL,
    max_uses INT,
    used_count INT DEFAULT 0,
    plan_ids JSONB,
    country_restrictions JSONB,
    expires_at TIMESTAMP,
    is_active SMALLINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS referrals (
    id SERIAL PRIMARY KEY,
    referrer_tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    referred_tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    status VARCHAR(20) DEFAULT 'pending',
    reward_type VARCHAR(20),
    reward_value DECIMAL(10,2),
    reward_applied SMALLINT DEFAULT 0,
    converted_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS support_tickets (
    id SERIAL PRIMARY KEY,
    tenant_id INT REFERENCES tenants(id) ON DELETE CASCADE,
    user_id INT REFERENCES users(id) ON DELETE SET NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'open',
    priority VARCHAR(10) DEFAULT 'normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS support_replies (
    id SERIAL PRIMARY KEY,
    ticket_id INT NOT NULL REFERENCES support_tickets(id) ON DELETE CASCADE,
    user_id INT REFERENCES users(id) ON DELETE SET NULL,
    message TEXT NOT NULL,
    is_admin SMALLINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notifications (
    id SERIAL PRIMARY KEY,
    tenant_id INT REFERENCES tenants(id) ON DELETE CASCADE,
    user_id INT REFERENCES users(id) ON DELETE SET NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    type VARCHAR(10) DEFAULT 'info',
    is_read SMALLINT DEFAULT 0,
    is_broadcast SMALLINT DEFAULT 0,
    link VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_notif_user ON notifications (user_id, is_read, created_at);

CREATE TABLE IF NOT EXISTS platform_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS api_keys (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    api_key VARCHAR(64) UNIQUE NOT NULL,
    secret_hash VARCHAR(255) NOT NULL,
    permissions JSONB,
    rate_limit INT DEFAULT 1000,
    last_used_at TIMESTAMP,
    expires_at TIMESTAMP,
    is_active SMALLINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS activity_log (
    id BIGSERIAL PRIMARY KEY,
    tenant_id INT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    metadata JSONB,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_activity_tenant ON activity_log (tenant_id, created_at);
CREATE INDEX IF NOT EXISTS idx_activity_user ON activity_log (user_id, created_at);

CREATE TABLE IF NOT EXISTS commission_settings (
    id SERIAL PRIMARY KEY,
    type VARCHAR(20) DEFAULT 'none',
    value DECIMAL(10,2) DEFAULT 0,
    plan_id INT REFERENCES plans(id) ON DELETE CASCADE,
    provider_id INT REFERENCES payment_providers(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS faqs (
    id SERIAL PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100),
    sort_order INT DEFAULT 0,
    is_active SMALLINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
