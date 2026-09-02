import { Controller } from '@hotwired/stimulus';

/*
 * Before/after image comparison slider (Major components repairs page).
 * Markup:
 *   <div class="ba-stage" data-controller="before-after" style="--split:50%">
 *     <div class="ba-layer before"> <img …> </div>
 *     <div class="ba-layer after">  <img …> </div>
 *     <span class="ba-badge before">Before</span>
 *     <span class="ba-badge after">After</span>
 *     <div class="ba-divider"></div>
 *     <div class="ba-knob" data-before-after-target="knob">…</div>
 *   </div>
 */
export default class extends Controller {
    connect() {
        this.dragging = false;
        this._move = (e) => this.onMove(e);
        this._up = () => { this.dragging = false; };
        this.element.addEventListener('mousedown', (e) => this.start(e));
        this.element.addEventListener('touchstart', (e) => this.start(e), { passive: true });
        window.addEventListener('mousemove', this._move);
        window.addEventListener('touchmove', this._move, { passive: false });
        window.addEventListener('mouseup', this._up);
        window.addEventListener('touchend', this._up);
    }

    disconnect() {
        window.removeEventListener('mousemove', this._move);
        window.removeEventListener('touchmove', this._move);
        window.removeEventListener('mouseup', this._up);
        window.removeEventListener('touchend', this._up);
    }

    start(e) { this.dragging = true; this.setFrom(e); }

    onMove(e) { if (this.dragging) this.setFrom(e); }

    setFrom(e) {
        const rect = this.element.getBoundingClientRect();
        const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
        const pct = Math.max(2, Math.min(98, (x / rect.width) * 100));
        this.element.style.setProperty('--split', pct + '%');
    }
}
