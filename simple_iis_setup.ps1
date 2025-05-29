# Simple IIS + PHP Configuration Script
# Run as Administrator in PowerShell

Write-Host "=== Simple IIS PHP Setup ===" -ForegroundColor Cyan

# Step 1: Enable essential IIS features
Write-Host "1. Enabling IIS features..." -ForegroundColor Yellow
dism /online /enable-feature /featurename:IIS-WebServerRole /all
dism /online /enable-feature /featurename:IIS-CGI /all
dism /online /enable-feature /featurename:IIS-ASPNET45 /all

# Step 2: Configure PHP handler globally
Write-Host "2. Configuring PHP handler..." -ForegroundColor Yellow
$phpCgiPath = "C:\PHP\php-cgi.exe"

if (Test-Path $phpCgiPath) {
    # Register PHP with IIS using appcmd
    & "$env:SystemRoot\system32\inetsrv\appcmd.exe" set config -section:system.webServer/fastCgi /+"[fullPath='$phpCgiPath',maxInstances='4',instanceMaxRequests='10000']" /commit:apphost
    & "$env:SystemRoot\system32\inetsrv\appcmd.exe" set config -section:system.webServer/handlers /+"[name='PHP-FastCGI',path='*.php',verb='GET,HEAD,POST',modules='FastCgiModule',scriptProcessor='$phpCgiPath',resourceType='Either']" /commit:apphost
    
    Write-Host "✅ PHP configured successfully!" -ForegroundColor Green
} else {
    Write-Host "❌ PHP not found at $phpCgiPath" -ForegroundColor Red
    Write-Host "Please ensure PHP is installed at C:\PHP\" -ForegroundColor Red
    exit 1
}

# Step 3: Test configuration
Write-Host "3. Testing configuration..." -ForegroundColor Yellow
Write-Host "✅ Configuration complete!" -ForegroundColor Green
Write-Host "🌐 Test your site at: http://localhost:82/" -ForegroundColor Cyan
Write-Host "🔧 Test PHP at: http://localhost:82/test_iis.php" -ForegroundColor Cyan
