/**
 * AgentPress browser adapter for the current WebMCP imperative API.
 *
 * Authentication, nonce refresh, and server-side authorization belong to the
 * AP-004 transport. This module owns only fixed naming, registration lifecycle,
 * structured execution, and cancellation propagation.
 */

export const ABILITY_TO_TOOL_NAME = Object.freeze({
  'agentpress/get-context': 'agentpress_get_context',
  'agentpress/get-site-structure': 'agentpress_get_structure',
  'agentpress/list-content': 'agentpress_list_content',
  'agentpress/get-content': 'agentpress_get_content',
  'agentpress/create-draft': 'agentpress_create_draft',
  'agentpress/update-content': 'agentpress_update_content',
  'agentpress/publish-content': 'agentpress_stage_publish',
  'agentpress/list-terms': 'agentpress_list_terms',
  'agentpress/create-term': 'agentpress_stage_term',
  'agentpress/assign-terms': 'agentpress_assign_terms',
  'agentpress/get-navigation': 'agentpress_get_navigation',
  'agentpress/stage-navigation-change': 'agentpress_stage_navigation',
  'agentpress/get-change-set': 'agentpress_get_change_set',
  'agentpress/list-change-sets': 'agentpress_list_change_sets',
  'agentpress/get-agent-activity': 'agentpress_get_activity',
});

const EMPTY_INPUT_SCHEMA = Object.freeze({
  type: 'object',
  properties: Object.freeze({}),
  additionalProperties: false,
});

function validateDefinitions(definitions) {
  if (!Array.isArray(definitions)) {
    throw new TypeError('AgentPress tool definitions must be an array.');
  }

  const seenAbilities = new Set();
  const seenToolNames = new Set();

  for (const definition of definitions) {
    if (!definition || typeof definition !== 'object') {
      throw new TypeError('Each AgentPress tool definition must be an object.');
    }

    const { ability, description } = definition;
    const toolName = ABILITY_TO_TOOL_NAME[ability];

    if (!toolName) {
      throw new RangeError(`Unknown AgentPress Ability: ${String(ability)}`);
    }

    if (typeof description !== 'string' || description.trim() === '') {
      throw new TypeError(`AgentPress Ability ${ability} requires a description.`);
    }

    if (seenAbilities.has(ability) || seenToolNames.has(toolName)) {
      throw new RangeError(`Duplicate AgentPress tool definition: ${ability}`);
    }

    seenAbilities.add(ability);
    seenToolNames.add(toolName);
  }
}

/**
 * Create the narrow JSON fetch primitive consumed by registered tools.
 * AP-004 supplies nonce headers and retry policy through getRequestInit.
 */
export function createJsonFetchExecutor({
  endpoint,
  fetchImpl = globalThis.fetch,
  getRequestInit = () => ({}),
  refreshNonce,
}) {
  if (typeof endpoint !== 'string' || endpoint === '') {
    throw new TypeError('AgentPress execute endpoint is required.');
  }

  if (typeof fetchImpl !== 'function') {
    throw new TypeError('AgentPress fetch implementation is required.');
  }

  if (typeof getRequestInit !== 'function') {
    throw new TypeError('AgentPress request initializer must be a function.');
  }

  return (ability, input, { signal } = {}) =>
    requestJsonWithNonceRetry({
      endpoint,
      fetchImpl,
      getRequestInit: () => getRequestInit({ ability, input }),
      refreshNonce,
      signal,
      method: 'POST',
      body: JSON.stringify({ ability, input }),
    });
}

/**
 * Fetch the current private definition set without persisting it.
 */
export function fetchAgentPressDefinitions({
  endpoint,
  fetchImpl = globalThis.fetch,
  getRequestInit = () => ({}),
  refreshNonce,
  signal,
}) {
  return requestJsonWithNonceRetry({
    endpoint,
    fetchImpl,
    getRequestInit,
    refreshNonce,
    signal,
    method: 'GET',
  }).then((payload) => (Array.isArray(payload.tools) ? payload.tools : []));
}

/**
 * Fetch one private AgentPress admin resource with the shared nonce policy.
 */
export function fetchAgentPressJson({
  endpoint,
  method = 'GET',
  body,
  fetchImpl = globalThis.fetch,
  getRequestInit = () => ({}),
  refreshNonce,
  signal,
}) {
  return requestJsonWithNonceRetry({
    endpoint,
    fetchImpl,
    getRequestInit,
    refreshNonce,
    signal,
    method,
    body: body === undefined ? undefined : JSON.stringify(body),
  });
}

/**
 * Keep the REST nonce only in this page's closure and refresh it once on demand.
 */
export function createRestNonceManager({
  initialNonce,
  refreshEndpoint,
  fetchImpl = globalThis.fetch,
}) {
  if (typeof initialNonce !== 'string' || initialNonce === '') {
    throw new TypeError('AgentPress initial REST nonce is required.');
  }

  if (typeof refreshEndpoint !== 'string' || refreshEndpoint === '') {
    throw new TypeError('AgentPress nonce refresh endpoint is required.');
  }

  if (typeof fetchImpl !== 'function') {
    throw new TypeError('AgentPress fetch implementation is required.');
  }

  let currentNonce = initialNonce;

  const getRequestInit = () => ({
    headers: { 'X-WP-Nonce': currentNonce },
  });

  const refreshNonce = async ({ signal } = {}) => {
    const response = await fetchImpl(refreshEndpoint, {
      method: 'POST',
      credentials: 'same-origin',
      cache: 'no-store',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body: 'action=agentpress_refresh_nonce',
      signal,
    });
    const payload = await response.json();
    const nonce = payload?.data?.nonce;

    if (!response.ok || payload?.success !== true || typeof nonce !== 'string' || nonce === '') {
      throw createTransportError(response, payload);
    }

    currentNonce = nonce;
    return nonce;
  };

  return Object.freeze({
    getNonce: () => currentNonce,
    getRequestInit,
    refreshNonce,
  });
}

const NONCE_ERROR_CODES = Object.freeze(
  new Set(['AP_NONCE_INVALID', 'rest_cookie_invalid_nonce']),
);

function getErrorPayload(payload) {
  return payload?.error && typeof payload.error === 'object' ? payload.error : payload;
}

async function requestJsonWithNonceRetry({
  endpoint,
  fetchImpl,
  getRequestInit,
  refreshNonce,
  signal,
  method,
  body,
}) {
  if (typeof endpoint !== 'string' || endpoint === '') {
    throw new TypeError('AgentPress endpoint is required.');
  }

  if (typeof fetchImpl !== 'function' || typeof getRequestInit !== 'function') {
    throw new TypeError('AgentPress request dependencies are required.');
  }

  for (let attempt = 0; attempt < 2; attempt += 1) {
    const suppliedInit = (await getRequestInit()) ?? {};
    const response = await fetchImpl(endpoint, {
      ...suppliedInit,
      method,
      credentials: 'same-origin',
      cache: 'no-store',
      headers: {
        ...(body === undefined ? {} : { 'Content-Type': 'application/json' }),
        ...(suppliedInit.headers ?? {}),
      },
      ...(body === undefined ? {} : { body }),
      signal,
    });
    const payload = await response.json();

    if (response.ok) {
      return payload;
    }

    if (
      attempt === 0 &&
      typeof refreshNonce === 'function' &&
      NONCE_ERROR_CODES.has(getErrorPayload(payload)?.code)
    ) {
      await refreshNonce({ signal });
      continue;
    }

    throw createTransportError(response, payload);
  }

  throw new Error('AgentPress nonce retry invariant failed.');
}

function createTransportError(response, payload) {
  const failure = getErrorPayload(payload);
  const error = new Error(
    typeof failure?.message === 'string'
      ? failure.message
      : `AgentPress request failed with HTTP ${response.status}.`,
  );
  error.status = response.status;
  error.code = typeof failure?.code === 'string' ? failure.code : 'AP_TRANSPORT_ERROR';
  return error;
}

/**
 * Register a supplied, pre-authorized AgentPress definition set.
 */
export async function registerAgentPressTools({
  definitions,
  executeAbility,
  documentRef = globalThis.document,
  AbortControllerImpl = globalThis.AbortController,
}) {
  const modelContext = documentRef?.modelContext;

  if (!modelContext || typeof modelContext.registerTool !== 'function') {
    return Object.freeze({
      supported: false,
      count: 0,
      names: Object.freeze([]),
      unregisterAbility: () => false,
      dispose: () => {},
    });
  }

  if (typeof executeAbility !== 'function') {
    throw new TypeError('AgentPress executeAbility callback is required.');
  }

  if (typeof AbortControllerImpl !== 'function') {
    throw new TypeError('AbortController is required for tool registration.');
  }

  validateDefinitions(definitions);

  const registrations = new Map();

  try {
    for (const definition of definitions) {
      const toolName = ABILITY_TO_TOOL_NAME[definition.ability];
      const controller = new AbortControllerImpl();
      const tool = {
        name: toolName,
        description: definition.description,
        inputSchema: definition.inputSchema ?? EMPTY_INPUT_SCHEMA,
        execute: (input, { signal } = {}) =>
          executeAbility(definition.ability, input, { signal }),
      };

      if (definition.annotations !== undefined) {
        tool.annotations = definition.annotations;
      }

      registrations.set(definition.ability, { controller, toolName });
      await modelContext.registerTool(tool, { signal: controller.signal });
    }
  } catch (error) {
    for (const { controller } of registrations.values()) {
      controller.abort();
    }
    throw error;
  }

  const unregisterAbility = (ability) => {
    const registration = registrations.get(ability);
    if (!registration) {
      return false;
    }

    registration.controller.abort();
    registrations.delete(ability);
    return true;
  };

  const dispose = () => {
    for (const { controller } of registrations.values()) {
      controller.abort();
    }
    registrations.clear();
  };

  return Object.freeze({
    supported: true,
    count: registrations.size,
    names: Object.freeze([...registrations.values()].map(({ toolName }) => toolName)),
    unregisterAbility,
    dispose,
  });
}
