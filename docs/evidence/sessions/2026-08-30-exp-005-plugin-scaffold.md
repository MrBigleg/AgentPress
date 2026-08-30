# EXP-005 — WordPress plugin scaffold

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-005` |
| Related task | `AP-001` |
| Status | `COMPLETED` |
| Result | `SUPPORTED` |
| Started local | `2026-08-30T10:44:44+07:00` |
| Started UTC | `2026-08-30T03:44:44Z` |
| Ended local | `2026-08-30T11:35:51+07:00` |
| Ended UTC | `2026-08-30T04:35:51Z` |
| Agent/operator | Codex, implementation agent |
| Branch | started on `main`; isolated on `ap-001-plugin-scaffold` before commit |
| Baseline commit | `89b4def3a2b4ae4d1b463885a787e2a3e45e1dda` |
| Ending commit | `807275f48002cb60793fe8c79e4283d11eef0d14` (implementation); evidence closeout commit follows |
| Environment | Windows; Node.js 22.23.2; npm 10.9.8; Docker 29.6.1; Git 2.55.0.windows.3; supported WordPress 6.9/PHP 8.0.30; controls WordPress 6.8/PHP 8.0.30 and WordPress 6.9/PHP 7.4.33; Playwright CLI browser; host PHP and Composer not installed |

## Question

Can the minimal AgentPress plugin scaffold install and activate on WordPress 6.9 with PHP 8.0+, while failing closed with one explanatory administrator notice on unsupported WordPress or PHP versions?

## Hypothesis

A dependency-light bootstrap with a guarded PSR-4 autoloader, version checks before runtime initialization, containerized WordPress tooling, and reproducible packaging can satisfy AP-001 without introducing any v0.1 product behavior ahead of its dependencies.

## Falsification condition

The hypothesis is falsified by a fatal error or PHP notice on the supported activation path, any runtime or Ability registration on an unsupported version, an unsupported version without exactly one explanatory administrator notice, a release ZIP that cannot be produced from documented commands, or an undeclared dependency on host PHP/Composer.

## Controls

- fixed commit/build: baseline `89b4def3a2b4ae4d1b463885a787e2a3e45e1dda`; no commit, push, deployment, or publication authorized;
- fixed fixture/data: clean WordPress installation with only AgentPress under test;
- fixed identity/capabilities: Administrator for activation and notice inspection;
- fixed policy/configuration: PRD v2 and implementation specification; no Abilities, REST routes, database tables, or product UI in AP-001;
- fixed client/environment: `.wp-env.json` targets WordPress 6.9; PHP minimum 8.0; Windows host with containerized runtime;
- explicit scope exclusions: AP-002 through AP-036, including bridge code, abilities, storage, approvals, and live Site Tools behavior.

## Variables

- **Independent:** WordPress version, PHP version, plugin activation state, and source versus packaged ZIP installation.
- **Dependent:** activation result, count/content of compatibility notices, runtime initialization, PHP errors, tooling exit codes, and ZIP contents/checksum.

## Preflight

```text
timestamp local: 2026-08-30T10:44:44+07:00
timestamp UTC: 2026-08-30T03:44:44Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main
git log -3 --oneline --decorate:
89b4def (HEAD -> main, origin/main, origin/HEAD) docs: close hero link evidence record
7f4dc00 docs: add AgentPress concept hero link
41c5e26 docs: add AgentPress research and evidence framework
baseline SHA: 89b4def3a2b4ae4d1b463885a787e2a3e45e1dda
current branch: main
unrelated existing changes: none observed; Git emitted a read-only global-ignore permission warning
AP task, issue, and PR: AP-001; issue created after verification as GitHub #1; PR pending
```

## Method

1. Record the repository and environment baseline before mutation.
2. Create the specified plugin layout needed by AP-001 only: entrypoint, compatibility guard, plugin runtime shell, autoloading, uninstall policy, package metadata, test configuration, and package script.
3. Add unit/static tests for the compatibility decision, autoloading, headers, and deterministic package manifest.
4. Install dependencies with the documented commands when the environment permits.
5. Boot the WordPress 6.9 test environment, activate AgentPress, and inspect PHP/WordPress output.
6. Run negative compatibility controls for WordPress 6.8 and PHP 7.4, asserting no runtime initialization and exactly one administrator notice.
7. Build the installable ZIP twice, inspect its contents, and compare SHA-256 results.
8. Record all failures and reruns in order; conclude only from observed results.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used | Not applicable | Not applicable | Work performed and checked in the primary session |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | [WordPress plugin header requirements](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/) | 2026-08-30 | WordPress recognizes plugin metadata including `Requires at least` and `Requires PHP`. | Current primary documentation; runtime guards remain necessary. |
| S2 | `SOURCE_VERIFIED` | [wp-env documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/) | 2026-08-30 | `wp-env` provides Docker-backed local WordPress environments and lifecycle commands. | Runtime availability still requires current verification. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-08-30T10:44:44+07:00 | `git status --short --branch`; recent log; SHA; timestamps | repository root | exit 0 | Clean `main` at baseline SHA; four documentation-only commits in history. |
| 2026-08-30 (time not independently captured; after preflight) | Inspect `php`, `composer`, `node`, `npm`, `docker`, and `git` | repository root | mixed | Node, npm, Docker CLI, and Git observed; PHP and Composer not found; npm and Docker also reported sandboxed user-config access warnings. |
| 2026-08-30T10:51:16+07:00 to 2026-08-30T10:52:30+07:00 (lockfile creation/modification) | Initial `npm install`; `npm audit --json`; upgrade to `@wordpress/env@11.14.0`; reinstall and audit | repository root | initial audit exit 1; final install/audit exit 0 | Initial tree reported the `extract-zip<=2.0.1` high-severity advisory; patched tree installed 437 packages and reported zero vulnerabilities. |
| 2026-08-30 (time not independently captured; before supported runtime start) | Run initial `npm run build:zip` twice and hash each output | repository root | exit 0, check failed | First SHA-256 `F2750126092D2DC19A1C0BFC8EF408C8ECB635A00574EF105EA9082ABA7BD686`; second `E45D6CCBDCBE9E0716C80D43584E2A0363B5CFA138517520E8E7B3930D75CF7E`. Contents were bounded and correct but archive was not reproducible. |
| 2026-08-30 (time not independently captured; immediately after failed build control) | Rebuild twice after ordered-buffer/UTC metadata correction | repository root | exit 0 | Both SHA-256 values were `32929C2B10CB4140CDB6CDAB9EDD54723E41FD3A347537B7348560EDE84471CA`; interim reproducibility control passed. |
| 2026-08-30 (time not independently captured; before 10:59:30+07:00 runtime start) | Initial `npm run env:start`; launch Docker Desktop after failure; exact retry | repository root | first exit 1; retry exit 0 | Initial named-pipe connection failed because Docker Desktop was stopped; authorized launch made Docker 29.6.1 ready; WordPress startup completed in 224.650 seconds. |
| 2026-08-30T10:59:30+07:00 (Apache container log) | Supported-runtime checks with `wp-env run cli wp ...`, HTTP request, and bounded logs | WordPress 6.9/PHP 8.0 environment | exit 0 | AgentPress 0.1.0 active; version option persisted; runtime `booted`; HTTP 200; no AgentPress/PHP warning or fatal observed. |
| 2026-08-30T11:01:22+07:00 (Composer lockfile time) | Install Composer dependencies in CLI container | plugin mount | exit 0 | 36 locked development packages installed and autoload generated. |
| 2026-08-30 (time not independently captured; after Composer install) | Run PHPUnit and PHPCS, correct recorded lint/configuration failures, and rerun | plugin mount | initial mixed; final exit 0 | PHPUnit passed 7 tests/12 assertions; final PHPCS passed 6 production files. The incorrect first PHPCBF invocation was retained before the corrected direct invocation. |
| 2026-08-30 (time not independently captured; before browser captures) | Start/query WordPress 6.8/PHP 8.0 and WordPress 6.9/PHP 7.4 controls | isolated wp68/php74 environments | exit 0 with expected option lookup failures | Both kept AgentPress inactive, produced one requirements warning, created no version option, and loaded no runtime class. |
| 2026-08-30T11:14:33+07:00 (ZIP file time) | Pre-commit ZIP build twice, hash/list, and install on clean unmapped supported site | repository root and isolated zip environment | exit 0 | Both builds SHA-256 `CF75187F7E107C9D5C9B17F898D395C3666B08C6CBD29D87D2CF800B610088A4`; ten expected entries; clean install activated and booted. Four later EOF-only fixes changed artifact bytes before commit. |
| 2026-08-30T11:20:43+07:00 and 2026-08-30T11:21:57+07:00 (screenshot file times) | Browser-login and inspect Installed Plugins on both controls | local wp-admin sites | supported observation | WordPress 6.8 showed one “does not work with your version of WordPress” row; PHP 7.4 showed one “does not work with your version of PHP” row. Screenshots saved and visually inspected. |
| 2026-08-30T11:31:18+07:00 | Create `difficulty: S`, milestone `v0.1`, and GitHub issue #1 | GitHub repository | success | [AP-001 issue #1](https://github.com/MrBigleg/AgentPress/issues/1) contains the task, dependency, deliverable, acceptance test, evidence pointer, and scope boundary. |
| 2026-08-30T11:35:51+07:00 | Pre-commit rerun: supported `wp-env`, PHPUnit, PHPCS, npm audit, Node syntax | task branch and supported container | exit 0 | PHPUnit 7 tests/12 assertions; PHPCS 6/6 files; npm zero vulnerabilities; `build-zip.mjs` syntax valid. |
| 2026-08-30T11:38:25+07:00 | Commit verified AP-001 implementation | `ap-001-plugin-scaffold` | success | Commit `807275f48002cb60793fe8c79e4283d11eef0d14`; 27 scoped files; no generated dependency tree, ZIP, browser profile, push, or deployment included. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | Recent history contains requirements, evidence framework, and concept-site documentation only; no plugin scaffold exists. | preflight and repository file listing | Supports AP-001 as the correct next task. |
| O2 | `OBSERVED` | The initial worktree is clean on `main` at `89b4def3a2b4ae4d1b463885a787e2a3e45e1dda`. | preflight | Establishes an attributable baseline. |
| O3 | `OBSERVED` | Host PHP and Composer are unavailable; Node and Docker CLI are installed. | environment command | Requires containerized/runtime tooling and makes host-PHP verification unavailable. |
| O4 | `SOURCE_VERIFIED` | Current WordPress plugin headers support `Requires at least` and `Requires PHP`; current `wp-env` supports local package installation, Docker runtimes, explicit core/PHP versions, and containerized Composer/PHPUnit/WP-CLI. | S1 and S2 | Supports the scaffold and test-method choices. |
| O5 | `OBSERVED` | The supported environment ran WordPress 6.9/PHP 8.0.30 with AgentPress active, version option persisted, and the runtime shell booted. | supported WP-CLI checks | Supports the activation hypothesis. |
| O6 | `OBSERVED` | PHPUnit passed 7 tests/12 assertions and PHPCS passed all 6 production files after the recorded correction. | container test commands | Supports the compatibility and quality-tooling portions of AP-001. |
| O7 | `OBSERVED` | Both unsupported environments kept AgentPress inactive, created no version option, loaded no runtime class, and presented exactly one WordPress compatibility message in wp-admin. | WP-CLI controls and compatibility screenshots | Supports the fail-closed hypothesis with zero initialization. |
| O8 | `OBSERVED` | The generated ZIP installed and activated on a clean, unmapped supported site. | isolated ZIP install command | Supports installability independently of the source mount. |
| O9 | `OBSERVED` | Two pre-commit ZIP builds were byte-identical at `CF75187F...`, and two post-commit-source builds after EOF cleanup were byte-identical at `2AB78B6C4B1CC4DB0DFEA7084DA52D1E9A29C3DC56C48D960737A3D12612BA7E`. | build controls; EXP-007 | Supports packaging reproducibility while distinguishing the installed pre-format artifact from the current source artifact. |
| O10 | `OBSERVED` | A concurrent owner commit `e665779` added EXP-006 while AP-001 was still uncommitted; the EXP-006 work was preserved in the task-branch base. | mid-session status/log comparison | Neutral to hypothesis; changes ending commit context without changing the AP-001 baseline. |
| O11 | `OBSERVED` | Verified AP-001 changes were moved intact from `main` onto local branch `ap-001-plugin-scaffold`; publication waited for explicit owner authorization. | branch/status checks | Improved task isolation while preserving the authorization boundary. |
| O12 | `OBSERVED` | After owner authorization, GitHub issue #1, the `difficulty: S` label, and `v0.1` milestone were created before commit; implementation was committed as `807275f`. | GitHub issue/milestone and Git output | Satisfies the checklist metadata requirement and establishes dated source history. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | Host tool version command completes without errors. | PHP and Composer commands failed because neither executable is installed; npm/Docker read sandboxed user configuration with warnings. | environment constraint | Use repository-local Node tooling and Docker-backed WordPress; record any runtime block rather than substituting static evidence. |
| C2 | The WordPress 6.9-tagged `wp-env` package has no known high-severity advisory. | npm reported a transitive `extract-zip` path-traversal advisory fixed in current `@wordpress/env@11.14.0`. | dependency security defect | Updated to current patched `wp-env`; audit rerun passed with zero findings. |
| C3 | Fixed entry dates make the initial Archiver build reproducible. | Two consecutive ZIP SHA-256 values differed. | packaging defect | Resolved: sorted in-memory buffers plus UTC metadata produced matching hashes in both subsequent two-build controls. |
| C4 | `wp-env` can boot the WordPress runtime because Docker CLI is installed. | Docker API named pipe was absent because Docker Desktop was not running. | environment blocker | Resolved after authorized background launch; exact startup rerun passed. |
| C5 | Initial coding-standard configuration passes the scaffold. | PHPUnit passed, but PHPCS found PascalCase filename conflicts with the specified layout plus missing docblocks and trailing blank lines. | lint/configuration defect | Resolved: only conflicting filename/reserved-parameter sniffs excluded, docs fixed, PHPCBF applied, PHPCS rerun passed. |
| C6 | The first PHPCBF command forwards its standard argument through Composer and wp-env. | Composer consumed `--standard`; command exited 1 without formatting. | command invocation defect | Corrected by invoking `vendor/bin/phpcbf` directly with wp-env argument separation; two source files fixed. |
| C7 | Existing hidden ignore rules were preserved by the first scaffold patch. | Initial `.gitignore` addition replaced 23 existing lines because the hidden file was absent from the ordinary file listing. | preservation defect | Restored every original rule from `HEAD` and added only scoped ignores; final diff confirms additive changes. |
| C8 | Manually entered execution-log minute values accurately reflected command time. | Pre-commit review found several estimated times later than the actual current time. | evidence chronology defect | Removed false precision, replaced recoverable entries with authoritative lockfile/ZIP/screenshot/log timestamps, explicitly marked unrecoverable times as not independently captured, and retained this correction before commit. |
| C9 | The pre-commit ZIP checksum remained the checksum of the committed source tree. | Four EOF-only fixes after the pre-commit build changed ZIP bytes; EXP-007 detected the current hash as `2AB78B6C...`. | artifact-evidence drift | Retained the installed pre-format hash, added the current committed-source hash, and updated the verification/artifact inventory before PR merge. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Implement only the AP-001 scaffold in this experiment and defer all product behavior to dependency-ordered experiments. | O1; build checklist | More experiments, but preserves causal and competition evidence. | AP-001 acceptance is reproducibly green. |
| D2 | `DECIDED` | Do not require host PHP or Composer for normal scaffold commands; use `wp-env` for the runtime path. | O3; S2 | Docker becomes required for local integration verification. | CI or a supported host-PHP path is added later. |
| D3 | `DECIDED` | Pin current `@wordpress/env@11.14.0` rather than the WordPress-6.9 tag because the older package carried a high-severity extraction advisory. | C2; npm audit | Tool version no longer mirrors the target core tag, but core/PHP remain pinned independently in `.wp-env` configs. | A later current release changes config/runtime behavior. |
| D4 | `DECIDED` | Exempt the WordPress filename sniff because the binding implementation specification requires PSR-4 PascalCase class paths. | C5; implementation spec section 3.1 | Small documented standards exception. | The binding layout is explicitly changed. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Clean checkout dependency installation | `npm install`; containerized `composer install --no-interaction --prefer-dist` | `PASS` | npm zero-advisory result; 36-package Composer lock/install |
| WordPress 6.9 boot and clean activation | Supported source-mount and clean ZIP sites on PHP 8.0.30 | `PASS` | WP-CLI activation/status/option/runtime checks; HTTP 200 and bounded logs |
| WordPress 6.8 fails closed with one notice | WP-CLI activation/state checks plus wp-admin browser inspection | `PASS` | no option/class; [screenshot](../assets/EXP-005/wp68-agentpress-compatibility.png) |
| PHP 7.4 fails closed with one notice | WP-CLI activation/state checks plus wp-admin browser inspection | `PASS` | no option/class; [screenshot](../assets/EXP-005/php74-agentpress-compatibility.png) |
| Installable ZIP script and contents | Two-build controls before and after EOF cleanup, ten-entry listing, isolated clean-site install | `PASS` | installed pre-format SHA-256 `CF75187F...`; current-source SHA-256 `2AB78B6C4B1CC4DB0DFEA7084DA52D1E9A29C3DC56C48D960737A3D12612BA7E` |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `agentpress/` | source/tests/tooling | untracked | repository paths | Entrypoint, compatibility guard, runtime shell, autoloader, uninstall policy, Composer lock/config, PHPUnit/PHPCS tests. |
| `.wp-env.json`, `.wp-env.wp68.json`, `.wp-env.php74.json`, `.wp-env.zip.json` | test configuration | untracked | repository paths | Supported, compatibility, and clean-ZIP environments. |
| `package.json`, `package-lock.json`, `scripts/build-zip.mjs` | tooling | untracked | repository paths | Pinned npm tree and deterministic ZIP generator. |
| `dist/agentpress.zip` | generated ZIP | ignored | current source `2AB78B6C4B1CC4DB0DFEA7084DA52D1E9A29C3DC56C48D960737A3D12612BA7E`; installed pre-format artifact `CF75187F...` | Test artifacts only; not AP-030 release evidence. |
| `docs/evidence/assets/EXP-005/wp68-agentpress-compatibility.png` | screenshot | untracked | `71D5D7D5552272ED695546C63BFF6F05165DB755B3A5E5993F4703BAE5E8E4AA` | Local WordPress 6.8 compatibility table; no private data. |
| `docs/evidence/assets/EXP-005/php74-agentpress-compatibility.png` | screenshot | untracked | `3C4DB43D33EC26F368ED75B49630AA6AC883A8D33735ADF31F1018807F1530BB` | Local PHP 7.4 compatibility table; no private data. |
| `docs/evidence/sessions/2026-08-30-exp-005-plugin-scaffold.md` | evidence | untracked | repository path | Session record opened before source mutation and updated during execution. |

## Result

`SUPPORTED`

The hypothesis is supported. The minimal scaffold activates cleanly on WordPress 6.9/PHP 8.0, fails closed with one visible WordPress compatibility message and zero AgentPress initialization on WordPress 6.8 or PHP 7.4, passes its unit/coding-standard checks, builds reproducibly from both tested source states, and installs from the tested pre-format ZIP on a clean supported site.

This result establishes AP-001 only. It does not establish any Ability, WebMCP, storage, permission, approval, admin UI, live ChatGPT, deployment, release, or challenge workflow behavior.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: WordPress versions above 6.9, PHP versions above 8.0.30, non-Docker hosts, multisite, production hosting, native upload-form installation by a human, ChatGPT Site Tools, and every AP-002+ behavior.

## Competition evidence statement

- work attributable to challenge period: experiment began at the recorded baseline and timestamps;
- pre-existing work distinguished by: clean baseline SHA and recent history;
- third-party material/license/pin: `NOT_APPLICABLE` to AP-001; AP-002 will own bridge attribution;
- commit/PR evidence: AP-001 issue [#1](https://github.com/MrBigleg/AgentPress/issues/1); implementation commit `807275f48002cb60793fe8c79e4283d11eef0d14`; PR pending; concurrent owner commit `e665779` contains EXP-006 only;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: EXP-006 / AP-002;
- next falsifiable question: can the audited bridge source be pinned and attributed without shipping unrelated or obsolete upstream behavior?;
- required prerequisites: AP-001 supported result.

## End state

```text
git status --short --branch: ap-001-plugin-scaffold; implementation commit 807275f; evidence closeout changes pending
tests/checks: PHPUnit 7 tests/12 assertions PASS; PHPCS 6 files PASS; supported/control/ZIP runtime checks PASS; npm audit 0 vulnerabilities
committed: AP-001 implementation 807275f; concurrent EXP-006 commit preserved
pushed: no
deployed: no
```
