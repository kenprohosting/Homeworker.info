<?php
// Added publishable key constant for client-side SDK : jean luc 29 SEP 25
define("INTASEND_PUBLISHABLE_KEY", "ISPubKey_live_40f25458-716c-47c5-b049-786fd1f3a1ce");

// Added secret key constant for server-side API calls : jean luc 29 SEP 25
define("INTASEND_SECRET_KEY", "sk_live_xxxxxx"); // keep this PRIVATE

// Added API endpoint constant for checkout verification : jean luc 29 SEP 25
define("INTASEND_API_ENDPOINT", "https://payment.intasend.com/api/v1/checkout/");

// Added flag to toggle between live and sandbox : jean luc 29 SEP 25
define("INTASEND_LIVE_MODE", true); // set to false for sandbox

// Updated registration fee constants : jean luc 29 SEP 25
define("EMPLOYER_REGISTRATION_FEE", 20); // example fee, update as needed
define("FREELANCER_REGISTRATION_FEE", 1); // example fee, update as needed

// Added payment currency constant : jean luc 29 SEP 25
define("PAYMENT_CURRENCY", "USD");

// Require IntaSend PHP SDK via Composer (do this once in your project root):
// composer require intasend/intasend-php : jean luc 29 SEP 25

// SDK test flag expected by the IntaSend SDK : jean luc 29 SEP 25
// IntaSend SDK examples refer to a 'test' boolean; we expose it as INTASEND_TEST_ENVIRONMENT
define("INTASEND_TEST_ENVIRONMENT", !INTASEND_LIVE_MODE);

// Added callback and redirect URL constants : jean luc 29 SEP 25
// Build a sane base URL when used in a normal web request; fallback to empty string if unavailable
$intasend_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$intasend_host   = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$intasend_base   = $intasend_host ? $intasend_scheme . '://' . $intasend_host : '';

// Allow environment overrides (useful if running in CLI or CI) : jean luc 29 SEP 25
$env_callback = getenv('INTASEND_CALLBACK_URL');
$env_redirect = getenv('INTASEND_REDIRECT_URL');

define("INTASEND_CALLBACK_URL", $env_callback ? $env_callback : $intasend_base . "/employer_register_callback.php");
define("INTASEND_REDIRECT_URL", $env_redirect ? $env_redirect : $intasend_base . "/employer_register_callback.php");

// Small advisory: keep secret key truly secret. Do not commit real secret keys to repos. : jean luc 29 SEP 25
?>