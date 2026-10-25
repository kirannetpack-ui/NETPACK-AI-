const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

console.log('[Vercel Build] Starting Netpack AI build process...');

const projectRoot = path.resolve(__dirname, '..');
const vendorAutoload = path.join(projectRoot, 'vendor', 'autoload.php');

// 1. Ensure Composer dependencies are installed if missing (e.g. Git clone in Vercel CI)
if (!fs.existsSync(vendorAutoload)) {
    console.log('[Vercel Build] vendor/autoload.php not found. Preparing PHP & Composer...');
    try {
        const pkgDir = path.dirname(require.resolve('@libphp/almalinux-9-v85/package.json'));
        const phpBin = path.join(pkgDir, 'native', 'php', 'php');
        const phpLibDir = path.join(pkgDir, 'native', 'lib');
        const phpModulesDir = path.join(pkgDir, 'native', 'php', 'modules');
        const buildPhpIni = path.join(require('os').tmpdir(), 'netpack-build-php.ini');
        fs.writeFileSync(buildPhpIni, fs.readFileSync(path.join(pkgDir, 'native', 'php', 'php.ini'), 'utf8')
            .replace(/^extension_dir\s*=.*$/m, `extension_dir="${phpModulesDir}"`));
        
        try {
            fs.chmodSync(phpBin, 0o755);
        } catch (_) {}

        // Download composer.phar if not present
        const composerPhar = path.join(projectRoot, 'composer.phar');
        if (!fs.existsSync(composerPhar)) {
            console.log('[Vercel Build] Downloading composer.phar...');
            execSync('curl -sS https://getcomposer.org/download/latest-stable/composer.phar -o composer.phar', {
                cwd: projectRoot,
                stdio: 'inherit'
            });
        }

        const env = {
            ...process.env,
            PHPRC: buildPhpIni,
            PATH: `${path.dirname(phpBin)}:${process.env.PATH}`,
            LD_LIBRARY_PATH: `${phpLibDir}:/usr/lib64:/lib64:${process.env.LD_LIBRARY_PATH || ''}`
        };

        console.log('[Vercel Build] Running composer install --no-dev --optimize-autoloader...');
        execSync(`"${phpBin}" -c "${buildPhpIni}" "${composerPhar}" install --no-dev --prefer-dist --optimize-autoloader --no-interaction`, {
            cwd: projectRoot,
            env,
            stdio: 'inherit'
        });
        console.log('[Vercel Build] Composer dependencies successfully installed.');
    } catch (err) {
        throw new Error('[Vercel Build] Composer installation failed: ' + err.message);
    }
} else {
    console.log('[Vercel Build] vendor/autoload.php found. Skipping composer install.');
}

// 2. Run production migrations only when explicitly requested for a controlled deployment.
// This is intentionally opt-in so ordinary deployments never modify production data.
if (process.env.NETPACK_RUN_MIGRATIONS === 'true') {
    console.log('[Vercel Build] Running requested production database migrations...');

    try {
        const pkgDir = path.dirname(require.resolve('@libphp/almalinux-9-v85/package.json'));
        const phpBin = path.join(pkgDir, 'native', 'php', 'php');
        const phpLibDir = path.join(pkgDir, 'native', 'lib');
        const phpModulesDir = path.join(pkgDir, 'native', 'php', 'modules');
        const migrationPhpIni = path.join(require('os').tmpdir(), 'netpack-migration-php.ini');
        const sourcePhpIni = path.join(pkgDir, 'native', 'php', 'php.ini');

        fs.writeFileSync(migrationPhpIni, fs.readFileSync(sourcePhpIni, 'utf8')
            .replace(/^extension_dir\s*=.*$/m, `extension_dir="${phpModulesDir}"`));

        // Neon poolers are ideal for web requests, while migrations must use
        // the direct endpoint so DDL runs in a dedicated database session.
        const migrationHost = process.env.PGHOST_UNPOOLED || process.env.PGHOST;
        const neonEndpoint = migrationHost?.endsWith('.neon.tech')
            ? migrationHost.split('.')[0]
            : undefined;

        execSync(`"${phpBin}" -c "${migrationPhpIni}" artisan migrate --force --no-interaction -vvv`, {
            cwd: projectRoot,
            env: {
                ...process.env,
                APP_ENV: 'production',
                DATABASE_URL: process.env.DATABASE_URL_UNPOOLED || process.env.DATABASE_URL,
                PGHOST: migrationHost,
                PHPRC: migrationPhpIni,
                PATH: `${path.dirname(phpBin)}:${process.env.PATH}`,
                LD_LIBRARY_PATH: `${phpLibDir}:/usr/lib64:/lib64:${process.env.LD_LIBRARY_PATH || ''}`,
                ...(neonEndpoint ? { PGOPTIONS: process.env.PGOPTIONS || `endpoint=${neonEndpoint}` } : {})
            },
            stdio: 'inherit'
        });

        console.log('[Vercel Build] Production database migrations completed.');

        if (process.env.NETPACK_DEMO_SEED === 'true') {
            console.log('[Vercel Build] Seeding explicitly enabled demo accounts...');
            execSync(`"${phpBin}" -c "${migrationPhpIni}" artisan db:seed --force --class='Database\\Seeders\\UserSeeder' --no-interaction`, {
                cwd: projectRoot,
                env: {
                    ...process.env,
                    APP_ENV: 'production',
                    APP_DEPLOYMENT_MODE: 'demo',
                    NETPACK_DEMO_SEED: 'true',
                    DATABASE_URL: process.env.DATABASE_URL_UNPOOLED || process.env.DATABASE_URL,
                    PGHOST: migrationHost,
                    PHPRC: migrationPhpIni,
                    PATH: `${path.dirname(phpBin)}:${process.env.PATH}`,
                    LD_LIBRARY_PATH: `${phpLibDir}:/usr/lib64:/lib64:${process.env.LD_LIBRARY_PATH || ''}`,
                    ...(neonEndpoint ? { PGOPTIONS: process.env.PGOPTIONS || `endpoint=${neonEndpoint}` } : {})
                },
                stdio: 'inherit'
            });
            console.log('[Vercel Build] Demo accounts seeded.');
        }
    } catch (err) {
        throw new Error('[Vercel Build] Production migration failed: ' + err.message);
    }
}

// 3. Build Vite assets
console.log('[Vercel Build] Building Vite assets...');
execSync('npx vite build', { cwd: projectRoot, stdio: 'inherit' });

// 4. Ensure both public and dist exist for Vercel Output Directory detection
const publicDir = path.join(projectRoot, 'public');
const distDir = path.join(projectRoot, 'dist');
if (!fs.existsSync(distDir)) {
    fs.mkdirSync(distDir, { recursive: true });
}
fs.cpSync(publicDir, distDir, { recursive: true });
// PHP is executed inside the function bundle, never published as a static file.
const staticFrontController = path.join(distDir, 'index.php');
if (fs.existsSync(staticFrontController)) fs.unlinkSync(staticFrontController);
console.log('[Vercel Build] Synced public assets to dist directory.');

console.log('[Vercel Build] Build completed successfully.');
