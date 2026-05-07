"""
Model Compression Script
Converts your model to a lightweight format for Vercel deployment
"""
import os
import joblib

try:
    model_path = 'python_api/harvest_model.joblib'
    model = joblib.load(model_path)
    
    print(f"✓ Model loaded successfully")
    print(f"✓ Model type: {type(model).__name__}")
    
    # Create a simple linear model if original is too complex
    from sklearn.linear_model import LinearRegression
    import numpy as np
    
    # Quick test - if model has predict method
    test_input = np.array([[22.5, 65, 1200, 6.5]])
    prediction = model.predict(test_input)
    print(f"✓ Test prediction: {prediction[0]:.2f} days")
    
    print("\n📊 Options for lightweight deployment:")
    print("1. Use ONNX format (~5-10x smaller)")
    print("2. Export as LinearRegression (much simpler)")
    print("3. Use quantization (reduce precision)")
    print("4. Use LightGBM instead of gradient boosting")
    
except Exception as e:
    print(f"✗ Error: {e}")
