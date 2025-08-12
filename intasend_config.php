<?php
// IntaSend Configuration
// Replace these with your actual IntaSend API keys

// For live environment - using public key only
define('INTASEND_PUBLISHABLE_KEY', 'ISPubKey_live_40f25458-716c-47c5-b049-786fd1f3a1ce');

// For sandbox/testing environment
// define('INTASEND_PUBLISHABLE_KEY', 'ISPubKey_test_your_test_key_here');

define('INTASEND_LIVE_MODE', true); // Set to false for sandbox testing

// Payment settings
define('EMPLOYER_REGISTRATION_FEE', 1); // 1 USD for testing
define('FREELANCER_REGISTRATION_FEE', 1); // 1 USD 1 for testing
define('PAYMENT_CURRENCY', 'USD');
?>