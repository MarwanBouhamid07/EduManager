@echo off
setlocal
echo ==========================================
echo   STUDENT PAYMENT SYSTEM - DATABASE SETUP
echo ==========================================
echo.

:: Try to find PHP
if exist "C:\xampp\php\php.exe" (
    set PHP_EXE="C:\xampp\php\php.exe"
) else if exist "C:\wamp64\bin\php\php*\php.exe" (
    :: Heuristic for WAMP, might need adjustment if multiple versions
    for /f "delims=" %%F in ('dir /b /s "C:\wamp64\bin\php\php*\php.exe"') do set PHP_EXE="%%F"
) else (
    set PHP_EXE=php
)

echo Found PHP at: %PHP_EXE%
echo.

%PHP_EXE% setup_db.php

if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Database setup failed.
)

echo.
pause
