# EXP-029 — Service page draft through live Site Tools

## Metadata and preflight

- Related task: AP-028 (partial); status: `COMPLETE`; result: `SUPPORTED` for this bounded draft workflow.
- Started: 2026-09-02T13:16:00+07:00 / 2026-09-02T06:16:00Z.
- Ended: 2026-09-02T13:24:00+07:00 / 2026-09-02T06:24:00Z. Start/end are session-minute markers; exact creation timing is below.
- Client: Codex in-app browser; WordPress 7.1; Administrator.
- App/model version, PHP version, installed plugin build/checksum: `NOT_TESTED`.
- Opening repository HEAD: `6081e3c`; current pre-write HEAD: `2e948563446e57328ce1dc80b54855cf6336748c`, branch `main`.
- Existing README/runbook/index/EXP-028 work was committed by another session during preflight; preserved. No source changes planned. Ending evidence commit: `6fee48d84ab810c82277d55245d96a942da3a3e7`.

## Question, hypothesis, and falsification

Can the signed-in client create exactly one useful service **page draft** through AgentPress and read it back while preserving existing content?

Hypothesis: the automatic create-page capability supports one forced draft and returns a Change Set reference. Falsified by failed creation/read-back, duplicate creation, publication, or changed existing page metadata.

## Controls and variables

- Fixed live HTTPS session and Administrator authority; no role, plugin, configuration, or navigation changes.
- Baseline: 0 posts and 15 pages. Existing page metadata captured in memory through `agentpress_list_content` for comparison, not persisted as raw customer data.
- Independent variable: one new homebuyer survey page payload.
- Dependent variables: tool result/request IDs, saved draft fields, page count, existing-page metadata equality, and visible Draft state.
- User explicitly authorizes a real service page draft, overriding the runbook's synthetic-post fixture. Category assignment is inapplicable to pages and omitted. Author denial, approvals, and publishing are excluded.

## Method and sources

1. Read the runbook and repository instructions; use only implemented AgentPress services.
2. Call context, structure, content reads, and list-content before creation.
3. Read public homepage service/area FAQs because existing About and Drain Unblocking raw content fields are empty. UI inspection is used for context/screenshots only; all content mutation uses Site Tools.
4. Create one page with key `agentpress-demo-20260902-homebuyer-page-v1`; never retry with a new key.
5. Read back the returned ID; compare the complete existing page metadata list before/after; verify ordinary WordPress Draft UI without saving or editing.
6. Save identity-cropped screenshots locally for private verification and retain only sanitized outcomes in the public repository. No raw customer responses, account identity, domain, credentials, customer-derived screenshots, or private content are committed.

`OBSERVED`: public homepage describes homebuyer/homeowner CCTV surveys, foul/storm drainage inspections, a written report with repairs/remedial options and a cost estimate, engineers across southern England, and a Poole business address. New copy will use those facts. Ranking performance, keyword demand, and SEO plugin fields are `NOT_TESTED`.

## Execution and observations

| Action | Result | Request ID |
|---|---|---|
| get_context | `OBSERVED` Administrator; page drafts automatic | `eb4a7775-ce5b-4d23-865b-5fe630e73f41` |
| get_structure | `OBSERVED` 0 posts / 15 pages | `82dc175d-42ac-4c9b-9e56-fb0d2b884525` |
| get_content, About | `OBSERVED` empty raw content | `d62bea9f-8d64-4a88-ae64-5cec0149e005` |
| get_content, Drain Unblocking | `OBSERVED` empty raw content | `3b1bfe0e-dfd1-4410-b362-7923b95edc10` |
| list_content baseline | `OBSERVED` 15 pages; proposed title/slug absent | `073be2e3-219d-44c9-b1bc-f4eeb2203ea1` |
| create_draft | `OBSERVED` APPLIED; draft 97; Change Set AP-1; change 1; replayed=false | `11a4ba29-440c-47fd-a98f-6b871112ec47` |
| get_content verification | `OBSERVED` draft; exact title/slug/body/excerpt match | `079bb0c9-eb79-41d8-a173-1fc52dbeba3c` |
| list_content verification | `OBSERVED` 16 pages; sole new ID 97; all 15 prior metadata records equal | `5689c309-348b-44e4-a67a-87280c8051c7` |

Creation ran 2026-09-02T06:20:47.798Z–06:20:54.337Z (6.54 seconds client elapsed, not server processing time). New page: **Homebuyer Drain Surveys in Poole**, slug `homebuyer-drain-surveys-poole`, 463 words. Descriptive title/slug, H2/H3 structure, four FAQs, related-service links and quote CTAs are stored. The excerpt is a suggested search description; no Yoast metadata/focus keyphrase was set.

`OBSERVED`: ordinary WordPress Pages search shows the new item marked Draft, All (16), Published (14), Drafts (2). Its authenticated preview renders the stored headings and body. No editor save, publish, update-content, term, navigation, approval, change-set-read, or activity-read operation was invoked.

## Verification matrix

| Check | Outcome |
|---|---|
| One create call, one new ID, status draft | `PASS` |
| Saved body/excerpt exactly match submitted content | `PASS` |
| All existing page list fields, including modification times and statuses, match | `PASS` |
| Normal WordPress draft label and rendered preview | `PASS` |
| Screenshot identity crop and local artifact hashes | `PASS` |
| Full database/body equality for every existing object | `NOT_TESTED` |

## Failures and boundaries

Existing page raw content was empty despite rendered public content; no content was reconstructed from a presumed builder implementation. Public FAQ inspection supplied the required facts. Screenshot clip offsets did not yield the intended crop; the captures were cropped locally, checked visually, and replaced before final storage. A local image-library import failed; native image cropping succeeded. No image content was generated or altered beyond rectangular crops. Temporary full captures were removed.

The original user tab changed between the dashboard and preview during capture; no additional write occurred and no stale tool handle was reused afterward. Temporary agent-created tabs were closed. Current user navigation was preserved.

The first commit request included the three cropped screenshots. It was rejected before staging because two crops still contain a customer-specific service-page title or derived marketing copy. No screenshot was committed. `DECIDED`: keep all three images local-only for private video editing unless the project owner later supplies explicit authorization to republish that customer-derived material in the public repository.

## Artifacts

- Local-only AgentPress capability crop, SHA-256 `C10C140BBACFB483AD6E1C633C24639AA9A9064BA632D2D32E731252D3A7B9C6`.
- Local-only WordPress draft-status crop, SHA-256 `0B1C9BBD0F6D049DD355ABBC1308BB68B17ED6E9BDB12C93481623296E2C33FB`.
- Local-only rendered-draft excerpt crop, SHA-256 `6B3118483E170FFCA853C2826E458F74AABBC13405F83F55F797E6A5A984E6CC`.
- Captures made 06:19–06:23 UTC; final files visually checked after cropping. No domain, business name, or account identity retained, but customer-derived title/copy remains; local evidence only, not committed or publicly distributed.
- Committed-change scope: this record, evidence index, README status row, and runbook checkpoint. No screenshots, runtime source, or deployment changes.

## Result and limits

`SUPPORTED`: the live client created exactly one service page draft and read it back correctly through AgentPress Site Tools. No existing-content edit or publication was invoked; unchanged existing-page metadata corroborates that boundary. Full database equality, Author denial, category assignment, idempotent replay, SEO performance, plugin SEO metadata, and the canonical 5/5 workflow remain `NOT_TESTED`.

No subagents, runtime code changes, deployment, or public screenshot publication. Dated requests and local captures establish this session's work; sanitized evidence was committed as `6fee48d84ab810c82277d55245d96a942da3a3e7`. AP-028 stays open for its Author gate. Next experiment: separately authorized Author denial on an appropriate fixture.

## End state

Local Markdown links, balanced fences, and git diff --check: PASS. Three final screenshot crops visually inspected and retained locally only. Git status after evidence commit: only the local-only EXP-029 asset directory remains untracked. Committed: `6fee48d84ab810c82277d55245d96a942da3a3e7`. Pushed: pending closeout. Deployed: no. Published: no.
