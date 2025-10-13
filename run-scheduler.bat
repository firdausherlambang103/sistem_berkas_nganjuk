@echo off
echo Starting Laravel Scheduler...
:loop
php artisan schedule:run
echo Waiting for 60 seconds...

REM Menggunakan sintaks timeout yang lebih sederhana dan kompatibel
timeout 60 > NUL

goto loop