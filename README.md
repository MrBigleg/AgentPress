# AgentPress

**The permission-aware WebMCP control layer for WordPress.**

<p align="center">
  <a href="https://agentpress-webmcp.bigleg.chatgpt.site/">
    <img src="docs/evidence/assets/approved/agentpress-approved-gradient-wordmark.png" alt="AgentPress" width="560">
  </a>
</p>

<p align="center">
  <a href="https://github.com/MrBigleg/AgentPress/raw/refs/heads/main/dist/agentpress.zip"><strong>Download AgentPress v0.1 (.zip)</strong></a>
  &nbsp;•&nbsp;
  <a href="https://youtu.be/DJs68ZSfrBA"><strong>Watch the Demo (02:17)</strong></a>
  &nbsp;•&nbsp;
  <a href="https://devpost.com/software/agentpress"><strong>Devpost Entry</strong></a>
  &nbsp;•&nbsp;
  <a href="https://agentpress-webmcp.bigleg.chatgpt.site/"><strong>Interactive Concept</strong></a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/WebMCP-Native_Adapter-blue" alt="WebMCP">
  <img src="https://img.shields.io/badge/WordPress-6.9+-21759b" alt="WordPress 6.9 or newer">
  <img src="https://img.shields.io/badge/Security-No_API_Keys-green" alt="No API keys">
  <img src="https://img.shields.io/badge/Version-v0.1.0-brightgreen" alt="v0.1.0">
  <img src="https://img.shields.io/badge/License-GPL--2.0--or--later-orange" alt="GPL-2.0-or-later">
</p>

> **Evaluation note:** AgentPress is ready for evaluation, but the remaining real-client Author-role gate and five-consecutive-run reliability gate are not yet complete. See [Known limitations](#known-limitations).

## Quickstart: install AgentPress on WordPress

### Before you start

You need:

- WordPress 6.9 or newer;
- PHP 8.0 or newer;
- an HTTPS WordPress site with the standard REST API available;
- the ChatGPT desktop built-in browser and an account/model with Site Tools access for the full WebMCP workflow.

AgentPress does not require an AgentPress account, API key, application password, OAuth setup, or separate MCP server.

### 1. Download the plugin

[Download `agentpress.zip`](https://github.com/MrBigleg/AgentPress/raw/refs/heads/main/dist/agentpress.zip).

Download the installable `dist/agentpress.zip` package directly from this repository. Do **not** use GitHub's green **Code → Download ZIP** button: that archive contains the development repository, not the installable WordPress plugin.

Published SHA-256:

```text
47546B6EF80C854648B0843A163AAAA3396851489998B21268D6383B0A1134C8
```

### 2. Upload, install, and activate it

1. Sign in to WordPress as an Administrator.
2. Open **Plugins → Add New Plugin → Upload Plugin**.
3. Choose `agentpress.zip`, then select **Install Now**.
4. When installation finishes, select **Activate Plugin**.
5. Open **AgentPress** from the wp-admin menu.

For an upgrade, back up WordPress first, upload the newer AgentPress ZIP through the same screen, and choose **Replace current with uploaded**. AgentPress preserves Change Sets and audit tables during plugin replacement.

### 3. Open AgentPress inside ChatGPT

1. Open ChatGPT's desktop built-in browser.
2. Navigate to your site's wp-admin and sign in there. The built-in browser has its own session.
3. Open **AgentPress** in the wp-admin menu, or go to `/wp-admin/admin.php?page=agentpress`.
4. Confirm the Overview screen reports the Site Tools connection as **Ready**. Keep this page open while using AgentPress; its tools are page-scoped.

### 4. Run a safe first task

Ask ChatGPT:

> Use AgentPress to inspect this site's structure first. Then draft a new service page about home drain surveys, matching the existing site where appropriate. Keep it as a draft and do not publish anything. Finally, read the new draft back and summarize what changed.

Confirm the result under **Pages → All Pages** and review the matching Change Set and Activity entries under **AgentPress**.

## What AgentPress does

AgentPress lets ChatGPT work through the WordPress session you already control. WordPress defines the logged-in user's maximum authority; AgentPress narrows that authority into safe automatic work, changes that require explicit human approval, and sensitive areas that are never exposed.

- **No additional credentials:** the agent uses the active WordPress browser session.
- **Safe work is automatic:** inspect the site and create unpublished drafts.
- **Consequential work is staged:** publishing, structural edits, and navigation changes wait for approval in wp-admin.
- **Sensitive administration is blocked:** users, plugins, themes, code, credentials, and settings are not tools.
- **Activity is visible:** Change Sets and sanitized audit events show what happened.

## The 15 Site Tools

The names below are the exact WebMCP tools registered by v0.1. Availability is filtered by the current WordPress user's real capabilities.

| Behavior | Site Tools | Result |
|---|---|---|
| Automatic reads and collaboration | `agentpress_get_context`, `agentpress_get_structure`, `agentpress_list_content`, `agentpress_get_content`, `agentpress_list_terms`, `agentpress_get_navigation`, `agentpress_get_change_set`, `agentpress_list_change_sets`, `agentpress_get_activity` | Reads only what the signed-in user may access. Navigation reads require the relevant WordPress capability. |
| Automatic safe write | `agentpress_create_draft` | Always creates a post or page with `draft` status. |
| Conditional write | `agentpress_update_content`, `agentpress_assign_terms` | Applies automatically only to eligible AgentPress-created drafts; otherwise creates a proposal for approval. |
| Always staged | `agentpress_stage_publish`, `agentpress_stage_term`, `agentpress_stage_navigation` | Never performs the consequential change during the tool call; a human must approve it in wp-admin. |

Approving or rejecting a proposal is an explicit human action in WordPress, not an agent tool call.

## How it works

```text
ChatGPT / AI agent in the authenticated browser
       │
       ▼  document.modelContext.registerTool
AgentPress WebMCP browser adapter
       │
       ▼  same-origin REST (session cookie + REST nonce)
AgentPress policy, Safe Mode, Change Sets, approvals, and audit
       │
       ▼
WordPress Abilities API
       │
       ▼
WordPress permissions and content APIs
```

Every execution revalidates authentication, nonce, schema, target, object-specific WordPress capability, AgentPress policy, idempotency, and current target state on the server.

## Troubleshooting

- **Overview does not show Ready:** confirm you opened the AgentPress wp-admin page inside ChatGPT's built-in browser, signed in within that browser session, and are using HTTPS with WordPress 6.9+/PHP 8.0+.
- **No Site Tools appear:** Site Tools availability depends on the supported ChatGPT desktop client, account, and model. Refresh the AgentPress page after signing in or changing users.
- **A normal browser shows a degraded state:** this is expected when `document.modelContext` is unavailable. Ordinary HTTP or a browser without WebMCP cannot expose AgentPress Site Tools.
- **A tool is missing:** AgentPress filters discovery to the current WordPress user's actual capabilities. Changing WordPress users changes the tool and operation envelope.
- **Navigation returns `AP_UNSUPPORTED_NAVIGATION`:** v0.1 supports classic WordPress menus only.

## Known limitations

- Site Tools are page-scoped; keep the AgentPress wp-admin screen open during the session.
- The v0.1 navigation adapter supports classic menus. Block Navigation is detected and rejected safely.
- ChatGPT real-client reads and one draft workflow have direct evidence, but the complete Administrator-versus-Author AP-028 gate remains open.
- The canonical workflow has not yet passed the required five consecutive AP-031 runs.
- This release is not a WordPress.org listing and is not a stable v0.1 declaration.

## Live verification and evidence

- **Live client proof ([EXP-029](docs/evidence/sessions/2026-09-02-exp-029-service-page-draft-demo.md)):** the built-in-browser client inspected a live WordPress site, created one page draft, and read it back; no page was published and the pre-existing page metadata remained unchanged.
- **Native wp-admin collaboration UI ([EXP-023](docs/evidence/sessions/2026-09-01-exp-023-admin-overview.md), [EXP-039](docs/evidence/sessions/2026-09-03-exp-039-changes-activity-ui.md)):** repository tests cover connection health, capability states, Change Set review, approval controls, and sanitized Activity behavior.
- **Release packaging ([EXP-043](docs/evidence/sessions/2026-09-03-exp-043-readme-release-quickstart.md)):** records the repository ZIP package checks, live installation verification, and tracked asset distribution. Historical package work remains in [EXP-042](docs/evidence/sessions/2026-09-03-exp-042-release-package.md).

The [interactive concept](https://agentpress-webmcp.bigleg.chatgpt.site/) is a presentation of the product direction. It is not the WordPress plugin download, a live judge WordPress site, or proof of runtime behavior.

## Developer setup

Prerequisites: Node.js 20+, Docker Desktop, and Git. Host PHP and Composer are optional because `wp-env` supplies them inside its CLI container.

```bash
git clone https://github.com/MrBigleg/AgentPress.git
cd AgentPress
npm install
npm run env:start
npm run env:activate
```

- WordPress: `http://localhost:8888`
- Username: `admin`
- Password: `password`
- AgentPress: `http://localhost:8888/wp-admin/admin.php?page=agentpress`

Useful checks:

```bash
npm run test:unit
npm run test:browser
npm run lint:php
npm run test:third-party
npm run build:zip
```

The installable plugin package is tracked in the repository at `dist/agentpress.zip`.

## Documentation and support

- [Product Requirements Document](docs/PRD.md)
- [Implementation Specification](docs/IMPLEMENTATION_SPEC.md)
- [Dependency-Ordered Build Checklist](docs/BUILD_CHECKLIST.md)
- [Evidence Index and Lab Notebook](docs/EVIDENCE_INDEX.md)
- [Challenge Submission Package](docs/CHALLENGE_SUBMISSION_PACKAGE.md)
- [Contributing](CONTRIBUTING.md)
- [Security Policy and private vulnerability reporting](SECURITY.md)
- [Visual Asset Ledger](docs/evidence/assets/README.md)

## A note from the creator

> I wanted to build something impressive, useful, and sustainable. I started with the problem and worked backwards from there: lots of sites are still on WordPress, and I wanted a one-click way for my agent to work there safely.
>
> I orchestrated the work, guided the architecture, and ran the live tests shown in the demo. AI wrote code, but this solves a real-world problem I face every day. I hope people find real use for it—I'll be using it on my own sites.
>
> Cheers, and thanks to all the sponsors of the WebMCP Hackathon!
>
> — **Craig Burton**

## License

AgentPress is open-source software licensed under the [GNU General Public License v2.0 or later](LICENSE).
