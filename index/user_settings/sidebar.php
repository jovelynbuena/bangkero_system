<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<div class="settings-sidebar">
    <ul>
        <li class="<?= $current=='profile.php'?'active':'' ?>">
            <a href="profile.php" style="text-decoration:none;color:inherit;">
                <i class="bi bi-person-circle"></i> Profile
            </a>
        </li>
        <li class="<?= $current=='archives.php'?'active':'' ?>">
            <a href="archives.php" style="text-decoration:none;color:inherit;">
                <i class="bi bi-archive"></i> Archives
            </a>
        </li>
        <li class="<?= $current=='security.php'?'active':'' ?>">
            <a href="security.php" style="text-decoration:none;color:inherit;">
                <i class="bi bi-shield-lock"></i> Security
            </a>
        </li>
        <li class="<?= $current=='preferences.php'?'active':'' ?>">
            <a href="preferences.php" style="text-decoration:none;color:inherit;">
                <i class="bi bi-gear"></i> Preferences
            </a>
        </li>
    </ul>
</div>