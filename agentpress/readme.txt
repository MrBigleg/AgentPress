=== AgentPress ===
Contributors: agentpress
Tags: webmcp, abilities, ai, workflow, approvals
Requires at least: 6.9
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The shared human-agent workspace for WordPress.

== Description ==

AgentPress is an identity-aware, permission-aware collaboration and control layer for WordPress. Version 0.1 provides a fixed 15-Ability catalog, safe draft workflows, staged consequential changes, explicit wp-admin approval, and sanitized activity history.

== Installation ==

1. Download the installable ZIP (`dist/agentpress.zip` from this repository). Do not use GitHub's Code > Download ZIP source archive.
2. In wp-admin, open Plugins > Add New Plugin > Upload Plugin.
3. Choose `agentpress.zip`, select Install Now, and then activate AgentPress.
4. Open the AgentPress wp-admin page inside ChatGPT's desktop built-in browser and sign in to WordPress there.
5. Confirm the Overview reports Ready. No external account, API key, or MCP configuration is required.

== Upgrade Notice ==

= 0.1.0 =
Back up WordPress before upgrading. Upload the new ZIP through Plugins > Add New > Upload Plugin, choose Replace current with uploaded, then confirm AgentPress remains active. AgentPress preserves its Change Sets and audit tables during plugin replacement.

== Changelog ==

= 0.1.0 =
* Initial 0.1.0 release with 15 AgentPress Abilities, permission-aware reads and writes, immutable Change Sets, human approval, classic-menu support, and sanitized activity history.
