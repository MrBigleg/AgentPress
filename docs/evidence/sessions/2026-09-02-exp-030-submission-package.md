# EXP-030 — Evidence-bounded challenge submission package

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-030` |
| Related task | `AP-032` |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-09-02T13:43:18+07:00` |
| Started UTC | `2026-09-02T06:43:18Z` |
| Ended local | `2026-09-02T13:49:11+07:00` |
| Ended UTC | `2026-09-02T06:49:11Z` |
| Agent/operator | Codex documentation agent; Craig as recorder/submitter |
| Branch | `main` |
| Baseline commit | `7643a5aef4abb000d2396cd13add7136fbdd5ce4` |
| Ending commit | `71d6ce813fe7257f5d7bbb1c949d37b1305ceebd` |
| Environment | Windows; repository evidence through EXP-029; no new WordPress or browser execution |

## Question

Can the demonstrated EXP-029 workflow be converted into a complete sub-three-minute filming script and Devpost draft without claiming unimplemented or untested AgentPress behavior?

## Hypothesis

The prior live evidence is sufficient for one strong, bounded story: ChatGPT inspects a signed-in WordPress site, creates one page draft, reads it back, and leaves publishing and existing content untouched.

## Falsification condition

The hypothesis is false if the package requires a new runtime action, exceeds three minutes at the planned pace, exposes customer/private data, treats the concept site as a plugin runtime, or claims approval, navigation, Author denial, all 15 implementations, or 5/5 reliability as demonstrated.

## Controls

- fixed commit/build: baseline `7643a5aef4abb000d2396cd13add7136fbdd5ce4`;
- fixed fixture/data: only the redacted observations recorded by EXP-029;
- fixed identity/capabilities: observed Administrator run; no Author claim;
- fixed policy/configuration: no plugin, server, or live-site changes;
- fixed client/environment: existing ChatGPT built-in-browser recording material;
- explicit scope exclusions: reinstall, rerun, approval/navigation implementation, Author gate, 5/5 reliability, video upload, and Devpost submission.

## Variables

- **Independent:** ordering and wording of the recorded evidence.
- **Dependent:** planned duration, privacy boundary, claim accuracy, and completion of required submission fields.

## Preflight

```text
timestamp: 2026-09-02T13:43:18+07:00 / 2026-09-02T06:43:18Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: main matches origin/main; docs/evidence/assets/EXP-029/ untracked
git log -3 --oneline --decorate: 7643a5a, 6fee48d, 2e94856
baseline SHA: 7643a5aef4abb000d2396cd13add7136fbdd5ce4
unrelated existing changes: three customer-derived EXP-029 screenshot crops remain local-only and untracked
```

## Method

1. Bound the story to the successful EXP-029 live read/create/read-back workflow.
2. Put the working product in the first 12 seconds and allocate a `02:35` target.
3. Write word-for-word narration with matching shots and compact on-screen captions.
4. Draft the Devpost short description, long-form sections, technology list, links, and privacy-safe testing instructions.
5. Add explicit prohibited claims and a final submission checklist.
6. Check Markdown structure, local links, staged whitespace, and the staged manifest before commit.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No delegation used | N/A | N/A | N/A |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `OBSERVED` | [EXP-029](2026-09-02-exp-029-service-page-draft-demo.md) | 2026-09-02 | One page draft was created and exactly read back in ChatGPT's built-in browser | Bounded Administrator workflow |
| S2 | `SOURCE_VERIFIED` | [WebMCP Challenge rules](https://webmcp.devpost.com/rules) | 2026-09-02 | Public YouTube video, audio, under three minutes, repository and testing requirements | Official rules |
| S3 | `SOURCE_VERIFIED` | [OpenAI Site Tools help](https://help.openai.com/en/articles/20001423-using-site-tools-in-the-chatgpt-desktop-app) | 2026-09-02 | ChatGPT built-in browser Site Tools context | Official OpenAI help |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-09-02T13:43:18+07:00 | Capture branch, status, log, and SHA | repository root | exit 0 | baseline and local-only screenshots recorded |
| 2026-09-02T13:43:18+07:00 | Draft filming and Devpost package | `docs/CHALLENGE_SUBMISSION_PACKAGE.md` | complete | target `02:35`; no new runtime claim |
| 2026-09-02T13:46:27+07:00 | Reinspect three final EXP-029 crops after owner publication authorization | `docs/evidence/assets/EXP-029/` | pass | no domain, business name, account identity, credentials, tokens, or nonces visible |
| 2026-09-02T13:49:11+07:00 | Markdown, link, privacy-string, staged whitespace, and manifest checks | repository root | pass | 329 narration words; balanced fences; local links and staged diff pass |
| 2026-09-02T13:49:11+07:00 | Commit submission package | repository root | exit 0 | `71d6ce813fe7257f5d7bbb1c949d37b1305ceebd` |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | EXP-029 contains a complete read/create/read-back workflow with a WordPress Draft outcome and bounded non-mutation checks. | S1 | supports |
| O2 | `OBSERVED` | The narration allocates `02:35`, begins with the working result, and uses the existing successful conversation. | submission package | supports |
| O3 | `OBSERVED` | The package explicitly excludes all untested critical claims and separates the concept URL from the runtime URL. | submission package | supports |
| O4 | `OBSERVED` | The testing template withholds credentials from public surfaces and requires explicit site-owner authorization. | submission package | supports |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | Existing screenshots might support public repository evidence | The crops contain customer-derived title/content and initially lacked publication authorization | privacy boundary | Project owner explicitly authorized publication; final crops reinspected and approved for the public evidence record |
| C2 | Submission package could contain a final judge URL and video URL | Neither final URL was supplied in this session | external completion dependency | Keep explicit placeholders and final checklist items |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Freeze runtime scope and film the existing successful workflow. | O1 | Does not demonstrate later v0.1 flows | After challenge submission |
| D2 | `DECIDED` | Use a read-only judge prompt unless a sanitized write fixture is separately authorized. | O4, C1 | Judge repeats the safe read path rather than the video write | Sanitized fixture available |
| D3 | `DECIDED` | Label the public concept site as presentation-only. | O3 | Requires a separate judge runtime URL | A real public fixture is deployed |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Working behavior appears first | Shot order inspection | `PASS` | first shot at `00:00` |
| Planned duration below three minutes | Segment allocation sum | `PASS` | target `02:35` |
| WebMCP use is central | Narration/shot inspection | `PASS` | `00:12–00:42` plus architecture segment |
| Claims match observed behavior | Compared with EXP-029 and open boundaries | `PASS` | claim-avoidance list |
| Private data is protected | Crop/blur and credential instructions present | `PASS` | pre-recording and testing sections |
| Final public video exists | No upload performed | `NOT_TESTED` | `[PUBLIC YOUTUBE URL]` |
| Judge runtime access exists | No authorized URL/credentials supplied | `NOT_TESTED` | `[PRIVATE JUDGE URL]` |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/CHALLENGE_SUBMISSION_PACKAGE.md` | submission/script | tracked after authorized commit | pending commit | contains no credentials |
| `docs/evidence/assets/EXP-029/01-agentpress-capabilities.jpg` | screenshot | tracked after authorized commit | `C10C140BBACFB483AD6E1C633C24639AA9A9064BA632D2D32E731252D3A7B9C6` | identity-cropped capability summary |
| `docs/evidence/assets/EXP-029/02-page-draft-status.jpg` | screenshot | tracked after authorized commit | `0B1C9BBD0F6D049DD355ABBC1308BB68B17ED6E9BDB12C93481623296E2C33FB` | owner-authorized customer-derived title |
| `docs/evidence/assets/EXP-029/03-draft-content-preview.jpg` | screenshot | tracked after authorized commit | `6B3118483E170FFCA853C2826E458F74AABBC13405F83F55F797E6A5A984E6CC` | owner-authorized customer-derived excerpt |
| public YouTube video | video | external/not created | `NOT_TESTED` | must be under three minutes with audio |

## Result

`SUPPORTED`

The recorded live workflow can support a focused `02:35` video and a complete Devpost narrative without expanding the demonstrated behavior. This does not establish a public video, submitted Devpost entry, judge-authorized runtime, Author denial, approval/navigation behavior, or five-run reliability.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: recorded narration duration at Craig's speaking pace.
- `NOT_TESTED`: final redaction, audio, upload visibility, and YouTube duration.
- `NOT_TESTED`: Devpost field validation or final submission.
- `NOT_TESTED`: judge access to an authorized live WordPress fixture.

## Competition evidence statement

- work attributable to challenge period: submission package derived from dated EXP-029 evidence;
- pre-existing work distinguished by: experiment ledger and Git history;
- third-party material/license/pin: no new third-party material;
- commit/PR evidence: package commit `71d6ce813fe7257f5d7bbb1c949d37b1305ceebd`;
- live URL evidence: EXP-029 records the private run; public judge access `NOT_TESTED`;
- real ChatGPT Site Tools evidence: bounded workflow recorded in EXP-029;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: script prepared; upload and submission `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: post-submission closeout / AP-032;
- next falsifiable question: does the final public video and Devpost entry satisfy every mandatory field without privacy leakage?
- required prerequisites: redacted final video, public YouTube URL, and authorized judge runtime details.

## End state

```text
git status --short --branch: main ahead of origin/main by one package commit before closeout
tests/checks: PASS — 329 narration words; balanced fences; local links; privacy-string scan; git diff --cached --check; exact staged manifest
committed: 71d6ce813fe7257f5d7bbb1c949d37b1305ceebd
pushed: pending closeout
deployed: no
```

## Dated link update — 2026-09-02T16:35:01+07:00

The project owner supplied the final video URL and current Devpost project URL after the original EXP-030 closeout.

- `OBSERVED`: `https://youtu.be/DJs68ZSfrBA` redirected to its YouTube watch URL and returned HTTP 200 in an unauthenticated header request on 2026-09-02. Video playback, audio, final duration, and visibility in a separate signed-out browser remain `NOT_TESTED` by this check.
- `OBSERVED`: `https://devpost.com/software/agentpress` returned HTTP 200 in an unauthenticated header request on 2026-09-02. The project owner states finalization is planned for 2026-09-03; final Devpost submission remains `NOT_TESTED`.
- `DECIDED`: place both links prominently in the README and replace the video placeholder in the submission package. The private judge-runtime placeholder remains open.
