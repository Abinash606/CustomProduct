(function ($) {
    'use strict';
    // ============================================
    // DESIGNER INTERFACE - Only runs when canvas exists
    // ============================================
    $(document).ready(function () {
        if (!$('#c').length) return;

        var canvas = null;

        const CANVAS_W = 300;
        const CANVAS_H = 450;

        window.swpLsCanvas = new fabric.Canvas('c', {
            width: CANVAS_W,
            height: CANVAS_H,
            backgroundColor: '#ffffff',
            preserveObjectStacking: true,
            selection: true
        });

        canvas = window.swpLsCanvas;

        // Fabric.js styling
        fabric.Object.prototype.transparentCorners = false;
        fabric.Object.prototype.cornerColor = '#0066ff';
        fabric.Object.prototype.cornerStyle = 'circle';
        fabric.Object.prototype.borderColor = '#0066ff';
        fabric.Object.prototype.cornerStrokeColor = 'white';
        fabric.Object.prototype.padding = 6;

        fabric.Text.prototype.textBaseline = 'alphabetic';
        fabric.IText.prototype.textBaseline = 'alphabetic';

        let history = [];
        let historyIndex = -1;
        let historyProcessing = false;
        let zoomLevel = 1;
        let snapEnabled = false;
        let guidesEnabled = false;
        let bleedEnabled = false;

        // Helper function to clamp numbers
        function clampNum(val, min, max) {
            return Math.min(Math.max(val, min), max);
        }

        // Helper function to get active object
        function getActiveOne() {
            return canvas.getActiveObject();
        }

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

        // === LOAD PRODUCT IMAGE ===
        window.loadProductImage = function () {
            const $app = $('#swp-ls-designer-app');
            const productImage = $app.data('image');

            if (!productImage || productImage === '') {
                console.warn('No product image URL provided');
                alert('No product image available');
                return;
            }

            fabric.Image.fromURL(productImage, function (img) {
                if (!img || !img.width) {
                    console.error('Failed to load product image');
                    alert('Failed to load product image');
                    return;
                }

                const scale = Math.min(CANVAS_W / img.width, CANVAS_H / img.height) * 0.8;
                img.scale(scale);
                img.set({
                    left: CANVAS_W / 2,
                    top: CANVAS_H / 2,
                    originX: 'center',
                    originY: 'center',
                    selectable: true
                });
                img.name = 'Product Image';
                canvas.add(img);
                canvas.sendToBack(img);
                canvas.renderAll();
                saveHistory();
            }, {
                crossOrigin: 'anonymous'
            });
        };

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

        // === CLIPART ===
        const CLIPART = [
            { key: "drop", name: "Water drop" },
            { key: "leaf", name: "Leaf" },
            { key: "mountain", name: "Mountain" },
            { key: "wave", name: "Wave" },
            { key: "sparkle", name: "Sparkle" },
            { key: "badge", name: "Badge" },
            { key: "recycle", name: "Recycle" },
            { key: "heart", name: "Heart" },
        ];

        window.showClipArtModal = function () {
            buildClipartGrid();
            const modal = new bootstrap.Modal(document.getElementById('clipModal'));
            modal.show();
        };

        function buildClipartGrid() {
            const grid = document.getElementById("clipGrid");
            grid.innerHTML = "";

            CLIPART.forEach(item => {
                const col = document.createElement("div");
                col.className = "col-6 col-md-3";
                col.innerHTML = `
                    <div class="clipart-card p-3 border rounded-4 bg-white h-100" style="cursor:pointer; transition: all 0.3s ease; box-shadow:0 12px 22px rgba(15,23,42,0.06);">
                        <div class="d-flex align-items-center justify-content-center" style="height:72px;">
                            ${clipartInlineSVG(item.key, "#0f172a")}
                        </div>
                        <div class="text-center fw-bold mt-2" style="font-size:0.9rem;">${item.name}</div>
                    </div>
                `;

                const card = col.querySelector('.clipart-card');
                card.addEventListener('mouseenter', function () {
                    this.style.transform = 'translateY(-4px)';
                    this.style.boxShadow = '0 20px 40px rgba(15,23,42,0.12)';
                });
                card.addEventListener('mouseleave', function () {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 12px 22px rgba(15,23,42,0.06)';
                });
                card.addEventListener("click", () => addClipart(item.key));

                grid.appendChild(col);
            });
        }

        function addClipart(key) {
            const svgString = clipartSVGString(key, "#0f172a");

            fabric.loadSVGFromString(svgString, function (objects, options) {
                const obj = fabric.util.groupSVGElements(objects, options);

                obj.set({
                    left: 100,
                    top: 150,
                    scaleX: 1.2,
                    scaleY: 1.2,
                    opacity: 0.95
                });

                obj.name = CLIPART.find(c => c.key === key)?.name || 'Clipart';

                canvas.add(obj);
                canvas.setActiveObject(obj);
                canvas.renderAll();
                saveHistory();

                bootstrap.Modal.getInstance(document.getElementById('clipModal')).hide();
            });
        }

        window.addWatermarkStamp = function () {
            const ring = new fabric.Circle({
                left: 72,
                top: 98,
                radius: 82,
                fill: "transparent",
                stroke: "rgba(0,102,255,0.65)",
                strokeWidth: 5
            });

            const txt = new fabric.IText("PURE", {
                left: 116,
                top: 138,
                fontFamily: "Plus Jakarta Sans",
                fontSize: 46,
                fontWeight: "800",
                fill: "rgba(0,102,255,0.9)"
            });

            const sub = new fabric.IText("SPRINGWATER", {
                left: 104,
                top: 190,
                fontFamily: "Plus Jakarta Sans",
                fontSize: 12,
                fontWeight: "800",
                fill: "rgba(0,102,255,0.85)",
                charSpacing: 260
            });

            const group = new fabric.Group([ring, txt, sub], {
                left: 56,
                top: 110
            });

            group.name = "Stamp";

            canvas.add(group);
            canvas.setActiveObject(group);
            canvas.renderAll();
            saveHistory();

            bootstrap.Modal.getInstance(document.getElementById('clipModal')).hide();
        };

        function clipartInlineSVG(key, color) {
            return clipartSVGString(key, color);
        }

        function clipartSVGString(key, color) {
            const svgs = {
                drop: `<svg viewBox="0 0 100 100" width="60" height="60" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 10 C35 30, 20 50, 20 65 C20 80, 33 90, 50 90 C67 90, 80 80, 80 65 C80 50, 65 30, 50 10 Z" 
                          fill="${color}" opacity="0.9"/>
                    <ellipse cx="40" cy="50" rx="8" ry="12" fill="white" opacity="0.3"/>
                </svg>`,
                leaf: `<svg viewBox="0 0 100 100" width="60" height="60" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 10 Q80 30, 85 60 Q85 80, 65 90 Q50 95, 50 95 Q50 95, 35 90 Q15 80, 15 60 Q20 30, 50 10 Z" 
                          fill="${color}" opacity="0.9"/>
                    <path d="M50 20 L50 85" stroke="white" stroke-width="2" opacity="0.4"/>
                    <path d="M50 35 Q65 45, 70 55" stroke="white" stroke-width="1.5" opacity="0.3" fill="none"/>
                    <path d="M50 35 Q35 45, 30 55" stroke="white" stroke-width="1.5" opacity="0.3" fill="none"/>
                </svg>`,
                mountain: `<svg viewBox="0 0 100 100" width="60" height="60" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 80 L35 35 L50 55 L75 20 L90 80 Z" fill="${color}" opacity="0.9"/>
                    <path d="M35 35 L42 45 L45 35 Z" fill="white" opacity="0.6"/>
                    <circle cx="75" cy="25" r="3" fill="white" opacity="0.5"/>
                </svg>`,
                wave: `<svg viewBox="0 0 100 100" width="60" height="60" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 50 Q20 30, 40 50 T80 50 T120 50" stroke="${color}" stroke-width="8" fill="none" opacity="0.9"/>
                    <path d="M0 65 Q20 45, 40 65 T80 65 T120 65" stroke="${color}" stroke-width="6" fill="none" opacity="0.6"/>
                    <path d="M0 77 Q20 57, 40 77 T80 77 T120 77" stroke="${color}" stroke-width="4" fill="none" opacity="0.3"/>
                </svg>`,
                sparkle: `<svg viewBox="0 0 100 100" width="60" height="60" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 10 L55 45 L90 50 L55 55 L50 90 L45 55 L10 50 L45 45 Z" fill="${color}" opacity="0.9"/>
                    <circle cx="50" cy="50" r="8" fill="white" opacity="0.6"/>
                    <path d="M75 25 L78 35 L88 38 L78 41 L75 51 L72 41 L62 38 L72 35 Z" fill="${color}" opacity="0.7"/>
                    <path d="M25 75 L27 82 L34 84 L27 86 L25 93 L23 86 L16 84 L23 82 Z" fill="${color}" opacity="0.7"/>
                </svg>`,
                badge: `<svg viewBox="0 0 100 100" width="60" height="60" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="35" fill="${color}" opacity="0.9"/>
                    <circle cx="50" cy="50" r="28" fill="none" stroke="white" stroke-width="2" opacity="0.6"/>
                    <path d="M50 25 L54 40 L70 42 L58 52 L62 67 L50 59 L38 67 L42 52 L30 42 L46 40 Z" 
                          fill="white" opacity="0.8"/>
                </svg>`,
                recycle: `<svg viewBox="0 0 100 100" width="60" height="60" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 20 L35 45 L50 45 L50 70 L65 45 L50 45 Z" fill="${color}" opacity="0" 
                          stroke="${color}" stroke-width="6"/>
                    <path d="M50 25 L60 40 M50 75 L40 60" stroke="${color}" stroke-width="6" opacity="0.9"/>
                    <circle cx="50" cy="50" r="30" fill="none" stroke="${color}" stroke-width="5" 
                            opacity="0.8" stroke-dasharray="10 5"/>
                    <path d="M75 35 L82 42 L85 33 Z" fill="${color}" opacity="0.9"/>
                    <path d="M25 65 L18 58 L15 67 Z" fill="${color}" opacity="0.9"/>
                </svg>`,
                heart: `<svg viewBox="0 0 100 100" width="60" height="60" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 85 C50 85, 20 60, 20 40 C20 25, 30 20, 37 20 C44 20, 50 25, 50 25 
                             C50 25, 56 20, 63 20 C70 20, 80 25, 80 40 C80 60, 50 85, 50 85 Z" 
                          fill="${color}" opacity="0.9"/>
                    <path d="M37 30 Q32 30, 30 35" stroke="white" stroke-width="2" opacity="0.4" fill="none"/>
                </svg>`
            };
            return svgs[key] || svgs.drop;
        }

        // === BACKGROUND ===
        window.setBg = function (color) {
            canvas.backgroundColor = color;
            canvas.renderAll();
            saveHistory();
        };

        // === COPY PASTE DELETE ===
        let _clipboard = null;

        window.copyObj = function () {
            const active = canvas.getActiveObject();
            if (!active) return;

            active.clone(function (cloned) {
                _clipboard = cloned;
                $('#statusText').text('Copied');
                setTimeout(() => {
                    $('#statusText').text('Saved');
                }, 1000);
            });
        };

        window.pasteObj = function () {
            if (!_clipboard) return;

            _clipboard.clone(function (clonedObj) {
                canvas.discardActiveObject();

                clonedObj.set({
                    left: (clonedObj.left || 0) + 15,
                    top: (clonedObj.top || 0) + 15,
                    evented: true
                });

                if (clonedObj.type === "activeSelection") {
                    clonedObj.canvas = canvas;
                    clonedObj.forEachObject(function (obj) {
                        canvas.add(obj);
                    });
                    clonedObj.setCoords();
                } else {
                    canvas.add(clonedObj);
                }

                _clipboard.top += 15;
                _clipboard.left += 15;

                canvas.setActiveObject(clonedObj);
                canvas.renderAll();
                saveHistory();

                $('#statusText').text('Pasted');
                setTimeout(() => {
                    $('#statusText').text('Unsaved');
                }, 1000);
            });
        };

        window.deleteObj = function () {
            const active = canvas.getActiveObjects();
            if (!active || !active.length) return;

            canvas.discardActiveObject();
            active.forEach(function (obj) {
                canvas.remove(obj);
            });

            canvas.renderAll();
            saveHistory();
            updatePropsPanel();
            renderLayers();
        };

        window.duplicateObj = function () {
            copyObj();
            setTimeout(() => {
                pasteObj();
            }, 50);
        };

        // === OVERLAYS & TOGGLES ===
        window.toggleGrid = function () {
            $('#gridOverlay').toggleClass('show');
            $('#gridBtn').toggleClass('active');
        };

        window.toggleSafe = function () {
            $('#safeOverlay').toggleClass('show');
            $('#safeBtn').toggleClass('active');
        };

        window.toggleSnap = function () {
            snapEnabled = !snapEnabled;
            $('#snapBtn').toggleClass('active', snapEnabled);
        };

        window.toggleGuides = function () {
            guidesEnabled = !guidesEnabled;
            $('#guidesBtn').toggleClass('active', guidesEnabled);

            // Create guides overlay if it doesn't exist
            let $guides = $('#centerGuidesOverlay');
            if (!$guides.length) {
                $guides = $('<div id="centerGuidesOverlay" class="center-guides-overlay"></div>');
                $('#canvasWrapper').append($guides);
            }
            $guides.toggleClass('show', guidesEnabled);
        };

        window.toggleBleed = function () {
            bleedEnabled = !bleedEnabled;
            $('#bleedBtn').toggleClass('active', bleedEnabled);

            // Create bleed overlay if it doesn't exist
            let $bleed = $('#bleedOverlay');
            if (!$bleed.length) {
                $bleed = $('<div id="bleedOverlay" class="bleed-overlay"></div>');
                $('#canvasWrapper').append($bleed);
            }
            $bleed.toggleClass('show', bleedEnabled);
        };

        // === CENTER ACTIVE OBJECT ===
        window.centerActive = function () {
            const obj = getActiveOne();
            if (!obj) {
                alert('Please select an object first');
                return;
            }

            canvas.viewportCenterObject(obj);
            obj.setCoords();
            canvas.renderAll();
            saveHistory();
            updatePropsPanel();
        };

        // === FIT TO STAGE ===
        window.fitToStage = function () {
            const area = document.querySelector(".canvas-area");
            if (!area) return;

            const rect = area.getBoundingClientRect();
            const padding = 44;
            const scaleX = (rect.width - padding) / CANVAS_W;
            const scaleY = (rect.height - padding) / CANVAS_H;
            zoomLevel = Math.min(scaleX, scaleY, 2.2);
            zoomLevel = clampNum(zoomLevel, 0.6, 2.2);
            applyZoom();
        };

        // === RESET CANVAS ===
        window.resetCanvas = function () {
            if (!confirm("Clear current design?")) return;

            canvas.discardActiveObject();
            canvas.clear();
            canvas.backgroundColor = "#ffffff";
            canvas.renderAll();
            saveHistory();
            updatePropsPanel();
            renderLayers();
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
            } else if (key === 'aqua') {
                canvas.backgroundColor = '#0066ff';
                const title = new fabric.IText('AQUA', {
                    left: 80, top: 150,
                    fontSize: 50,
                    fontFamily: 'Inter',
                    fontWeight: '900',
                    fill: '#ffffff'
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
                    fill: '#34d399'
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
                    fill: '#ffffff'
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
                    fill: '#6d28d9'
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
                top: 40
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

        // === PREVIEW ===
        let previewModalInstance = null;
        let isPreviewOpen = false;

        window.showPreview = function () {
            if (!canvas) return;

            const previewData = canvas.toDataURL({
                format: 'png',
                multiplier: 2
            });

            document.getElementById('previewImage').src = previewData;

            if (!isPreviewOpen) {
                previewModalInstance = new bootstrap.Modal(document.getElementById('previewModal'));
                previewModalInstance.show();
                isPreviewOpen = true;

                document.getElementById('previewModal').addEventListener('hidden.bs.modal', function () {
                    isPreviewOpen = false;
                });
            }
        };

        function updatePreviewIfOpen() {
            if (isPreviewOpen && canvas) {
                const previewData = canvas.toDataURL({
                    format: 'png',
                    multiplier: 2
                });
                document.getElementById('previewImage').src = previewData;
            }
        }

        // === EXPORT ===
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
            const scale = parseInt($('#exportScale').val()) || swp_ls_vars.export_scale;
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

        // === DRAFTS ===
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
            if (!window.swpLsCanvas) {
                alert('Designer not initialized');
                return;
            }

            const $app = $('#swp-ls-designer-app');
            const productId = parseInt($app.data('product-id'));
            const variationId = parseInt($app.data('variation-id')) || 0;
            const qty = parseInt($app.data('qty')) || 1;

            if (!productId) {
                alert('Product ID missing. Please go back and launch designer from product page.');
                return;
            }

            const jsonData = JSON.stringify(swpLsCanvas.toJSON(['name']));
            const pngData = swpLsCanvas.toDataURL({
                format: 'png',
                multiplier: 2
            });

            $('#statusText').text('Saving...');
            $('#swp-ls-add-to-cart').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Adding to Cart...');

            $.ajax({
                url: swp_ls_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'swp_ls_save_design',
                    nonce: swp_ls_vars.nonce,
                    product_id: productId,
                    variation_id: variationId,
                    qty: qty,
                    design_json: jsonData,
                    design_png: pngData
                },
                success: function (response) {
                    if (response.success && response.data.cart_url) {
                        $('#statusText').text('Success!');
                        window.location.href = response.data.cart_url;
                    } else {
                        console.error(response);
                        alert(response.data || 'Failed to add product to cart');
                        $('#statusText').text('Error');
                        $('#swp-ls-add-to-cart').prop('disabled', false).html('<i class="fa-solid fa-cart-plus me-2"></i>Add to Cart');
                    }
                },
                error: function (xhr) {
                    console.error('AJAX error:', xhr.responseText);
                    alert('Network error. Please try again.');
                    $('#statusText').text('Error');
                    $('#swp-ls-add-to-cart').prop('disabled', false).html('<i class="fa-solid fa-cart-plus me-2"></i>Add to Cart');
                }
            });
        };

        // === SHORTCUTS ===
        window.openShortcuts = function () {
            const modal = new bootstrap.Modal(document.getElementById('shortcutsModal'));
            modal.show();
        };

        // === EVENT LISTENERS ===
        canvas.on('object:modified', function () {
            saveHistory();
            updatePreviewIfOpen();
        });

        canvas.on('object:added', function (e) {
            if (!historyProcessing) saveHistory();
            updatePreviewIfOpen();
        });

        canvas.on('object:removed', function () {
            saveHistory();
            updatePreviewIfOpen();
        });

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

        canvas.on('object:scaling', updatePreviewIfOpen);
        canvas.on('object:moving', updatePreviewIfOpen);
        canvas.on('object:rotating', updatePreviewIfOpen);
        canvas.on('text:changed', updatePreviewIfOpen);

        canvas.on('after:render', function () {
            if (isPreviewOpen) {
                clearTimeout(window.previewDebounce);
                window.previewDebounce = setTimeout(updatePreviewIfOpen, 100);
            }
        });

        // === KEYBOARD SHORTCUTS ===
        $(document).on('keydown', function (e) {
            const isMac = navigator.platform.toUpperCase().includes('MAC');
            const ctrl = isMac ? e.metaKey : e.ctrlKey;

            // Undo
            if (ctrl && e.key.toLowerCase() === 'z' && !e.shiftKey) {
                e.preventDefault();
                undo();
            }

            // Redo
            if (ctrl && e.key.toLowerCase() === 'z' && e.shiftKey) {
                e.preventDefault();
                redo();
            }

            // Copy
            if (ctrl && e.key.toLowerCase() === 'c' && !e.shiftKey) {
                const active = canvas.getActiveObject();
                if (active && !(active.isEditing || (active.type === 'i-text' && active.hiddenTextarea))) {
                    e.preventDefault();
                    copyObj();
                }
            }

            // Paste
            if (ctrl && e.key.toLowerCase() === 'v') {
                e.preventDefault();
                pasteObj();
            }

            // Duplicate
            if (ctrl && e.key.toLowerCase() === 'd') {
                e.preventDefault();
                duplicateObj();
            }

            // Toggle Grid
            if (ctrl && e.key.toLowerCase() === 'g') {
                e.preventDefault();
                toggleGrid();
            }

            // Save Draft
            if (ctrl && e.key.toLowerCase() === 's') {
                e.preventDefault();
                saveDraftPrompt();
            }

            // Delete
            if (e.key === 'Delete' || e.key === 'Backspace') {
                const active = canvas.getActiveObject();
                if (active && !(active.isEditing || (active.type === 'i-text' && active.hiddenTextarea))) {
                    e.preventDefault();
                    deleteObj();
                }
            }

            // Arrow key nudging
            const active = canvas.getActiveObject();
            if (active && !(active.isEditing || (active.type === 'i-text' && active.hiddenTextarea))) {
                const nudgeAmount = e.shiftKey ? 10 : 1;
                let moved = false;

                switch (e.key) {
                    case 'ArrowLeft':
                        e.preventDefault();
                        active.left -= nudgeAmount;
                        moved = true;
                        break;
                    case 'ArrowRight':
                        e.preventDefault();
                        active.left += nudgeAmount;
                        moved = true;
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        active.top -= nudgeAmount;
                        moved = true;
                        break;
                    case 'ArrowDown':
                        e.preventDefault();
                        active.top += nudgeAmount;
                        moved = true;
                        break;
                }

                if (moved) {
                    active.setCoords();
                    canvas.renderAll();
                    saveHistory();
                }
            }
        });

        // === ADD TO CART BUTTON ===
        $(document).on('click', '#swp-ls-add-to-cart', function (e) {
            e.preventDefault();
            saveAndAddToCart();
        });

        // === INITIALIZE ===
        function init() {
            const $app = $('#swp-ls-designer-app');
            const loadProductImg = $app.data('load-product-image');

            if (loadProductImg === true || loadProductImg === 'true') {
                loadProductImage();
            } else {
                loadTemplate('minimal');
            }

            history = [JSON.stringify(canvas.toJSON(['name']))];
            historyIndex = 0;
            setDirty(false);
            updatePropsPanel();
            renderLayers();
            applyZoom();
        }

        init();
    });

})(jQuery);