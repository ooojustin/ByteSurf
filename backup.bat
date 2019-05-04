@echo off

goto comment
Pre-requisites to run this:
MySQL v5.6 Server binaries (https://dev.mysql.com/downloads/windows/installer/5.6.html)
The b2 cli (https://www.backblaze.com/b2/docs/quick_command_line.html)
Winrar (https://www.rarlab.com/download.htm)
Commands 'b2', 'rar', and 'mysqldump' must be accessible via the windows PATH variable.
:comment

:: Establish date/time to be used in path of the online file.
for /f %%a in ('powershell -Command "Get-Date -format MM_dd_yyyy-HH_mm_ss"') do set datetime=%%a

:: Establish the name of the current directory.
for %%I in (.) do set dirname=%%~nxI

:: Set variables for local/online archive paths.
set localfile=bytesurf.io.rar
set onlinefile=bytesurf.io.%datetime%.rar

:: Set MySQL username, password, and host. Dump database to file.
echo Dumping MySQL database to local file...
set mysql_host=185.207.206.50
set mysql_username=bytesurf_db
set mysql_password=ad!lb36IUL2mFDf*C75X0#db
mysqldump -u %mysql_username% -p%mysql_password% -h %mysql_host% bytesurf_db > database.sql 2>NUL

:: Put the current directory into an archive.
echo Archiving repository contents...
rar a %localfile% ../%dirname% >NUL

:: Initialize BackBlaze B2 API
echo Authorizing BackBlaze B2...
b2 authorize-account 002ac8e2726131f0000000001 K002mS7GgNS8n6iMOA7RK6y/Ww7fSKY >NUL

:: Upload the generated archive to our backup hosting.
echo Uploading file to %onlinefile%...
b2 upload-file bytesurf-backup %localfile% %onlinefile%

:: Delete the database + uploaded archive and we're done.
del database.sql
del %localfile%

echo Backup completed!
PAUSE