@echo off
rem MST yerel onizleme: cift tiklayin.
rem Bu klasordeki calismayi korur; GitHub'dan indirip dosyalari sifirlamaz.
rem Demo veya stil dosyalari degisince acik sayfayi yeniler.
rem Acik kaldigi surece GitHub'daki yeni surumleri 20 saniyede bir kendisi indirir; elle guncellemeye gerek yok.
rem Baska sayfa icin: onizleme.bat akademi   (randevu, uygulama, akademi, akademi-randevu, cinebook)
chcp 65001 >nul
cd /d "%~dp0"
set "SAYFA=%~1"
if "%SAYFA%"=="" set "SAYFA=cinebook"
if /i "%SAYFA%"=="randevu" set "SAYFA="

where node >nul 2>nul || (echo Node.js bulunamadi: https://nodejs.org adresinden kurun. & pause & exit /b 1)

rem Eski onizleme aciksa kapat, yenisini baslat
for /f "tokens=5" %%p in ('netstat -ano ^| findstr ":8788" ^| findstr "LISTENING"') do taskkill /pid %%p /f >nul 2>nul
echo Canli onizleme baslatiliyor...
start "MST yerel onizleme - kapatmayin" /min cmd /k node demo-sunucu.js --canli
timeout /t 2 >nul
start "" http://localhost:8788/%SAYFA%
exit /b 0
