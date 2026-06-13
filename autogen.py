#!/usr/bin/env python3
"""Generate vendor/composer autoload files + installed.json/php without Composer."""
import json, os, re, sys
from collections import defaultdict

ROOT = "/sessions/blissful-beautiful-mayer/tmp/ranga"
VENDOR = os.path.join(ROOT, "vendor")
COMP = os.path.join(VENDOR, "composer")
STATE = json.load(open(os.path.join(ROOT, ".dep-state.json")))
DEV_TOP = set(STATE.get("dev", []))

os.makedirs(COMP, exist_ok=True)

pkgs = {}
for name, ver in STATE["resolved"].items():
    if str(ver).startswith("replaced:"):
        continue
    cjp = os.path.join(VENDOR, name, "composer.json")
    pkgs[name] = {"version": ver, "composer": json.load(open(cjp))}

# ---- topo sort (deps first) ----
graph = {n: [] for n in pkgs}
for n, d in pkgs.items():
    for dep in d["composer"].get("require", {}):
        dep = dep.lower()
        if dep in pkgs:
            graph[n].append(dep)
order, seen = [], {}
def visit(n):
    if seen.get(n) == 2: return
    if seen.get(n) == 1: return  # cycle
    seen[n] = 1
    for d in graph[n]:
        visit(d)
    seen[n] = 2
    order.append(n)
for n in sorted(pkgs):
    visit(n)

# ---- dev package closure ----
dev_names = set()
def mark_dev(n):
    if n in dev_names or n not in pkgs: return
    dev_names.add(n)
    for d in graph[n]:
        mark_dev(d)
for n in DEV_TOP:
    mark_dev(n)
# any package required by a non-dev pkg or root runtime is NOT dev
runtime_roots = [n for n in pkgs if n not in DEV_TOP and n not in dev_names]
runtime = set()
def mark_rt(n):
    if n in runtime or n not in pkgs: return
    runtime.add(n)
    for d in graph[n]:
        mark_rt(d)
TOP_RT = ["laravel/framework","laravel/sanctum","laravel/socialite","livewire/livewire",
          "spatie/laravel-permission","pragmarx/google2fa","guzzlehttp/guzzle"]
for n in TOP_RT:
    mark_rt(n)
dev_only = sorted(set(pkgs) - runtime)

# ---- autoload maps ----
psr4, psr0, classmap_entries, files = {}, {}, [], []
CLASS_RE = re.compile(r"^\s*(?:abstract\s+|final\s+|readonly\s+)*(class|interface|trait|enum)\s+([A-Za-z_\x80-\xff][\w\x80-\xff]*)", re.M)
NS_RE = re.compile(r"^\s*namespace\s+([^;{]+)[;{]", re.M)

def scan_classmap(base):
    out = {}
    for dirpath, _, fnames in os.walk(base):
        for f in fnames:
            if not f.endswith(".php"): continue
            p = os.path.join(dirpath, f)
            try:
                src = open(p, encoding="utf-8", errors="replace").read()
            except OSError:
                continue
            ns = NS_RE.search(src)
            prefix = (ns.group(1).strip() + "\\") if ns else ""
            for m in CLASS_RE.finditer(src):
                out[prefix + m.group(2)] = p
    return out

for name in order:
    d = pkgs[name]
    al = d["composer"].get("autoload", {})
    base = os.path.join(VENDOR, name)
    for ns, paths in al.get("psr-4", {}).items():
        paths = [paths] if isinstance(paths, str) else paths
        psr4.setdefault(ns, []).extend(os.path.join(base, p) for p in paths)
    for ns, paths in al.get("psr-0", {}).items():
        paths = [paths] if isinstance(paths, str) else paths
        psr0.setdefault(ns, []).extend(os.path.join(base, p) for p in paths)
    for p in al.get("classmap", []):
        target = os.path.join(base, p)
        if os.path.isdir(target):
            classmap_entries.append(target)
        elif os.path.isfile(target):
            classmap_entries.append(target)
    for f in al.get("files", []):
        files.append(os.path.join(base, f))

classmap = {}
for entry in classmap_entries:
    if os.path.isdir(entry):
        classmap.update(scan_classmap(entry))
    else:
        src = open(entry, encoding="utf-8", errors="replace").read()
        ns = NS_RE.search(src)
        prefix = (ns.group(1).strip() + "\\") if ns else ""
        for m in CLASS_RE.finditer(src):
            classmap[prefix + m.group(2)] = entry

def php_export_map_of_lists(m):
    lines = []
    for k in sorted(m):
        vs = ", ".join("'%s'" % v.replace("\\", "\\\\") for v in m[k])
        lines.append("    '%s' => array(%s)," % (k.replace("\\", "\\\\"), vs))
    return "<?php\nreturn array(\n%s\n);\n" % "\n".join(lines)

open(os.path.join(COMP, "autoload_psr4.php"), "w").write(php_export_map_of_lists(psr4))
open(os.path.join(COMP, "autoload_namespaces.php"), "w").write(php_export_map_of_lists(psr0))
cm_lines = "\n".join("    '%s' => '%s'," % (k.replace("\\", "\\\\"), v) for k, v in sorted(classmap.items()))
open(os.path.join(COMP, "autoload_classmap.php"), "w").write("<?php\nreturn array(\n%s\n);\n" % cm_lines)
f_lines = "\n".join("    '%s'," % f for f in files)
open(os.path.join(COMP, "autoload_files.php"), "w").write("<?php\nreturn array(\n%s\n);\n" % f_lines)

# ---- root autoload.php ----
open(os.path.join(VENDOR, "autoload.php"), "w").write("""<?php

// Generated autoloader (composer-compatible)
require_once __DIR__ . '/composer/ClassLoader.php';
require_once __DIR__ . '/composer/InstalledVersions.php';

$loader = new \\Composer\\Autoload\\ClassLoader(__DIR__);
foreach (require __DIR__ . '/composer/autoload_psr4.php' as $ns => $paths) {
    $loader->setPsr4($ns, $paths);
}
foreach (require __DIR__ . '/composer/autoload_namespaces.php' as $ns => $paths) {
    $loader->set($ns, $paths);
}
$loader->addClassMap(require __DIR__ . '/composer/autoload_classmap.php');

// application namespaces
$loader->setPsr4('App\\\\', array(__DIR__ . '/../app'));
$loader->setPsr4('Database\\\\Factories\\\\', array(__DIR__ . '/../database/factories'));
$loader->setPsr4('Database\\\\Seeders\\\\', array(__DIR__ . '/../database/seeders'));
$loader->setPsr4('Tests\\\\', array(__DIR__ . '/../tests'));

$loader->register(true);

foreach (require __DIR__ . '/composer/autoload_files.php' as $file) {
    require_once $file;
}

return $loader;
""")

# ---- installed.json ----
def norm_ver(v):
    m = re.match(r"^v?(\d+)\.(\d+)(?:\.(\d+))?", v)
    if m:
        return "%s.%s.%s.0" % (m.group(1), m.group(2), m.group(3) or "0")
    return "1.0.0.0"

packages_json = []
for name in order:
    d = pkgs[name]
    cj = dict(d["composer"])
    cj["name"] = name
    cj["version"] = d["version"]
    cj["version_normalized"] = norm_ver(d["version"])
    cj["install-path"] = "../" + name
    packages_json.append(cj)
json.dump({"packages": packages_json, "dev": True, "dev-package-names": dev_only},
          open(os.path.join(COMP, "installed.json"), "w"), indent=1)

# ---- installed.php ----
lines = []
lines.append("'ranga/ranga' => array('pretty_version' => 'dev-main', 'version' => 'dev-main', 'reference' => null, 'type' => 'project', 'install_path' => __DIR__ . '/../../', 'aliases' => array(), 'dev_requirement' => false),")
for name in order:
    d = pkgs[name]
    lines.append("'%s' => array('pretty_version' => '%s', 'version' => '%s', 'reference' => null, 'type' => '%s', 'install_path' => __DIR__ . '/../%s', 'aliases' => array(), 'dev_requirement' => %s),"
                 % (name, d["version"], norm_ver(d["version"]), d["composer"].get("type", "library"), name, "true" if name in dev_only else "false"))
for name, ver in STATE["resolved"].items():
    if str(ver).startswith("replaced:"):
        lines.append("'%s' => array('dev_requirement' => false, 'replaced' => array('*')),"% name)
open(os.path.join(COMP, "installed.php"), "w").write(
    "<?php return array(\n  'root' => array('name' => 'ranga/ranga', 'pretty_version' => 'dev-main', 'version' => 'dev-main', 'reference' => null, 'type' => 'project', 'install_path' => __DIR__ . '/../../', 'aliases' => array(), 'dev' => true),\n  'versions' => array(\n    %s\n  ),\n);\n"
    % "\n    ".join(lines))

print("autoload generated: %d psr4 prefixes, %d classmap, %d files, %d dev-only pkgs"
      % (len(psr4), len(classmap), len(files), len(dev_only)))
