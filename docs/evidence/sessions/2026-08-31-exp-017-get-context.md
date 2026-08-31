# EXP-017 — AP-011 safe get-context envelope

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-017` |
| Related task | `AP-011`; GitHub issue #21 |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-08-31T23:02:55+07:00` |
| Started UTC | `2026-08-31T16:02:55Z` |
| Ended local | `2026-09-01T03:42:07+07:00` |
| Ended UTC | `2026-08-31T20:42:07Z` |
| Agent/operator | Codex, implementation and verification agent |
| Branch | `ap-011-get-context` |
| Baseline commit | `bb497b54a994fabe86618b85ed44a1af58541c0d` |
| Ending commit | `f66788a11c9836e8b5ec0f542e41b63ba56c34cd` |
| Environment | Windows; PowerShell 7; Node.js 22.23.2; npm 10.9.8; Docker 29.6.1; WordPress 6.9; PHP 8.0.30 |

## Question

Can the registered `agentpress/get-context` Ability return the exact closed safe site/user/16-operation envelope for Administrator, Editor, Author, Subscriber, and capability-mutated users while denying logged-out execution and omitting private identity, raw capabilities, session material, paths, plugin configuration, and environment details?

## Hypothesis

The merged AP-007 `SafeMode` and `DiscoveryPolicy` already derive capability-sensitive operation decisions, while AP-008 supplies the exact registered Ability contract. A narrow protocol-independent context service should be able to translate those live decisions into the fixed output schema without reading or serializing unsafe WordPress/user/server fields.

## Falsification condition

The hypothesis is false if any fixture user receives an operation state broader or narrower than current WordPress authority plus Safe Mode; capability mutation does not update the envelope; logged-out execution succeeds; the output violates the exact registered schema; or serialized output contains email, raw capability names/maps, cookies, nonces, filesystem paths, plugin settings/configuration, environment variables, credentials, or other private user metadata.

## Controls

- fixed commit/build: clean synchronized main baseline `bb497b54a994fabe86618b85ed44a1af58541c0d`;
- fixed fixture/data: synthetic default-role users plus one temporary capability mutation; no real private site content;
- fixed identity/capabilities: Administrator, Editor, Author, Subscriber, logged-out, and a capability-mutated user;
- fixed policy/configuration: merged AP-007 Safe Mode and AP-008 15-Ability catalog; no policy-profile expansion;
- fixed client/environment: unit controls plus real WordPress 6.9 wp-env integration;
- explicit scope exclusions: AP-012+ structure/content/term services, admin UI, deployment, real browser/ChatGPT acceptance, and release/reliability evidence.

## Variables

- **Independent:** current user identity, live capabilities, login state, site metadata, and deliberate secret-like sentinel values outside the permitted data sources.
- **Dependent:** registered Ability result/error, each of 16 operation states/reasons, exact output shape/schema validity, serialized sentinel absence, execution/audit count, and target mutation count.

## Preflight

```text
timestamp: local 2026-08-31T23:02:55+07:00; UTC 2026-08-31T16:02:55Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main (clean)
git log -3 --oneline --decorate:
bb497b5 (HEAD -> main, origin/main, origin/HEAD) docs: record AP-010 merge evidence
a1b3d4d AP-010: coordinate idempotent change sets (#20)
7ad2e0b (origin/ap-010-change-set-coordinator, ap-010-change-set-coordinator) docs: record AP-010 hosted gate
baseline SHA: bb497b54a994fabe86618b85ed44a1af58541c0d
current branch: main; isolated ap-011-get-context created after preflight
unrelated existing changes: none
AP task, issue, and PR: AP-011; no duplicate AP-011 issue observed; issue #21 created; PR pending
environment: Node.js 22.23.2; npm 10.9.8; Docker 29.6.1; exact baseline run 33411814921/job 99552948065 passed
```

## Method

1. Open this record/index entry and AP-011 issue on an isolated branch before product-code inspection or mutation.
2. Inspect the exact AP-007 policy decisions, AP-008 contract/callback wiring, WordPress role/capability sources, and existing test helpers.
3. Define an explicit allowlisted mapper for the 16 operation keys and five safe site/user fields; do not serialize raw WordPress capability maps or arbitrary user/site metadata.
4. Implement the smallest protocol-independent context service and connect only `agentpress/get-context` to it.
5. Unit-test exact shape, bounds, role normalization, decision mapping, capability mutation sensitivity, and unsafe-key/value absence.
6. Run a real WordPress role matrix plus logged-out/direct-execution controls through the registered Ability; validate the output schema and assert zero target mutation.
7. Run prerequisite integrations and repository-wide unit, standards, browser, provenance, dependency-audit, and deterministic package gates.
8. Commit, push, open an issue-linked PR, merge only the exact latest green head, and append merge/main evidence.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary agent owns inspection, implementation, and verification. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `DECIDED` | `docs/IMPLEMENTATION_SPEC.md` sections 4 and 6.1 at baseline `bb497b5` | 2026-08-31 | Exact authentication, safe site/user fields, 16 operation keys, state enum, blocked areas, and omissions are fixed. | Project contract, not runtime proof. |
| S2 | `OBSERVED` | `docs/BUILD_CHECKLIST.md` AP-011 at baseline `bb497b5` | 2026-08-31 | Acceptance requires correct fixture-user states, secret/private-field omission, and logged-out denial. | Executable evidence pending. |
| S3 | `OBSERVED` | EXP-013 and EXP-014 merged evidence | 2026-08-31 | Capability-aware policy and the exact registered get-context schema/callback placeholder exist with green hosted evidence. | Exact service wiring pending inspection. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-08-31T23:02:55+07:00 | Re-read required project contracts; capture clean baseline, versions, duplicate issue search, and exact main CI | repository/GitHub | exit 0 | Clean main at full SHA `bb497b54a994fabe86618b85ed44a1af58541c0d`; no AP-011 issue; run `33411814921`/job `99552948065` success. |
| timestamp not independently captured | Create isolated `ap-011-get-context` branch and issue #21 | repository/GitHub | exit 0 | [Issue #21](https://github.com/MrBigleg/AgentPress/issues/21) preserves task, dependency, deliverable, and acceptance test. |
| timestamp not independently captured | Inspect AP-007 policy/envelope, AP-008 catalog/registrar, schemas, result factory, plugin wiring, and role integration controls | repository | exit 0 | The exact 16-operation `CapabilityEnvelope` already derives live states without raw capability serialization. The production registrar still dispatches every Ability to `AP_INTERNAL_ERROR`; AP-011 needs only a narrow get-context service/default dispatch while later services remain fail-closed. |
| timestamp not independently captured | Start WordPress 6.9/PHP 8.0.30, syntax-check AP-011 files, and run first unit/standards gates | repository/wp-env | syntax/unit exit 0; lint exit 2 | All three AP-011 PHP files passed syntax. PHPUnit passed 68 tests/593 assertions. PHPCS found 15 errors and one warning in the new service, limited to compact docblocks/layout and use of generic `strip_tags`; replace with WordPress `wp_strip_all_tags`, expand documentation, and rerun. |
| timestamp not independently captured | Rerun standards and first real AP-011 WordPress matrix | repository/wp-env | standards exit 0; matrix exit 1 | All 36 PHP files passed PHPCS. Matrix stopped at Administrator registered-Ability execution before making any acceptance claim; enhance the assertion with the safe returned error/result summary and rerun. |
| timestamp not independently captured | Rerun AP-011 matrix with scoped HTTPS control | repository/wp-env | exit 0 | Four roles and all 16 operations matched; four registered outputs passed the exact schema; live capability mutation updated the envelope; seven private sentinels were absent; logged-out permission failed closed; target mutations remained zero. |
| timestamp not independently captured | Run final unit/standards and AP-004 through AP-010 WordPress regressions | repository/wp-env | exit 0 | PHPUnit 68 tests/593 assertions; PHPCS 36/36 files; all seven prerequisite integration scripts passed, including zero unauthorized mutations and AP-010 failure/idempotency controls. |
| timestamp not independently captured | Run browser, provenance, dependency-audit, script-syntax, diff, and deterministic package gates | repository | exit 0 | Browser 14/14; provenance 46 ZIP entries with pinned GPL source and no upstream runtime; npm audit zero vulnerabilities; scripts/diff clean; two builds produced SHA-256 `438C7FE4DD65BFB627013406498CA7B93C5F15A2632B36AA1577CDE5E5382BFB`. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-007, AP-008, and AP-010 are merged, main is clean, and exact baseline run `33411814921` passed. | preflight and GitHub run | AP-011 can begin on its isolated branch. |
| O2 | `OBSERVED` | No existing GitHub issue matched AP-011 before issue #21 was created. | preflight query | No issue duplication. |
| O3 | `OBSERVED` | AP-007 already owns the exact 16 operation keys, stable state/reason objects, blocked-area allowlist, and live capability mutation behavior. | `CapabilityEnvelope.php`; AP-007 integration | AP-011 should reuse this policy output rather than duplicate capability logic. |
| O4 | `OBSERVED` | `AbilityRegistrar` has exact schema and permission wiring but its default executor intentionally returns `AP_INTERNAL_ERROR` for every Ability. | registrar/catalog inspection | Add only a get-context default service dispatch; preserve fail-closed placeholders for AP-012+. |
| O5 | `OBSERVED` | Administrator, Editor, Author, and Subscriber received all 16 expected operation states through the registered Ability; a live Subscriber capability mutation immediately expanded only the relevant operation. | AP-011 integration output | The capability-sensitive envelope hypothesis is supported under real WordPress default-role controls. |
| O6 | `OBSERVED` | All four results passed the exact registered output schema; seven private/session/path/config sentinels were absent; logged-out permission failed with `AP_NOT_AUTHENTICATED`; no target mutation occurred. | AP-011 integration output | The closed output and privacy/denial hypotheses are supported under controlled fixtures. |
| O7 | `OBSERVED` | AP-004 through AP-010, unit, standards, browser, provenance, audit, and deterministic packaging all passed after AP-011. | final command outputs | Prerequisite behavior and repository gates are preserved locally. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| none | No contradiction observed before source inspection. | N/A | N/A | Open. |
| F1 | First PHP standards gate should pass. | New service used compact docblocks/layout and generic `strip_tags`; PHPCS reported 15 errors and one warning. | implementation standards defect | Preserve the failure, use the WordPress text-strip primitive, expand comments/layout, and rerun. |
| F2 | The first real role matrix should execute get-context for Administrator. | Registered Ability execution returned a non-success result, but the first assertion did not expose its safe error code. | implementation or test diagnostic failure | Preserve the stop, add bounded error/result detail to the assertion, and rerun before diagnosing. |
| F3 | Setting the wp-env `home` option to synthetic HTTPS should satisfy the production HTTPS output schema. | wp-env's configured URL takes precedence, so `home_url()` remained HTTP and WordPress rejected the registered Ability output with `ability_invalid_output`. | test control defect | Keep the production schema/service strict; use the scoped WordPress `home_url` filter for this synthetic test and remove it during cleanup. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Build only the fixed get-context service and callback wiring; do not begin AP-012+ or admin UI. | S1/S2; v0.1 scope | Keeps AP-011 independently auditable. | A required AP-011 field cannot be derived without a missing prerequisite. |
| D2 | `DECIDED` | Reuse `CapabilityEnvelope` verbatim, source user data only from ID/display name/role slugs, and source site data only from the five fixed WordPress accessors. | O3/O4; S1 | Avoids a second policy model and prevents arbitrary option/user serialization. | The fixed output contract changes. |
| D3 | `DECIDED` | Keep production HTTPS output strict; set a synthetic HTTPS home URL in wp-env integration rather than weakening the schema for local HTTP. | S1; v0.1 HTTPS minimum | Test setup must restore the original option. | Product minimum changes to allow HTTP. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Correct operation states for every fixture user | Registered Ability for Administrator/Editor/Author/Subscriber × 16 operations | `SUPPORTED` | four roles; 64 state checks |
| Capability mutation changes the live envelope | Add/remove synthetic Subscriber `edit_posts` capability | `SUPPORTED` | `create_post_draft` changed unavailable → automatic live |
| Output matches exact registered schema and omits private/secret fields | Four `SchemaValidator::validate_output` calls plus seven sentinel scans | `SUPPORTED` | four validations; seven sentinels absent |
| Logged-out execution is denied with zero target mutation | Registered permission callback at user 0 and mutation counter | `SUPPORTED` | `AP_NOT_AUTHENTICATED`; `target_mutations:0` |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-08-31-exp-017-get-context.md` | evidence | committed | `EXP-017`; `f66788a` | Opened before AP-011 product-code inspection or mutation. |
| `agentpress/includes/Context/ContextService.php` | source | committed | `f66788a` | Fixed five-field site, three-field user, policy-envelope bootstrap service. |
| `agentpress/tests/integration/ap011-get-context.php` | executable evidence | committed | `f66788a` | Real role/schema/privacy/capability/logged-out matrix. |
| `agentpress/tests/phpunit/unit/ContextServiceTest.php` | executable evidence | committed | `f66788a` | Pure mapper bounds, allowlist, and denial controls. |
| `dist/agentpress.zip` | ignored release candidate | generated | `438C7FE4DD65BFB627013406498CA7B93C5F15A2632B36AA1577CDE5E5382BFB` | Two deterministic local builds; not release/deployment evidence. |

## Result

`SUPPORTED`

Under controlled WordPress 6.9/PHP 8.0.30 fixtures, `agentpress/get-context` returned the exact registered closed schema and expected 16-operation envelope for four default roles, responded immediately to a live capability mutation, omitted all tested private/session/path/configuration sentinels, denied logged-out permission, and caused zero target mutations. This supports AP-011 locally; hosted CI and merge evidence remain pending.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: live browser/ChatGPT behavior, admin UI, deployment, release installation, and five-run reliability. The browser unit gate passed but is not live Site Tools evidence.

## Competition evidence statement

- work attributable to challenge period: baseline/timestamps captured before AP-011 product-code inspection or mutation;
- pre-existing work distinguished by: synchronized AP-010 closeout baseline;
- third-party material/license/pin: no new third-party runtime; existing GPL pin/provenance gate passed with 46 ZIP entries and no upstream runtime code;
- commit/PR evidence: implementation/evidence `f66788a11c9836e8b5ec0f542e41b63ba56c34cd`; issue #21; PR pending;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: `EXP-018` / AP-012 `get-site-structure`;
- next falsifiable question: can one bounded read service return the exact visible page hierarchy/counts/taxonomy/menu-location shape while excluding unreadable objects and full content/destinations?
- required prerequisites: merged AP-008 Ability catalog; AP-011 read-service dispatch pattern.

## End state

```text
git status --short --branch: AP-011 implementation/evidence committed on ap-011-get-context; SHA closeout pending; no unrelated changes observed
tests/checks: AP-011 WordPress matrix pass; AP-004–AP-010 regressions pass; unit 68/593; PHPCS 36/36; browser 14/14; provenance 46 entries; npm audit 0; deterministic ZIP SHA-256 438C7FE4DD65BFB627013406498CA7B93C5F15A2632B36AA1577CDE5E5382BFB
committed: yes, implementation/evidence f66788a11c9836e8b5ec0f542e41b63ba56c34cd
pushed: no
deployed: no
```
