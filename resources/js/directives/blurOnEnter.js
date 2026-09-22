export default {
  mounted(el) {
    const target = el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT'
      ? el
      : el.querySelector('input, textarea, select');
    if (!target) return;

    const handler = (event) => {
      // Shift+Enter en un textarea sigue insertando salto de línea normal;
      // solo Enter "solo" cierra el teclado.
      if (event.key !== 'Enter' || event.shiftKey) return;
      event.preventDefault();
      target.blur();
    };

    el._blurOnEnterHandler = handler;
    target.addEventListener('keydown', handler);
  },
  beforeUnmount(el) {
    const target = el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT'
      ? el
      : el.querySelector('input, textarea, select');
    if (target && el._blurOnEnterHandler) {
      target.removeEventListener('keydown', el._blurOnEnterHandler);
    }
  }
};