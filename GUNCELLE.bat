@echo off
rem MST: bu klasordeki calisma ile GitHub'daki en guncel hali birlestirir. Cift tiklayin.
rem - Once kendini gecici klasore kopyalayip oradan calisir; birlestirme bu dosyayi degistirse de bozulmaz.
rem - Yarim kalmis bir birlestirme varsa geri alir.
rem - Kaydedilmemis degisiklikleri kaydeder (hicbir sey silinmez), main-dayiyo ile birlestirir, gonderir.
rem - Yalnizca GUNCELLE.bat cakisirsa GitHub'daki surumu alir; baska dosya cakisirsa birlestirmeyi geri alir,
rem   bu klasorun halini "yerel-caglar" dalina gonderir ve durur (Claude birlestirir).
chcp 65001 >nul
setlocal
if /i not "%~1"=="--gecici" (
  copy /y "%~f0" "%TEMP%\mst-guncelle.bat" >nul
  call "%TEMP%\mst-guncelle.bat" --gecici "%~dp0"
  exit /b
)

cd /d "%~2"
if exist "demo-sunucu.js" goto bulundu
for %%d in ("%USERPROFILE%\Desktop\mst-randevu" "%USERPROFILE%\OneDrive\Desktop\mst-randevu" "%USERPROFILE%\Documents\mst-randevu" "%USERPROFILE%\mst-randevu" "C:\mst-randevu") do (
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

rem Yarim kalmis birlestirme varsa geri al
if exist ".git\MERGE_HEAD" (
  echo Yarim kalmis birlestirme geri aliniyor...
  git merge --abort
)
git checkout -q main-dayiyo || goto hata

rem Kaydedilmemis degisiklikleri kaydet
git add -A
git diff --cached --quiet || git commit -q -m "Yerel calisma (GUNCELLE.bat ile kaydedildi)" || goto hata

echo En guncel hal indiriliyor...
git fetch -q origin main-dayiyo || goto hata
git merge --no-edit -q FETCH_HEAD
if not errorlevel 1 goto gonder

rem Cakisma: yalnizca GUNCELLE.bat ise GitHub'daki surumu al
set "BASKA="
for /f "delims=" %%f in ('git diff --name-only --diff-filter=U') do (
  if /i not "%%f"=="GUNCELLE.bat" set "BASKA=1"
)
if defined BASKA goto cakisma
git checkout --theirs -- GUNCELLE.bat || goto cakisma
git add GUNCELLE.bat || goto cakisma
git commit --no-edit -q || goto cakisma

:gonder
echo GitHub'a gonderiliyor...
git push -q origin HEAD:main-dayiyo || goto hata
echo.
echo Tamam: bu klasor ve GitHub artik ayni, en guncel halde.
echo Onizleme aciksa sayfa kendiliginden yenilenir; acik degilse onizleme.bat'a cift tiklayin.
echo.
pause
exit /b 0

:cakisma
echo.
echo Iki tarafta da ayni yerler degismis; otomatik birlestirilemedi.
git merge --abort >nul 2>nul
echo Bu klasorun hali hicbir sey kaybolmadan "yerel-caglar" dalina gonderiliyor...
git push -q -f origin HEAD:refs/heads/yerel-caglar || goto hata
echo.
echo Tamam. Claude'a "yerel-caglar'a gonderdim" yazin; birlestirip haber verecek.
echo Sonra bu dosyaya tekrar cift tiklamaniz yeterli.
echo.
pause
exit /b 1

:hata
echo.
echo Bir adim tamamlanamadi. Bu pencerenin ekran goruntusunu gonderin.
echo.
pause
exit /b 1
