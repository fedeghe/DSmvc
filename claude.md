# Instructions for Claude

## Conversation language
All conversation with the user happens in **Italian**.

## Code language
All code written in the codebase (class names, methods, variables, comments, internal code documentation, configuration files, etc.) MUST be exclusively in **English**.

## Exception
The only exception is when explicitly requested by the user for a specific case.

---

# DSMVC Framework — Usage Guide

DSMVC is a self-contained PHP micro-framework (front controller MVC): proprietary template parser, MySQLi as DB layer, automatic i18n, no Composer dependencies. Code requires PHP 8.

## Project structure

```
.htaccess              rewrites everything → /htdocs/
config.php             wrapper that includes config.sample.php
config.sample.php      config constants (DB_*, APP_NAME, DEFAULT_LANG, SHRINK_HTML)
configs/dev.php        real config (git-ignored); uncomment its include in config.sample.php
core/                  framework engine
  /config/             bootstrap.php, define.php (constants), langset.php, error_handling.php
  /lib/                db.php, auth.php, Csrf.php, utility.php, arr.php, chunks/, snippets/
app/                   application code
  /controllers/        <name>.php  (class name = file name, lowercase)
  /views/              <name>.phtml
  /chunks/             <name>.phtml   (included with {{name}})
  /snippets/           <name>.php     (included with [[name]])
  /models/             <Name>.php     (CamelCase)
  /i18n/               <lang>.php (en, it, de, ch, jp)
  /lib/                app utility classes (e.g. mydb.php)
  csp.php              Content-Security-Policy sent on every request
htdocs/                the actual web root (index.php = front controller)
  /css/ /js/ /media/   static assets (paths relative to htdocs)
  /models/             WARNING: NOT MVC models — 3D model assets for the JS demo
cache/                 full-page cache (disabled by default, 60s TTL)
cleanCache.sh          empties the cache
```

## Response flow

`htaccess → htdocs/index.php → core/config/bootstrap.php → app/csp.php → Dispatcher::dispatch() → Request::handle() → controller → Response::getContent() → echo`

The bootstrap defines all constants (see `core/config/define.php`): `DSMVC` (gate: every core file starts with `defined('DSMVC') || die`), paths (`PATH_APP`, `PATH_VIEW`, ...), URLs (`URL_ROOT`, `URL_BASE`, `URL`), environment (`ON_DOMAIN` true = vhost, false = subfolder; `ONLINE`). If `ONLINE=false` → redirect to `/offline.php`.

Superglobals (`$_POST`, `$_GET`, `$_REQUEST`, `$_COOKIE`) are **pre-escaped with `mres()`** by the bootstrap (magic-quotes style). Keep this in mind when building queries.

## Configuration

Constants in `config.sample.php` / `configs/dev.php`: `DB_NAME, DB_USER, DB_PWD, DB_HOST, DB_PORT, APP_NAME, DEFAULT_LANG, SHRINK_HTML (true=minify HTML), FAVICON (optional)`. Multi-DB environments: subclass in `app/lib/mydb.php` with a `case '<name>'` and define `DB='<name>'`.

## Routing

URL pattern: `/<controller>/<action>/<key>/<val>/<key2>/<val2>?get=...`
- All lowercase; `.html`/`.php` suffixes are allowed and stripped.
- Segments after the action are key/value pairs → `$params` array; merged with `$_GET` (GET wins).
- Missing controller → fallback to `ctrl404` (app/controllers/ctrl404.php), index action.
- Nonexistent action → redirect to `URL_BASE` (home).
- Language switch: append `?lang=it` to any URL (stored in `$_SESSION['lang']`).

## Controllers

File `app/controllers/<name>.php` → `class <name> extends Controller`. File name = URL segment.

```php
class index extends Controller {
    public function before() {}               // optional hooks: before(), before_<action>(),
    public function after() {}                //   after_<action>(), after()
    public function action_index($params, $url) {
        // $params = key/value array from URL+GET; also available as $this->key ("first-assignment-wins")
        // Request::ajaxCheck();   // die() unless called via XMLHttpRequest (AJAX-only endpoints)
        // Csrf::validate();      // MANDATORY here for POST handlers (mismatch → 403)
        Response::send($html);   // the ONLY output path for HTML
    }
}
```

- Hook lifecycle: `before()` → `before_<action>()` → `action_<action>` → `after_<action>()` → `after()`.
- Action signature: `action_<name>($params, $url, $controller, $action)`.
- `$this-><var>` returns a URL param; `false` if absent.

## Views and Parser markers

Views are `.phtml` = full PHP+HTML. In the controller:

```php
$v = DSMVC::getView('canvas');         // app/views/canvas.phtml
$v->title = 'Page title';              // property (only the FIRST assignment wins)
$v->head = DSMVC::getView('head')->display();   // compose partial by injecting its output
Response::send($v->display());         // parse (AUTO_PARSE) + minify (SHRINK_HTML)
```

Markers (resolved recursively, also nested inside chunks):

| Marker | Meaning |
|---|---|
| `[$var$]` | view property |
| `[D[CONST]D]` | PHP constant |
| `[S[x]S]` / `[P[x]P]` / `[G[x]G]` | `$_SESSION` / `$_POST` / `$_GET` |
| `{{name}}` | chunk `app/chunks/name.phtml` (fallback `core/lib/chunks/`) |
| `[[name]]` | snippet `app/snippets/name.php` (also `name/name.php`) |
| `[[name &par=\`val\`]]` | snippet with parameters → local `$par` variables |
| `[[php Class::method()]]` | eval of a static call, output captured |
| `{C{css/x.css}C}` | `<link>` if the file exists (path relative to htdocs) |
| `{J{js/x.js}J}` | `<script>` if the file exists (path relative to htdocs) |
| `[V[name]V]` | direct include of `app/views/name.phtml` |
| `[T[label]T]` | translation; if missing wraps in `<i class="untranslated">` |
| `[t[label]t]` | soft translation: if missing leaves the bare label |
| `[T[label>it]T]` | translation forced to language `it` |

Layout: a partial (e.g. `head`) is rendered with `->display()` and injected as a property into the main view, which does `<?php echo $head; ?>`.

## i18n

`app/i18n/<lang>.php` defines `$trans = array('label' => 'translation', ...)`. Used only in templates via `[T[..]T]`/`[t[..]t]`. With `CUMULATE_LANG=true` (default) missing labels are **auto-written** to the language file with the label as value → then translate by hand. To translate from PHP: include `PATH_TRANSLATIONS.$_SESSION['lang'].'.php'` and read `$trans[$label]`.

## Models and DB

MySQLi (no PDO), singleton wrapper in `core/lib/db.php`. Access via `mydb::getInstance()` (app subclass in `app/lib/mydb.php`).

```php
class Places {                          // app/models/Places.php, static or plain POJO
    public static function getAll() {
        $db = mydb::getInstance();
        $res = $db->query("SELECT * FROM `hs`");
        return $db->fetch_all_assoc($res);
    }
    public static function get($id) {                       // prepared: ? for values
        $res = mydb::getInstance()->query("SELECT * FROM `hs` WHERE `id` = ?", array($id));
        return $res ? $db->fetch_assoc($res) : array();     // automatic escaping via params
    }
}
```

Main `db` API: `query($sql, $pars)`, `sql2assoc($sql, $pars)`, `fetch_all_assoc/fetch_assoc/num_rows/insert_id/affected_rows`, `real_escape_string`, `escape_identifier()` (backquotes for table/column names — `?` is for values only), `sql_array_transaction($queries)` (manual transaction). Typical CRUD: getAll/get/save/update/delete. Models are not required to extend anything.

## JSON/AJAX endpoints

```php
public function action_api($params) {
    Request::ajaxCheck();
    Response::sendJson(json_encode($data));    // Content-type: application/json, no view
}
```

## Security

- **CSRF**: `Csrf::field()` outputs the hidden input in forms; `Csrf::validate()` must be called explicitly in every POST handler. Per-session token (`Csrf::init()` from bootstrap).
- **CSP**: headers are sent by `app/csp.php` on every request. Edit there to add CDNs.
- **XSS**: `utility::make_safe()`; input is already escaped with `mres()` by the bootstrap.

## Helpers

- `utility::*`: `make_safe()` (anti-XSS), `get_combo()` (`<select>` builder), `print_d()` (dump), `validEmail()`, `trace()`, `mem()`.
- `arr::*`: `arr2treearr()` (flat→tree), `arr2attr()`/`assoc2attrib()` (array→HTML attributes), `arr2get()` (→query string), `array_partition()`, `sortArrayByArray()`.
- `debug($x)` (print_r in `<pre>`), `mres()`, `microtime_float()`.
- `Factory::get('<class>')` for the singleton pool.

## Authentication

`core/lib/auth.php` is a stub: `auth::can_enter()` returns `false` (hook point to implement), `auth::gout()` does logout+redirect. Demo user: `$_SESSION['user']`. Expected pattern: check in `before()` and redirect to `/login` (→ `htdocs/login.php`, placeholder to implement). The session is started by the bootstrap.

## Cache

Full-page file cache in `cache/`, disabled by default (`CACHE_ACTIVE=false`), hardcoded 60s TTL. Empty it with `cleanCache.sh`.