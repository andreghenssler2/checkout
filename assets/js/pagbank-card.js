(() => {
  'use strict';

  if (window.checkoutCardProvider !== 'PagBank') {
    return;
  }

  const form = document.querySelector('form.checkout-main');

  if (!form) {
    return;
  }

  const encryptedField =
    form.querySelector('#pagbankEncryptedCard');

  const submitButton =
    form.querySelector('.paybtn');

  let sending = false;

  const field = name =>
    form.querySelector(`[name="${name}"]`);

  function cardSelected() {
    return form.querySelector(
      'input[name="formaPagamento"]:checked'
    )?.value === 'Cartao';
  }

  function message(text) {
    let box = form.querySelector(
      '.pagbank-card-encryption-error'
    );

    if (!box) {
      box = document.createElement('div');
      box.className =
        'alert error pagbank-card-encryption-error';

      const cardFields =
        form.querySelector('#cardFields');

      cardFields?.prepend(box);
    }

    box.textContent = text;
    box.scrollIntoView({
      behavior: 'smooth',
      block: 'center'
    });
  }

  function clearMessage() {
    form.querySelector(
      '.pagbank-card-encryption-error'
    )?.remove();
  }

  function encryptCard() {
    const publicKey =
      String(
        window.checkoutPagBankPublicKey || ''
      ).trim();

    if (!publicKey) {
      throw new Error(
        'A chave pública do PagBank não está disponível para este ambiente. Acesse Admin > PagBank e prepare a chave pública.'
      );
    }

    if (
      !window.PagSeguro
      || typeof window.PagSeguro.encryptCard !== 'function'
    ) {
      throw new Error(
        'O SDK de segurança do PagBank não pôde ser carregado. Atualize a página e tente novamente.'
      );
    }

    const result = window.PagSeguro.encryptCard({
      publicKey,
      holder: field('card_holder')?.value || '',
      number: (
        field('card_number')?.value || ''
      ).replace(/\D+/g, ''),
      expMonth:
        field('card_month')?.value || '',
      expYear:
        field('card_year')?.value || '',
      securityCode: (
        field('card_ccv')?.value || ''
      ).replace(/\D+/g, '')
    });

    if (result?.hasErrors) {
      const details = (
        result.errors || []
      )
        .map(item =>
          item?.message
          || item?.code
          || ''
        )
        .filter(Boolean)
        .join(' ');

      throw new Error(
        details
        || 'Não foi possível validar e criptografar o cartão.'
      );
    }

    const encrypted = String(
      result?.encryptedCard || ''
    ).trim();

    if (!encrypted) {
      throw new Error(
        'O PagBank não retornou o cartão criptografado.'
      );
    }

    return encrypted;
  }

  function disableSensitiveFields() {
    [
      'card_number',
      'card_month',
      'card_year',
      'card_ccv'
    ].forEach(name => {
      const input = field(name);

      if (input) {
        input.disabled = true;
      }
    });
  }

  form.addEventListener(
    'submit',
    event => {
      if (
        sending
        || !cardSelected()
      ) {
        return;
      }

      event.preventDefault();
      clearMessage();

      try {
        if (encryptedField) {
          encryptedField.value = '';
        }

        if (!encryptedField) {
          throw new Error(
            'Campo seguro do cartão PagBank não encontrado.'
          );
        }

        encryptedField.value =
          encryptCard();

        /*
         * Depois da criptografia, número, validade e CVV são
         * desabilitados antes do POST e não são enviados ao PHP.
         */
        disableSensitiveFields();

        sending = true;

        if (submitButton) {
          submitButton.disabled = true;
          submitButton.dataset.originalText =
            submitButton.textContent;

          submitButton.textContent =
            'Processando cartão...';
        }

        HTMLFormElement.prototype.submit.call(
          form
        );
      } catch (error) {
        message(
          error?.message
          || 'Não foi possível processar o cartão PagBank.'
        );
      }
    }
  );
})();
