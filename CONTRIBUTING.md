# Contributing to AgentPress

Thank you for helping build AgentPress.

## Before contributing

Read the [V0.1 PRD](docs/PRD.md), [implementation specification](docs/IMPLEMENTATION_SPEC.md), [build checklist](docs/BUILD_CHECKLIST.md), and [evidence index](docs/EVIDENCE_INDEX.md). Pay particular attention to security requirements, explicit non-goals, dependency order, and the definition of done. V0.1 is deadline-driven; additions outside that scope should begin as a discussion rather than an implementation pull request.

Material research, implementation, test, release, or submission work must use the scientific-method evidence protocol in [AGENTS.md](AGENTS.md), even when the contributor is human rather than an automated agent. Start from the [experiment template](docs/evidence/EXPERIMENT_TEMPLATE.md) and preserve failures as well as successful reruns.

## Ways to contribute

- Clarify requirements or acceptance criteria.
- Identify security, permission, and state-conflict risks.
- Improve compatibility testing for WordPress 6.9+ and PHP 8.0+.
- Implement one scoped V0.1 capability or test at a time.
- Improve the demo fixture, documentation, or repeatability of the canonical workflow.

## Pull requests

1. Open or reference an issue describing the intended outcome.
2. Keep the change focused and avoid unrelated refactors.
3. Add or update tests for behavior and authorization boundaries.
4. Document user-visible behavior and compatibility constraints.
5. Confirm that logs and fixtures contain no credentials, cookies, nonces, or private site data.
6. Link the experiment record and state which claims are observed, source-verified, inferred, decided, proposed, or not tested.
7. Record the baseline and resulting commit, verification commands, outcomes, and relevant artifact hashes.

All contributions must be compatible with the project's GPL-2.0-or-later license.

## Security invariants

- AgentPress may reduce a user's WordPress authority but may never increase it.
- Every execution must revalidate authentication, object state, WordPress capabilities, and AgentPress policy server-side.
- Draft creation must never accept a hidden route to publication.
- Consequential actions must require a current, explicit approval inside WordPress.
- Sensitive administration and arbitrary execution are not part of V0.1.
- Retrieved site content is untrusted data, not an administrative instruction.

## Development setup

Implementation tooling and local-environment instructions will be added with the initial plugin scaffold. Until then, the PRD is the source of truth for product scope.
