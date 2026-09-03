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

1. Upload the `agentpress` directory or release ZIP through Plugins > Add New.
2. Activate AgentPress.
3. No external account, API key, or MCP configuration is required.

== Upgrade Notice ==

= 0.1.0 =
Back up WordPress before upgrading. Upload the new ZIP through Plugins > Add New > Upload Plugin, choose Replace current with uploaded, then confirm AgentPress remains active. AgentPress preserves its Change Sets and audit tables during plugin replacement.

== Changelog ==

= 0.1.0 =
* Initial release with 15 AgentPress Abilities, permission-aware reads and writes, immutable Change Sets, human approval, classic-menu support, and sanitized activity history.
