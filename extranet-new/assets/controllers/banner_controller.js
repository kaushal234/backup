import { Controller } from '@hotwired/stimulus';

/*
 * Banner rotator for the home page.
 * Markup (see index.html.twig):
 *   <section class="banner-carousel" data-controller="banner">
 *     <div class="banner-track" data-banner-target="track"> …slides… </div>
 *     <button data-action="banner#prev">…</button>
 *     <button data-action="banner#next">…</button>
 *     <div class="banner-dots" data-banner-target="dots"></div>
 *   </section>
 */
export default class extends Controller {
    static targets = ['track', 'dots'];

    connect() {
        this.index = 0;
        this.slides = this.trackTarget.children.length;
        this.buildDots();
        this.render();
        this.timer = setInterval(() => this.next(), 6000);
        // pause on hover
        this.element.addEventListener('mouseenter', () => clearInterval(this.timer));
        this.element.addEventListener('mouseleave', () => {
            this.timer = setInterval(() => this.next(), 6000);
        });
    }

    disconnect() { clearInterval(this.timer); }

    buildDots() {
        if (!this.hasDotsTarget) return;
        this.dotsTarget.innerHTML = '';
        for (let i = 0; i < this.slides; i++) {
            const b = document.createElement('button');
            b.className = 'banner-dot';
            b.addEventListener('click', () => this.goTo(i));
            this.dotsTarget.appendChild(b);
        }
    }

    goTo(i) { this.index = (i + this.slides) % this.slides; this.render(); }
    next() { this.goTo(this.index + 1); }
    prev() { this.goTo(this.index - 1); }

    render() {
        this.trackTarget.style.transform = `translateX(-${this.index * 100}%)`;
        if (this.hasDotsTarget) {
            [...this.dotsTarget.children].forEach((d, i) =>
                d.classList.toggle('active', i === this.index));
        }
    }
}
