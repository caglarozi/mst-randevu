@echo off
rem MST canli onizleme: cift tiklayin, sonra bu pencereyi unutun.
rem - En guncel hali GitHub'dan indirir (bu klasorde elle yapilan degisiklikler silinir).
rem - Onizlemeyi arka planda acar; yeni surum geldikce kendiliginden indirir ve
rem   tarayicidaki sayfayi kendiliginden yeniler. Tekrar indirmeye, ayiklamaya gerek yok.
rem Baska sayfa icin: onizleme.bat akademi   (randevu, uygulama, akademi, akademi-randevu, cinebook)
chcp 65001 >nul
cd /d "%~dp0"
set "SAYFA=%~1"
if "%SAYFA%"=="" set "SAYFA=cinebook"
if /i "%SAYFA%"=="randevu" set "SAYFA="

where git >nul 2>nul || (echo Git bulunamadi: https://git-scm.com adresinden kurun. & pause & exit /b 1)
where node >nul 2>nul || (echo Node.js bulunamadi: https://nodejs.org adresinden kurun. & pause & exit /b 1)

echo En guncel hal indiriliyor...
git fetch -q origin main-dayiyo || goto hata
git checkout -q main-dayiyo || goto hata
git reset -q --hard origin/main-dayiyo || goto hata

rem Eski (canli olmayan) onizleme aciksa kapat, canli olani baslat
for /f "tokens=5" %%p in ('netstat -ano ^| findstr ":8788" ^| findstr "LISTENING"') do taskkill /pid %%p /f >nul 2>nul
echo Canli onizleme baslatiliyor...
start "MST canli onizleme - kapatmayin" /min cmd /k node demo-sunucu.js --canli
timeout /t 2 >nul
start "" http://localhost:8788/%SAYFA%
exit /b 0

:hata
echo.
echo Guncelleme yapilamadi. Bu pencerenin ekran goruntusunu gonderin.
pause
