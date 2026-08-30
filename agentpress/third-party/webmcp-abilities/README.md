# WebMCP Abilities provenance

AgentPress audits selected patterns from [Code Atlantic's WebMCP Abilities for WordPress](https://github.com/code-atlantic/webmcp-abilities) at commit [`ea5a2bcbe77dc13aafc95b4c83a18c9b11d85da6`](https://github.com/code-atlantic/webmcp-abilities/commit/ea5a2bcbe77dc13aafc95b4c83a18c9b11d85da6), upstream version 0.6.1.

`PROVENANCE.json` is the machine-readable source of truth. At AP-002, `copied_or_adapted_material` is empty: AgentPress packages attribution and the GPL text, but no upstream PHP, JavaScript, TypeScript, bootstrap, settings, generic tools, routes, cache, or client bundle.

The pinned source declares `GPL-2.0-or-later` in `composer.json`, `webmcp-abilities.php`, and `readme.txt`. Its README links to a repository `LICENSE`, but no such file exists in the pinned tree. The `LICENSE` beside this file is therefore the complete GNU GPL version 2 text supplied by AgentPress for compliance; it is not represented as a file retrieved from the upstream commit.

Later tasks may independently adapt the three concepts recorded in `PROVENANCE.json`. Any copied or adapted implementation must add an exact upstream path/blob and AgentPress target path before merge.
