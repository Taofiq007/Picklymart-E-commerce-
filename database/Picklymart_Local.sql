-- ============================================================================
-- Picklymart Local Catalog + Media Mapping Database
-- Target: MySQL 8.0+ (or a compatible recent MariaDB version)
-- Generated from the Picklymart Laravel starter schema discussed in this chat.
--
-- IMPORTANT:
-- 1) This is a standalone SQL installation for a CLEAN local database.
-- 2) The DROP statements below erase existing rows; import only into a
--    disposable database.
-- 3) Migration history is included, so `php artisan migrate` is safe after
--    import and will report no pending migrations.
-- 4) The catalog rows at the end are fictional DEMO rows, not real Picklymart data.
-- 5) This script does not create an admin login or set any passwords.
-- 6) The media path is metadata only; copy the sample image to the matching
--    configured Laravel storage disk if you want the demo image URL to resolve.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `picklymart_local`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `picklymart_local`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Drop in dependency-safe order to make this script reproducible on a local DB.
-- WARNING: the DROP statements erase existing data in these tables. Use only for
-- a fresh/disposable local database. Remove this block if you need to preserve data.
DROP TABLE IF EXISTS `category_media`;
DROP TABLE IF EXISTS `product_media`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `media_assets`;
DROP TABLE IF EXISTS `brands`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `personal_access_tokens`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `migrations`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- Laravel supporting tables (authentication, sessions, cache and queue)
-- ============================================================================

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `remember_token` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_is_admin_index` (`is_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `personal_access_tokens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` VARCHAR(255) NOT NULL,
  `tokenable_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `abilities` TEXT DEFAULT NULL,
  `last_used_at` TIMESTAMP NULL DEFAULT NULL,
  `expires_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`, `tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED DEFAULT NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT DEFAULT NULL,
  `cancelled_at` INT DEFAULT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Picklymart catalog and central media repository mapping tables
-- ============================================================================

CREATE TABLE `categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_id` VARCHAR(191) DEFAULT NULL,
  `parent_id` BIGINT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(180) NOT NULL,
  `slug` VARCHAR(191) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_source_id_unique` (`source_id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_status_index` (`status`),
  KEY `categories_parent_id_status_index` (`parent_id`, `status`),
  CONSTRAINT `categories_parent_id_foreign`
    FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `brands` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_id` VARCHAR(191) DEFAULT NULL,
  `name` VARCHAR(180) NOT NULL,
  `slug` VARCHAR(191) NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_source_id_unique` (`source_id`),
  UNIQUE KEY `brands_slug_unique` (`slug`),
  KEY `brands_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_id` VARCHAR(191) DEFAULT NULL,
  `sku` VARCHAR(100) DEFAULT NULL,
  `slug` VARCHAR(191) NOT NULL,
  `name` VARCHAR(220) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(12,2) DEFAULT NULL,
  `sale_price` DECIMAL(12,2) DEFAULT NULL,
  `currency` CHAR(3) NOT NULL DEFAULT 'BDT',
  `category_id` BIGINT UNSIGNED DEFAULT NULL,
  `brand_id` BIGINT UNSIGNED DEFAULT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_source_id_unique` (`source_id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_status_index` (`status`),
  KEY `products_category_id_status_index` (`category_id`, `status`),
  KEY `products_brand_id_status_index` (`brand_id`, `status`),
  KEY `products_name_index` (`name`),
  CONSTRAINT `products_category_id_foreign`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_brand_id_foreign`
    FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `media_assets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_id` VARCHAR(191) DEFAULT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) DEFAULT NULL,
  `disk` VARCHAR(80) NOT NULL DEFAULT 'public',
  `path` VARCHAR(255) NOT NULL,
  `mime_type` VARCHAR(120) DEFAULT NULL,
  `size_bytes` BIGINT UNSIGNED DEFAULT NULL,
  `checksum` CHAR(64) DEFAULT NULL,
  `width` INT UNSIGNED DEFAULT NULL,
  `height` INT UNSIGNED DEFAULT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_assets_source_id_unique` (`source_id`),
  UNIQUE KEY `media_assets_disk_path_unique` (`disk`, `path`),
  KEY `media_assets_mime_type_index` (`mime_type`),
  KEY `media_assets_checksum_index` (`checksum`),
  KEY `media_assets_status_index` (`status`),
  KEY `media_assets_disk_status_index` (`disk`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_media` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `media_asset_id` BIGINT UNSIGNED NOT NULL,
  `role` VARCHAR(30) NOT NULL DEFAULT 'gallery',
  `position` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_media_product_asset_unique` (`product_id`, `media_asset_id`),
  KEY `product_media_product_role_position_index` (`product_id`, `role`, `position`),
  CONSTRAINT `product_media_product_id_foreign`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_media_media_asset_id_foreign`
    FOREIGN KEY (`media_asset_id`) REFERENCES `media_assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `category_media` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `media_asset_id` BIGINT UNSIGNED NOT NULL,
  `role` VARCHAR(30) NOT NULL DEFAULT 'banner',
  `position` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_media_category_asset_unique` (`category_id`, `media_asset_id`),
  KEY `category_media_category_role_position_index` (`category_id`, `role`, `position`),
  CONSTRAINT `category_media_category_id_foreign`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `category_media_media_asset_id_foreign`
    FOREIGN KEY (`media_asset_id`) REFERENCES `media_assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`) VALUES
  ('2026_10_17_000000_create_framework_support_tables', 1),
  ('2026_10_17_000001_create_categories_table', 1),
  ('2026_10_17_000002_create_brands_table', 1),
  ('2026_10_17_000003_create_products_table', 1),
  ('2026_10_17_000004_create_media_assets_table', 1),
  ('2026_10_17_000005_create_product_media_table', 1),
  ('2026_10_17_000006_create_category_media_table', 1),
  ('2026_10_19_000001_add_admin_access_to_users', 2);

-- ============================================================================
-- OPTIONAL fictional demonstration data (safe to delete in a local sandbox)
-- These IDs are local IDs; source_id holds the example external identifier.
-- ============================================================================

INSERT INTO `categories`
  (`id`, `source_id`, `parent_id`, `name`, `slug`, `description`, `status`, `created_at`, `updated_at`)
VALUES
  (1, 'cat-electronics', NULL, 'Electronics', 'electronics', NULL, 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  (2, 'cat-phones', 1, 'Mobile Phones', 'mobile-phones', 'Phones and accessories', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  (3, 'cat-home', NULL, 'Home and Living', 'home-and-living', NULL, 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO `brands`
  (`id`, `source_id`, `name`, `slug`, `status`, `created_at`, `updated_at`)
VALUES
  (1, 'brand-demo', 'Demo Brand', 'demo-brand', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO `products`
  (`id`, `source_id`, `sku`, `slug`, `name`, `description`, `price`, `sale_price`, `currency`, `category_id`, `brand_id`, `status`, `created_at`, `updated_at`)
VALUES
  (1, 'prod-demo-001', 'DEMO-001', 'demo-phone', 'Demo Smartphone', 'Fictional example product; replace with approved Picklymart catalog data.', 12000.00, 10999.00, 'BDT', 2, 1, 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO `media_assets`
  (`id`, `source_id`, `filename`, `original_name`, `disk`, `path`, `mime_type`, `size_bytes`, `checksum`, `width`, `height`, `status`, `created_at`, `updated_at`)
VALUES
  (1, 'asset-demo-001', 'phone-main.png', 'phone-main.png', 'public', 'media/products/DEMO-001/phone-main.png', 'image/png', NULL, NULL, NULL, NULL, 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO `product_media`
  (`id`, `product_id`, `media_asset_id`, `role`, `position`, `created_at`, `updated_at`)
VALUES
  (1, 1, 1, 'main', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

-- No demo user is inserted: create a local user through Laravel so the password
-- is securely hashed, then issue a Sanctum token if API bearer auth is required.
