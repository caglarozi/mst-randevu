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
echo Baslangic: %TIME%
echo %CD% | findstr /i "OneDrive" >nul && echo UYARI: Klasor OneDrive icinde; OneDrive git'i cok yavaslatir. Klasoru C:\mst-randevu gibi OneDrive disina tasimaniz onerilir.

rem Git'i Windows'ta hizlandiran ayarlar (bir kez yazilir, zararsizdir)
git config core.fscache true >nul 2>nul
git config core.untrackedCache true >nul 2>nul
git config gc.auto 256 >nul 2>nul

rem Yarim kalmis birlestirme varsa geri al
if exist ".git\MERGE_HEAD" (
  echo Yarim kalmis birlestirme geri aliniyor...
  git merge --abort
)
git checkout -q main-dayiyo || goto hata

echo [1/3] Yerel degisiklikler kaydediliyor... %TIME%
git add -A
git diff --cached --quiet || git commit -q -m "Yerel calisma (GUNCELLE.bat ile kaydedildi)" || goto hata

echo [2/3] GitHub'dan en guncel hal indiriliyor... %TIME%
git fetch -q origin main-dayiyo || goto hata
set "GERIDE=0"
for /f %%n in ('git rev-list --count HEAD..FETCH_HEAD') do set "GERIDE=%%n"
if "%GERIDE%"=="0" goto gonder
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
rem Gonderme yalnizca GitHub'da olmayan yerel calisma varsa yapilir
set "ILERDE=0"
for /f %%n in ('git rev-list --count FETCH_HEAD..HEAD') do set "ILERDE=%%n"
if "%ILERDE%"=="0" (
  echo [3/3] Gonderilecek yerel degisiklik yok, atlandi.
) else (
  echo [3/3] Yerel degisiklikler GitHub'a gonderiliyor... %TIME%
  git push -q origin HEAD:main-dayiyo || goto hata
)
echo Bitis: %TIME%
echo.
if "%GERIDE%"=="0" (echo Zaten en guncel halde.) else (echo Tamam: en guncel hal indirildi.)
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
