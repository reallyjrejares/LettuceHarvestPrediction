#!/bin/bash
# Flask API Server Launcher for Smart Harvest (Linux/Mac)

echo "========================================"
echo "Smart Harvest - Flask ML API Server"
echo "========================================"
echo ""

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo "ERROR: Python is not installed"
    echo "Please install Python 3 from https://www.python.org/"
    exit 1
fi

echo "[1/3] Checking Python installation..."
python3 --version
echo ""

# Check if venv exists, if not create it
if [ ! -d "venv" ]; then
    echo "[2/3] Creating Python virtual environment..."
    python3 -m venv venv
    if [ $? -ne 0 ]; then
        echo "ERROR: Failed to create virtual environment"
        exit 1
    fi
else
    echo "[2/3] Virtual environment already exists..."
fi
echo ""

# Activate venv and install requirements
echo "[3/3] Installing dependencies..."
source venv/bin/activate
pip install --upgrade pip --quiet
pip install -r requirements.txt --quiet

if [ $? -ne 0 ]; then
    echo "ERROR: Failed to install dependencies"
    exit 1
fi

echo ""
echo "========================================"
echo "Dependencies installed successfully!"
echo "========================================"
echo ""
echo "Starting Flask API Server..."
echo "Server will run on: http://127.0.0.1:5000"
echo ""
echo "Press CTRL+C to stop the server"
echo "========================================"
echo ""

# Run the Flask app
python3 app.py
