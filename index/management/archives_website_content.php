<?php
session_start();

if (empty($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

// ------------ Restore handlers for website content archives ------------
// Who We Are
if (isset($_GET['restore_who'])) {
    $aid = (int)$_GET['restore_who'];
    if ($aid > 0) {
        try {
            $conn->begin_transaction();
            $stmt = $conn->prepare("INSERT INTO who_we_are (id, title, content, image, created_at)
                                    SELECT original_id, title, content, image, original_created_at
                                    FROM who_we_are_archive WHERE archive_id = ?");
            $stmt->bind_param('i', $aid);
            $stmt->execute();
            $stmt->close();

            $stmtDel = $conn->prepare("DELETE FROM who_we_are_archive WHERE archive_id = ?");
            $stmtDel->bind_param('i', $aid);
            $stmtDel->execute();
            $stmtDel->close();

            $conn->commit();
            header('Location: archives_website_content.php?restored_who=1');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            header('Location: archives_website_content.php?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}

// Featured Programs
if (isset($_GET['restore_program'])) {
    $aid = (int)$_GET['restore_program'];
    if ($aid > 0) {
        try {
            $conn->begin_transaction();
            $stmt = $conn->prepare("INSERT INTO featured_programs (id, title, description, icon_class, button_label, button_link, sort_order, created_at)
                                    SELECT original_id, title, description, icon_class, button_label, button_link, sort_order, original_created_at
                                    FROM featured_programs_archive WHERE archive_id = ?");
            $stmt->bind_param('i', $aid);
            $stmt->execute();
            $stmt->close();

            $stmtDel = $conn->prepare("DELETE FROM featured_programs_archive WHERE archive_id = ?");
            $stmtDel->bind_param('i', $aid);
            $stmtDel->execute();
            $stmtDel->close();

            $conn->commit();
            header('Location: archives_website_content.php?restored_program=1');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            header('Location: archives_website_content.php?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}

// Core Values
if (isset($_GET['restore_value'])) {
    $aid = (int)$_GET['restore_value'];
    if ($aid > 0) {
        try {
            $conn->begin_transaction();
            $stmt = $conn->prepare("INSERT INTO core_values (id, title, description, sort_order, created_at)
                                    SELECT original_id, title, description, sort_order, original_created_at
                                    FROM core_values_archive WHERE archive_id = ?");
            $stmt->bind_param('i', $aid);
            $stmt->execute();
            $stmt->close();

            $stmtDel = $conn->prepare("DELETE FROM core_values_archive WHERE archive_id = ?");
            $stmtDel->bind_param('i', $aid);
            $stmtDel->execute();
            $stmtDel->close();

            $conn->commit();
            header('Location: archives_website_content.php?restored_value=1');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            header('Location: archives_website_content.php?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}

// Partners & Sponsors
if (isset($_GET['restore_partner'])) {
    $aid = (int)$_GET['restore_partner'];
    if ($aid > 0) {
        try {
            $conn->begin_transaction();
            $stmt = $conn->prepare("INSERT INTO partners_sponsors (id, name, logo_path, type, sort_order, created_at)
                                    SELECT original_id, name, logo_path, type, sort_order, original_created_at
                                    FROM partners_sponsors_archive WHERE archive_id = ?");
            $stmt->bind_param('i', $aid);
            $stmt->execute();
            $stmt->close();

            $stmtDel = $conn->prepare("DELETE FROM partners_sponsors_archive WHERE archive_id = ?");
            $stmtDel->bind_param('i', $aid);
            $stmtDel->execute();
            $stmtDel->close();

            $conn->commit();
            header('Location: archives_website_content.php?restored_partner=1');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            header('Location: archives_website_content.php?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}

// Homepage Carousel Slides
if (isset($_GET['restore_carousel'])) {
    $aid = (int)$_GET['restore_carousel'];
    if ($aid > 0) {
        try {
            $conn->begin_transaction();
            $stmt = $conn->prepare("INSERT INTO home_carousel_slides (id, title, subtitle, image_path, primary_button_label, primary_button_link, secondary_button_label, secondary_button_link, sort_order, created_at)
                                    SELECT original_id, title, subtitle, image_path, primary_button_label, primary_button_link, secondary_button_label, secondary_button_link, sort_order, original_created_at
                                    FROM home_carousel_slides_archive WHERE archive_id = ?");
            $stmt->bind_param('i', $aid);
            $stmt->execute();
            $stmt->close();

            $stmtDel = $conn->prepare("DELETE FROM home_carousel_slides_archive WHERE archive_id = ?");
            $stmtDel->bind_param('i', $aid);
            $stmtDel->execute();
            $stmtDel->close();

            $conn->commit();
            header('Location: archives_website_content.php?restored_carousel=1');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            header('Location: archives_website_content.php?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}

// Downloadable Resources
if (isset($_GET['restore_resource'])) {
    $aid = (int)$_GET['restore_resource'];
    if ($aid > 0) {
        try {
            $conn->begin_transaction();
            $stmt = $conn->prepare("INSERT INTO downloadable_resources (id, file_key, title, icon_class, color_hex, sort_order, is_active, created_at)
                                    SELECT original_id, file_key, title, icon_class, color_hex, sort_order, is_active, original_created_at
                                    FROM downloadable_resources_archive WHERE archive_id = ?");
            $stmt->bind_param('i', $aid);
            $stmt->execute();
            $stmt->close();

            $stmtDel = $conn->prepare("DELETE FROM downloadable_resources_archive WHERE archive_id = ?");
            $stmtDel->bind_param('i', $aid);
            $stmtDel->execute();
            $stmtDel->close();

            $conn->commit();
            header('Location: archives_website_content.php?restored_resource=1');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            header('Location: archives_website_content.php?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}

// Fetch archived records for each website content section
$who_archived = $conn->query("SELECT * FROM who_we_are_archive ORDER BY archived_at DESC") ?: false;
$programs_archived = $conn->query("SELECT * FROM featured_programs_archive ORDER BY archived_at DESC") ?: false;
$values_archived = $conn->query("SELECT * FROM core_values_archive ORDER BY archived_at DESC") ?: false;
$partners_archived = $conn->query("SELECT * FROM partners_sponsors_archive ORDER BY archived_at DESC") ?: false;
$carousel_archived = $conn->query("SELECT * FROM home_carousel_slides_archive ORDER BY archived_at DESC") ?: false;
$resources_archived = $conn->query("SELECT * FROM downloadable_resources_archive ORDER BY archived_at DESC") ?: false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Archived Website Content | Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
    .main-content { margin-left: 250px; padding: 32px; min-height: 100vh; }
    .page-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 30px; margin-bottom: 30px; box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2); color: white; }
    .section-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 20px; }
    .section-card h5 { font-weight: 600; }
    .table thead th { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; border: none; padding: 10px; font-size: 0.85rem; }
    .badge-pill { border-radius: 50rem; }
    @media (max-width: 991.98px) { .main-content { margin-left: 0; padding: 16px; } }
</style>
</head>
<body>

<?php include('../navbar.php'); ?>

<div class="main-content">
    <div class="page-header">
        <h2><i class="bi bi-archive me-2"></i>Archived Website Content</h2>
        <p class="mb-0">Lahat ng dating "deleted" sa About the Association, naka-save dito para hindi tuluyang mawala.</p>
    </div>

    <?php if (isset($_GET['restored_who']) || isset($_GET['restored_program']) || isset($_GET['restored_value']) || isset($_GET['restored_partner']) || isset($_GET['restored_carousel']) || isset($_GET['restored_resource'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Na-restore na!</strong> Balik na sa active list yung napili mong item.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Oops!</strong> <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>


    <div class="section-card">
        <h5 class="mb-3"><i class="bi bi-people me-2"></i>Who We Are (History Section)</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Created At</th>
                        <th>Archived At</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($who_archived && $who_archived->num_rows > 0): ?>
                    <?php while ($row = $who_archived->fetch_assoc()): ?>
                        <tr>
                            <td class="text-start"><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['original_created_at'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['archived_at'] ?? '') ?></td>
                            <td>
                                <a href="?restore_who=<?= (int)$row['archive_id'] ?>" class="btn btn-sm btn-success">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                </a>
                            </td>
                        </tr>

                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-muted py-3">Wala pang naka-archive na Who We Are entry.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-card">
        <h5 class="mb-3"><i class="bi bi-stars me-2"></i>Featured Programs</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Button Label</th>
                        <th>Order</th>
                        <th>Archived At</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($programs_archived && $programs_archived->num_rows > 0): ?>
                    <?php while ($row = $programs_archived->fetch_assoc()): ?>
                        <tr>
                            <td class="text-start"><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['button_label']) ?></td>
                            <td><?= (int)$row['sort_order'] ?></td>
                            <td><?= htmlspecialchars($row['archived_at'] ?? '') ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-muted py-3">Wala pang naka-archive na Featured Programs.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-card">
        <h5 class="mb-3"><i class="bi bi-heart me-2"></i>Core Values</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Order</th>
                        <th>Archived At</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($values_archived && $values_archived->num_rows > 0): ?>
                    <?php while ($row = $values_archived->fetch_assoc()): ?>
                        <tr>
                            <td class="text-start"><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= (int)$row['sort_order'] ?></td>
                            <td><?= htmlspecialchars($row['archived_at'] ?? '') ?></td>
                            <td>
                                <a href="?restore_value=<?= (int)$row['archive_id'] ?>" class="btn btn-sm btn-success">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                </a>
                            </td>
                        </tr>

                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-muted py-3">Wala pang naka-archive na Core Values.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-card">
        <h5 class="mb-3"><i class="bi bi-people-fill me-2"></i>Partners & Sponsors</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Order</th>
                        <th>Archived At</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($partners_archived && $partners_archived->num_rows > 0): ?>
                    <?php while ($row = $partners_archived->fetch_assoc()): ?>
                        <tr>
                            <td class="text-start"><?= htmlspecialchars($row['name']) ?></td>
                            <td><span class="badge badge-pill bg-secondary text-capitalize"><?= htmlspecialchars($row['type']) ?></span></td>
                            <td><?= (int)$row['sort_order'] ?></td>
                            <td><?= htmlspecialchars($row['archived_at'] ?? '') ?></td>
                            <td>
                                <a href="?restore_partner=<?= (int)$row['archive_id'] ?>" class="btn btn-sm btn-success">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                </a>
                            </td>
                        </tr>

                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-muted py-3">Wala pang naka-archive na Partners / Sponsors.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-card">
        <h5 class="mb-3"><i class="bi bi-images me-2"></i>Homepage Carousel Slides</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Subtitle</th>
                        <th>Order</th>
                        <th>Archived At</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($carousel_archived && $carousel_archived->num_rows > 0): ?>
                    <?php while ($row = $carousel_archived->fetch_assoc()): ?>
                        <tr>
                            <td class="text-start"><?= htmlspecialchars($row['title']) ?></td>
                            <td class="text-start text-truncate" style="max-width: 320px;"><?= htmlspecialchars($row['subtitle']) ?></td>
                            <td><?= (int)$row['sort_order'] ?></td>
                            <td><?= htmlspecialchars($row['archived_at'] ?? '') ?></td>
                            <td>
                                <a href="?restore_carousel=<?= (int)$row['archive_id'] ?>" class="btn btn-sm btn-success">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                </a>
                            </td>
                        </tr>

                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-muted py-3">Wala pang naka-archive na Carousel slides.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-card mb-5">
        <h5 class="mb-3"><i class="bi bi-file-earmark-arrow-down me-2"></i>Downloadable Resources</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>File Key</th>
                        <th>Active?</th>
                        <th>Archived At</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($resources_archived && $resources_archived->num_rows > 0): ?>
                    <?php while ($row = $resources_archived->fetch_assoc()): ?>
                        <tr>
                            <td class="text-start"><?= htmlspecialchars($row['title']) ?></td>
                            <td><code class="small"><?= htmlspecialchars($row['file_key']) ?></code></td>
                            <td>
                                <?php if ((int)$row['is_active'] === 1): ?>
                                    <span class="badge bg-success">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['archived_at'] ?? '') ?></td>
                            <td>
                                <a href="?restore_resource=<?= (int)$row['archive_id'] ?>" class="btn btn-sm btn-success">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                </a>
                            </td>
                        </tr>

                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-muted py-3">Wala pang naka-archive na Resources.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
