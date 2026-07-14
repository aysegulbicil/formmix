param(
    [string]$OutputDirectory = (Join-Path $PSScriptRoot '..\backups')
)

$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$env:DOCKER_CONFIG = (Resolve-Path (Join-Path $projectRoot 'writable')).Path

if (-not (Test-Path -LiteralPath $OutputDirectory)) {
    New-Item -ItemType Directory -Path $OutputDirectory | Out-Null
}

$resolvedOutput = (Resolve-Path -LiteralPath $OutputDirectory).Path
$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupFile = Join-Path $resolvedOutput "formmix-$timestamp.sql"
$containerFile = '/tmp/formmix-backup.sql'

Push-Location $projectRoot
try {
    docker compose exec -T db sh -c 'exec mysqldump --single-transaction --no-tablespaces --routines --triggers -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" > /tmp/formmix-backup.sql'
    if ($LASTEXITCODE -ne 0) {
        throw 'MySQL yedeği oluşturulamadı.'
    }

    docker compose cp "db:$containerFile" $backupFile
    if ($LASTEXITCODE -ne 0) {
        throw 'Yedek dosyası Docker dışına kopyalanamadı.'
    }

    $file = Get-Item -LiteralPath $backupFile
    if ($file.Length -lt 100) {
        throw 'Oluşturulan yedek dosyası beklenenden küçük.'
    }

    Write-Output $file.FullName
} finally {
    docker compose exec -T db sh -c 'rm -f /tmp/formmix-backup.sql' | Out-Null
    Pop-Location
}
