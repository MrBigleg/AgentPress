import assert from 'node:assert/strict';
import test from 'node:test';

import {
  approvalErrorMessage,
  createActivityPoller,
  createChangeActionRunner,
  mergeActivityEvents,
  renderActivityTable,
  renderChangeDetail,
  renderChangeList,
  summarizeOverview,
} from '../../admin/src/admin-overview.mjs';

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

test('semantic diffs and activity fields are escaped instead of rendered as HTML', () => {
  const detail = renderChangeDetail({
    reference: 'AP-2', title: '<img src=x onerror=alert(1)>', status: 'READY_FOR_REVIEW',
    changes: [{ id: 9, reference: 'AP-C-9', operation: 'update', object_type: 'post', object_id: 4, status: 'PENDING_APPROVAL', semantic_before: '<script>old()</script>', semantic_after: '<b>new</b>' }],
  });
  const activity = renderActivityTable([{ id: 7, created_gmt: 'now', result: 'FAILED', error_code: '<svg/onload=x>', ability: '<b>ability</b>', object_type: 'post', object_id: 4, change_set_id: 2 }]);

  assert.doesNotMatch(detail, /<script>|<img|<b>new/);
  assert.match(detail, /&lt;script&gt;old\(\)&lt;\/script&gt;/);
  assert.doesNotMatch(activity, /<svg|<b>ability/);
  assert.match(activity, /&lt;svg\/onload=x&gt;/);
});

test('Changes renders empty and pending list states without exposing actions in the list', () => {
  assert.match(renderChangeList([]), /No changes to review/);
  const pending = renderChangeList([{ id: 2, reference: 'AP-2', title: 'Review me', status: 'READY_FOR_REVIEW', pending_count: 1, updated_gmt: 'now' }]);
  assert.match(pending, /1 pending/);
  assert.match(pending, /data-change-set-id="2"/);
  assert.doesNotMatch(pending, /data-change-action/);
});

test('approval runner requires an explicit call and suppresses a double click', async () => {
  let requests = 0;
  let refreshes = 0;
  let release;
  const gate = new Promise((resolve) => { release = resolve; });
  const runner = createChangeActionRunner({
    request: async () => { requests += 1; await gate; },
    refresh: async () => { refreshes += 1; },
  });

  assert.equal(requests, 0);
  const first = runner.run(12, 'approve');
  const second = await runner.run(12, 'approve');
  assert.equal(second, false);
  assert.equal(requests, 1);
  assert.equal(runner.isInFlight(12), true);
  release();
  assert.equal(await first, true);
  assert.equal(refreshes, 1);
  assert.equal(runner.isInFlight(12), false);
});

test('approval runner sends rejection only when explicitly invoked', async () => {
  const actions = [];
  const runner = createChangeActionRunner({ request: async (id, action) => actions.push([id, action]), refresh: async () => {} });
  assert.deepEqual(actions, []);
  assert.equal(await runner.run(13, 'reject'), true);
  assert.deepEqual(actions, [[13, 'reject']]);
});

test('Activity distinguishes draft, staged, and applied outcomes', () => {
  const html = renderActivityTable([
    { id: 1, created_gmt: 'now', result: 'SUCCESS', ability: 'agentpress/create-draft', object_type: 'post', object_id: 1 },
    { id: 2, created_gmt: 'now', result: 'PENDING', ability: 'agentpress/update-content', object_type: 'post', object_id: 1 },
    { id: 3, created_gmt: 'now', result: 'SUCCESS', ability: 'agentpress/update-content', object_type: 'post', object_id: 1 },
  ]);
  assert.match(html, /Draft created/);
  assert.match(html, /Staged/);
  assert.match(html, /Applied/);
});

test('approval failures explain permission loss, conflicts, and expiry', () => {
  assert.match(approvalErrorMessage({ code: 'AP_PERMISSION_DENIED' }), /permission changed/i);
  assert.match(approvalErrorMessage({ code: 'AP_STATE_CONFLICT' }), /changed after/i);
  assert.match(approvalErrorMessage({ code: 'AP_CHANGE_EXPIRED' }), /expired/i);
});

test('activity merge keeps newest-first unique events', () => {
  const merged = mergeActivityEvents([{ id: 2, result: 'PENDING' }, { id: 1 }], [{ id: 2, result: 'SUCCESS' }, { id: 3 }]);
  assert.deepEqual(merged.map(({ id }) => id), [3, 2, 1]);
  assert.equal(merged[1].result, 'SUCCESS');
});

test('activity polling pauses while hidden, resumes visibly, and advances its cursor', async () => {
  const listeners = new Map();
  const documentRef = {
    visibilityState: 'hidden',
    addEventListener: (name, listener) => listeners.set(name, listener),
    removeEventListener: (name) => listeners.delete(name),
  };
  const timers = [];
  const cursors = [];
  const batches = [];
  const poller = createActivityPoller({
    documentRef,
    intervalMs: 5000,
    setTimer: (callback, delay) => { timers.push({ callback, delay }); return timers.length; },
    clearTimer: () => {},
    poll: async (cursor) => { cursors.push(cursor); return { items: [{ id: 8 }], cursor: 8 }; },
    onEvents: (items) => batches.push(items),
  });
  poller.start(7);
  assert.equal(timers[0].delay, 5000);
  await poller.tick();
  assert.equal(cursors.length, 0);
  documentRef.visibilityState = 'visible';
  await listeners.get('visibilitychange')();
  await new Promise((resolve) => setImmediate(resolve));
  assert.deepEqual(cursors, [7]);
  assert.equal(batches.length, 1);
  assert.equal(poller.getCursor(), 8);
  poller.stop();
});
