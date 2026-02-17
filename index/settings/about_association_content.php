<?php
session_start();
require_once('../../config/db_connect.php');

if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

// Ensure who_we_are table exists (MySQL-compatible timestamps)
$conn->query("CREATE TABLE IF NOT EXISTS who_we_are (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure featured_programs table exists
$conn->query("CREATE TABLE IF NOT EXISTS featured_programs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    icon_class VARCHAR(100) DEFAULT NULL,
    button_label VARCHAR(100) DEFAULT 'View Events',
    button_link VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure mission_vision table exists
$conn->query("CREATE TABLE IF NOT EXISTS mission_vision (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mission LONGTEXT NOT NULL,
    vision LONGTEXT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure core_values table exists
$conn->query("CREATE TABLE IF NOT EXISTS core_values (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure association_glance table exists
$conn->query("CREATE TABLE IF NOT EXISTS association_glance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    overview LONGTEXT NOT NULL,
    founded_year INT NOT NULL,
    members_count INT NOT NULL,
    projects_count INT NOT NULL,
    events_count INT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure partners_sponsors table exists
$conn->query("CREATE TABLE IF NOT EXISTS partners_sponsors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    logo_path VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'partner',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure home_carousel_slides table exists
$conn->query("CREATE TABLE IF NOT EXISTS home_carousel_slides (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    primary_button_label VARCHAR(100) DEFAULT 'Learn More',
    primary_button_link VARCHAR(255) DEFAULT 'about_us.php',
    secondary_button_label VARCHAR(100) DEFAULT 'Join Us',
    secondary_button_link VARCHAR(255) DEFAULT 'contact_us.php',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure downloadable_resources table exists (for Resources page)
$conn->query("CREATE TABLE IF NOT EXISTS downloadable_resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    file_key VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    icon_class VARCHAR(100) DEFAULT NULL,
    color_hex VARCHAR(20) DEFAULT '#0d6efd',
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ==================== ARCHIVE TABLES FOR WEBSITE CONTENT ====================
// Archive for Who We Are entries
$conn->query("CREATE TABLE IF NOT EXISTS who_we_are_archive (
    archive_id INT PRIMARY KEY AUTO_INCREMENT,
    original_id INT,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    original_created_at DATETIME NULL,
    archived_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Archive for Featured Programs
$conn->query("CREATE TABLE IF NOT EXISTS featured_programs_archive (
    archive_id INT PRIMARY KEY AUTO_INCREMENT,
    original_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    icon_class VARCHAR(100) DEFAULT NULL,
    button_label VARCHAR(100) DEFAULT 'View Events',
    button_link VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    original_created_at DATETIME NULL,
    archived_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Archive for Core Values
$conn->query("CREATE TABLE IF NOT EXISTS core_values_archive (
    archive_id INT PRIMARY KEY AUTO_INCREMENT,
    original_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    original_created_at DATETIME NULL,
    archived_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Archive for Partners & Sponsors
$conn->query("CREATE TABLE IF NOT EXISTS partners_sponsors_archive (
    archive_id INT PRIMARY KEY AUTO_INCREMENT,
    original_id INT,
    name VARCHAR(255) NOT NULL,
    logo_path VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'partner',
    sort_order INT NOT NULL DEFAULT 0,
    original_created_at DATETIME NULL,
    archived_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Archive for Homepage Carousel slides
$conn->query("CREATE TABLE IF NOT EXISTS home_carousel_slides_archive (
    archive_id INT PRIMARY KEY AUTO_INCREMENT,
    original_id INT,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    primary_button_label VARCHAR(100) DEFAULT 'Learn More',
    primary_button_link VARCHAR(255) DEFAULT 'about_us.php',
    secondary_button_label VARCHAR(100) DEFAULT 'Join Us',
    secondary_button_link VARCHAR(255) DEFAULT 'contact_us.php',
    sort_order INT NOT NULL DEFAULT 0,
    original_created_at DATETIME NULL,
    archived_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Archive for Downloadable Resources
$conn->query("CREATE TABLE IF NOT EXISTS downloadable_resources_archive (
    archive_id INT PRIMARY KEY AUTO_INCREMENT,
    original_id INT,
    file_key VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    icon_class VARCHAR(100) DEFAULT NULL,
    color_hex VARCHAR(20) DEFAULT '#0d6efd',
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    original_created_at DATETIME NULL,
    archived_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");


$who_error = '';
$who_success = '';
$program_error = '';
$program_success = '';
$mv_error = '';
$mv_success = '';
$values_error = '';
$values_success = '';
$glance_error = '';
$glance_success = '';
$partners_error = '';
$partners_success = '';
$carousel_error = '';
$carousel_success = '';
$resources_error = '';
$resources_success = '';







// Track which tab should be active after form submissions
$activeTab = 'who'; // default tab
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['program_action'])) {
        $activeTab = 'programs';
    } elseif (isset($_POST['mv_action'])) {
        $activeTab = 'mv';
    } elseif (isset($_POST['values_action'])) {
        $activeTab = 'values';
    } elseif (isset($_POST['glance_action'])) {
        $activeTab = 'glance';
    } elseif (isset($_POST['partners_action'])) {
        $activeTab = 'partners';
    } elseif (isset($_POST['carousel_action'])) {
        $activeTab = 'carousel';
    } elseif (isset($_POST['resources_action'])) {
        $activeTab = 'resources';
    } elseif (isset($_POST['who_action'])) {
        $activeTab = 'who';
    }
}





// Handle Who We Are form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['who_action'])) {
    $action = $_POST['who_action'];
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    // Basic validation
    if ($action !== 'delete' && ($title === '' || $content === '')) {
        $who_error = 'Title and content are required.';
    } else {
        // Handle image upload if present
        $imageFileName = null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/who_we_are/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if (in_array($ext, $allowed, true)) {
                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', $_FILES['image']['name']);
                $destPath = $uploadDir . $safeName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
                    $imageFileName = $safeName;
                }
            }
        }

        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO who_we_are (title, content, image) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $title, $content, $imageFileName);
            if ($stmt->execute()) {
                $who_success = 'Entry added successfully.';
            } else {
                $who_error = 'Failed to add entry.';
            }
            $stmt->close();
        } elseif ($action === 'edit' && $id > 0) {
            if ($imageFileName) {
                $stmt = $conn->prepare("UPDATE who_we_are SET title = ?, content = ?, image = ? WHERE id = ?");
                $stmt->bind_param('sssi', $title, $content, $imageFileName, $id);
            } else {
                $stmt = $conn->prepare("UPDATE who_we_are SET title = ?, content = ? WHERE id = ?");
                $stmt->bind_param('ssi', $title, $content, $id);
            }
            if ($stmt->execute()) {
                $who_success = 'Entry updated successfully.';
            } else {
                $who_error = 'Failed to update entry.';
            }
            $stmt->close();
        } elseif ($action === 'delete' && $id > 0) {
            // Archive instead of hard delete
            $stmtSel = $conn->prepare("SELECT title, content, image, created_at FROM who_we_are WHERE id = ?");
            $stmtSel->bind_param('i', $id);
            if ($stmtSel->execute()) {
                $resultSel = $stmtSel->get_result();
                if ($row = $resultSel->fetch_assoc()) {
                    $stmtArch = $conn->prepare("INSERT INTO who_we_are_archive (original_id, title, content, image, original_created_at) VALUES (?, ?, ?, ?, ?)");
                    $origCreated = $row['created_at'] ?? null;
                    $stmtArch->bind_param('issss', $id, $row['title'], $row['content'], $row['image'], $origCreated);
                    if ($stmtArch->execute()) {
                        $stmtDel = $conn->prepare("DELETE FROM who_we_are WHERE id = ?");
                        $stmtDel->bind_param('i', $id);
                        if ($stmtDel->execute()) {
                            $who_success = 'Entry archived successfully.';
                        } else {
                            $who_error = 'Failed to remove entry after archiving.';
                        }
                        $stmtDel->close();
                    } else {
                        $who_error = 'Failed to archive entry.';
                    }
                    $stmtArch->close();
                } else {
                    $who_error = 'Entry not found for archiving.';
                }
            } else {
                $who_error = 'Failed to load entry for archiving.';
            }
            $stmtSel->close();
        }

    }
}

// Fetch all Who We Are entries
$who_entries = [];
$resultWho = $conn->query("SELECT * FROM who_we_are ORDER BY created_at ASC");
if ($resultWho && $resultWho->num_rows > 0) {
    while ($row = $resultWho->fetch_assoc()) {
        $who_entries[] = $row;
    }
}

// Handle Mission & Vision form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mv_action'])) {
    $mission = trim($_POST['mv_mission'] ?? '');
    $vision  = trim($_POST['mv_vision'] ?? '');

    if ($mission === '' || $vision === '') {
        $mv_error = 'Mission and vision are required.';
    } else {
        $mvCheck = $conn->query("SELECT id FROM mission_vision LIMIT 1");
        if ($mvCheck && $mvCheck->num_rows > 0) {
            $row = $mvCheck->fetch_assoc();
            $id  = (int)$row['id'];
            $stmt = $conn->prepare("UPDATE mission_vision SET mission = ?, vision = ? WHERE id = ?");
            $stmt->bind_param('ssi', $mission, $vision, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO mission_vision (mission, vision) VALUES (?, ?)");
            $stmt->bind_param('ss', $mission, $vision);
        }

        if ($stmt->execute()) {
            $mv_success = 'Mission & Vision updated successfully.';
            $mission_text = $mission;
            $vision_text  = $vision;
        } else {
            $mv_error = 'Failed to save Mission & Vision.';
        }
        $stmt->close();
    }
}

// Handle Featured Programs form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['program_action'])) {
    $p_action = $_POST['program_action'];
    $p_id = isset($_POST['program_id']) ? (int)$_POST['program_id'] : 0;
    $p_title = trim($_POST['program_title'] ?? '');
    $p_description = trim($_POST['program_description'] ?? '');
    $p_icon = trim($_POST['program_icon'] ?? '');
    $p_button_label = trim($_POST['program_button_label'] ?? 'View Events');
    $p_button_link = trim($_POST['program_button_link'] ?? '');
    $p_sort_order = isset($_POST['program_sort_order']) ? (int)$_POST['program_sort_order'] : 1;
    if ($p_sort_order < 1) {
        $p_sort_order = 1;
    }


    if ($p_action !== 'delete' && ($p_title === '' || $p_description === '')) {
        $program_error = 'Title and description are required for a featured program.';
    } else {
        if ($p_action === 'add') {
            $stmt = $conn->prepare("INSERT INTO featured_programs (title, description, icon_class, button_label, button_link, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssi', $p_title, $p_description, $p_icon, $p_button_label, $p_button_link, $p_sort_order);
            if ($stmt->execute()) {
                $program_success = 'Featured program added successfully.';
            } else {
                $program_error = 'Failed to add featured program.';
            }
            $stmt->close();
        } elseif ($p_action === 'edit' && $p_id > 0) {
            $stmt = $conn->prepare("UPDATE featured_programs SET title = ?, description = ?, icon_class = ?, button_label = ?, button_link = ?, sort_order = ? WHERE id = ?");
            $stmt->bind_param('sssssii', $p_title, $p_description, $p_icon, $p_button_label, $p_button_link, $p_sort_order, $p_id);
            if ($stmt->execute()) {
                $program_success = 'Featured program updated successfully.';
            } else {
                $program_error = 'Failed to update featured program.';
            }
            $stmt->close();
        } elseif ($p_action === 'delete' && $p_id > 0) {
            // Archive instead of hard delete
            $stmtSel = $conn->prepare("SELECT title, description, icon_class, button_label, button_link, sort_order, created_at FROM featured_programs WHERE id = ?");
            $stmtSel->bind_param('i', $p_id);
            if ($stmtSel->execute()) {
                $resultSel = $stmtSel->get_result();
                if ($row = $resultSel->fetch_assoc()) {
                    $stmtArch = $conn->prepare("INSERT INTO featured_programs_archive (original_id, title, description, icon_class, button_label, button_link, sort_order, original_created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $origCreated = $row['created_at'] ?? null;
                    $stmtArch->bind_param('isssssis', $p_id, $row['title'], $row['description'], $row['icon_class'], $row['button_label'], $row['button_link'], $row['sort_order'], $origCreated);
                    if ($stmtArch->execute()) {
                        $stmtDel = $conn->prepare("DELETE FROM featured_programs WHERE id = ?");
                        $stmtDel->bind_param('i', $p_id);
                        if ($stmtDel->execute()) {
                            $program_success = 'Featured program archived successfully.';
                        } else {
                            $program_error = 'Failed to remove featured program after archiving.';
                        }
                        $stmtDel->close();
                    } else {
                        $program_error = 'Failed to archive featured program.';
                    }
                    $stmtArch->close();
                } else {
                    $program_error = 'Featured program not found for archiving.';
                }
            } else {
                $program_error = 'Failed to load featured program for archiving.';
            }
            $stmtSel->close();
        }

    }
}

// Handle Association at a Glance form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['glance_action'])) {
    $g_overview = trim($_POST['glance_overview'] ?? '');
    $g_year = isset($_POST['glance_founded_year']) ? (int)$_POST['glance_founded_year'] : 0;
    $g_members = isset($_POST['glance_members']) ? (int)$_POST['glance_members'] : 0;
    $g_projects = isset($_POST['glance_projects']) ? (int)$_POST['glance_projects'] : 0;
    $g_events = isset($_POST['glance_events']) ? (int)$_POST['glance_events'] : 0;

    if ($g_overview === '') {
        $glance_error = 'Overview content is required.';
    } else {
        if ($g_year < 1) { $g_year = 1; }
        if ($g_members < 1) { $g_members = 1; }
        if ($g_projects < 1) { $g_projects = 1; }
        if ($g_events < 1) { $g_events = 1; }

        $glanceCheck = $conn->query("SELECT id FROM association_glance LIMIT 1");
        if ($glanceCheck && $glanceCheck->num_rows > 0) {
            $row = $glanceCheck->fetch_assoc();
            $gid = (int)$row['id'];
            $stmt = $conn->prepare("UPDATE association_glance SET overview = ?, founded_year = ?, members_count = ?, projects_count = ?, events_count = ? WHERE id = ?");
            $stmt->bind_param('siiiii', $g_overview, $g_year, $g_members, $g_projects, $g_events, $gid);
        } else {
            $stmt = $conn->prepare("INSERT INTO association_glance (overview, founded_year, members_count, projects_count, events_count) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('siiii', $g_overview, $g_year, $g_members, $g_projects, $g_events);
        }

        if ($stmt->execute()) {
            $glance_success = 'Association at a Glance updated successfully.';
            $glance_overview = $g_overview;
            $glance_founded_year = $g_year;
            $glance_members = $g_members;
            $glance_projects = $g_projects;
            $glance_events = $g_events;
        } else {
            $glance_error = 'Failed to save Association at a Glance.';
        }
        $stmt->close();
    }
}

// Handle Core Values form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['values_action'])) {

    $v_action = $_POST['values_action'];
    $v_id = isset($_POST['values_id']) ? (int)$_POST['values_id'] : 0;
    $v_title = trim($_POST['values_title'] ?? '');
    $v_description = trim($_POST['values_description'] ?? '');
    $v_sort_order = isset($_POST['values_sort_order']) ? (int)$_POST['values_sort_order'] : 1;
    if ($v_sort_order < 1) {
        $v_sort_order = 1;
    }


    if ($v_action !== 'delete' && ($v_title === '' || $v_description === '')) {
        $values_error = 'Title and description are required for each core value.';
    } else {
        if ($v_action === 'add') {
            $stmt = $conn->prepare("INSERT INTO core_values (title, description, sort_order) VALUES (?, ?, ?)");
            $stmt->bind_param('ssi', $v_title, $v_description, $v_sort_order);
            if ($stmt->execute()) {
                $values_success = 'Core value added successfully.';
            } else {
                $values_error = 'Failed to add core value.';
            }
            $stmt->close();
        } elseif ($v_action === 'edit' && $v_id > 0) {
            $stmt = $conn->prepare("UPDATE core_values SET title = ?, description = ?, sort_order = ? WHERE id = ?");
            $stmt->bind_param('ssii', $v_title, $v_description, $v_sort_order, $v_id);
            if ($stmt->execute()) {
                $values_success = 'Core value updated successfully.';
            } else {
                $values_error = 'Failed to update core value.';
            }
            $stmt->close();
        } elseif ($v_action === 'delete' && $v_id > 0) {
            // Archive instead of hard delete
            $stmtSel = $conn->prepare("SELECT title, description, sort_order, created_at FROM core_values WHERE id = ?");
            $stmtSel->bind_param('i', $v_id);
            if ($stmtSel->execute()) {
                $resultSel = $stmtSel->get_result();
                if ($row = $resultSel->fetch_assoc()) {
                    $stmtArch = $conn->prepare("INSERT INTO core_values_archive (original_id, title, description, sort_order, original_created_at) VALUES (?, ?, ?, ?, ?)");
                    $origCreated = $row['created_at'] ?? null;
                    $stmtArch->bind_param('issis', $v_id, $row['title'], $row['description'], $row['sort_order'], $origCreated);
                    if ($stmtArch->execute()) {
                        $stmtDel = $conn->prepare("DELETE FROM core_values WHERE id = ?");
                        $stmtDel->bind_param('i', $v_id);
                        if ($stmtDel->execute()) {
                            $values_success = 'Core value archived successfully.';
                        } else {
                            $values_error = 'Failed to remove core value after archiving.';
                        }
                        $stmtDel->close();
                    } else {
                        $values_error = 'Failed to archive core value.';
                    }
                    $stmtArch->close();
                } else {
                    $values_error = 'Core value not found for archiving.';
                }
            } else {
                $values_error = 'Failed to load core value for archiving.';
            }
            $stmtSel->close();
        }

    }
}

// Handle Partners & Sponsors form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['partners_action'])) {
    $ps_action = $_POST['partners_action'];
    $ps_id = isset($_POST['partners_id']) ? (int)$_POST['partners_id'] : 0;
    $ps_name = trim($_POST['partners_name'] ?? '');
    $ps_logo = trim($_POST['partners_logo_path'] ?? '');
    $ps_type = trim($_POST['partners_type'] ?? 'partner');
    $ps_sort_order = isset($_POST['partners_sort_order']) ? (int)$_POST['partners_sort_order'] : 1;
    if ($ps_sort_order < 1) {
        $ps_sort_order = 1;
    }

    // Handle logo upload if present
    $uploadDirPartners = __DIR__ . '/../../uploads/partners/';
    if (!is_dir($uploadDirPartners)) {
        @mkdir($uploadDirPartners, 0775, true);
    }
    if (!empty($_FILES['partners_logo']['name']) && $_FILES['partners_logo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['partners_logo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if (in_array($ext, $allowed, true)) {
            $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', $_FILES['partners_logo']['name']);
            $destPath = $uploadDirPartners . $safeName;
            if (move_uploaded_file($_FILES['partners_logo']['tmp_name'], $destPath)) {
                // store relative path used on frontend
                $ps_logo = 'uploads/partners/' . $safeName;
            }
        }
    }

    if ($ps_action !== 'delete' && ($ps_name === '' || $ps_logo === '')) {
        $partners_error = 'Name and logo are required for each partner or sponsor.';
    } else {
        if ($ps_type !== 'partner' && $ps_type !== 'sponsor') {
            $ps_type = 'partner';
        }

        if ($ps_action === 'add') {
            $stmt = $conn->prepare("INSERT INTO partners_sponsors (name, logo_path, type, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('sssi', $ps_name, $ps_logo, $ps_type, $ps_sort_order);
            if ($stmt->execute()) {
                $partners_success = 'Partner / sponsor added successfully.';
            } else {
                $partners_error = 'Failed to add partner / sponsor.';
            }
            $stmt->close();
        } elseif ($ps_action === 'edit' && $ps_id > 0) {
            $stmt = $conn->prepare("UPDATE partners_sponsors SET name = ?, logo_path = ?, type = ?, sort_order = ? WHERE id = ?");
            $stmt->bind_param('sssii', $ps_name, $ps_logo, $ps_type, $ps_sort_order, $ps_id);
            if ($stmt->execute()) {
                $partners_success = 'Partner / sponsor updated successfully.';
            } else {
                $partners_error = 'Failed to update partner / sponsor.';
            }
            $stmt->close();
        } elseif ($ps_action === 'delete' && $ps_id > 0) {
            // Archive instead of hard delete
            $stmtSel = $conn->prepare("SELECT name, logo_path, type, sort_order, created_at FROM partners_sponsors WHERE id = ?");
            $stmtSel->bind_param('i', $ps_id);
            if ($stmtSel->execute()) {
                $resultSel = $stmtSel->get_result();
                if ($row = $resultSel->fetch_assoc()) {
                    $stmtArch = $conn->prepare("INSERT INTO partners_sponsors_archive (original_id, name, logo_path, type, sort_order, original_created_at) VALUES (?, ?, ?, ?, ?, ?)");
                    $origCreated = $row['created_at'] ?? null;
                    $stmtArch->bind_param('isssis', $ps_id, $row['name'], $row['logo_path'], $row['type'], $row['sort_order'], $origCreated);
                    if ($stmtArch->execute()) {
                        $stmtDel = $conn->prepare("DELETE FROM partners_sponsors WHERE id = ?");
                        $stmtDel->bind_param('i', $ps_id);
                        if ($stmtDel->execute()) {
                            $partners_success = 'Partner / sponsor archived successfully.';
                        } else {
                            $partners_error = 'Failed to remove partner / sponsor after archiving.';
                        }
                        $stmtDel->close();
                    } else {
                        $partners_error = 'Failed to archive partner / sponsor.';
                    }
                    $stmtArch->close();
                } else {
                    $partners_error = 'Partner / sponsor not found for archiving.';
                }
            } else {
                $partners_error = 'Failed to load partner / sponsor for archiving.';
            }
            $stmtSel->close();
        }

    }
}

// Handle Homepage Carousel form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['carousel_action'])) {
    $c_action = $_POST['carousel_action'];
    $c_id = isset($_POST['carousel_id']) ? (int)($_POST['carousel_id']) : 0;
    $c_title = trim($_POST['carousel_title'] ?? '');
    $c_subtitle = trim($_POST['carousel_subtitle'] ?? '');
    $c_image_path = trim($_POST['carousel_image_path'] ?? '');
    $c_primary_label = trim($_POST['carousel_primary_label'] ?? 'Learn More');
    $c_primary_link = trim($_POST['carousel_primary_link'] ?? 'about_us.php');
    $c_secondary_label = trim($_POST['carousel_secondary_label'] ?? 'Join Us');
    $c_secondary_link = trim($_POST['carousel_secondary_link'] ?? 'contact_us.php');
    $c_sort_order = isset($_POST['carousel_sort_order']) ? (int)$_POST['carousel_sort_order'] : 1;
    if ($c_sort_order < 1) {
        $c_sort_order = 1;
    }

    // Handle carousel background image upload if present
    $uploadDirCarousel = __DIR__ . '/../../uploads/carousel/';
    if (!is_dir($uploadDirCarousel)) {
        @mkdir($uploadDirCarousel, 0775, true);
    }
    if (!empty($_FILES['carousel_image']['name']) && $_FILES['carousel_image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['carousel_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if (in_array($ext, $allowed, true)) {
            $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', $_FILES['carousel_image']['name']);
            $destPath = $uploadDirCarousel . $safeName;
            if (move_uploaded_file($_FILES['carousel_image']['tmp_name'], $destPath)) {
                // store relative path used on frontend
                $c_image_path = 'uploads/carousel/' . $safeName;
            }
        }
    }

    if ($c_action !== 'delete' && ($c_title === '' || $c_subtitle === '' || $c_image_path === '')) {
        $carousel_error = 'Title, subtitle, and background image are required for each slide.';
    } else {
        if ($c_action === 'add') {
            $stmt = $conn->prepare("INSERT INTO home_carousel_slides (title, subtitle, image_path, primary_button_label, primary_button_link, secondary_button_label, secondary_button_link, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssssi', $c_title, $c_subtitle, $c_image_path, $c_primary_label, $c_primary_link, $c_secondary_label, $c_secondary_link, $c_sort_order);
            if ($stmt->execute()) {
                $carousel_success = 'Carousel slide added successfully.';
            } else {
                $carousel_error = 'Failed to add carousel slide.';
            }
            $stmt->close();
        } elseif ($c_action === 'edit' && $c_id > 0) {
            $stmt = $conn->prepare("UPDATE home_carousel_slides SET title = ?, subtitle = ?, image_path = ?, primary_button_label = ?, primary_button_link = ?, secondary_button_label = ?, secondary_button_link = ?, sort_order = ? WHERE id = ?");
            $stmt->bind_param('sssssssii', $c_title, $c_subtitle, $c_image_path, $c_primary_label, $c_primary_link, $c_secondary_label, $c_secondary_link, $c_sort_order, $c_id);
            if ($stmt->execute()) {
                $carousel_success = 'Carousel slide updated successfully.';
            } else {
                $carousel_error = 'Failed to update carousel slide.';
            }
            $stmt->close();
        } elseif ($c_action === 'delete' && $c_id > 0) {
            // Archive instead of hard delete
            $stmtSel = $conn->prepare("SELECT title, subtitle, image_path, primary_button_label, primary_button_link, secondary_button_label, secondary_button_link, sort_order, created_at FROM home_carousel_slides WHERE id = ?");
            $stmtSel->bind_param('i', $c_id);
            if ($stmtSel->execute()) {
                $resultSel = $stmtSel->get_result();
                if ($row = $resultSel->fetch_assoc()) {
                    $stmtArch = $conn->prepare("INSERT INTO home_carousel_slides_archive (original_id, title, subtitle, image_path, primary_button_label, primary_button_link, secondary_button_label, secondary_button_link, sort_order, original_created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $origCreated = $row['created_at'] ?? null;
                    $stmtArch->bind_param('issssssiss', $c_id, $row['title'], $row['subtitle'], $row['image_path'], $row['primary_button_label'], $row['primary_button_link'], $row['secondary_button_label'], $row['secondary_button_link'], $row['sort_order'], $origCreated);
                    if ($stmtArch->execute()) {
                        $stmtDel = $conn->prepare("DELETE FROM home_carousel_slides WHERE id = ?");
                        $stmtDel->bind_param('i', $c_id);
                        if ($stmtDel->execute()) {
                            $carousel_success = 'Carousel slide archived successfully.';
                        } else {
                            $carousel_error = 'Failed to remove carousel slide after archiving.';
                        }
                        $stmtDel->close();
                    } else {
                        $carousel_error = 'Failed to archive carousel slide.';
                    }
                    $stmtArch->close();
                } else {
                    $carousel_error = 'Carousel slide not found for archiving.';
                }
            } else {
                $carousel_error = 'Failed to load carousel slide for archiving.';
            }
            $stmtSel->close();
        }

    }
}




// Fetch Featured Programs
$featured_programs = [];

$resultPrograms = $conn->query("SELECT * FROM featured_programs ORDER BY sort_order ASC, created_at ASC");
if ($resultPrograms && $resultPrograms->num_rows > 0) {
    while ($row = $resultPrograms->fetch_assoc()) {
        $featured_programs[] = $row;
    }
}

// Compute next default sort order for programs
$next_program_order = 1;
if (!empty($featured_programs)) {
    $maxOrder = 0;
    foreach ($featured_programs as $p) {
        if ((int)$p['sort_order'] > $maxOrder) {
            $maxOrder = (int)$p['sort_order'];
        }
    }
    $next_program_order = $maxOrder + 1;
}

// Fetch Mission & Vision (single record)
$mission_text = '';
$vision_text = '';
$mvResult = $conn->query("SELECT * FROM mission_vision ORDER BY id ASC LIMIT 1");
if ($mvResult && $mvResult->num_rows > 0) {
    $mvRow = $mvResult->fetch_assoc();
    $mission_text = $mvRow['mission'];
    $vision_text = $mvRow['vision'];
}

// Fetch Core Values
$core_values = [];
$valuesResult = $conn->query("SELECT * FROM core_values ORDER BY sort_order ASC, created_at ASC");
if ($valuesResult && $valuesResult->num_rows > 0) {
    while ($row = $valuesResult->fetch_assoc()) {
        $core_values[] = $row;
    }
}

// Compute next default sort order for core values
$next_values_order = 1;
if (!empty($core_values)) {
    $maxOrder = 0;
    foreach ($core_values as $v) {
        if ((int)$v['sort_order'] > $maxOrder) {
            $maxOrder = (int)$v['sort_order'];
        }
    }
    $next_values_order = $maxOrder + 1;
}

// Fetch Association at a Glance
$glance_overview = '';
$glance_founded_year = '';
$glance_members = '';
$glance_projects = '';
$glance_events = '';

$glanceResult = $conn->query("SELECT * FROM association_glance ORDER BY id ASC LIMIT 1");
if ($glanceResult && $glanceResult->num_rows > 0) {
    $glanceRow = $glanceResult->fetch_assoc();
    $glance_overview = $glanceRow['overview'];
    $glance_founded_year = $glanceRow['founded_year'];
    $glance_members = $glanceRow['members_count'];
    $glance_projects = $glanceRow['projects_count'];
    $glance_events = $glanceRow['events_count'];
}

// Fetch Partners & Sponsors
$partners_list = [];
$partnersResult = $conn->query("SELECT * FROM partners_sponsors ORDER BY sort_order ASC, created_at ASC");
if ($partnersResult && $partnersResult->num_rows > 0) {
    while ($row = $partnersResult->fetch_assoc()) {
        $partners_list[] = $row;
    }
}

// Compute next default sort order for partners
$next_partners_order = 1;
if (!empty($partners_list)) {
    $maxOrder = 0;
    foreach ($partners_list as $ps) {
        if ((int)$ps['sort_order'] > $maxOrder) {
            $maxOrder = (int)$ps['sort_order'];
        }
    }
    $next_partners_order = $maxOrder + 1;
}

// Fetch Homepage Carousel slides
$carousel_slides = [];
$carouselResult = $conn->query("SELECT * FROM home_carousel_slides ORDER BY sort_order ASC, created_at ASC");
if ($carouselResult && $carouselResult->num_rows > 0) {
    while ($row = $carouselResult->fetch_assoc()) {
        $carousel_slides[] = $row;
    }
}

// Compute next default sort order for carousel slides
$next_carousel_order = 1;
if (!empty($carousel_slides)) {
    $maxOrder = 0;
    foreach ($carousel_slides as $cs) {
        if ((int)$cs['sort_order'] > $maxOrder) {
            $maxOrder = (int)$cs['sort_order'];
        }
    }
    $next_carousel_order = $maxOrder + 1;
}

// Handle Resources (downloadable resources for Resources page)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resources_action'])) {
    $r_action = $_POST['resources_action'];
    $r_id = isset($_POST['resources_id']) ? (int)$_POST['resources_id'] : 0;
    $r_title = trim($_POST['resources_title'] ?? '');
    $r_file_key = trim($_POST['resources_file_key'] ?? '');
    $r_icon_class = trim($_POST['resources_icon_class'] ?? '');
    $r_color_hex = trim($_POST['resources_color_hex'] ?? '#0d6efd');
    $r_sort_order = isset($_POST['resources_sort_order']) ? (int)$_POST['resources_sort_order'] : 1;
    $r_is_active = isset($_POST['resources_is_active']) ? 1 : 0;

    if ($r_sort_order < 1) {
        $r_sort_order = 1;
    }

    if ($r_action !== 'delete' && ($r_title === '' || $r_file_key === '')) {
        $resources_error = 'Title and File Key are required for each resource.';
    } else {
        if ($r_action === 'add') {
            $stmt = $conn->prepare("INSERT INTO downloadable_resources (file_key, title, icon_class, color_hex, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssii', $r_file_key, $r_title, $r_icon_class, $r_color_hex, $r_sort_order, $r_is_active);
            if ($stmt->execute()) {
                $resources_success = 'Resource added successfully.';
            } else {
                $resources_error = 'Failed to add resource.';
            }
            $stmt->close();
        } elseif ($r_action === 'edit' && $r_id > 0) {
            $stmt = $conn->prepare("UPDATE downloadable_resources SET file_key = ?, title = ?, icon_class = ?, color_hex = ?, sort_order = ?, is_active = ? WHERE id = ?");
            // file_key (s), title (s), icon_class (s), color_hex (s), sort_order (i), is_active (i), id (i)
            $stmt->bind_param('ssssiii', $r_file_key, $r_title, $r_icon_class, $r_color_hex, $r_sort_order, $r_is_active, $r_id);
            if ($stmt->execute()) {
                $resources_success = 'Resource updated successfully.';
            } else {
                $resources_error = 'Failed to update resource.';
            }
            $stmt->close();
        } elseif ($r_action === 'delete' && $r_id > 0) {
            // Archive instead of hard delete
            $stmtSel = $conn->prepare("SELECT file_key, title, icon_class, color_hex, sort_order, is_active, created_at FROM downloadable_resources WHERE id = ?");
            $stmtSel->bind_param('i', $r_id);
            if ($stmtSel->execute()) {
                $resultSel = $stmtSel->get_result();
                if ($row = $resultSel->fetch_assoc()) {
                    $stmtArch = $conn->prepare("INSERT INTO downloadable_resources_archive (original_id, file_key, title, icon_class, color_hex, sort_order, is_active, original_created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $origCreated = $row['created_at'] ?? null;
                    $stmtArch->bind_param('issssiis', $r_id, $row['file_key'], $row['title'], $row['icon_class'], $row['color_hex'], $row['sort_order'], $row['is_active'], $origCreated);
                    if ($stmtArch->execute()) {
                        $stmtDel = $conn->prepare("DELETE FROM downloadable_resources WHERE id = ?");
                        $stmtDel->bind_param('i', $r_id);
                        if ($stmtDel->execute()) {
                            $resources_success = 'Resource archived successfully.';
                        } else {
                            $resources_error = 'Failed to remove resource after archiving.';
                        }
                        $stmtDel->close();
                    } else {
                        $resources_error = 'Failed to archive resource.';
                    }
                    $stmtArch->close();
                } else {
                    $resources_error = 'Resource not found for archiving.';
                }
            } else {
                $resources_error = 'Failed to load resource for archiving.';
            }
            $stmtSel->close();
        }


    }
}

// Fetch Resources list
$resources_list = [];
$resourcesResult = $conn->query("SELECT * FROM downloadable_resources ORDER BY sort_order ASC, created_at ASC");
if ($resourcesResult && $resourcesResult->num_rows > 0) {
    while ($row = $resourcesResult->fetch_assoc()) {
        $resources_list[] = $row;
    }
}

// Compute next sort order for resources
$next_resources_order = 1;
if (!empty($resources_list)) {
    $maxOrder = 0;
    foreach ($resources_list as $res) {
        if ((int)$res['sort_order'] > $maxOrder) {
            $maxOrder = (int)$res['sort_order'];
        }
    }
    $next_resources_order = $maxOrder + 1;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>About the Association Content | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}

.main-content {
    margin-left: 250px;
    padding: 32px;
    min-height: 100vh;
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2);
    color: white;
}

.page-header h2 {
    font-weight: 700;
    margin: 0 0 10px 0;
    font-size: 2rem;
}

.page-header p {
    margin: 0;
    opacity: 0.95;
    font-size: 1.05rem;
}

.content-card {
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    padding: 24px 24px 28px;
}

.content-card h5 {
    font-weight: 600;
    color: #111827;
}

.nav-pills .nav-link {
    border-radius: 999px;
    padding: 8px 18px;
    font-weight: 500;
    color: #4b5563;
}

.nav-pills .nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
    font-size: 0.9rem;
}

.form-control {
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.12);
    outline: none;
}

.section-hint {
    font-size: 0.85rem;
    color: #6b7280;
}

.badge-section-key {
    background: rgba(102, 126, 234, 0.08);
    color: #4f46e5;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 0.75rem;
    font-weight: 500;
}

@media (max-width: 991.98px) {
    .main-content {
        margin-left: 0;
        padding: 16px;
    }
}
</style>
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <h2><i class="bi bi-globe2 me-2"></i>About the Association Content</h2>
        <p class="mb-0">Draft layout only (no backend yet) for managing your public "About" sections.</p>
    </div>

    <div class="container-fluid">
        <div class="content-card mb-3">
            <!-- Tabs -->
            <ul class="nav nav-pills mb-3" id="aboutTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($activeTab === 'who') ? 'active' : '' ?>" id="who-tab" data-bs-toggle="tab" data-bs-target="#who" type="button" role="tab">
                        Who We Are
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($activeTab === 'mv') ? 'active' : '' ?>" id="mv-tab" data-bs-toggle="tab" data-bs-target="#missionVision" type="button" role="tab">
                        Mission &amp; Vision
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($activeTab === 'values') ? 'active' : '' ?>" id="values-tab" data-bs-toggle="tab" data-bs-target="#coreValues" type="button" role="tab">
                        Core Values
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($activeTab === 'glance') ? 'active' : '' ?>" id="glance-tab" data-bs-toggle="tab" data-bs-target="#atGlance" type="button" role="tab">
                        Association at a Glance
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($activeTab === 'programs') ? 'active' : '' ?>" id="programs-tab" data-bs-toggle="tab" data-bs-target="#featuredPrograms" type="button" role="tab">
                        Featured Programs
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($activeTab === 'carousel') ? 'active' : '' ?>" id="carousel-tab" data-bs-toggle="tab" data-bs-target="#homepageCarousel" type="button" role="tab">
                        Homepage Carousel
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($activeTab === 'partners') ? 'active' : '' ?>" id="partners-tab" data-bs-toggle="tab" data-bs-target="#partnersSection" type="button" role="tab">
                        Partners &amp; Sponsors
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($activeTab === 'resources') ? 'active' : '' ?>" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resourcesSection" type="button" role="tab">
                        Resources
                    </button>
                </li>
            </ul>




            <div class="tab-content" id="aboutTabsContent">
                <!-- Who We Are -->
                <div class="tab-pane fade <?= ($activeTab === 'who') ? 'show active' : '' ?>" id="who" role="tabpanel" aria-labelledby="who-tab">
                    <h5 class="mb-2">Who We Are</h5>
                    <p class="section-hint mb-3">Manage the history and introduction content that appears on your public site.</p>
                    <span class="badge-section-key mb-3 d-inline-block">section_key: who_we_are</span>

                    <?php if ($who_error): ?>
                        <div class="alert alert-danger py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($who_error) ?>
                        </div>
                    <?php elseif ($who_success): ?>
                        <div class="alert alert-success py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($who_success) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add / Edit Form -->
                    <form method="POST" enctype="multipart/form-data" class="mb-4" id="whoForm">
                        <input type="hidden" name="who_action" id="who_action" value="add">
                        <input type="hidden" name="id" id="who_id" value="0">
                        <div class="mb-3 mt-2">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" id="who_title" class="form-control" placeholder="e.g. Who We Are">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Main Content</label>
                            <textarea name="content" id="who_content" class="form-control" rows="6" placeholder="Describe your association..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image (optional)</label>
                            <input type="file" name="image" id="who_image" class="form-control" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary" id="who_submit_btn">
                            <i class="bi bi-plus-circle me-1"></i><span id="who_submit_text">Add Entry</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2 d-none" id="who_cancel_btn" onclick="resetWhoForm()">
                            Cancel
                        </button>
                    </form>

                    <!-- Existing Entries -->
                    <?php if (!empty($who_entries)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 20%;">Title</th>
                                        <th>Content</th>
                                        <th style="width: 15%;" class="text-center">Image</th>
                                        <th style="width: 15%;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($who_entries as $index => $entry): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($entry['title']) ?></td>
                                            <td style="max-width: 320px;">
                                                <div style="font-size:0.9rem;" class="text-truncate" title="<?= htmlspecialchars($entry['content']) ?>">
                                                    <?= nl2br(htmlspecialchars(mb_strimwidth($entry['content'], 0, 160, '...'))) ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($entry['image'])): ?>
                                                    <img src="../../uploads/who_we_are/<?= htmlspecialchars($entry['image']) ?>" alt="Preview" style="max-height:60px; border-radius:6px;">
                                                <?php else: ?>
                                                    <span class="text-muted small">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary me-1"
                                                        onclick="startWhoEdit(<?= (int)$entry['id'] ?>, '<?= htmlspecialchars($entry['title'], ENT_QUOTES) ?>', '<?= htmlspecialchars($entry['content'], ENT_QUOTES) ?>')">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form method="POST" class="d-inline-block" onsubmit="return confirm('Delete this entry?');">
                                                    <input type="hidden" name="who_action" value="delete">
                                                    <input type="hidden" name="id" value="<?= (int)$entry['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small">No entries yet. Use the form above to add the first "Who We Are" content.</p>
                    <?php endif; ?>
                </div>

                <!-- Mission & Vision -->
                <div class="tab-pane fade <?= ($activeTab === 'mv') ? 'show active' : '' ?>" id="missionVision" role="tabpanel" aria-labelledby="mv-tab">
                    <h5 class="mb-2">Mission &amp; Vision</h5>
                    <p class="section-hint mb-3">Separate mission and vision statements that will show on your public About page.</p>
                    <span class="badge-section-key mb-2 d-inline-block">section_key: mission_vision</span>

                    <?php if ($mv_error): ?>
                        <div class="alert alert-danger py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($mv_error) ?>
                        </div>
                    <?php elseif ($mv_success): ?>
                        <div class="alert alert-success py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($mv_success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="mt-2">
                        <input type="hidden" name="mv_action" value="save">

                        <div class="mb-3 mt-2">
                            <label class="form-label">Mission</label>
                            <textarea name="mv_mission" class="form-control" rows="4" placeholder="Write your mission statement here..."><?= htmlspecialchars($mission_text) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vision</label>
                            <textarea name="mv_vision" class="form-control" rows="4" placeholder="Write your vision statement here..."><?= htmlspecialchars($vision_text) ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save Mission &amp; Vision
                        </button>
                    </form>
                </div>

                <!-- Core Values -->
                <div class="tab-pane fade <?= ($activeTab === 'values') ? 'show active' : '' ?>" id="coreValues" role="tabpanel" aria-labelledby="values-tab">
                    <h5 class="mb-2">Core Values</h5>
                    <p class="section-hint mb-3">Manage the core values that describe the character of your association.</p>
                    <span class="badge-section-key mb-2 d-inline-block">section_key: core_values</span>

                    <?php if ($values_error): ?>
                        <div class="alert alert-danger py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($values_error) ?>
                        </div>
                    <?php elseif ($values_success): ?>
                        <div class="alert alert-success py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($values_success) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add / Edit Core Value Form -->
                    <form method="POST" class="mb-4" id="valuesForm">
                        <input type="hidden" name="values_action" id="values_action" value="add">
                        <input type="hidden" name="values_id" id="values_id" value="0">

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Value Title</label>
                                <input type="text" name="values_title" id="values_title" class="form-control" placeholder="e.g. Integrity">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="values_sort_order" id="values_sort_order" class="form-control" min="1" value="<?= (int)$next_values_order ?>">


                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Short Description</label>
                            <textarea name="values_description" id="values_description" class="form-control" rows="4" placeholder="Describe what this value means for your association..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3" id="values_submit_btn">
                            <i class="bi bi-plus-circle me-1"></i><span id="values_submit_text">Add Core Value</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2 mt-3 d-none" id="values_cancel_btn" onclick="resetValuesForm()">
                            Cancel
                        </button>
                    </form>

                    <!-- Existing Core Values -->
                    <?php if (!empty($core_values)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 25%;">Title</th>
                                        <th>Description</th>
                                        <th style="width: 10%;">Order</th>
                                        <th style="width: 15%;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($core_values as $index => $value): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($value['title']) ?></td>
                                            <td style="max-width: 320px;">
                                                <div style="font-size:0.9rem;" class="text-truncate" title="<?= htmlspecialchars($value['description']) ?>">
                                                    <?= nl2br(htmlspecialchars(mb_strimwidth($value['description'], 0, 160, '...'))) ?>
                                                </div>
                                            </td>
                                            <td><?= (int)$value['sort_order'] ?></td>
                                            <td class="text-center">
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary me-1"
                                                        onclick="startValuesEdit(<?= (int)$value['id'] ?>,
                                                            '<?= htmlspecialchars($value['title'], ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($value['description'], ENT_QUOTES) ?>',
                                                            <?= (int)$value['sort_order'] ?>)">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form method="POST" class="d-inline-block" onsubmit="return confirm('Delete this core value?');">
                                                    <input type="hidden" name="values_action" value="delete">
                                                    <input type="hidden" name="values_id" value="<?= (int)$value['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small">No core values yet. Use the form above to add your first core value.</p>
                    <?php endif; ?>
                </div>


                <!-- Association at a Glance -->
                <div class="tab-pane fade <?= ($activeTab === 'glance') ? 'show active' : '' ?>" id="atGlance" role="tabpanel" aria-labelledby="glance-tab">
                    <h5 class="mb-2">Association at a Glance</h5>
                    <p class="section-hint mb-3">Set the short overview and key numbers that will appear on the About Us page.</p>
                    <span class="badge-section-key mb-2 d-inline-block">section_key: association_glance</span>

                    <?php if ($glance_error): ?>
                        <div class="alert alert-danger py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($glance_error) ?>
                        </div>
                    <?php elseif ($glance_success): ?>
                        <div class="alert alert-success py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($glance_success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="mt-2">
                        <input type="hidden" name="glance_action" value="save">

                        <div class="mb-3 mt-2">
                            <label class="form-label">Overview Content</label>
                            <textarea name="glance_overview" class="form-control" rows="4" placeholder="Example: Founded in 2009, 250+ members, community projects and events..."><?= htmlspecialchars($glance_overview) ?></textarea>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-3">
                                <label class="form-label">Year Founded</label>
                                <input type="number" name="glance_founded_year" class="form-control" min="1" value="<?= htmlspecialchars($glance_founded_year) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Members</label>
                                <input type="number" name="glance_members" class="form-control" min="1" value="<?= htmlspecialchars($glance_members) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Community Projects</label>
                                <input type="number" name="glance_projects" class="form-control" min="1" value="<?= htmlspecialchars($glance_projects) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Events Organized</label>
                                <input type="number" name="glance_events" class="form-control" min="1" value="<?= htmlspecialchars($glance_events) ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="bi bi-save me-1"></i>Save Association at a Glance
                        </button>
                    </form>
                </div>


                <!-- Featured Programs -->
                <div class="tab-pane fade <?= ($activeTab === 'programs') ? 'show active' : '' ?>" id="featuredPrograms" role="tabpanel" aria-labelledby="programs-tab">
                    <h5 class="mb-2">Featured Programs</h5>
                    <p class="section-hint mb-3">Manage the cards shown in the "Featured Programs" section on the homepage.</p>
                    <span class="badge-section-key mb-2 d-inline-block">section_key: featured_programs</span>

                    <?php if ($program_error): ?>
                        <div class="alert alert-danger py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($program_error) ?>
                        </div>
                    <?php elseif ($program_success): ?>
                        <div class="alert alert-success py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($program_success) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add / Edit Featured Program Form -->
                    <form method="POST" class="mb-4" id="programForm">
                        <input type="hidden" name="program_action" id="program_action" value="add">
                        <input type="hidden" name="program_id" id="program_id" value="0">

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Program Title</label>
                                <input type="text" name="program_title" id="program_title" class="form-control" placeholder="e.g. Coastal Clean-up Drives">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Icon</label>
                                <select name="program_icon" id="program_icon" class="form-control">
                                    <option value="">Default icon</option>
                                    <option value="bi-water">Coastal / Sea</option>
                                    <option value="bi-briefcase">Livelihood / Support</option>
                                    <option value="bi-shield-check">Safety & Training</option>
                                    <option value="bi-tree">Environment / Nature</option>
                                </select>
                                <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">Piliin lang kung anong bagay sa program.</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="program_sort_order" id="program_sort_order" class="form-control" min="1" value="<?= (int)$next_program_order ?>">


                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Short Description</label>
                            <textarea name="program_description" id="program_description" class="form-control" rows="4" placeholder="Describe the program..."></textarea>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-4">
                                <label class="form-label">Button Label</label>
                                <input type="text" name="program_button_label" id="program_button_label" class="form-control" value="View Events">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Button Link (URL)</label>
                                <input type="text" name="program_button_link" id="program_button_link" class="form-control" placeholder="e.g. events.php?category=cleanup">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3" id="program_submit_btn">
                            <i class="bi bi-plus-circle me-1"></i><span id="program_submit_text">Add Program</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2 mt-3 d-none" id="program_cancel_btn" onclick="resetProgramForm()">
                            Cancel
                        </button>
                    </form>

                    <!-- Existing Featured Programs -->
                    <?php if (!empty($featured_programs)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 20%;">Title</th>
                                        <th>Description</th>
                                        <th style="width: 15%;">Icon</th>
                                        <th style="width: 10%;">Order</th>
                                        <th style="width: 15%;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($featured_programs as $index => $program): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($program['title']) ?></td>
                                            <td style="max-width: 320px;">
                                                <div style="font-size:0.9rem;" class="text-truncate" title="<?= htmlspecialchars($program['description']) ?>">
                                                    <?= nl2br(htmlspecialchars(mb_strimwidth($program['description'], 0, 160, '...'))) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($program['icon_class'])): ?>
                                                    <code class="small"><?= htmlspecialchars($program['icon_class']) ?></code>
                                                <?php else: ?>
                                                    <span class="text-muted small">Default icon</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= (int)$program['sort_order'] ?></td>
                                            <td class="text-center">
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary me-1"
                                                        onclick="startProgramEdit(<?= (int)$program['id'] ?>,
                                                            '<?= htmlspecialchars($program['title'], ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($program['description'], ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($program['icon_class'] ?? '', ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($program['button_label'] ?? 'View Events', ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($program['button_link'] ?? '', ENT_QUOTES) ?>',
                                                            <?= (int)$program['sort_order'] ?>)">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form method="POST" class="d-inline-block" onsubmit="return confirm('Delete this program?');">
                                                    <input type="hidden" name="program_action" value="delete">
                                                    <input type="hidden" name="program_id" value="<?= (int)$program['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small">No featured programs yet. Use the form above to add your first card.</p>
                    <?php endif; ?>
                </div>

                <!-- Homepage Carousel -->
                <div class="tab-pane fade <?= ($activeTab === 'carousel') ? 'show active' : '' ?>" id="homepageCarousel" role="tabpanel" aria-labelledby="carousel-tab">
                    <h5 class="mb-2">Homepage Hero Carousel</h5>
                    <p class="section-hint mb-3">Manage the large slideshow at the top of the homepage (background image, title, subtitle, and buttons).</p>
                    <span class="badge-section-key mb-2 d-inline-block">section_key: home_carousel_slides</span>

                    <?php if ($carousel_error): ?>
                        <div class="alert alert-danger py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($carousel_error) ?>
                        </div>
                    <?php elseif ($carousel_success): ?>
                        <div class="alert alert-success py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($carousel_success) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add / Edit Carousel Slide Form -->
                    <form method="POST" enctype="multipart/form-data" class="mb-4" id="carouselForm">
                        <input type="hidden" name="carousel_action" id="carousel_action" value="add">
                        <input type="hidden" name="carousel_id" id="carousel_id" value="0">

                        <div class="row g-3 mt-1">
                            <div class="col-md-5">
                                <label class="form-label">Slide Title</label>
                                <input type="text" name="carousel_title" id="carousel_title" class="form-control" placeholder="e.g. Welcome to Our Association">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="carousel_sort_order" id="carousel_sort_order" class="form-control" min="1" value="<?= (int)$next_carousel_order ?>">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Subtitle / Short Description</label>
                            <textarea name="carousel_subtitle" id="carousel_subtitle" class="form-control" rows="3" placeholder="Short text that appears under the title..."></textarea>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-4">
                                <label class="form-label">Background Image</label>
                                <input type="file" name="carousel_image" id="carousel_image" class="form-control" accept="image/*">
                                <small class="text-muted" style="font-size: 0.8rem;">Best with wide images. Auto-upload to <code>uploads/carousel/</code>.</small>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Stored Image Path (read-only)</label>
                                <input type="text" name="carousel_image_path" id="carousel_image_path" class="form-control" readonly placeholder="Will be filled after upload, e.g. uploads/carousel/slide1.jpg">
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Primary Button Label</label>
                                <input type="text" name="carousel_primary_label" id="carousel_primary_label" class="form-control" value="Learn More">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Primary Button Link</label>
                                <input type="text" name="carousel_primary_link" id="carousel_primary_link" class="form-control" value="about_us.php">
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Secondary Button Label</label>
                                <input type="text" name="carousel_secondary_label" id="carousel_secondary_label" class="form-control" value="Join Us">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Secondary Button Link</label>
                                <input type="text" name="carousel_secondary_link" id="carousel_secondary_link" class="form-control" value="contact_us.php">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3" id="carousel_submit_btn">
                            <i class="bi bi-plus-circle me-1"></i><span id="carousel_submit_text">Add Slide</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2 mt-3 d-none" id="carousel_cancel_btn" onclick="resetCarouselForm()">
                            Cancel
                        </button>
                    </form>

                    <!-- Existing Carousel Slides -->
                    <?php if (!empty($carousel_slides)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 20%;">Title</th>
                                        <th>Subtitle</th>
                                        <th style="width: 20%;">Background</th>
                                        <th style="width: 10%;">Order</th>
                                        <th style="width: 15%;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($carousel_slides as $index => $slide): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($slide['title']) ?></td>
                                            <td style="max-width: 260px;">
                                                <div class="small text-truncate" title="<?= htmlspecialchars($slide['subtitle']) ?>">
                                                    <?= nl2br(htmlspecialchars(mb_strimwidth($slide['subtitle'], 0, 120, '...'))) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div style="width: 120px;">
                                                        <div style="background-image: url('../../<?= htmlspecialchars($slide['image_path']) ?>'); background-size: cover; background-position: center; height: 60px; border-radius: 8px; border:1px solid #e5e7eb;"></div>
                                                    </div>
                                                    <div class="small text-muted text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($slide['image_path']) ?>">
                                                        <?= htmlspecialchars($slide['image_path']) ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= (int)$slide['sort_order'] ?></td>
                                            <td class="text-center">
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary me-1"
                                                        onclick="startCarouselEdit(<?= (int)$slide['id'] ?>,
                                                            '<?= htmlspecialchars($slide['title'], ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($slide['subtitle'], ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($slide['image_path'], ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($slide['primary_button_label'] ?? 'Learn More', ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($slide['primary_button_link'] ?? 'about_us.php', ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($slide['secondary_button_label'] ?? 'Join Us', ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($slide['secondary_button_link'] ?? 'contact_us.php', ENT_QUOTES) ?>',
                                                            <?= (int)$slide['sort_order'] ?>)">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form method="POST" class="d-inline-block" onsubmit="return confirm('Delete this slide?');">
                                                    <input type="hidden" name="carousel_action" value="delete">
                                                    <input type="hidden" name="carousel_id" value="<?= (int)$slide['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small">No carousel slides yet. Use the form above to add your first slide.</p>
                    <?php endif; ?>
                </div>


                <!-- Partners & Sponsors -->
                <div class="tab-pane fade <?= ($activeTab === 'partners') ? 'show active' : '' ?>" id="partnersSection" role="tabpanel" aria-labelledby="partners-tab">
                    <h5 class="mb-2">Partners &amp; Sponsors</h5>
                    <p class="section-hint mb-3">Manage the logos and labels shown in the "Our Partners &amp; Sponsors" section on the homepage.</p>
                    <span class="badge-section-key mb-2 d-inline-block">section_key: partners_sponsors</span>

                    <?php if ($partners_error): ?>
                        <div class="alert alert-danger py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($partners_error) ?>
                        </div>
                    <?php elseif ($partners_success): ?>
                        <div class="alert alert-success py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($partners_success) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add / Edit Partner / Sponsor Form -->
                    <form method="POST" enctype="multipart/form-data" class="mb-4" id="partnersForm">
                        <input type="hidden" name="partners_action" id="partners_action" value="add">
                        <input type="hidden" name="partners_id" id="partners_id" value="0">

                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">Name</label>
                                <input type="text" name="partners_name" id="partners_name" class="form-control" placeholder="e.g. Municipality of Olongapo City">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Type</label>
                                <select name="partners_type" id="partners_type" class="form-control">
                                    <option value="partner">Partner</option>
                                    <option value="sponsor">Sponsor</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="partners_sort_order" id="partners_sort_order" class="form-control" min="1" value="<?= (int)$next_partners_order ?>">
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-4">
                                <label class="form-label">Logo Image</label>
                                <input type="file" name="partners_logo" id="partners_logo" class="form-control" accept="image/*">
                                <small class="text-muted" style="font-size: 0.8rem;">Allowed: JPG, PNG, GIF. Auto-upload to <code>uploads/partners/</code>.</small>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Stored Logo Path (read-only)</label>
                                <input type="text" name="partners_logo_path" id="partners_logo_path" class="form-control" readonly placeholder="Will be filled after upload, e.g. uploads/partners/logo.png">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3" id="partners_submit_btn">
                            <i class="bi bi-plus-circle me-1"></i><span id="partners_submit_text">Add Partner / Sponsor</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2 mt-3 d-none" id="partners_cancel_btn" onclick="resetPartnersForm()">
                            Cancel
                        </button>
                    </form>


                    <!-- Existing Partners / Sponsors -->
                    <?php if (!empty($partners_list)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 20%;">Name</th>
                                        <th style="width: 15%;">Type</th>
                                        <th style="width: 25%;">Logo</th>
                                        <th style="width: 10%;">Order</th>
                                        <th style="width: 15%;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($partners_list as $index => $ps): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($ps['name']) ?></td>
                                            <td>
                                                <?php if ($ps['type'] === 'sponsor'): ?>
                                                    <span class="badge bg-warning text-dark">Sponsor</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info text-dark">Partner</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div style="width: 90px;">
                                                        <img src="../../<?= htmlspecialchars($ps['logo_path']) ?>" alt="<?= htmlspecialchars($ps['name']) ?>" style="max-height: 60px;" class="img-fluid rounded border">
                                                    </div>
                                                    <div class="small text-muted text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($ps['logo_path']) ?>">
                                                        <?= htmlspecialchars($ps['logo_path']) ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= (int)$ps['sort_order'] ?></td>
                                            <td class="text-center">
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary me-1"
                                                        onclick="startPartnersEdit(<?= (int)$ps['id'] ?>,
                                                            '<?= htmlspecialchars($ps['name'], ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($ps['logo_path'], ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($ps['type'], ENT_QUOTES) ?>',
                                                            <?= (int)$ps['sort_order'] ?>)">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form method="POST" class="d-inline-block" onsubmit="return confirm('Delete this partner / sponsor?');">
                                                    <input type="hidden" name="partners_action" value="delete">
                                                    <input type="hidden" name="partners_id" value="<?= (int)$ps['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small">No partners or sponsors yet. Use the form above to add your first logo.</p>
                    <?php endif; ?>
                </div>

                <!-- Resources -->
                <div class="tab-pane fade <?= ($activeTab === 'resources') ? 'show active' : '' ?>" id="resourcesSection" role="tabpanel" aria-labelledby="resources-tab">
                    <h5 class="mb-2">Resources (Downloadable Forms)</h5>
                    <p class="section-hint mb-3">Manage the items shown on the public Resources page (links to PDF generator).</p>
                    <span class="badge-section-key mb-2 d-inline-block">section_key: downloadable_resources</span>

                    <?php if ($resources_error): ?>
                        <div class="alert alert-danger py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($resources_error) ?>
                        </div>
                    <?php elseif ($resources_success): ?>
                        <div class="alert alert-success py-2 px-3 mb-3 small">
                            <?= htmlspecialchars($resources_success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="mb-4" id="resourcesForm">
                        <input type="hidden" name="resources_action" id="resources_action" value="add">
                        <input type="hidden" name="resources_id" id="resources_id" value="0">

                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">Title</label>
                                <input type="text" name="resources_title" id="resources_title" class="form-control" placeholder="e.g. Membership Form">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Resource Type</label>
                                <select name="resources_file_key" id="resources_file_key" class="form-control">
                                    <option value="">Select resource type...</option>
                                    <option value="membership_form">Membership Form</option>
                                    <option value="event_guidelines">Event Guidelines</option>
                                    <option value="attendance_sheet">Attendance Sheet</option>
                                    <option value="officers_list">Officers List</option>
                                </select>
                                <small class="text-muted" style="font-size:0.8rem;">Piliin lang kung anong klaseng form ito.</small>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="resources_sort_order" id="resources_sort_order" class="form-control" min="1" value="<?= (int)$next_resources_order ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="resources_is_active" name="resources_is_active" checked>
                                    <label class="form-check-label" for="resources_is_active">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-3 d-none">
                            <div class="col-md-4">
                                <label class="form-label">Icon Class (Bootstrap Icons)</label>
                                <input type="text" name="resources_icon_class" id="resources_icon_class" class="form-control" placeholder="e.g. bi-file-earmark-person">
                                <small class="text-muted" style="font-size:0.8rem;">Leave blank for default icon.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color Hex</label>
                                <input type="text" name="resources_color_hex" id="resources_color_hex" class="form-control" value="#0d6efd" placeholder="#0d6efd">
                            </div>
                        </div>


                        <button type="submit" class="btn btn-primary mt-3" id="resources_submit_btn">
                            <i class="bi bi-plus-circle me-1"></i><span id="resources_submit_text">Add Resource</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2 mt-3 d-none" id="resources_cancel_btn" onclick="resetResourcesForm()">
                            Cancel
                        </button>
                    </form>

                    <?php if (!empty($resources_list)): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width:5%;">#</th>
                                        <th style="width:20%;">Title</th>
                                        <th style="width:15%;">File Key</th>
                                        <th style="width:20%;">Icon</th>
                                        <th style="width:10%;">Color</th>
                                        <th style="width:10%;">Order</th>
                                        <th style="width:10%;">Active</th>
                                        <th style="width:10%;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resources_list as $index => $res): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($res['title']) ?></td>
                                            <td><code class="small"><?= htmlspecialchars($res['file_key']) ?></code></td>
                                            <td>
                                                <?php if (!empty($res['icon_class'])): ?>
                                                    <code class="small"><?= htmlspecialchars($res['icon_class']) ?></code>
                                                <?php else: ?>
                                                    <span class="text-muted small">Default</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge" style="background: <?= htmlspecialchars($res['color_hex']) ?>;">&nbsp;</span>
                                                <span class="small text-muted ms-1"><?= htmlspecialchars($res['color_hex']) ?></span>
                                            </td>
                                            <td><?= (int)$res['sort_order'] ?></td>
                                            <td>
                                                <?php if ((int)$res['is_active'] === 1): ?>
                                                    <span class="badge bg-success">Yes</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                    onclick="startResourcesEdit(<?= (int)$res['id'] ?>,
                                                        '<?= htmlspecialchars($res['title'], ENT_QUOTES) ?>',
                                                        '<?= htmlspecialchars($res['file_key'], ENT_QUOTES) ?>',
                                                        '<?= htmlspecialchars($res['icon_class'], ENT_QUOTES) ?>',
                                                        '<?= htmlspecialchars($res['color_hex'], ENT_QUOTES) ?>',
                                                        <?= (int)$res['sort_order'] ?>,
                                                        <?= (int)$res['is_active'] ?>)">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form method="POST" class="d-inline-block" onsubmit="return confirm('Delete this resource?');">
                                                    <input type="hidden" name="resources_action" value="delete">
                                                    <input type="hidden" name="resources_id" value="<?= (int)$res['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small">No resources configured yet. Use the form above to add your first downloadable item.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>



    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function startWhoEdit(id, title, content) {
    const actionInput = document.getElementById('who_action');
    const idInput = document.getElementById('who_id');
    const titleInput = document.getElementById('who_title');
    const contentInput = document.getElementById('who_content');
    const submitBtn = document.getElementById('who_submit_btn');
    const submitText = document.getElementById('who_submit_text');
    const cancelBtn = document.getElementById('who_cancel_btn');

    if (!actionInput || !idInput || !titleInput || !contentInput) return;

    actionInput.value = 'edit';
    idInput.value = id;
    titleInput.value = title;
    contentInput.value = content;

    submitBtn.classList.remove('btn-primary');
    submitBtn.classList.add('btn-warning');
    submitText.textContent = 'Update Entry';
    cancelBtn.classList.remove('d-none');

    const whoSection = document.getElementById('who');
    if (whoSection) {
        window.scrollTo({ top: whoSection.offsetTop - 80, behavior: 'smooth' });
    }
}

function resetWhoForm() {
    const form = document.getElementById('whoForm');
    if (!form) return;

    form.reset();
    document.getElementById('who_action').value = 'add';
    document.getElementById('who_id').value = '0';

    const submitBtn = document.getElementById('who_submit_btn');
    const submitText = document.getElementById('who_submit_text');
    const cancelBtn = document.getElementById('who_cancel_btn');

    submitBtn.classList.remove('btn-warning');
    submitBtn.classList.add('btn-primary');
    submitText.textContent = 'Add Entry';
    cancelBtn.classList.add('d-none');
}

function startProgramEdit(id, title, description, iconClass, buttonLabel, buttonLink, sortOrder) {
    const actionInput = document.getElementById('program_action');
    const idInput = document.getElementById('program_id');
    const titleInput = document.getElementById('program_title');
    const descInput = document.getElementById('program_description');
    const iconInput = document.getElementById('program_icon');
    const labelInput = document.getElementById('program_button_label');
    const linkInput = document.getElementById('program_button_link');
    const orderInput = document.getElementById('program_sort_order');
    const submitBtn = document.getElementById('program_submit_btn');
    const submitText = document.getElementById('program_submit_text');
    const cancelBtn = document.getElementById('program_cancel_btn');

    if (!actionInput || !idInput || !titleInput || !descInput) return;

    actionInput.value = 'edit';
    idInput.value = id;
    titleInput.value = title;
    descInput.value = description;
    iconInput.value = iconClass;
    labelInput.value = buttonLabel;
    linkInput.value = buttonLink;
    orderInput.value = sortOrder;

    submitBtn.classList.remove('btn-primary');
    submitBtn.classList.add('btn-warning');
    submitText.textContent = 'Update Program';
    cancelBtn.classList.remove('d-none');

    const programsTab = document.getElementById('featuredPrograms');
    if (programsTab) {
        window.scrollTo({ top: programsTab.offsetTop - 80, behavior: 'smooth' });
    }
}

function resetProgramForm() {
    const form = document.getElementById('programForm');
    if (!form) return;

    form.reset();
    document.getElementById('program_action').value = 'add';
    document.getElementById('program_id').value = '0';

    const submitBtn = document.getElementById('program_submit_btn');
    const submitText = document.getElementById('program_submit_text');
    const cancelBtn = document.getElementById('program_cancel_btn');

    submitBtn.classList.remove('btn-warning');
    submitBtn.classList.add('btn-primary');
    submitText.textContent = 'Add Program';
    cancelBtn.classList.add('d-none');
}

function startValuesEdit(id, title, description, sortOrder) {
    const actionInput = document.getElementById('values_action');
    const idInput = document.getElementById('values_id');
    const titleInput = document.getElementById('values_title');
    const descInput = document.getElementById('values_description');
    const orderInput = document.getElementById('values_sort_order');
    const submitBtn = document.getElementById('values_submit_btn');
    const submitText = document.getElementById('values_submit_text');
    const cancelBtn = document.getElementById('values_cancel_btn');

    if (!actionInput || !idInput || !titleInput || !descInput) return;

    actionInput.value = 'edit';
    idInput.value = id;
    titleInput.value = title;
    descInput.value = description;
    orderInput.value = sortOrder;

    submitBtn.classList.remove('btn-primary');
    submitBtn.classList.add('btn-warning');
    submitText.textContent = 'Update Core Value';
    cancelBtn.classList.remove('d-none');

    const valuesTab = document.getElementById('coreValues');
    if (valuesTab) {
        window.scrollTo({ top: valuesTab.offsetTop - 80, behavior: 'smooth' });
    }
}

function resetValuesForm() {
    const form = document.getElementById('valuesForm');
    if (!form) return;

    form.reset();
    document.getElementById('values_action').value = 'add';
    document.getElementById('values_id').value = '0';

    const submitBtn = document.getElementById('values_submit_btn');
    const submitText = document.getElementById('values_submit_text');
    const cancelBtn = document.getElementById('values_cancel_btn');

    submitBtn.classList.remove('btn-warning');
    submitBtn.classList.add('btn-primary');
    submitText.textContent = 'Add Core Value';
    cancelBtn.classList.add('d-none');
}

function startPartnersEdit(id, name, logoPath, type, sortOrder) {
    const actionInput = document.getElementById('partners_action');
    const idInput = document.getElementById('partners_id');
    const nameInput = document.getElementById('partners_name');
    const logoInput = document.getElementById('partners_logo_path');
    const typeInput = document.getElementById('partners_type');
    const orderInput = document.getElementById('partners_sort_order');
    const submitBtn = document.getElementById('partners_submit_btn');
    const submitText = document.getElementById('partners_submit_text');
    const cancelBtn = document.getElementById('partners_cancel_btn');

    if (!actionInput || !idInput || !nameInput || !logoInput || !typeInput || !orderInput) return;

    actionInput.value = 'edit';
    idInput.value = id;
    nameInput.value = name;
    logoInput.value = logoPath;
    typeInput.value = type;
    orderInput.value = sortOrder;

    submitBtn.classList.remove('btn-primary');
    submitBtn.classList.add('btn-warning');
    submitText.textContent = 'Update Partner / Sponsor';
    cancelBtn.classList.remove('d-none');

    const partnersTab = document.getElementById('partnersSection');
    if (partnersTab) {
        window.scrollTo({ top: partnersTab.offsetTop - 80, behavior: 'smooth' });
    }
}

function resetPartnersForm() {
    const form = document.getElementById('partnersForm');
    if (!form) return;

    form.reset();
    document.getElementById('partners_action').value = 'add';
    document.getElementById('partners_id').value = '0';

    const submitBtn = document.getElementById('partners_submit_btn');
    const submitText = document.getElementById('partners_submit_text');
    const cancelBtn = document.getElementById('partners_cancel_btn');

    submitBtn.classList.remove('btn-warning');
    submitBtn.classList.add('btn-primary');
    submitText.textContent = 'Add Partner / Sponsor';
    cancelBtn.classList.add('d-none');

    // clear read-only logo path display
    const logoPathInput = document.getElementById('partners_logo_path');
    if (logoPathInput) {
        logoPathInput.value = '';
    }
}

function startResourcesEdit(id, title, fileKey, iconClass, colorHex, sortOrder, isActive) {
    const actionInput = document.getElementById('resources_action');
    const idInput = document.getElementById('resources_id');
    const titleInput = document.getElementById('resources_title');
    const fileKeyInput = document.getElementById('resources_file_key');
    const iconInput = document.getElementById('resources_icon_class');
    const colorInput = document.getElementById('resources_color_hex');
    const orderInput = document.getElementById('resources_sort_order');
    const activeInput = document.getElementById('resources_is_active');
    const submitBtn = document.getElementById('resources_submit_btn');
    const submitText = document.getElementById('resources_submit_text');
    const cancelBtn = document.getElementById('resources_cancel_btn');

    if (!actionInput || !idInput || !titleInput || !fileKeyInput || !orderInput || !activeInput) return;

    actionInput.value = 'edit';
    idInput.value = id;
    titleInput.value = title;
    fileKeyInput.value = fileKey;
    iconInput.value = iconClass;
    colorInput.value = colorHex;
    orderInput.value = sortOrder;
    activeInput.checked = (parseInt(isActive, 10) === 1);

    submitBtn.classList.remove('btn-primary');
    submitBtn.classList.add('btn-warning');
    submitText.textContent = 'Update Resource';
    cancelBtn.classList.remove('d-none');

    const resTab = document.getElementById('resourcesSection');
    if (resTab) {
        window.scrollTo({ top: resTab.offsetTop - 80, behavior: 'smooth' });
    }
}

function resetResourcesForm() {
    const form = document.getElementById('resourcesForm');
    if (!form) return;

    form.reset();
    document.getElementById('resources_action').value = 'add';
    document.getElementById('resources_id').value = '0';

    const submitBtn = document.getElementById('resources_submit_btn');
    const submitText = document.getElementById('resources_submit_text');
    const cancelBtn = document.getElementById('resources_cancel_btn');

    submitBtn.classList.remove('btn-warning');
    submitBtn.classList.add('btn-primary');
    submitText.textContent = 'Add Resource';
    cancelBtn.classList.add('d-none');

    const activeInput = document.getElementById('resources_is_active');
    if (activeInput) {
        activeInput.checked = true;
    }
}

function startCarouselEdit(id, title, subtitle, imagePath, primaryLabel, primaryLink, secondaryLabel, secondaryLink, sortOrder) {

    const actionInput = document.getElementById('carousel_action');
    const idInput = document.getElementById('carousel_id');
    const titleInput = document.getElementById('carousel_title');
    const subtitleInput = document.getElementById('carousel_subtitle');
    const imagePathInput = document.getElementById('carousel_image_path');
    const primaryLabelInput = document.getElementById('carousel_primary_label');
    const primaryLinkInput = document.getElementById('carousel_primary_link');
    const secondaryLabelInput = document.getElementById('carousel_secondary_label');
    const secondaryLinkInput = document.getElementById('carousel_secondary_link');
    const sortOrderInput = document.getElementById('carousel_sort_order');
    const submitBtn = document.getElementById('carousel_submit_btn');
    const submitText = document.getElementById('carousel_submit_text');
    const cancelBtn = document.getElementById('carousel_cancel_btn');

    if (!actionInput || !idInput || !titleInput || !subtitleInput || !imagePathInput || !sortOrderInput) return;

    actionInput.value = 'edit';
    idInput.value = id;
    titleInput.value = title;
    subtitleInput.value = subtitle;
    imagePathInput.value = imagePath;
    primaryLabelInput.value = primaryLabel;
    primaryLinkInput.value = primaryLink;
    secondaryLabelInput.value = secondaryLabel;
    secondaryLinkInput.value = secondaryLink;
    sortOrderInput.value = sortOrder;

    submitBtn.classList.remove('btn-primary');
    submitBtn.classList.add('btn-warning');
    submitText.textContent = 'Update Slide';
    cancelBtn.classList.remove('d-none');

    const carouselTab = document.getElementById('homepageCarousel');
    if (carouselTab) {
        window.scrollTo({ top: carouselTab.offsetTop - 80, behavior: 'smooth' });
    }
}

function resetCarouselForm() {
    const form = document.getElementById('carouselForm');
    if (!form) return;

    form.reset();
    document.getElementById('carousel_action').value = 'add';
    document.getElementById('carousel_id').value = '0';

    const submitBtn = document.getElementById('carousel_submit_btn');
    const submitText = document.getElementById('carousel_submit_text');
    const cancelBtn = document.getElementById('carousel_cancel_btn');

    submitBtn.classList.remove('btn-warning');
    submitBtn.classList.add('btn-primary');
    submitText.textContent = 'Add Slide';
    cancelBtn.classList.add('d-none');

    const imagePathInput = document.getElementById('carousel_image_path');
    if (imagePathInput) {
        imagePathInput.value = '';
    }
}

</script>
</body>
</html>





