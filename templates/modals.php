<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- QR CODE MODAL -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px; overflow:hidden;">
            <div class="modal-header">
                <h5 class="modal-title fs-6 fw-bold"><?php _e( 'Generate QR Code', 'swp-label-studio' ); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="small fw-bold mb-2"><?php _e( 'Website URL', 'swp-label-studio' ); ?></label>
                <input type="text" id="qrInput" class="form-control mb-3" placeholder="https://..." value="<?php echo esc_url( home_url() ); ?>">
                <button class="btn btn-dark w-100 rounded-pill fw-bold" onclick="generateQR()">
                    <?php _e( 'Create QR', 'swp-label-studio' ); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CLIPART MODAL -->
<div class="modal fade" id="clipModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px; overflow:hidden;">
            <div class="modal-header">
                <h5 class="modal-title fs-6 fw-bold"><?php _e( 'Clipart Library', 'swp-label-studio' ); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="clipGrid"></div>
            </div>
        </div>
    </div>
</div>

<!-- EXPORT MODAL -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px; overflow:hidden;">
            <div class="modal-header">
                <h5 class="modal-title fs-6 fw-bold"><?php _e( 'Export', 'swp-label-studio' ); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="hint-card">
                            <div class="fw-bold mb-2"><?php _e( 'Print ready tips', 'swp-label-studio' ); ?></div>
                            <div class="text-muted">
                                <?php _e( 'Keep important text inside the safe area, and extend backgrounds past the bleed line if you want edge to edge color.', 'swp-label-studio' ); ?>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="prop-label"><?php _e( 'Resolution', 'swp-label-studio' ); ?></span>
                            <select class="form-select" id="exportScale">
                                <option value="2"><?php _e( '2x (Recommended)', 'swp-label-studio' ); ?></option>
                                <option value="3"><?php _e( '3x (High)', 'swp-label-studio' ); ?></option>
                                <option value="4"><?php _e( '4x (Ultra)', 'swp-label-studio' ); ?></option>
                            </select>
                        </div>
                        <div class="d-flex gap-2 mt-3 flex-wrap">
                            <button class="btn btn-primary rounded-pill fw-bold px-4" onclick="exportPNG()">
                                <?php _e( 'Download PNG', 'swp-label-studio' ); ?>
                            </button>
                            <button class="btn btn-outline-primary rounded-pill fw-bold px-4" onclick="exportPDF()">
                                <?php _e( 'Download PDF', 'swp-label-studio' ); ?>
                            </button>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <span class="prop-label"><?php _e( 'Preview', 'swp-label-studio' ); ?></span>
                        <div class="p-3 border rounded-4 bg-white">
                            <img id="exportPreview" alt="Preview" style="width:100%; height:auto; border-radius:16px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DRAFTS MODAL -->
<div class="modal fade" id="draftsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px; overflow:hidden;">
            <div class="modal-header">
                <h5 class="modal-title fs-6 fw-bold"><?php _e( 'Drafts', 'swp-label-studio' ); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-2 mb-3 flex-wrap">
                    <button class="btn btn-primary rounded-pill fw-bold px-3" onclick="saveDraftPrompt()">
                        <i class="fa-solid fa-floppy-disk me-2"></i><?php _e( 'Save current draft', 'swp-label-studio' ); ?>
                    </button>
                    <button class="btn btn-outline-danger rounded-pill fw-bold px-3" onclick="clearDrafts()">
                        <?php _e( 'Clear all', 'swp-label-studio' ); ?>
                    </button>
                </div>
                <div id="draftsList" class="row g-3"></div>
                <div class="hint-card mt-3">
                    <?php _e( 'Drafts are stored locally in your browser.', 'swp-label-studio' ); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SHORTCUTS MODAL -->
<div class="modal fade" id="shortcutsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px; overflow:hidden;">
            <div class="modal-header">
                <h5 class="modal-title fs-6 fw-bold"><?php _e( 'Keyboard shortcuts', 'swp-label-studio' ); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="hint-card">
                            <div class="fw-bold mb-2"><?php _e( 'Editing', 'swp-label-studio' ); ?></div>
                            <div class="d-flex justify-content-between">
                                <span><?php _e( 'Undo', 'swp-label-studio' ); ?></span>
                                <span class="kbd">Ctrl+Z</span>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <span><?php _e( 'Delete', 'swp-label-studio' ); ?></span>
                                <span class="kbd">Del</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="hint-card">
                            <div class="fw-bold mb-2"><?php _e( 'Canvas', 'swp-label-studio' ); ?></div>
                            <div class="d-flex justify-content-between">
                                <span><?php _e( 'Toggle grid', 'swp-label-studio' ); ?></span>
                                <span class="kbd">Ctrl+G</span>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <span><?php _e( 'Save draft', 'swp-label-studio' ); ?></span>
                                <span class="kbd">Ctrl+S</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- PREVIEW MODAL -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px; overflow:hidden;">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="modal-title fs-6 fw-bold mb-0"><?php _e('Live Preview', 'swp-label-studio'); ?></h5>
                    <span class="badge bg-success rounded-pill">
                        <i class="fa-solid fa-circle-dot fa-fade"></i> <?php _e('Live', 'swp-label-studio'); ?>
                    </span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 500px; display: flex; align-items: center; justify-content: center;">
                <div style="background: white; padding: 20px; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
                    <img id="previewImage" alt="<?php _e('Preview', 'swp-label-studio'); ?>" style="max-width:100%; max-height: 600px; border-radius: 12px; display: block;">
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div class="text-muted small">
                    <i class="fa-solid fa-info-circle me-1"></i>
                    <?php _e('Preview updates automatically as you edit', 'swp-label-studio'); ?>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <?php _e('Close', 'swp-label-studio'); ?>
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" onclick="openExport()">
                        <i class="fa-solid fa-download me-2"></i><?php _e('Export', 'swp-label-studio'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CLIPART MODAL - Replace in modals.php -->
<div class="modal fade" id="clipModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px; overflow:hidden;">
            <div class="modal-header">
                <h5 class="modal-title fs-6 fw-bold"><?php _e( 'Clipart Library', 'swp-label-studio' ); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
                    <div class="text-muted">
                        <i class="fa-solid fa-hand-pointer me-1"></i>
                        <?php _e( 'Click any icon to add it to your label.', 'swp-label-studio' ); ?>
                    </div>
                    <button class="btn btn-light border rounded-pill fw-bold btn-sm" onclick="addWatermarkStamp()">
                        <i class="fa-solid fa-stamp me-2"></i><?php _e( 'Add "PURE" Stamp', 'swp-label-studio' ); ?>
                    </button>
                </div>
                <div class="row g-3" id="clipGrid"></div>
            </div>
        </div>
    </div>
</div>