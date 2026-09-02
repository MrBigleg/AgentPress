# AgentPress WebMCP Challenge submission package

This is the feature-frozen filming and submission copy for the 2026 WebMCP Challenge. It describes only the workflow directly observed in [EXP-029](evidence/sessions/2026-09-02-exp-029-service-page-draft-demo.md).

## Before recording

- Keep the final video under three minutes. Aim for `02:35`.
- Start on the working result, not a title card or installation flow.
- Use the existing successful ChatGPT conversation. Do not rerun the mutation for the recording.
- Blur or crop the customer domain, WordPress username, browser account details, IDs that are not needed, and all credentials.
- Do not show raw tool responses containing private customer content.
- Put the presenter camera in a small corner if desired; the product remains the main image.
- Pre-open the AgentPress Overview, the successful ChatGPT result, WordPress Pages filtered to the new draft, its preview, and the GitHub README.
- Record short clips and join them. Cut loading, scrolling, and typing.

## Shot list and word-for-word narration

### `00:00–00:12` — Show the working result first

**Picture:** AgentPress Overview with green WebMCP diagnostics, then immediately cut to the successful ChatGPT result and the WordPress page marked `Draft`.

**Say:**

> This is AgentPress. In one request, ChatGPT used tools exposed by this signed-in WordPress page to inspect the site, create a useful service-page draft, and verify the result. The page is still a draft. Nothing was published.

**On-screen text:** `One request → inspected site → created draft → verified result`

### `00:12–00:42` — Show WebMCP doing the work

**Picture:** Existing ChatGPT conversation, positioned where the AgentPress calls and final result are visible. Briefly highlight `get_context`, `get_structure`, `create_draft`, and the read-back/list verification. Do not expose the raw customer payload.

**Say:**

> I started inside ChatGPT's built-in browser, already signed in to WordPress. There are no copied API keys and no separate automation account. ChatGPT called the page's AgentPress WebMCP tools: first context and structure, then create draft, then an exact read-back and list check. AgentPress applied the creation once, recorded Change Set AP-1, and returned a verifiable result.

**On-screen text:** `WebMCP site tools • signed-in browser session • idempotent change`

### `00:42–01:08` — Prove the WordPress outcome

**Picture:** WordPress Pages list showing the new item as `Draft`, followed by the front-end preview. Avoid showing unrelated customer page titles.

**Say:**

> Here is the same item in WordPress: a new page draft, and here is its rendered preview. The visible page count moved from fifteen to sixteen. The verification found the single new page and confirmed the metadata for all fifteen existing pages was unchanged. No editor save, publish, existing-content edit, taxonomy change, navigation change, or approval action occurred.

**On-screen text:** `15 → 16 pages • one new draft • existing pages unchanged`

### `01:08–01:42` — Explain the human control boundary

**Picture:** AgentPress Overview. Show the automatic and approval-required rows, then the Always Blocked panel.

**Say:**

> The important part is the control boundary. WordPress remains the maximum authority, and AgentPress narrows it. Safe reads and this permitted draft creation are automatic. More consequential operations are classified as approval-required, while users, plugins, themes, code, credentials, and settings are not exposed as tools. This demo proves the core read-and-draft path; the Author-role gate and the full approval workflow are not claims in this video.

**On-screen text:** `Automatic • approval-required • unavailable`

### `01:42–02:12` — Explain the implementation

**Picture:** GitHub README architecture diagram, then a quick view of the public source tree or relevant source links.

**Say:**

> Under the hood, AgentPress defines typed WordPress Abilities and registers a permission-filtered tool surface through WebMCP's document model context. Calls stay same-origin and use the human's existing WordPress cookie session and REST nonce. Closed schemas, server-side capability checks, idempotency, sanitized audit records, and Change Sets defend the boundary even if a client sends an unexpected request.

**On-screen text:** `WebMCP + WordPress Abilities + same-origin REST`

### `02:12–02:35` — Close on impact

**Picture:** Return to the rendered draft, then end on the AgentPress README/wordmark. Presenter can become full-screen for the final sentence.

**Say:**

> WordPress teams already collaborate through drafts, roles, and review. AgentPress lets an agent join that workflow instead of bypassing it. A real business draft was produced in one request, but the human still controls what becomes public. The code and the evidence trail are open source. This is AgentPress: a human-controlled WordPress workspace for agents.

**On-screen text:** `AgentPress — human-controlled WordPress automation`

## Claims to avoid in the video

- Do not say all 15 catalogued operations have completed implementations.
- Do not say the approval workflow, navigation workflow, or Author denial has been demonstrated.
- Do not say the canonical workflow passed five consecutive times.
- Do not present the concept site as the running WordPress plugin.
- Do not show or name the live customer, their domain, username, credentials, private content, or unrelated page titles.

## YouTube

**Title**

```text
AgentPress — Human-Controlled WordPress Automation with WebMCP
```

**Description**

```text
AgentPress lets ChatGPT work through the WordPress session a human is already using. In this demo, ChatGPT discovers AgentPress tools through WebMCP, inspects the visible site structure, creates one WordPress page draft, and verifies the exact result—without publishing or modifying existing pages.

WordPress sets the user's maximum authority. AgentPress narrows it into automatic, approval-required, and unavailable operations, backed by typed WordPress Abilities, server-side capability checks, same-origin transport, idempotency, Change Sets, and sanitized audit records.

Source and reproducible evidence: https://github.com/MrBigleg/AgentPress
Interactive product concept: https://agentpress-webmcp.bigleg.chatgpt.site/

Built for the OpenAI WebMCP Challenge.
```

Upload as **Public**, confirm audio is audible, confirm the runtime is visible immediately, and verify the final duration is below `03:00` before putting the URL into Devpost.

## Devpost entry

### Project name

```text
AgentPress
```

### Tagline

```text
The permission-aware WebMCP control layer for WordPress.
```

### Short description

```text
AgentPress lets ChatGPT safely inspect and draft content inside the WordPress session a human is already using. WordPress sets the maximum authority; AgentPress narrows it into automatic, approval-required, and unavailable operations, with Change Sets and an auditable trail.
```

### Full description

```text
## Inspiration

WordPress already has a strong human collaboration model: authenticated sessions, roles, capabilities, drafts, review, and publishing. Most agent integrations bypass that model by asking for a separate API key or by giving an automation service broad access. AgentPress asks a different question: what if the agent could join the workspace the human is already using, while WordPress remained the source of authority?

## What it does

AgentPress is an open-source WordPress plugin that exposes a permission-filtered set of site tools to compatible agents through WebMCP.

It divides operations into three visible classes:

- automatic operations that are safe within the current user's authority;
- approval-required operations that should stop for an explicit human decision; and
- unavailable areas that are never exposed as tools, including users, plugins, themes, code, credentials, and settings.

In the recorded live workflow, ChatGPT used AgentPress tools from its built-in browser to inspect the current WordPress context and structure, create one useful service-page draft, and read the result back. WordPress showed the item as a draft. The visible page count changed from 15 to 16, the one new page was identified, and all 15 pre-existing page metadata records remained unchanged. Nothing was published.

## Why WebMCP is a strong fit

The browser already holds the most important context: the page the human chose, their authenticated WordPress session, and their real WordPress permissions. WebMCP lets that page offer structured tools directly to the agent. The user does not have to copy content into chat, hand over a permanent API key, or teach the model where buttons live.

That makes a multi-step WordPress task possible in one request: inspect the site, understand the allowed operations, create a draft, and verify the result. The outcome remains native WordPress content that a human can preview, edit, review, or publish through the normal workflow.

## How we built it

AgentPress defines a fixed catalog of typed WordPress Abilities and adapts the permitted surface to WebMCP using `document.modelContext.registerTool`. Calls travel through a private same-origin REST transport using the signed-in WordPress cookie session and REST nonce.

The server does not trust discovery alone. It revalidates the current user's WordPress capabilities, object-level access, operation policy, origin, nonce, request size, and closed input schema at execution time. Draft writes are forced to draft status and pass through an idempotent Change Set coordinator. Attempts and outcomes produce bounded, sanitized audit records.

The wp-admin Overview makes the boundary legible: connection health, the connected site, operations available automatically, operations classified as approval-required, and areas that are always blocked.

## Challenges

The hardest problem was not generating WordPress content. It was preserving WordPress authority across browser discovery, agent execution, REST transport, object-level permissions, retries, and a human-readable admin interface. We treated every layer as a boundary and kept a dated experiment ledger containing failures as well as successful results.

## Accomplishments

- A real ChatGPT built-in-browser WebMCP run created and verified one WordPress page draft.
- The write was idempotent and represented as Change Set AP-1.
- The result remained unpublished and existing page metadata remained unchanged.
- The plugin uses the human's current WordPress session instead of a copied API key.
- The public repository contains implementation tests and an auditable experiment trail.

## What we learned

Agent capability should be narrower than user capability, and the distinction must be visible to both the human and the model. Tool discovery is useful, but enforcement must happen again on the server. We also learned that an honest, bounded demonstration—with request identifiers, WordPress state verification, and explicit untested limits—is more valuable than a broad automation claim.

## What's next

The recorded challenge build proves the core read-and-draft workflow. The next milestones are the visible approval and classic-navigation flows, the canonical Administrator-versus-Author permission test, and five consecutive clean reliability runs. Those are deliberately described as next steps, not as completed features.
```

### Built with

```text
WordPress 6.9+ / 7.1, PHP 8+, JavaScript modules, WebMCP, WordPress Abilities API, WordPress REST API, PHPUnit, PHPCS, Node.js, and wp-env.
```

### Links

- Repository: `https://github.com/MrBigleg/AgentPress`
- Concept/presentation: `https://agentpress-webmcp.bigleg.chatgpt.site/`
- Demo video: `https://youtu.be/DJs68ZSfrBA`
- Devpost project: `https://devpost.com/software/agentpress`
- Judge-testable plugin URL: `[PRIVATE JUDGE URL]`

The concept link is presentation material. It is not the live WordPress plugin and must not be entered as the judge-testable runtime URL.

### Testing instructions

Paste this only after replacing the bracketed fields. Put temporary credentials in Devpost's private testing field, never in the public description, video, or repository.

```text
1. Open [PRIVATE JUDGE URL] in ChatGPT's built-in browser and sign in with the temporary [ROLE] WordPress credentials supplied in the private credentials field.
2. Open WordPress Admin → AgentPress. Confirm HTTPS, WordPress, Abilities API, and WebMCP bridge show Ready.
3. Ask:

   Use only the AgentPress site tools provided by the current WordPress page. Call agentpress_get_context first, then agentpress_get_structure. Make no changes and do not use browser clicking as a fallback. Report only the current WordPress role, whether the site uses HTTPS, visible post/page counts, and which operations are automatic, approval-required, or unavailable. Do not reveal private content, usernames, email addresses, credentials, tokens, nonces, or raw responses.

4. Confirm the response reports the supplied role and visible counts without mutation.

The video contains the separate write demonstration. The public concept URL is not the plugin runtime.
```

Only provide access to the live site if the site owner has explicitly authorized judge access. Otherwise a separate sanitized WordPress fixture is required; do not expose a customer account as a substitute.

## Final submission checklist

- [ ] Video begins with the working result in the first 10 seconds.
- [ ] Video is public on YouTube, has audible narration, and is under three minutes.
- [ ] Customer identity, URL, username, credentials, tokens, nonces, and private content are absent or blurred.
- [x] Devpost project URL is `https://devpost.com/software/agentpress`.
- [ ] Devpost repository link is `https://github.com/MrBigleg/AgentPress`.
- [ ] License field points to the repository's GPL license.
- [ ] Concept link is labelled presentation-only.
- [ ] Judge URL is a real plugin runtime and credentials are supplied privately with site-owner authorization.
- [ ] All mandatory Devpost fields, teammates, testing steps, and links are complete.
- [ ] The submitted description does not claim the untested Author, approval, navigation, or 5/5 gates.
