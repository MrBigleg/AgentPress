import assert from 'node:assert/strict';
import test from 'node:test';

import { summarizeOverview } from '../../admin/src/admin-overview.mjs';

const context = ({ capabilities, blocked = ['users', 'plugins'], role = 'author' }) => ({
  ok: true,
  data: {
    site: { title: 'AgentPress Fixture', wordpress_version: '6.9' },
    user: { id: 7, display_name: 'Fixture User', roles: [role] },
    capabilities,
    blocked_areas: blocked,
  },
});

const diagnostics = (overrides = {}) => ({
  https: true,
  wordpress: true,
  abilitiesApi: true,
  webmcp: true,
  ...overrides,
});

test('Administrator active overview counts only registered tools and separates blocked areas', () => {
  const model = summarizeOverview({
    context: context({ role: 'administrator', capabilities: {
      read_site: { state: 'automatic', reason: '' },
      create_post_draft: { state: 'automatic', reason: '' },
      publish_own_content: { state: 'approval_required', reason: '' },
      create_page_draft: { state: 'unavailable', reason: 'Page creation is unavailable.' },
    } }),
    definitions: [{ ability: 'a' }, { ability: 'b' }, { ability: 'c' }],
    registration: { supported: true, count: 3 },
    diagnostics: diagnostics(),
  });

  assert.equal(model.state, 'active');
  assert.deepEqual(model.user.roles, ['administrator']);
  assert.equal(model.exposedCount, 3);
  assert.equal(model.eligibleCount, 3);
  assert.equal(model.automatic, 2);
  assert.equal(model.approvals, 1);
  assert.deepEqual(model.blocked, ['users', 'plugins']);
});

test('Author overview is degraded when WebMCP is unavailable', () => {
  const model = summarizeOverview({
    context: context({ capabilities: { read_site: { state: 'automatic', reason: '' } } }),
    definitions: [{ ability: 'agentpress/get-context' }],
    registration: { supported: false, count: 0 },
    diagnostics: diagnostics({ webmcp: false }),
  });

  assert.equal(model.state, 'degraded');
  assert.deepEqual(model.user.roles, ['author']);
  assert.equal(model.exposedCount, 0);
  assert.equal(model.eligibleCount, 1);
});

test('Subscriber no-tool response produces the empty state', () => {
  const model = summarizeOverview({
    context: context({ role: 'subscriber', capabilities: {
      read_site: { state: 'unavailable', reason: 'Read access is required.' },
      create_post_draft: { state: 'unavailable', reason: 'Post creation is unavailable.' },
    } }),
    definitions: [],
    registration: { supported: true, count: 0 },
    diagnostics: diagnostics(),
  });

  assert.equal(model.state, 'empty');
  assert.deepEqual(model.user.roles, ['subscriber']);
  assert.ok(model.capabilityRows.every(({ humanAllowed }) => humanAllowed === false));
});

test('blocked areas are deduplicated and never affect counts', () => {
  const model = summarizeOverview({
    context: context({ capabilities: { read_site: { state: 'automatic', reason: '' } }, blocked: ['users', 'users', 'themes'] }),
    definitions: [{ ability: 'agentpress/get-context' }],
    registration: { supported: true, count: 1 },
    diagnostics: diagnostics(),
  });

  assert.deepEqual(model.blocked, ['users', 'themes']);
  assert.equal(model.automatic, 1);
  assert.equal(model.exposedCount, 1);
});
