<?php
// ============================================
// DATABASE CONNECTION SWITCHER
// Change between ONLINE and OFFLINE modes
// ============================================

// 🌐 ONLINE MODE (default) - Uses freesqldatabase.com
require_once(__DIR__ . '/db_connect_online.php');

// 💻 OFFLINE MODE - Uses localhost (uncomment for defense day)
// require_once(__DIR__ . '/db_connect_local.php');

// ============================================
// INSTRUCTIONS:
// - For normal use: Use ONLINE mode (current)
// - For defense/offline: Comment ONLINE, uncomment OFFLINE
// ============================================

