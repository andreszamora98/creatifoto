<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Cotizaciones</h2>
      <button @click="showQuoteModal = true" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm flex items-center gap-2 transition-colors">
        <i class="ti ti-plus text-base"></i>
        <span>Nueva cotización</span>
      </button>
    </div>

    <div v-if="loading" class="text-center py-8">
      <i class="ti ti-refresh animate-spin text-xl text-blue-500"></i>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Cargando cotizaciones...</p>
    </div>

    <div v-else-if="quotes.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
      No hay cotizaciones registradas.
    </div>

    <table v-else class="w-full text-left text-sm bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
      <thead>
        <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400">
          <th class="py-3 px-4">Folio</th>
          <th class="px-4">Cliente</th>
          <th class="px-4">Total</th>
          <th class="px-4">Vigencia</th>
          <th class="px-4">Estado</th>
          <th class="px-4"></th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="q in quotes"
          :key="q.id"
          :id="'quote-card-' + q.id"
          class="border-b border-gray-100 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
          :class="{ 'animate-blink-row': isBlinking(q.id) }"
          @click="openDetail(q)"
        >
          <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">C-{{ String(q.id).padStart(3, '0') }}</td>
          <td class="px-4 text-gray-600 dark:text-gray-400">{{ q.customer?.name || 'Sin cliente' }}</td>
          <td class="px-4 text-gray-600 dark:text-gray-400">{{ formatCurrency(q.total) }}</td>
          <td class="px-4 text-gray-600 dark:text-gray-400">{{ formatDate(q.valid_until) }}</td>
          <td class="px-4">
            <span class="px-2 py-0.5 text-xs rounded-full" :class="statusClass(q.status)">
              {{ statusLabel(q.status) }}
            </span>
          </td>
          <td class="px-4 text-gray-400"><i class="ti ti-chevron-right"></i></td>
        </tr>
      </tbody>
    </table>

    <QuoteModal
      :show="showQuoteModal"
      @close="handleQuoteModalClosed"
      @created="handleQuoteCreated"
      @notify="handleNotify"
    />

    <QuoteDetailModal
      :show="showDetailModal"
      :quote="selectedQuote"
      @close="showDetailModal = false"
      @updated="handleQuoteUpdated"
      @convert="handleConvertRequest"
      @notify="handleNotify"
    />

    <ConvertQuoteModal
      :show="showConvertModal"
      :quote="selectedQuote"
      @close="handleConvertCancelled"
      @converted="handleQuoteConverted"
      @notify="handleNotify"
    />

    <transition name="fade-slide">
      <div v-if="showNotification" class="fixed top-3 right-3 z-50 max-w-xs w-full">
        <div class="flex items-center p-3 rounded-lg shadow-lg"
             :class="{
               'bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800': notificationType === 'success',
               'bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800': notificationType === 'error',
               'bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800': notificationType === 'info'
             }">
          <div class="flex-1 min-w-0">
            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-0.5 text-xs">{{ notificationTitle }}</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400">{{ notificationMessage }}</p>
          </div>
          <button @click="showNotification = false" class="ml-2 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <i class="ti ti-x text-sm"></i>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from 'axios';
import { setPageTitle } from '../../store';
import QuoteModal from './QuoteModal.vue';
import QuoteDetailModal from './QuoteDetailModal.vue';
import ConvertQuoteModal from './ConvertQuoteModal.vue';

export default {
  name: 'QuotesBoard',
  components: { QuoteModal, QuoteDetailModal, ConvertQuoteModal  },
  data() {
    return {
      quotes: [],
      loading: false,
      showQuoteModal: false,
      showDetailModal: false,
      selectedQuote: null,
      showNotification: false,
      notificationTitle: '',
      notificationMessage: '',
      notificationType: 'info',
      notificationTimeout: null,
      newQuoteId: null,
      blinkInterval: null,
      pendingBlinkQuoteId: null,
      showConvertModal: false
    };
  },
  async mounted() {
    setPageTitle('Cotizaciones');
    await this.loadQuotes();

    const pendingNotif = sessionStorage.getItem('pendingQuoteNotification');
    if (pendingNotif) {
      sessionStorage.removeItem('pendingQuoteNotification');
      try {
        const notif = JSON.parse(pendingNotif);
        this.handleNotify(notif);
      } catch (e) { /* ignorar JSON inválido */ }
    }

    const pendingId = sessionStorage.getItem('pendingBlinkQuoteId');
    if (pendingId) {
      sessionStorage.removeItem('pendingBlinkQuoteId');
      const id = Number(pendingId);
      this.$nextTick(() => {
        this.startBlink(id);
        setTimeout(() => {
          const el = document.getElementById('quote-card-' + id);
          if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 50);
      });
    }
  },
  beforeUnmount() {
    if (this.blinkInterval) {
      clearInterval(this.blinkInterval);
      this.blinkInterval = null;
    }
  },
  methods: {
    handleConvertRequest(quote) {
      this.showDetailModal = false;
      this.selectedQuote = quote;
      this.showConvertModal = true;
    },
    // El usuario confirmó que cancela la conversión: se cierra y se regresa al detalle de la cotización
    handleConvertCancelled() {
      this.showConvertModal = false;
      this.showDetailModal = true;
    },
    async handleQuoteConverted(order) {
      this.showConvertModal = false;
      await this.loadQuotes();
      this.handleNotify({
        title: 'Orden creada',
        message: `Se generó la orden ORD-${String(order.id).padStart(3, '0')} a partir de la cotización.`,
        type: 'success'
      });
    },
    async handleQuoteUpdated() {
      await this.loadQuotes();
      // Se refresca la cotización abierta para que el detalle muestre los datos recién guardados
      if (this.selectedQuote) {
        const fresh = this.quotes.find(q => q.id === this.selectedQuote.id);
        if (fresh) this.selectedQuote = fresh;
      }
    },
    async loadQuotes() {
      this.loading = true;
      try {
        const res = await axios.get('/quotes');
        this.quotes = res.data;
      } catch (error) {
        this.handleNotify({ title: 'Error', message: 'No se pudieron cargar las cotizaciones', type: 'error' });
      } finally {
        this.loading = false;
      }
    },
    openDetail(q) {
      this.selectedQuote = q;
      this.showDetailModal = true;
    },
    async handleQuoteCreated(quote) {
      if (quote && quote.id) {
        this.pendingBlinkQuoteId = quote.id;
      }
      await this.loadQuotes();
      this.handleNotify({
        title: 'Cotización creada',
        message: `La cotización C-${String(quote.id).padStart(3, '0')} se registró correctamente.`,
        type: 'success'
      });
    },
    handleQuoteModalClosed() {
      this.showQuoteModal = false;
      if (this.pendingBlinkQuoteId) {
        const idToBlink = this.pendingBlinkQuoteId;
        this.pendingBlinkQuoteId = null;
        this.$nextTick(() => {
          this.startBlink(idToBlink);
          setTimeout(() => {
            const el = document.getElementById('quote-card-' + idToBlink);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }, 50);
        });
      }
    },
    handleNotify({ title, message, type }) {
      this.notificationTitle = title;
      this.notificationMessage = message;
      this.notificationType = type;
      this.showNotification = true;
      if (this.notificationTimeout) clearTimeout(this.notificationTimeout);
      this.notificationTimeout = setTimeout(() => { this.showNotification = false; }, 3000);
    },
    startBlink(quoteId) {
      if (this.blinkInterval) {
        clearInterval(this.blinkInterval);
        this.blinkInterval = null;
      }
      this.newQuoteId = quoteId;
      let count = 0;
      const maxCount = 8;
      this.blinkInterval = setInterval(() => {
        count++;
        if (count >= maxCount) {
          clearInterval(this.blinkInterval);
          this.blinkInterval = null;
          this.newQuoteId = null;
        }
      }, 400);
    },
    isBlinking(quoteId) {
      return this.newQuoteId === quoteId && this.blinkInterval !== null;
    },
    statusLabel(status) {
      const labels = { pending: 'Pendiente', accepted: 'Aceptada', rejected: 'Rechazada', expired: 'Vencida', converted: 'Convertida' };
      return labels[status] || status;
    },
    statusClass(status) {
      const colors = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        accepted: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        expired: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        converted: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
      };
      return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    },
    formatCurrency(value) {
      const num = Number(value || 0);
      return '$' + num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    formatDate(dateString) {
      if (!dateString) return '—';
      const datePart = String(dateString).substring(0, 10);
      const [y, m, d] = datePart.split('-');
      if (!y || !m || !d) return dateString;
      return `${d}/${m}/${y}`;
    }
  }
};
</script>

<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.3s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateX(20px); }

.animate-blink-row {
  animation: blinkRow 0.8s ease-in-out 4;
}
.dark .animate-blink-row {
  animation: blinkRowDark 0.8s ease-in-out 4;
}

@keyframes blinkRow {
  0%, 100% { background-color: transparent; }
  50% { background-color: #FFF9C4; }
}
@keyframes blinkRowDark {
  0%, 100% { background-color: transparent; }
  50% { background-color: #FFD700; }
}
</style>