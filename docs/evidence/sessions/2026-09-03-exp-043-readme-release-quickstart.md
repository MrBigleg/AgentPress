# EXP-043 — User-first README and downloadable v0.1 release candidate

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-043` |
| Related task | `AP-036` (partial; owner-authorized ahead of the AP-031 P2 gate) |
| Status | `IN_PROGRESS` |
| Result | `PENDING` |
| Started local | `2026-09-03T21:16:17+07:00` |
| Started UTC | `2026-09-03T14:16:17Z` |
| Ended local | `PENDING` |
| Ended UTC | `PENDING` |
| Agent/operator | Codex primary agent |
| Branch | `main` (owner-authorized direct work) |
| Baseline commit | `ddc7ac5bfa48112ea133264dfce51bd46f09c44c` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows; Node.js 22.23.2; npm 11.7.0; Docker 29.6.1; clean-install WordPress/PHP target pending |

## Question

Can an unaffiliated WordPress user find the actual AgentPress plugin ZIP, install and activate it through wp-admin, reach the supported Site Tools page, and distinguish the release candidate, repository source, concept site, and remaining verification limits without developer knowledge?

## Hypothesis

A versioned GitHub prerelease, prominent direct-download link, ordered WordPress installation steps, accurate 15-tool catalog, and separate developer setup will provide a complete first-run path while preserving the project's evidence boundaries.

## Falsification condition

The hypothesis is falsified if the documented asset is unavailable or differs from the tested checksum, GitHub's source archive can still be mistaken for the plugin package, the ZIP does not clean-install and activate, version declarations disagree, the README tool catalog differs from the fixed registrar, or the instructions imply that AP-028/AP-031 or unsupported navigation has been verified.

## Controls

- fixed commit/build: baseline `ddc7ac5bfa48112ea133264dfce51bd46f09c44c`; release changes limited to documentation and RC version metadata;
- fixed fixture/data: clean WordPress 6.9/PHP 8.0 package-install environment and the existing 15-Ability catalog smoke;
- fixed identity/capabilities: authenticated Administrator for package activation/context smoke;
- fixed policy/configuration: Safe Mode and all Ability/REST/database behavior unchanged;
- fixed client/environment: local clean-install verification plus public GitHub prerelease download;
- explicit scope exclusions: no Ability, REST, database, authorization, UI behavior, deployment, AP-028 completion, AP-031 reliability claim, or stable v0.1 release.

## Variables

- **Independent:** RC version metadata, README information architecture, and public release-asset availability.
- **Dependent:** version consistency, link validity, package reproducibility/hash, clean activation, Ability count/context result, and unaffiliated-user install-path completeness.

## Preflight

```text
timestamp: 2026-09-03T21:16:17+07:00 / 2026-09-03T14:16:17Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: clean; main synchronized with origin/main
git log -3: ddc7ac5 README rewrite; 1824c8d AP-030 package; e86696e AP-029 gate
baseline SHA: ddc7ac5bfa48112ea133264dfce51bd46f09c44c
unrelated existing changes: none observed
GitHub releases/tags: no releases returned by authenticated `gh release list`; no local tags
```

## Method

1. Change every installed/build RC version declaration to `0.1.0-rc.1` without altering runtime interfaces.
2. Rewrite the README entry path around the exact GitHub prerelease asset, wp-admin ZIP upload, activation, supported-client readiness check, and read-first draft smoke.
3. Separate developer setup, document troubleshooting/known limits, and reconcile the user-facing catalog with all 15 registered tools.
4. Validate Markdown links/catalog/version consistency, then run PHP lint, unit, browser, provenance, and sequential reproducible ZIP checks.
5. Install the exact package into a clean WordPress 6.9/PHP 8.0 environment and verify activation, tables, 15 Abilities, and context smoke.
6. Commit and push the verified package source/docs, create GitHub prerelease `v0.1.0-rc.1`, upload the versioned ZIP, download it independently, and compare its SHA-256 with the tested artifact.
7. Append this record to the evidence index and retain AP-036/AP-032 as open.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | Work remains sequential to avoid competing ZIP replacement on Windows. | repository and release target | `NOT_APPLICABLE` | Primary agent performs and records every check. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `OBSERVED` | current repository at baseline | 2026-09-03 | The README begins Site Tools use before plugin acquisition/installation; `dist/` and `*.zip` are ignored. | Current checkout. |
| S2 | `OBSERVED` | authenticated GitHub release listing | 2026-09-03 | The canonical repository has no published GitHub release at preflight. | Empty `gh release list` output. |
| S3 | `OBSERVED` | [EXP-042](2026-09-03-exp-042-release-package.md) | 2026-09-03 | The prior `0.1.0` artifact was locally reproducible and clean-install verified, but was ignored and not publicly downloadable. | Historical checksum remains unchanged. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| `2026-09-03T21:16:17+07:00` | Repository, environment, and authenticated release preflight | repository / GitHub | exit 0 | Clean synchronized `main`; baseline and tool versions captured; no release returned. |
| 2026-09-03 session | Apply RC metadata and user-first documentation changes | repository | changed | Plugin/package/CI metadata aligned to `0.1.0-rc.1`; README now leads with the release asset and ordered wp-admin installation. |
| 2026-09-03 session | Static README/version/catalog checks and `git diff --check` | repository | exit 0 | Balanced fences; zero missing local links; 15 source-map names equal 15 README names; no whitespace errors. |
| 2026-09-03 session | `npm run test:unit` | repository / source-mounted wp-env | exit 0 | 68 tests, 593 assertions. |
| 2026-09-03 session | `npm run lint:php` | repository / source-mounted wp-env | exit 0 | 54/54 files passed PHPCS. |
| 2026-09-03 session | `npm run test:browser` | repository | exit 0 | 27/27 browser/admin tests passed. |
| 2026-09-03 session | `npm run test:third-party` | repository | exit 0 | GPL attribution/pin passed; 67 production entries; no upstream runtime code. |
| 2026-09-03 session | Two sequential `npm run build:zip` runs | repository | exit 0 | Both produced SHA-256 `96E50234EEC7FED583EC6ABC1A01C0A39A555B948296EBEA1E06E466E453BC4D`; 134,296 bytes; 67 entries. |
| 2026-09-03 session | Install through temporary `host.docker.internal` URL | isolated wp-env | command failure | WordPress rejected the URL before download; no plugin package conclusion. |
| 2026-09-03 session | First direct archive install/probe | pre-existing isolated wp-env volume | install succeeded; clean-state check failed | Plugin reported RC version, but stale active-plugin state from EXP-042 meant the database was not a clean activation target. |
| 2026-09-03 session | Destroy/recreate only `.wp-env.zip.json` environment | isolated wp-env | exit 0 | Separate `wp-env-WP-Agent-Admin-zip-ad175d3b` environment recreated; normal port-8888 environment preserved. |
| 2026-09-03 session | Copy exact archive into clean CLI container and `wp plugin install ... --activate` | isolated wp-env | exit 0 | Pre-install list contained only Akismet/Hello Dolly; AgentPress installed and activated successfully. |
| 2026-09-03 session | Version, runtime, schema, and catalog probes | isolated wp-env | pass with one corrected quote harness | WordPress 6.9; PHP 8.0.30; AgentPress `0.1.0-rc.1` active; three tables; 15 registered AgentPress Abilities. |
| 2026-09-03 session | Authenticated `get-context` smoke | isolated wp-env | initial expected config rejection; corrected pass | HTTP home URL failed closed as `ability_invalid_output`; setting disposable `WP_HOME`/`WP_SITEURL` HTTPS constants yielded success `1`. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | The only verified plugin ZIP is an ignored local artifact; the public repository offers no release asset. | preflight; `.gitignore`; EXP-042 | Supports the need for a public release artifact. |
| O2 | `OBSERVED` | The current Quickstart asks the user to open AgentPress and prompt a client without first acquiring, uploading, installing, or activating the plugin. | baseline `README.md` | Supports the user-first rewrite. |
| O3 | `OBSERVED` | README local links/fences, all RC declarations, and all 15 exact WebMCP names agree with current source after the rewrite. | static checks | Supports documentation correctness. |
| O4 | `OBSERVED` | Unit, PHPCS, browser, attribution, production-boundary, and two-build reproducibility gates pass for the RC source. | execution log | Supports package integrity. |
| O5 | `OBSERVED` | A genuinely clean WordPress 6.9/PHP 8.0.30 site installed and activated the exact archive, created three AgentPress tables, registered 15 AgentPress Abilities, and passed context after the required HTTPS constants were set. | isolated wp-env execution | Supports install/use prerequisites and package viability. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | A reproducible release claim should lead to a retrievable tested artifact. | The artifact exists only under ignored `dist/`; no GitHub release exists. | documentation/distribution gap | Publish an explicitly prerelease asset and verify its downloaded hash. |
| C2 | The first isolated check should begin with no AgentPress install. | The preserved EXP-042 volume already had AgentPress active and no current tables after archive replacement. | stale test-environment state | Destroyed/recreated only the dedicated ZIP environment, confirmed the pre-install plugin list, and reran successfully. |
| C3 | The local temporary archive URL should install in wp-env. | WordPress rejected `host.docker.internal` as an invalid URL before download. | harness/network validation | Copied the unchanged archive directly into the isolated CLI container. |
| C4 | Quote-bearing WP-CLI eval probes should survive the Windows/wp-env boundary. | One filtered-count probe lost string quotes and caused a parse error. | harness quoting | Used class constants and quote-free probes; runtime catalog count passed at 15. |
| C5 | Context should succeed after changing database URL options to HTTPS. | wp-env constants overrode the options and context correctly rejected an HTTP `home_url`. | disposable environment configuration | Set `WP_HOME` and `WP_SITEURL` constants to HTTPS; the same context probe passed. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Publish `v0.1.0-rc.1` as a GitHub prerelease and match installed metadata to `0.1.0-rc.1`. | O1; owner selection | Requires a new package hash and clean-install verification. | Stable-release gates complete. |
| D2 | `DECIDED` | Make the WordPress ZIP upload path primary and keep `wp-env` under Developer Setup. | O2; owner request | Adds README length while removing first-run ambiguity. | A WordPress.org distribution channel exists. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| RC versions agree | Compare plugin header/constant, readme stable tag, package/lock, and CI value | `PASS` | all declare `0.1.0-rc.1` |
| README catalog and local links agree with source | Compare unique `AbilityMap` names; local-link/fence checks | `PASS` | 15/15 names; zero missing local links; balanced fences |
| ZIP is reproducible and production-only | Two sequential builds plus provenance scan | `PASS` | identical SHA-256; 67 entries; attribution passed |
| Exact RC clean-installs and activates | Fresh plugin list, archive install, version/tables/catalog/context probes | `PASS` | WordPress 6.9/PHP 8.0.30; RC active; 3 tables; 15 Abilities; context success |
| Published asset matches tested hash | pending | `NOT_TESTED` | pending |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-09-03-exp-043-readme-release-quickstart.md` | evidence | uncommitted | pending | Current experiment record. |
| `dist/agentpress.zip` | tested generated ZIP | ignored | `96E50234EEC7FED583EC6ABC1A01C0A39A555B948296EBEA1E06E466E453BC4D` | 134,296 bytes; 67 entries; two deterministic builds and clean install. |
| `dist/agentpress-0.1.0-rc.1.zip` | release ZIP | pending | `96E50234EEC7FED583EC6ABC1A01C0A39A555B948296EBEA1E06E466E453BC4D` expected | Versioned copy must be uploaded and independently downloaded. |

## Result

`PENDING`

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: public release availability, downloaded-asset equality, AP-028 completion, and AP-031 reliability.

## Competition evidence statement

- work attributable to challenge period: pending commit/release evidence;
- pre-existing work distinguished by: baseline and historical EXP-042 preserved;
- third-party material/license/pin: unchanged from the AP-002/AP-030 package boundary;
- commit/PR evidence: `UNCOMMITTED`;
- live URL evidence: `NOT_TESTED` for the release asset;
- real ChatGPT Site Tools evidence: not changed; AP-028 remains open;
- five-run reliability evidence: `NOT_TESTED`; AP-031 remains open;
- submission/video evidence: not changed by this experiment.

## Next experiment

- proposed experiment ID/task: AP-028 continuation, then AP-031;
- next falsifiable question: can the exact published RC complete the remaining real-client role gate and five consecutive canonical runs?;
- required prerequisites: this release artifact must be published and checksum-verified.

## End state

```text
git status --short --branch: EXP-043 uncommitted; remaining changes pending
tests/checks: pending
committed: no
pushed: no
deployed: no
```
