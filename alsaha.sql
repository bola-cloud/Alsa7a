-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 21, 2025 at 02:37 AM
-- Server version: 8.0.36-28
-- PHP Version: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alsaha`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `seats` int NOT NULL DEFAULT '1',
  `price_paid` decimal(10,2) DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `description_en` text COLLATE utf8mb4_unicode_ci,
  `description_ar` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`, `name_en`, `name_ar`, `description`, `description_en`, `description_ar`, `created_at`, `updated_at`) VALUES
(1, 'Player', 'images-demo/player_category.jpg', 'Player', 'لاعب', 'Players (amateur/professional)', 'Players (amateur/professional)', 'اللاعبون (هواة/محترفون)', '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(2, 'Coach', NULL, 'Coach', 'مدرب', 'Coaches and trainers', 'Coaches and trainers', 'المدربون والمدربات', '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(3, 'Club', NULL, 'Club', 'نادي', 'Clubs and academies', 'Clubs and academies', 'الأندية والأكاديميات', '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(4, 'Photographer', NULL, 'Photographer', 'مصور', 'Event photographers and videographers', 'Event photographers and videographers', 'مصورو الفعاليات والفيديوغرافيون', '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(5, 'Physiotherapist', NULL, 'Physiotherapist', 'أخصائي علاج طبيعي', 'Sports therapists and physios', 'Sports therapists and physios', 'أخصائيو العلاج الطبيعي والفيزيوثيرابيين', '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(6, 'Parent', NULL, 'Parent', 'ولي أمر', 'Parents of players', 'Parents of players', 'أولياء أمور اللاعبين', '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(7, 'Fan', NULL, 'Fan', 'مشجع', 'Fans and supporters', 'Fans and supporters', 'المشجعون والمؤيدون', '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(8, 'Agent', NULL, 'Agent', 'وكيل', 'Player agents and scouts', 'Player agents and scouts', 'وكلاء اللاعبين والكشافون', '2025-11-23 01:23:44', '2025-11-23 01:23:44');

-- --------------------------------------------------------

--
-- Table structure for table `clubs`
--

CREATE TABLE `clubs` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `description_en` text COLLATE utf8mb4_unicode_ci,
  `description_ar` text COLLATE utf8mb4_unicode_ci,
  `logo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `founded_year` year DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clubs`
--

INSERT INTO `clubs` (`id`, `name`, `name_en`, `name_ar`, `slug`, `description`, `description_en`, `description_ar`, `logo_url`, `banner_url`, `city`, `country`, `founded_year`, `website`, `rating`, `is_featured`, `meta`, `created_at`, `updated_at`) VALUES
(1, 'Madreed Club', 'Madreed Club', 'نادي مدريد', 'madreed-club', NULL, NULL, NULL, 'images-demo/club1.png', NULL, 'Madrid', 'Spain', NULL, NULL, NULL, 1, '[]', '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(2, 'Barcelona Club', 'Barcelona Club', 'نادي برشلونة', 'barcelona-club', NULL, NULL, NULL, 'images-demo/club2.jpg', NULL, 'Barcelona', 'Spain', NULL, NULL, NULL, 1, '[]', '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(3, 'Al-Ahly Club', 'Al-Ahly Club', 'النادي الاهلي ', 'al-ahly-club', NULL, NULL, NULL, 'images-demo/club3.jpg', NULL, 'Cairo', 'Egypt', NULL, NULL, NULL, 0, '[]', '2025-11-23 01:23:44', '2025-11-23 01:23:44');

-- --------------------------------------------------------

--
-- Table structure for table `club_sport`
--

CREATE TABLE `club_sport` (
  `id` bigint UNSIGNED NOT NULL,
  `club_id` bigint UNSIGNED NOT NULL,
  `sport_id` bigint UNSIGNED NOT NULL,
  `meta` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `club_sport`
--

INSERT INTO `club_sport` (`id`, `club_id`, `sport_id`, `meta`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 1, NULL, NULL),
(2, 1, 2, NULL, 1, NULL, NULL),
(3, 2, 1, NULL, 1, NULL, NULL),
(4, 3, 1, NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` bigint UNSIGNED NOT NULL,
  `club_id` bigint UNSIGNED DEFAULT NULL,
  `sport_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `description_en` text COLLATE utf8mb4_unicode_ci,
  `description_ar` text COLLATE utf8mb4_unicode_ci,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `venue` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `capacity` int DEFAULT NULL,
  `tickets_sold` int NOT NULL DEFAULT '0',
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `club_id`, `sport_id`, `title`, `title_en`, `title_ar`, `slug`, `description`, `description_en`, `description_ar`, `start_at`, `end_at`, `venue`, `price`, `capacity`, `tickets_sold`, `featured_image`, `is_featured`, `meta`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Madreed vs Al-Nasr', 'Madreed vs Al-Nasr', 'مدريد ضد النصر', 'madreed-vs-al-nasr', 'Championship match', 'Championship match', 'مباراة البطولة', '2025-11-30 01:23:44', NULL, 'Main Stadium', 25.00, NULL, 0, 'images-demo/events.png', 1, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(2, 3, 1, 'Al-Mussanah Friendly', 'Al-Mussanah Friendly', 'ودية الموسنة', 'al-mussanah-friendly', 'Friendly match', 'Friendly match', 'مباراة ودية', '2025-11-26 01:23:44', NULL, 'City Arena', 10.00, NULL, 0, 'images-demo/events.png', 1, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leagues`
--

CREATE TABLE `leagues` (
  `id` bigint UNSIGNED NOT NULL,
  `sport_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `description_en` text COLLATE utf8mb4_unicode_ci,
  `description_ar` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `season` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leagues`
--

INSERT INTO `leagues` (`id`, `sport_id`, `name`, `name_en`, `name_ar`, `slug`, `description`, `description_en`, `description_ar`, `image`, `season`, `start_date`, `end_date`, `is_active`, `meta`, `created_at`, `updated_at`) VALUES
(1, 1, 'Champion League', 'Champion League', 'دوري الأبطال', 'champion-league', NULL, NULL, NULL, 'images-demo/popular-leagues2.png', '2025/2026', NULL, NULL, 1, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(2, 2, 'Basket League', 'Basket League', 'دوري السلة', 'basket-league', NULL, NULL, NULL, 'images-demo/popular-leagues.png', '2025', NULL, NULL, 1, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44');

-- --------------------------------------------------------

--
-- Table structure for table `league_team`
--

CREATE TABLE `league_team` (
  `id` bigint UNSIGNED NOT NULL,
  `league_id` bigint UNSIGNED NOT NULL,
  `team_id` bigint UNSIGNED NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seed` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `league_team`
--

INSERT INTO `league_team` (`id`, `league_id`, `team_id`, `group`, `seed`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL, 1, NULL, NULL),
(2, 1, 2, NULL, NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `league_videos`
--

CREATE TABLE `league_videos` (
  `id` bigint UNSIGNED NOT NULL,
  `league_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_seconds` int DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `league_videos`
--

INSERT INTO `league_videos` (`id`, `league_id`, `title`, `url`, `duration_seconds`, `meta`, `created_at`, `updated_at`) VALUES
(1, 1, 'Al-Nasr vs Madreed Highlights', 'https://placehold.co/640x360/video.mp4', 262, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(2, 1, 'Top Goals', 'https://placehold.co/640x360/video2.mp4', 120, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` bigint UNSIGNED NOT NULL,
  `mediaable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mediaable_id` bigint UNSIGNED NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'image',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_seconds` int DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `mediaable_type`, `mediaable_id`, `url`, `type`, `title`, `duration_seconds`, `meta`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\Club', 1, 'https://placehold.co/600x300', 'image', 'Club banner', NULL, NULL, 0, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(2, 'App\\Models\\Club', 2, 'https://placehold.co/600x300', 'image', 'Club banner', NULL, NULL, 0, '2025-11-23 01:23:44', '2025-11-23 01:23:44');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(75, '2012_11_04_000001_create_sports_table', 1),
(76, '2013_10_23_000001_create_categories_table', 1),
(77, '2013_11_04_000002_create_clubs_table', 1),
(78, '2013_11_04_000004_create_teams_table', 1),
(79, '2014_10_12_000000_create_users_table', 1),
(80, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(81, '2014_10_12_200000_add_two_factor_columns_to_users_table', 1),
(82, '2019_08_19_000000_create_failed_jobs_table', 1),
(83, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(84, '2024_10_09_123456_create_sessions_table', 1),
(85, '2025_10_23_000002_create_questions_table', 1),
(86, '2025_11_04_000003_create_club_sport_table', 1),
(87, '2025_11_04_000005_create_leagues_table', 1),
(88, '2025_11_04_000006_create_league_team_table', 1),
(89, '2025_11_04_000007_create_league_videos_table', 1),
(90, '2025_11_04_000008_create_players_table', 1),
(91, '2025_11_04_000009_create_events_table', 1),
(92, '2025_11_04_000010_create_bookings_table', 1),
(93, '2025_11_04_000011_create_media_table', 1),
(94, '2025_11_04_000012_create_services_table', 1),
(95, '2025_11_04_000013_create_service_media_table', 1),
(96, '2025_11_04_000014_create_service_requests_table', 1),
(97, '2025_11_04_000015_create_service_reviews_table', 1),
(98, '2025_11_22_000100_create_sliders_table', 1),
(99, '2025_11_22_000300_create_question_answers_table', 1),
(100, '2025_11_22_000400_add_localization_columns', 1),
(101, '2025_11_23_000500_add_localization_to_leagues', 1),
(102, '2025_12_02_000600_rename_categories_slug_to_image', 2),
(103, '2025_12_04_000001_add_image_to_leagues_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 3, 'api-token', 'd8d186a64abf14ae53a00b844fee81f5a3fa1f30d085fcb7b14aeb367107d00e', '[\"*\"]', NULL, NULL, '2025-12-01 17:28:17', '2025-12-01 17:28:17'),
(2, 'App\\Models\\User', 4, 'api-token', '018ff33ff282103ff3532071b60564a2ff7f8c425673b747473b105fdece6671', '[\"*\"]', NULL, NULL, '2025-12-01 17:58:52', '2025-12-01 17:58:52'),
(3, 'App\\Models\\User', 4, 'api-token', 'a2cc93ca660c0c00ba2315d085b2bc8abefcd3018bd61fb058db99712b75d742', '[\"*\"]', NULL, NULL, '2025-12-01 18:05:35', '2025-12-01 18:05:35'),
(4, 'App\\Models\\User', 5, 'api-token', '27a8475aa3afa44f49a872a9329f349620860a470c143b8b383f3e9ce66f5e69', '[\"*\"]', NULL, NULL, '2025-12-01 18:19:13', '2025-12-01 18:19:13'),
(5, 'App\\Models\\User', 6, 'api-token', '00e03f925dc2e0b9d0c737ba83087866da49eb8f675465f195671a669cb0d3e7', '[\"*\"]', NULL, NULL, '2025-12-01 18:28:24', '2025-12-01 18:28:24'),
(6, 'App\\Models\\User', 7, 'api-token', 'c489f01160de6cf15e9c0ccf45ee863055868ec4f00c8bb9c942fac8cb3b89b2', '[\"*\"]', NULL, NULL, '2025-12-02 00:10:19', '2025-12-02 00:10:19'),
(7, 'App\\Models\\User', 7, 'api-token', 'fdf9076aac600831b4244affd4be353d0b444197a08ee40e1fce27eefd7ec101', '[\"*\"]', NULL, NULL, '2025-12-02 00:11:03', '2025-12-02 00:11:03'),
(8, 'App\\Models\\User', 4, 'api-token', '001bdff39bd11e05b7c1a9e77ca6972748a1eab38b16a0b730316744e4bcaaa0', '[\"*\"]', NULL, NULL, '2025-12-02 14:54:42', '2025-12-02 14:54:42'),
(9, 'App\\Models\\User', 8, 'api-token', '76c21737b852e6dc7dc3cc329372ce2e1b9a86765ec1f1890effa73ad15d8c59', '[\"*\"]', NULL, NULL, '2025-12-02 15:19:37', '2025-12-02 15:19:37'),
(10, 'App\\Models\\User', 4, 'api-token', '3ee451b2530dce5cf6f80cf6e2f722a1cc8e301ae980be094e498e4c58ecc02f', '[\"*\"]', NULL, NULL, '2025-12-02 15:31:25', '2025-12-02 15:31:25'),
(11, 'App\\Models\\User', 4, 'api-token', '74b7065422fc6d59edb143780e5ede9ec9c98f8f4f357e870d806c181c0cc9d8', '[\"*\"]', '2025-12-02 15:50:07', NULL, '2025-12-02 15:42:09', '2025-12-02 15:50:07'),
(12, 'App\\Models\\User', 9, 'api-token', '80824e16e990700648d0269972376dad4e25126a22b1d12d5a1349781e82bf4f', '[\"*\"]', NULL, NULL, '2025-12-02 21:00:11', '2025-12-02 21:00:11'),
(13, 'App\\Models\\User', 10, 'api-token', '821416b823fb8b7089098cf0b19d30abeebe4b9a60d6dafead67f80e9785e31e', '[\"*\"]', NULL, NULL, '2025-12-02 21:00:55', '2025-12-02 21:00:55'),
(14, 'App\\Models\\User', 10, 'api-token', '6b89c4a52a6080d019deb78332f4e72669fad5bebaa4cc20f6f442536ed57b22', '[\"*\"]', NULL, NULL, '2025-12-02 21:01:30', '2025-12-02 21:01:30'),
(15, 'App\\Models\\User', 7, 'api-token', 'f318dcf94259cbc279a4553347eec98a7f8c3b3e7a318f6914a4e723f4b363a5', '[\"*\"]', NULL, NULL, '2025-12-02 21:01:55', '2025-12-02 21:01:55'),
(16, 'App\\Models\\User', 9, 'api-token', '422710f2e171bde65f41475fcb90df4c103744a02f1311ccee1789cb9ea63b7e', '[\"*\"]', NULL, NULL, '2025-12-02 21:03:12', '2025-12-02 21:03:12'),
(17, 'App\\Models\\User', 4, 'api-token', 'ad393a8b857e3365e6ea060a6ce2d51f8f3637f4fa3a4e5f803ab04c7d32e640', '[\"*\"]', '2025-12-02 22:49:37', NULL, '2025-12-02 22:46:17', '2025-12-02 22:49:37'),
(18, 'App\\Models\\User', 4, 'api-token', 'b55642865117a46d445650a83afa2ec8b0cf2fb59fe7b214a4d7db889d731cc3', '[\"*\"]', NULL, NULL, '2025-12-02 22:46:40', '2025-12-02 22:46:40'),
(19, 'App\\Models\\User', 4, 'api-token', 'ab7f7a3f964fcb262d189d4431cd260c0ee73e70705320490773ac24b138ed6b', '[\"*\"]', NULL, NULL, '2025-12-02 22:49:46', '2025-12-02 22:49:46'),
(20, 'App\\Models\\User', 4, 'api-token', '582cb09a61b8bd55d83e9a82d86d681960a5c08c41509667a850ac024366c7ea', '[\"*\"]', NULL, NULL, '2025-12-02 22:49:57', '2025-12-02 22:49:57'),
(21, 'App\\Models\\User', 4, 'api-token', 'd991c925220725ca526b9e78d6792e875a4bf9cd99fae3fdefe1c47ffc23f96c', '[\"*\"]', NULL, NULL, '2025-12-04 15:10:53', '2025-12-04 15:10:53'),
(22, 'App\\Models\\User', 4, 'api-token', '70879d04860b5fc3dd18b38f052602f20f5b401aa21e04233c0d914f978c1df2', '[\"*\"]', NULL, NULL, '2025-12-04 15:11:40', '2025-12-04 15:11:40'),
(23, 'App\\Models\\User', 4, 'api-token', '6e940c64d72ad00b88403754d1ed4d072b0be89bf3a68458905b31ad3b2b7e24', '[\"*\"]', NULL, NULL, '2025-12-04 15:15:41', '2025-12-04 15:15:41'),
(24, 'App\\Models\\User', 4, 'api-token', '657b0994f25f82ad8c0c7ec1f5f77ba7874435aff7868d2c1b6ed9b9ca67d667', '[\"*\"]', NULL, NULL, '2025-12-04 15:18:31', '2025-12-04 15:18:31'),
(25, 'App\\Models\\User', 4, 'api-token', 'db0d8169441391e82da5f4c3b7d91d9a38ff3b0d5a18eb4cdfe84f0575c2e60f', '[\"*\"]', NULL, NULL, '2025-12-04 15:22:40', '2025-12-04 15:22:40'),
(30, 'App\\Models\\User', 13, 'api-token', 'f2e06a0fd0665e78109beba2d6b90bd00a4be62459546a101dcd09617d990086', '[\"*\"]', '2025-12-04 19:07:41', NULL, '2025-12-04 18:53:53', '2025-12-04 19:07:41'),
(32, 'App\\Models\\User', 12, 'api-token', 'd1bea6481a39f01d81a2d209207498a7d44543deb2942a1fc766297c4242557a', '[\"*\"]', NULL, NULL, '2025-12-04 19:12:16', '2025-12-04 19:12:16'),
(33, 'App\\Models\\User', 12, 'api-token', '2a59c3564e4a2e689304de5ec47130797256f813ae842df6481731510fcab92f', '[\"*\"]', NULL, NULL, '2025-12-04 19:14:21', '2025-12-04 19:14:21'),
(37, 'App\\Models\\User', 4, 'api-token', '6d45f1fa8a0190f465bd4424fa560cc39c35ab3cd1068e45064c88814d4095e2', '[\"*\"]', NULL, NULL, '2025-12-04 19:36:04', '2025-12-04 19:36:04'),
(38, 'App\\Models\\User', 4, 'api-token', 'de93f52212b9d03bdc9971957cdb49e3e498bcea4b0c78a0e4db5d70b6cab07a', '[\"*\"]', NULL, NULL, '2025-12-04 19:40:47', '2025-12-04 19:40:47'),
(42, 'App\\Models\\User', 4, 'api-token', '634ff5617223e711af85c1bcfc30defe6d25d861739118ebd2d26fe72e0f5409', '[\"*\"]', NULL, NULL, '2025-12-05 02:36:06', '2025-12-05 02:36:06'),
(43, 'App\\Models\\User', 15, 'api-token', '4e6ce51f8be031558f4c01b419f9c24e088e8462fdd2497f03f233d66aa33351', '[\"*\"]', NULL, NULL, '2025-12-05 16:41:00', '2025-12-05 16:41:00'),
(44, 'App\\Models\\User', 15, 'api-token', '3db081e59c81838329f49afa7c16395ec302c1af5f9f35923f2cc1fc84b29077', '[\"*\"]', NULL, NULL, '2025-12-05 16:45:54', '2025-12-05 16:45:54');

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `id` bigint UNSIGNED NOT NULL,
  `team_id` bigint UNSIGNED DEFAULT NULL,
  `club_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` int DEFAULT NULL,
  `nationality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stats` json DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_en` text COLLATE utf8mb4_unicode_ci,
  `question_ar` text COLLATE utf8mb4_unicode_ci,
  `meta` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `choices` json DEFAULT NULL,
  `choices_en` json DEFAULT NULL,
  `choices_ar` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `category_id`, `question`, `question_en`, `question_ar`, `meta`, `type`, `choices`, `choices_en`, `choices_ar`, `created_at`, `updated_at`) VALUES
(1, 1, 'What is your age category? (Junior / Youth / Senior)', 'What is your age category? (Junior / Youth / Senior)', 'ما هي فئتك العمرية؟ (ناشئين / شباب / كبار)', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(2, 1, 'What is your primary position?', 'What is your primary position?', 'ما هو مركزك الأساسي؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(3, 1, 'What is your playing level? (Amateur / Academy / Experienced / Professional)', 'What is your playing level? (Amateur / Academy / Experienced / Professional)', 'ما هو مستوى لعبك؟ (هواة / أكاديمي / ذو خبرة / محترف)', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(4, 1, 'What is your preferred foot (if applicable)?', 'What is your preferred foot (if applicable)?', 'ما هي قدمك المفضلة (إن أمكن)؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(5, 1, 'Do you have any previous injuries we should know about?', 'Do you have any previous injuries we should know about?', 'هل لديك أي إصابات سابقة يجب أن نعرف عنها؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(6, 2, 'What coaching licenses or certificates do you hold?', 'What coaching licenses or certificates do you hold?', 'ما هي رخص أو شهادات التدريب التي تحملها؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(7, 2, 'How many years of coaching experience do you have?', 'How many years of coaching experience do you have?', 'كم سنة من الخبرة التدريبية لديك؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(8, 2, 'Which age groups do you coach most often?', 'Which age groups do you coach most often?', 'أي الفئات العمرية تقوم بتدريبها غالبًا؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(9, 2, 'Are you available for private coaching sessions?', 'Are you available for private coaching sessions?', 'هل أنت متاح لجلسات تدريب خاصة؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(10, 3, 'What type of club are you? (Official / Community / Academy)', 'What type of club are you? (Official / Community / Academy)', 'ما نوع النادي؟ (رسمي / مجتمعي / أكاديمي)', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(11, 3, 'How many teams does the club manage?', 'How many teams does the club manage?', 'كم عدد الفرق التي يديرها النادي؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(12, 3, 'Which sports does the club operate in?', 'Which sports does the club operate in?', 'في أي رياضات يعمل النادي؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(13, 3, 'Do you accept player join requests?', 'Do you accept player join requests?', 'هل تقبل طلبات انضمام لاعبين؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(14, 4, 'What coverage types do you offer? (Photo / Video / Live broadcast)', 'What coverage types do you offer? (Photo / Video / Live broadcast)', 'ما أنواع التغطية التي تقدمها؟ (تصوير / فيديو / بث مباشر)', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(15, 4, 'Do you provide editing and post-production services?', 'Do you provide editing and post-production services?', 'هل تقدم خدمات التحرير وما بعد الإنتاج؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(16, 4, 'Do you have sample work or a portfolio link?', 'Do you have sample work or a portfolio link?', 'هل لديك أعمال سابقة أو رابط للمحفظة؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(17, 4, 'Are you available for event-based bookings?', 'Are you available for event-based bookings?', 'هل أنت متاح للحجوزات حسب الفعاليات؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(18, 5, 'What qualifications or certifications do you hold?', 'What qualifications or certifications do you hold?', 'ما هي مؤهلاتك أو الشهادات التي تحملها؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(19, 5, 'Do you offer in-clinic and on-field services?', 'Do you offer in-clinic and on-field services?', 'هل تقدم خدمات داخل العيادة وعلى أرض الملعب؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(20, 5, 'What treatment types do you specialise in?', 'What treatment types do you specialise in?', 'ما أنواع العلاجات التي تتخصص بها؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(21, 5, 'Are you available for match-day support?', 'Are you available for match-day support?', 'هل أنت متاح لدعم يوم المباراة؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(22, 6, 'How many children are participating?', 'How many children are participating?', 'كم عدد الأطفال المشاركين؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(23, 6, 'What are the preferred sports for your children?', 'What are the preferred sports for your children?', 'ما هي الرياضات المفضلة لأطفالك؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(24, 6, 'Would you like weekly progress reports?', 'Would you like weekly progress reports?', 'هل ترغب في تقارير تقدم أسبوعية؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(25, 7, 'Which teams do you follow most closely?', 'Which teams do you follow most closely?', 'أي الفرق تتابعها عن كثب؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(26, 7, 'Do you attend matches regularly?', 'Do you attend matches regularly?', 'هل تحضر المباريات بانتظام؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(27, 8, 'Do you represent players currently?', 'Do you represent players currently?', 'هل تمثل لاعبين حاليًا؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(28, 8, 'Which player age groups do you scout?', 'Which player age groups do you scout?', 'أي فئات عمرية للاعبين تقوم بالتنقيب عنها؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(29, 8, 'What regions do you operate in?', 'What regions do you operate in?', 'في أي مناطق تعمل؟', NULL, 'text', NULL, NULL, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(30, 1, 'Which of the following best describes you?', 'Which of the following best describes you?', 'أي مما يلي يصفك بشكل أفضل؟', NULL, 'multiple_choice', '[\"Option 1\", \"Option 2\", \"Option 3\"]', NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(31, 1, 'Are you available for contact?', 'Are you available for contact?', 'هل أنت متاح للتواصل؟', NULL, 'boolean', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(32, 1, 'How many years of experience do you have? (enter a number)', 'How many years of experience do you have? (enter a number)', 'كم سنة من الخبرة لديك؟ (أدخل رقما)', NULL, 'number', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(33, 2, 'Which of the following best describes you?', 'Which of the following best describes you?', 'أي مما يلي يصفك بشكل أفضل؟', NULL, 'multiple_choice', '[\"Option 1\", \"Option 2\", \"Option 3\"]', NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(34, 2, 'Are you available for contact?', 'Are you available for contact?', 'هل أنت متاح للتواصل؟', NULL, 'boolean', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(35, 2, 'How many years of experience do you have? (enter a number)', 'How many years of experience do you have? (enter a number)', 'كم سنة من الخبرة لديك؟ (أدخل رقما)', NULL, 'number', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(36, 3, 'Which of the following best describes you?', 'Which of the following best describes you?', 'أي مما يلي يصفك بشكل أفضل؟', NULL, 'multiple_choice', '[\"Option 1\", \"Option 2\", \"Option 3\"]', NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(37, 3, 'Are you available for contact?', 'Are you available for contact?', 'هل أنت متاح للتواصل؟', NULL, 'boolean', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(38, 3, 'How many years of experience do you have? (enter a number)', 'How many years of experience do you have? (enter a number)', 'كم سنة من الخبرة لديك؟ (أدخل رقما)', NULL, 'number', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(39, 4, 'Which of the following best describes you?', 'Which of the following best describes you?', 'أي مما يلي يصفك بشكل أفضل؟', NULL, 'multiple_choice', '[\"Option 1\", \"Option 2\", \"Option 3\"]', NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(40, 4, 'Are you available for contact?', 'Are you available for contact?', 'هل أنت متاح للتواصل؟', NULL, 'boolean', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(41, 4, 'How many years of experience do you have? (enter a number)', 'How many years of experience do you have? (enter a number)', 'كم سنة من الخبرة لديك؟ (أدخل رقما)', NULL, 'number', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(42, 5, 'Which of the following best describes you?', 'Which of the following best describes you?', 'أي مما يلي يصفك بشكل أفضل؟', NULL, 'multiple_choice', '[\"Option 1\", \"Option 2\", \"Option 3\"]', NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(43, 5, 'Are you available for contact?', 'Are you available for contact?', 'هل أنت متاح للتواصل؟', NULL, 'boolean', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(44, 5, 'How many years of experience do you have? (enter a number)', 'How many years of experience do you have? (enter a number)', 'كم سنة من الخبرة لديك؟ (أدخل رقما)', NULL, 'number', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(45, 6, 'Which of the following best describes you?', 'Which of the following best describes you?', 'أي مما يلي يصفك بشكل أفضل؟', NULL, 'multiple_choice', '[\"Option 1\", \"Option 2\", \"Option 3\"]', NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(46, 6, 'Are you available for contact?', 'Are you available for contact?', 'هل أنت متاح للتواصل؟', NULL, 'boolean', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(47, 6, 'How many years of experience do you have? (enter a number)', 'How many years of experience do you have? (enter a number)', 'كم سنة من الخبرة لديك؟ (أدخل رقما)', NULL, 'number', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(48, 7, 'Which of the following best describes you?', 'Which of the following best describes you?', 'أي مما يلي يصفك بشكل أفضل؟', NULL, 'multiple_choice', '[\"Option 1\", \"Option 2\", \"Option 3\"]', NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(49, 7, 'Are you available for contact?', 'Are you available for contact?', 'هل أنت متاح للتواصل؟', NULL, 'boolean', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(50, 7, 'How many years of experience do you have? (enter a number)', 'How many years of experience do you have? (enter a number)', 'كم سنة من الخبرة لديك؟ (أدخل رقما)', NULL, 'number', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(51, 8, 'Which of the following best describes you?', 'Which of the following best describes you?', 'أي مما يلي يصفك بشكل أفضل؟', NULL, 'multiple_choice', '[\"Option 1\", \"Option 2\", \"Option 3\"]', NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(52, 8, 'Are you available for contact?', 'Are you available for contact?', 'هل أنت متاح للتواصل؟', NULL, 'boolean', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57'),
(53, 8, 'How many years of experience do you have? (enter a number)', 'How many years of experience do you have? (enter a number)', 'كم سنة من الخبرة لديك؟ (أدخل رقما)', NULL, 'number', NULL, NULL, NULL, '2025-12-02 21:04:57', '2025-12-02 21:04:57');

-- --------------------------------------------------------

--
-- Table structure for table `question_answers`
--

CREATE TABLE `question_answers` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `answer` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `question_answers`
--

INSERT INTO `question_answers` (`id`, `user_id`, `question_id`, `answer`, `created_at`, `updated_at`) VALUES
(1, 4, 1, '{\"value\": \"Tt\"}', '2025-12-02 15:50:07', '2025-12-02 15:50:07'),
(2, 4, 2, '{\"value\": \"T2\"}', '2025-12-02 15:50:07', '2025-12-02 15:50:07'),
(3, 4, 3, '{\"value\": \"T3\"}', '2025-12-02 15:50:07', '2025-12-02 15:50:07'),
(4, 4, 4, '{\"value\": \"T4\"}', '2025-12-02 15:50:07', '2025-12-02 15:50:07'),
(5, 4, 5, '{\"value\": \"T5\"}', '2025-12-02 15:50:07', '2025-12-02 15:50:07'),
(6, 4, 30, '{\"value\": \"Option 1\"}', '2025-12-02 22:49:37', '2025-12-02 22:49:37'),
(7, 4, 31, '{\"value\": true}', '2025-12-02 22:49:37', '2025-12-02 22:49:37'),
(8, 4, 32, '{\"value\": \"5\"}', '2025-12-02 22:49:37', '2025-12-02 22:49:37'),
(9, 13, 1, '{\"value\": \"T1\"}', '2025-12-04 19:03:18', '2025-12-04 19:03:18'),
(10, 13, 2, '{\"value\": \"هجوم\"}', '2025-12-04 19:03:19', '2025-12-04 19:03:19'),
(11, 13, 3, '{\"value\": \"محترف\"}', '2025-12-04 19:03:19', '2025-12-04 19:03:19'),
(12, 13, 4, '{\"value\": \"يمين\"}', '2025-12-04 19:03:19', '2025-12-04 19:03:19'),
(13, 13, 5, '{\"value\": \"لا\"}', '2025-12-04 19:03:19', '2025-12-04 19:03:19'),
(14, 13, 30, '{\"value\": \"Option 1\"}', '2025-12-04 19:03:19', '2025-12-04 19:03:19'),
(15, 13, 31, '{\"value\": true}', '2025-12-04 19:03:19', '2025-12-04 19:03:19'),
(16, 13, 32, '{\"value\": \"5\"}', '2025-12-04 19:03:19', '2025-12-04 19:03:19'),
(17, 13, 27, '{\"value\": \"T1\"}', '2025-12-04 19:07:41', '2025-12-04 19:07:41'),
(18, 13, 28, '{\"value\": \"T2\"}', '2025-12-04 19:07:41', '2025-12-04 19:07:41'),
(19, 13, 29, '{\"value\": \"T3\"}', '2025-12-04 19:07:41', '2025-12-04 19:07:41'),
(20, 13, 51, '{\"value\": \"Option 1\"}', '2025-12-04 19:07:41', '2025-12-04 19:07:41'),
(21, 13, 52, '{\"value\": true}', '2025-12-04 19:07:41', '2025-12-04 19:07:41'),
(22, 13, 53, '{\"value\": \"5\"}', '2025-12-04 19:07:41', '2025-12-04 19:07:41'),
(23, 12, 1, '{\"value\": \"T\"}', '2025-12-04 19:13:58', '2025-12-04 19:13:58'),
(24, 12, 2, '{\"value\": \"T\"}', '2025-12-04 19:13:58', '2025-12-04 19:13:58'),
(25, 12, 3, '{\"value\": \"T\"}', '2025-12-04 19:13:58', '2025-12-04 19:13:58'),
(26, 12, 4, '{\"value\": \"R\"}', '2025-12-04 19:13:58', '2025-12-04 19:13:58'),
(27, 12, 5, '{\"value\": \"R\"}', '2025-12-04 19:13:58', '2025-12-04 19:13:58'),
(28, 12, 30, '{\"value\": \"Option 3\"}', '2025-12-04 19:13:58', '2025-12-04 19:13:58'),
(29, 12, 31, '{\"value\": true}', '2025-12-04 19:13:58', '2025-12-04 19:13:58'),
(30, 12, 32, '{\"value\": \"5\"}', '2025-12-04 19:13:58', '2025-12-04 19:13:58'),
(31, 15, 1, '{\"value\": \"شباب\"}', '2025-12-04 21:46:17', '2025-12-04 21:46:17'),
(32, 15, 2, '{\"value\": \"وسط\"}', '2025-12-04 21:46:17', '2025-12-04 21:46:17'),
(33, 15, 3, '{\"value\": \"محترفاليمنى\"}', '2025-12-04 21:46:17', '2025-12-04 21:46:17'),
(34, 15, 4, '{\"value\": \"اليمنى\"}', '2025-12-04 21:46:17', '2025-12-04 21:46:17'),
(35, 15, 5, '{\"value\": \"لا\"}', '2025-12-04 21:46:17', '2025-12-04 21:46:17'),
(36, 15, 30, '{\"value\": \"Option 1\"}', '2025-12-04 21:46:17', '2025-12-04 21:46:17'),
(37, 15, 31, '{\"value\": true}', '2025-12-04 21:46:17', '2025-12-04 21:46:17'),
(38, 15, 32, '{\"value\": \"4\"}', '2025-12-04 21:46:17', '2025-12-04 21:46:17');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint UNSIGNED NOT NULL,
  `provider_id` bigint UNSIGNED NOT NULL,
  `club_id` bigint UNSIGNED DEFAULT NULL,
  `sport_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) DEFAULT NULL,
  `duration_minutes` int DEFAULT NULL,
  `currency` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `provider_id`, `club_id`, `sport_id`, `title`, `slug`, `description`, `price`, `duration_minutes`, `currency`, `is_active`, `meta`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 'Personal Coaching', 'personal-coaching', 'One-on-one coaching session', 21.89, 60, 'USD', 1, NULL, '2025-11-23 01:23:45', '2025-11-23 01:23:45'),
(2, 2, NULL, NULL, 'Goalkeeping Clinic', 'goalkeeping-clinic', 'Group clinic', 15.00, 120, 'USD', 1, NULL, '2025-11-23 01:23:45', '2025-11-23 01:23:45');

-- --------------------------------------------------------

--
-- Table structure for table `service_media`
--

CREATE TABLE `service_media` (
  `id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'image',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_media`
--

INSERT INTO `service_media` (`id`, `service_id`, `url`, `type`, `title`, `meta`, `created_at`, `updated_at`) VALUES
(1, 1, 'https://placehold.co/600x300', 'image', 'Coaching photo', NULL, '2025-11-23 01:23:45', '2025-11-23 01:23:45');

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `requester_id` bigint UNSIGNED NOT NULL,
  `provider_id` bigint UNSIGNED NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `message` text COLLATE utf8mb4_unicode_ci,
  `scheduled_at` datetime DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `payment_meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_reviews`
--

CREATE TABLE `service_reviews` (
  `id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0nzh9tI6pY8P9LJirGHWIWFxgIYhElEgrWMgSu2Q', NULL, '35.173.122.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36 Assetnote/1.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYklkdmNQQWoxZzY2RVluUWZoNGpURjBkZnVVNTdMd3YyZFpoeTczQSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1766048261),
('8861tkXZkBxNbx3jBGUHqBteEPDDWXuBuKYwqPNH', NULL, '134.199.164.161', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiVmtPeWdZV2VpM1Jldkdqb21RS0x2NUU0Q3cyMFRneTBtTHZSYk1ycCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1766077526),
('9NXm3zkUL3uQXVKQWOlgmIqjFikaEkkDebaTM1xW', NULL, '134.199.164.161', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieGNpN3VaZXY5UnlVYzdXd0puN1FmOFl6MjU0UUFrbUYyQnBiTGZJSyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1766077527),
('aGWEJJBIjyJ1d0BlNq5MXFF0kQuIvv2XkBtGhWnl', NULL, '194.180.49.169', 'python-httpx/0.28.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYUZ4bDFIMWswRGQ2SmFzQmlsaUZBZjBDRG9yQVF2WEtXQjN4OUxzUiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1765727558),
('ALDymw1XVg0HdbOG7xqdAi7rsGCLgkeRNfrvILCg', NULL, '198.235.24.145', '', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVzJUYWlhSjU2NGplQzRpc2xuVFMxbmVHVVJ1QUlDZ0RMSHlvT0hHYSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1765703226),
('bVTX8Ox68hi8ht7qZaOPwa8zPU7a8AAW2mSqQQTW', NULL, '74.7.227.164', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMzRteHgzT0s4Sk5taElEVU52bUlVcnFOOHdyWmJ3WG5odklpbmF5aSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1765899284),
('dyPCaJwNXBVlXp9anM8TbTi4cxLfZ1sLA1e0HaJr', NULL, '205.210.31.20', '', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWE9wOGF1cWdTYjk4V2hOWXE5bmdha3lremg2dUJtSndhSEV2TVZDYyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1765641491),
('fMBsuOOVok2qsXiGqCvw9F2F4FWYJleGYapHTPEL', NULL, '134.199.164.161', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiQkJWUlNuZUt0eWg1UjNZeVpybGp1UXE1eTdMOXJueW9CWjJqV3RqRyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1766077543),
('FYc8GvKtLIPd1GSEF2afmZHFc5OasBPRypgsO7cr', NULL, '134.199.164.161', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMXNMSk1HbWZxQWQ0ODFrZXFLd3lqa29TOVVXWWRXY1M5a0Z3c2pJTiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHBzOi8vc2FoYS5tdWx0aXRlY2huby5zaG9wL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1766077527),
('IpRNvZMmZ7FKP1XqYw03S0dXGSd0uNRz5ZtwqYtO', NULL, '167.94.138.114', 'Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaFBtWFVCWEpTTDcyYlpSSG9haXFTeGVDbHo2Q0o2Z0pNUVpXVm1QdiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1765832134),
('IR4qvoQZiSygwnssOYw7Vh45QbDZrqN79RHCBw2i', NULL, '134.199.164.161', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieHdxS3h0a1cweDFFZUVqUWhZUW5pTGJvRmpmV05LTGJiQ0M2cElmTyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1766077525),
('jNcU5BMX0oKo1WlccVjP7yH6uDN8vR3UO4g00z99', NULL, '185.247.137.187', 'Mozilla/5.0 (compatible; InternetMeasurement/1.0; +https://internet-measurement.com/)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSjA5MGg0MVFRQWllVWdjTUd5U0ZyMWRMdlk0NDdYcXhFU0dJanI3bCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1766121297),
('lkyCcpat8vnDXUT6sOCBedVVZ4gbDytJbeKcscOx', NULL, '74.7.227.163', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWVdheGUzYm1uMmU2dTU5eUVrUHFvUFFLM0pORnY1YlFWdUN3SGpkeiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1765764080),
('nEFOYDhd9hooQUFvT1TCDHgeKwnRbJ8kUmclOhib', NULL, '205.210.31.20', '', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNHVEME5LblBnZE1NNWVrc2JRUVFGVXRSckxMUHZZR0xycWhTU1VxQSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHBzOi8vc2FoYS5tdWx0aXRlY2huby5zaG9wL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1765641492),
('oRgENBut0h2bDyZII2sABfrnnB2NWZyeN0TPGsZw', NULL, '74.7.243.218', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaHFRZGJTOHg1d2lYVUZKV1BaMmJNSVhsYjBiZ3N3SmF3MndVeG1vVyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1765850090),
('QFz9GtR867SjnFbL4Kc3usDtjeyHV30BxZYNuoLh', NULL, '74.7.241.56', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibm1ON2RFZ3dpT0M4NTNFZjNDUllWVzZ5OFZ3YWlORHpIRHJzSjNPSSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1766071270),
('Rz2wiFMU4xtHTSoHQc3PL59pBGkQU5UAljZNXoGt', NULL, '139.59.10.68', 'Mozilla/5.0 (X11; Linux x86_64; rv:139.0) Gecko/20100101 Firefox/139.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiY3NleHNGR0pJbDRPRlRUM2dyQ1ZYU21MMTY2aHRjaGZscDc3cTljMSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1766241602),
('TNJ85dtsRFREZGygfsm0Vg5SFaXysFko67i8fc9G', NULL, '198.235.24.145', '', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZWNFQUlibWtUQXFhVXAyZEdZUFFqb3k1ejh2cnl4NUZBSFpEdURaUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHBzOi8vc2FoYS5tdWx0aXRlY2huby5zaG9wL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1765703227),
('v1mnVCbOjBUWrrqTlMPHVb9ze0TU2msQ7LBnGhhJ', NULL, '194.180.49.169', 'python-httpx/0.28.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYzdhTmhydGR1TXRQc21zeXhVTGllcXdDVVNlZmE4eXJoS0dlSlZmMiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1765727560),
('VyooBuUT7fkLLm8hDN1uUuo59V88Z0A5XoXEg15N', NULL, '74.7.241.56', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMENsamszWm9PQjY2ZktSY2htTjZZYUFUeXFoaU43OUNyYzNiNUFKaiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1765968700),
('Yf0UN3xyY7W2LhM5VCy8kQQpY6hzgfIk2KWYgW3I', NULL, '35.226.83.254', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUk1GcGVlRG5EcFlQSDVuNkNOeFMxVGNqaTdMZVlmVWNSNjkyajdybCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1765961322),
('Z2jbatFithDHzdEVrtPl4Q846Uc71xmCJNeVmPks', NULL, '134.199.164.161', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTlRaam9RUG1EOXloelM5VlF6a1lMek42WG5rNndDakFjSjQ4bWx2YiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHBzOi8vc2FoYS5tdWx0aXRlY2huby5zaG9wL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1766077540),
('zrh08bsZFmwht6edxy4HGylI2ZkE4EG9NIp1RzDD', NULL, '134.199.164.161', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWjFLU00zVkNhSXNaWng1eTRaOHpZVkZRTllwN1d5Sk90aHozMHdHYyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyOToiaHR0cHM6Ly9zYWhhLm11bHRpdGVjaG5vLnNob3AiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1766077539);

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `title`, `title_en`, `title_ar`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Football Champion League Celebration', 'Football Champion League Celebration', 'احتفال دوري أبطال الكرة', 'images-demo/slider.png', '2025-11-23 01:23:45', '2025-11-23 01:23:45'),
(2, 'Basketball Championship Highlights', 'Basketball Championship Highlights', 'أبرز أحداث بطولة السلة', 'images-demo/slider.png', '2025-11-23 01:23:45', '2025-11-23 01:23:45'),
(3, 'Join Our Training Camps', 'Join Our Training Camps', 'انضم إلى معسكراتنا التدريبية', 'images-demo/slider.png', '2025-11-23 01:23:45', '2025-11-23 01:23:45');

-- --------------------------------------------------------

--
-- Table structure for table `sports`
--

CREATE TABLE `sports` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `description_en` text COLLATE utf8mb4_unicode_ci,
  `description_ar` text COLLATE utf8mb4_unicode_ci,
  `icon_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sports`
--

INSERT INTO `sports` (`id`, `name`, `name_en`, `name_ar`, `slug`, `description`, `description_en`, `description_ar`, `icon_url`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Football', 'Football', 'كرة القدم', 'football', NULL, NULL, NULL, 'images-demo/soccer-ball.png', 1, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(2, 'Basketball', 'Basketball', 'كرة السلة', 'basketball', NULL, NULL, NULL, 'images-demo/basketball.png', 1, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(3, 'Cricket', 'Cricket', 'الكريكيت', 'cricket', NULL, NULL, NULL, 'images-demo/cricket.png', 1, '2025-11-23 01:23:44', '2025-11-23 01:23:44');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` bigint UNSIGNED NOT NULL,
  `club_id` bigint UNSIGNED NOT NULL,
  `sport_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jersey_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coach` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `founded_year` year DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `club_id`, `sport_id`, `name`, `short_name`, `slug`, `jersey_color`, `coach`, `founded_year`, `active`, `meta`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Madreed FC', NULL, 'madreed-fc', NULL, NULL, NULL, 1, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44'),
(2, 2, 1, 'Al-Nasr FC', NULL, 'al-nasr-fc', NULL, NULL, NULL, 1, NULL, '2025-11-23 01:23:44', '2025-11-23 01:23:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verification_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_team_id` bigint UNSIGNED DEFAULT NULL,
  `profile_photo_path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `rate` decimal(10,2) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `team_id` bigint UNSIGNED DEFAULT NULL,
  `club_id` bigint UNSIGNED DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` int DEFAULT NULL,
  `nationality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stats` json DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `availability` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `phone`, `phone_verified_at`, `phone_verification_code`, `category_id`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `current_team_id`, `profile_photo_path`, `profile_title`, `bio`, `rate`, `rating`, `city`, `country`, `team_id`, `club_id`, `position`, `number`, `nationality`, `stats`, `is_featured`, `availability`, `created_at`, `updated_at`) VALUES
(1, 'Heba Abdelmonem', 'provider1@example.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$N2VYmhOARvWO5y2b./0bH.1AzPAEC/s8LmE3ElSQbkd.JcBpVqhMa', NULL, NULL, NULL, NULL, NULL, 'images-demo/players.png', 'Football Player', 'Experienced midfielder', NULL, NULL, 'Cairo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-23 01:23:45', '2025-11-23 01:23:45'),
(2, 'Marco H', 'provider2@example.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$dSkDF93/SpH76jjFHqAce.TQk6jBYSu2xDjJB4VKr7XoeHdRD5eje', NULL, NULL, NULL, NULL, NULL, 'images-demo/players.png', 'Goalkeeper', 'Top keeper', NULL, NULL, 'Madrid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-23 01:23:45', '2025-11-23 01:23:45'),
(3, 'abdoo', 't@t.com', NULL, '+96899999999', NULL, NULL, 1, '$2y$12$vfJtGVZfwGyxBFhedUNM7es0S/DzgbenyfZm2H0RnvGLXd6hlH.HC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-01 17:28:17', '2025-12-01 17:28:17'),
(4, 'أحمد محمد', 't@tt.com', NULL, '+968-92345678', NULL, NULL, 1, '$2y$12$uyWXVtlyKBgFeHmMELLLZu6OEGjWgIVC/pksM0KxPz8Lwyf8LSIEa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-01 17:58:52', '2025-12-01 17:58:52'),
(5, 'أحمد محمد', 't@ttt.com', NULL, '+968-92345679', NULL, NULL, 1, '$2y$12$IKUgDJA4sFGg6uK7hmpZm.U981mIi.tnciaVpF6PpU51fvnNsboYC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-01 18:19:13', '2025-12-01 18:19:13'),
(6, 'أحمد محمد', 't@tttt.com', NULL, '+968-92345688', NULL, NULL, 1, '$2y$12$capDpzox9xA7IlisDNKHs.2YiYlJ8khA/o9rt5wxDJlAPSfxySJ7G', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-01 18:28:24', '2025-12-01 18:28:24'),
(7, 'user2', 'pop@example.com', NULL, '01010669099', NULL, NULL, 1, '$2y$12$awL1g7OjJ1AlDd7jiyATxeJd.NMy3KqBgrIQOrC9/oCMWWf4i1Wha', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-02 00:10:19', '2025-12-02 00:10:19'),
(8, 'أحمد محمد', 'ttt@t.com', NULL, '+968-92345677', NULL, NULL, 1, '$2y$12$fsmrZzW6.YfmNv3/RPeEiuzVOkwHXw56APYXVdtetrEMS5bjk3dly', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-02 15:19:37', '2025-12-02 15:19:37'),
(9, 'user23', 'pop@example2.com', NULL, '01010661099', NULL, NULL, NULL, '$2y$12$9p72oxPSADltCB0emDIXhuJY/FYX9MKfDHxf7nmk.EAwUiFkG8t/C', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-02 21:00:11', '2025-12-02 21:00:11'),
(10, 'user2', 'pop@example3.com', NULL, '01000661099', NULL, NULL, 1, '$2y$12$td04dh3IPM8jKeXKDoDW7e0VR3gbsCslfI7/9vGmRBbyir10Xrl1S', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-02 21:00:55', '2025-12-02 21:00:55'),
(11, 'أحمد محمد', 'ttt@tttt.com', NULL, '+968-92345655', NULL, NULL, 1, '$2y$12$BeQpdf1Hi/cQW9fgXPLbRObjo4QotO5AdlDQkdjJAPHQdYUnRUaMC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-04 18:37:03', '2025-12-04 18:37:03'),
(12, 'أحمد محمد', 'tttt@t.com', NULL, '+968-92345444', NULL, NULL, 1, '$2y$12$SYfbXolBu7IB.hRc1mWr3O9nZUb0gVIanX6duPYo0Zb2.89GWF5ea', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-04 18:41:38', '2025-12-04 19:13:58'),
(13, 'أحمد محمد', 'tttttt@t.com', NULL, '+968-92345111', NULL, NULL, 8, '$2y$12$kr4ygM51ajC8Mx4df1Qg/.PGeXla.km66k50WJ3MnJ6KAZbw2kwwS', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-04 18:53:53', '2025-12-04 19:07:41'),
(14, 'أحمد محمد', 'ttttttt@t.com', NULL, '+968-92345333', NULL, NULL, NULL, '$2y$12$o0eEKdieQrvdTolJvEX22uWteFvnIN3XH9d9GYwayM7SU8VrE4jme', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-04 19:18:48', '2025-12-04 19:18:48'),
(15, 'ماجد', 'starz10ify@gmail.com', NULL, '+968-99169676', NULL, NULL, 1, '$2y$12$4./dnAjp1WToOUW6oK1zK.VfqGJNlHrKVmyHf5SXuiK40/9tC2kmm', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-04 21:44:15', '2025-12-04 21:46:17'),
(16, 'k', 'sfdgyyf@hotmail.com', NULL, '+968-99119922', NULL, NULL, NULL, '$2y$12$v423VjgBc7HyqgyBJmitNey/gZSVFSSrr867AkOp6D9Ggk/pp9CJi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-05 02:12:19', '2025-12-05 02:12:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_event_id_foreign` (`event_id`),
  ADD KEY `bookings_user_id_event_id_index` (`user_id`,`event_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clubs_slug_unique` (`slug`);

--
-- Indexes for table `club_sport`
--
ALTER TABLE `club_sport`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `club_sport_club_id_sport_id_unique` (`club_id`,`sport_id`),
  ADD KEY `club_sport_sport_id_foreign` (`sport_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `events_slug_unique` (`slug`),
  ADD KEY `events_sport_id_foreign` (`sport_id`),
  ADD KEY `events_club_id_sport_id_index` (`club_id`,`sport_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `leagues`
--
ALTER TABLE `leagues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leagues_slug_unique` (`slug`),
  ADD KEY `leagues_sport_id_index` (`sport_id`);

--
-- Indexes for table `league_team`
--
ALTER TABLE `league_team`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `league_team_league_id_team_id_unique` (`league_id`,`team_id`),
  ADD KEY `league_team_team_id_foreign` (`team_id`);

--
-- Indexes for table `league_videos`
--
ALTER TABLE `league_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `league_videos_league_id_index` (`league_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `media_mediaable_type_mediaable_id_index` (`mediaable_type`,`mediaable_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `players_slug_unique` (`slug`),
  ADD KEY `players_club_id_foreign` (`club_id`),
  ADD KEY `players_team_id_club_id_index` (`team_id`,`club_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `questions_category_id_foreign` (`category_id`);

--
-- Indexes for table `question_answers`
--
ALTER TABLE `question_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_answers_question_id_foreign` (`question_id`),
  ADD KEY `question_answers_user_id_foreign` (`user_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `services_slug_unique` (`slug`),
  ADD KEY `services_club_id_foreign` (`club_id`),
  ADD KEY `services_sport_id_foreign` (`sport_id`),
  ADD KEY `services_provider_id_club_id_sport_id_index` (`provider_id`,`club_id`,`sport_id`);

--
-- Indexes for table `service_media`
--
ALTER TABLE `service_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_media_service_id_foreign` (`service_id`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_requests_requester_id_foreign` (`requester_id`),
  ADD KEY `service_requests_provider_id_foreign` (`provider_id`),
  ADD KEY `sr_idx_s_r_p_st` (`service_id`,`requester_id`,`provider_id`,`status`);

--
-- Indexes for table `service_reviews`
--
ALTER TABLE `service_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_reviews_user_id_foreign` (`user_id`),
  ADD KEY `service_reviews_service_id_user_id_index` (`service_id`,`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sports`
--
ALTER TABLE `sports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sports_slug_unique` (`slug`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teams_slug_unique` (`slug`),
  ADD KEY `teams_sport_id_foreign` (`sport_id`),
  ADD KEY `teams_club_id_sport_id_index` (`club_id`,`sport_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_team_id_foreign` (`team_id`),
  ADD KEY `users_club_id_foreign` (`club_id`),
  ADD KEY `users_category_id_foreign` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `club_sport`
--
ALTER TABLE `club_sport`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leagues`
--
ALTER TABLE `leagues`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `league_team`
--
ALTER TABLE `league_team`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `league_videos`
--
ALTER TABLE `league_videos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `question_answers`
--
ALTER TABLE `question_answers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `service_media`
--
ALTER TABLE `service_media`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_reviews`
--
ALTER TABLE `service_reviews`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sports`
--
ALTER TABLE `sports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `club_sport`
--
ALTER TABLE `club_sport`
  ADD CONSTRAINT `club_sport_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `club_sport_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `events_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leagues`
--
ALTER TABLE `leagues`
  ADD CONSTRAINT `leagues_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `league_team`
--
ALTER TABLE `league_team`
  ADD CONSTRAINT `league_team_league_id_foreign` FOREIGN KEY (`league_id`) REFERENCES `leagues` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `league_team_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `league_videos`
--
ALTER TABLE `league_videos`
  ADD CONSTRAINT `league_videos_league_id_foreign` FOREIGN KEY (`league_id`) REFERENCES `leagues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `players_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_answers`
--
ALTER TABLE `question_answers`
  ADD CONSTRAINT `question_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_answers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `services_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `services_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `service_media`
--
ALTER TABLE `service_media`
  ADD CONSTRAINT `service_media_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_requester_id_foreign` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_reviews`
--
ALTER TABLE `service_reviews`
  ADD CONSTRAINT `service_reviews_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teams_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
