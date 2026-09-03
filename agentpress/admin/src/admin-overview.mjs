import {
  createJsonFetchExecutor,
  createRestNonceManager,
  fetchAgentPressDefinitions,
  fetchAgentPressJson,
  registerAgentPressTools,
} from './webmcp-adapter.mjs';

const CAPABILITY_LABELS = Object.freeze({
  read_site: 'Read site context', read_content: 'Read content', create_post_draft: 'Create post drafts',
  create_page_draft: 'Create page drafts', edit_own_agentpress_draft: 'Edit AgentPress drafts',
  edit_other_draft: 'Edit other drafts', edit_published_content: 'Edit published content',
  publish_own_content: 'Publish own content', publish_others_content: 'Publish others’ content',
  list_terms: 'Read categories and tags', create_terms: 'Create categories and tags',
  assign_terms: 'Assign existing terms', read_navigation: 'Read navigation',
  modify_navigation: 'Modify navigation', read_change_sets: 'Read change sets', read_activity: 'Read activity',
});
const STATE_LABELS = Object.freeze({ automatic: 'Automatic', approval_required: 'Approval', unavailable: 'Unavailable' });
const RESULT_LABELS = Object.freeze({ SUCCESS: 'Applied', PENDING: 'Staged', REJECTED: 'Rejected', DENIED: 'Denied', FAILED: 'Failed', REPLAYED: 'Replayed' });

export function summarizeOverview({ context, definitions, registration, diagnostics }) {
  const capabilities = context?.data?.capabilities ?? {};
  const capabilityRows = Object.entries(capabilities).map(([key, value]) => ({
    key, label: CAPABILITY_LABELS[key] ?? key.replaceAll('_', ' '), humanAllowed: value?.state !== 'unavailable',
    state: STATE_LABELS[value?.state] ? value.state : 'unavailable', reason: typeof value?.reason === 'string' ? value.reason : '',
  }));
  const tools = Array.isArray(definitions) ? definitions : [];
  const exposedCount = registration?.supported ? Number(registration.count) || 0 : 0;
  return Object.freeze({
    state: exposedCount === 0 && tools.length === 0 ? 'empty' : Object.values(diagnostics).some((value) => value !== true) ? 'degraded' : 'active',
    site: context?.data?.site ?? {}, user: context?.data?.user ?? {}, capabilityRows,
    automatic: capabilityRows.filter(({ state }) => state === 'automatic').length,
    approvals: capabilityRows.filter(({ state }) => state === 'approval_required').length,
    blocked: Array.isArray(context?.data?.blocked_areas) ? [...new Set(context.data.blocked_areas.map(String))] : [],
    eligibleCount: tools.length, exposedCount, diagnostics,
  });
}

export function escapeHtml(value) {
  return String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
}

export function mergeActivityEvents(current, incoming) {
  const events = new Map();
  for (const event of [...(Array.isArray(current) ? current : []), ...(Array.isArray(incoming) ? incoming : [])]) {
    const id = Number(event?.id);
    if (Number.isInteger(id) && id > 0) events.set(id, event);
  }
  return [...events.values()].sort((a, b) => Number(b.id) - Number(a.id));
}

export function approvalErrorMessage(error) {
  const messages = {
    AP_PERMISSION_DENIED: 'Your permission changed. The change was not applied.',
    AP_STATE_CONFLICT: 'The WordPress item changed after this proposal was created. Refresh and review the current state.',
    AP_CHANGE_EXPIRED: 'This proposal expired and can no longer be applied.',
    AP_CHANGE_NOT_FOUND: 'This proposal is no longer available.',
  };
  return messages[error?.code] ?? error?.message ?? 'The change could not be completed.';
}

export function createChangeActionRunner({ request, refresh }) {
  const inFlight = new Set();
  return Object.freeze({
    isInFlight: (id) => inFlight.has(Number(id)),
    run: async (id, action) => {
      const numericId = Number(id);
      if (!Number.isInteger(numericId) || numericId <= 0 || !['approve', 'reject'].includes(action) || inFlight.has(numericId)) return false;
      inFlight.add(numericId);
      try { await request(numericId, action); await refresh(numericId); return true; }
      finally { inFlight.delete(numericId); }
    },
  });
}

export function createActivityPoller({ poll, onEvents, onError = () => {}, documentRef = globalThis.document, intervalMs = 5000, setTimer = setTimeout, clearTimer = clearTimeout }) {
  let cursor = 0; let timer = null; let stopped = false; let running = false;
  const schedule = () => { if (!stopped) timer = setTimer(tick, intervalMs); };
  const tick = async () => {
    if (stopped) return;
    if (documentRef?.visibilityState === 'hidden' || running) { schedule(); return; }
    running = true;
    try {
      const payload = await poll(cursor);
      const items = Array.isArray(payload?.items) ? payload.items : [];
      cursor = Math.max(cursor, Number(payload?.cursor) || 0, ...items.map((item) => Number(item.id) || 0));
      if (items.length) onEvents(items, cursor);
    } catch (error) { onError(error); }
    finally { running = false; schedule(); }
  };
  const visibility = () => { if (documentRef?.visibilityState !== 'hidden' && !running) { if (timer !== null) clearTimer(timer); tick(); } };
  documentRef?.addEventListener?.('visibilitychange', visibility);
  return Object.freeze({
    start(initialCursor = 0) { cursor = Math.max(0, Number(initialCursor) || 0); schedule(); },
    stop() { stopped = true; if (timer !== null) clearTimer(timer); documentRef?.removeEventListener?.('visibilitychange', visibility); },
    tick, getCursor: () => cursor,
  });
}

function statePill(state) { return `<span class="agentpress-pill agentpress-pill--${escapeHtml(state)}">${escapeHtml(STATE_LABELS[state] ?? state)}</span>`; }
function panelLoading(label) { return `<div class="agentpress-panel-loading" role="status"><span class="spinner is-active"></span>${escapeHtml(label)}</div>`; }

export function renderChangeList(items) {
  if (!items.length) return '<div class="agentpress-empty agentpress-empty--panel"><span class="dashicons dashicons-yes-alt"></span><h2>No changes to review</h2><p>New staged work will appear here.</p></div>';
  return `<div class="agentpress-change-list">${items.map((item) => `<button type="button" class="agentpress-change-set" data-change-set-id="${Number(item.id)}"><span><strong>${escapeHtml(item.title || item.reference)}</strong><small>${escapeHtml(item.reference)} · ${escapeHtml(item.updated_gmt)}</small></span><span class="agentpress-status agentpress-status--${escapeHtml(String(item.status).toLowerCase())}">${escapeHtml(item.status)}</span><b>${Number(item.pending_count) || 0} pending</b></button>`).join('')}</div>`;
}

export function renderChangeDetail(data, inFlight = new Set()) {
  const changes = Array.isArray(data?.changes) ? data.changes : [];
  return `<div class="agentpress-detail-heading"><div><p>${escapeHtml(data?.reference)}</p><h3>${escapeHtml(data?.title || 'Change Set')}</h3></div><span>${escapeHtml(data?.status)}</span></div>${changes.map((change) => {
    const pending = change.status === 'PENDING_APPROVAL'; const disabled = inFlight.has(Number(change.id));
    return `<article class="agentpress-change"><header><strong>${escapeHtml(change.operation)} ${escapeHtml(change.object_type)} #${Number(change.object_id) || 'new'}</strong><span>${escapeHtml(change.reference)} · ${escapeHtml(change.status)}</span></header><div class="agentpress-diff"><div><small>Current</small><pre><code>${escapeHtml(change.semantic_before || 'No existing value')}</code></pre></div><div><small>Proposed</small><pre><code>${escapeHtml(change.semantic_after || 'No proposed value')}</code></pre></div></div>${pending ? `<footer><button type="button" class="button" data-change-action="reject" data-change-id="${Number(change.id)}" ${disabled ? 'disabled' : ''}>Reject</button><button type="button" class="button button-primary" data-change-action="approve" data-change-id="${Number(change.id)}" ${disabled ? 'disabled' : ''}>Approve</button></footer>` : ''}</article>`;
  }).join('') || '<p class="agentpress-muted">This Change Set has no changes.</p>'}`;
}

export function renderActivityTable(items) {
  if (!items.length) return '<div class="agentpress-empty agentpress-empty--panel"><span class="dashicons dashicons-list-view"></span><h2>No activity yet</h2><p>Sanitized events will appear here.</p></div>';
  return `<div class="agentpress-table-scroll"><table class="agentpress-activity-table"><thead><tr><th>Time</th><th>Outcome</th><th>Operation</th><th>Object</th><th>Change</th></tr></thead><tbody>${items.map((item) => {
    const label = item.result === 'SUCCESS' && item.ability === 'agentpress/create-draft' ? 'Draft created' : (RESULT_LABELS[item.result] ?? item.result);
    return `<tr><td>${escapeHtml(item.created_gmt)}</td><td><span class="agentpress-result agentpress-result--${escapeHtml(String(item.result).toLowerCase())}">${escapeHtml(label)}</span>${item.error_code ? `<small>${escapeHtml(item.error_code)}</small>` : ''}</td><td>${escapeHtml(item.ability)}</td><td>${escapeHtml(item.object_type)}${Number(item.object_id) > 0 ? ` #${Number(item.object_id)}` : ''}</td><td>${Number(item.change_set_id) > 0 ? `AP-${Number(item.change_set_id)}` : '—'}</td></tr>`;
  }).join('')}</tbody></table></div>`;
}

function renderModel(root, model) {
  const diagnosticLabels = { https: 'HTTPS', wordpress: 'WordPress 6.9+', abilitiesApi: 'Abilities API', webmcp: 'WebMCP bridge' };
  const diagnostics = Object.entries(model.diagnostics).map(([key, ok]) => `<li class="${ok ? 'is-good' : 'is-bad'}"><span aria-hidden="true"></span>${escapeHtml(diagnosticLabels[key] ?? key)}<strong>${ok ? 'Ready' : 'Needs attention'}</strong></li>`).join('');
  const rows = model.capabilityRows.map(({ label, humanAllowed, state, reason }) => `<tr><th scope="row">${escapeHtml(label)}</th><td><span class="agentpress-human ${humanAllowed ? 'is-allowed' : 'is-limited'}">${humanAllowed ? 'Allowed' : 'Unavailable'}</span></td><td>${statePill(state)}${reason ? `<small>${escapeHtml(reason)}</small>` : ''}</td></tr>`).join('');
  const blocked = model.blocked.map((area) => `<li><span class="dashicons dashicons-lock" aria-hidden="true"></span>${escapeHtml(area)}</li>`).join('');
  const role = Array.isArray(model.user.roles) ? model.user.roles.join(', ') : '';
  const stateCopy = model.state === 'active' ? 'Your signed-in WordPress session is ready for AgentPress.' : model.state === 'empty' ? 'This account has no discoverable AgentPress tools.' : 'AgentPress is available with one or more environment limitations.';
  root.innerHTML = `<header class="agentpress-hero"><div class="agentpress-brand-mark" aria-hidden="true"><i></i><b>AP</b></div><div><p class="agentpress-kicker">Human-controlled WordPress automation</p><h1>AgentPress</h1><p>${escapeHtml(stateCopy)}</p></div><div class="agentpress-session"><span>${escapeHtml(model.user.display_name || 'WordPress user')}</span><small>${escapeHtml(role)}</small></div></header>
    <nav class="agentpress-tabs" aria-label="AgentPress sections"><button type="button" class="is-active" aria-current="page" data-tab="overview">Overview</button><button type="button" data-tab="changes">Changes <span class="agentpress-tab-count" data-pending-count hidden>0</span></button><button type="button" data-tab="activity">Activity</button></nav>
    <main><section class="agentpress-panel is-visible" data-panel="overview"><div class="agentpress-status-grid"><article class="agentpress-readout"><span>Tools exposed now</span><strong>${model.exposedCount}</strong><small>${model.eligibleCount} permitted by WordPress</small></article><article class="agentpress-readout"><span>Automatic operations</span><strong>${model.automatic}</strong><small>Within current authority</small></article><article class="agentpress-readout"><span>Approval operations</span><strong>${model.approvals}</strong><small>Require a human click</small></article><article class="agentpress-readout agentpress-readout--site"><span>Connected site</span><strong>${escapeHtml(model.site.title || 'WordPress')}</strong><small>WordPress ${escapeHtml(model.site.wordpress_version || '')}</small></article></div><div class="agentpress-overview-grid"><section class="agentpress-card agentpress-card--matrix"><div class="agentpress-card-heading"><div><p>Control circuit</p><h2>Who can do what</h2></div><span>Live capability envelope</span></div><div class="agentpress-table-scroll"><table><thead><tr><th>Operation</th><th>Human authority</th><th>AgentPress outcome</th></tr></thead><tbody>${rows}</tbody></table></div></section><aside class="agentpress-side-stack"><section class="agentpress-card"><div class="agentpress-card-heading"><div><p>Diagnostics</p><h2>Connection</h2></div></div><ul class="agentpress-diagnostics">${diagnostics}</ul></section><section class="agentpress-card agentpress-card--blocked"><div class="agentpress-card-heading"><div><p>Policy boundary</p><h2>Always blocked</h2></div></div><p>These areas are not tools and are not included in the exposed count.</p><ul>${blocked}</ul></section></aside></div></section>
    <section class="agentpress-panel" data-panel="changes"><div class="agentpress-workspace"><section class="agentpress-card"><div class="agentpress-card-heading"><div><p>Human review</p><h2>Changes</h2></div><button type="button" class="button" data-refresh-changes>Refresh</button></div><div data-change-list>${panelLoading('Loading changes')}</div></section><section class="agentpress-card agentpress-change-detail" data-change-detail><div class="agentpress-empty agentpress-empty--panel"><span class="dashicons dashicons-clipboard"></span><h2>Select a Change Set</h2><p>Review its semantic diff before approving or rejecting.</p></div></section></div><div class="agentpress-notice" data-change-notice role="status" hidden></div></section>
    <section class="agentpress-panel" data-panel="activity"><section class="agentpress-card"><div class="agentpress-card-heading"><div><p>Sanitized audit</p><h2>Activity</h2></div><div class="agentpress-filters"><label>Outcome <select data-activity-result><option value="">All</option>${Object.keys(RESULT_LABELS).map((result) => `<option>${result}</option>`).join('')}</select></label><label>Change Set <input type="number" min="1" data-activity-set></label><button type="button" class="button" data-refresh-activity>Refresh</button></div></div><div data-activity-list>${panelLoading('Loading activity')}</div></section><div class="agentpress-notice" data-activity-notice role="status" hidden></div></section></main>`;
  for (const tab of root.querySelectorAll('[data-tab]')) tab.addEventListener('click', () => {
    for (const item of root.querySelectorAll('[data-tab]')) { item.classList.toggle('is-active', item === tab); item.toggleAttribute('aria-current', item === tab); }
    for (const panel of root.querySelectorAll('[data-panel]')) panel.classList.toggle('is-visible', panel.dataset.panel === tab.dataset.tab);
  });
}

function showNotice(node, message, kind = 'success') {
  if (!node) return; node.hidden = false; node.className = `agentpress-notice agentpress-notice--${kind}`; node.textContent = message;
}

async function attachCollaboration({ root, settings, nonceManager, fetchImpl, documentRef }) {
  const request = (endpoint, options = {}) => fetchAgentPressJson({ endpoint, fetchImpl, getRequestInit: nonceManager.getRequestInit, refreshNonce: nonceManager.refreshNonce, ...options });
  const listNode = root.querySelector('[data-change-list]'); const detailNode = root.querySelector('[data-change-detail]');
  const activityNode = root.querySelector('[data-activity-list]'); const changeNotice = root.querySelector('[data-change-notice]');
  const activityNotice = root.querySelector('[data-activity-notice]'); let selectedSetId = 0; let activity = [];

  const loadChangeDetail = async (id) => {
    selectedSetId = Number(id); detailNode.innerHTML = panelLoading('Loading Change Set');
    try { const payload = await request(`${settings.changeSetsEndpoint}/${selectedSetId}`); detailNode.innerHTML = renderChangeDetail(payload.data, new Set()); }
    catch (error) { detailNode.innerHTML = `<div class="agentpress-inline-error" role="alert">${escapeHtml(approvalErrorMessage(error))}</div>`; }
  };
  const loadChanges = async () => {
    try {
      const payload = await request(`${settings.changeSetsEndpoint}?per_page=50`); const items = Array.isArray(payload?.data?.items) ? payload.data.items : [];
      listNode.innerHTML = renderChangeList(items); const pending = items.reduce((sum, item) => sum + (Number(item.pending_count) || 0), 0);
      const badge = root.querySelector('[data-pending-count]'); badge.textContent = String(pending); badge.hidden = pending === 0;
    } catch (error) { listNode.innerHTML = `<div class="agentpress-inline-error" role="alert">${escapeHtml(error.message)}</div>`; }
  };
  const renderActivity = () => {
    const result = root.querySelector('[data-activity-result]')?.value;
    const setId = Number(root.querySelector('[data-activity-set]')?.value);
    const visible = activity.filter((item) => (!result || item.result === result) && (!(setId > 0) || Number(item.change_set_id) === setId));
    activityNode.innerHTML = renderActivityTable(visible);
  };
  const loadActivity = async () => {
    try { const payload = await request(`${settings.activityEndpoint}?per_page=100`); activity = mergeActivityEvents([], payload?.data?.items); renderActivity(); }
    catch (error) { activityNode.innerHTML = `<div class="agentpress-inline-error" role="alert">${escapeHtml(error.message)}</div>`; }
  };
  const runner = createChangeActionRunner({
    request: (id, action) => request(`${settings.changesEndpoint}${id}/${action}`, { method: 'POST', body: action === 'reject' ? { reason: 'Rejected in AgentPress Changes.' } : {} }),
    refresh: async (id) => { await Promise.all([loadChanges(), loadChangeDetail(selectedSetId || id), loadActivity()]); },
  });
  root.addEventListener('click', async (event) => {
    const setButton = event.target.closest?.('[data-change-set-id]'); if (setButton) { await loadChangeDetail(setButton.dataset.changeSetId); return; }
    if (event.target.closest?.('[data-refresh-changes]')) { await loadChanges(); if (selectedSetId) await loadChangeDetail(selectedSetId); return; }
    if (event.target.closest?.('[data-refresh-activity]')) { await loadActivity(); return; }
    const actionButton = event.target.closest?.('[data-change-action]'); if (!actionButton) return;
    const id = Number(actionButton.dataset.changeId); if (runner.isInFlight(id)) return;
    for (const button of detailNode.querySelectorAll(`[data-change-id="${id}"]`)) button.disabled = true; changeNotice.hidden = true;
    try { const completed = await runner.run(id, actionButton.dataset.changeAction); if (completed) showNotice(changeNotice, actionButton.dataset.changeAction === 'approve' ? 'Change approved and current state refreshed.' : 'Change rejected and current state refreshed.'); }
    catch (error) { showNotice(changeNotice, approvalErrorMessage(error), 'error'); await loadChangeDetail(selectedSetId); }
  });
  root.querySelector('[data-activity-result]')?.addEventListener('change', renderActivity);
  root.querySelector('[data-activity-set]')?.addEventListener('input', renderActivity);
  await Promise.all([loadChanges(), loadActivity()]);
  const poller = createActivityPoller({
    documentRef, intervalMs: Math.min(10000, Math.max(1000, Number(settings.pollIntervalMs) || 5000)),
    poll: async (cursor) => (await request(`${settings.updatesEndpoint}?after_event_id=${cursor}&per_page=100`)).data,
    onEvents: (events) => { activity = mergeActivityEvents(activity, events); renderActivity(); showNotice(activityNotice, `${events.length} new activity ${events.length === 1 ? 'event' : 'events'}.`); },
    onError: (error) => showNotice(activityNotice, error?.message || 'Activity polling failed.', 'error'),
  });
  poller.start(Math.max(0, ...activity.map((item) => Number(item.id) || 0)));
  return poller;
}

function renderError(root, error, retry) {
  root.innerHTML = `<div class="agentpress-error" role="alert"><span class="dashicons dashicons-warning"></span><h1>AgentPress could not load</h1><p>${escapeHtml(error?.message || 'The private WordPress connection failed.')}</p><button type="button" class="button button-primary">Retry</button></div>`;
  root.querySelector('button')?.addEventListener('click', retry);
}

export async function startAdminOverview({ root, settings, documentRef = document, fetchImpl = fetch }) {
  const nonceManager = createRestNonceManager({ initialNonce: settings.nonce, refreshEndpoint: settings.refreshEndpoint, fetchImpl });
  const executeAbility = createJsonFetchExecutor({ endpoint: settings.executeEndpoint, fetchImpl, getRequestInit: nonceManager.getRequestInit, refreshNonce: nonceManager.refreshNonce });
  const definitions = await fetchAgentPressDefinitions({ endpoint: settings.toolsEndpoint, fetchImpl, getRequestInit: nonceManager.getRequestInit, refreshNonce: nonceManager.refreshNonce });
  const context = settings.context; if (context?.ok !== true || !context.data) throw new Error('AgentPress context is unavailable for this WordPress session.');
  const registration = await registerAgentPressTools({ definitions, executeAbility, documentRef });
  const model = summarizeOverview({ context, definitions, registration, diagnostics: { https: settings.isHttps === true, wordpress: Number.parseFloat(settings.wordpress) >= 6.9, abilitiesApi: settings.abilitiesApi === true, webmcp: registration.supported === true } });
  renderModel(root, model); await attachCollaboration({ root, settings, nonceManager, fetchImpl, documentRef }); return model;
}

const root = typeof document === 'undefined' ? null : document.getElementById('agentpress-admin');
const settingsNode = typeof document === 'undefined' ? null : document.getElementById('agentpress-admin-settings');
if (root && settingsNode) {
  const load = () => {
    root.innerHTML = '<div class="agentpress-skeleton" aria-label="Loading AgentPress"><span></span><span></span><span></span></div>';
    let settings; try { settings = JSON.parse(settingsNode.textContent || '{}'); } catch (error) { renderError(root, error, load); return; }
    startAdminOverview({ root, settings }).catch((error) => renderError(root, error, load));
  };
  load();
}
