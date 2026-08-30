import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

import {
  ABILITY_TO_TOOL_NAME,
  createJsonFetchExecutor,
  registerAgentPressTools,
} from '../../admin/src/webmcp-adapter.mjs';

const currentDirectory = path.dirname(fileURLToPath(import.meta.url));
const sourcePath = path.resolve(currentDirectory, '../../admin/src/webmcp-adapter.mjs');

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
  ];

  for (const identifier of prohibited) {
    assert.equal(source.includes(identifier), false, `prohibited production identifier: ${identifier}`);
  }
});
