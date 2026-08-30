# AgentPress Visual Asset Ledger

This directory separates approved visual direction from concept/reference material. The classification comes from the project owner's explicit direction on 30 August 2026. Visuals are not evidence that the depicted UI, domain, workflow, or feature has been implemented.

## Approved

| Asset | Original filename | Size | SHA-256 | Classification |
|---|---|---:|---|---|
| [Approved identity system](approved/agentpress-approved-identity-system.png) | `AgentPress Aproved Design.png` | 1,544,974 bytes | `c070c3f96d3255bb928a17c3002e797504e8b04ee1895ae778eeacc2abe69c4b` | `DECIDED`: approved logo/identity direction. |
| [Approved gradient wordmark](approved/agentpress-approved-gradient-wordmark.png) | `AgentPress Tech Logo with Gradient Icon.png` | 321,369 bytes | `314161f7cf4c332d0d15a3d741c3c3b56d95c5eb9d532f450d8b468a79103edd` | `DECIDED`: approved standalone transparent wordmark supplied directly in the approved folder. |

The approved sheet covers the primary icon, alternate icon treatments, wordmark lockups, color guidance, spacing/radius guidance, and illustrative WordPress/browser applications. The separate gradient wordmark is a directly usable raster export. “Approved” applies to these supplied visual assets; vector/editable masters and production exports at other required sizes have not yet been verified.

## Concepts and references

| Asset | Original/observed filename | Size | SHA-256 | Intended use |
|---|---|---:|---|---|
| [Brand system concept](concepts/agentpress-brand-system-concept.png) | `AgentPress Human–Conecpt Ideas.png` | 1,418,301 bytes | `99210312052b5c14f4c954c865438b5c46f97c4bfabc85f93c130a950c8ba7c4` | Brand, color, typography, status, and human-agent motif reference. |
| [Hackathon asset concept A](concepts/agentpress-hackathon-assets-concept-a.png) | `AgentPress Human–AI Asset Showcase.png` | 1,678,455 bytes | `2d93b4fe2f473bcadc0a2c5cc9c458b84bce82fac66f15d778ea30d90fe9fdee` | Submission/banner/social/video composition concept with blue robot. |
| [Hackathon asset concept B](concepts/agentpress-hackathon-assets-concept-b.png) | first observed as `ChatGPT Image Aug 30, 2026, 09_02_51 AM (4).png`, then renamed `AgentPress Human–Conecpt Ideas2.png` before curation | 1,651,906 bytes | `053264caa38dde8a0125e2e8205765444b3d674d306041127cced3b3db698256` | Alternate submission/banner/social/video composition with white robot. |
| [Icon library concept](concepts/agentpress-icon-library-concept.png) | `AgentPress Human–Icon Library.png` | 1,527,907 bytes | `98fc2d515bf2f01bc38a2a0fc2b935f9e845353e6915d8c8b41786e3fa4780f8` | Visual icon/style reference only. Some tool names/statuses predate the final specification. |

Concept assets must not override [PRD v2](../../PRD.md), the [implementation specification](../../IMPLEMENTATION_SPEC.md), or verified product behavior. They may be used to guide future design, but a later experiment must record any promotion into implemented/approved UI.

## Provenance and use boundary

- `OBSERVED`: the project owner supplied the files in this repository and explicitly identified them as concept and approved images suitable for inclusion.
- `OBSERVED`: the images were visually inspected and moved/normalized without image editing into approved/concept folders during [EXP-003](../sessions/2026-08-30-exp-003-visual-assets.md).
- `NOT_TESTED`: original editable source files, font licenses, generator settings/prompts, independent authorship chain, and exact creation tool for every sheet.
- `NOT_TESTED`: whether `agentpress.dev` shown inside the compositions is owned, configured, or live. It is illustrative text, not deployment evidence.
- Before public competition submission, the project owner should confirm publication rights and any required AI-generated-content disclosure for the selected assets.

Do not store private credentials, browser state, or private site content in visual evidence.
