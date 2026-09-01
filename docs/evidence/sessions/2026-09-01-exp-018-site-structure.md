# EXP-018 — AP-012 bounded visible site structure

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-018` |
| Related task | `AP-012`; GitHub issue #23 |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-09-01T03:53:36+07:00` |
| Started UTC | `2026-08-31T20:53:36Z` |
| Ended local | `2026-09-01T04:14:12+07:00` |
| Ended UTC | `2026-08-31T21:14:12Z` |
| Agent/operator | Codex, implementation and verification agent |
| Branch | `ap-012-site-structure` |
| Baseline commit | `e6ec207df6ca5156de83b5714e4abbdbe1746dff` |
| Ending commit | `dca847fd47b6792f6522818ff861cffb85d88743` |
| Environment | Windows; PowerShell 7; Node.js 22.23.2; npm 10.9.8; Docker 29.6.1; WordPress 6.9; PHP 8.0.30 |

## Question

Can `agentpress/get-site-structure` return the exact bounded page hierarchy, post/page counts, category/tag definitions, and registered classic-menu locations visible to the current user while excluding unreadable objects, full content, and menu destinations?

## Hypothesis

The AP-008 schema and AP-011 narrow read-service dispatch pattern provide the fixed boundary. WordPress post queries plus per-object `read_post` checks, allowlisted taxonomies, and registered navigation locations should produce a deterministic snapshot capped at 200 visible pages without exposing bodies or menu items.

## Falsification condition

The hypothesis is false if a restricted user receives a private/unreadable page; hierarchy or counts do not match the controlled fixture definition; more than 200 pages are returned; truncation is false when eligible visible results exceed the cap; taxonomy or location output exceeds the allowlist/registered state; any full body/menu destination/private sentinel appears; logged-out execution succeeds; or output violates the registered schema.

## Controls

- fixed build: clean synchronized baseline `e6ec207df6ca5156de83b5714e4abbdbe1746dff`;
- fixture: synthetic public/private/draft hierarchical pages, posts, fixed category/tag registration, and synthetic menu-location assignment;
- identities: Administrator, Author, Subscriber, logged out; explicit ownership/read controls;
- policy: merged AP-007/AP-008; exact 200-page cap; category/post_tag only;
- environment: real WordPress 6.9/PHP 8.0.30 wp-env plus unit controls;
- exclusions: full content reads, navigation items/destinations, AP-013+ services, UI, deployment, ChatGPT, and release/reliability.

## Variables

- **Independent:** role/user, page status/ownership/parent, number of eligible pages, taxonomy visibility, and menu assignment.
- **Dependent:** returned IDs/hierarchy/counts/taxonomies/locations/truncated flag, schema result, private sentinel absence, denial, and mutation count.

## Preflight

```text
timestamp: local 2026-09-01T03:53:36+07:00; UTC 2026-08-31T20:53:36Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main (clean)
git log -3 --oneline --decorate:
e6ec207 (HEAD -> main, origin/main, origin/HEAD) docs: record AP-011 merge evidence
1c0761e AP-011: implement safe get-context envelope (#22)
44f45eb (origin/ap-011-get-context, ap-011-get-context) docs: record AP-011 hosted gate
baseline SHA: e6ec207df6ca5156de83b5714e4abbdbe1746dff
current branch: main; isolated ap-012-site-structure created after preflight
unrelated changes: none
AP task/issue/PR: AP-012; exact-title duplicate search empty; issue #23; PR pending
environment: Node.js 22.23.2; npm 10.9.8; Docker 29.6.1; baseline run 33438395810/job 99640333585 success
```

## Method

1. Open EXP-018/index and issue #23 on an isolated branch before product-code inspection/mutation.
2. Inspect AP-008 schema, AP-011 dispatcher, WordPress query/visibility semantics, and existing fixture/test conventions.
3. Implement a protocol-independent service with explicit field allowlists, per-object read checks, stable order, and a hard 200-page cap plus overflow probe.
4. Wire only get-site-structure; keep AP-013+ fail-closed.
5. Run unit bounds/shape controls and a real role/object/hierarchy/count/taxonomy/location/schema/privacy matrix, including logged-out and >200-page truncation.
6. Run AP-004–AP-011 regressions and repository-wide gates.
7. Commit, push, open PR, verify exact latest green head, merge, and append merge/main evidence.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary agent owns implementation and verification. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `DECIDED` | Implementation spec 6.2 at baseline `e6ec207` | 2026-09-01 | Exact shape, 200-page cap, object filtering, allowed taxonomies, and menu-location fields are fixed. | Project contract. |
| S2 | `OBSERVED` | Build checklist AP-012 at baseline `e6ec207` | 2026-09-01 | Acceptance requires unreadable exclusion, fixture hierarchy/counts, cap/truncation, and untrusted annotation. | Runtime pending. |
| S3 | `OBSERVED` | EXP-014 and EXP-017 merged evidence | 2026-09-01 | Exact schema/annotation and narrow read-service dispatch pattern exist with green hosted evidence. | Service compatibility pending. |

## Execution log

| Time | Command/action | Target | Result | Evidence |
|---|---|---|---|---|
| 2026-09-01T03:53:36+07:00 | Capture baseline, exact-title duplicate issue search, versions, and final AP-011 main CI | repository/GitHub | exit 0 | Clean full SHA; no AP-012 title; run/job above green. |
| timestamp not independently captured | Create branch and issue #23 | repository/GitHub | exit 0 | Isolated task and issue created. |
| timestamp not independently captured | Inspect exact AP-008 schema, AP-011 dispatcher pattern, and WordPress query/taxonomy/menu APIs | repository | exit 0 | AP-012 can remain one service: paged ID queries plus per-object `read_post`, fixed taxonomy names, registered location assignments only, and no content/menu-item reads. |
| timestamp not independently captured | Start WordPress, syntax-check AP-012 files, and run first unit/standards gates | repository/wp-env | syntax/unit exit 0; lint exit 2 | Service/integration syntax passed; PHPUnit 68/593 passed. PHPCS found two mechanical errors and one warning: alignment, size functions in loop conditions, and extra EOF newline. Preserve and fix before runtime claims. |
| timestamp not independently captured | Run first AP-012 WordPress matrix and second standards gate | repository/wp-env | matrix exit 0; lint exit 2 | Matrix passed three roles, 200-page cap, 202 public pages, private/draft filtering, counts, hierarchy, two taxonomies, one location, three schema validations, four absent content/destination sentinels, logged-out denial, and zero target mutations. PHPCS retained one alignment warning and one Yoda-condition error; fix and rerun. |
| timestamp not independently captured | Run third and final PHP standards gates | repository/wp-env | first exit 2; final exit 0 | Third run exposed one remaining assignment-alignment warning. After the mechanical correction, all 37 files passed. A sandboxed attempt also failed before lint on Docker API/config permissions; the authorized rerun reached the container. |
| timestamp not independently captured | Run AP-004 through AP-011 WordPress regressions | repository/wp-env | exit 0 | All eight prior runtime matrices passed, including AP-010 one R1 mutation/zero R2/storage/claim mutations and AP-011 four roles/16 operations/seven absent private sentinels/zero target mutations. |
| timestamp not independently captured | Run final unit, browser, syntax, whitespace, provenance, audit, and deterministic-package gates | repository | exit 0 after one environment rerun | PHPUnit 68/593; browser 14/14; both Node scripts parse; `git diff --check` clean; provenance 47 entries; audit zero vulnerabilities; two ZIP builds matched SHA-256 `2E14A5C689332B654CF21C40235145FE16599A7FBD909F57D605BA90BCEB497D`. Initial sandboxed provenance run could not unlink the prior ZIP (`EPERM`); authorized rerun passed. |
| timestamp not independently captured | Inspect staged manifest and cached whitespace | repository | manifest correct; first cached check exit 2 | The intended eight-file package was staged. Cached check found one extra blank line at the integration fixture EOF; remove, restage, and rerun before commit. |
| 2026-09-01T10:37:12+07:00 | Verify exact PR #24 head | GitHub Actions | success | Head `c0dfc1fe547e0681b7d993c51ac60ebdc4935b10`; run `33466836478`; job `99728337930`. |
| 2026-09-01T10:39:44+07:00 | Merge PR #24 and verify merge commit | GitHub/main | success | Merge `146dc425ed9ba28656e1950b40a84b4b7765a0e0`; run `33466988437`; job `99728789828`; issue #23 closed after the green merge gate. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-008/AP-011 are merged and baseline main is clean/green. | preflight | AP-012 may start. |
| O2 | `OBSERVED` | Broad issue search matched AP-006 only because its body mentioned AP-012; exact-title search was empty. | GitHub queries | Issue #23 is not a duplicate. |
| O3 | `OBSERVED` | The registered schema exposes page summaries, two counts, taxonomy definitions, location assignment metadata, and truncation only; it has no body or menu-item/destination field. | Ability catalog | Service should construct that closed shape directly. |
| O4 | `OBSERVED` | Administrator and Author each saw 204 eligible pages and two posts; Subscriber saw 202 pages and one post. Every role received only 200 page summaries with `truncated=true`. | AP-012 WordPress matrix | Visibility filtering, exact counts, and the hard materialization cap held in the controlled fixture. |
| O5 | `OBSERVED` | The three role outputs validated against the registered schema; category/post_tag and the registered unassigned menu location matched the fixture; four content/destination sentinels were absent; logged out was denied; target mutations remained zero. | AP-012 WordPress matrix | The closed structural envelope met its security and shape controls. |
| O6 | `OBSERVED` | Final local repository gates passed and two generated ZIPs had the same SHA-256. | execution log | The uncommitted implementation is locally releasable; hosted commit/PR evidence remains pending. |
| O7 | `OBSERVED` | Exact PR head and resulting main merge commit each passed the complete hosted repository workflow. | runs `33466836478`, `33466988437` | AP-012 is merged with reproducible hosted evidence; issue #23 is closed. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| none | No contradiction before source inspection. | N/A | N/A | Open. |
| F1 | First PHP standards gate should pass. | New service had alignment, `count()` loop-condition, and extra EOF-line findings. | implementation standards defect | Cache batch/sample counts outside loop conditions, correct layout, and rerun. |
| F2 | Second PHP standards gate should be clean after the first mechanical fixes. | One new-query assignment remained unaligned and the cached batch comparison was not Yoda style. | implementation standards defect | Correct both mechanically and rerun; runtime matrix result is independent and preserved. |
| F3 | A corrected standards rerun should reach the wp-env container. | The sandboxed process could not read Docker config/connect to the Docker named pipe. | environment permissions | Authorized rerun reached the same running environment; one final alignment warning was then corrected and the next run passed. |
| F4 | Provenance verification should rebuild the ZIP. | The sandboxed process received `EPERM` unlinking `dist/agentpress.zip`. | environment permissions | Authorized rerun replaced the generated artifact and passed all provenance assertions. |
| F5 | The first staged package should pass cached whitespace validation. | The integration fixture had one extra blank line at EOF. | packaging hygiene | Remove the blank line, restage the fixture/evidence correction, and require a clean cached check before commit. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Implement only the fixed structural summary, never full bodies or menu items. | S1/S2 | AP-013/AP-021 remain separate. | Fixed contract changes. |
| D2 | `DECIDED` | Page/count queries use stable paged ID batches and recheck `read_post` for every counted/returned object; only the first 200 visible pages are materialized. | S1/O3 | Exact counts require scanning all candidate IDs, but each query/result allocation stays bounded. | Representative large-site performance fails AP-035 budgets. |
| D3 | `DECIDED` | Parent IDs collapse to zero when the parent is not in the returned visible page set. | unreadable-object non-disclosure | Truncated hierarchies may show an otherwise readable root at top level. | Contract adds an explicit omitted-parent indicator. |

## Verification matrix

| Acceptance | Check | Outcome | Evidence |
|---|---|---|---|
| Unreadable/private objects excluded | Administrator/Author/Subscriber role matrix with private/draft ownership controls | pass | Subscriber saw 202 public pages/one public post, not the two private/draft pages or private post. |
| Hierarchy/counts/taxonomies/locations match fixture | exact controlled assertions | pass | Admin/Author counts 204 pages/two posts; Subscriber 202/one; parent-child link, two taxonomy definitions, and one unassigned registered location matched. |
| Cap/truncation and exact schema hold | >200 visible pages plus registered output validation | pass | 200 summaries and `truncated=true`; three schema validations. |
| No bodies/destinations/private sentinels; logged out denied | serialized sentinel scan and anonymous execution | pass | four sentinels absent; `AP_NOT_AUTHENTICATED`; zero target mutations. |

## Artifact inventory

| Artifact | Type | State | Identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-09-01-exp-018-site-structure.md` | evidence | uncommitted | EXP-018 | Opened before source inspection/mutation. |
| `agentpress/includes/Context/SiteStructureService.php` | implementation | committed | `dca847f` | Bounded capability-filtered structural reader. |
| `agentpress/tests/integration/ap012-site-structure.php` | executable evidence | committed | `dca847f` | Synthetic roles, hierarchy, visibility, cap, schema, sentinel, denial, and mutation controls. |
| `dist/agentpress.zip` | generated package | uncommitted/excluded | SHA-256 `2E14A5C689332B654CF21C40235145FE16599A7FBD909F57D605BA90BCEB497D` | Two consecutive builds matched; 47 entries. |
| PR #24 | hosted review | merged | head `c0dfc1f`; merge `146dc42` | Exact head and merge-head workflows succeeded. |

## Result

`SUPPORTED`

`OBSERVED`: the controlled real-WordPress matrix supports the hypothesis. The service returned the exact capability-sensitive structural counts and bounded hierarchy, fixed taxonomies, and registered menu-location summary without content/destination sentinels or target mutations; anonymous execution was denied. All prior runtime regressions and local repository/package gates passed.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: representative large-production-site timing, wp-admin UI, deployment, real ChatGPT Site Tools, release installation, and five-run reliability.
- Local browser-contract tests passed, but they are not live Chrome/WebMCP or ChatGPT evidence.

## Competition evidence statement

- work attributable: pre-mutation baseline/timestamps recorded;
- pre-existing distinguished by: synchronized AP-011 closeout baseline;
- third-party: `NOT_APPLICABLE` pending inspection;
- commit/PR: implementation `dca847fd47b6792f6522818ff861cffb85d88743`; PR #24 merged as `146dc425ed9ba28656e1950b40a84b4b7765a0e0`; issue #23 closed;
- live URL: `NOT_TESTED`;
- ChatGPT: `NOT_TESTED`;
- five-run: `NOT_TESTED`;
- submission/video: `NOT_TESTED`.

## Next experiment

- proposed: `AP-013 — Implement list-content and get-content`;
- question: can bounded deterministic discovery and object-specific retrieval enforce `read_post` on every returned/requested object while excluding unsupported types and oversized results?;
- prerequisites: merged AP-012 exact-head and merge-head hosted gates.

## End state

```text
git status: clean synchronized main at AP-012 merge before this closeout append
tests: AP-012 matrix pass; AP-004–AP-011 regressions pass; PHPUnit 68/593; PHPCS 37 files; browser 14/14; provenance 47 entries; audit 0; deterministic ZIP pass
committed: implementation/evidence `dca847fd47b6792f6522818ff861cffb85d88743`
pushed: PR #24 head and merge commit verified on GitHub
deployed: no
```
