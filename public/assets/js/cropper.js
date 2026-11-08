/*
 * SIMDA image cropper: a small, dependency-free crop dialog.
 *
 *   SimdaCropper.attach(fileInput, { aspect: 1, round: true, outWidth: 600, onCropped(file, url) {} })
 *
 * When a picture is chosen, a dialog lets the user drag to position it and zoom with the slider or mouse wheel.
 * "Crop" replaces the input's file with the cropped PNG, so the normal form upload sends the cropped picture.
 * "Use original" keeps the file as chosen.
 */
(function () {
    'use strict';
    var isSo = (document.documentElement.lang || '').indexOf('so') === 0;
    var T = isSo
        ? { title: 'Jar sawirka', hint: 'Jiid si aad u dhaqaajiso · isticmaal slider-ka si aad u weyneyso', zoom: 'Weynee', cancel: 'Jooji', original: 'Sidiisa u isticmaal', crop: 'Jar & Kaydi', reset: 'Dib u celi' }
        : { title: 'Crop picture', hint: 'Drag to move · use the slider or mouse wheel to zoom', zoom: 'Zoom', cancel: 'Cancel', original: 'Use original', crop: 'Crop & Use', reset: 'Reset' };

    function buildDialog() {
        var wrap = document.createElement('div');
        wrap.className = 'scrop';
        wrap.setAttribute('role', 'dialog');
        wrap.setAttribute('aria-modal', 'true');
        wrap.innerHTML =
            '<div class="scrop-backdrop"></div>' +
            '<div class="scrop-box">' +
            '  <div class="scrop-head"><strong>' + T.title + '</strong><span>' + T.hint + '</span></div>' +
            '  <div class="scrop-stage"><img class="scrop-img" alt="" draggable="false"><div class="scrop-mask"></div></div>' +
            '  <div class="scrop-zoom"><span>' + T.zoom + '</span><input type="range" class="scrop-range" min="0" max="100" value="0" aria-label="' + T.zoom + '"><button type="button" class="scrop-reset">' + T.reset + '</button></div>' +
            '  <div class="scrop-actions">' +
            '    <button type="button" class="btn btn-outline scrop-cancel">' + T.cancel + '</button>' +
            '    <button type="button" class="btn btn-outline scrop-original">' + T.original + '</button>' +
            '    <button type="button" class="btn btn-primary scrop-ok">' + T.crop + '</button>' +
            '  </div>' +
            '</div>';
        document.body.appendChild(wrap);
        return wrap;
    }

    function open(file, opts) {
        return new Promise(function (resolve) {
            var aspect = opts.aspect || 1;
            var dlg = buildDialog();
            var stage = dlg.querySelector('.scrop-stage');
            var img = dlg.querySelector('.scrop-img');
            var range = dlg.querySelector('.scrop-range');
            dlg.classList.toggle('is-round', !!opts.round);

            // Stage size follows the aspect ratio, within 340px.
            var SW = aspect >= 1 ? 340 : Math.round(340 * aspect);
            var SH = aspect >= 1 ? Math.round(340 / aspect) : 340;
            stage.style.width = SW + 'px';
            stage.style.height = SH + 'px';

            var url = URL.createObjectURL(file);
            var iw = 0, ih = 0, minScale = 1, maxScale = 1, scale = 1, x = 0, y = 0;

            function apply() {
                img.style.width = (iw * scale) + 'px';
                img.style.height = (ih * scale) + 'px';
                img.style.transform = 'translate(' + x + 'px,' + y + 'px)';
            }
            function setScale(s, cx, cy) {
                s = Math.max(minScale, Math.min(maxScale, s));
                // Zoom around the point (cx, cy) in stage coordinates.
                cx = cx === undefined ? SW / 2 : cx;
                cy = cy === undefined ? SH / 2 : cy;
                x = cx - (cx - x) * (s / scale);
                y = cy - (cy - y) * (s / scale);
                scale = s;
                range.value = Math.round((scale - minScale) / (maxScale - minScale || 1) * 100);
                apply();
            }
            function reset() {
                // Start with the whole picture visible ("contain"); zoom in from there.
                scale = minScale;
                x = (SW - iw * scale) / 2;
                y = (SH - ih * scale) / 2;
                range.value = 0;
                apply();
            }

            img.onload = function () {
                iw = img.naturalWidth;
                ih = img.naturalHeight;
                minScale = Math.min(SW / iw, SH / ih);
                maxScale = Math.max(SW / iw, SH / ih) * 4;
                reset();
                range.focus();
            };
            img.src = url;

            range.addEventListener('input', function () {
                setScale(minScale + (maxScale - minScale) * (range.value / 100));
            });
            dlg.querySelector('.scrop-reset').addEventListener('click', reset);
            stage.addEventListener('wheel', function (e) {
                e.preventDefault();
                var r = stage.getBoundingClientRect();
                setScale(scale * (e.deltaY < 0 ? 1.08 : 1 / 1.08), e.clientX - r.left, e.clientY - r.top);
            }, { passive: false });

            var drag = null;
            stage.addEventListener('pointerdown', function (e) {
                drag = { px: e.clientX, py: e.clientY, x: x, y: y };
                stage.setPointerCapture(e.pointerId);
                stage.classList.add('is-dragging');
            });
            stage.addEventListener('pointermove', function (e) {
                if (!drag) return;
                x = drag.x + (e.clientX - drag.px);
                y = drag.y + (e.clientY - drag.py);
                apply();
            });
            function endDrag() { drag = null; stage.classList.remove('is-dragging'); }
            stage.addEventListener('pointerup', endDrag);
            stage.addEventListener('pointercancel', endDrag);

            // Arrow keys move, +/- zoom (keyboard users).
            dlg.addEventListener('keydown', function (e) {
                var step = e.shiftKey ? 20 : 5;
                if (e.key === 'Escape') { finish(null); return; }
                if (e.target === range && (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) return;
                if (e.key === 'ArrowLeft') { x -= step; } else if (e.key === 'ArrowRight') { x += step; }
                else if (e.key === 'ArrowUp') { y -= step; } else if (e.key === 'ArrowDown') { y += step; }
                else if (e.key === '+' || e.key === '=') { setScale(scale * 1.08); return; }
                else if (e.key === '-') { setScale(scale / 1.08); return; }
                else { return; }
                e.preventDefault();
                apply();
            });

            function finish(result) {
                URL.revokeObjectURL(url);
                dlg.remove();
                resolve(result);
            }
            dlg.querySelector('.scrop-cancel').addEventListener('click', function () { finish(null); });
            dlg.querySelector('.scrop-backdrop').addEventListener('click', function () { finish(null); });
            dlg.querySelector('.scrop-original').addEventListener('click', function () { finish(file); });
            dlg.querySelector('.scrop-ok').addEventListener('click', function () {
                var outW = opts.outWidth || 600;
                var outH = Math.round(outW / aspect);
                var k = outW / SW;
                var canvas = document.createElement('canvas');
                canvas.width = outW;
                canvas.height = outH;
                var ctx = canvas.getContext('2d');
                ctx.imageSmoothingQuality = 'high';
                ctx.drawImage(img, x * k, y * k, iw * scale * k, ih * scale * k);
                canvas.toBlob(function (blob) {
                    if (!blob) { finish(file); return; }
                    var name = (file.name || 'image').replace(/\.[^.]+$/, '') + '-cropped.png';
                    finish(new File([blob], name, { type: 'image/png' }));
                }, 'image/png');
            });
        });
    }

    function attach(input, opts) {
        if (!input) return;
        opts = opts || {};
        input.addEventListener('change', function () {
            var f = input.files && input.files[0];
            if (!f || !/^image\/(png|jpe?g|webp)$/i.test(f.type)) return;
            open(f, opts).then(function (result) {
                if (!result) { input.value = ''; return; }
                try {
                    var dt = new DataTransfer();
                    dt.items.add(result);
                    input.files = dt.files; // setting files does not fire "change" again
                } catch (e) {
                    // Very old browsers cannot replace the file: the original upload is kept.
                }
                if (typeof opts.onCropped === 'function') opts.onCropped(result, URL.createObjectURL(result));
            });
        });
    }

    window.SimdaCropper = { open: open, attach: attach };
})();
