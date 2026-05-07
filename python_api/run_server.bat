@echo off
REM Flask API Server Launcher for Smart Harvest
REM This script starts the Python Flask API that serves ML predictions

echo ========================================
echo Smart Harvest - Flask ML API Server
echo ========================================
echo.

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python is not installed or not in PATH
    echo Please install Python from https://www.python.org/
    pause
    exit /b 1
)

echo [1/3] Checking Python installation...
python --version
echo.

REM Check if venv exists, if not create it
if not exist "venv" (
    echo [2/3] Creating Python virtual environment...
    python -m venv venv
    if errorlevel 1 (
        echo ERROR: Failed to create virtual environment
        pause
        exit /b 1
    )
) else (
    echo [2/3] Virtual environment already exists...
)
echo.

REM Activate venv and install/upgrade pip and requirements
echo [3/3] Installing dependencies...
call venv\Scripts\activate.bat
pip install --upgrade pip --quiet
pip install -r requirements.txt --quiet

if errorlevel 1 (
    echo ERROR: Failed to install dependencies
    pause
    exit /b 1
)

echo.
echo ========================================
echo Dependencies installed successfully!
echo ========================================
echo.
echo Starting Flask API Server...
echo Server will run on: http://127.0.0.1:5000
echo.
echo Press CTRL+C to stop the server
echo ========================================
echo.

REM Run the Flask app
python app.py

pause
