# AgentPress live test and demo recording runbook

**Status:** `DECIDED` recording plan; live execution remains `NOT_TESTED` until recorded in EXP-025.
**Scope:** the currently implemented safe core on a real HTTPS WordPress session. Do not depict unfinished navigation, approval, publishing, term creation, update-content, Change Set reads, or Activity reads.

## Immediate decision

Stay signed in as **Administrator** for the first read-only smoke test and the positive demo clip. Use the **Author** account in a separate clip for the permission-boundary proof. Do not switch roles during the hero take.

Do not make the final video the first execution attempt. Run the read-only prompt below once before recording. It changes nothing and confirms that ChatGPT can actually call the site tools.

## Customer-site safety gate

Do not record or submit a customer site unless the customer has explicitly authorized public display and judge access. The public video must not expose the customer domain, username, email, unpublished content, credentials, nonces, or other private data. Blur or crop identifying browser and WordPress chrome. Never provide judges with a customer Administrator account.

Preferred order:

1. Use an isolated staging/demo site with synthetic content.
2. If that is impossible and the customer has authorized testing, use one uniquely titled synthetic **post draft** only.
3. Give judges a temporary least-privilege demo account, not an Administrator account, and remove it after the judging period.

Abort immediately if a prompt proposes publication, an existing-content edit, term creation, navigation mutation, user/plugin/theme/settings access, or any tool outside the allowed list below.

## Implemented tools allowed in this recording

- `agentpress_get_context`
- `agentpress_get_structure`
- `agentpress_list_content`
- `agentpress_get_content`
- `agentpress_create_draft`
- `agentpress_list_terms`
- `agentpress_assign_terms`

The dashboard currently registers 15 catalogued tool contracts, but the other eight do not yet have concrete service dispatchers in this build. Do not call them or imply that all 15 execute successfully.

## Before recording

- Confirm HTTPS, WordPress, Abilities API, and WebMCP Bridge are green.
- Confirm the address-bar Site Tools indicator is present.
- Record the installed plugin ZIP checksum/build commit if available.
- Choose an existing, non-sensitive category and put its exact name in `{SAFE_EXISTING_CATEGORY}` below.
- Confirm the demo title does not already exist.
- Pre-open the WordPress Posts list in a second tab for visual verification.
- Close notifications, email, password managers, and unrelated customer tabs.
- Hide or crop the address bar, customer identity, WordPress username, and private content.
- Use a neutral recording viewport and increase UI zoom enough for tool activity/results to be readable.
- Paste prompts; do not type them live.
- Record short clips. Preserve the raw clips and edit out waits.

## Rehearsal prompt — read only

Paste this now while signed in as Administrator. It is safe to run before recording.

```text
Use only the AgentPress site tools provided by the current WordPress page.

Call agentpress_get_context first, then agentpress_get_structure. Make no changes. Do not use normal browser clicking as a fallback and do not call any non-AgentPress tool.

Report only:
1. my current WordPress role;
2. whether this is an HTTPS WordPress site;
3. the visible post and page counts; and
4. which AgentPress operations are automatic, approval-required, or unavailable.

Do not reveal private content, usernames, email addresses, credentials, tokens, nonces, or raw responses.
```

Pass conditions:

- ChatGPT visibly uses Site Tools.
- `agentpress_get_context` and `agentpress_get_structure` appear in recent tool activity.
- The role is Administrator and the summary matches the page.
- No site data changes.

If this fails, stop. Save the exact error and do not start the mutation take.

## Hero prompt — one safe draft workflow

Replace `{SAFE_EXISTING_CATEGORY}` once before pasting. Keep the idempotency keys unchanged if the prompt is accidentally retried; that prevents duplicate mutations.

```text
Use only these AgentPress site tools on the current WordPress page:
agentpress_get_context, agentpress_list_terms, agentpress_create_draft, agentpress_assign_terms, and agentpress_get_content.

Do not call any other tool. Do not publish anything. Do not edit existing content. Do not create or modify terms. Do not change navigation, users, plugins, themes, settings, or media.

First confirm that my current role is Administrator. Then list existing categories and find the exact category named "{SAFE_EXISTING_CATEGORY}". If that category does not exist, stop without creating anything.

If it exists, create exactly one WordPress POST draft with:
- title: "AgentPress WebMCP Demo — 2026-09-02"
- content: "<p>This unpublished synthetic draft was created through AgentPress using WebMCP inside the signed-in WordPress session.</p>"
- excerpt: "Synthetic WebMCP demonstration draft."
- change set title: "AgentPress WebMCP demo"
- idempotency key: "agentpress-demo-20260902-create-v1"

Assign only the existing category you found to that new draft in append mode, using the same change set ID and this idempotency key: "agentpress-demo-20260902-category-v1".

Finally, read the new post back with agentpress_get_content and report its content ID, title, post status, assigned category, change-set reference, and the AgentPress tools used. The final status must remain draft.
```

Pass conditions:

- One post is created, never a page.
- The returned status is `draft`/`APPLIED`.
- The existing category is assigned to the new AgentPress-created draft.
- `get-content` confirms the stored result.
- No existing post, page, category, menu, or published state changes.
- Recent Site Tools activity shows the AgentPress calls.

## Author permission proof — separate clip

Sign out of WordPress inside the built-in browser, sign in as the Author, return to AgentPress, and wait for the dashboard identity/counts to update. Do not reuse the Administrator page without a full role/session refresh.

```text
Use only AgentPress site tools on the current WordPress page.

First call agentpress_get_context and report my current WordPress role. Then make exactly one direct call to agentpress_create_draft requesting post_type "page", title "AgentPress Author Denial Check — 2026-09-02", and idempotency key "agentpress-demo-20260902-author-page-denial-v1".

Do not fall back to browser clicks or another tool. Do not create a post instead. Do not make any other change. Report the exact AgentPress outcome and explain whether WordPress allowed this Author account to create a page.
```

Pass conditions:

- Context reports Author.
- The page request returns `AP_PERMISSION_DENIED` or an equivalent undiscoverable/unauthorized result.
- No page with that title exists.
- No navigation, term, post, or approval record is created by fallback behavior.

## Recommended video order — target 2:30

### 0:00–0:12 — working result first

Start already logged in on the green AgentPress screen with the hero prompt ready. Submit it immediately and show the address-bar Site Tools indicator activate.

Narration:

> This is AgentPress: a human-controlled WebMCP layer inside WordPress. ChatGPT is using tools from the page and the WordPress session I am already signed into—there is no copied API key or separate agent account.

On-screen text: `Real WordPress session • Real WebMCP tools • No API keys`

### 0:12–0:55 — real multi-tool workflow

Show the context/category lookup, draft creation, term assignment, and read-back. Jump-cut waits, but keep the tool names and final structured result visible.

Narration:

> In one request, AgentPress checks my current authority, finds an existing category, creates exactly one unpublished post draft, assigns the category, and reads the saved result back. Closed schemas and idempotency keep retries bounded, and the tool can never publish this draft.

On-screen text: `Inspect → Draft → Categorize → Verify`

### 0:55–1:15 — verify in WordPress

Jump to the pre-opened Posts tab and show the uniquely titled item with status Draft. Do not open real customer posts.

Narration:

> The result is visible in ordinary WordPress: one draft, still unpublished. AgentPress records the work against a Change Set so human and agent actions remain attributable.

On-screen text: `Draft only • Existing category • Auditable Change Set`

### 1:15–1:50 — permission boundary

Cut to the separately recorded Author clip. Show the Author identity, the page-creation request, the denial, and—only if safely visible—the absence of the synthetic page.

Narration:

> The key idea is not giving an AI Administrator power. WordPress remains the authority. When I switch to an Author, AgentPress exposes a narrower effective envelope, and the same direct page-creation request is denied server-side with zero page mutation.

On-screen text: `Administrator ≠ Author • Server-side capability checks`

### 1:50–2:10 — why WebMCP matters

Optionally show a tightly cropped two-second red unsupported-browser diagnostic followed by the green ChatGPT built-in-browser diagnostic.

Narration:

> This is genuinely WebMCP. A browser without WebMCP reports the bridge unavailable; ChatGPT's built-in browser discovers the same site's tools directly from the current page and account.

On-screen text: `Progressive enhancement: unavailable → discovered`

### 2:10–2:30 — implementation and impact

Return to AgentPress Overview or show a brief code overlay containing `document.modelContext.registerTool`.

Narration:

> Under the hood, AgentPress adapts typed WordPress Abilities to document.modelContext.registerTool, uses same-origin cookie authentication and REST nonces, rechecks capabilities for every execution, and keeps consequential areas outside the automatic path. It turns WordPress from a screen an agent clicks into a shared, permission-aware workspace.

End card: `AgentPress — the shared human-agent workspace for WordPress`

## Claims to make

- AgentPress uses the signed-in WordPress browser session.
- ChatGPT discovers page-provided tools through WebMCP.
- The demonstrated workflow creates one unpublished post draft and assigns an existing category.
- WordPress capabilities remain the maximum authority; AgentPress can narrow them.
- Server-side checks, closed schemas, idempotency, and sanitized audit/change records form the safety boundary.
- The project is open source and implemented during the challenge period, with dated evidence in the repository.

## Claims not to make yet

- “All 15 tools are fully implemented.”
- “The complete approval/navigation workflow works.”
- “The build passed 5/5 live reliability runs.”
- “The customer production site is the public judge environment.”
- “Inspector, dashboard green status, or source code alone proves live execution.”
- “Nothing can ever go wrong” or any absolute security claim.

## Capture immediately after each clip

- Local and UTC timestamp.
- ChatGPT/desktop app version and selected model.
- Installed AgentPress build/ZIP checksum if known.
- WordPress and PHP versions.
- Signed-in role, without username/email.
- Prompt used and tool names shown in recent activity.
- Returned request IDs, content ID, Change Set ID/reference, status, and timing.
- Verification that no content was published and no unintended object changed.
- Raw clip filename and SHA-256 after copying it to safe storage.

Do not commit raw customer-site video or screenshots. Store only approved redacted captures or external public video metadata.

## Submission requirements checked 2026-09-02

- Official rules: [The WebMCP Challenge rules](https://webmcp.devpost.com/rules).
- Official client behavior: [Using site tools in the ChatGPT desktop app](https://help.openai.com/en/articles/20001423-using-site-tools-in-the-chatgpt-desktop-app).

`SOURCE_VERIFIED`: the official rules require a working judge-accessible URL, a public source repository with a detectable open-source licence, and a public YouTube demonstration video under three minutes with audio showing the project functioning. If authentication is required, testing credentials may be placed in the submission form. The project must behave as depicted and described.

`SOURCE_VERIFIED`: OpenAI documents that Site Tools run from the current built-in-browser page and its signed-in session, are discovered from the page, expose recent tool activity, and may vary by page/account. Passwords must be entered on the site, never pasted into ChatGPT.
