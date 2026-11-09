@echo off
title Sistema de Matriculas - Universidad de La Salle
echo ========================================================
echo   INICIANDO SERVIDOR WEB LOCAL (PHP + BD 3 TABLAS)
echo   Universidad de La Salle - Asignatura: Bases de Datos
echo ========================================================
echo.

cd /d "%~dp0"

echo [1/2] Verificando interprete PHP de XAMPP...
if exist "C:\xampp\php\php.exe" (
    set PHP_EXE=C:\xampp\php\php.exe
) else (
    set PHP_EXE=php
)

echo [2/2] Abriendo el navegador en http://localhost:8000 ...
start "" "http://localhost:8000"

echo.
echo ========================================================
echo   Servidor iniciado en http://localhost:8000
echo   Presione Ctrl + C en esta ventana para detenerlo.
echo ========================================================
echo.

"%PHP_EXE%" -S localhost:8000
pause

