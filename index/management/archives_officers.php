<?php
session_start();

if (empty($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

$userId = $_SESSION['user_id'] ?? 0;

// Handle restore from archive
if (isset($_GET['retrieve']) && $userId) {
    $oid = intval($_GET['retrieve']);
    if ($oid > 0) {
        // Fetch officer info before restoring
        $stmt_get = $conn->prepare("
            SELECT o.id, m.name AS member_name 
            FROM officers_archive o 
            JOIN members m ON o.member_id = m.id 
            WHERE o.id = ?
        ");
        $stmt_get->bind_param("i", $oid);
        $stmt_get->execute();
        $res_get = $stmt_get->get_result();
        $officer = $res_get->fetch_assoc();
        $stmt_get->close();

        // Restore officer
        $stmt_insert = $conn->prepare("
            INSERT INTO officers (member_id, role_id, term_start, term_end, image)
            SELECT member_id, role_id, term_start, term_end, image
            FROM officers_archive
            WHERE id = ?
        ");
        $stmt_insert->bind_param("i", $oid);

        if ($stmt_insert->execute()) {
            $stmt_insert->close();

            // Delete from archive
            $stmt_delete = $conn->prepare("DELETE FROM officers_archive WHERE id = ?");
            $stmt_delete->bind_param("i", $oid);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Log action
            $actionText = "Restored officer: {$officer['member_name']}";
            $stmt_log = $conn->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)");
            $stmt_log->bind_param("iss", $userId, $actionText, $actionText);
            $stmt_log->execute();
            $stmt_log->close();

            // Redirect to active officers list with success message
            header('Location: officerslist.php?retrieved=1');
            exit();
        } else {
            $stmt_insert->close();
            header('Location: officerslist.php?error=1');
            exit();
        }
    }
}

// Optional search
$search = $_GET['search'] ?? '';
$search_safe = $conn->real_escape_string($search);

$sql = "
    SELECT 
        o.id,
        o.term_start,
        o.term_end,
        o.image,
        r.role_name AS position,
        m.name AS member_name
    FROM officers_archive o
    JOIN members m ON o.member_id = m.id
    JOIN officer_roles r ON o.role_id = r.id
    WHERE m.name LIKE '%$search_safe%' OR r.role_name LIKE '%$search_safe%'
    ORDER BY r.role_name ASC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Archived Officers | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

.search-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.search-box {
    position: relative;
}

.search-box input {
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px 120px 12px 16px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.search-box input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

.search-box button {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
    padding: 8px 20px;
    color: white;
    transition: all 0.3s ease;
}

.search-box button:hover {
    transform: translateY(-50%) scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.table-container {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.table thead th {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    padding: 16px;
    border: none;
}

.table tbody tr {
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background-color: #f8f9ff;
    transform: scale(1.01);
}

.table tbody td {
    padding: 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f3f4f6;
}

.officer-img { 
    width: 60px; 
    height: 60px; 
    object-fit: cover; 
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.btn-restore {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    color: white;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-restore:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    color: white;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #9ca3af;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
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
    <div class="page-header">
        <h2><i class="bi bi-archive-fill me-2"></i>Archived Officers</h2>
        <p>View and restore archived officer records</p>
    </div>

    <!-- Search Section -->
    <div class="search-card">
        <form method="GET" action="archives_officers.php">
            <div class="search-box">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search by name or position..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit">
                    <i class="bi bi-search me-1"></i>Search
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Photo</th>
                        <th>Member Name</th>
                        <th>Position</th>
                        <th class="text-center">Term Start</th>
                        <th class="text-center">Term End</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php $count = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><strong><?= $count++ ?></strong></td>
                                <td class="text-center">
                                    <?php if (!empty($row['image'])): ?>
                                        <img src="../../uploads/<?= htmlspecialchars($row['image']) ?>" class="officer-img" alt="Officer">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/60x60?text=No+Image" class="officer-img" alt="No Image">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['member_name']) ?></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($row['position']) ?></span></td>
                                <td class="text-center"><?= ($row['term_start'] !== "0000-00-00") ? date('M d, Y', strtotime($row['term_start'])) : 'N/A' ?></td>
                                <td class="text-center"><?= ($row['term_end'] !== "0000-00-00") ? date('M d, Y', strtotime($row['term_end'])) : 'N/A' ?></td>
                                <td class="text-center">
                                    <button class="btn-restore" onclick="confirmRetrieve(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['member_name'])) ?>')">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h5>No Archived Officers</h5>
                                    <p>There are no archived officers to display.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Live Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    const tableRows = document.querySelectorAll('tbody tr');
    
    if (searchInput && tableRows.length > 0) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            
            tableRows.forEach(row => {
                // Skip empty state row
                if (row.querySelector('.empty-state')) {
                    return;
                }
                
                const memberName = row.cells[2]?.textContent.toLowerCase() || '';
                const position = row.cells[3]?.textContent.toLowerCase() || '';
                
                if (memberName.includes(searchTerm) || position.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide empty state
            const tbody = document.querySelector('tbody');
            const existingEmptyRow = tbody.querySelector('.empty-state-search');
            
            if (visibleCount === 0 && searchTerm !== '') {
                if (!existingEmptyRow) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.className = 'empty-state-search';
                    emptyRow.innerHTML = `
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="bi bi-search"></i>
                                <h5>No Results Found</h5>
                                <p>No officers match your search for "<strong>${searchTerm}</strong>"</p>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(emptyRow);
                }
            } else {
                if (existingEmptyRow) {
                    existingEmptyRow.remove();
                }
            }
        });
    }
});

function confirmRetrieve(id, name) {
    Swal.fire({
        title: 'Restore Officer?',
        html: `Are you sure you want to restore <strong>${name}</strong> back to active officers list?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-arrow-counterclockwise"></i> Yes, Restore',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'archives_officers.php?retrieve=' + id;
        }
    });
}

<?php if (isset($_GET['retrieved'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Officer Restored!',
        text: 'The officer has been successfully restored to active list.',
        timer: 2500,
        showConfirmButton: false
    });
<?php endif; ?>
</script>

</body>
</html>
