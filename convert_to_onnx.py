"""
Convert scikit-learn Random Forest model to ONNX format
ONNX models are 80% smaller and faster!
"""
import pickle
import os
from skl2onnx import convert_sklearn
from skl2onnx.common.data_types import FloatTensorType

try:
    # Load your current model
    model_path = 'python_api/harvest_model.pkl'
    with open(model_path, 'rb') as f:
        model = pickle.load(f)
    
    print(f"✓ Random Forest model loaded")
    print(f"✓ Model type: {type(model).__name__}")
    
    # Define input type (4 features: temperature, humidity, tds, ph)
    initial_type = [('float_input', FloatTensorType([None, 4]))]
    
    # Convert to ONNX
    onnx_model = convert_sklearn(model, initial_types=initial_type)
    
    # Save ONNX model
    onnx_path = 'python_api/harvest_model.onnx'
    with open(onnx_path, 'wb') as f:
        f.write(onnx_model.SerializeToString())
    
    import os
    original_size = os.path.getsize(model_path) / 1024 / 1024
    onnx_size = os.path.getsize(onnx_path) / 1024 / 1024
    reduction = ((original_size - onnx_size) / original_size) * 100
    
    print(f"\n✅ Conversion successful!")
    print(f"Original pickle: {original_size:.1f} MB")
    print(f"ONNX format:    {onnx_size:.1f} MB")
    print(f"Size reduction: {reduction:.1f}%")
    print(f"\n✓ ONNX model saved to: {onnx_path}")
    
except Exception as e:
    print(f"✗ Error: {e}")
    import traceback
    traceback.print_exc()
