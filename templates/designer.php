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
                    
                    <!-- Horizontal Alignment -->
                    <div class="mb-2">
                        <small class="text-muted d-block mb-1"><?php _e('Horizontal', 'swp-label-studio'); ?></small>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-light border rounded-pill fw-bold" onclick="alignActive('left')" title="<?php esc_attr_e('Align Left', 'swp-label-studio'); ?>">
                                <i class="fa-solid fa-align-left me-2"></i><?php _e('Left', 'swp-label-studio'); ?>
                            </button>
                            <button class="btn btn-light border rounded-pill fw-bold" onclick="alignActive('center')" title="<?php esc_attr_e('Align Center', 'swp-label-studio'); ?>">
                                <i class="fa-solid fa-align-center me-2"></i><?php _e('Center', 'swp-label-studio'); ?>
                            </button>
                            <button class="btn btn-light border rounded-pill fw-bold" onclick="alignActive('right')" title="<?php esc_attr_e('Align Right', 'swp-label-studio'); ?>">
                                <i class="fa-solid fa-align-right me-2"></i><?php _e('Right', 'swp-label-studio'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Vertical Alignment -->
                    <div>
                        <small class="text-muted d-block mb-1"><?php _e('Vertical', 'swp-label-studio'); ?></small>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-light border rounded-pill fw-bold" onclick="alignActive('top')" title="<?php esc_attr_e('Align Top', 'swp-label-studio'); ?>">
                                <i class="fa-solid fa-arrow-up me-2"></i><?php _e('Top', 'swp-label-studio'); ?>
                            </button>
                            <button class="btn btn-light border rounded-pill fw-bold" onclick="alignActive('middle')" title="<?php esc_attr_e('Align Middle', 'swp-label-studio'); ?>">
                                <i class="fa-solid fa-grip-lines me-2"></i><?php _e('Middle', 'swp-label-studio'); ?>
                            </button>
                            <button class="btn btn-light border rounded-pill fw-bold" onclick="alignActive('bottom')" title="<?php esc_attr_e('Align Bottom', 'swp-label-studio'); ?>">
                                <i class="fa-solid fa-arrow-down me-2"></i><?php _e('Bottom', 'swp-label-studio'); ?>
                            </button>
                        </div>
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
                    <button class="icon-btn" data-action="undo" title="Undo (Ctrl+Z)"><i class="fa-solid fa-rotate-left"></i></button>
                    <button class="icon-btn" data-action="redo" title="Redo (Ctrl+Shift+Z)"><i class="fa-solid fa-rotate-right"></i></button>
                    <div class="vr-separator"></div>
                    <button class="icon-btn" data-action="copyObj" title="Copy (Ctrl+C)"><i class="fa-regular fa-copy"></i></button>
                    <button class="icon-btn" data-action="pasteObj" title="Paste (Ctrl+V)"><i class="fa-regular fa-clipboard"></i></button>
                    <button class="icon-btn danger" data-action="deleteObj" title="Delete (Del)"><i class="fa-solid fa-trash"></i></button>
                    <div class="vr-separator"></div>
                    <button class="icon-btn" id="gridBtn" data-action="toggleGrid" title="Toggle grid (Ctrl+G)"><i class="fa-solid fa-border-all"></i></button>
                    <button class="icon-btn" id="snapBtn" data-action="toggleSnap" title="Snap on/off"><i class="fa-solid fa-magnet"></i></button>
                    <button class="icon-btn" id="guidesBtn" data-action="toggleGuides" title="Center guides"><i class="fa-solid fa-crosshairs"></i></button>
                    <div class="vr-separator"></div>
                    <button class="icon-btn" id="safeBtn" data-action="toggleSafe" title="Safe area overlay"><i class="fa-solid fa-shield-halved"></i></button>
                    <button class="icon-btn" id="bleedBtn" data-action="toggleBleed" title="Bleed overlay"><i class="fa-solid fa-scissors"></i></button>
                    <div class="vr-separator"></div>
                    <button class="icon-btn" data-action="centerActive" title="Center selected"><i class="fa-solid fa-bullseye"></i></button>
                    <button class="icon-btn" data-action="fitToStage" title="Fit to screen"><i class="fa-solid fa-expand"></i></button>
                    <button class="icon-btn" data-action="resetCanvas" title="Reset canvas"><i class="fa-solid fa-eraser"></i></button>
                </div>
                <div class="action-right">
                    <button class="btn btn-light border rounded-pill fw-bold px-3" data-action="quickAddBrandBlock">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Brand Block
                    </button>
                    <button class="btn btn-primary rounded-pill fw-bold px-3" data-action="openExport">
                        <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Export
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

            <?php
// Ensure WooCommerce session
if ( ! WC()->session ) {
    WC()->initialize_session();
}

// Get product from session
$product_id = WC()->session->get('swp_ls_product_id');
$product    = $product_id ? wc_get_product($product_id) : null;
?>

<div class="price-box mt-auto">

<?php if ( $product && $product->get_id() ): ?>

    <?php
    $is_variable  = $product->is_type('variable');
    $variations   = $is_variable ? $product->get_available_variations() : [];
    $has_variants = ( $is_variable && ! empty($variations) );
    ?>

    <!-- VARIANT SELECTOR (ENHANCED DESIGN) -->
    <div class="mb-3">
        <label class="form-label fw-bold d-flex align-items-center gap-2">
            <i class="fa-solid fa-layer-group text-primary"></i>
            <?php _e('Variant', 'swp-label-studio'); ?>
        </label>

        <div class="variant-selector-wrapper">
            <select
                id="swp-ls-variation"
                class="form-select variant-select"
                <?php echo ! $has_variants ? 'disabled' : ''; ?>
            >
                <?php if ( $has_variants ): ?>
                    <option value="">
                        <?php _e('Select variant', 'swp-label-studio'); ?>
                    </option>

                    <?php foreach ( $variations as $variation ): ?>
                        <option
                            value="<?php echo esc_attr($variation['variation_id']); ?>"
                            data-price="<?php echo esc_attr($variation['display_price']); ?>"
                        >
                            <?php
                            $variation_name = [];
                            foreach ( $variation['attributes'] as $attr_value ) {
                                $variation_name[] = $attr_value;
                            }
                            echo esc_html( implode(' / ', $variation_name) );
                            ?>
                        </option>
                    <?php endforeach; ?>

                <?php else: ?>
                    <option value="">
                        <?php _e('No variants available', 'swp-label-studio'); ?>
                    </option>
                <?php endif; ?>
            </select>
            <i class="fa-solid fa-chevron-down variant-icon"></i>
        </div>
    </div>

    <!-- QUANTITY (ENHANCED WITH +/- BUTTONS) -->
    <div class="mb-3">
        <label class="form-label fw-bold d-flex align-items-center gap-2">
            <i class="fa-solid fa-hashtag text-primary"></i>
            <?php _e('Quantity', 'swp-label-studio'); ?>
        </label>
        
        <div class="quantity-selector">
            <button type="button" class="qty-btn qty-minus" id="qtyMinus">
                <i class="fa-solid fa-minus"></i>
            </button>
            <input
                type="number"
                id="swp-ls-qty"
                class="qty-input"
                min="1"
                value="1"
                readonly
            >
            <button type="button" class="qty-btn qty-plus" id="qtyPlus">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
    </div>

    <!-- TOTAL PRICE -->
    <div class="price-summary">
        <div class="price-label">
            <i class="fa-solid fa-calculator me-1"></i>
            <?php _e('Total', 'swp-label-studio'); ?>
        </div>
        <div class="price-amount" id="totalPrice" 
             data-base-price="<?php echo esc_attr($product->get_price()); ?>"
             data-currency-symbol="<?php echo esc_attr(get_woocommerce_currency_symbol()); ?>">
            <?php echo wc_price($product->get_price()); ?>
        </div>
    </div>

    <!-- ADD TO CART -->
    <button
        id="swp-ls-add-to-cart"
        class="btn btn-primary w-100 btn-lg fw-bold mb-2 add-to-cart-btn"
        data-product-id="<?php echo esc_attr($product->get_id()); ?>"
    >
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

    <!-- EXPORT BUTTON -->
    <button class="btn btn-outline-secondary w-100" onclick="openExport()">
        <i class="fa-solid fa-download me-2"></i>
        <?php _e('Export Design', 'swp-label-studio'); ?>
    </button>

</div>
<!-- MODALS -->
<?php include 'modals.php'; ?>