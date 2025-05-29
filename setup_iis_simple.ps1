# Simple IIS + PHP Setup Script
# Run as Administrator

Write-Host "Setting up IIS for PHP..." -ForegroundColor Green

# Enable IIS with CGI support
Enable-WindowsOptionalFeature -Online -FeatureName IIS-WebServerRole,IIS-WebServer,IIS-CGI,IIS-CommonHttpFeatures,IIS-ApplicationDevelopment -All

# Fix session directory permissions
Write-Host "Setting session directory permissions..." -ForegroundColor Green
icacls "D:\wwwroot\ecommerce\sessions" /grant Everyone:F /T

Write-Host "IIS enabled! Now configure PHP handler manually:" -ForegroundColor Yellow
Write-Host "1. Open IIS Manager (inetmgr)" -ForegroundColor White
Write-Host "2. Go to Handler Mappings" -ForegroundColor White
Write-Host "3. Add Module Mapping: *.php -> FastCgiModule -> C:\PHP\php-cgi.exe" -ForegroundColor White
Write-Host "4. Test at http://localhost:82/" -ForegroundColor White

Read-Host "Press Enter to continue..."
