import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import test from 'node:test';
import { fileURLToPath } from 'node:url';

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../../..');

test('AP-029 runner declares every required security class once', () => {
  const output = execFileSync(process.execPath, ['scripts/run-security-gate.mjs', '--list'], { cwd: projectRoot, encoding: 'utf8' });
  const matrices = JSON.parse(output);
  const files = matrices.map(({ file }) => file);
  const coverage = matrices.map(({ coverage }) => coverage).join(' ');

  assert.equal(matrices.length, 16);
  assert.equal(new Set(files).size, matrices.length);
  for (const required of ['nonce', 'rate', 'schemas', 'audit', 'idempotency', 'single-winner', 'five-role', 'staleness', 'expiry']) {
    assert.match(coverage, new RegExp(required, 'i'));
  }
});
