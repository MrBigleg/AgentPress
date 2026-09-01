# EXP-019 — AP-013 bounded content reads

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-019` |
| Related task | `AP-013`; GitHub issue #25 |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-09-01T10:44:44+07:00` |
| Started UTC | `2026-09-01T03:44:44Z` |
| Ended local | `2026-09-01T11:10:46+07:00` |
| Ended UTC | `2026-09-01T04:10:46Z` |
| Agent/operator | Codex, implementation and verification agent |
| Branch | `ap-013-content-reads` |
| Baseline commit | `926d08bbb7a5d84a4bc10639b8b00f7af679eac5` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows; PowerShell 7; Node.js 22.23.2; npm 11.7.0; Docker 29.6.1; WordPress 6.9; PHP 8.0.30 |

## Question

Can `agentpress/list-content` and `agentpress/get-content` provide bounded deterministic post/page discovery and object-specific retrieval while enforcing live `read_post` authority for every object and returning zero unreadable or unsupported content?

## Hypothesis

The merged closed schemas, capability-aware discovery policy, and AP-011/AP-012 read-service boundary make two narrow services sufficient: stable post/page ID queries plus per-object `read_post` checks can satisfy pagination and direct-ID retrieval without exposing an unreadable draft or permitting mutation.

## Falsification condition

The hypothesis is false if ordering or pagination changes for a fixed fixture; any listed object fails `read_post`; Author can list or directly fetch another user's unreadable draft; an unsupported post type or invalid/oversized request succeeds; a response violates its registered schema or bound; a private sentinel crosses the role boundary; or either read causes any target mutation.

## Controls

- fixed build: synchronized green baseline `926d08bbb7a5d84a4bc10639b8b00f7af679eac5`;
- fixture: synthetic post/page objects with fixed authors, statuses, timestamps, terms, and private sentinels;
- identities: Administrator, Author, Subscriber, logged out; explicit ownership/read controls;
- policy: merged AP-007/AP-008 exact Ability contracts and live execution checks;
- environment: controlled WordPress 6.9/PHP 8.0.30 wp-env plus repository unit controls;
- exclusions: mutation/draft creation, term mutation, UI, deployment, ChatGPT, release installation, and reliability.

## Variables

- **Independent:** operation, role/user, object owner/status/type, filters, page/per-page/order inputs, direct ID, and invalid/oversized input class.
- **Dependent:** returned IDs/order/pagination/object fields/terms, schema result, denial/error code, sentinel absence, and target mutation count.

## Preflight

```text
timestamp: local 2026-09-01T10:44:44+07:00; UTC 2026-09-01T03:44:44Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main (clean)
git log -3 --oneline --decorate:
926d08b (HEAD -> main, origin/main, origin/HEAD) docs: record AP-012 merge evidence
146dc42 Merge pull request #24 from MrBigleg/ap-012-site-structure
c0dfc1f docs: record AP-012 implementation evidence
baseline SHA: 926d08bbb7a5d84a4bc10639b8b00f7af679eac5
current branch: main; isolated ap-013-content-reads created after preflight
unrelated changes: none
AP task/issue/PR: AP-013; exact-title duplicate search empty; issue #25; PR pending
environment: Node.js 22.23.2; npm 11.7.0; Docker 29.6.1; baseline run 33467250475/job 99729567363 success
```

## Method

1. Open EXP-019/index and issue #25 on an isolated branch before product-code inspection or mutation.
2. Inspect the exact AP-008 schemas, policy checks, existing read services, and fixture conventions.
3. Implement two protocol-independent services using closed field allowlists, stable ordering, bounded pagination, post/page allowlisting, and per-object `read_post` checks.
4. Wire only `list-content` and `get-content`; keep AP-014+ unimplemented operations fail-closed.
5. Run a real role/object/filter/pagination/direct-ID/schema/privacy/invalid-input matrix with zero target mutation.
6. Run AP-004 through AP-012 regressions and all repository/package gates.
7. Commit, push, open a PR, verify the exact latest green head, merge, and append merge/main evidence.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary agent owns implementation and verification. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `OBSERVED` | Build checklist AP-013 at baseline `926d08b` | 2026-09-01 | Deterministic filters/pagination, per-object reads, direct-ID denial, type and size bounds are acceptance requirements. | Runtime pending. |
| S2 | `DECIDED` | Implementation specification at baseline `926d08b` | 2026-09-01 | Fixed input/output contracts and error taxonomy remain binding. | Exact source inspection follows record creation. |
| S3 | `OBSERVED` | EXP-014, EXP-017, and EXP-018 merged evidence | 2026-09-01 | Closed schemas, narrow dispatch, live role filtering, and hosted gates exist. | AP-013 behavior pending. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-09-01T10:44:44+07:00 | Capture synchronized baseline, versions, exact-title issue search, and final AP-012 closeout CI | repository/GitHub | exit 0 | Clean full SHA; no AP-013 title; run/job above green. |
| timestamp not independently captured | Create issue #25, branch, EXP-019, and index entry | repository/GitHub | exit 0 | Material session isolated and recorded before product-code inspection/mutation. |
| timestamp not independently captured | Inspect exact AP-013 schemas, execution policy, dispatcher, and prior read-service conventions | repository | exit 0 | Fixed list fields/defaults/bounds and get fields/50,000-character cap confirmed; policy performs live object check for get-content, while the service must recheck every listed object. |
| timestamp not independently captured | Implement ContentReadService, narrow dispatch, package manifest, and AP-013 integration matrix | repository | uncommitted | Stable tie-broken ID scans calculate visible pagination after per-object authority checks; get-content allowlists type/status and projects bounded raw fields plus fixed terms. Runtime pending. |
| timestamp not independently captured | Start/activate WordPress 6.9 and syntax-check service/matrix | repository/wp-env | exit 0 | Environment started; plugin already active; both PHP files parsed. |
| 2026-09-01T10:54:18+07:00 | Run first AP-013 WordPress matrix | repository/wp-env | exit 1 | Valid list/get checks reached unsupported direct-type control. WordPress Ability permission rejection produced a `_doing_it_wrong` notice/null execute result, so the assertion incorrectly expected the policy WP_Error from `execute`. Fixture was left by the intentional fail-fast test. |
| 2026-09-01T10:56:04+07:00 | Run second AP-013 WordPress matrix after harness correction | repository/wp-env | exit 1 | Syntax passed; Administrator filters, pagination, schemas, terms, truncation, and denials passed. Author list returned a WP_Error, but the harness indexed it before reporting its code, causing a fatal at line 127. Add explicit result assertions to expose the real failure. |
| timestamp not independently captured | Run third AP-013 matrix and bounded diagnostic rerun | repository/wp-env | both exit 1 | Explicit assertion identified `ability_invalid_output`. Direct bounded output showed the Author's readable draft had WordPress sentinel `post_modified_gmt=0000-00-00 00:00:00`, which formatted as invalid `-0001-11-30T00:00:00Z`; all visibility fields were otherwise correct. |
| 2026-09-01T10:58:27+07:00 | Run AP-013 matrix after GMT normalization | repository/wp-env | exit 1 | Syntax and Author list schema passed. The next negative direct-ID control repeated the F1 harness mistake: core emitted a permission notice/null execute result, so the assertion did not receive `AP_PERMISSION_DENIED`. |
| timestamp not independently captured | Run corrected AP-013 matrix, PHPUnit, and first PHP standards gate | repository/wp-env | matrix/unit exit 0; lint exit 1 | Matrix passed the complete role/filter/pagination/schema/denial/truncation/privacy/zero-mutation controls. PHPUnit passed 68/593. PHPCS found 14 doc-comment errors and one justified fixed-taxonomy-query warning in the new service; correct comments and add a narrow sniff justification before rerun. |
| timestamp not independently captured | Rerun PHP standards | repository/wp-env | exit 0 | All 38 PHP files passed after comment correction and the scoped contract-required taxonomy-query justification. |
| timestamp not independently captured | Run AP-004 through AP-012 real WordPress regressions | repository/wp-env | exit 0 | All nine prior matrices passed, including transport forbidden controls, zero unauthorized mutations, Change Set invariants, context privacy, and site-structure visibility. |
| timestamp not independently captured | Run browser, provenance, audit, Node syntax, whitespace, and deterministic-package gates | repository | exit 0 | Browser 14/14; provenance 48 entries/no upstream runtime; audit zero vulnerabilities; Node scripts parse; `git diff --check` clean; two ZIPs matched SHA-256 `376BCED942E2BCA05890D29818CAC0D3C040CC2E6F87BDAAC21E8F7EB9AB8DC4`. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-012 closeout commit is synchronized on main and its complete hosted workflow passed. | preflight | AP-013 dependency baseline is green. |
| O2 | `OBSERVED` | Exact-title issue search returned no AP-013 issue before #25 was created. | GitHub query | Issue #25 is not a duplicate. |
| O3 | `OBSERVED` | The fixed list schema permits only post/page, four statuses or any, three ordering keys, 100 items, category/tag filters, and summaries without full content. | implementation spec/catalog | A closed query builder can implement the contract directly. |
| O4 | `OBSERVED` | The fixed get schema requires raw editable content capped at 50,000 characters, a truncation flag, and category/tag assignments; policy already resolves the object and checks `read_post`. | implementation spec/catalog/policy | Service still rechecks object type/status/authority at execution for defense in depth. |
| O5 | `OBSERVED` | The final real WordPress matrix passed three roles, two deterministic pages, four filters, three output schema validations, two direct-ID denials, three unsupported/oversized denials, the exact 50,000-character cap, two absent private sentinels, anonymous denial, and zero target mutation. | AP-013 integration matrix | Supports the AP-013 hypothesis in the controlled fixture. |
| O6 | `OBSERVED` | All nine prior runtime matrices and all repository/package gates passed against the AP-013 worktree. | execution log | No observed regression in the covered boundaries. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| none | No contradiction before source inspection. | N/A | N/A | Open. |
| F1 | Unsupported direct-ID type control should assert a stable policy error through Ability execution. | WordPress Ability emits a developer notice and does not return the permission WP_Error from `execute` when permission fails. | test-harness defect | Assert `check_permissions` for the policy error; test the service allowlist directly; add stale-fixture cleanup before rerun. Product behavior remains fail-closed. |
| F2 | Corrected matrix should report any role-level service error cleanly. | Author list returned WP_Error and the harness attempted array access, masking its code with a fatal. | test-harness defect with underlying result pending | Assert array/error type before indexing Author and Subscriber results, then rerun to classify the underlying error. |
| F3 | Every supported draft should yield a schema-valid `modified_gmt`. | WordPress left an unpublished draft's GMT field at its zero-date sentinel, producing an invalid negative-year RFC3339 string. | implementation normalization defect | When GMT is empty/zero-date, convert the populated local `post_modified` through WordPress `get_gmt_from_date`; rerun full matrix. |
| F4 | Negative direct-ID role assertions should use the previously corrected permission surface. | Author denial still called `execute`, reproducing core's notice/null behavior. | test-harness defect | Use `check_permissions` for both Author-other-draft and Subscriber-private stable error assertions; valid execution remains tested separately. |
| F5 | First PHP standards gate should pass after runtime behavior is green. | New helper one-line docblocks violated repository comment rules; fixed taxonomy filtering triggered the generic slow-query warning. | implementation standards defect | Expand helper documentation and narrowly justify the contract-required bounded `tax_query`; rerun. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Implement only post/page reads and the exact registered fields; no mutation or unsupported types. | S1/S2 | AP-014/AP-015 remain separate. | Fixed contract changes. |
| D2 | `DECIDED` | Scan stable candidate-ID batches and apply `read_post` before visible offset/count calculation. | O3/S1 | Exact permission-filtered totals require scanning all matching candidates, but every query and returned page remains bounded. | AP-035 representative timing/query budgets fail. |
| D3 | `DECIDED` | Use the requested primary order plus ID in the same direction as a deterministic tie-breaker. | O3 | Stable pages remain reproducible when dates/titles tie. | Contract specifies a different tie-break rule. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Deterministic bounded filters and pagination | two exact title-ordered pages plus type/search/author/taxonomy filters | `PASS` | four published posts yielded exact two-page IDs/totals; four filters matched fixtures. |
| Per-object list and direct-ID authority | Administrator/Author/Subscriber plus own/other/private direct IDs | `PASS` | Author own draft allowed; other draft denied; Subscriber private denied; visible totals matched. |
| Exact schemas and supported types/bounds | registered output validation, unsupported custom/attachment controls, per_page=101, long content | `PASS` | three output validations; three stable denials; exactly 50,000 characters plus truncation true. |
| Private sentinels absent and zero mutation | serialized Subscriber scan and before/after target snapshots | `PASS` | two sentinels absent; zero changed target fields. |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-09-01-exp-019-content-reads.md` | evidence | uncommitted | EXP-019 | Opened before product-code inspection/mutation. |
| `agentpress/includes/Content/ContentReadService.php` | implementation | uncommitted | AP-013 | Exact list/get service with stable visible pagination and bounded projections. |
| `agentpress/tests/integration/ap013-content-reads.php` | executable evidence | uncommitted | AP-013 matrix | Synthetic role/object/filter/schema/privacy/denial/mutation controls. |
| `dist/agentpress.zip` | generated package | excluded | SHA-256 `376BCED942E2BCA05890D29818CAC0D3C040CC2E6F87BDAAC21E8F7EB9AB8DC4` | Two consecutive builds matched; 48 entries. |

## Result

`SUPPORTED`

`OBSERVED`: the controlled real-WordPress evidence supports the hypothesis. Permission-filtered totals/pages remained deterministic; only readable post/page objects crossed list/direct-ID boundaries; fixed summaries, raw bounded content, and visible terms validated against the exact schemas; unsupported and oversized controls failed closed; reads changed no target fixture state. Prior runtime and repository gates remained green.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: representative large-production-site timing/query budgets, wp-admin UI, deployment, real ChatGPT Site Tools, release installation, and five-run reliability.
- Local browser-contract tests passed but are not live Chrome/WebMCP or ChatGPT evidence.

## Competition evidence statement

- work attributable to challenge period: pre-mutation baseline/timestamps recorded;
- pre-existing work distinguished by: synchronized AP-012 closeout baseline;
- third-party material/license/pin: `NOT_APPLICABLE` pending inspection;
- commit/PR evidence: `UNCOMMITTED`; issue #25; PR pending at experiment conclusion;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: `AP-014 — Implement list-terms`;
- next falsifiable question: can a fixed category/tag reader return deterministic visible search/pagination results while rejecting custom taxonomies and causing zero mutation?;
- required prerequisites: merged AP-013 exact-head and merge-head hosted gates.

## End state

```text
git status --short --branch: AP-013 implementation, integration evidence, EXP-019, index, dispatcher, and build manifest uncommitted on ap-013-content-reads
tests/checks: AP-013 matrix pass; AP-004–AP-012 regressions pass; PHPUnit 68/593; PHPCS 38 files; browser 14/14; provenance 48 entries; audit 0; deterministic ZIP pass
committed: no
pushed: no
deployed: no
```
