@ECHO OFF
SETLOCAL
SET command=%1
SET verbose=1

REM ----------------------------------------------------------------------------------------------------------------
REM --- Versions ---------------------------------------------------------------------------------------------------
REM ----------------------------------------------------------------------------------------------------------------
IF "%command%"=="version_test" (
	call php Framework/Manager.php version test 0
)
IF "%command%"=="version_commit" (
	call php Framework/Manager.php version commit 0
)

REM --------------------------------------------------------------------------------------------------------------------------------------------
REM --- Help commands --------------------------------------------------------------------------------------------------------------------------
REM --------------------------------------------------------------------------------------------------------------------------------------------
IF "%command%"=="help" (
	echo "Available commands:"
	echo "  Version commands:"
	echo "		make version_test - test version"
	echo "		make version_commit - commit version"
)

REM force execution to quit at the end of the "main" logic
exit /B %ERRORLEVEL%