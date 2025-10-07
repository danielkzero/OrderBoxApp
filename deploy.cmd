@echo off
SETLOCAL

echo 🚀 Iniciando deploy Laravel + Inertia + Vite...

REM Limpar caches Laravel
echo Limpando caches Laravel...
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

REM Verificar Blade
IF NOT EXIST resources\views\app.blade.php (
    echo ERROR: resources/views/app.blade.php nao encontrado!
    EXIT /B 1
)
echo Blade confirmado.

REM 3Instalar dependencias Node.js
echo Instalando dependencias Node.js...
call npm install --legacy-peer-deps

REM Build do Vite
echo Executando build do Vite...
call npm run build

REM Otimizacaoo final Laravel
echo Otimizando autoload e configs...
composer dump-autoload -o
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo concluído!

ENDLOCAL
pause
