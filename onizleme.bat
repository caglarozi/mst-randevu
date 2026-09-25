@echo off
rem MST onizleme: cift tiklayin. En guncel hali indirir, onizleme kapaliysa acar,
rem tarayicida Akademi sayfasini gosterir. Bu klasorde elle yapilan degisiklikler silinir.
chcp 65001 >nul
cd /d "%~dp0"
echo En guncel hal indiriliyor...
git fetch -q origin main-dayiyo || goto hata
git checkout -q main-dayiyo || goto hata
git reset -q --hard origin/main-dayiyo || goto hata
netstat -ano | findstr ":8788" | findstr "LISTENING" >nul
if errorlevel 1 (
  echo Onizleme baslatiliyor...
  start "MST onizleme - kapatmayin" cmd /k node demo-sunucu.js
  timeout /t 2 >nul
)
start "" http://localhost:8788/akademi
exit /b 0
:hata
echo.
echo Guncelleme yapilamadi. Bu pencerenin ekran goruntusunu gonderin.
pause
