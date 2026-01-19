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

        // Fabric.js styling - FIX: Remove invalid textBaseline
        fabric.Object.prototype.transparentCorners = false;
        fabric.Object.prototype.cornerColor = '#0066ff';
        fabric.Object.prototype.cornerStyle = 'circle';
        fabric.Object.prototype.borderColor = '#0066ff';
        fabric.Object.prototype.cornerStrokeColor = 'white';
        fabric.Object.prototype.padding = 6;

        // FIX: Set valid textBaseline for text objects only
        fabric.Text.prototype.textBaseline = 'alphabetic'; // Valid enum value
        fabric.IText.prototype.textBaseline = 'alphabetic'; // Valid enum value

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
                fill: '#0f172a',
                textBaseline: 'alphabetic' // FIX: Use valid enum value
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
                    lineHeight: 0.95,
                    textBaseline: 'alphabetic'
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
                    charSpacing: 200,
                    textBaseline: 'alphabetic'
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
                    lineHeight: 0.9,
                    textBaseline: 'alphabetic'
                });
                title.name = 'Title';
                canvas.add(title);
            } else if (key === 'aqua') {
                canvas.backgroundColor = '#0066ff';
                const title = new fabric.IText('AQUA', {
                    left: 80, top: 150,
                    fontSize: 50,
                    fontFamily: 'Inter',
                    fontWeight: '900',
                    fill: '#ffffff',
                    textBaseline: 'alphabetic'
                });
                title.name = 'Title';
                canvas.add(title);
            } else if (key === 'organic') {
                canvas.backgroundColor = '#064e3b';
                const title = new fabric.IText('ORGANIC', {
                    left: 60, top: 150,
                    fontSize: 36,
                    fontFamily: 'Inter',
                    fontWeight: '700',
                    fill: '#34d399',
                    textBaseline: 'alphabetic'
                });
                title.name = 'Title';
                canvas.add(title);
            } else if (key === 'sunset') {
                canvas.backgroundColor = '#f97316';
                const title = new fabric.IText('SUNSET', {
                    left: 70, top: 150,
                    fontSize: 40,
                    fontFamily: 'Inter',
                    fontWeight: '800',
                    fill: '#ffffff',
                    textBaseline: 'alphabetic'
                });
                title.name = 'Title';
                canvas.add(title);
            } else if (key === 'night') {
                canvas.backgroundColor = '#111827';
                const title = new fabric.IText('NIGHT', {
                    left: 80, top: 150,
                    fontSize: 42,
                    fontFamily: 'Inter',
                    fontWeight: '800',
                    fill: '#6d28d9',
                    textBaseline: 'alphabetic'
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

        // === BRAND BLOCK ===
        window.quickAddBrandBlock = function () {
            const rect = new fabric.Rect({
                width: 200,
                height: 80,
                fill: '#0066ff',
                rx: 12,
                ry: 12
            });

            const text = new fabric.IText('Your Brand', {
                fontSize: 28,
                fontFamily: 'Inter',
                fontWeight: 'bold',
                fill: '#ffffff',
                originX: 'center',
                originY: 'center',
                left: 100,
                top: 40,
                textBaseline: 'alphabetic'
            });

            const group = new fabric.Group([rect, text], {
                left: 50,
                top: 180
            });

            group.name = 'Brand Block';

            canvas.add(group);
            canvas.setActiveObject(group);
            canvas.renderAll();
            saveHistory();
        };

        // === STATUS UI ===
        window.setDirty = function (isDirty) {
            $('#statusDot').toggleClass('dirty', isDirty);
            $('#statusText').text(isDirty ? 'Unsaved' : 'Saved');
            if (isDirty) {
                $('#statusTime').text('Just now');
            }
        };

        // === EXPORT FUNCTIONS ===
        window.openExport = function () {
            const modal = new bootstrap.Modal(document.getElementById('exportModal'));

            const previewData = canvas.toDataURL({
                format: 'png',
                multiplier: 1
            });
            $('#exportPreview').attr('src', previewData);

            modal.show();
        };

        window.exportPNG = function () {
            const scale = parseInt($('#exportScale').val()) || 2;
            const dataURL = canvas.toDataURL({
                format: 'png',
                multiplier: scale,
                quality: 1
            });

            const link = document.createElement('a');
            link.download = 'label-design-' + Date.now() + '.png';
            link.href = dataURL;
            link.click();
        };

        window.exportPDF = function () {
            const scale = parseInt($('#exportScale').val()) || 2;
            const imgData = canvas.toDataURL({
                format: 'png',
                multiplier: scale
            });

            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: [CANVAS_W * 0.264583, CANVAS_H * 0.264583]
            });

            pdf.addImage(imgData, 'PNG', 0, 0, CANVAS_W * 0.264583, CANVAS_H * 0.264583);
            pdf.save('label-design-' + Date.now() + '.pdf');
        };

        // === DRAFTS FUNCTIONS ===
        window.openDrafts = function () {
            const modal = new bootstrap.Modal(document.getElementById('draftsModal'));
            loadDraftsList();
            modal.show();
        };

        function loadDraftsList() {
            const $list = $('#draftsList');
            $list.empty();

            const drafts = JSON.parse(localStorage.getItem('swp_ls_drafts') || '[]');

            if (drafts.length === 0) {
                $list.html('<div class="col-12"><div class="hint-card">No drafts saved yet</div></div>');
                return;
            }

            drafts.forEach((draft, index) => {
                const $col = $('<div class="col-md-4"></div>');
                const $card = $('<div class="hint-card" style="cursor:pointer;"></div>');
                $card.html(`
                    <div style="font-weight:800; margin-bottom:8px;">${draft.name}</div>
                    <div style="font-size:0.85rem; color:#94a3b8;">${draft.date}</div>
                `);
                $card.on('click', () => loadDraft(index));
                $col.append($card);
                $list.append($col);
            });
        }

        window.saveDraftPrompt = function () {
            const name = prompt('Enter draft name:', 'Design ' + (new Date().toLocaleString()));
            if (name) {
                saveDraft(name);
            }
        };

        function saveDraft(name) {
            const drafts = JSON.parse(localStorage.getItem('swp_ls_drafts') || '[]');
            drafts.push({
                name: name,
                date: new Date().toLocaleString(),
                data: JSON.stringify(canvas.toJSON(['name']))
            });
            localStorage.setItem('swp_ls_drafts', JSON.stringify(drafts));
            loadDraftsList();
            alert('Draft saved!');
        }

        function loadDraft(index) {
            const drafts = JSON.parse(localStorage.getItem('swp_ls_drafts') || '[]');
            if (drafts[index]) {
                canvas.loadFromJSON(drafts[index].data, () => {
                    canvas.renderAll();
                    saveHistory();
                    bootstrap.Modal.getInstance(document.getElementById('draftsModal')).hide();
                });
            }
        }

        window.clearDrafts = function () {
            if (confirm('Are you sure you want to delete all drafts?')) {
                localStorage.removeItem('swp_ls_drafts');
                loadDraftsList();
            }
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

        // === SAVE & ADD TO CART ===
        window.saveAndAddToCart = function () {
            const jsonData = JSON.stringify(canvas.toJSON(['name']));
            const pngData = canvas.toDataURL({ format: 'png', multiplier: 2 });

            $('#statusText').text('Saving...');

            $.ajax({
                url: swp_ls_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'swp_ls_save_design',
                    nonce: swp_ls_vars.nonce,
                    product_id: $('#swp-ls-designer-app').data('product-id'),
                    variation_id: $('#swp-ls-designer-app').data('variation-id'),
                    qty: $('#swp-ls-designer-app').data('qty') || 1,
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
            if (ctrl && e.key.toLowerCase() === 's') {
                e.preventDefault();
                saveDraftPrompt();
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