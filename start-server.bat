@echo off
title FastClaim Server

:: Set path ke Laragon PHP & Composer
set PATH=C:\laragon\bin\php\php-8.3.12-nts-Win32-vs16-x64;%PATH%
set PATH=C:\laragon\bin\composer;%PATH%

:: Pindah ke direktori project
cd /d "D:\Web Development Reyhan\kubr-claim"

:: Jalankan composer prod (atau perintah lain)
call composer run prod

pause
