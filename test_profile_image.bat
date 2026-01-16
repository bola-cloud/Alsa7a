@echo off
setlocal

:: Set your token here
set TOKEN=3|o7Q9x2xZ... (Paste your token here if you want to hardcode, or pass it as arg)
if not "%~1"=="" set TOKEN=%~1

if "%TOKEN%"=="" (
    echo Usage: test_profile_image.bat [YOUR_TOKEN]
    echo Please provide your bearer token.
    exit /b 1
)

:: Create a dummy image if not exists
if not exist dummy.jpg (
    echo Creating dummy image...
    fsutil file createnew dummy.jpg 1024
)

echo.
echo --- Uploading Profile Image ---
echo Token: %TOKEN%
echo.

curl -X POST ^
  https://saha.wasl-x.com/api/v1/users/profile ^
  -H "Authorization: Bearer %TOKEN%" ^
  -H "Accept: application/json" ^
  -F "image=@dummy.jpg;type=image/jpeg" ^
  -k

echo.
echo.
echo Check the 'data.image' field in the response above. It should be a full URL now.
endlocal
