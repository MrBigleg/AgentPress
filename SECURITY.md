# Security Policy

AgentPress is pre-release software and should not be installed on production WordPress sites.

## Reporting a vulnerability

Do not open a public issue for a suspected vulnerability. Use GitHub's private vulnerability reporting feature on this repository. Include:

- the affected component and version or commit;
- reproduction steps or a minimal proof of concept;
- the WordPress role and capabilities involved;
- the expected and actual authorization behavior;
- any suggested mitigation.

Please do not include real credentials, authentication cookies, REST nonces, personal data, or private site content.

## Security scope

High-priority reports include authentication or nonce bypasses, privilege escalation, missing object-specific capability checks, approval bypasses, stale-state overwrites, sensitive-data exposure, unsafe schema handling, and routes that allow draft creation to publish content.

## Supported versions

There is no supported production release yet. This policy will be updated when the first tagged release is available.
