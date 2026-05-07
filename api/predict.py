import joblib
import json
import os
from pathlib import Path

# Load model once (cached between invocations)
MODEL_PATH = os.path.join(os.path.dirname(__file__), '..', 'python_api', 'harvest_model.joblib')

try:
    model = joblib.load(MODEL_PATH)
    model_loaded = True
except:
    model = None
    model_loaded = False


def handler(request):
    """
    Serverless function handler for Vercel
    Handles POST requests for predictions
    """
    
    # Only accept POST requests
    if request.method != 'POST':
        return {
            'statusCode': 405,
            'body': json.dumps({
                'ok': False,
                'error': 'Use POST method',
                'example': {
                    'temperature_c': 22.5,
                    'humidity_pct': 65,
                    'tds_ppm': 1200,
                    'ph_level': 6.5
                }
            })
        }
    
    # Check if model is loaded
    if not model_loaded or model is None:
        return {
            'statusCode': 500,
            'body': json.dumps({
                'ok': False,
                'error': 'Model not loaded'
            })
        }
    
    try:
        # Parse request body
        if isinstance(request.body, bytes):
            data = json.loads(request.body.decode('utf-8'))
        else:
            data = json.loads(request.body) if isinstance(request.body, str) else request.body
        
        # Extract features
        features = [
            float(data.get('temperature_c', 0)),
            float(data.get('humidity_pct', 0)),
            float(data.get('tds_ppm', 0)),
            float(data.get('ph_level', 0)),
        ]
        
        # Make prediction
        prediction = model.predict([features])[0]
        
        return {
            'statusCode': 200,
            'headers': {
                'Content-Type': 'application/json',
                'Access-Control-Allow-Origin': '*'
            },
            'body': json.dumps({
                'ok': True,
                'prediction': float(prediction),
                'days_to_harvest': int(prediction) if isinstance(prediction, (int, float)) else prediction
            })
        }
        
    except Exception as e:
        return {
            'statusCode': 400,
            'body': json.dumps({
                'ok': False,
                'error': str(e)
            })
        }
