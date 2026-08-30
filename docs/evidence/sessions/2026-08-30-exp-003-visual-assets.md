# EXP-003 — Visual asset classification and curation

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-003` |
| Related task | visual evidence curation before implementation |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-08-30T09:13:58+07:00` (record opened during curation; exact message time not preserved) |
| Started UTC | `2026-08-30T02:13:58Z` |
| Ended local | `2026-08-30T09:16:24+07:00` |
| Ended UTC | `2026-08-30T02:16:24Z` |
| Agent/operator | Codex with project-owner classification and authorization |
| Branch | `main` |
| Baseline commit | `835a8d241519544053a8c90b9047c665727b92bf` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows/PowerShell; five PNG source assets; no image editing |

## Question

Can the supplied visual material be organized as competition evidence while keeping approved identity direction, concept references, and implemented product evidence unambiguously separate?

## Hypothesis

One approved identity folder, one concept folder, a hashed asset ledger, and explicit README/evidence labels will preserve the useful visuals without allowing concept screens or older icon labels to become false implementation claims.

## Falsification condition

The hypothesis is falsified if any source asset is lost/overwritten, an unapproved concept is presented as final, an illustrative domain/UI is described as live, or another agent cannot trace the curated file to its observed original filename and final hash.

## Controls

- No pixel/image-content edits.
- Exact source files only; no wildcard move or overwrite.
- Technical scope and specifications remain unchanged.
- The project owner's explicit classification controls approved versus concept status.
- No claim about source-file provenance, publication rights, or live `agentpress.dev` status beyond what was directly provided/observed.

## Variables

- **Independent:** classification, final folder, and normalized filename.
- **Dependent:** asset preservation, traceability, hash, README presentation, and evidence clarity.

## Preflight

```text
workspace: C:\Users\craig\01_Projects\WP-Agent-Admin
source: C:\Users\craig\01_Projects\WP-Agent-Admin\docs\assets
target: C:\Users\craig\01_Projects\WP-Agent-Admin\docs\evidence\assets
source verified inside workspace: true
source file count before move: 5
existing target collisions: none
```

## Method

1. Visually inspect each asset without editing it.
2. Treat `AgentPress Aproved Design.png` as approved identity direction per project-owner instruction.
3. Treat the remaining four sheets as concept/reference material.
4. Verify absolute source/target roots remain inside the workspace.
5. Create approved/concept directories and move five exact literal paths with collision checks.
6. Normalize filenames while preserving original/observed filenames in the ledger.
7. Calculate SHA-256 and byte size for every final file.
8. Embed only the approved identity sheet in README and link concepts through the ledger.
9. Verify file count, paths, hashes, Markdown links, and worktree scope.

## Observation ledger

| ID | Label | Observation | Effect |
|---|---|---|---|
| O1 | `OBSERVED` | The approved sheet is an identity system containing primary/alternate marks, wordmarks, colors, usage guidance, and application examples. | Suitable for approved identity presentation. |
| O2 | `OBSERVED` | Two hackathon/showcase sheets are alternate visual concepts using different robot treatments. | Both remain concepts; neither is promoted over the other. |
| O3 | `OBSERVED` | The brand-system sheet is a concept with palette, typography, UI motifs, and a collaboration illustration. | Useful design reference, not implemented UI evidence. |
| O4 | `OBSERVED` | The icon sheet contains 15 illustrative icons but includes older decomposed bootstrap tools and non-v0.1 statuses such as scheduled/archived. | Must not override the final 15 Ability contracts. |
| O5 | `OBSERVED` | The fifth file was initially observed with a generated-image filename and was renamed to `AgentPress Human–Conecpt Ideas2.png` before the authorized move. | Both observed names are preserved in the asset ledger. |
| O6 | `DECIDED` | Only the approved identity sheet is embedded in the root README. | Avoids presenting concept screens as shipped product. |

## Execution log

| Step | Action | Evidence | Outcome |
|---|---|---|---|
| 1 | Inspect the five source PNGs | Direct visual inspection of each local file | `PASS`: one approved identity sheet and four concept sheets classified |
| 2 | Move and normalize filenames | Five exact literal-path moves with workspace and collision checks | `PASS`: five files in the curated folders; zero files remain in `docs/assets/` |
| 3 | Hash curated files | PowerShell `Get-FileHash -Algorithm SHA256` and byte counts | `PASS`: all five results match the asset ledger |
| 4 | Check documentation integrity | Local Markdown target resolution across 14 Markdown files | `PASS`: zero broken local links and zero unbalanced code fences |
| 5 | Check formatting | Trailing-whitespace scan across Markdown | `PASS`: zero trailing-whitespace lines |
| 6 | Check technical-scope control | Re-hash implementation spec and build checklist | `PASS`: both hashes remain identical to EXP-001 |
| 7 | Inspect repository scope | `git status --short --branch` | `PASS`: documentation/assets only; no commit, push, deployment, or product code |

## Verification matrix

| Acceptance condition | Observed result | Status |
|---|---|---|
| No supplied source asset is lost | Five source files before move; five curated PNG files after move | `PASS` |
| No target is overwritten | Collision checks found no existing targets | `PASS` |
| Approved and concept material remain distinct | One PNG under `approved/`; four under `concepts/` | `PASS` |
| Every asset is traceable | Asset ledger records original filename, final path, classification, bytes, and SHA-256 | `PASS` |
| README does not imply concept implementation | Only approved identity is embedded; concept limitations are adjacent | `PASS` |
| Illustrative domains/UI are not treated as live | README, asset ledger, and this record state the boundary | `PASS` |
| Documentation references resolve | 14 Markdown files checked; zero broken local links | `PASS` |
| Planning artifacts are unchanged | Spec SHA-256 `8aa5daed0db772ecd96be7fa1d736706cfe659f5548bcf725dea7754383de492`; checklist SHA-256 `5d13543170e9fbbd77114ddee35ce0c646dead0e8db9c35bb0ab17d995956a46` | `PASS` |

## Artifact inventory

| Artifact | Bytes | SHA-256 |
|---|---:|---|
| `docs/evidence/assets/approved/agentpress-approved-identity-system.png` | 1,544,974 | `c070c3f96d3255bb928a17c3002e797504e8b04ee1895ae778eeacc2abe69c4b` |
| `docs/evidence/assets/concepts/agentpress-brand-system-concept.png` | 1,418,301 | `99210312052b5c14f4c954c865438b5c46f97c4bfabc85f93c130a950c8ba7c4` |
| `docs/evidence/assets/concepts/agentpress-hackathon-assets-concept-a.png` | 1,678,455 | `2d93b4fe2f473bcadc0a2c5cc9c458b84bce82fac66f15d778ea30d90fe9fdee` |
| `docs/evidence/assets/concepts/agentpress-hackathon-assets-concept-b.png` | 1,651,906 | `053264caa38dde8a0125e2e8205765444b3d674d306041127cced3b3db698256` |
| `docs/evidence/assets/concepts/agentpress-icon-library-concept.png` | 1,527,907 | `98fc2d515bf2f01bc38a2a0fc2b935f9e845353e6915d8c8b41786e3fa4780f8` |
| `docs/evidence/assets/README.md` | 3,558 | `bbf5a5f6e3931f4959fd038e6bb66ef3fe8b34d211bf86307bb333d091ef90c7` |
| `README.md` | 10,955 | `f21f40564e90a905025305d3b33da73ab7b1c96b4ede9b43b342e4822f4ff113` |
| `AGENTS.md` | 8,519 | `2dc78baff35d4da2197b0c73470ac86a386b48ea535eb7a5d62a0edf5ae7966a` |

## Result

`SUPPORTED`

The curated structure preserves all five supplied images and separates one owner-approved identity direction from four concept references. The asset ledger makes each file independently checkable, and the README presents the approved identity without claiming that concept UI, domains, or icons are implemented.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: editable/vector source availability.
- `NOT_TESTED`: independent authorship/licensing chain and required AI-content disclosure.
- `NOT_TESTED`: production logo exports at required sizes.
- `NOT_TESTED`: any depicted UI or `agentpress.dev` deployment.

## Competition evidence statement

- The project owner authorized inclusion and approved/concept classification in the active session.
- Assets remain `UNCOMMITTED` until an authorized commit.
- No visual is proof of implementation, live deployment, or a passing demo.

## Next experiment

- AP-001 is proposed as EXP-004 when implementation is authorized.

## End state

```text
five curated PNG files: verified
approved/concept split: verified
asset hashes and sizes: verified
broken local Markdown links: 0
unbalanced Markdown code fences: 0
trailing-whitespace lines: 0
technical planning artifacts changed: no
commit/push/deployment/product tests: not performed
```

## Dated amendment — `2026-08-30T09:20:12+07:00`

After the first terminal verification, the project owner added `AgentPress Tech Logo with Gradient Icon.png` directly to the approved folder at approximately `2026-08-30T09:17:30+07:00`. This concurrent addition was detected by the final file-count check rather than silently omitted.

The PNG was visually inspected as a transparent AgentPress gradient wordmark, then renamed by exact literal path to `docs/evidence/assets/approved/agentpress-approved-gradient-wordmark.png`; its pixels were not edited. Its classification as approved is based on the owner's placement in the explicit approved folder following the owner's instruction that approved and concept images may be included.

| Added artifact | Bytes | SHA-256 |
|---|---:|---|
| `docs/evidence/assets/approved/agentpress-approved-gradient-wordmark.png` | 321,369 | `314161f7cf4c332d0d15a3d741c3c3b56d95c5eb9d532f450d8b468a79103edd` |

The first artifact table and five-file end state remain the accurate `09:16:24+07:00` snapshot. The amended terminal state is six curated PNGs: two approved identity assets and four concept sheets. The asset ledger now has SHA-256 `25e6572d84828c8083fb1d3517c4d7140b1870c8831819f5715164d8dfc9a946`; the revised root README has SHA-256 `58b9894e8d2a2eda3d4577df5361e392e1a064d0373dde1760ef425a04c679d0`. The hypothesis remains `SUPPORTED`; no concept was promoted and no implementation claim was added.
