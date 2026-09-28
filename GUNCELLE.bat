@echo off
rem MST: bu klasordeki calisma ile GitHub'daki en guncel hali birlestirir. Cift tiklayin.
rem 1) Bu klasorde kaydedilmemis degisiklik varsa once kaydeder (hicbir sey silinmez).
rem 2) GitHub'daki main-dayiyo ile birlestirir.
rem 3) Basarirsa birlesmis hali GitHub'a gonderir; iki taraf ayni olur.
rem 4) Ayni satirlar iki tarafta da degistiyse birlestirmeyi geri alir, bu klasorun halini
rem    "yerel-caglar" adli ayri bir dala gonderir ve durur (Claude birlestirir).
rem Bu dosya klasorun disinda calistirilirsa mst-randevu klasorunu kendisi arar.
chcp 65001 >nul
setlocal
cd /d "%~dp0"
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
git checkout -q main-dayiyo || goto hata

rem 1) Kaydedilmemis degisiklikleri kaydet
git add -A
git diff --cached --quiet || git commit -q -m "Yerel calisma (GUNCELLE.bat ile kaydedildi)" || goto hata

rem 2) GitHub'daki en guncel hali al ve birlestir
echo En guncel hal indiriliyor...
git fetch -q origin main-dayiyo || goto hata
git merge --no-edit -q FETCH_HEAD
if errorlevel 1 goto cakisma

rem 3) Birlesmis hali GitHub'a gonder
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
