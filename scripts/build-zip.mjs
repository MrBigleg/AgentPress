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
  'includes/Admin/AdminPage.php',
  'includes/Abilities/AbilityCatalog.php',
  'includes/Abilities/AbilityRegistrar.php',
  'includes/Audit/ArgumentSanitizer.php',
  'includes/Audit/AuditEventRepository.php',
  'includes/Audit/AuditLogger.php',
  'includes/Autoloader.php',
  'includes/Changes/ChangeRepository.php',
  'includes/Changes/ChangeCoordinator.php',
  'includes/Changes/ChangeSetRepository.php',
  'includes/Changes/ChangeSetStateReducer.php',
  'includes/Changes/StateHasher.php',
  'includes/Content/ContentReadService.php',
  'includes/Content/ContentUpdateService.php',
  'includes/Content/DraftCreationService.php',
  'includes/Context/ContextService.php',
  'includes/Context/SiteStructureService.php',
  'includes/Compatibility.php',
  'includes/Errors/ErrorFactory.php',
  'includes/Navigation/ClassicMenuAdapter.php',
  'includes/Navigation/NavigationReadService.php',
  'includes/Navigation/StageNavigationChangeService.php',
  'includes/Plugin.php',
  'includes/Policy/AgentCreatedDraftLookup.php',
  'includes/Policy/CapabilityEnvelope.php',
  'includes/Policy/CapabilityResolver.php',
  'includes/Policy/DiscoveryPolicy.php',
  'includes/Policy/ExecutionPolicy.php',
  'includes/Policy/RiskClassifier.php',
  'includes/Policy/SafeModePolicy.php',
  'includes/Results/ResultFactory.php',
  'includes/Terms/TermReadService.php',
  'includes/Terms/TermAssignmentService.php',
  'includes/Rest/RequestGuard.php',
  'includes/Rest/RequestRateLimiter.php',
  'includes/Rest/WebMCPRoutes.php',
  'includes/Storage/JsonCodec.php',
  'includes/Storage/Migrator.php',
  'includes/Storage/RecordStore.php',
  'includes/Schemas/CombinationRules.php',
  'includes/Schemas/SchemaBuilder.php',
  'includes/Schemas/SchemaValidator.php',
  'includes/WebMCP/AbilityMap.php',
  'readme.txt',
  'THIRD_PARTY_NOTICES.md',
  'third-party/webmcp-abilities/LICENSE',
  'third-party/webmcp-abilities/PINNED_COMMIT',
  'third-party/webmcp-abilities/PROVENANCE.json',
  'third-party/webmcp-abilities/README.md',
  'uninstall.php',
  'admin/src/admin-overview.css',
  'admin/src/admin-overview.mjs',
  'admin/src/webmcp-adapter.mjs',
];

const browserModule = {
  source: 'admin/src/webmcp-adapter.mjs',
  target: 'admin/build/webmcp-adapter.js',
};

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

// The adapter is native ESM and needs no transpilation for the target browser.
archive.append(readFileSync(path.join(sourceRoot, browserModule.source)), {
  name: `agentpress/${browserModule.target}`,
  date: fixedDate,
  mode: 0o644,
});

archive.append(readFileSync(path.join(projectRoot, 'LICENSE')), {
  name: 'agentpress/LICENSE',
  date: fixedDate,
  mode: 0o644,
});

await archive.finalize();
await completed;

process.stdout.write(`${outputPath}\n`);
