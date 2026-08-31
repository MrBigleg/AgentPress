import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

import {
  ABILITY_TO_TOOL_NAME,
  createJsonFetchExecutor,
  createRestNonceManager,
  fetchAgentPressDefinitions,
  registerAgentPressTools,
} from '../../admin/src/webmcp-adapter.mjs';

const currentDirectory = path.dirname(fileURLToPath(import.meta.url));
const sourcePath = path.resolve(currentDirectory, '../../admin/src/webmcp-adapter.mjs');
const phpMapPath = path.resolve(currentDirectory, '../../includes/WebMCP/AbilityMap.php');

function jsonResponse(status, payload) {
  return {
    ok: status >= 200 && status < 300,
    status,
    async json() {
      return payload;
    },
  };
}

function definitionsForAllAbilities() {
  return Object.keys(ABILITY_TO_TOOL_NAME).map((ability, index) => ({
    ability,
    description: `Synthetic definition ${index + 1}`,
    inputSchema: {
      type: 'object',
      properties: { value: { type: 'string' } },
      additionalProperties: false,
    },
    annotations: {
      readOnlyHint: index % 2 === 0,
      untrustedContentHint: index % 3 === 0,
    },
  }));
}

test('fixed map contains the 15 collision-free specification names', () => {
  const entries = Object.entries(ABILITY_TO_TOOL_NAME);

  assert.equal(entries.length, 15);
  assert.equal(new Set(entries.map(([, toolName]) => toolName)).size, 15);
  assert.ok(entries.every(([ability]) => ability.startsWith('agentpress/')));
  assert.ok(entries.every(([, toolName]) => !toolName.includes('/')));
  assert.equal(ABILITY_TO_TOOL_NAME['agentpress/publish-content'], 'agentpress_stage_publish');
  assert.equal(ABILITY_TO_TOOL_NAME['agentpress/create-term'], 'agentpress_stage_term');
});

test('browser and PHP transport use the same fixed mapping', () => {
  const phpSource = readFileSync(phpMapPath, 'utf8');
  const phpEntries = [...phpSource.matchAll(/'(agentpress\/[^']+)'\s*=>\s*'([^']+)'/gu)].map(
    ([, ability, toolName]) => [ability, toolName],
  );

  assert.deepEqual(Object.fromEntries(phpEntries), ABILITY_TO_TOOL_NAME);
});

test('unsupported clients return a stable no-op handle', async () => {
  const handle = await registerAgentPressTools({
    definitions: definitionsForAllAbilities(),
    executeAbility: async () => ({}),
    documentRef: {},
  });

  assert.equal(handle.supported, false);
  assert.equal(handle.count, 0);
  assert.deepEqual(handle.names, []);
  assert.equal(handle.unregisterAbility('agentpress/get-context'), false);
  assert.doesNotThrow(() => handle.dispose());
});

test('all supplied definitions register with fixed names, schemas, and annotations', async () => {
  const calls = [];
  const definitions = definitionsForAllAbilities();
  const expectedResult = { ok: true, request_id: 'synthetic', data: { value: 7 } };
  const executions = [];
  const documentRef = {
    modelContext: {
      async registerTool(tool, options) {
        calls.push({ tool, options });
      },
    },
  };

  const handle = await registerAgentPressTools({
    definitions,
    documentRef,
    executeAbility: async (...args) => {
      executions.push(args);
      return expectedResult;
    },
  });

  assert.equal(handle.supported, true);
  assert.equal(handle.count, 15);
  assert.equal(calls.length, 15);
  assert.deepEqual(handle.names, Object.values(ABILITY_TO_TOOL_NAME));
  assert.ok(calls.every(({ tool }) => !tool.name.includes('/')));
  assert.equal(new Set(calls.map(({ options }) => options.signal)).size, 15);

  for (const [index, { tool, options }] of calls.entries()) {
    assert.equal(tool.name, ABILITY_TO_TOOL_NAME[definitions[index].ability]);
    assert.strictEqual(tool.inputSchema, definitions[index].inputSchema);
    assert.strictEqual(tool.annotations, definitions[index].annotations);
    assert.equal(options.signal.aborted, false);
  }

  const executionController = new AbortController();
  const returned = await calls[0].tool.execute(
    { value: 'direct' },
    { signal: executionController.signal },
  );

  assert.strictEqual(returned, expectedResult);
  assert.deepEqual(executions[0], [
    definitions[0].ability,
    { value: 'direct' },
    { signal: executionController.signal },
  ]);

  assert.equal(handle.unregisterAbility(definitions[4].ability), true);
  assert.equal(calls[4].options.signal.aborted, true);
  assert.ok(calls.filter(({ options }) => options.signal.aborted).length === 1);
  assert.equal(handle.unregisterAbility(definitions[4].ability), false);

  handle.dispose();
  assert.ok(calls.every(({ options }) => options.signal.aborted));
});

test('execution cancellation signal is passed unchanged to fetch', async () => {
  let request;
  const fetchImpl = async (endpoint, options) => {
    request = { endpoint, options };

    return new Promise((resolve, reject) => {
      if (options.signal.aborted) {
        reject(options.signal.reason);
        return;
      }
      options.signal.addEventListener('abort', () => reject(options.signal.reason), { once: true });
    });
  };
  const executeAbility = createJsonFetchExecutor({
    endpoint: '/wp-json/agentpress/v1/webmcp/execute',
    fetchImpl,
    getRequestInit: () => ({ headers: { 'X-Synthetic-Control': 'present' } }),
  });
  const calls = [];
  const documentRef = {
    modelContext: {
      async registerTool(tool, options) {
        calls.push({ tool, options });
      },
    },
  };

  await registerAgentPressTools({
    definitions: [definitionsForAllAbilities()[0]],
    executeAbility,
    documentRef,
  });

  const executionController = new AbortController();
  const execution = calls[0].tool.execute(
    { value: 'cancel-me' },
    { signal: executionController.signal },
  );
  executionController.abort(new DOMException('Synthetic cancellation', 'AbortError'));

  await assert.rejects(execution, { name: 'AbortError' });
  assert.equal(request.endpoint, '/wp-json/agentpress/v1/webmcp/execute');
  assert.strictEqual(request.options.signal, executionController.signal);
  assert.equal(request.options.credentials, 'same-origin');
  assert.deepEqual(JSON.parse(request.options.body), {
    ability: 'agentpress/get-context',
    input: { value: 'cancel-me' },
  });
  assert.equal(request.options.headers['X-Synthetic-Control'], 'present');
});

test('fetch executor returns structured JSON directly', async () => {
  const result = { ok: true, request_id: 'direct-json', data: { nested: true } };
  const executeAbility = createJsonFetchExecutor({
    endpoint: '/execute',
    fetchImpl: async () => ({
      ok: true,
      status: 200,
      async json() {
        return result;
      },
    }),
  });

  assert.strictEqual(await executeAbility('agentpress/get-context', {}), result);
});

test('nonce failure refreshes and retries execution exactly once', async () => {
  let nonce = 'old-nonce';
  let refreshCount = 0;
  const requests = [];
  const expected = { ok: true, request_id: 'retry-success', data: {} };
  const executeAbility = createJsonFetchExecutor({
    endpoint: '/execute',
    getRequestInit: () => ({ headers: { 'X-WP-Nonce': nonce } }),
    refreshNonce: async () => {
      refreshCount += 1;
      nonce = 'fresh-nonce';
    },
    fetchImpl: async (endpoint, options) => {
      requests.push({ endpoint, options });
      return requests.length === 1
        ? jsonResponse(403, { code: 'rest_cookie_invalid_nonce', message: 'Expired.' })
        : jsonResponse(200, expected);
    },
  });

  assert.strictEqual(await executeAbility('agentpress/get-context', {}), expected);
  assert.equal(refreshCount, 1);
  assert.equal(requests.length, 2);
  assert.equal(requests[0].options.headers['X-WP-Nonce'], 'old-nonce');
  assert.equal(requests[1].options.headers['X-WP-Nonce'], 'fresh-nonce');
  assert.ok(requests.every(({ options }) => options.cache === 'no-store'));
});

test('a repeated nonce failure stops after one refresh and two requests', async () => {
  let refreshCount = 0;
  let requestCount = 0;
  const executeAbility = createJsonFetchExecutor({
    endpoint: '/execute',
    refreshNonce: async () => {
      refreshCount += 1;
    },
    fetchImpl: async () => {
      requestCount += 1;
      return jsonResponse(403, {
        ok: false,
        request_id: '00000000-0000-4000-8000-000000000012',
        error: { code: 'AP_NONCE_INVALID', message: 'Still invalid.', retryable: true, details: {} },
      });
    },
  });

  await assert.rejects(executeAbility('agentpress/get-context', {}), {
    code: 'AP_NONCE_INVALID',
    status: 403,
  });
  assert.equal(refreshCount, 1);
  assert.equal(requestCount, 2);
});

test('non-nonce denials are never retried', async () => {
  let refreshCount = 0;
  let requestCount = 0;
  const executeAbility = createJsonFetchExecutor({
    endpoint: '/execute',
    refreshNonce: async () => {
      refreshCount += 1;
    },
    fetchImpl: async () => {
      requestCount += 1;
      return jsonResponse(403, {
        ok: false,
        request_id: '00000000-0000-4000-8000-000000000012',
        error: { code: 'AP_PERMISSION_DENIED', message: 'Denied.', retryable: false, details: {} },
      });
    },
  });

  await assert.rejects(executeAbility('agentpress/get-context', {}), {
    code: 'AP_PERMISSION_DENIED',
    status: 403,
  });
  assert.equal(refreshCount, 0);
  assert.equal(requestCount, 1);
});

test('private discovery uses same-origin no-store requests and no persistent cache', async () => {
  const requests = [];
  const definitions = definitionsForAllAbilities().slice(0, 2);
  const returned = await fetchAgentPressDefinitions({
    endpoint: '/tools',
    getRequestInit: () => ({ headers: { 'X-WP-Nonce': 'synthetic' } }),
    fetchImpl: async (endpoint, options) => {
      requests.push({ endpoint, options });
      return jsonResponse(200, { tools: definitions });
    },
  });

  assert.strictEqual(returned, definitions);
  assert.equal(requests[0].options.method, 'GET');
  assert.equal(requests[0].options.credentials, 'same-origin');
  assert.equal(requests[0].options.cache, 'no-store');
  assert.equal(requests[0].options.headers['X-WP-Nonce'], 'synthetic');
});

test('nonce manager keeps the refreshed nonce in page memory only', async () => {
  const requests = [];
  const manager = createRestNonceManager({
    initialNonce: 'initial',
    refreshEndpoint: '/wp-admin/admin-ajax.php',
    fetchImpl: async (endpoint, options) => {
      requests.push({ endpoint, options });
      return jsonResponse(200, { success: true, data: { nonce: 'refreshed' } });
    },
  });

  assert.equal(manager.getRequestInit().headers['X-WP-Nonce'], 'initial');
  assert.equal(await manager.refreshNonce(), 'refreshed');
  assert.equal(manager.getNonce(), 'refreshed');
  assert.equal(manager.getRequestInit().headers['X-WP-Nonce'], 'refreshed');
  assert.equal(requests.length, 1);
  assert.equal(requests[0].options.credentials, 'same-origin');
  assert.equal(requests[0].options.cache, 'no-store');
  assert.equal(requests[0].options.body, 'action=agentpress_refresh_nonce');
});

test('unknown or duplicate definitions fail before partial registration', async () => {
  let registrationCount = 0;
  const documentRef = {
    modelContext: {
      async registerTool() {
        registrationCount += 1;
      },
    },
  };
  const known = definitionsForAllAbilities()[0];

  await assert.rejects(
    registerAgentPressTools({
      definitions: [known, { ...known }],
      executeAbility: async () => ({}),
      documentRef,
    }),
    /Duplicate AgentPress tool definition/u,
  );
  await assert.rejects(
    registerAgentPressTools({
      definitions: [{ ...known, ability: 'third-party/unknown' }],
      executeAbility: async () => ({}),
      documentRef,
    }),
    /Unknown AgentPress Ability/u,
  );
  assert.equal(registrationCount, 0);
});

test('a browser registration failure aborts every registration attempted in the batch', async () => {
  const signals = [];
  const documentRef = {
    modelContext: {
      async registerTool(tool, options) {
        signals.push(options.signal);
        if (signals.length === 2) {
          throw new DOMException(`Synthetic rejection for ${tool.name}`, 'InvalidStateError');
        }
      },
    },
  };

  await assert.rejects(
    registerAgentPressTools({
      definitions: definitionsForAllAbilities().slice(0, 3),
      executeAbility: async () => ({}),
      documentRef,
    }),
    { name: 'InvalidStateError' },
  );
  assert.equal(signals.length, 2);
  assert.ok(signals.every((signal) => signal.aborted));
});

test('production adapter contains no obsolete WebMCP API identifiers', () => {
  const source = readFileSync(sourcePath, 'utf8');
  const prohibited = [
    ['navigator', 'modelContext'].join('.'),
    ['provide', 'Context'].join(''),
    ['local', 'Storage'].join(''),
    ['session', 'Storage'].join(''),
  ];

  for (const identifier of prohibited) {
    assert.equal(source.includes(identifier), false, `prohibited production identifier: ${identifier}`);
  }
});
