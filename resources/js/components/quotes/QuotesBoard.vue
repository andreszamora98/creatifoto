<template>
  <div class="h-full flex flex-col overflow-hidden">
    <div class="flex-shrink-0 mb-4 mt-2 ml-4 flex flex-col md:flex-row items-start md:items-center gap-3">
      <div class="flex-1 max-w-xl w-full md:w-auto">
        <div class="relative">
          <i class="ti ti-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
          <input v-model="searchQuery" type="text"
                placeholder="Buscar por folio, cliente, teléfono, correo, estado..."
                class="w-full pl-9 pr-8 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
      </div>

      <div class="flex items-center gap-2 flex-wrap">
        <button @click="loadQuotes" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2.5 rounded-lg text-sm flex items-center gap-2 transition-colors">
          <i class="ti ti-refresh text-base" :class="{ 'animate-spin': loading }"></i>
          Actualizar
        </button>
        <button @click="showQuoteModal = true" class="bg-blue-500 dark:bg-blue-600 hover:bg-blue-600 dark:hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm flex items-center gap-2 transition-colors">
          <i class="ti ti-plus text-base"></i>
          <span>Nueva cotización</span>
        </button>
      </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden min-h-0">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow flex-1 flex flex-col overflow-hidden min-h-0">

        <div class="flex-shrink-0 p-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3 flex-wrap">
          <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <span>{{ filteredQuotes.length }} {{ filteredQuotes.length === 1 ? 'cotización' : 'cotizaciones' }}</span>
            <span v-if="searchQuery" class="text-gray-400 ml-2">(filtrado)</span>
          </div>
        </div>

        <div class="flex-1 overflow-hidden min-h-0 relative">
          <div v-if="loading" class="absolute inset-0 flex items-center justify-center p-4">
            <div class="text-center">
              <i class="ti ti-refresh animate-spin text-xl text-blue-500 mb-2"></i>
              <p class="text-gray-600 dark:text-gray-400 text-sm">Cargando cotizaciones...</p>
            </div>
          </div>

          <div v-else-if="filteredQuotes.length === 0" class="absolute inset-0 flex items-center justify-center p-4">
            <div class="text-center max-w-xs">
              <div class="w-12 h-12 mx-auto mb-3 rounded-full flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                <i class="ti ti-file-off text-lg text-gray-400 dark:text-gray-500"></i>
              </div>
              <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                {{ searchQuery ? 'No se encontraron resultados' : 'No hay cotizaciones' }}
              </h3>
              <p class="text-gray-500 dark:text-gray-400 text-xs">
                {{ searchQuery ? 'Intenta con otros términos de búsqueda.' : 'Aún no hay cotizaciones registradas.' }}
              </p>
            </div>
          </div>

          <div v-else class="h-full overflow-y-auto px-3 pb-3">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
              <thead class="sticky top-0 z-20 bg-gray-50 dark:bg-gray-800">
                <tr>
                  <th class="px-3 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Folio</th>
                  <th class="px-3 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Cliente</th>
                  <th class="px-3 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Teléfono</th>
                  <th class="px-3 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Correo</th>
                  <th class="px-3 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Total</th>
                  <th class="px-3 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Vigencia</th>
                  <th class="px-3 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Estado</th>
                  <th class="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr
                  v-for="q in paginatedQuotes"
                  :key="q.id"
                  :id="'quote-card-' + q.id"
                  @click="openDetail(q)"
                  class="cursor-pointer transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                  :class="{ 'animate-blink-row': isBlinking(q.id) }"
                >
                  <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">C-{{ String(q.id).padStart(3, '0') }}</td>
                  <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ q.customer?.name || 'Sin cliente' }}</td>
                  <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ q.customer?.phone || '—' }}</td>
                  <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ q.customer?.email || '—' }}</td>
                  <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ formatCurrency(q.total) }}</td>
                  <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ formatDate(q.valid_until) }}</td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <span class="px-2 py-0.5 text-xs rounded-full" :class="statusClass(q.status)">
                      {{ statusLabel(q.status) }}
                    </span>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap text-gray-400">
                    <i class="ti ti-chevron-right"></i>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="flex-shrink-0 mt-3 flex items-center justify-center gap-2 pb-3 border-t border-gray-200 dark:border-gray-700 pt-3">
          <button
            @click="prevPage"
            :disabled="currentPage === 1 || totalPages === 0"
            class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
            :class="currentPage === 1 || totalPages === 0
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
          >
            <i class="ti ti-chevron-left text-sm"></i> Anterior
          </button>

          <div class="flex items-center gap-1">
            <button
              v-for="page in totalPages"
              :key="page"
              @click="goToPage(page)"
              class="w-8 h-8 rounded-md text-xs font-medium transition-colors"
              :class="currentPage === page
                ? 'bg-blue-500 text-white dark:bg-blue-600'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
            >
              {{ page }}
            </button>
            <span v-if="totalPages === 0" class="w-8 h-8 rounded-md bg-gray-100 text-gray-400 flex items-center justify-center text-xs dark:bg-gray-800 dark:text-gray-600">1</span>
          </div>

          <button
            @click="nextPage"
            :disabled="currentPage === totalPages || totalPages === 0"
            class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
            :class="currentPage === totalPages || totalPages === 0
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
          >
            Siguiente <i class="ti ti-chevron-right text-sm"></i>
          </button>
        </div>

        <div class="flex-shrink-0 flex items-center justify-center gap-4 text-xs text-gray-500 dark:text-gray-400 pb-2">
          <span>Mostrando {{ paginatedQuotes.length }}/{{ filteredQuotes.length }} {{ filteredQuotes.length === 1 ? 'cotización' : 'cotizaciones' }}</span>
          <span v-if="totalPages > 0">Página {{ currentPage }} de {{ totalPages }}</span>
          <span v-else>Página —</span>
        </div>
      </div>
    </div>

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
  components: { QuoteModal, QuoteDetailModal, ConvertQuoteModal },
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
      showConvertModal: false,

      searchQuery: '',
      currentPage: 1,
      itemsPerPage: 15
    };
  },
  computed: {
    filteredQuotes() {
      const q = this.searchQuery.toLowerCase().trim();
      if (!q) return this.quotes;

      return this.quotes.filter(quote => {
        const folio = `c-${String(quote.id).padStart(3, '0')}`;
        const customerName = (quote.customer?.name || '').toLowerCase();
        const customerPhone = (quote.customer?.phone || '').toLowerCase();
        const customerEmail = (quote.customer?.email || '').toLowerCase();
        const total = String(quote.total ?? '');
        const statusLabel = this.statusLabel(quote.status).toLowerCase();
        const status = (quote.status || '').toLowerCase();
        const validUntil = (quote.valid_until || '').toLowerCase();
        const validUntilFormatted = this.formatDate(quote.valid_until).toLowerCase();

        return (
          folio.includes(q) ||
          customerName.includes(q) ||
          customerPhone.includes(q) ||
          customerEmail.includes(q) ||
          total.includes(q) ||
          statusLabel.includes(q) ||
          status.includes(q) ||
          validUntil.includes(q) ||
          validUntilFormatted.includes(q)
        );
      });
    },
    paginatedQuotes() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredQuotes.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredQuotes.length / this.itemsPerPage);
    }
  },
  watch: {
    filteredQuotes() {
      if (this.currentPage > this.totalPages && this.totalPages > 0) {
        this.currentPage = this.totalPages;
      }
    },
    searchQuery() {
      this.currentPage = 1;
    }
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
    goToPage(page) {
      if (this.totalPages > 0 && page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
    nextPage() {
      if (this.totalPages > 0 && this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
    prevPage() {
      if (this.totalPages > 0 && this.currentPage > 1) {
        this.currentPage--;
      }
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