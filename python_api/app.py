"""
Flask API for Lettuce Harvest Prediction
Loads scikit-learn model and serves predictions
"""
import os
import json
import pickle
from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# Load the model
MODEL_PATH = os.path.join(os.path.dirname(__file__), 'model.pkl')

try:
    with open(MODEL_PATH, 'rb') as f:
        model = pickle.load(f)
    print(f"✓ Model loaded successfully from {MODEL_PATH}")
except FileNotFoundError:
    print(f"✗ Model file not found at {MODEL_PATH}")
    model = None
except Exception as e:
    print(f"✗ Error loading model: {e}")
    model = None


@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'ok': True,
        'model_loaded': model is not None,
        'status': 'running'
    }), 200


@app.route('/predict', methods=['POST'])
def predict():
    """
    Prediction endpoint
    Expects JSON with plant features
    """
    if model is None:
        return jsonify({
            'ok': False,
            'error': 'Model not loaded'
        }), 500
    
    try:
        data = request.get_json()
        
        if not data:
            return jsonify({
                'ok': False,
                'error': 'No data provided'
            }), 400
        
        # Extract features - adjust these based on your model's input features
        features = [
            data.get('temperature_c', 0),
            data.get('humidity_pct', 0),
            data.get('tds_ppm', 0),
            data.get('ph_level', 0),
        ]
        
        # Make prediction
        prediction = model.predict([features])[0]
        
        return jsonify({
            'ok': True,
            'prediction': float(prediction),
            'days_to_harvest': int(prediction) if isinstance(prediction, (int, float)) else prediction
        }), 200
        
    except Exception as e:
        return jsonify({
            'ok': False,
            'error': str(e)
        }), 500


@app.route('/predict', methods=['GET'])
def predict_get():
    """Handle GET requests for testing"""
    return jsonify({
        'ok': False,
        'error': 'Use POST method with JSON data',
        'example': {
            'temperature_c': 22.5,
            'humidity_pct': 65,
            'tds_ppm': 1200,
            'ph_level': 6.5
        }
    }), 405


if __name__ == '__main__':
    # Run on localhost:5000
    print("Starting Flask API on http://127.0.0.1:5000")
    print("Prediction endpoint: POST http://127.0.0.1:5000/predict")
    app.run(host='127.0.0.1', port=5000, debug=False)
