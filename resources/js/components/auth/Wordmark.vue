<template>
  <div
    class="wordmark relative inline-flex items-start select-none"
    :class="[`mode-${mode}`, { animated, intro }]"
    role="img"
    aria-label="creatifoto foto y video"
    :style="{ fontSize: size }"
  >
    <span class="creati" aria-hidden="true">
      <span v-for="(ch, i) in 'creati'" :key="'c' + i" class="ch" :style="vars(i)">{{ ch }}</span>
    </span>

    <span class="flex flex-col">
      <span class="flex items-end">
        <span class="foto" aria-hidden="true">
          <span v-for="(ch, i) in 'foto'" :key="'f' + i" class="ch" :style="vars(i + 6)">{{ ch }}</span>
        </span>
        <span class="ch reg" aria-hidden="true" :style="vars(10)">®</span>
      </span>

      <span class="sub" aria-hidden="true">
        <span v-for="(ch, i) in 'foto y video'" :key="'s' + i" class="ch" :style="vars(i + 11)">{{
          ch === ' ' ? '\u00A0' : ch
        }}</span>
      </span>
    </span>
  </div>
</template>

<script>
const TOTAL = 23;
const SMALL_FROM = 10;

const SPREAD = Array.from({ length: TOTAL }, (_, i) => {
  const t = i / (TOTAL - 1) - 0.5;
  const k = i >= SMALL_FROM ? 1 / 0.3 : 1;
  const side = i % 2 ? 1 : -1;
  return {
    dx: `${(t * 20 * k).toFixed(1)}em`,
    dy: `${(side * (2.5 + (i % 3) * 0.7) * k).toFixed(1)}em`,
    rot: `${side * (6 + (i % 4) * 3)}deg`,
  };
});

export default {
  name: 'Wordmark',
  props: {
    size: { type: String, default: '2.5rem' },
    mode: { type: String, default: 'idle' },
    animated: { type: Boolean, default: false },
  },
  data() {
    return { intro: this.animated };
  },
  watch: {
    mode(value) {
      const letters = this.$el.querySelectorAll('.ch');

      if (value === 'open') {
        letters.forEach((el) => {
          const cs = getComputedStyle(el);
          el.style.translate = cs.translate;
          el.style.scale = cs.scale;
        });
      } else {
        letters.forEach((el) => {
          el.style.translate = '';
          el.style.scale = '';
        });
      }
    },
  },
  mounted() {
    if (!this.animated) return;
    this.timer = window.setTimeout(() => (this.intro = false), 2200);
  },
  beforeUnmount() {
    window.clearTimeout(this.timer);
  },
  methods: {
    vars(n) {
      const s = SPREAD[n];
      return { '--i': n, '--dx': s.dx, '--dy': s.dy, '--rot': s.rot };
    },
  },
};
</script>

<style scoped>
.wordmark {
  font-family: 'Montserrat', 'Century Gothic', 'Avenir Next', ui-sans-serif, system-ui, sans-serif;
  font-weight: 400;
  line-height: 1;
  letter-spacing: -0.02em;
}
.creati { color: #9c9ca0; }
.foto   { color: #ffbe00; }

.reg {
  color: #ffbe00;
  font-size: 0.3em;
  line-height: 1;
  font-weight: 400;
  margin: 0 0 0.2em 0.12em;
}
.sub {
  color: #9c9ca0;
  font-size: 0.3em;
  font-weight: 300;
  letter-spacing: 0.02em;
  margin-top: 0.35em;
  white-space: nowrap;
}

.ch {
  display: inline-block;
}

.animated .ch {
  animation:
    breathe 5s ease-in-out infinite,
    grow 7s ease-in-out infinite;
  animation-delay:
    calc(var(--i) * 0.2s),
    calc(var(--i) * 0.1s + 1.5s);
}
@keyframes breathe {
  0%, 100% { translate: 0 0; }
  50%      { translate: 0 -0.06em; }
}
@keyframes grow {
  0%, 8%, 100% { scale: 1; }
  4%           { scale: 1.18; }
}

.animated.intro .ch {
  animation: focus-in 0.8s cubic-bezier(0.2, 0.7, 0.2, 1) both;
  animation-delay: calc(var(--i) * 45ms + 150ms);
}
@keyframes focus-in {
  from { opacity: 0; filter: blur(0.12em); transform: translateY(0.25em) scale(0.92); }
  to   { opacity: 1; filter: blur(0);      transform: none; }
}

.animated.mode-focus .ch {
  animation:
    breathe 5s ease-in-out infinite,
    grow-fast 1s ease-in-out infinite;
  animation-delay:
    calc(var(--i) * 0.2s),
    calc(var(--i) * 45ms);
}
@keyframes grow-fast {
  0%, 50%, 100% { scale: 1; }
  25%           { scale: 1.2; }
}

.animated.mode-open .ch {
  animation: scatter 0.9s cubic-bezier(0.5, 0, 0.75, 0) forwards;
  animation-delay: calc(var(--i) * 15ms + 100ms);
}
@keyframes scatter {
  to {
    opacity: 0;
    filter: blur(0.2em);
    transform: translate(var(--dx), var(--dy)) rotate(var(--rot)) scale(1.4);
  }
}

@media (prefers-reduced-motion: reduce) {
  .animated .ch,
  .animated.intro .ch,
  .animated.mode-focus .ch {
    animation: none;
  }
  .animated.mode-open .ch {
    animation: none;
    opacity: 0;
    transition: opacity 0.4s ease;
  }
}
</style>