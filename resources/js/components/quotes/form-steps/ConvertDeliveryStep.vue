    <template>
  <section class="space-y-6">
    <p class="text-lg text-gray-700 dark:text-gray-300">
      Define la fecha y hora en que se entregará el pedido.
    </p>
    <DeliveryDateTimeFields
      :delivery-date="deliveryDate"
      :delivery-time="deliveryTime"
      :show-errors="showErrors"
      @update:deliveryDate="$emit('update:deliveryDate', $event)"
      @update:deliveryTime="$emit('update:deliveryTime', $event)"
    />
  </section>
</template>

<script>
import DeliveryDateTimeFields from '../../order-quote-shared/DeliveryDateTimeFields.vue';

export default {
  name: 'ConvertDeliveryStep',
  components: { DeliveryDateTimeFields },
  props: {
    deliveryDate: { type: String, default: '' },
    deliveryTime: { type: String, default: '' },
    showErrors: { type: Boolean, default: false }
  },
  emits: ['update:deliveryDate', 'update:deliveryTime']
};
</script>