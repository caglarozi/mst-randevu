@echo off
rem MST: GitHub'daki en guncel hali bu klasore indirir. Cift tiklayin.
rem Bu klasorde yapilan calismayi silmez; cakisma olursa hicbir seyi degistirmeden durur.
rem Bu dosya klasorun disinda (ornegin Indirilenler'de) calistirilirsa mst-randevu klasorunu kendisi arar.
chcp 65001 >nul
setlocal
cd /d "%~dp0"
if exist "demo-sunucu.js" goto bulundu
for %%d in ("%USERPROFILE%\Desktop\mst-randevu" "%USERPROFILE%\OneDrive\Desktop\mst-randevu" "%USERPROFILE%\OneDrive\Masaüstü\mst-randevu" "%USERPROFILE%\Documents\mst-randevu" "%USERPROFILE%\mst-randevu" "C:\mst-randevu") do (
  if exist "%%~d\demo-sunucu.js" (cd /d "%%~d" & goto bulundu)
)
echo.
echo mst-randevu klasoru bulunamadi.
echo Bu dosyayi mst-randevu klasorunun (onizleme.bat'in oldugu yer) icine tasiyip tekrar cift tiklayin.
echo.
pause
exit /b 1

:bulundu
where git >nul 2>nul || (echo Git bulunamadi: https://git-scm.com adresinden kurun. & pause & exit /b 1)
echo Klasor: %CD%
echo En guncel hal indiriliyor...
echo.
git pull --ff-only origin main-dayiyo
if errorlevel 1 goto hata
echo.
echo Tamam, en guncel hal indirildi. Onizleme aciksa sayfa kendiliginden yenilenir.
echo Acik degilse onizleme.bat'a cift tiklayin.
echo.
pause
exit /b 0

:hata
echo.
echo Guncelleme yapilamadi; bu klasordeki hicbir sey degistirilmedi.
echo Bu pencerenin ekran goruntusunu gonderin.
echo.
pause
exit /b 1
