<?php
// CSP default headers
// Note: 'unsafe-inline' is required because the app uses inline scripts
// (hokuto components, inline JS in views) and inline styles.

$defaultSrc = "default-src 'self';";
$scriptSrc  = "script-src 'self' 'unsafe-inline' https://unpkg.com;";
$styleSrc   = "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://unpkg.com;";
$fontSrc    = "font-src 'self' https://cdnjs.cloudflare.com;";
$imgSrc     = "img-src 'self' data:;";
$connectSrc = "connect-src 'self';";

$policy = implode(' ', [
    $defaultSrc,
    $scriptSrc,
    $styleSrc,
    $fontSrc,
    $imgSrc,
    $connectSrc
]);

header('Content-Security-Policy: ' . $policy);
