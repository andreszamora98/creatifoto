<template>
  <svg
    class="lens"
    viewBox="-112 -112 224 224"
    role="img"
    aria-label="Diafragma de cámara"
  >
    <defs>
      <linearGradient id="lens-leaf" x1="0" y1="-1" x2="0" y2="1">
        <stop offset="0" style="stop-color: var(--leaf-a)" />
        <stop offset="1" style="stop-color: var(--leaf-b)" />
      </linearGradient>
      <radialGradient id="lens-glow" cx="0" cy="0" r="1">
        <stop offset="0" stop-color="#fff3c4" />
        <stop offset="0.55" stop-color="#ffd24d" />
        <stop offset="1" stop-color="#ffbe00" />
      </radialGradient>
      <radialGradient id="lens-halo" cx="0" cy="0" r="1">
        <stop offset="0" stop-color="#ffbe00" stop-opacity="0.55" />
        <stop offset="1" stop-color="#ffbe00" stop-opacity="0" />
      </radialGradient>
    </defs>

    <!-- Halo de luz detrás del diafragma -->
    <circle r="108" fill="url(#lens-halo)" :opacity="haloOpacity" />

    <!-- Marcas del barril del lente -->
    <g class="ring ring-slow">
      <circle r="107" fill="none" stroke="var(--ring)" stroke-width="0.8" stroke-dasharray="2 6" />
    </g>
    <circle r="102.5" fill="none" stroke="var(--ring)" stroke-width="1.2" />

    <!-- Luz que atraviesa la apertura -->
    <polygon :points="hole" fill="url(#lens-glow)" />

    <!-- Aspas -->
    <g stroke="var(--seam)" stroke-width="0.9" stroke-linejoin="round">
      <path v-for="(d, i) in leaves" :key="i" :d="d" fill="url(#lens-leaf)" />
    </g>

    <!-- Borde amarillo de la apertura -->
    <polygon
      :points="hole"
      fill="none"
      stroke="#ffbe00"
      stroke-width="1.6"
      stroke-linejoin="round"
    />
  </svg>
</template>

<script>
const R = 100; // radio exterior de las aspas
const SIDES = 6;

function leafPaths(rho, phi) {
  const V = [];
  for (let k = 0; k < SIDES; k++) {
    const a = phi + (k * 2 * Math.PI) / SIDES;
    V.push([rho * Math.cos(a), rho * Math.sin(a)]);
  }

  const E = V.map((A, k) => {
    const B = V[(k + 1) % SIDES];
    const dx = B[0] - A[0];
    const dy = B[1] - A[1];
    const len = Math.hypot(dx, dy) || 1;
    const ux = dx / len;
    const uy = dy / len;
    const b = B[0] * ux + B[1] * uy;
    const t = -b + Math.sqrt(Math.max(b * b - (B[0] ** 2 + B[1] ** 2) + R * R, 0));
    return [B[0] + t * ux, B[1] + t * uy];
  });

  const f = (n) => n.toFixed(2);
  const pt = (p) => `${f(p[0])} ${f(p[1])}`;

  return V.map((A, k) => {
    const B = V[(k + 1) % SIDES];
    const prev = E[(k + SIDES - 1) % SIDES];
    return `M${pt(A)} L${pt(B)} L${pt(E[k])} A${R} ${R} 0 0 0 ${pt(prev)} Z`;
  });
}

export default {
  name: 'ApertureLens',
  props: {
    // idle | focus | open | blink
    mode: { type: String, default: 'idle' },
  },
  data() {
    return {
      rho: 20,
      phi: 0.35,
      raf: 0,
      last: 0,
      time: 0,
      blinkStart: -1,
      reduced: false,
    };
  },
  computed: {
    leaves() {
      return leafPaths(this.rho, this.phi);
    },
    hole() {
      const pts = [];
      for (let k = 0; k < SIDES; k++) {
        const a = this.phi + (k * 2 * Math.PI) / SIDES;
        pts.push(`${(this.rho * Math.cos(a)).toFixed(2)},${(this.rho * Math.sin(a)).toFixed(2)}`);
      }
      return pts.join(' ');
    },
    haloOpacity() {
      return Math.min(1, 0.35 + this.rho / 90);
    },
  },
  watch: {
    mode(value) {
      if (value === 'blink') this.blinkStart = this.time;
    },
  },
  mounted() {
    this.reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    this.last = performance.now();
    this.raf = requestAnimationFrame(this.tick);
  },
  beforeUnmount() {
    cancelAnimationFrame(this.raf);
  },
  methods: {
    target() {
      const t = this.time;
      const calm = this.reduced ? 0 : 1;

      if (this.mode === 'open') return 88;

      if (this.mode === 'focus') return 9 + calm * 1.6 * Math.sin(t * 7);

      if (this.mode === 'blink') {
        const e = t - this.blinkStart;
        return e < 0.18 ? 3 : 20;
      }

      return 20 + calm * 3.2 * Math.sin(t * 0.9);
    },
    tick(now) {
      const dt = Math.min((now - this.last) / 1000, 0.05);
      this.last = now;
      this.time += dt;

      const speed = this.mode === 'open' ? 3.2 : this.mode === 'blink' ? 16 : 5;
      this.rho += (this.target() - this.rho) * (1 - Math.exp(-speed * dt));

      if (!this.reduced) {
        const spin = this.mode === 'open' ? 0.9 : this.mode === 'focus' ? 0.5 : 0.06;
        this.phi += spin * dt;
      }

      this.raf = requestAnimationFrame(this.tick);
    },
  },
};
</script>

<style scoped>
.lens {
  --leaf-a: #e4e4e8;
  --leaf-b: #c3c3c9;
  --seam: rgba(255, 255, 255, 0.85);
  --ring: #b4b4ba;
  width: 100%;
  height: 100%;
  overflow: visible;
  filter: drop-shadow(0 18px 28px rgba(60, 60, 70, 0.18));
}
:global(.dark .lens) {
  --leaf-a: #3d414b;
  --leaf-b: #1b1d24;
  --seam: rgba(0, 0, 0, 0.55);
  --ring: #5a5d68;
  filter: drop-shadow(0 18px 34px rgba(0, 0, 0, 0.55));
}

.ring {
  transform-box: fill-box;
  transform-origin: center;
}
.ring-slow {
  animation: ring-spin 60s linear infinite;
}
@keyframes ring-spin {
  to {
    transform: rotate(360deg);
  }
}

@media (prefers-reduced-motion: reduce) {
  .ring-slow {
    animation: none;
  }
}
</style>
