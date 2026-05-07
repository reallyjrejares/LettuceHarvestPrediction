# Smart Harvest Flask ML API - Setup Guide

## Overview
This directory contains a local Flask API server that loads your scikit-learn `.pkl` model and serves predictions to your CodeIgniter application.

## Prerequisites
- Python 3.8+ installed on your machine ([Download Python](https://www.python.org/))
- Your `model.pkl` file (downloaded from Google Colab)

## Setup Instructions

### Step 1: Place Your Model
1. Download your `.pkl` model file from Google Colab:
   ```python
   from google.colab import files
   files.download('your_model.pkl')
   ```

2. Place the downloaded file in this directory (`python_api/`) and rename it to:
   ```
   model.pkl
   ```

### Step 2: Install Dependencies (Automatic)

**Windows:**
- Double-click `run_server.bat`

**macOS/Linux:**
- Run: `bash run_server.sh`

The scripts will:
- Create a Python virtual environment
- Install all required packages
- Start the Flask server automatically

### Step 3: Verify It's Running
Open your browser and visit:
```
http://127.0.0.1:5000/health
```

You should see:
```json
{
  "ok": true,
  "model_loaded": true,
  "status": "running"
}
```

## Testing the API

### Using cURL (Command line):
```bash
curl -X POST http://127.0.0.1:5000/predict \
  -H "Content-Type: application/json" \
  -d '{
    "temperature_c": 22.5,
    "humidity_pct": 65,
    "tds_ppm": 1200,
    "ph_level": 6.5
  }'
```

### Using Python:
```python
import requests

response = requests.post('http://127.0.0.1:5000/predict', json={
    'temperature_c': 22.5,
    'humidity_pct': 65,
    'tds_ppm': 1200,
    'ph_level': 6.5
})

print(response.json())
```

## API Endpoints

### GET `/health`
Health check endpoint
```
Response: {"ok": true, "model_loaded": true, "status": "running"}
```

### POST `/predict`
Make a prediction
```
Request:
{
  "temperature_c": 22.5,
  "humidity_pct": 65,
  "tds_ppm": 1200,
  "ph_level": 6.5
}

Response:
{
  "ok": true,
  "prediction": 28.5,
  "days_to_harvest": 28
}
```

## Troubleshooting

### Issue: "Model file not found"
**Solution:** Make sure your `model.pkl` is in the same directory as `app.py`

### Issue: "Port 5000 is already in use"
**Solution:** 
- Close other applications using port 5000
- Or modify the port in `app.py`: change `port=5000` to `port=5001`

### Issue: "Python not found"
**Solution:** 
- Install Python from https://www.python.org/
- Make sure to check "Add Python to PATH" during installation

### Issue: Module import errors
**Solution:** 
- Delete the `venv` folder
- Re-run the setup script to reinstall dependencies

## Auto-start on Windows (Optional)

To start the server automatically when Windows starts:

1. Press `Win + R` and type: `shell:startup`
2. Create a shortcut to `run_server.bat` in the Startup folder
3. The server will start automatically on next boot

## Production Deployment

For production deployment on a server:
1. Use a production WSGI server like **Gunicorn** or **uWSGI**
2. Set up a reverse proxy (Nginx/Apache)
3. Use environment variables for configuration
4. Monitor logs and health checks
5. Set up auto-restart on server reboot

For help with production setup, contact your hosting provider or DevOps team.

## File Structure
```
python_api/
├── app.py                    # Flask application
├── model.pkl                 # Your ML model (place here)
├── requirements.txt          # Python dependencies
├── run_server.bat            # Windows launcher
├── run_server.sh             # macOS/Linux launcher
└── README.md                 # This file
```

## Next Steps
1. ✅ Place your `model.pkl` in this directory
2. ✅ Run `run_server.bat` (Windows) or `bash run_server.sh` (Mac/Linux)
3. ✅ Verify the API is working with the health check
4. ✅ Your CodeIgniter app is already configured to use `http://127.0.0.1:5000/predict`
5. ✅ Start making predictions!

---
**Questions?** Check the CodeIgniter app's `predictions.php` view or contact your development team.
