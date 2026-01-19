(function ($) {
    'use strict';

    $(document).ready(function () {
        if (!$('#c').length) return;

        const CANVAS_W = 300;
        const CANVAS_H = 450;

        const canvas = new fabric.Canvas('c', {
            width: CANVAS_W,
            height: CANVAS_H,
            backgroundColor: '#ffffff',
            preserveObjectStacking: true,
            selection: true
        });

        // Fabric.js styling
        fabric.Object.prototype.transparentCorners = false;
        fabric.Object.prototype.cornerColor = '#0066ff';
        fabric.Object.prototype.cornerStyle = 'circle';
        fabric.Object.prototype.borderColor = '#0066ff';
        fabric.Object.prototype.cornerStrokeColor = 'white';
        fabric.Object.prototype.padding = 6;

        let history = [];
        let historyIndex = -1;
        let historyProcessing = false;
        let zoomLevel = 1;

        // === HISTORY ===
        function saveHistory() {
            if (historyProcessing) return;
            if (historyIndex < history.length - 1) history = history.slice(0, historyIndex + 1);
            history.push(JSON.stringify(canvas.toJSON(['name'])));
            historyIndex++;
            setDirty(true);
            renderLayers();
        }

        function loadHistoryIndex(idx) {
            if (idx < 0 || idx >= history.length) return;
            historyProcessing = true;
            canvas.loadFromJSON(history[idx], () => {
                canvas.renderAll();
                historyProcessing = false;
                updatePropsPanel();
                renderLayers();
            });
        }

        window.undo = function () {
            if (historyIndex > 0) {
                historyIndex--;
                loadHistoryIndex(historyIndex);
            }
        };

        window.redo = function () {
            if (historyIndex < history.length - 1) {
                historyIndex++;
                loadHistoryIndex(historyIndex);
            }
        };

        // === OBJECT CREATION ===
        window.addText = function () {
            const text = new fabric.IText('Your Text', {
                left: 100,
                top: 100,
                fontFamily: 'Inter',
                fontSize: 24,
                fontWeight: 'bold',
                fill: '#0f172a'
            });
            text.name = 'Text';
            canvas.add(text);
            canvas.setActiveObject(text);
            canvas.renderAll();
            saveHistory();
        };

        window.addShape = function (type) {
            let shape;
            if (type === 'rect') {
                shape = new fabric.Rect({
                    left: 100, top: 100,
                    width: 100, height: 60,
                    fill: '#0066ff',
                    rx: 10, ry: 10
                });
                shape.name = 'Box';
            } else if (type === 'circle') {
                shape = new fabric.Circle({
                    left: 100, top: 100,
                    radius: 50,
                    fill: '#00d2ff'
                });
                shape.name = 'Circle';
            }
            canvas.add(shape);
            canvas.setActiveObject(shape);
            canvas.renderAll();
            saveHistory();
        };

        $('#imgInput').on('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (f) {
                fabric.Image.fromURL(f.target.result, function (img) {
                    img.scaleToWidth(150);
                    img.name = 'Photo';
                    canvas.add(img);
                    canvas.centerObject(img);
                    canvas.setActiveObject(img);
                    canvas.renderAll();
                    saveHistory();
                });
            };
            reader.readAsDataURL(file);
            e.target.value = '';
        });

        // === QR CODE ===
        window.showQRCodeModal = function () {
            const modal = new bootstrap.Modal(document.getElementById('qrModal'));
            modal.show();
        };

        window.generateQR = function () {
            const url = $('#qrInput').val() || 'https://example.com';
            const qrApi = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" + encodeURIComponent(url);
            fabric.Image.fromURL(qrApi, function (img) {
                img.name = 'QR Code';
                canvas.add(img);
                canvas.centerObject(img);
                canvas.setActiveObject(img);
                canvas.renderAll();
                saveHistory();
                bootstrap.Modal.getInstance(document.getElementById('qrModal')).hide();
            }, { crossOrigin: 'anonymous' });
        };

        window.showClipArtModal = function () {
            alert('Clipart library coming soon!');
        };

        // === BACKGROUND ===
        window.setBg = function (color) {
            canvas.backgroundColor = color;
            canvas.renderAll();
            saveHistory();
        };

        // === DELETE ===
        window.deleteObj = function () {
            const active = canvas.getActiveObjects();
            if (active.length) {
                canvas.discardActiveObject();
                active.forEach(obj => canvas.remove(obj));
                canvas.renderAll();
                saveHistory();
                updatePropsPanel();
                renderLayers();
            }
        };

        // === OVERLAYS ===
        window.toggleGrid = function () {
            $('#gridOverlay').toggleClass('show');
            $('#gridBtn').toggleClass('active');
        };

        window.toggleSafe = function () {
            $('#safeOverlay').toggleClass('show');
            $('#safeBtn').toggleClass('active');
        };

        // === ZOOM ===
        const wrapper = document.getElementById('canvasWrapper');

        function applyZoom() {
            zoomLevel = Math.max(0.5, Math.min(2.5, zoomLevel));
            if (wrapper) wrapper.style.transform = `scale(${zoomLevel})`;
            $('#zoomPct').text(Math.round(zoomLevel * 100) + '%');
        }

        window.zoomIn = function () {
            zoomLevel += 0.1;
            applyZoom();
        };

        window.zoomOut = function () {
            zoomLevel -= 0.1;
            applyZoom();
        };

        window.zoomReset = function () {
            zoomLevel = 1;
            applyZoom();
        };

        // === TEMPLATES ===
        window.loadTemplate = function (key) {
            canvas.clear();
            canvas.backgroundColor = '#ffffff';

            if (key === 'fresh') {
                canvas.backgroundColor = '#e0f7fa';
                const title = new fabric.IText('PURE\nSPRING', {
                    left: 50, top: 50,
                    fontSize: 40,
                    fontFamily: 'Inter',
                    fontWeight: '800',
                    fill: '#064e3b',
                    lineHeight: 0.95
                });
                title.name = 'Title';
                canvas.add(title);
            } else if (key === 'minimal') {
                const rect = new fabric.Rect({
                    left: 20, top: 20,
                    width: 260, height: 410,
                    fill: 'transparent',
                    stroke: '#0f172a',
                    strokeWidth: 2,
                    rx: 18, ry: 18
                });
                rect.name = 'Border';
                const title = new fabric.IText('MINIMAL', {
                    left: 50, top: 60,
                    fontSize: 30,
                    fontFamily: 'Inter',
                    fontWeight: '500',
                    charSpacing: 200
                });
                title.name = 'Title';
                canvas.add(rect, title);
            } else if (key === 'elite') {
                canvas.backgroundColor = '#0f172a';
                const title = new fabric.IText('ELITE\nWATER', {
                    left: 66, top: 92,
                    fontSize: 44,
                    fontFamily: 'Impact',
                    fill: '#f8fafc',
                    textAlign: 'center',
                    lineHeight: 0.9
                });
                title.name = 'Title';
                canvas.add(title);
            }

            canvas.renderAll();
            saveHistory();
        };

        // === ALIGNMENT ===
        window.alignActive = function (mode) {
            const obj = canvas.getActiveObject();
            if (!obj) return;

            const bounds = obj.getBoundingRect(true, true);

            if (mode === 'left') obj.left = 0;
            if (mode === 'center') obj.left = (CANVAS_W - bounds.width) / 2;
            if (mode === 'right') obj.left = CANVAS_W - bounds.width;
            if (mode === 'top') obj.top = 0;
            if (mode === 'middle') obj.top = (CANVAS_H - bounds.height) / 2;
            if (mode === 'bottom') obj.top = CANVAS_H - bounds.height;

            obj.setCoords();
            canvas.renderAll();
            saveHistory();
            updatePropsPanel();
        };

        // === STATUS UI ===
        window.setDirty = function (isDirty) {
            $('#statusDot').toggleClass('dirty', isDirty);
            $('#statusText').text(isDirty ? 'Unsaved' : 'Saved');
        };

        // === LAYERS ===
        function renderLayers() {
            const $list = $('#layersList');
            $list.empty();
            const objs = canvas.getObjects().slice().reverse();
            const active = canvas.getActiveObject();

            if (!objs.length) {
                $list.html('<div class="hint-card">No layers yet</div>');
                return;
            }

            objs.forEach(obj => {
                const $item = $('<div class="layer-item"></div>');
                if (active === obj) $item.addClass('active');
                $item.text(obj.name || 'Layer');
                $item.on('click', () => {
                    canvas.setActiveObject(obj);
                    canvas.renderAll();
                    updatePropsPanel();
                    renderLayers();
                });
                $list.append($item);
            });
        }

        // === PROPERTIES PANEL ===
        function updatePropsPanel() {
            const obj = canvas.getActiveObject();
            if (obj) {
                $('#no-selection').hide();
                $('#prop-controls').show();
                $('#selectionBadge').text(obj.name || 'Layer');

                const fill = (typeof obj.fill === 'string') ? obj.fill : '#000000';
                $('#fillColor').val(normalizeHex(fill));

                const isText = (obj.type === 'i-text' || obj.type === 'text');
                $('#text-props').toggle(isText);
                if (isText) {
                    $('#fontFamily').val(obj.fontFamily || 'Inter');
                    if ($('#fontSize').length) $('#fontSize').val(obj.fontSize || 24);
                }
            } else {
                $('#no-selection').show();
                $('#prop-controls').hide();
                $('#selectionBadge').text('No selection');
            }
        }

        function normalizeHex(color) {
            if (!color || typeof color !== 'string') return '#000000';
            if (color.startsWith('#') && (color.length === 7 || color.length === 4)) {
                if (color.length === 4) {
                    return '#' + color[1] + color[1] + color[2] + color[2] + color[3] + color[3];
                }
                return color;
            }
            const m = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
            if (m) {
                const r = Number(m[1]).toString(16).padStart(2, '0');
                const g = Number(m[2]).toString(16).padStart(2, '0');
                const b = Number(m[3]).toString(16).padStart(2, '0');
                return '#' + r + g + b;
            }
            return '#000000';
        }

        window.updateProp = function (prop, val) {
            const obj = canvas.getActiveObject();
            if (obj) {
                if (prop === 'fill' && obj.type === 'image') return;
                if (['fontSize'].includes(prop)) val = Number(val);
                obj.set(prop, val);
                canvas.renderAll();
                saveHistory();
                updatePropsPanel();
            }
        };

        // === STEPS ===
        window.goToStep = function (step) {
            $('.step-item').removeClass('active');
            $(`.step-item[data-step="${step}"]`).addClass('active');
            $('.step-panel').hide();
            $(`#step-panel-${step}`).show();
            if (step === 4) renderReviewStep();
        };

        function renderReviewStep() {
            const thumb = canvas.toDataURL({ format: 'png', multiplier: 0.5 });
            $('#review-thumb').attr('src', thumb);
            $('#review-product').text($('#product-select option:selected').text() || 'Standard Bottle');
            $('#review-qty').text($('#qty-input').val() || '1');
        }

        // === SAVE & ADD TO CART ===
        window.saveAndAddToCart = function () {
            const jsonData = JSON.stringify(canvas.toJSON(['name']));
            const pngData = canvas.toDataURL({ format: 'png', multiplier: parseInt(swp_ls_vars.export_scale) || 2 });

            $('#statusText').text('Saving...');

            $.ajax({
                url: swp_ls_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'swp_ls_save_design',
                    nonce: swp_ls_vars.nonce,
                    product_id: $('#swp-ls-designer-app').data('product-id'),
                    variation_id: $('#swp-ls-designer-app').data('variation-id'),
                    qty: $('#qty-input').val() || 1,
                    design_json: jsonData,
                    design_png: pngData
                },
                success: function (response) {
                    if (response.success) {
                        window.location.href = response.data.cart_url;
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                        $('#statusText').text('Error');
                    }
                },
                error: function () {
                    alert('Network error. Please try again.');
                    $('#statusText').text('Error');
                }
            });
        };

        // === SHORTCUTS ===
        window.openShortcuts = function () {
            const modal = new bootstrap.Modal(document.getElementById('shortcutsModal'));
            modal.show();
        };

        // === EVENT LISTENERS ===
        canvas.on('object:modified', saveHistory);
        canvas.on('object:added', (e) => {
            if (!historyProcessing) saveHistory();
        });
        canvas.on('object:removed', saveHistory);
        canvas.on('selection:created', () => {
            updatePropsPanel();
            renderLayers();
        });
        canvas.on('selection:updated', () => {
            updatePropsPanel();
            renderLayers();
        });
        canvas.on('selection:cleared', () => {
            updatePropsPanel();
            renderLayers();
        });

        // Keyboard shortcuts
        $(document).on('keydown', function (e) {
            const isMac = navigator.platform.toUpperCase().includes('MAC');
            const ctrl = isMac ? e.metaKey : e.ctrlKey;

            if (ctrl && e.key.toLowerCase() === 'z' && !e.shiftKey) {
                e.preventDefault();
                undo();
            }
            if (ctrl && e.key.toLowerCase() === 'z' && e.shiftKey) {
                e.preventDefault();
                redo();
            }
            if (ctrl && e.key.toLowerCase() === 'g') {
                e.preventDefault();
                toggleGrid();
            }

            if (e.key === 'Delete' || e.key === 'Backspace') {
                const active = canvas.getActiveObject();
                if (active && !(active.isEditing || (active.type === 'i-text' && active.hiddenTextarea))) {
                    e.preventDefault();
                    deleteObj();
                }
            }
        });

        // === INITIALIZE ===
        function init() {
            loadTemplate('minimal');
            history = [JSON.stringify(canvas.toJSON(['name']))];
            historyIndex = 0;
            setDirty(false);
            updatePropsPanel();
            renderLayers();
            applyZoom();
            goToStep(3); // Start on design step
        }

        init();
    });

})(jQuery);