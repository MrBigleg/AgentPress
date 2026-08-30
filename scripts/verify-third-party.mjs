import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const pluginRoot = path.join(projectRoot, 'agentpress');
const provenanceRoot = path.join(pluginRoot, 'third-party', 'webmcp-abilities');
const expectedCommit = 'ea5a2bcbe77dc13aafc95b4c83a18c9b11d85da6';
const expectedTree = '232869b917e491b6c15b47b0cd58fc941dda9797';
const expectedLicense = 'GPL-2.0-or-later';

const readText = (relativePath) =>
  readFileSync(path.join(pluginRoot, relativePath), 'utf8');

const pin = readFileSync(path.join(provenanceRoot, 'PINNED_COMMIT'), 'utf8').trim();
const provenance = JSON.parse(
  readFileSync(path.join(provenanceRoot, 'PROVENANCE.json'), 'utf8'),
);
const notices = readText('THIRD_PARTY_NOTICES.md');
const upstreamLicense = readText('third-party/webmcp-abilities/LICENSE');
const agentpressComposer = JSON.parse(readText('composer.json'));

assert.equal(pin, expectedCommit, 'PINNED_COMMIT must contain the audited commit');
assert.equal(provenance.schema_version, 1, 'unsupported provenance schema');
assert.equal(
  provenance.upstream_repository,
  'https://github.com/code-atlantic/webmcp-abilities',
  'unexpected upstream repository',
);
assert.equal(provenance.upstream_commit, pin, 'provenance commit must match the pin');
assert.equal(provenance.upstream_tree, expectedTree, 'unexpected audited tree');
assert.equal(provenance.upstream_version, '0.6.1', 'unexpected upstream version');
assert.equal(provenance.license_expression, expectedLicense, 'unexpected upstream license');
assert.equal(provenance.upstream_license_file_present, false, 'pinned tree has no LICENSE blob');
assert.deepEqual(
  provenance.copied_or_adapted_material,
  [],
  'AP-002 must not claim or package adapted runtime source',
);
assert.equal(
  provenance.concepts_reserved_for_later_adaptation.length,
  3,
  'the audited concept inventory changed unexpectedly',
);
assert.equal(agentpressComposer.license, expectedLicense, 'AgentPress Composer license drifted');

for (const evidence of provenance.license_evidence) {
  assert.match(evidence.blob_sha1, /^[0-9a-f]{40}$/u, `invalid blob SHA for ${evidence.path}`);
}

for (const requiredText of [
  expectedCommit,
  expectedTree,
  expectedLicense,
  'Code Atlantic',
  'copied or shipped',
]) {
  assert.ok(notices.includes(requiredText), `third-party notice is missing: ${requiredText}`);
}

assert.ok(
  upstreamLicense.includes('GNU GENERAL PUBLIC LICENSE') &&
    upstreamLicense.includes('Version 2, June 1991'),
  'the packaged upstream license must contain the complete GPL version 2 text',
);
assert.equal(
  upstreamLicense.replaceAll('\r\n', '\n').trimEnd(),
  readFileSync(path.join(projectRoot, 'LICENSE'), 'utf8').replaceAll('\r\n', '\n').trimEnd(),
  'the third-party license copy must match AgentPress full GPL version 2 text',
);

const buildScriptPath = path.join(projectRoot, 'scripts', 'build-zip.mjs');
const buildScript = readFileSync(buildScriptPath, 'utf8');
for (const requiredSourcePath of [
  'THIRD_PARTY_NOTICES.md',
  'third-party/webmcp-abilities/LICENSE',
  'third-party/webmcp-abilities/PINNED_COMMIT',
  'third-party/webmcp-abilities/PROVENANCE.json',
  'third-party/webmcp-abilities/README.md',
]) {
  assert.ok(buildScript.includes(`'${requiredSourcePath}'`), `ZIP manifest omits ${requiredSourcePath}`);
}

execFileSync(process.execPath, [buildScriptPath], { cwd: projectRoot, stdio: 'pipe' });
const archive = readFileSync(path.join(projectRoot, 'dist', 'agentpress.zip'));

function readZipEntries(buffer) {
  const endSignature = 0x06054b50;
  const centralSignature = 0x02014b50;
  const minimumEndOffset = Math.max(0, buffer.length - 65_557);
  let endOffset = -1;

  for (let offset = buffer.length - 22; offset >= minimumEndOffset; offset -= 1) {
    if (buffer.readUInt32LE(offset) === endSignature) {
      endOffset = offset;
      break;
    }
  }

  assert.notEqual(endOffset, -1, 'ZIP end-of-central-directory record not found');
  const entryCount = buffer.readUInt16LE(endOffset + 10);
  let offset = buffer.readUInt32LE(endOffset + 16);
  const entries = [];

  for (let index = 0; index < entryCount; index += 1) {
    assert.equal(buffer.readUInt32LE(offset), centralSignature, 'invalid ZIP central directory');
    const nameLength = buffer.readUInt16LE(offset + 28);
    const extraLength = buffer.readUInt16LE(offset + 30);
    const commentLength = buffer.readUInt16LE(offset + 32);
    entries.push(buffer.subarray(offset + 46, offset + 46 + nameLength).toString('utf8'));
    offset += 46 + nameLength + extraLength + commentLength;
  }

  return entries;
}

const entries = readZipEntries(archive);
for (const requiredEntry of [
  'agentpress/LICENSE',
  'agentpress/THIRD_PARTY_NOTICES.md',
  'agentpress/third-party/webmcp-abilities/LICENSE',
  'agentpress/third-party/webmcp-abilities/PINNED_COMMIT',
  'agentpress/third-party/webmcp-abilities/PROVENANCE.json',
  'agentpress/third-party/webmcp-abilities/README.md',
]) {
  assert.ok(entries.includes(requiredEntry), `ZIP is missing ${requiredEntry}`);
}

const forbiddenRuntimeNames = new Set([
  'webmcp-abilities.php',
  'class-admin-page.php',
  'class-builtin-tools.php',
  'class-plugin.php',
  'class-rest-api.php',
  'class-settings.php',
  'webmcp-abilities.js',
  'webmcp-abilities.ts',
]);

for (const entry of entries) {
  assert.ok(!forbiddenRuntimeNames.has(path.posix.basename(entry)), `excluded upstream runtime found: ${entry}`);
  if (entry.startsWith('agentpress/third-party/webmcp-abilities/')) {
    assert.ok(!/\.(?:php|js|ts)$/u.test(entry), `AP-002 must not package upstream runtime code: ${entry}`);
  }
}

process.stdout.write(
  `Licenses verified: AgentPress=${agentpressComposer.license}; WebMCP Abilities=${provenance.license_expression}. Pin=${pin}; ${entries.length} ZIP entries; no upstream runtime code.\n`,
);
