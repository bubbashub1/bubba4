#!/usr/bin/env python3
"""Static BH4 migration audit. Read-only, no WordPress runtime required."""
from collections import defaultdict
from pathlib import Path
import json
import re
import sys

ROOT = Path(__file__).resolve().parents[1]
PHP_ROOT = ROOT / "plugin" / "bubba-hub-manager"
FILES = sorted(PHP_ROOT.rglob("*.php"))
ENTRYPOINTS = [ROOT / "bubbawp.php", PHP_ROOT / "bubba-hub-manager.php"]
CLASS_RE = re.compile(r"^[ \t]*(?:(?:final|abstract|readonly)[ \t]+)?(?:class|interface|trait)[ \t]+([A-Za-z_][A-Za-z0-9_]*)\b", re.M)
SHORTCODE_RE = re.compile(r"\badd_shortcode\s*\(\s*['\"]([^'\"]+)")
HOOK_RE = re.compile(r"\bregister_(?:activation|deactivation|uninstall)_hook\s*\(")
DDL_RE = re.compile(r"\b(?:dbDelta|CREATE\s+TABLE|ALTER\s+TABLE|DROP\s+TABLE)\b", re.I)
CONST_RE = re.compile(r"\bdefine\s*\(\s*['\"]([^'\"]+)")
REQUIRE_RE = re.compile(r"\b(?:require|include)(?:_once)?\b")
records = defaultdict(list)
shortcodes = defaultdict(list)
constants = defaultdict(list)
activation = []
ddl = []
includes = []

for path in [ROOT / "bubbawp.php", *FILES]:
    if not path.is_file():
        continue
    source = path.read_text(encoding="utf-8")
    rel = str(path.relative_to(ROOT))
    for name in CLASS_RE.findall(source):
        records[name].append(rel)
    for name in SHORTCODE_RE.findall(source):
        shortcodes[name].append(rel)
    for name in CONST_RE.findall(source):
        constants[name].append(rel)
    if HOOK_RE.search(source):
        activation.append(rel)
    if DDL_RE.search(source):
        ddl.append(rel)
    if REQUIRE_RE.search(source):
        includes.append(rel)

report = {
    "source_files": len(FILES) + 1,
    "entrypoints": [str(p.relative_to(ROOT)) for p in ENTRYPOINTS if p.exists()],
    "class_definitions": len(records),
    "duplicate_class_definitions": {k: v for k, v in records.items() if len(v) > 1},
    "shortcodes": dict(sorted(shortcodes.items())),
    "duplicate_shortcode_registrations": {k: v for k, v in shortcodes.items() if len(v) > 1},
    "constants": dict(sorted(constants.items())),
    "activation_hook_files": activation,
    "potential_schema_change_files": ddl,
    "include_statement_files": includes,
    "note": "Static pattern matches only; manual review and runtime tests required. No files changed.",
}
print(json.dumps(report, indent=2, sort_keys=True))
if "--strict" in sys.argv and report["duplicate_class_definitions"]:
    sys.exit(1)
