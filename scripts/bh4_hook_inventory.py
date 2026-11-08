#!/usr/bin/env python3
"""Static hook/cron/action inventory. Source text only; never executes PHP."""
from pathlib import Path
import json
import re

root = Path(__file__).resolve().parents[1]
php = root / "plugin" / "bubba-hub-manager"
patterns = {
    "activation_hooks": r"\bregister_activation_hook\s*\(",
    "deactivation_hooks": r"\bregister_deactivation_hook\s*\(",
    "uninstall_hooks": r"\bregister_uninstall_hook\s*\(",
    "wp_schedule_event": r"\bwp_schedule_(?:event|single_event)\s*\(",
    "wp_clear_scheduled_hook": r"\bwp_clear_scheduled_hook\s*\(",
    "action_scheduler": r"\bas_(?:schedule|enqueue|unschedule|next_scheduled|has_scheduled)[A-Za-z_]*\s*\(",
    "cron_hook_registration": r"\badd_action\s*\(\s*['\"](?:[^'\"]*cron|[^'\"]*scheduled)[^'\"]*['\"]",
    "booking_hook_registration": r"\badd_(?:action|filter)\s*\(\s*['\"][^'\"]*(?:book|checkout|stripe|payment|refund)[^'\"]*['\"]",
    "booking_shortcodes": r"\badd_shortcode\s*\(\s*['\"][^'\"]*(?:book|checkout|payment)[^'\"]*['\"]",
}
report = {"source_only": True, "wordpress_executed": False, "categories": {}}
for category, pattern in patterns.items():
    matches = []
    for path in sorted(php.rglob("*.php")):
        source = path.read_text(encoding="utf-8")
        for match in re.finditer(pattern, source, flags=re.I):
            line = source.count("\n", 0, match.start()) + 1
            matches.append({"file": str(path.relative_to(root)), "line": line, "snippet": source[match.start():source.find("\n", match.start()) if "\n" in source[match.start():] else len(source)][:180].strip()})
    report["categories"][category] = matches
report["total_matches"] = sum(map(len, report["categories"].values()))
print(json.dumps(report, indent=2, sort_keys=True))
