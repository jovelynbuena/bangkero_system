<?php
/**
 * logo_helper.php
 * Central association info loader — always reads from system_config.
 * Include this file wherever logo or association details are needed.
 *
 * Provides:
 *   $assocName      — association name
 *   $assocAddress   — association address
 *   $assocPhone     — association phone
 *   $assocEmail     — association email
 *   $assocLogoUrl   — full HTTP URL  (for <img src="...">)
 *   $assocLogoPath  — absolute filesystem path  (for file_exists / PDF libs)
 *   $assocLogoB64   — base64-encoded data  (for inline HTML <img> or FPDF)
 */

if (!isset($conn)) {
    $__helperBase = dirname(__DIR__);
    require_once $__helperBase . '/config/db_connect.php';
}

if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/path.php';
}

// Pull all association info from system_config
$__cfgRow  = $conn->query("SELECT assoc_name, assoc_address, assoc_phone, assoc_email, assoc_logo FROM system_config LIMIT 1");
$__cfg     = ($__cfgRow && ($__r = $__cfgRow->fetch_assoc())) ? $__r : [];

$assocName    = !empty($__cfg['assoc_name'])    ? $__cfg['assoc_name']    : 'Bangkero & Fishermen Association';
$assocAddress = !empty($__cfg['assoc_address']) ? $__cfg['assoc_address'] : 'Barangay Barretto, Olongapo City';
$assocPhone   = !empty($__cfg['assoc_phone'])   ? $__cfg['assoc_phone']   : '';
$assocEmail   = !empty($__cfg['assoc_email'])   ? $__cfg['assoc_email']   : '';

// Determine logo paths
// Uploads are stored inside index/uploads/config/ (where config.php lives)
$__root     = dirname(__DIR__);
$__indexDir = $__root . '/index';
$__logoFile = $__cfg['assoc_logo'] ?? '';

if (!empty($__logoFile)) {
    $__uploadPath = $__indexDir . '/uploads/config/' . $__logoFile;
    if (file_exists($__uploadPath)) {
        $assocLogoUrl  = BASE_URL . 'uploads/config/' . htmlspecialchars($__logoFile) . '?v=' . filemtime($__uploadPath);
        $assocLogoPath = $__uploadPath;
    } else {
        $assocLogoUrl  = BASE_URL . '../images/logo1.png';
        $assocLogoPath = $__root . '/images/logo1.png';
    }
} else {
    $assocLogoUrl  = BASE_URL . '../images/logo1.png';
    $assocLogoPath = $__root . '/images/logo1.png';
}

$assocLogoB64 = file_exists($assocLogoPath) ? base64_encode(file_get_contents($assocLogoPath)) : '';

unset($__cfgRow, $__cfg, $__logoFile, $__uploadPath, $__root, $__indexDir, $__helperBase, $__r);
