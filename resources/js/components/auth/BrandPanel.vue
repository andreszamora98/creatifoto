<template>
  <aside class="panel relative isolate overflow-hidden">
    <!-- Bokeh -->
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
      <span
        v-for="(b, i) in bokeh"
        :key="i"
        class="bokeh"
        :class="b.tone"
        :style="{
          width: b.size + 'rem',
          height: b.size + 'rem',
          left: b.x + '%',
          top: b.y + '%',
          '--blur': b.blur + 'px',
          '--dur': b.dur + 's',
          '--delay': b.delay + 's',
          '--dx': b.dx + 'rem',
          '--dy': b.dy + 'rem',
        }"
      ></span>
    </div>

    <!-- Destello al iniciar sesión -->
    <div class="flash pointer-events-none absolute inset-0 -z-10" :class="{ 'flash-on': flash }" aria-hidden="true"></div>

    <div class="relative flex h-full flex-col items-center justify-center gap-4 px-6 py-6 lg:gap-10 lg:px-12 lg:py-12">
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

// Posiciones y tamaños fijos: así el fondo no cambia entre cargas.
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

/* ---- Bokeh ---- */
.bokeh {
  position: absolute;
  border-radius: 9999px;
  filter: blur(var(--blur));
  animation: drift var(--dur) ease-in-out var(--delay) infinite alternate;
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

@keyframes drift {
  from {
    transform: translate3d(0, 0, 0) scale(1);
    opacity: 0.75;
  }
  to {
    transform: translate3d(var(--dx), var(--dy), 0) scale(1.08);
    opacity: 1;
  }
}

/* ---- Destello ---- */
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

@media (prefers-reduced-motion: reduce) {
  .bokeh {
    animation: none;
  }
}
</style>
