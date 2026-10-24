param([string]$OutputDirectory = 'docs/audit')
$ErrorActionPreference = 'Stop'
New-Item -ItemType Directory -Force -Path $OutputDirectory | Out-Null
$sourceFiles = @(rg --files app routes config database resources tests public scripts .github api -g '!public/build/**' -g '!public/docs/images/**' -g '!database/*.sqlite')
$sourceFiles += @('server.php', 'composer.json', 'composer.lock', 'package.json', 'package-lock.json', 'capacitor.config.json', 'vercel.json', 'vite.config.mjs', '.env.example', '.env.production.example')
$sourceFiles = @($sourceFiles | Sort-Object -Unique)
$oldFunctions = @{}
$inventoryPath = Join-Path $OutputDirectory 'function-inventory.csv'
if (Test-Path -LiteralPath $inventoryPath) {
    foreach ($row in (Import-Csv -LiteralPath $inventoryPath)) { $oldFunctions[$row.File + ':' + $row.Function] = $row }
}
$functions = foreach ($file in ($sourceFiles | Where-Object { $_ -like '*.php' -and $_ -notlike '*.blade.php' })) {
    $line = 0
    foreach ($contentLine in [IO.File]::ReadLines((Join-Path $PWD $file))) {
        $line++
        if ($contentLine -match '\bfunction\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(') {
            $name = $Matches[1]
            $normalized = $file.Replace('\', '/')
            $existing = $oldFunctions[$normalized + ':' + $name]
            [pscustomobject]@{File=$normalized; Function=$name; Line=$line; Status=$(if($existing){$existing.Status}else{'Unreviewed'}); Evidence=$(if($existing){$existing.Evidence}else{'Declaration inventoried; behavior review pending'})}
        }
    }
}
$functions | Export-Csv -LiteralPath $inventoryPath -NoTypeInformation
$sourceFiles | ForEach-Object { [pscustomobject]@{File=$_.Replace('\','/');Status='Inventoried; see release report for reviewed workflows'} } | Export-Csv -LiteralPath (Join-Path $OutputDirectory 'file-inventory.csv') -NoTypeInformation
$routeJson = & php artisan route:list --json
if ($LASTEXITCODE -ne 0) { throw 'Route inventory failed.' }
$routes = $routeJson | ConvertFrom-Json
$routes | Select-Object method,uri,name,action,@{Name='middleware';Expression={$_.middleware -join '; '}} | Export-Csv -LiteralPath (Join-Path $OutputDirectory 'route-inventory.csv') -NoTypeInformation
[pscustomobject]@{Files=$sourceFiles.Count;NamedPhpDeclarations=@($functions).Count;Routes=@($routes).Count} | ConvertTo-Json
