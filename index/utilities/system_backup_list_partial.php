<?php
// This partial expects: $sysPagedBackups, $sysPage, $sysTotalPages, $sysTotalFiltered,
// $sysShowingFrom, $sysShowingTo, $sysSearch, $sysSort, $sysDateFrom, $sysDateTo, $sysMinSize, $sysMaxSize
?>

<!-- Filters Bar -->
<div class="mb-3">
    <form class="row g-2 align-items-end" id="sysBackupFilterForm" onsubmit="return false;">
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label mb-1" for="sysBackupSearch" style="font-size:12px;">Search by filename</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="sysBackupSearch" placeholder="e.g. system_backup_2026" value="<?= htmlspecialchars($sysSearch) ?>">
                <button class="btn btn-outline-secondary btn-sm" type="button" id="sysBackupSearchBtn">
                    Search
                </button>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label mb-1" style="font-size:12px;">Date range</label>
            <div class="d-flex gap-1 flex-wrap">
                <input type="date" class="form-control form-control-sm sys-backup-filter-input flex-grow-1" id="sysBackupDateFrom" value="<?= htmlspecialchars($sysDateFrom) ?>">
                <input type="date" class="form-control form-control-sm sys-backup-filter-input flex-grow-1" id="sysBackupDateTo" value="<?= htmlspecialchars($sysDateTo) ?>">
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label mb-1" style="font-size:12px;">Size (MB)</label>
            <div class="d-flex gap-1 flex-wrap">
                <input type="number" min="0" step="0.1" class="form-control form-control-sm sys-backup-filter-input flex-grow-1" id="sysBackupMinSize" placeholder="Min" value="<?= htmlspecialchars($sysMinSize) ?>">
                <input type="number" min="0" step="0.1" class="form-control form-control-sm sys-backup-filter-input flex-grow-1" id="sysBackupMaxSize" placeholder="Max" value="<?= htmlspecialchars($sysMaxSize) ?>">
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mt-2 mt-md-0">
            <label class="form-label mb-1" style="font-size:12px;">Sort by</label>
            <select class="form-select form-select-sm sys-backup-filter-input" id="sysBackupSort">
                <option value="newest"  <?= $sysSort === 'newest'   ? 'selected' : '' ?>>Newest first</option>
                <option value="oldest"  <?= $sysSort === 'oldest'   ? 'selected' : '' ?>>Oldest first</option>
                <option value="largest" <?= $sysSort === 'largest'  ? 'selected' : '' ?>>Largest size</option>
                <option value="smallest"<?= $sysSort === 'smallest' ? 'selected' : '' ?>>Smallest size</option>
            </select>
        </div>
    </form>
</div>

<?php if ($sysTotalFiltered > 0): ?>
    <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:12px; color:#6c757d;">
        <div>
            Showing <strong><?= $sysShowingFrom ?></strong>–<strong><?= $sysShowingTo ?></strong> of <strong><?= $sysTotalFiltered ?></strong> system backups
        </div>
    </div>

    <div class="backup-list">
        <?php foreach ($sysPagedBackups as $backup): ?>
            <div class="backup-item">
                <div class="backup-icon-wrapper" style="background: linear-gradient(135deg,#1B4F72,#2E86AB);">
                    <i class="bi bi-file-zip"></i>
                </div>
                <div class="backup-details">
                    <div class="backup-filename">
                        <i class="bi bi-file-zip"></i>
                        <?= htmlspecialchars($backup['name']) ?>
                        <span class="badge ms-1" style="font-size:10px; background:#1B4F72;">System ZIP</span>
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
                            onclick="confirmRestoreZipFromHistory('<?= htmlspecialchars($backup['name'], ENT_QUOTES) ?>')">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Restore
                    </button>
                    <button type="button"
                            class="btn-action-small btn-download-small"
                            onclick="triggerBackupDownload('<?= htmlspecialchars($backup['name'], ENT_QUOTES) ?>')">
                        <i class="bi bi-download"></i>
                        Download
                    </button>
                    <button type="button"
                            class="btn-action-small btn-delete-small"
                            onclick="confirmDeleteZip('<?= htmlspecialchars($backup['name'], ENT_QUOTES) ?>')">
                        <i class="bi bi-trash"></i>
                        Delete
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($sysTotalPages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-2" style="font-size:12px;">
            <div></div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $sysPage <= 1 ? 'disabled' : '' ?>">
                        <button class="page-link" type="button" data-sys-page="<?= max(1, $sysPage-1) ?>">&laquo; Prev</button>
                    </li>
                    <?php for ($p = 1; $p <= $sysTotalPages; $p++): ?>
                        <li class="page-item <?= $p == $sysPage ? 'active' : '' ?>">
                            <button class="page-link" type="button" data-sys-page="<?= $p ?>"><?= $p ?></button>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $sysPage >= $sysTotalPages ? 'disabled' : '' ?>">
                        <button class="page-link" type="button" data-sys-page="<?= min($sysTotalPages, $sysPage+1) ?>">Next &raquo;</button>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bi bi-file-zip"></i>
        </div>
        <h4 class="empty-state-title">No System Backups Found</h4>
        <p class="empty-state-text">
            No system backup ZIP files match your current filters.<br>
            Try adjusting the search or date range.
        </p>
    </div>
<?php endif; ?>
