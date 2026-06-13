#!/usr/bin/env python3
"""Composer-less dependency resolver: clones packages from GitHub at tags
satisfying constraints, recursively. Resumable / idempotent."""
import json, os, re, subprocess, sys, functools

ROOT = "/sessions/blissful-beautiful-mayer/tmp/ranga"
VENDOR = os.path.join(ROOT, "vendor")
STATE = os.path.join(ROOT, ".dep-state.json")

TOP = {
    "laravel/framework": "^12.0",
    "laravel/sanctum": "^4.0",
    "laravel/socialite": "^5.0",
    "livewire/livewire": "^3.0",
    "spatie/laravel-permission": "^6.0",
    "pragmarx/google2fa": "^8.0",
    "guzzlehttp/guzzle": "^7.8",
}
TOP_DEV = {
    "phpunit/phpunit": "^11.5",
    "mockery/mockery": "^1.6",
    "fakerphp/faker": "^1.23",
}

REPO_MAP = {
    "nesbot/carbon": "CarbonPHP/carbon",
    "egulias/email-validator": "egulias/EmailValidator",
    "myclabs/deep-copy": "myclabs/DeepCopy",
    "nikic/php-parser": "nikic/PHP-Parser",
    "graham-campbell/result-type": "GrahamCampbell/Result-Type",
    "phpoption/phpoption": "schmittjoh/php-option",
    "tijsverkoyen/css-to-inline-styles": "tijsverkoyen/CssToInlineStyles",
    "webmozart/assert": "webmozarts/assert",
    "dflydev/dot-access-data": "dflydev/dflydev-dot-access-data",
    "guzzlehttp/guzzle": "guzzle/guzzle",
    "guzzlehttp/promises": "guzzle/promises",
    "guzzlehttp/psr7": "guzzle/psr7",
    "guzzlehttp/uri-template": "guzzle/uri-template",
    "monolog/monolog": "Seldaek/monolog",
    "fakerphp/faker": "FakerPHP/Faker",
    "pragmarx/google2fa": "antonioribeiro/google2fa",
    "hamcrest/hamcrest-php": "hamcrest/hamcrest-php",
    "carbonphp/carbon-doctrine-types": "CarbonPHP/carbon-doctrine-types",
}

def repo_url(pkg):
    if pkg in REPO_MAP:
        return "https://github.com/%s.git" % REPO_MAP[pkg]
    vendor, name = pkg.split("/", 1)
    org = {"league": "thephpleague", "psr": "php-fig",
           "sebastian": "sebastianbergmann", "phpunit": "sebastianbergmann"}.get(vendor, vendor)
    return "https://github.com/%s/%s.git" % (org, name)

VER_RE = re.compile(r"^v?(\d+)\.(\d+)(?:\.(\d+))?(?:\.(\d+))?$")

def parse_ver(s):
    m = VER_RE.match(s.strip())
    if not m:
        return None
    return tuple(int(x) if x else 0 for x in m.groups())

def cmp_key(v):
    return v

def sat_one(ver, op, target):
    t = parse_ver(target)
    if t is None:
        return True  # unparseable target -> permissive
    if op == "^":
        if t[0] > 0:
            lo, hi = t, (t[0] + 1, 0, 0, 0)
        elif t[1] > 0:
            lo, hi = t, (0, t[1] + 1, 0, 0)
        else:
            lo, hi = t, (0, 0, t[2] + 1, 0)
        return lo <= ver < hi
    if op == "~":
        parts = target.split(".")
        if len(parts) == 2:
            lo, hi = t, (t[0] + 1, 0, 0, 0)
        else:
            lo, hi = t, (t[0], t[1] + 1, 0, 0)
        return lo <= ver < hi
    if op == ">=": return ver >= t
    if op == ">": return ver > t
    if op == "<=": return ver <= t
    if op == "<": return ver < t
    if op == "==":
        if target.endswith(".*"):
            pre = tuple(int(x) for x in target[:-2].lstrip("v").split("."))
            return ver[:len(pre)] == pre
        return ver[:3] == t[:3]
    return True

def satisfies(ver, constraint):
    constraint = constraint.strip()
    if constraint in ("*", "", "@stable") or "dev" in constraint:
        return True
    for alt in re.split(r"\|\|?", constraint):  # OR groups
        ok = True
        alt = alt.strip()
        if not alt:
            continue
        # AND parts: split on comma or spaces (not inside operators)
        parts = re.split(r"[,\s]+", alt)
        for p in parts:
            p = p.strip()
            if not p or p == "*":
                continue
            if p.endswith(".*"):
                ok = ok and sat_one(ver, "==", p)
                continue
            m = re.match(r"^(\^|~|>=|<=|>|<|=)?\s*(.+)$", p)
            op = m.group(1) or "=="
            op = "^" if op == "^" else op
            if op == "=": op = "=="
            tgt = m.group(2)
            if parse_ver(tgt) is None and not tgt.endswith(".*"):
                continue
            ok = ok and sat_one(ver, op, tgt)
            if not ok:
                break
        if ok:
            return True
    return False

@functools.lru_cache(maxsize=None)
def remote_tags(url):
    out = subprocess.run(["git", "ls-remote", "--tags", url],
                         capture_output=True, text=True, timeout=60)
    if out.returncode != 0:
        raise RuntimeError("ls-remote failed for %s: %s" % (url, out.stderr[:200]))
    tags = []
    for line in out.stdout.splitlines():
        ref = line.split("\t")[-1]
        tag = ref.replace("refs/tags/", "").replace("^{}", "")
        v = parse_ver(tag)
        if v:
            tags.append((v, tag))
    return sorted(set(tags), reverse=True)

def load_state():
    if os.path.exists(STATE):
        return json.load(open(STATE))
    return {"resolved": {}, "constraints": {}, "dev": list(TOP_DEV.keys())}

def save_state(st):
    json.dump(st, open(STATE, "w"), indent=1)

def clone(pkg, tag):
    dest = os.path.join(VENDOR, pkg)
    if os.path.exists(os.path.join(dest, "composer.json")):
        return dest
    os.makedirs(os.path.dirname(dest), exist_ok=True)
    url = repo_url(pkg)
    r = subprocess.run(["git", "clone", "-q", "--depth", "1", "--branch", tag, url, dest],
                       capture_output=True, text=True, timeout=120)
    if r.returncode != 0:
        raise RuntimeError("clone failed %s@%s: %s" % (pkg, tag, r.stderr[:300]))
    return dest

SKIP = ("php", "php-64bit", "composer-runtime-api", "composer-plugin-api")

def main():
    os.makedirs(VENDOR, exist_ok=True)
    st = load_state()
    queue = []
    for pkg, c in list(TOP.items()) + list(TOP_DEV.items()):
        st["constraints"].setdefault(pkg, []).append(c) if c not in st["constraints"].get(pkg, []) else None
        if pkg not in st["resolved"]:
            queue.append(pkg)
    # also re-queue resolved-but-not-cloned, and re-extract deps of cloned pkgs
    for pkg in list(st["resolved"]):
        if str(st["resolved"][pkg]).startswith("replaced:"):
            continue
        cjp = os.path.join(VENDOR, pkg, "composer.json")
        if not os.path.exists(cjp):
            queue.append(pkg)
        else:
            cj = json.load(open(cjp))
            for dep, c in cj.get("require", {}).items():
                dep = dep.lower()
                if (dep in SKIP or dep.startswith("ext-") or dep.startswith("lib-")
                        or dep == "php" or dep.startswith("illuminate/")
                        or dep.endswith("-implementation")):
                    continue
                st["constraints"].setdefault(dep, [])
                if c not in st["constraints"][dep]:
                    st["constraints"][dep].append(c)
                if dep not in st["resolved"] and dep not in queue:
                    queue.append(dep)
    done_any = True
    while queue or done_any:
        done_any = False
        while queue:
            pkg = queue.pop(0)
            if pkg.startswith(SKIP[0] + "-") or pkg in SKIP or pkg.startswith("ext-") or pkg.startswith("lib-"):
                continue
            if pkg.startswith("illuminate/") or str(st["resolved"].get(pkg, "")).startswith("replaced:"):
                continue
            cons = st["constraints"].get(pkg, ["*"])
            if pkg in st["resolved"] and os.path.exists(os.path.join(VENDOR, pkg, "composer.json")):
                continue
            url = repo_url(pkg)
            tags = remote_tags(url)
            pick = None
            for v, tag in tags:
                # cap symfony components below 8.0 (symfony 8 needs PHP >= 8.4)
                if pkg.startswith("symfony/") and not pkg.startswith("symfony/polyfill") and v >= (8, 0, 0, 0):
                    continue
                if all(satisfies(v, c) for c in cons):
                    pick = (v, tag)
                    break
            if not pick:
                print("!! no tag satisfies %s %s" % (pkg, cons))
                continue
            dest = clone(pkg, pick[1])
            st["resolved"][pkg] = pick[1]
            save_state(st)
            cj = json.load(open(os.path.join(dest, "composer.json")))
            print("ok %s %s" % (pkg, pick[1]))
            for dep, c in cj.get("require", {}).items():
                dep = dep.lower()
                if dep in SKIP or dep.startswith("ext-") or dep.startswith("lib-") or dep == "php":
                    continue
                if dep.startswith("illuminate/") or dep.startswith("psr/log-implementation"):
                    continue  # provided by laravel/framework monorepo
                # skip virtual implementations
                if dep.endswith("-implementation"):
                    continue
                st["constraints"].setdefault(dep, [])
                if c not in st["constraints"][dep]:
                    st["constraints"][dep].append(c)
                if dep not in st["resolved"]:
                    queue.append(dep)
                    done_any = True
            # replaced packages: mark resolved so we don't fetch (e.g. illuminate/*)
            for rep in cj.get("replace", {}):
                st["resolved"].setdefault(rep.lower(), "replaced:" + pkg)
            save_state(st)
    print("RESOLVE COMPLETE: %d packages" % len([k for k, v in st["resolved"].items() if not str(v).startswith("replaced:")]))

if __name__ == "__main__":
    main()
