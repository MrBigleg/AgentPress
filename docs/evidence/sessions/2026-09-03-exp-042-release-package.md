# EXP-042 — Reproducible release package and clean activation

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-042` |
| Related task | `AP-030` |
| Status | `COMPLETED` |
| Result | `SUPPORTED` |
| Started local | `2026-09-03T20:25:41+07:00` |
| Started UTC | `2026-09-03T13:25:41Z` |
| Ended local / UTC | `2026-09-03T20:48:37+07:00` / `2026-09-03T13:48:37Z` |
| Agent/operator | Codex primary agent |
| Branch | `main` (owner-authorized direct work) |
| Baseline / ending commit | `e86696e082a838ac87d8d5aa5f0db957c8391007` / `UNCOMMITTED` |
| Environment | Windows; Node.js 22.23.2; Docker wp-env WordPress 6.9 / PHP 8.0 |

## Question

Can the production-only AgentPress ZIP build byte-for-byte reproducibly, contain the required runtime/license/install material without development junk, and install and activate on a clean WordPress 6.9 site with the fixed 15-Ability catalog available?

## Hypothesis and falsification

The explicit sorted manifest, fixed timestamps, pinned dependencies, and existing clean wp-env make a reproducible installable package plausible.

The hypothesis is falsified if two unchanged-source builds differ, required files are absent, excluded development/vendor-test material is present, clean ZIP installation or activation fails, activation emits a PHP fatal/notice, or the installed plugin does not expose exactly 15 registered AgentPress abilities.

## Controls and variables

- Fixed source baseline, WordPress 6.9, PHP 8.0, production ZIP manifest, and no external account/configuration.
- Independent variables: consecutive build number and source-versus-ZIP plugin installation.
- Dependent variables: ZIP entries/hash, install/activation outcome, PHP output, plugin status, and Ability count.
- Excluded: hosted deployment, live ChatGPT execution, submission work, and five-run reliability.

## Preflight and planned method

```text
timestamp: 2026-09-03T20:25:41+07:00 / 2026-09-03T13:25:41Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status: clean main synchronized with origin/main
git log -3: e86696e AP-029 gate; 11dd163 AP-029 hardening; 97d1392 AP-024/AP-025
baseline SHA: e86696e082a838ac87d8d5aa5f0db957c8391007
unrelated changes: none observed
```

1. Audit the production manifest and install/upgrade documentation.
2. Build twice and compare SHA-256 and entry lists.
3. Verify license/provenance and absence of development/test material.
4. Install the ZIP into a clean WordPress 6.9 environment, activate it, inspect PHP output/plugin state, and verify the exact 15-Ability catalog.
5. Record artifacts and update release documentation/checklist only after acceptance.

## Observations

- `OBSERVED`: the AP-029 GitHub run at the baseline passed unit, browser, PHPCS, provenance, and deterministic-package steps but failed immediately in its new WordPress security step; this is recorded separately from AP-030 and will be diagnosed if it blocks the clean package verification.
- `OBSERVED`: the failure occurred because a clean wp-env contains only the Twenty Twenty-Five block theme; the canonical reset conditionally switched to Twenty Twenty-One only when already installed, leaving no registered `primary` classic-menu location.
- `DECIDED`: make the CI fixture prerequisite explicit by installing and activating Twenty Twenty-One before the security runner. This changes no production runtime behavior or supported navigation architecture.
- `OBSERVED`: two consecutive baseline builds were byte-identical at SHA-256 `C9EEFA24E1BF720C92A7ACF5971A58BD4A160FC7B6C32802EAB961F213769C17`; the ZIP contained 66 entries and 130,752 bytes.
- `OBSERVED`: the provenance/license boundary passed and an entry scan found zero test, node_modules, vendor, Git, docs, scripts, PHPUnit, PHPCS, or package-manager artifacts.
- `OBSERVED`: the first isolated ZIP installation failed activation with a PHP fatal because `includes/Changes/ChangeSetReadService.php` was absent from the explicit build manifest even though source-mounted tests passed.
- `DECIDED`: add the missing runtime class to the manifest and package-boundary assertion, then discard the failed candidate hash rather than presenting it as a release artifact.
- `OBSERVED`: after correction, two consecutive builds matched at SHA-256 `234A1981C8D15DE97E125F064170BC868448134DEBFC5A8541F78090AC88B97F`; the final ZIP contains 67 entries and 134,110 bytes.
- `OBSERVED`: a separate wp-env project downloaded the ZIP as an archive source, installed it on WordPress 6.9/PHP 8.0.30, and activated AgentPress 0.1.0 without a plugin fatal or notice.
- `OBSERVED`: explicit deactivate/reactivate succeeded, `agentpress_db_version=1`, `agentpress_version=0.1.0`, and all three `wp_agentpress_*` tables existed.
- `OBSERVED`: the installed archive registered exactly 15 AgentPress Abilities. After setting the disposable site's `WP_HOME` and `WP_SITEURL` to HTTPS, `agentpress/get-context` returned a valid success envelope (`[15,1]`).
- `OBSERVED`: intermediate PHP/table probes failed because nested quotes were stripped by the Windows wp-env command boundary; corrected quote-free commands established the PHP version, catalog count, complete table list, and smoke result. No product-source change followed those harness errors.
- `OBSERVED`: the isolated release environment and temporary HTTP server were removed after verification; the normal source-mounted development environment was preserved.

## Result

`SUPPORTED`: the corrected archive is byte-reproducible, contains required runtime and license material without scanned development junk, installs and activates cleanly on the supported WordPress/PHP target, creates its schema, exposes exactly 15 Abilities, and executes the context smoke on an HTTPS-configured disposable site.

## Verification matrix and artifacts

| Acceptance condition | Outcome | Evidence |
|---|---|---|
| Two unchanged-source builds match | `PASS` | SHA-256 `234A1981C8D15DE97E125F064170BC868448134DEBFC5A8541F78090AC88B97F` |
| Production/license boundary | `PASS` | 67 entries; provenance test passed; zero scanned development artifacts |
| Clean install and activation | `PASS` | WordPress 6.9; PHP 8.0.30; AgentPress 0.1.0 active |
| Database initialization | `PASS` | version options plus three `wp_agentpress_*` tables |
| Catalog and smoke | `PASS` | 15 registered Abilities; `get-context` success envelope |
| Hosted install/upgrade | `NOT_TESTED` | local clean wp-env only |

| Artifact | State | SHA-256 / notes |
|---|---|---|
| `dist/agentpress.zip` | ignored local release artifact | `234A1981C8D15DE97E125F064170BC868448134DEBFC5A8541F78090AC88B97F`; 134,110 bytes; 67 entries |
| `agentpress/readme.txt` | tracked source | install, replacement-upgrade, and v0.1 changelog text |

## Limitations and next experiment

- `NOT_TESTED`: hosted upload/replacement, real ChatGPT execution against this exact checksum, and five consecutive canonical runs.
- Next: finish AP-028's Administrator/Author real-client gate, then run AP-031 5/5 using this release candidate.

## End state

```text
git status: AP-030 source/docs changes pending commit on main
checks: two-build reproducibility PASS; provenance/package boundary PASS; clean install/activate/catalog/context PASS
committed/pushed: pending owner-authorized closeout
deployed: no
```
