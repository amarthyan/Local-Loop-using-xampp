@echo off
setlocal enabledelayedexpansion
title LocalLoop - Community Platform Launcher

echo ==========================================================
echo          LocalLoop - Hyperlocal Community Platform        
echo ==========================================================
echo.

:: Detect XAMPP path relative to script or standard installation
set "XAMPP_DIR="
if exist "%~dp0..\..\apache\bin\httpd.exe" (
    pushd "%~dp0..\.."
    set "XAMPP_DIR=!CD!"
    popd
) else if exist "C:\xampp\apache\bin\httpd.exe" (
    set "XAMPP_DIR=C:\xampp"
)

:: 1. Check & Start Apache
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe" >NUL
if "%ERRORLEVEL%"=="0" (
    echo  [OK] Apache is running.
) else (
    echo  [..] Starting Apache web server...
    if defined XAMPP_DIR (
        if exist "!XAMPP_DIR!\apache_start.bat" (
            start "" /B cmd /c "!XAMPP_DIR!\apache_start.bat"
        ) else if exist "!XAMPP_DIR!\apache\bin\httpd.exe" (
            start "" /B "!XAMPP_DIR!\apache\bin\httpd.exe"
        )
    ) else (
        echo  [!] Warning: Could not locate Apache binary. Make sure XAMPP is running.
    )
    timeout /t 2 /nobreak >nul
)

:: 2. Check & Start MySQL
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe" >NUL
if "%ERRORLEVEL%"=="0" (
    echo  [OK] MySQL database is running.
) else (
    echo  [..] Starting MySQL database server...
    if defined XAMPP_DIR (
        if exist "!XAMPP_DIR!\mysql_start.bat" (
            start "" /B cmd /c "!XAMPP_DIR!\mysql_start.bat"
        ) else if exist "!XAMPP_DIR!\mysql\bin\mysqld.exe" (
            start "" /B "!XAMPP_DIR!\mysql\bin\mysqld.exe" --defaults-file="!XAMPP_DIR!\mysql\bin\my.ini" --standalone
        )
    ) else (
        echo  [!] Warning: Could not locate MySQL binary. Make sure XAMPP is running.
    )
    timeout /t 2 /nobreak >nul
)

echo.
echo  ----------------------------------------------------------
echo   Project URL : http://localhost/BCN%%20project/
echo   Admin URL   : http://localhost/BCN%%20project/admin/
echo   phpMyAdmin  : http://localhost/phpmyadmin/
echo  ----------------------------------------------------------
echo.
echo  [i] Launching LocalLoop in your default browser...
echo.

:: Launch the browser (using %%20 to preserve the encoded space)
start "" "http://localhost/BCN%%20project/"

echo ==========================================================
echo  Demo Credentials:
echo   - Admin:      admin        / admin123
echo   - Resident:   rahulk       / password123
echo   - Business:   malabarbakes / password123
echo   - Helper:     ananya_doc   / password123
echo ==========================================================
echo.
echo  [R] Reopen LocalLoop in Browser
echo  [A] Open Admin Panel
echo  [P] Open phpMyAdmin
echo  [Q] Exit this window
echo.

:menu
choice /C RAPQ /N /M "Choose an option [R, A, P, Q]: "
if errorlevel 4 goto exit
if errorlevel 3 goto phpmyadmin
if errorlevel 2 goto admin
if errorlevel 1 goto reopen

:reopen
start "" "http://localhost/BCN%%20project/"
echo  Reopened LocalLoop in browser.
goto menu

:admin
start "" "http://localhost/BCN%%20project/admin/"
echo  Opened Admin Panel in browser.
goto menu

:phpmyadmin
start "" "http://localhost/phpmyadmin/"
echo  Opened phpMyAdmin in browser.
goto menu

:exit
exit
