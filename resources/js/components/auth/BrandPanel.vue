<template>
  <aside class="panel relative isolate overflow-hidden">
    <div ref="field" class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
      <span
        v-for="(b, i) in bokeh"
        :key="i"
        class="bokeh"
        :class="b.tone"
        @pointerdown="onDown($event, i)"
        @pointermove="onMove($event, i)"
        @pointerup="onUp($event, i)"
        @pointercancel="onUp($event, i)"
        @contextmenu.prevent
      ></span>
    </div>

    <div class="flash pointer-events-none absolute inset-0 -z-10" :class="{ 'flash-on': flash }" aria-hidden="true"></div>

    <div class="pointer-events-none relative flex h-full flex-col items-center justify-center gap-4 px-6 py-6 lg:gap-10 lg:px-12 lg:py-12">
      <div class="hidden lg:block"><Wordmark size="3.1rem" :mode="mode" animated /></div>
      <div class="lg:hidden"><Wordmark size="1.9rem" :mode="mode" animated /></div>
      <div class="lens-wrap">
        <ApertureLens :mode="mode" />
      </div>

      <div class="hidden max-w-sm text-center lg:block">
        <p class="tagline text-2xl font-light leading-snug text-gray-700 dark:text-gray-200">
          Cada sesión,<br />
          <span class="font-medium text-[#d99f00] dark:text-[#ffbe00]">bien enfocada.</span>
        </p>
        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
          Órdenes, cotizaciones y paquetes escolares en un solo lugar.
        </p>
      </div>
    </div>
  </aside>
</template>

<script>
import Wordmark from './Wordmark.vue';
import ApertureLens from './ApertureLens.vue';

const BOKEH = [
  { size: 9,   x: 6,  y: 8,  blur: 2,  dur: 16, delay: 0,   dx: 2,  dy: 2.5,  tone: 'b-gold' },
  { size: 5,   x: 78, y: 12, blur: 1,  dur: 12, delay: -4,  dx: -1.5, dy: 2,  tone: 'b-gray' },
  { size: 13,  x: 62, y: 58, blur: 5,  dur: 20, delay: -8,  dx: -2.5, dy: -2, tone: 'b-gold' },
  { size: 4,   x: 14, y: 64, blur: 0,  dur: 10, delay: -2,  dx: 1.5, dy: -2.5, tone: 'b-gold' },
  { size: 7,   x: 38, y: 82, blur: 3,  dur: 18, delay: -6,  dx: 2,  dy: -1.5, tone: 'b-gray' },
  { size: 3,   x: 86, y: 74, blur: 0,  dur: 9,  delay: -1,  dx: -1,  dy: 1.5, tone: 'b-gold' },
  { size: 10,  x: 82, y: 32, blur: 4,  dur: 22, delay: -11, dx: -2,  dy: 2.5, tone: 'b-gray' },
  { size: 2.5, x: 28, y: 20, blur: 0,  dur: 8,  delay: -3,  dx: 1,  dy: 1.5,  tone: 'b-gold' },
  { size: 6,   x: 48, y: 6,  blur: 2,  dur: 14, delay: -9,  dx: 1.5, dy: 2,   tone: 'b-gold' },
];

const REM = 16;
const REF = 720;
const MIN_SCALE = 0.3;
const MIN_R = 13;
const DRIFT = 2.5;
const RELAX = 0.5;
const WALL_E = 0.88;
const BALL_E = 0.94;
const MAX_V = 2200;
const KICK = 380;
const SUBSTEPS = 3;
const TAP_SLOP = 6;
const TAP_SLOP_TOUCH = 12;

const clamp = (v, lo, hi) => Math.min(Math.max(lo, hi), Math.max(lo, v));

export default {
  name: 'BrandPanel',
  components: { Wordmark, ApertureLens },
  props: {
    mode: { type: String, default: 'idle' },
    flash: { type: Boolean, default: false },
  },
  data() {
    return { bokeh: BOKEH };
  },
  mounted() {
    const field = this.$refs.field;
    const els = field.querySelectorAll('.bokeh');
    const { width: w, height: h } = field.getBoundingClientRect();
    const calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    this.sim = {
      field, w, h, raf: 0, running: false,
      scale: this.scaleFor(w, h),
      last: performance.now(),
      bodies: this.bokeh.map((b, i) => {
        const bx = calm ? 0 : ((b.dx * REM) / b.dur) * DRIFT;
        const by = calm ? 0 : ((b.dy * REM) / b.dur) * DRIFT;
        return {
          el: els[i],
          base: b.size * REM, blur: b.blur, fx: b.x / 100, fy: b.y / 100,
          r: 0, m: 0, x: 0, y: 0,
          vx: bx, vy: by, bx, by,
          held: false, pid: null, ox: 0, oy: 0,
          lastMove: 0, samples: [], p0: null,
        };
      }),
    };

    this.sizeBodies();
    for (const b of this.sim.bodies) {
      b.x = clamp(b.fx * w + b.r, b.r, w - b.r);
      b.y = clamp(b.fy * h + b.r, b.r, h - b.r);
    }
    this.render();

    this.ro = new ResizeObserver(() => this.onResize());
    this.ro.observe(field);

    this.io = new IntersectionObserver(([entry]) => {
      if (entry.isIntersecting) this.start();
      else this.stop();
    });
    this.io.observe(field);

    this.start();
  },
  beforeUnmount() {
    this.stop();
    this.ro.disconnect();
    this.io.disconnect();
  },
  methods: {
    scaleFor(w, h) {
      return clamp(Math.sqrt(w * h) / REF, MIN_SCALE, 1);
    },

    sizeBodies() {
      const s = this.sim;
      for (const b of s.bodies) {
        b.r = Math.max((b.base * s.scale) / 2, MIN_R);
        b.m = b.r * b.r;
        b.el.style.width = b.r * 2 + 'px';
        b.el.style.height = b.r * 2 + 'px';
        b.el.style.setProperty('--blur', b.blur * s.scale + 'px');
      }
    },

    start() {
      const s = this.sim;
      if (!s || s.running) return;
      s.running = true;
      s.last = performance.now();
      s.raf = requestAnimationFrame(this.tick);
    },

    stop() {
      const s = this.sim;
      if (!s) return;
      s.running = false;
      cancelAnimationFrame(s.raf);
    },

    tick(t) {
      const s = this.sim;
      if (!s.running) return;
      const dt = Math.min((t - s.last) / 1000, 1 / 30);
      s.last = t;
      for (let k = 0; k < SUBSTEPS; k++) this.step(dt / SUBSTEPS, t);
      this.render();
      s.raf = requestAnimationFrame(this.tick);
    },

    render() {
      for (const b of this.sim.bodies) {
        b.el.style.transform = `translate3d(${b.x - b.r}px, ${b.y - b.r}px, 0)`;
      }
    },

    step(dt, now) {
      const { bodies, w, h } = this.sim;

      for (const b of bodies) {
        if (b.held) {
          if (now - b.lastMove > 60) { b.vx = 0; b.vy = 0; }
          continue;
        }
        const k = Math.min(1, RELAX * dt);
        b.vx += (b.bx - b.vx) * k;
        b.vy += (b.by - b.vy) * k;
        b.x += b.vx * dt;
        b.y += b.vy * dt;

        if (b.x < b.r)          { b.x = b.r;     b.vx =  Math.abs(b.vx) * WALL_E; b.bx =  Math.abs(b.bx); }
        else if (b.x > w - b.r) { b.x = w - b.r; b.vx = -Math.abs(b.vx) * WALL_E; b.bx = -Math.abs(b.bx); }
        if (b.y < b.r)          { b.y = b.r;     b.vy =  Math.abs(b.vy) * WALL_E; b.by =  Math.abs(b.by); }
        else if (b.y > h - b.r) { b.y = h - b.r; b.vy = -Math.abs(b.vy) * WALL_E; b.by = -Math.abs(b.by); }
      }

      for (let i = 0; i < bodies.length; i++) {
        for (let j = i + 1; j < bodies.length; j++) {
          const a = bodies[i], b = bodies[j];
          const dx = b.x - a.x, dy = b.y - a.y;
          const min = a.r + b.r;
          const d2 = dx * dx + dy * dy;
          if (d2 >= min * min || d2 === 0) continue;

          const d = Math.sqrt(d2);
          const nx = dx / d, ny = dy / d;
          const ia = a.held ? 0 : 1 / a.m;
          const ib = b.held ? 0 : 1 / b.m;
          const sum = ia + ib;
          if (!sum) continue;

          const overlap = min - d;
          a.x -= nx * overlap * (ia / sum); a.y -= ny * overlap * (ia / sum);
          b.x += nx * overlap * (ib / sum); b.y += ny * overlap * (ib / sum);

          const rv = (b.vx - a.vx) * nx + (b.vy - a.vy) * ny;
          if (rv > 0) continue;
          const imp = (-(1 + BALL_E) * rv) / sum;
          a.vx -= imp * ia * nx; a.vy -= imp * ia * ny;
          b.vx += imp * ib * nx; b.vy += imp * ib * ny;
        }
      }
    },

    onResize() {
      const s = this.sim;
      if (!s) return;
      const { width: w, height: h } = s.field.getBoundingClientRect();
      if (!w || !h) return;

      const fresh = !s.w || !s.h;
      const sx = fresh ? 1 : w / s.w;
      const sy = fresh ? 1 : h / s.h;
      s.w = w;
      s.h = h;
      s.scale = this.scaleFor(w, h);
      this.sizeBodies();

      for (const b of s.bodies) {
        if (fresh) {
          b.x = clamp(b.fx * w + b.r, b.r, w - b.r);
          b.y = clamp(b.fy * h + b.r, b.r, h - b.r);
        } else {
          b.x = clamp(b.x * sx, b.r, w - b.r);
          b.y = clamp(b.y * sy, b.r, h - b.r);
        }
      }
      this.render();
    },

    local(e) {
      const r = this.sim.field.getBoundingClientRect();
      return { x: e.clientX - r.left, y: e.clientY - r.top };
    },

    onDown(e, i) {
      const b = this.sim.bodies[i];
      if (b.held || (e.pointerType === 'mouse' && e.button !== 0)) return;
      const p = this.local(e);
      const now = performance.now();
      b.held = true;
      b.pid = e.pointerId;
      b.ox = p.x - b.x;
      b.oy = p.y - b.y;
      b.p0 = { x: e.clientX, y: e.clientY, t: now };
      b.samples = [{ x: b.x, y: b.y, t: now }];
      b.lastMove = now;
      b.vx = 0; b.vy = 0;
      b.el.dataset.held = '';
      b.el.setPointerCapture(e.pointerId);
    },

    onMove(e, i) {
      const b = this.sim.bodies[i];
      if (!b.held || e.pointerId !== b.pid) return;
      const s = this.sim;
      const p = this.local(e);
      const now = performance.now();

      b.x = clamp(p.x - b.ox, b.r, s.w - b.r);
      b.y = clamp(p.y - b.oy, b.r, s.h - b.r);

      b.samples.push({ x: b.x, y: b.y, t: now });
      while (b.samples.length > 2 && now - b.samples[0].t > 90) b.samples.shift();
      const f = b.samples[0];
      const dt = (now - f.t) / 1000;
      if (dt > 0) {
        b.vx = (b.x - f.x) / dt;
        b.vy = (b.y - f.y) / dt;
      }
      b.lastMove = now;
    },

    onUp(e, i) {
      const s = this.sim;
      const b = s.bodies[i];
      if (!b.held || e.pointerId !== b.pid) return;
      b.held = false;
      delete b.el.dataset.held;

      const vf = 0.55 + 0.45 * s.scale;
      const slop = e.pointerType === 'touch' ? TAP_SLOP_TOUCH : TAP_SLOP;
      const now = performance.now();
      if (now - b.lastMove > 80) { b.vx = 0; b.vy = 0; }

      const moved = Math.hypot(e.clientX - b.p0.x, e.clientY - b.p0.y);
      if (e.type === 'pointerup' && moved < slop && now - b.p0.t < 300) {
        const p = this.local(e);
        let dx = b.x - p.x, dy = b.y - p.y;
        let d = Math.hypot(dx, dy);
        if (d < 1) { const a = Math.random() * Math.PI * 2; dx = Math.cos(a); dy = Math.sin(a); d = 1; }
        b.vx = (dx / d) * KICK * vf;
        b.vy = (dy / d) * KICK * vf;
      }

      const max = MAX_V * vf;
      const sp = Math.hypot(b.vx, b.vy);
      if (sp > max) { b.vx *= max / sp; b.vy *= max / sp; }
    },
  },
};
</script>

<style scoped>
.panel {
  background:
    radial-gradient(120% 90% at 20% 0%, #fff8e0 0%, rgba(255, 248, 224, 0) 55%),
    linear-gradient(160deg, #fbfbfc 0%, #eeeef1 100%);
}
:global(.dark .panel) {
  background:
    radial-gradient(110% 80% at 15% 0%, rgba(255, 190, 0, 0.1) 0%, rgba(255, 190, 0, 0) 55%),
    linear-gradient(160deg, #14161c 0%, #0b0d12 100%);
}

.lens-wrap {
  width: 5.5rem;
  height: 5.5rem;
}
@media (min-width: 1024px) {
  .lens-wrap {
    width: min(26rem, 52vh, 100%);
    height: min(26rem, 52vh, 100%);
    aspect-ratio: 1;
  }
}

.bokeh {
  position: absolute;
  left: 0;
  top: 0;
  border-radius: 9999px;
  opacity: 0.9;
  filter: blur(var(--blur));
  will-change: transform;
  pointer-events: auto;
  cursor: grab;
  touch-action: none;
  user-select: none;
  -webkit-user-select: none;
  -webkit-touch-callout: none;
  -webkit-tap-highlight-color: transparent;
  transition: scale 0.2s ease, filter 0.2s ease;
}
.bokeh[data-held] {
  cursor: grabbing;
  scale: 1.08;
  filter: blur(0);
}
@media (pointer: coarse) {
  .bokeh::before {
    content: '';
    position: absolute;
    inset: -8px;
    border-radius: 9999px;
  }
}
.b-gold {
  background: radial-gradient(circle at 35% 30%, rgba(255, 214, 90, 0.55), rgba(255, 190, 0, 0.22) 60%, rgba(255, 190, 0, 0.05) 100%);
  box-shadow: inset 0 0 0 1px rgba(255, 190, 0, 0.35);
}
.b-gray {
  background: radial-gradient(circle at 35% 30%, rgba(190, 190, 198, 0.5), rgba(156, 156, 160, 0.2) 60%, rgba(156, 156, 160, 0.04) 100%);
  box-shadow: inset 0 0 0 1px rgba(156, 156, 160, 0.35);
}
:global(.dark .b-gold) {
  background: radial-gradient(circle at 35% 30%, rgba(255, 205, 70, 0.4), rgba(255, 190, 0, 0.14) 60%, rgba(255, 190, 0, 0.03) 100%);
  box-shadow: inset 0 0 0 1px rgba(255, 190, 0, 0.3);
}
:global(.dark .b-gray) {
  background: radial-gradient(circle at 35% 30%, rgba(170, 170, 180, 0.28), rgba(120, 120, 130, 0.1) 60%, rgba(120, 120, 130, 0.02) 100%);
  box-shadow: inset 0 0 0 1px rgba(156, 156, 160, 0.22);
}

.flash {
  background: radial-gradient(circle at 50% 50%, rgba(255, 224, 120, 0.95) 0%, rgba(255, 190, 0, 0.55) 35%, rgba(255, 190, 0, 0) 70%);
  opacity: 0;
  transform: scale(0.6);
  transition: opacity 0.7s ease, transform 0.9s cubic-bezier(0.2, 0.7, 0.2, 1);
}
.flash-on {
  opacity: 1;
  transform: scale(1.6);
}
</style>