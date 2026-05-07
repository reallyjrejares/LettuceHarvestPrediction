"""
Compress the existing model using joblib compression
Joblib handles version mismatches better than pickle
"""
import pickle
import joblib
import os

try:
    # Load the existing pickle model
    model_path = 'python_api/harvest_model.pkl'
    print("Loading model...")
    
    with open(model_path, 'rb') as f:
        model = pickle.load(f)
    
    print(f"✓ Model loaded: {type(model).__name__}")
    
    # Save using joblib with compression (zlib, gzip, or lz4)
    joblib_path = 'python_api/harvest_model_compressed.joblib'
    print(f"Compressing with joblib...")
    
    joblib.dump(model, joblib_path, compress=3)  # compression level 3 (good balance)
    
    # Compare sizes
    original_size = os.path.getsize(model_path) / 1024 / 1024
    compressed_size = os.path.getsize(joblib_path) / 1024 / 1024
    reduction = ((original_size - compressed_size) / original_size) * 100
    
    print(f"\n✅ Compression successful!")
    print(f"Original:   {original_size:.1f} MB")
    print(f"Compressed: {compressed_size:.1f} MB")
    print(f"Reduction:  {reduction:.1f}%")
    print(f"\n✓ Compressed model: {joblib_path}")
    
except Exception as e:
    print(f"✗ Error: {e}")
    import traceback
    traceback.print_exc()
