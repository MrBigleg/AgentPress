import {
  createJsonFetchExecutor,
  createRestNonceManager,
  fetchAgentPressDefinitions,
  registerAgentPressTools,
} from './webmcp-adapter.mjs';

const CAPABILITY_LABELS = Object.freeze({
  read_site: 'Read site context',
  read_content: 'Read content',
  create_post_draft: 'Create post drafts',
  create_page_draft: 'Create page drafts',
  edit_own_agentpress_draft: 'Edit AgentPress drafts',
  edit_other_draft: 'Edit other drafts',
  edit_published_content: 'Edit published content',
  publish_own_content: 'Publish own content',
  publish_others_content: 'Publish others’ content',
  list_terms: 'Read categories and tags',
  create_terms: 'Create categories and tags',
  assign_terms: 'Assign existing terms',
  read_navigation: 'Read navigation',
  modify_navigation: 'Modify navigation',
  read_change_sets: 'Read change sets',
  read_activity: 'Read activity',
});

const STATE_LABELS = Object.freeze({
  automatic: 'Automatic',
  approval_required: 'Approval',
  unavailable: 'Unavailable',
});

export function summarizeOverview({ context, definitions, registration, diagnostics }) {
  const capabilities = context?.data?.capabilities ?? {};
  const capabilityRows = Object.entries(capabilities).map(([key, value]) => ({
    key,
    label: CAPABILITY_LABELS[key] ?? key.replaceAll('_', ' '),
    humanAllowed: value?.state !== 'unavailable',
    state: STATE_LABELS[value?.state] ? value.state : 'unavailable',
    reason: typeof value?.reason === 'string' ? value.reason : '',
  }));
  const automatic = capabilityRows.filter(({ state }) => state === 'automatic').length;
  const approvals = capabilityRows.filter(({ state }) => state === 'approval_required').length;
  const blocked = Array.isArray(context?.data?.blocked_areas)
    ? [...new Set(context.data.blocked_areas.map(String))]
    : [];
  const tools = Array.isArray(definitions) ? definitions : [];
  const exposedCount = registration?.supported ? Number(registration.count) || 0 : 0;
  const degraded = Object.values(diagnostics).some((value) => value !== true);

  return Object.freeze({
    state: exposedCount === 0 && tools.length === 0 ? 'empty' : degraded ? 'degraded' : 'active',
    site: context?.data?.site ?? {},
    user: context?.data?.user ?? {},
    capabilityRows,
    automatic,
    approvals,
    blocked,
    eligibleCount: tools.length,
    exposedCount,
    diagnostics,
  });
}

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function statePill(state) {
  return `<span class="agentpress-pill agentpress-pill--${escapeHtml(state)}">${escapeHtml(STATE_LABELS[state] ?? 'Unavailable')}</span>`;
}

function renderModel(root, model) {
  const diagnosticLabels = {
    https: 'HTTPS',
    wordpress: 'WordPress 6.9+',
    abilitiesApi: 'Abilities API',
    webmcp: 'WebMCP bridge',
  };
  const diagnostics = Object.entries(model.diagnostics)
    .map(([key, ok]) => `<li class="${ok ? 'is-good' : 'is-bad'}"><span aria-hidden="true"></span>${escapeHtml(diagnosticLabels[key] ?? key)}<strong>${ok ? 'Ready' : 'Needs attention'}</strong></li>`)
    .join('');
  const rows = model.capabilityRows
    .map(({ label, humanAllowed, state, reason }) => `<tr><th scope="row">${escapeHtml(label)}</th><td><span class="agentpress-human ${humanAllowed ? 'is-allowed' : 'is-limited'}">${humanAllowed ? 'Allowed' : 'Unavailable'}</span></td><td>${statePill(state)}${reason ? `<small>${escapeHtml(reason)}</small>` : ''}</td></tr>`)
    .join('');
  const blocked = model.blocked
    .map((area) => `<li><span class="dashicons dashicons-lock" aria-hidden="true"></span>${escapeHtml(area)}</li>`)
    .join('');
  const role = Array.isArray(model.user.roles) ? model.user.roles.join(', ') : '';
  const stateCopy = model.state === 'active'
    ? 'Your signed-in WordPress session is ready for AgentPress.'
    : model.state === 'empty'
      ? 'This account has no discoverable AgentPress tools.'
      : 'AgentPress is available with one or more environment limitations.';

  root.innerHTML = `
    <header class="agentpress-hero">
      <div class="agentpress-brand-mark" aria-hidden="true"><i></i><b>AP</b></div>
      <div><p class="agentpress-kicker">Human-controlled WordPress automation</p><h1>AgentPress</h1><p>${escapeHtml(stateCopy)}</p></div>
      <div class="agentpress-session"><span>${escapeHtml(model.user.display_name || 'WordPress user')}</span><small>${escapeHtml(role)}</small></div>
    </header>
    <nav class="agentpress-tabs" aria-label="AgentPress sections">
      <button type="button" class="is-active" aria-current="page" data-tab="overview">Overview</button>
      <button type="button" data-tab="changes">Changes</button>
      <button type="button" data-tab="activity">Activity</button>
    </nav>
    <main>
      <section class="agentpress-panel is-visible" data-panel="overview">
        <div class="agentpress-status-grid">
          <article class="agentpress-readout"><span>Tools exposed now</span><strong>${model.exposedCount}</strong><small>${model.eligibleCount} permitted by WordPress</small></article>
          <article class="agentpress-readout"><span>Automatic operations</span><strong>${model.automatic}</strong><small>Within current authority</small></article>
          <article class="agentpress-readout"><span>Approval operations</span><strong>${model.approvals}</strong><small>Require a human click</small></article>
          <article class="agentpress-readout agentpress-readout--site"><span>Connected site</span><strong>${escapeHtml(model.site.title || 'WordPress')}</strong><small>WordPress ${escapeHtml(model.site.wordpress_version || '')}</small></article>
        </div>
        <div class="agentpress-overview-grid">
          <section class="agentpress-card agentpress-card--matrix"><div class="agentpress-card-heading"><div><p>Control circuit</p><h2>Who can do what</h2></div><span>Live capability envelope</span></div><div class="agentpress-table-scroll"><table><thead><tr><th>Operation</th><th>Human authority</th><th>AgentPress outcome</th></tr></thead><tbody>${rows}</tbody></table></div></section>
          <aside class="agentpress-side-stack">
            <section class="agentpress-card"><div class="agentpress-card-heading"><div><p>Diagnostics</p><h2>Connection</h2></div></div><ul class="agentpress-diagnostics">${diagnostics}</ul></section>
            <section class="agentpress-card agentpress-card--blocked"><div class="agentpress-card-heading"><div><p>Policy boundary</p><h2>Always blocked</h2></div></div><p>These areas are not tools and are not included in the exposed count.</p><ul>${blocked}</ul></section>
          </aside>
        </div>
      </section>
      <section class="agentpress-panel" data-panel="changes"><div class="agentpress-empty"><span class="dashicons dashicons-clipboard"></span><h2>Changes are next</h2><p>Approval lists and semantic diffs arrive with the dedicated Change Set service. No placeholder actions are exposed.</p></div></section>
      <section class="agentpress-panel" data-panel="activity"><div class="agentpress-empty"><span class="dashicons dashicons-list-view"></span><h2>Activity is next</h2><p>Sanitized collaboration events arrive with the dedicated activity service. No private request data is shown here.</p></div></section>
    </main>`;

  for (const tab of root.querySelectorAll('[data-tab]')) {
    tab.addEventListener('click', () => {
      for (const item of root.querySelectorAll('[data-tab]')) {
        item.classList.toggle('is-active', item === tab);
        item.toggleAttribute('aria-current', item === tab);
      }
      for (const panel of root.querySelectorAll('[data-panel]')) {
        panel.classList.toggle('is-visible', panel.dataset.panel === tab.dataset.tab);
      }
    });
  }
}

function renderError(root, error, retry) {
  root.innerHTML = `<div class="agentpress-error" role="alert"><span class="dashicons dashicons-warning"></span><h1>AgentPress could not load</h1><p>${escapeHtml(error?.message || 'The private WordPress connection failed.')}</p><button type="button" class="button button-primary">Retry</button></div>`;
  root.querySelector('button')?.addEventListener('click', retry);
}

export async function startAdminOverview({ root, settings, documentRef = document, fetchImpl = fetch }) {
  const nonceManager = createRestNonceManager({
    initialNonce: settings.nonce,
    refreshEndpoint: settings.refreshEndpoint,
    fetchImpl,
  });
  const executeAbility = createJsonFetchExecutor({
    endpoint: settings.executeEndpoint,
    fetchImpl,
    getRequestInit: nonceManager.getRequestInit,
    refreshNonce: nonceManager.refreshNonce,
  });
  const definitions = await fetchAgentPressDefinitions({
    endpoint: settings.toolsEndpoint,
    fetchImpl,
    getRequestInit: nonceManager.getRequestInit,
    refreshNonce: nonceManager.refreshNonce,
  });
  const context = settings.context;
  if (context?.ok !== true || !context.data) {
    throw new Error('AgentPress context is unavailable for this WordPress session.');
  }
  const registration = await registerAgentPressTools({ definitions, executeAbility, documentRef });
  const model = summarizeOverview({
    context,
    definitions,
    registration,
    diagnostics: {
      https: settings.isHttps === true,
      wordpress: Number.parseFloat(settings.wordpress) >= 6.9,
      abilitiesApi: settings.abilitiesApi === true,
      webmcp: registration.supported === true,
    },
  });
  renderModel(root, model);
  return model;
}

const root = typeof document === 'undefined' ? null : document.getElementById('agentpress-admin');
const settingsNode = typeof document === 'undefined' ? null : document.getElementById('agentpress-admin-settings');
if (root && settingsNode) {
  const load = () => {
    root.innerHTML = '<div class="agentpress-skeleton" aria-label="Loading AgentPress"><span></span><span></span><span></span></div>';
    let settings;
    try {
      settings = JSON.parse(settingsNode.textContent || '{}');
    } catch (error) {
      renderError(root, error, load);
      return;
    }
    startAdminOverview({ root, settings }).catch((error) => renderError(root, error, load));
  };
  load();
}
