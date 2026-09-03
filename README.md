# AgentPress

**The permission-aware WebMCP control layer for WordPress.**

<p align="center">
  <a href="https://agentpress-webmcp.bigleg.chatgpt.site/">
    <img src="docs/evidence/assets/approved/agentpress-approved-gradient-wordmark.png" alt="AgentPress" width="560">
  </a>
</p>

<p align="center">
  <a href="https://youtu.be/DJs68ZSfrBA"><strong>Watch the Demo (02:17)</strong></a>
  &nbsp;•&nbsp;
  <a href="https://devpost.com/software/agentpress"><strong>Devpost Entry</strong></a>
  &nbsp;•&nbsp;
  <a href="https://agentpress-webmcp.bigleg.chatgpt.site/"><strong>Interactive Concept</strong></a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/WebMCP-Native_Adapter-blue" alt="WebMCP">
  <img src="https://img.shields.io/badge/WordPress-6.9+_%2F_7.1-21759b" alt="WordPress">
  <img src="https://img.shields.io/badge/Security-No_API_Keys-green" alt="No API Keys">
  <img src="https://img.shields.io/badge/Release_Candidate-v0.1-brightgreen" alt="v0.1">
  <img src="https://img.shields.io/badge/License-GPL--2.0--or--later-orange" alt="GPL-2.0">
</p>

---

## The Problem & The WebMCP Solution

WordPress powers over 40% of the web. But letting an AI agent interact with a WordPress site has always required an unacceptable security trade-off: hand over an unconstrained administrator API key, create permanent application passwords, or rely on slow, brittle browser screen-scraping.

**AgentPress inverts that model using [WebMCP](https://github.com/webmachinelearning/webmcp).**

Instead of granting an agent independent, credentialed access to your server, AgentPress lets ChatGPT work directly inside the authenticated browser session you are already using. 

WordPress defines the human user's maximum authority. AgentPress strictly narrows that authority:
* **No API keys or application passwords:** The agent operates exclusively through your active, authenticated login.
* **Safe operations happen automatically:** Inspect site structure, read content styles, and create unpublished drafts.
* **Consequential operations wait for approval:** Publishing posts, modifying navigation menus, and adding taxonomy terms are staged in wp-admin for explicit human sign-off.
* **Dangerous areas are strictly blocked:** Users, plugins, themes, core code, credentials, and settings are never exposed as tools.

---

## How It Works

AgentPress operates as a secure bridge between the browser's model context and the WordPress core:

```text
ChatGPT / AI Agent (in-browser)
       │
       ▼  document.modelContext.registerTool
AgentPress WebMCP Browser Adapter
       │
       ▼  Private same-origin REST transport (session cookie + REST nonce)
AgentPress Policy & Safe Mode Engine
       │  ├─ Checks live user capabilities
       │  ├─ Validates strict closed schemas
       │  ├─ Idempotent Change Set coordinator (AP-1)
       │  └─ Sanitized audit logging (secrets scrubbed)
       ▼
WordPress Abilities API (agentpress/* catalog)
       │
       ▼
WordPress Core (Posts, Pages, Terms, Menus)
```

### The Three Tiers of Authority

| Tier | Capabilities | Policy Boundary |
| :--- | :--- | :--- |
| 🟢 **Automatic** | `get-context`, `get-site-structure`, `list-content`, `get-content`, `list-terms`, `create-draft` | Reversible and safe. Drafts are forced to `draft` status under an idempotent **Change Set** (`AP-1`). |
| 🟡 **Approval Required** | `publish-content`, `update-navigation`, `create-term`, `assign-terms` | Consequential actions. Staged for explicit human review in the native wp-admin collaboration interface. |
| 🔴 **Always Blocked** | Users, plugins, themes, core code, credentials, options/settings | Never registered or exposed to WebMCP. Hard-blocked at the server boundary. |

---

## Live Verification & Evidence

AgentPress was built following a strict, reproducible evidence protocol. Every capability was developed against falsifiable hypotheses and verified with passing acceptance tests:

* **Live Client Proof ([EXP-029](docs/evidence/sessions/2026-09-02-exp-029-service-page-draft-demo.md)):** Tested in the Codex / ChatGPT in-app browser on a live WordPress 7.1 HTTPS site. In one request, ChatGPT inspected site structure (15 pages) and created a 463-word service page draft (*Homebuyer Drain Surveys in Poole*).
  * **Verified boundary:** Visible page count moved from 15 to 16, draft ID 97 was created, all 15 pre-existing page metadata records remained 100% identical, and zero pages were published.
* **Native wp-admin Collaboration UI ([EXP-023](docs/evidence/sessions/2026-09-01-exp-023-admin-overview.md), [EXP-039](docs/evidence/sessions/2026-09-03-exp-039-changes-activity-ui.md)):** Dedicated admin screens provide real-time connection health, WebMCP bridge state, pending Change Set reviews, and a sanitized audit log.
* **Reproducible Release Package ([EXP-042](docs/evidence/sessions/2026-09-03-exp-042-release-package.md)):** Deterministic 67-entry release ZIP with verified SHA-256 checksum (`234A1981C8D15DE97E125F064170BC868448134DEBFC5A8541F78090AC88B97F`) that installs and activates cleanly on WordPress 6.9+ and PHP 8.0+.

---

## Quickstart

### Option 1: Try with ChatGPT / WebMCP Browser
1. Log in to your WordPress site inside ChatGPT's built-in browser (or Chrome with WebMCP enabled).
2. Open **AgentPress** in the WordPress admin to verify connection health shows **Ready**.
3. In chat, prompt your agent:
   > *"Use AgentPress to inspect this site's structure, then draft a new service page about home drain surveys. Do not publish it."*
4. The agent discovers the tools, inspects the site, creates the draft, and verifies the result.

### Option 2: Local Development (`wp-env`)
Run a fully configured WordPress 6.9 test environment with one command:

```bash
# Clone the repository
git clone https://github.com/MrBigleg/AgentPress.git
cd AgentPress

# Install dependencies and start WordPress
npm install
npm run env:start

# Activate AgentPress
npm run env:activate
```

* **WordPress URL:** `http://localhost:8888`
* **Username:** `admin` | **Password:** `password`
* **AgentPress Dashboard:** `http://localhost:8888/wp-admin/admin.php?page=agentpress`

---

## 💡 A Note from the Creator

> *"I wanted to build something impressive, useful, and sustainable. I started with the problem and worked backwards from there:*
> 
> *'Lots of sites are still on WordPress. I really want a one-click solution for my agent to jump right in and work there safely.'*
> 
> *I was here to orchestrate, guide the architecture, run all the working live tests (linked in the demo video), and click commit along the way. AI wrote code, but this solves a real-world problem I face every day. I hope people find real use for this—it's well beyond a novelty, and I'll be using it on my own sites.*
> 
> *Cheers, and thanks to all the sponsors of the WebMCP Hackathon!"*  
> — **Craig Burton**

---

## Documentation & Evidence

* [Product Requirements Document (PRD)](docs/PRD.md)
* [Implementation Specification](docs/IMPLEMENTATION_SPEC.md)
* [Dependency-Ordered Build Checklist](docs/BUILD_CHECKLIST.md)
* [Evidence Index & Lab Notebook](docs/EVIDENCE_INDEX.md)
* [Challenge Submission Package](docs/CHALLENGE_SUBMISSION_PACKAGE.md)
* [Visual Asset Ledger](docs/evidence/assets/README.md)

## License

AgentPress is open-source software licensed under the [GNU General Public License v2.0 or later](LICENSE).
