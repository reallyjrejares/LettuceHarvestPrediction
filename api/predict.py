import joblib
import json
import os
from pathlib import Path

# Resolve the model path across deployment environments
ROOT = Path(__file__).resolve().parent
MODEL_CANDIDATES = [
    ROOT / 'harvest_model.joblib',
    ROOT.parent / 'python_api' / 'harvest_model.joblib',
    ROOT.parent.parent / 'python_api' / 'harvest_model.joblib',
]
MODEL_PATH = next((str(path) for path in MODEL_CANDIDATES if path.exists()), str(MODEL_CANDIDATES[0]))

try:
    model = joblib.load(MODEL_PATH)
    model_loaded = True
    print(f'✓ Model loaded successfully from {MODEL_PATH}')
except Exception as e:
    model = None
    model_loaded = False
    print(f'✗ Failed to load model from {MODEL_PATH}: {e}')


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
