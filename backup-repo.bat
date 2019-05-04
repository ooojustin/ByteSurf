@echo off

goto comment
Pre-requisites to run this:
The b2 cli (https://www.backblaze.com/b2/docs/quick_command_line.html)
Winrar (https://www.rarlab.com/download.htm)
Both b2.exe & rar.exe executable files must be accessible via PATH.
:comment

:: Establish date/time to be used in path of the online file.
for /f %%a in ('powershell -Command "Get-Date -format MM_dd_yyyy-HH_mm_ss"') do set datetime=%%a

:: Establish the name of the current directory.
for %%I in (.) do set dirname=%%~nxI

:: Set variables for local/online archive paths.
set localfile=bytesurf.io.rar
set onlinefile=bytesurf.io.%datetime%.rar

:: Initialize BackBlaze B2 API
echo Authorizing BackBlaze B2...
b2 authorize-account 002ac8e2726131f0000000001 K002mS7GgNS8n6iMOA7RK6y/Ww7fSKY

:: Put the current directory into an archive.
echo Archiving repository contents...
rar a %localfile% ../%dirname%

:: Upload the generated archive to our backup hosting.
echo Uploading file to %onlinefile%...
b2 upload-file bytesurf-backup %localfile% %onlinefile%

:: Delete the uploaded archive and we're done.
del %localfile%

echo Backup completed!
PAUSE