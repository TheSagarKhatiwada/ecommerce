@echo off
echo === Quick IIS PHP Setup ===
echo.

REM Enable IIS and CGI
echo Enabling IIS features...
dism /online /enable-feature /featurename:IIS-WebServerRole /all /quiet
dism /online /enable-feature /featurename:IIS-CGI /all /quiet

REM Configure PHP
echo Configuring PHP...
%systemroot%\system32\inetsrv\appcmd.exe set config -section:system.webServer/handlers /+"[name='PHP-FastCGI',path='*.php',verb='*',modules='FastCgiModule',scriptProcessor='C:\PHP\php-cgi.exe',resourceType='Either']" /commit:apphost

echo.
echo Setup complete!
echo Test at: http://localhost/test_iis.php
pause
