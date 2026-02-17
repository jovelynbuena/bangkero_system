<?php
// This partial expects: $pagedBackups, $page, $totalPages, $totalFiltered, $showingFrom, $showingTo,
// $search, $sort, $dateFrom, $dateTo, $minSize, $maxSize
?>

<!-- Filters Bar -->
<div class="mb-3">
    <form class="row g-2 align-items-end" id="backupFilterForm" onsubmit="return false;">
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label mb-1" for="backupSearch" style="font-size:12px;">Search by filename</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="backupSearch" placeholder="e.g. backup_2026-02-17" value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-secondary btn-sm" type="button" id="backupSearchBtn">
                    Search
                </button>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label mb-1" style="font-size:12px;">Date range</label>
            <div class="d-flex gap-1 flex-wrap">
                <input type="date" class="form-control form-control-sm backup-filter-input flex-grow-1" id="backupDateFrom" value="<?= htmlspecialchars($dateFrom) ?>">
                <input type="date" class="form-control form-control-sm backup-filter-input flex-grow-1" id="backupDateTo" value="<?= htmlspecialchars($dateTo) ?>">
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label mb-1" style="font-size:12px;">Size (MB)</label>
            <div class="d-flex gap-1 flex-wrap">
                <input type="number" min="0" step="0.1" class="form-control form-control-sm backup-filter-input flex-grow-1" id="backupMinSize" placeholder="Min" value="<?= htmlspecialchars($minSize) ?>">
                <input type="number" min="0" step="0.1" class="form-control form-control-sm backup-filter-input flex-grow-1" id="backupMaxSize" placeholder="Max" value="<?= htmlspecialchars($maxSize) ?>">
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mt-2 mt-md-0">
            <label class="form-label mb-1" style="font-size:12px;">Sort by</label>
            <select class="form-select form-select-sm backup-filter-input" id="backupSort">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest first</option>
                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest first</option>
                <option value="largest" <?= $sort === 'largest' ? 'selected' : '' ?>>Largest size</option>
                <option value="smallest" <?= $sort === 'smallest' ? 'selected' : '' ?>>Smallest size</option>
            </select>
        </div>
    </form>
</div>

<?php if ($totalFiltered > 0): ?>
    <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:12px; color:#6c757d;">
        <div>
            Showing <strong><?= $showingFrom ?></strong>–<strong><?= $showingTo ?></strong> of <strong><?= $totalFiltered ?></strong> backups
        </div>
    </div>

    <div class="backup-list">
        <?php foreach ($pagedBackups as $backup): ?>
            <div class="backup-item">
                <div class="backup-icon-wrapper">
                    <i class="bi bi-database"></i>
                </div>
                <div class="backup-details">
                    <div class="backup-filename">
                        <i class="bi bi-file-earmark-zip"></i>
                        <?= htmlspecialchars($backup['name']) ?>
                    </div>
                    <div class="backup-meta">
                        <div class="backup-meta-item">
                            <i class="bi bi-calendar3"></i>
                            <?= date('M j, Y - g:i A', $backup['date']) ?>
                        </div>
                        <div class="backup-meta-item">
                            <i class="bi bi-hdd"></i>
                            <?= formatFileSize($backup['size']) ?>
                        </div>
                    </div>
                </div>
                <div class="backup-actions">
                    <button type="button" 
                            class="btn-action-small btn-success-custom" 
                            onclick="confirmRestoreFromBackup('<?= htmlspecialchars($backup['name'], ENT_QUOTES) ?>')">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Restore
                    </button>
                    <a href="download_backup.php?file=<?= urlencode($backup['name']) ?>" 
                       class="btn-action-small btn-download-small">
                        <i class="bi bi-download"></i>
                        Download
                    </a>
                    <button type="button" 
                            class="btn-action-small btn-delete-small" 
                            onclick="confirmDelete('<?= htmlspecialchars($backup['name'], ENT_QUOTES) ?>')">
                        <i class="bi bi-trash"></i>
                        Delete
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-2" style="font-size:12px;">
            <div></div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <button class="page-link" type="button" data-page="<?= max(1, $page-1) ?>">&laquo; Prev</button>
                    </li>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                            <button class="page-link" type="button" data-page="<?= $p ?>"><?= $p ?></button>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <button class="page-link" type="button" data-page="<?= min($totalPages, $page+1) ?>">Next &raquo;</button>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bi bi-inbox"></i>
        </div>
        <h4 class="empty-state-title">No Backups Found</h4>
        <p class="empty-state-text">
            No backups match your current filters.<br>
            Try adjusting the search or date range.
        </p>
    </div>
<?php endif; ?>
