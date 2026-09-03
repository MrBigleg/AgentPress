import { spawn } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, statSync, writeFileSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const currentDir = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(currentDir, '..');
const phpScriptPath = path.join(projectRoot, 'scripts', 'reset-fixture.php');
const fixturesDir = path.join(projectRoot, 'docs', 'evidence', 'fixtures');
const manifestPath = path.join(fixturesDir, 'canonical-fixture.json');

if (!existsSync(phpScriptPath)) {
  console.error(`PHP reset script not found at ${phpScriptPath}`);
  process.exit(1);
}

const phpCode = readFileSync(phpScriptPath, 'utf8');

// Determine if we need --config for wp-env if running from a worktree
let configArg = null;
const gitPath = path.join(projectRoot, '.git');
const rootConfig = path.resolve(projectRoot, '..', '..', '.wp-env.json');
if (existsSync(gitPath) && !statSync(gitPath).isDirectory() && existsSync(rootConfig)) {
  configArg = `--config=${rootConfig}`;
}

const args = ['wp-env'];
if (configArg) {
  args.push(configArg);
}
args.push('run', 'cli', 'wp', 'eval-file', '-');

console.log('Resetting AgentPress challenge fixture to deterministic state...');

const npx = process.platform === 'win32' ? 'npx.cmd' : 'npx';
const child = spawn(npx, args, {
  cwd: projectRoot,
  shell: true,
  stdio: ['pipe', 'pipe', 'inherit'],
});

let stdout = '';
child.stdout.on('data', (data) => {
  stdout += data.toString();
});

child.stdin.write(phpCode);
child.stdin.end();

child.on('close', (code) => {
  if (code !== 0) {
    console.error(`Fixture reset failed with exit code ${code}`);
    process.exit(code);
  }

  const startMarker = '=== AGENTPRESS FIXTURE MANIFEST ===';
  const endMarker = '=== END AGENTPRESS FIXTURE MANIFEST ===';
  const startIndex = stdout.indexOf(startMarker);
  const endIndex = stdout.indexOf(endMarker);

  if (startIndex === -1 || endIndex === -1) {
    console.log(stdout);
    console.error('Could not locate fixture manifest in output.');
    process.exit(1);
  }

  const jsonStr = stdout.slice(startIndex + startMarker.length, endIndex).trim();
  try {
    const manifest = JSON.parse(jsonStr);
    if (!existsSync(fixturesDir)) {
      mkdirSync(fixturesDir, { recursive: true });
    }
    writeFileSync(manifestPath, JSON.stringify(manifest, null, 2) + '\n', 'utf8');
    console.log('Deterministic fixture reset successful:');
    console.log(`- Site Title: ${manifest.site.title}`);
    console.log(`- Theme: ${manifest.site.theme}`);
    console.log(`- Administrator ID: ${manifest.users.administrator.id} (${manifest.users.administrator.login})`);
    console.log(`- Author ID: ${manifest.users.author.id} (${manifest.users.author.login})`);
    console.log(`- Menu ID: ${manifest.navigation.menu_id} (Location: ${manifest.navigation.location})`);
    console.log(`- Items: Home (${manifest.navigation.items.home}), About (${manifest.navigation.items.about}), Blog (${manifest.navigation.items.blog}), Contact (${manifest.navigation.items.contact})`);
    console.log(`- State Hash: ${manifest.navigation.state_hash}`);
    console.log(`- AgentPress Tables: changes=${manifest.agentpress_db.changes_count}, change_sets=${manifest.agentpress_db.change_sets_count}, audit=${manifest.agentpress_db.audit_count}`);
    console.log(`Manifest written to: ${path.relative(projectRoot, manifestPath)}`);
  } catch (err) {
    console.error('Failed to parse manifest JSON:', err);
    console.log(stdout);
    process.exit(1);
  }
});
