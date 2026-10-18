<template>
  <div class="min-h-dvh bg-white text-gray-900 dark:bg-[#0b0d12] dark:text-gray-100 lg:grid lg:grid-cols-2">
    <BrandPanel :mode="apertureMode" :flash="flash" class="h-52 lg:h-auto" />

    <main class="relative flex min-h-[calc(100dvh-13rem)] flex-col items-center justify-center px-6 py-10 lg:min-h-dvh lg:px-16">
      <button
        type="button"
        class="absolute right-4 top-4 rounded-full p-2 text-gray-500 transition hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-[#ffbe00] dark:text-gray-400 dark:hover:bg-gray-800"
        :aria-label="dark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
        @click="toggleTheme"
      >
        <i :class="['ti', dark ? 'ti-moon' : 'ti-sun']" class="text-xl" aria-hidden="true"></i>
      </button>

      <div class="w-full max-w-sm" :class="{ shake }">
        <transition name="view" mode="out-in">
          <section v-if="view === 'login'" key="login">
            <header class="stagger" style="--i: 0">
              <h1 class="text-3xl font-semibold tracking-tight">Bienvenido</h1>
              <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                Inicia sesión para entrar al panel de Creatifoto.
              </p>
            </header>

            <p
              v-if="notice"
              class="stagger mt-6 flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
              role="status"
              style="--i: 1"
            >
              <i class="ti ti-circle-check mt-0.5 shrink-0 text-base" aria-hidden="true"></i>
              <span>{{ notice }}</span>
            </p>

            <form class="mt-8 space-y-5" novalidate @submit.prevent="submitLogin">
              <AuthField
                v-model="login.email"
                class="stagger"
                style="--i: 2"
                label="Correo electrónico"
                name="email"
                type="email"
                icon="ti-mail"
                autocomplete="username"
                placeholder="tucorreo@ejemplo.com"
                :error="errors.email"
                autofocus
              />
              <AuthField
                v-model="login.password"
                class="stagger"
                style="--i: 3"
                label="Contraseña"
                name="password"
                type="password"
                icon="ti-lock"
                autocomplete="current-password"
                placeholder="••••••••"
                :error="errors.password"
              />

              <div class="stagger flex items-center justify-between text-sm" style="--i: 4">
                <label class="flex cursor-pointer items-center gap-2 text-gray-600 dark:text-gray-300">
                  <input
                    v-model="login.remember"
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 accent-[#ffbe00] dark:border-gray-600"
                  />
                  Mantener sesión
                </label>
                <button type="button" class="link" @click="go('forgot')">¿Olvidaste tu contraseña?</button>
              </div>

              <p v-if="formError" class="alert" role="alert">
                <i class="ti ti-alert-circle mt-0.5 shrink-0 text-base" aria-hidden="true"></i>
                <span>{{ formError }}</span>
              </p>

              <button type="submit" class="btn stagger" style="--i: 5" :disabled="loading || success">
                <span v-if="success" class="inline-flex items-center gap-2">
                  <i class="ti ti-check text-lg" aria-hidden="true"></i> Listo
                </span>
                <span v-else-if="loading" class="inline-flex items-center gap-2">
                  <span class="spinner" aria-hidden="true"></span> Entrando…
                </span>
                <span v-else>Entrar</span>
              </button>
            </form>
          </section>

          <section v-else-if="view === 'forgot'" key="forgot">
            <header class="stagger" style="--i: 0">
              <h1 class="text-3xl font-semibold tracking-tight">Recupera tu acceso</h1>
              <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                Escribe tu correo y te enviaremos un enlace para crear una contraseña nueva.
              </p>
            </header>

            <div v-if="sent" class="mt-8">
              <div
                class="stagger flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
                role="status"
                style="--i: 1"
              >
                <i class="ti ti-mail-check mt-0.5 shrink-0 text-xl" aria-hidden="true"></i>
                <span>{{ sent }}</span>
              </div>
              <button type="button" class="btn-ghost stagger mt-5" style="--i: 2" @click="go('login')">
                <i class="ti ti-arrow-left" aria-hidden="true"></i> Volver a iniciar sesión
              </button>
            </div>

            <form v-else class="mt-8 space-y-5" novalidate @submit.prevent="submitForgot">
              <AuthField
                v-model="forgot.email"
                class="stagger"
                style="--i: 1"
                label="Correo electrónico"
                name="forgot-email"
                type="email"
                icon="ti-mail"
                autocomplete="email"
                placeholder="tucorreo@ejemplo.com"
                :error="errors.email"
                autofocus
              />

              <p v-if="formError" class="alert" role="alert">
                <i class="ti ti-alert-circle mt-0.5 shrink-0 text-base" aria-hidden="true"></i>
                <span>{{ formError }}</span>
              </p>

              <button type="submit" class="btn stagger" style="--i: 2" :disabled="loading">
                <span v-if="loading" class="inline-flex items-center gap-2">
                  <span class="spinner" aria-hidden="true"></span> Enviando…
                </span>
                <span v-else>Enviar enlace</span>
              </button>
              <button type="button" class="btn-ghost stagger" style="--i: 3" @click="go('login')">
                <i class="ti ti-arrow-left" aria-hidden="true"></i> Volver a iniciar sesión
              </button>
            </form>
          </section>

          <section v-else key="reset">
            <header class="stagger" style="--i: 0">
              <h1 class="text-3xl font-semibold tracking-tight">Nueva contraseña</h1>
              <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                Elige una contraseña de al menos 8 caracteres.
              </p>
            </header>

            <form class="mt-8 space-y-5" novalidate @submit.prevent="submitReset">
              <AuthField
                v-model="reset.email"
                class="stagger"
                style="--i: 1"
                label="Correo electrónico"
                name="reset-email"
                type="email"
                icon="ti-mail"
                autocomplete="username"
                readonly
                :error="errors.email"
              />
              <AuthField
                v-model="reset.password"
                class="stagger"
                style="--i: 2"
                label="Contraseña nueva"
                name="reset-password"
                type="password"
                icon="ti-lock"
                autocomplete="new-password"
                :error="errors.password"
                autofocus
              />
              <AuthField
                v-model="reset.password_confirmation"
                class="stagger"
                style="--i: 3"
                label="Confirma la contraseña"
                name="reset-password-confirmation"
                type="password"
                icon="ti-lock-check"
                autocomplete="new-password"
              />

              <p v-if="formError" class="alert" role="alert">
                <i class="ti ti-alert-circle mt-0.5 shrink-0 text-base" aria-hidden="true"></i>
                <span>{{ formError }}</span>
              </p>

              <button type="submit" class="btn stagger" style="--i: 4" :disabled="loading">
                <span v-if="loading" class="inline-flex items-center gap-2">
                  <span class="spinner" aria-hidden="true"></span> Guardando…
                </span>
                <span v-else>Guardar contraseña</span>
              </button>
              <button type="button" class="btn-ghost stagger" style="--i: 5" @click="go('forgot')">
                Solicitar un enlace nuevo
              </button>
            </form>
          </section>
        </transition>
      </div>

      <p class="mt-10 text-xs text-gray-400 dark:text-gray-500">
        © {{ year }} Creatifoto · Foto y video
      </p>
    </main>

    <div class="curtain" :class="{ 'curtain-on': success }" aria-hidden="true"></div>
    <div class="arrival" :class="{ 'arrival-off': !arriving }" aria-hidden="true"></div>
  </div>
</template>

<script>
import axios from 'axios';
import BrandPanel from './BrandPanel.vue';
import AuthField from './AuthField.vue';

function consumeLogoutFlag() {
  try {
    if (sessionStorage.getItem('logoutFade')) {
      sessionStorage.removeItem('logoutFade');
      return true;
    }
  } catch (e) {}
  return false;
}

export default {
  name: 'AuthApp',
  components: { BrandPanel, AuthField },
  props: {
    initialView: { type: String, default: 'login' },
    resetToken: { type: String, default: '' },
    resetEmail: { type: String, default: '' },
  },
  data() {
    return {
      view: this.initialView === 'reset' && this.resetToken ? 'reset' : 'login',
      dark: document.documentElement.classList.contains('dark'),
      year: new Date().getFullYear(),

      login: { email: '', password: '', remember: false },
      forgot: { email: '' },
      reset: { email: this.resetEmail, password: '', password_confirmation: '' },

      errors: {},
      formError: '',
      notice: '',
      sent: '',

      loading: false,
      success: false,
      shake: false,
      flash: false,
      arriving: consumeLogoutFlag(),
      apertureMode: 'idle',
    };
  },
  mounted() {
    if (this.arriving) {
      window.setTimeout(() => (this.arriving = false), 150);
    }
  },
  methods: {
    go(view) {
      this.errors = {};
      this.formError = '';
      this.sent = '';
      if (view !== 'login') this.notice = '';
      this.view = view;
    },

    async submitLogin() {
      if (this.loading || this.success) return;
      this.clearErrors();

      if (!this.login.email.trim() || !this.login.password) {
        if (!this.login.email.trim()) this.errors = { ...this.errors, email: 'Escribe tu correo electrónico.' };
        if (!this.login.password) this.errors = { ...this.errors, password: 'Escribe tu contraseña.' };
        return this.fail();
      }

      this.loading = true;
      this.apertureMode = 'focus';
      this.notice = '';

      try {
        const minWait = new Promise((r) => window.setTimeout(r, 900));
        const [{ data }] = await Promise.all([
          axios.post('/login', {
            email: this.login.email.trim(),
            password: this.login.password,
            remember: this.login.remember,
          }),
          minWait,
        ]);

        this.success = true;
        this.apertureMode = 'open';
        window.setTimeout(() => (this.flash = true), 250);
        window.setTimeout(() => {
          window.location.href = (data && data.redirect) || '/';
        }, 1250);
      } catch (e) {
        this.handleError(e);
      } finally {
        this.loading = false;
      }
    },

    async submitForgot() {
      if (this.loading) return;
      this.clearErrors();

      if (!this.forgot.email.trim()) {
        this.errors = { email: 'Escribe tu correo electrónico.' };
        return this.fail();
      }

      this.loading = true;
      try {
        const { data } = await axios.post('/forgot-password', { email: this.forgot.email.trim() });
        this.sent = data.message;
      } catch (e) {
        this.handleError(e);
      } finally {
        this.loading = false;
      }
    },

    async submitReset() {
      if (this.loading) return;
      this.clearErrors();

      this.loading = true;
      this.apertureMode = 'focus';
      try {
        const { data } = await axios.post('/reset-password', {
          token: this.resetToken,
          email: this.reset.email,
          password: this.reset.password,
          password_confirmation: this.reset.password_confirmation,
        });

        this.apertureMode = 'idle';
        this.login.email = this.reset.email;
        this.login.password = '';
        this.reset.password = '';
        this.reset.password_confirmation = '';
        window.history.replaceState({}, '', '/login');
        this.go('login');
        this.notice = data.message;
      } catch (e) {
        this.handleError(e);
      } finally {
        this.loading = false;
      }
    },

    clearErrors() {
      this.errors = {};
      this.formError = '';
    },

    handleError(e) {
      const res = e.response;

      if (res && res.status === 422) {
        const raw = (res.data && res.data.errors) || {};
        const flat = {};
        Object.keys(raw).forEach((k) => (flat[k] = raw[k][0]));
        this.errors = flat;
      } else if (res && res.status === 419) {
        this.formError = 'La página caducó. La vamos a recargar para que puedas continuar.';
        window.setTimeout(() => window.location.reload(), 1600);
      } else if (res && res.status === 429) {
        this.formError = (res.data && res.data.message) || 'Demasiados intentos. Espera un momento.';
      } else {
        this.formError = 'No pudimos completar la solicitud. Revisa tu conexión e inténtalo de nuevo.';
      }

      this.fail();
    },

    fail() {
      this.apertureMode = 'blink';
      this.shake = false;
      this.$nextTick(() => {
        this.shake = true;
        window.setTimeout(() => (this.shake = false), 500);
      });
      window.setTimeout(() => {
        if (this.apertureMode === 'blink') this.apertureMode = 'idle';
      }, 700);
    },

    toggleTheme() {
      this.dark = !this.dark;
      document.documentElement.classList.toggle('dark', this.dark);
      try {
        localStorage.setItem('darkMode', JSON.stringify(this.dark));
      } catch (e) {}
    },
  },
};
</script>

<style scoped>
.stagger {
  animation: rise 0.55s cubic-bezier(0.2, 0.7, 0.2, 1) both;
  animation-delay: calc(var(--i, 0) * 70ms + 60ms);
}
@keyframes rise {
  from {
    opacity: 0;
    transform: translateY(14px);
  }
  to {
    opacity: 1;
    transform: none;
  }
}

.view-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}
.view-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.shake {
  animation: shake 0.45s cubic-bezier(0.36, 0.07, 0.19, 0.97);
}
@keyframes shake {
  10%, 90% { transform: translateX(-2px); }
  20%, 80% { transform: translateX(4px); }
  30%, 50%, 70% { transform: translateX(-7px); }
  40%, 60% { transform: translateX(7px); }
}

.alert {
  display: flex;
  gap: 0.5rem;
  border-radius: 0.75rem;
  border: 1px solid #fecaca;
  background: #fef2f2;
  padding: 0.75rem 0.875rem;
  font-size: 0.875rem;
  color: #b91c1c;
}
:global(.dark .alert) {
  border-color: rgba(239, 68, 68, 0.3);
  background: rgba(239, 68, 68, 0.1);
  color: #fca5a5;
}

.btn {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: center;
  border-radius: 0.75rem;
  background: #ffbe00;
  padding: 0.8rem 1rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: #1c1500;
  box-shadow: 0 8px 20px -8px rgba(255, 190, 0, 0.7);
  transition: transform 0.15s ease, box-shadow 0.2s ease, background-color 0.2s ease, opacity 0.2s ease;
}
.btn:hover:not(:disabled) {
  background: #ffc826;
  box-shadow: 0 12px 24px -8px rgba(255, 190, 0, 0.85);
  transform: translateY(-1px);
}
.btn:active:not(:disabled) {
  transform: translateY(0);
}
.btn:focus-visible {
  outline: 3px solid rgba(255, 190, 0, 0.5);
  outline-offset: 2px;
}
.btn:disabled {
  cursor: default;
  opacity: 0.8;
}

.btn-ghost {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  border-radius: 0.75rem;
  padding: 0.7rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  transition: background-color 0.2s ease, color 0.2s ease;
}
.btn-ghost:hover {
  background: rgba(156, 156, 160, 0.14);
  color: #374151;
}
:global(.dark .btn-ghost) {
  color: #9ca3af;
}
:global(.dark .btn-ghost:hover) {
  color: #e5e7eb;
}

.link {
  font-weight: 500;
  color: #b88300;
  transition: color 0.2s ease;
}
.link:hover {
  color: #8a6200;
  text-decoration: underline;
  text-underline-offset: 3px;
}
:global(.dark .link) {
  color: #ffbe00;
}
:global(.dark .link:hover) {
  color: #ffd45c;
}

.spinner {
  width: 1rem;
  height: 1rem;
  border-radius: 9999px;
  border: 2px solid rgba(28, 21, 0, 0.25);
  border-top-color: #1c1500;
  animation: spin 0.7s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

.curtain {
  position: fixed;
  inset: 0;
  z-index: 50;
  pointer-events: none;
  background: #f9fafb;
  opacity: 0;
  transition: opacity 0.45s ease 0.8s;
}
:global(.dark .curtain) {
  background: #111827;
}
.curtain-on {
  opacity: 1;
}

.arrival {
  position: fixed;
  inset: 0;
  z-index: 60;
  pointer-events: none;
  background: #ffffff;
  opacity: 1;
  transition: opacity 0.9s ease;
}
:global(.dark .arrival) {
  background: #0b0d12;
}
.arrival-off {
  opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
  .stagger,
  .shake,
  .spinner {
    animation-duration: 0.01ms;
    animation-iteration-count: 1;
  }
  .curtain {
    transition-delay: 0s;
  }
  .arrival {
    transition-duration: 0.2s;
  }
}
</style>