import { createWriteStream, mkdirSync, readFileSync, rmSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';
import archiver from 'archiver';

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const sourceRoot = path.join(projectRoot, 'agentpress');
const outputDirectory = path.join(projectRoot, 'dist');
const outputPath = path.join(outputDirectory, 'agentpress.zip');
const fixedDate = new Date('2026-08-30T00:00:00.000Z');

const files = [
  'agentpress.php',
  'composer.json',
  'composer.lock',
  'includes/Activation.php',
  'includes/Autoloader.php',
  'includes/Compatibility.php',
  'includes/Plugin.php',
  'readme.txt',
  'THIRD_PARTY_NOTICES.md',
  'third-party/webmcp-abilities/LICENSE',
  'third-party/webmcp-abilities/PINNED_COMMIT',
  'third-party/webmcp-abilities/PROVENANCE.json',
  'third-party/webmcp-abilities/README.md',
  'uninstall.php',
];

rmSync(outputDirectory, { force: true, recursive: true });
mkdirSync(outputDirectory, { recursive: true });

const output = createWriteStream(outputPath);
const archive = archiver('zip', {
  forceLocalTime: false,
  zlib: { level: 9 },
});

const completed = new Promise((resolve, reject) => {
  output.on('close', resolve);
  output.on('error', reject);
  archive.on('error', reject);
});

archive.pipe(output);

for (const relativePath of files.sort()) {
  archive.append(readFileSync(path.join(sourceRoot, relativePath)), {
    name: `agentpress/${relativePath.replaceAll('\\', '/')}`,
    date: fixedDate,
    mode: 0o644,
  });
}

archive.append(readFileSync(path.join(projectRoot, 'LICENSE')), {
  name: 'agentpress/LICENSE',
  date: fixedDate,
  mode: 0o644,
});

await archive.finalize();
await completed;

process.stdout.write(`${outputPath}\n`);
