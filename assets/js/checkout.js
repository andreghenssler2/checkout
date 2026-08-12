(() => {
  const radios = [...document.querySelectorAll('input[name="valor_escolhido"]')];
  const free = document.getElementById('valorLivre');
  const total = document.getElementById('summaryTotal');
  const methods = [...document.querySelectorAll('input[name="formaPagamento"]')];
  const cardFields = document.getElementById('cardFields');
  const pixMessage = document.getElementById('pixMessage');
  const boletoMessage = document.getElementById('boletoMessage');

  const money = v =>
    new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(v || 0);

  function updateAmount() {
    let value = 0;

    if (free && free.value.trim() !== '') {
      value =
        parseFloat(
          free.value
            .replace(/\./g, '')
            .replace(',', '.')
        ) || 0;
    } else {
      const selected = radios.find(item => item.checked);
      value = selected
        ? parseFloat(selected.value)
        : window.offerMin;
    }

    if (total) {
      total.textContent = money(value);
    }
  }

  radios.forEach(radio => {
    radio.addEventListener('change', () => {
      if (free) {
        free.value = '';
      }

      updateAmount();
    });
  });

  if (free) {
    free.addEventListener('input', () => {
      if (free.value.trim() !== '') {
        radios.forEach(radio => {
          radio.checked = false;
        });
      }

      updateAmount();
    });
  }

  function updatePaymentMethod() {
    const selected = methods.find(item => item.checked);
    const isCard = selected?.value === 'Cartao';
    const isPix = selected?.value === 'PIX';
    const isBoleto = selected?.value === 'Boleto';

    cardFields?.classList.toggle('hidden', !isCard);
    pixMessage?.classList.toggle('hidden', !isPix);
    boletoMessage?.classList.toggle('hidden', !isBoleto);

    cardFields
      ?.querySelectorAll('input,select')
      .forEach(field => {
        field.required =
          isCard
          && field.name !== 'holder_complemento';
      });
  }

  methods.forEach(method => {
    method.addEventListener(
      'change',
      updatePaymentMethod
    );
  });

  updatePaymentMethod();
  updateAmount();
})();
