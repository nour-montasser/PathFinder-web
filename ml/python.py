import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import classification_report, confusion_matrix, roc_auc_score
from imblearn.over_sampling import SMOTE
from imblearn.pipeline import make_pipeline
import joblib
import warnings
from collections import defaultdict

# Suppress warnings
warnings.filterwarnings('ignore')

# 1. Load and Prepare Data
df = pd.read_csv('ml/processed_data.csv')

# Feature Engineering
def create_features(df):
    df = df.copy()
    # Interaction features
    df['skill_title_interaction'] = df['skills_match'] * df['title_similarity']
    # Penalty for too many applications
    df['application_penalty'] = np.log1p(df['previous_applications'])
    return df

# Prepare features - using your existing engineered features
X = create_features(df[['skills_match', 'title_similarity', 'previous_applications']])
y = df['y']  # This is our target: 1=will apply, 0=won't apply

# 2. Train-Test Split (stratified by y)
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, stratify=y, random_state=42
)

# 3. Recommendation-Optimized Model Pipeline
model = make_pipeline(
    SMOTE(sampling_strategy=0.5, k_neighbors=3, random_state=42),
    RandomForestClassifier(
        class_weight='balanced_subsample',
        n_estimators=200,
        max_depth=6,
        min_samples_leaf=5,
        random_state=42
    )
)

# 4. Training and Evaluation
model.fit(X_train, y_train)
y_pred = model.predict(X_test)
y_proba = model.predict_proba(X_test)[:, 1]  # Probability of applying

print("\n=== Model Performance ===")
print(classification_report(y_test, y_pred, target_names=['Wont Apply', 'Will Apply']))
print("\nConfusion Matrix:")
print(confusion_matrix(y_test, y_pred))
print(f"\nAUC-ROC: {roc_auc_score(y_test, y_proba):.2f}")

# 5. Save Model
joblib.dump(model, 'ml/recommendation_model.pkl')

# 6. Recommendation Engine
def generate_recommendations(user_id, job_pool, top_n=5):
    """
    Generate top job recommendations for a user
    Args:
        user_id: ID of the user to recommend for
        job_pool: DataFrame of available jobs
        top_n: Number of recommendations to return
    Returns:
        DataFrame of recommended jobs with match scores
    """
    # Get user's features (in a real system, this would come from a DB)
    user_data = df[df['user_id'] == user_id].iloc[0]
    
    recommendations = []
    for _, job in job_pool.iterrows():
        # Create features for this user-job pair
        features = pd.DataFrame({
            'skills_match': [job['skills_match']],
            'title_similarity': [job['title_similarity']],
            'previous_applications': [user_data['previous_applications']]
        })
        features = create_features(features)
        
        # Predict application probability
        proba = model.predict_proba(features)[0][1]
        
        recommendations.append({
            'job_offer_id': job['job_offer_id'],
            'job_title': job['job_offer_title'],
            'match_score': proba,
            'skills_match': job['skills_match'],
            'title_similarity': job['title_similarity']
        })
    
    # Return top N recommendations
    return pd.DataFrame(recommendations).sort_values('match_score', ascending=False).head(top_n)

# Example Usage
if __name__ == "__main__":
    # Load sample job pool (in practice, this would be your current job listings)
    job_pool = df[['job_offer_id', 'job_offer_title', 'skills_match', 'title_similarity']].drop_duplicates()
    
    # Generate recommendations for user 25
    user_id = 25
    recs = generate_recommendations(user_id, job_pool)
    
    print(f"\nTop 5 Job Recommendations for User {user_id}:")
    print(recs[['job_offer_id', 'job_title', 'match_score']])