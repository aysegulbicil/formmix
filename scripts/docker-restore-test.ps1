param(
    [Parameter(Mandatory = $true)]
    [string]$BackupFile
)

$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$resolvedBackup = (Resolve-Path -LiteralPath $BackupFile).Path
$env:DOCKER_CONFIG = (Resolve-Path (Join-Path $projectRoot 'writable')).Path
$containerFile = '/tmp/formmix-restore-test.sql'
$createSql = (Resolve-Path (Join-Path $PSScriptRoot 'restore-test-create.sql')).Path
$dropSql = (Resolve-Path (Join-Path $PSScriptRoot 'restore-test-drop.sql')).Path
$countSql = (Resolve-Path (Join-Path $PSScriptRoot 'restore-test-count.sql')).Path

Push-Location $projectRoot
try {
    docker compose cp $resolvedBackup "db:$containerFile"
    if ($LASTEXITCODE -ne 0) {
        throw 'Yedek dosyası test konteynerine kopyalanamadı.'
    }

    docker compose cp $createSql 'db:/tmp/formmix-restore-create.sql'
    docker compose cp $dropSql 'db:/tmp/formmix-restore-drop.sql'
    docker compose cp $countSql 'db:/tmp/formmix-restore-count.sql'

    docker compose exec -T db sh -c 'exec mysql -u root -p"$MYSQL_ROOT_PASSWORD" < /tmp/formmix-restore-create.sql'
    if ($LASTEXITCODE -ne 0) {
        throw 'Geçici geri yükleme veritabanı oluşturulamadı.'
    }

    docker compose exec -T db sh -c 'mysql -u root -p"$MYSQL_ROOT_PASSWORD" formmix_restore_test < /tmp/formmix-restore-test.sql'
    if ($LASTEXITCODE -ne 0) {
        throw 'Yedek geçici veritabanına geri yüklenemedi.'
    }

    $tableCount = docker compose exec -T db sh -c 'exec mysql -N -u root -p"$MYSQL_ROOT_PASSWORD" < /tmp/formmix-restore-count.sql'
    if ($LASTEXITCODE -ne 0 -or [int]$tableCount -lt 1) {
        throw 'Geri yüklenen veritabanında tablo bulunamadı.'
    }

    Write-Output "Geri yükleme başarılı. Doğrulanan tablo sayısı: $tableCount"
} finally {
    docker compose exec -T db sh -c 'if [ -f /tmp/formmix-restore-drop.sql ]; then mysql -u root -p"$MYSQL_ROOT_PASSWORD" < /tmp/formmix-restore-drop.sql; fi' | Out-Null
    docker compose exec -T db sh -c 'rm -f /tmp/formmix-restore-test.sql /tmp/formmix-restore-create.sql /tmp/formmix-restore-drop.sql /tmp/formmix-restore-count.sql' | Out-Null
    Pop-Location
}
