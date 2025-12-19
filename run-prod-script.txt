@echo off
title FastClaim Server

:: Set path ke Laragon PHP & Composer
set PATH=C:\laragon\bin\php\php-8.3.12-Win32-vs16-x64;%PATH%
set PATH=C:\laragon\bin\composer;%PATH%
set PATH=C:\laragon\bin\nodejs\node-v24.11.1-win-x64;%PATH%

:: Pindah ke direktori project
cd /d "C:\laragon\www\kubr-claim"

:: Jalankan composer prod
call composer run prod

pause
