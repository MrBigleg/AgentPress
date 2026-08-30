# AgentPress

**The shared human-agent workspace for WordPress.**

AgentPress is an open-source WordPress plugin concept that lets ChatGPT work inside the WordPress session a human is already using. WordPress defines the user's maximum authority; AgentPress narrows that authority into actions the agent may perform automatically, actions requiring explicit approval, and actions that remain blocked.

> AgentPress is currently at the product-requirements stage. No production plugin has been released yet.

## Product principles

- Use the logged-in WordPress identity—no copied API keys or application passwords.
- Build capabilities on the WordPress Abilities API so core behavior remains protocol-independent.
- Allow safe, reversible work such as reading and creating drafts.
- Stage consequential changes such as publishing and navigation edits for approval in wp-admin.
- Revalidate authorization server-side for every execution.
- Keep a visible, sanitized audit trail of agent activity.
- Never expose sensitive administration, arbitrary code execution, credentials, or privilege escalation.

## V0.1 reference flow

```text
ChatGPT Site Tools
        ↓
WebMCP bridge
        ↓
AgentPress policy, Change Sets, approvals, and audit
        ↓
WordPress Abilities API
        ↓
WordPress capabilities and application logic
```

The canonical workflow is to inspect a site, create content drafts, assign existing taxonomy, stage a navigation change, approve it visibly in WordPress, and verify that a restricted Author account cannot exceed its permissions.

## Documentation

- [Product requirements](docs/PRD.md)
- [Contributing](CONTRIBUTING.md)
- [Security policy](SECURITY.md)
- [Code of Conduct](CODE_OF_CONDUCT.md)

## Current status

The V0.1 PRD is build-ready and intentionally narrow. The immediate target is a dependable ChatGPT Site Tools → WordPress demonstration with a 5/5 successful canonical workflow. WooCommerce, page builders, SEO plugins, classic MCP, remote automation, user administration, plugin administration, and proprietary AI are outside V0.1.

## Contributing

Discussion and focused contributions are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md) before proposing implementation changes. Security issues should be reported privately as described in [SECURITY.md](SECURITY.md).

## License

AgentPress is licensed under the [GNU General Public License v2.0 or later](LICENSE).
