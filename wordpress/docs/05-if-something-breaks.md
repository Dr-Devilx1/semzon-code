# If the site shows "There has been a critical error"

That message means PHP hit a fatal error. WordPress hides the detail from
visitors on purpose. Here is how to get the site back and find out what
actually happened.

---

## 1. Get the site back up (30 seconds)

You do not need FTP. In **Hostinger → hPanel → File Manager**:

1. Go to `public_html/wp-content/themes/`
2. Rename `semzon-child` to `semzon-child-OFF`
3. Reload the site

WordPress cannot find the active theme, so it falls back to a default one. The
site will look plain, but it loads and wp-admin works again. **No content is
lost** — products, fields and settings all live in the database.

If it is still broken with the theme off, the cause is a plugin. Rename
`public_html/wp-content/plugins/semzon-setup` the same way.

To put it back, just rename the folder to its original name.

---

## 2. Get the actual error message

This is the part that matters — everything else is guesswork without it.

**Option A — Hostinger's error log (easiest).**
hPanel → **Websites → Manage → Advanced → PHP Error Log**. The newest entry
will read something like:

```
PHP Fatal error: Cannot redeclare class Semzon_CPT ... in /wp-content/... on line 42
```

**Option B — turn on WordPress debug logging.**
File Manager → edit `public_html/wp-config.php`. Find the line
`/* That's all, stop editing! */` and paste **above** it:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Reload the broken page once, then open
`public_html/wp-content/debug.log` and copy the last 20 lines.

**Turn this back off before go-live** — change `WP_DEBUG` to `false`.

**Send me those lines.** The file name and line number tell me exactly what
to fix, in one round instead of three.

---

## 3. Known cause, already fixed in v1.2

If the log says **"Cannot redeclare class Semzon_CPT"** (or `Semzon_ACF`,
`Semzon_Admin`), the cause is a leftover copy of the **v1.0 installer plugin**.
That version declared those classes itself; they now live in the theme, so
having both installed meant PHP saw the same class twice.

Fix:

1. Plugins → find **any** older SEMZON Setup entry → Deactivate → **Delete**
2. Check `wp-content/plugins/` in File Manager for a stray folder such as
   `semzon-setup-2` or `semzon-setup-1` and delete it
3. Upload the v1.2 theme and plugin from this delivery

v1.2 also guards every class declaration, so even if a stale copy survives
somewhere, the site stays up instead of white-screening.

---

## 4. Templates will not import

Elementor → Templates → **Saved Templates** → *Import Templates*.

- Upload **`semzon-templates.zip`** (inside `elementor-templates.zip`) — that
  is the file Elementor's bulk importer expects. Individual `.json` files work
  too, one at a time.
- Do not upload the outer `elementor-templates.zip` itself; unzip it first.
- If it still refuses, check **Elementor → Settings → Features** and confirm
  nothing is disabling the template library.

The v1.2 templates are plain Elementor **sections** built from
section → column → widget with core widgets only, so they import on any
Elementor version, free or Pro, with or without the Container experiment.

---

## 5. Order of operations that avoids all of this

1. Deactivate + **delete** any older SEMZON Setup plugin
2. Appearance → Themes → upload the v1.2 theme, activate
3. Plugins → upload the v1.2 installer, activate
4. SEMZON → Setup Wizard → Run setup
5. Delete the installer plugin
6. Import the templates

---

## What I can and cannot see

I have no network access from where I work, so I cannot open your site, read
your logs, or run a real WordPress install to test against. Everything I ship
is verified against an automated harness (137 checks: registrations, field-name
contracts, the full import, idempotency, plugin-removal survival, template
structure, and a duplicate-declaration regression) — but a harness cannot catch
a genuine incompatibility with your specific Elementor, ACF or PHP version.

That is why the log lines from step 2 are worth more than any amount of
guessing on my side. Paste them and the next fix is targeted.
