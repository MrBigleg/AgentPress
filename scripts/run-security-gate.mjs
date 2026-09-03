import { readFileSync } from 'node:fs';
import path from 'node:path';
import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const resetCode = readFileSync(path.join(projectRoot, 'scripts', 'reset-fixture.php'), 'utf8');
const resetBody = resetCode.replace(/^<\?php\s*/u, '');
const wpEnv = process.platform === 'win32' ? 'npx.cmd' : 'npx';

const matrices = Object.freeze([
  ['ap004-rest-transport.php', 'nonce, origin, request size, and rate limits'],
  ['ap006-common-schemas-errors.php', 'closed schemas and normalized errors'],
  ['ap007-safe-mode-discovery-policy.php', 'Safe Mode and capability policy'],
  ['ap008-ability-registry.php', 'fixed registry and direct execution boundary'],
  ['ap009-sanitized-audit-logging.php', 'sanitized audit and fail-closed storage'],
  ['ap010-change-set-coordinator.php', 'idempotency, immutable staging, and atomic claims'],
  ['ap029-atomic-claim.php', 'database-enforced single-winner approval claim'],
  ['ap015-create-draft.php', 'draft authority, replay, and zero denied mutation'],
  ['ap016-update-content.php', 'R1/R2 update boundary and zero staged mutation'],
  ['ap017-assign-terms.php', 'atomic taxonomy writes and permission boundary'],
  ['ap018-authorization-suite.php', 'five-role discovery and direct-call regression'],
  ['ap020-change-activity-reads.php', 'owner-visible collaboration reads and privacy'],
  ['ap021-get-navigation.php', 'bounded navigation reads and state hashes'],
  ['ap022-stage-navigation-change.php', 'immutable navigation proposals'],
  ['ap023-approval-rejection.php', 'approval staleness, expiry, rejection, and replay'],
  ['ap026-ap033-publish-term.php', 'publish and term staging plus approval'],
]);
const onlyArgument = process.argv.find((argument) => argument.startsWith('--only='));
const onlyFile = onlyArgument?.slice('--only='.length);
const selectedMatrices = onlyFile ? matrices.filter(([file]) => file === onlyFile) : matrices;

function runWp(args, { input, quiet = false } = {}) {
  const result = spawnSync(wpEnv, ['wp-env', 'run', 'cli', 'wp', ...args], {
    cwd: projectRoot,
    encoding: 'utf8',
    input,
    shell: process.platform === 'win32',
    maxBuffer: 10 * 1024 * 1024,
  });
  if (!quiet || result.status !== 0) {
    if (result.stdout) process.stdout.write(result.stdout);
    if (result.stderr) process.stderr.write(result.stderr);
  }
  if (result.error) throw result.error;
  if (result.status !== 0) throw new Error(`wp-env command failed with exit code ${result.status}.`);
  return result.stdout;
}

function resetFixture() {
  runWp(['eval-file', '-'], { input: resetCode, quiet: true });
}

function runIsolatedMatrix(file) {
  const code = `<?php
ob_start();
${resetBody}
ob_end_clean();
require ABSPATH . 'wp-content/plugins/agentpress/tests/integration/${file}';
`;
  runWp(['eval-file', '-'], { input: code });
}

function verifyEnvironment() {
  const code = `<?php
if ( '6.9' !== get_bloginfo( 'version' ) || 8 !== PHP_MAJOR_VERSION || 0 !== PHP_MINOR_VERSION ) {
    fwrite( STDERR, 'AP-029 requires WordPress 6.9 and PHP 8.0.\\n' );
    exit( 1 );
}
if ( ! class_exists( 'AgentPress\\\\Plugin' ) ) {
    fwrite( STDERR, 'AgentPress is not active.\\n' );
    exit( 1 );
}
echo wp_json_encode( array( 'wordpress' => get_bloginfo( 'version' ), 'php' => PHP_VERSION, 'agentpress_active' => true ) ) . "\\n";
`;
  runWp(['eval-file', '-'], { input: code });
}

function verifyFixture() {
  runWp(['eval-file', 'wp-content/plugins/agentpress/tests/integration/ap027-fixture-reset.php'], { quiet: true });
}

if (process.argv.includes('--list')) {
  process.stdout.write(`${JSON.stringify(matrices.map(([file, coverage]) => ({ file, coverage })), null, 2)}\n`);
  process.exit(0);
}

if (process.argv.includes('--verify-environment')) {
  resetFixture();
  verifyEnvironment();
  verifyFixture();
  process.stdout.write('AP-029 environment and canonical fixture verified.\n');
  process.exit(0);
}

if (selectedMatrices.length === 0) {
  process.stderr.write(`Unknown AP-029 matrix: ${onlyFile}\n`);
  process.exit(1);
}

const started = Date.now();
const results = [];
let failure = null;

try {
  resetFixture();
  verifyEnvironment();
  verifyFixture();
  for (const [file, coverage] of selectedMatrices) {
    const matrixStarted = Date.now();
    process.stdout.write(`\n[AP-029] ${file}: ${coverage}\n`);
    runIsolatedMatrix(file);
    results.push({ file, coverage, duration_ms: Date.now() - matrixStarted, result: 'SUPPORTED' });
  }
} catch (error) {
  failure = error;
} finally {
  try { resetFixture(); verifyFixture(); }
  catch (resetError) { failure ??= resetError; }
}

process.stdout.write(`\n${JSON.stringify({
  task: 'AP-029',
  result: failure ? 'FALSIFIED' : 'SUPPORTED',
  matrices_passed: results.length,
  matrices_total: selectedMatrices.length,
  duration_ms: Date.now() - started,
}, null, 2)}\n`);

if (failure) {
  process.stderr.write(`AP-029 gate failed: ${failure.message}\n`);
  process.exit(1);
}
