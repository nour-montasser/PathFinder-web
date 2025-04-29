import sys
import joblib
import numpy as np
import pandas as pd
from pathlib import Path

def create_features(input_data):
    """Feature engineering matching the training pipeline"""
    df = pd.DataFrame(
        input_data,
        columns=['skills_match', 'title_similarity', 'previous_applications']
    )
    # Same features used in training
    df['skill_title_interaction'] = df['skills_match'] * df['title_similarity']
    df['application_penalty'] = np.log1p(df['previous_applications'])
    return df

def load_model(model_path):
    """Safe model loading with validation"""
    if not Path(model_path).exists():
        raise FileNotFoundError(f"Model file not found at {model_path}")
    model = joblib.load(model_path)
    # Verify expected features
    expected_features = ['skills_match', 'title_similarity', 'previous_applications',
                       'skill_title_interaction', 'application_penalty']
    if hasattr(model, 'feature_names_in_'):
        assert all(f in model.feature_names_in_ for f in expected_features), \
               "Model expects different features"
    return model

def validate_input(skills, title, apps):
    """Validate input ranges"""
    if not (0 <= skills <= 1):
        raise ValueError("skills_match must be between 0 and 1")
    if not (0 <= title <= 1):
        raise ValueError("title_similarity must be between 0 and 1")
    if apps < 0:
        raise ValueError("previous_applications must be >= 0")
    return [skills, title, apps]

def predict_application_probability(model, features):
    """Predict probability user will apply (y=1)"""
    try:
        proba = model.predict_proba(features)[0]
        # Ensure we return probability for class 1 (will apply)
        if model.classes_[0] == 1:  # Handle class order variation
            return round(proba[0], 4)
        else:
            return round(proba[1], 4)
    except Exception as e:
        sys.stderr.write(f"Prediction error: {str(e)}\n")
        return 0.0  # Default to 0 probability on error

def main():
    try:
        # 1. Load the trained recommendation model
        model = load_model('ml/recommendation_model.pkl')  # Changed model name
        
        # 2. Validate and prepare input
        raw_input = validate_input(
            float(sys.argv[1]),  # skills_match (0-1)
            float(sys.argv[2]),  # title_similarity (0-1)
            int(sys.argv[3])     # previous_applications (>=0)
        )
        features = create_features([raw_input])
        
        # 3. Get application probability
        proba = predict_application_probability(model, features)
        
        # Output: probability only (since we're recommending, not classifying)
        print(f"{proba:.4f}")
        
    except Exception as e:
        sys.stderr.write(f"ERROR: {str(e)}\n")
        print("0.0000")  # Fail-safe output
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) != 4:
        sys.stderr.write("Usage: python predict_script.py <skills_match> <title_similarity> <previous_applications>\n")
        sys.exit(1)
    main()