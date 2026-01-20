<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="bg-blob"></div>

<div class="app-shell">
    <!-- TOP HEADER -->
    <div class="top-header">
        <div class="container-fluid py-2 px-3 d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="brand-pill">
                    <div class="brand-logo"><i class="fa-solid fa-droplet"></i></div>
                    <div>
                        <p class="brand-title"><?php echo esc_html(get_bloginfo('name')); ?></p>
                        <p class="brand-sub"><?php _e('Label Studio 2026', 'swp-label-studio'); ?></p>
                    </div>
                </div>
                <div class="status-pill" id="saveStatus">
                    <span class="status-dot" id="statusDot"></span>
                    <span id="statusText"><?php _e('Saved', 'swp-label-studio'); ?></span>
                    <span class="opacity-50">•</span>
                    <span id="statusTime" class="opacity-75"><?php _e('Just now', 'swp-label-studio'); ?></span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button class="btn btn-light border rounded-pill fw-bold px-3" onclick="showPreview()">
                    <i class="fa-solid fa-eye me-2"></i> <?php _e('Preview', 'swp-label-studio'); ?>
                </button>
                <button class="btn btn-light border rounded-pill fw-bold px-3" onclick="openDrafts()">
                    <i class="fa-solid fa-folder-open me-2"></i> <?php _e('Drafts', 'swp-label-studio'); ?>
                </button>
                <button class="btn btn-light border rounded-pill fw-bold px-3" onclick="openExport()">
                    <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> <?php _e('Export', 'swp-label-studio'); ?>
                </button>
                <button class="btn btn-outline-secondary rounded-pill fw-bold px-3" onclick="openShortcuts()">
                    <i class="fa-regular fa-circle-question me-2"></i> <?php _e('Shortcuts', 'swp-label-studio'); ?>
                </button>
            </div>
        </div>

        <div class="steps-bar">
            <div class="container-fluid px-3">
                <div class="d-flex align-items-center justify-content-between gap-3 overflow-auto">
                    <div class="step-item">
                        <div class="step-icon"><i class="fa-solid fa-check"></i></div> <?php _e('1. Size', 'swp-label-studio'); ?>
                    </div>
                    <div class="step-item">
                        <div class="step-icon"><i class="fa-solid fa-check"></i></div> <?php _e('2. Style', 'swp-label-studio'); ?>
                    </div>
                    <div class="step-item active">
                        <div class="step-icon">3</div> <strong><?php _e('Design Label', 'swp-label-studio'); ?></strong>
                    </div>
                    <div class="step-item">
                        <div class="step-icon">4</div> <?php _e('Review', 'swp-label-studio'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="designer-layout">
        <!-- LEFT PANEL: TOOLBOX -->
        <div class="panel">
            <div class="panel-header">
                <span><i class="fa-solid fa-toolbox me-2"></i> <?php _e('Toolbox', 'swp-label-studio'); ?></span>
                <span class="text-muted" style="font-size:0.75rem; font-weight:800;">v2026</span>
            </div>
            <div class="panel-body">
                <span class="prop-label"><?php _e('Start with a template', 'swp-label-studio'); ?></span>
                <div class="template-scroller">
                    <div class="template-thumb" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);" onclick="loadTemplate('fresh')">
                        <div class="template-label"><?php _e('Fresh', 'swp-label-studio'); ?></div>
                    </div>
                    <div class="template-thumb" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);" onclick="loadTemplate('minimal')">
                        <div class="template-label"><?php _e('Minimal', 'swp-label-studio'); ?></div>
                    </div>
                    <div class="template-thumb" style="background: linear-gradient(135deg, #0f172a 0%, #111827 100%);" onclick="loadTemplate('elite')">
                        <div class="template-label"><?php _e('Elite', 'swp-label-studio'); ?></div>
                    </div>
                    <div class="template-thumb" style="background: linear-gradient(135deg, #0066ff 0%, #00d2ff 100%);" onclick="loadTemplate('aqua')">
                        <div class="template-label"><?php _e('Aqua', 'swp-label-studio'); ?></div>
                    </div>
                    <div class="template-thumb" style="background: radial-gradient(circle at 25% 20%, #34d399, #059669, #064e3b);" onclick="loadTemplate('organic')">
                        <div class="template-label"><?php _e('Organic', 'swp-label-studio'); ?></div>
                    </div>
                    <div class="template-thumb" style="background: linear-gradient(135deg, #f97316, #fb7185);" onclick="loadTemplate('sunset')">
                        <div class="template-label"><?php _e('Sunset', 'swp-label-studio'); ?></div>
                    </div>
                    <div class="template-thumb" style="background: linear-gradient(135deg, #111827, #6d28d9);" onclick="loadTemplate('night')">
                        <div class="template-label"><?php _e('Night', 'swp-label-studio'); ?></div>
                    </div>
                </div>

                <span class="prop-label mt-2"><?php _e('Add elements', 'swp-label-studio'); ?></span>
                <div class="tools-grid">
                    <div class="tool-card" onclick="addText()">
                        <i class="fa-solid fa-font"></i>
                        <span><?php _e('Text', 'swp-label-studio'); ?></span>
                    </div>
                    <div class="tool-card" onclick="document.getElementById('imgInput').click()">
                        <i class="fa-regular fa-image"></i>
                        <span><?php _e('Photo', 'swp-label-studio'); ?></span>
                        <input type="file" id="imgInput" style="display:none" accept="image/*">
                    </div>
                    <div class="tool-card" onclick="addShape('rect')">
                        <i class="fa-regular fa-square"></i>
                        <span><?php _e('Box', 'swp-label-studio'); ?></span>
                    </div>
                    <div class="tool-card" onclick="addShape('circle')">
                        <i class="fa-regular fa-circle"></i>
                        <span><?php _e('Circle', 'swp-label-studio'); ?></span>
                    </div>
                    <div class="tool-card" onclick="showQRCodeModal()">
                        <i class="fa-solid fa-qrcode"></i>
                        <span><?php _e('QR Code', 'swp-label-studio'); ?></span>
                    </div>
                    <div class="tool-card" onclick="showClipArtModal()">
                        <i class="fa-solid fa-icons"></i>
                        <span><?php _e('Clipart', 'swp-label-studio'); ?></span>
                    </div>
                </div>

                <div class="mt-4">
                    <span class="prop-label"><?php _e('Quick align', 'swp-label-studio'); ?></span>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-light border rounded-pill fw-bold" onclick="alignActive('left')">
                            <i class="fa-solid fa-align-left me-2"></i><?php _e('Left', 'swp-label-studio'); ?>
                        </button>
                        <button class="btn btn-light border rounded-pill fw-bold" onclick="alignActive('center')">
                            <i class="fa-solid fa-align-center me-2"></i><?php _e('Center', 'swp-label-studio'); ?>
                        </button>
                        <button class="btn btn-light border rounded-pill fw-bold" onclick="alignActive('right')">
                            <i class="fa-solid fa-align-right me-2"></i><?php _e('Right', 'swp-label-studio'); ?>
                        </button>
                    </div>
                </div>

                <div class="mt-4">
                    <span class="prop-label"><?php _e('Background', 'swp-label-studio'); ?></span>
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold" onclick="setBg('#ffffff')"><?php _e('White', 'swp-label-studio'); ?></button>
                        <button class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold" onclick="setBg('#0f172a')"><?php _e('Ink', 'swp-label-studio'); ?></button>
                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold" onclick="setBg('#0066ff')"><?php _e('Blue', 'swp-label-studio'); ?></button>
                        <input type="color" class="form-control form-control-color" value="#ffffff" oninput="setBg(this.value)" title="<?php esc_attr_e('Pick background color', 'swp-label-studio'); ?>">
                    </div>
                </div>

                <div class="mt-4 hint-card">
                    <?php _e('Tip: Use', 'swp-label-studio'); ?> <span class="kbd">Ctrl</span> + <span class="kbd">S</span> <?php _e('to save a draft, arrows to nudge.', 'swp-label-studio'); ?>
                </div>
            </div>
        </div>

        <!-- CENTER STAGE: CANVAS -->
        <div class="center-stage">
            <div class="action-bar">
                <div class="action-left">
                    <button class="icon-btn" onclick="undo()" title="<?php esc_attr_e('Undo (Ctrl+Z)', 'swp-label-studio'); ?>">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                    <button class="icon-btn" onclick="redo()" title="<?php esc_attr_e('Redo (Ctrl+Shift+Z)', 'swp-label-studio'); ?>">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                    <div class="vr-separator"></div>
                    <button class="icon-btn danger" onclick="deleteObj()" title="<?php esc_attr_e('Delete (Del)', 'swp-label-studio'); ?>">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                    <div class="vr-separator"></div>
                    <button class="icon-btn" id="gridBtn" onclick="toggleGrid()" title="<?php esc_attr_e('Toggle grid (Ctrl+G)', 'swp-label-studio'); ?>">
                        <i class="fa-solid fa-border-all"></i>
                    </button>
                    <button class="icon-btn" id="safeBtn" onclick="toggleSafe()" title="<?php esc_attr_e('Safe area overlay', 'swp-label-studio'); ?>">
                        <i class="fa-solid fa-shield-halved"></i>
                    </button>
                </div>
                <div class="action-right">
                    <button class="btn btn-light border rounded-pill fw-bold px-3" onclick="quickAddBrandBlock()">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i> <?php _e('Brand Block', 'swp-label-studio'); ?>
                    </button>
                    <button class="btn btn-primary rounded-pill fw-bold px-3" onclick="openExport()">
                        <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> <?php _e('Export', 'swp-label-studio'); ?>
                    </button>
                </div>
            </div>

            <div class="canvas-area">
                <div class="canvas-wrapper" id="canvasWrapper">
                    <div class="canvas-card">
                        <canvas id="c"></canvas>
                    </div>
                    <div id="gridOverlay" class="grid-overlay"></div>
                    <div id="safeOverlay" class="safe-overlay"></div>
                </div>
            </div>

            <div class="zoom-bar">
                <button class="pill-btn" onclick="zoomOut()">
                    <i class="fa-solid fa-magnifying-glass-minus me-2"></i><?php _e('Zoom out', 'swp-label-studio'); ?>
                </button>
                <button class="pill-btn" onclick="zoomReset()">
                    <i class="fa-solid fa-rotate me-2"></i><span id="zoomPct">100%</span>
                </button>
                <button class="pill-btn" onclick="zoomIn()">
                    <i class="fa-solid fa-magnifying-glass-plus me-2"></i><?php _e('Zoom in', 'swp-label-studio'); ?>
                </button>
            </div>
        </div>

        <!-- RIGHT PANEL: EDITOR -->
        <div class="panel">
            <div class="panel-header">
                <span><i class="fa-solid fa-sliders me-2"></i> <?php _e('Editor', 'swp-label-studio'); ?></span>
                <span class="text-muted" style="font-size:0.75rem; font-weight:800;" id="selectionBadge">
                    <?php _e('No selection', 'swp-label-studio'); ?>
                </span>
            </div>
            <div class="panel-body">
                <ul class="nav nav-pills mb-3 gap-2" id="editorTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tabProps" type="button" role="tab">
                            <?php _e('Properties', 'swp-label-studio'); ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabLayers" type="button" role="tab">
                            <?php _e('Layers', 'swp-label-studio'); ?>
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- PROPS TAB -->
                    <div class="tab-pane fade show active" id="tabProps" role="tabpanel">
                        <div id="no-selection" class="hint-card">
                            <div class="d-flex align-items-center gap-3">
                                <div class="brand-logo" style="width:44px;height:44px;border-radius:16px;">
                                    <i class="fa-solid fa-arrow-pointer"></i>
                                </div>
                                <div>
                                    <div style="font-weight:900;"><?php _e('Select an element', 'swp-label-studio'); ?></div>
                                    <div style="color:#64748b;"><?php _e('Click any item on the label to edit color, size, fonts, and layering.', 'swp-label-studio'); ?></div>
                                </div>
                            </div>
                        </div>

                        <div id="prop-controls" style="display:none;">
                            <div class="prop-row">
                                <span class="prop-label"><?php _e('Fill', 'swp-label-studio'); ?></span>
                                <input type="color" id="fillColor" class="form-control form-control-color w-100" oninput="updateProp('fill', this.value)">
                            </div>

                            <div id="text-props" style="display:none;">
                                <div class="prop-row">
                                    <span class="prop-label"><?php _e('Font', 'swp-label-studio'); ?></span>
                                    <select class="form-select" id="fontFamily" onchange="updateProp('fontFamily', this.value)">
                                        <option value="Inter">Inter</option>
                                        <option value="Plus Jakarta Sans">Plus Jakarta Sans</option>
                                        <option value="Impact">Impact</option>
                                        <option value="Times New Roman">Times New Roman</option>
                                        <option value="Arial">Arial</option>
                                        <option value="Georgia">Georgia</option>
                                    </select>
                                </div>
                                <div class="prop-row">
                                    <span class="prop-label"><?php _e('Font size', 'swp-label-studio'); ?></span>
                                    <input type="range" class="form-range" id="fontSize" min="8" max="120" step="1" oninput="updateProp('fontSize', this.value)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LAYERS TAB -->
                    <div class="tab-pane fade" id="tabLayers" role="tabpanel">
                        <div id="layersList"></div>
                    </div>
                </div>

<div class="price-box mt-auto">
    <div class="d-flex justify-content-between mb-3">
        <strong><?php _e('Total', 'swp-label-studio'); ?></strong>
        <strong class="text-primary" id="totalPrice">
            <?php 
            // Get product ID from session instead of URL
            if (!WC()->session) {
                WC()->initialize_session();
            }
            $product_id = WC()->session->get('swp_ls_product_id');
            
            if ($product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    echo wc_price($product->get_price());
                } else {
                    echo wc_price(0);
                }
            } else {
                echo wc_price(0);
            }
            ?>
        </strong>
    </div>
    
    <?php if ($product_id): ?>
        <button id="swp-ls-add-to-cart" class="btn btn-success w-100 btn-lg fw-bold mb-2">
            <i class="fa-solid fa-cart-plus me-2"></i>
            <?php _e('Add to Cart', 'swp-label-studio'); ?>
        </button>
    <?php else: ?>
        <div class="alert alert-warning mb-2">
            <small>
                <i class="fa-solid fa-exclamation-triangle me-1"></i>
                <?php _e('Please launch designer from a product page', 'swp-label-studio'); ?>
            </small>
        </div>
    <?php endif; ?>
    
    <button class="btn btn-outline-secondary w-100" onclick="openExport()">
        <i class="fa-solid fa-download me-2"></i>
        <?php _e('Export Design', 'swp-label-studio'); ?>
    </button>
</div>
            </div>
        </div>
    </div>
</div>

<!-- MODALS -->
<?php include 'modals.php'; ?>