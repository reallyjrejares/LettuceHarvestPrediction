# Deploy to Vercel Serverless - Quick Start

## What's Happening

Your Flask app has been converted to **Vercel serverless functions**:
- `api/predict.py` - handles predictions (60s timeout)
- `api/health.py` - health check endpoint

Model is loaded from `python_api/harvest_model.joblib`

## 🚀 Deployment Steps

### 1. Prepare Your Project
```bash
# Make sure your model is renamed and placed correctly
python_api/harvest_model.joblib  ← your model file
```

### 2. Push to GitHub
```bash
git add .
git commit -m "Convert to Vercel serverless"
git push origin main
```

### 3. Deploy to Vercel

**Option A: Using Vercel Website (Easiest)**
1. Go to https://vercel.com
2. Click "Add New" → "Project"
3. Select your GitHub repo
4. Click "Deploy"
5. Wait 2-3 minutes

**Option B: Using Vercel CLI**
```bash
npm i -g vercel
vercel login
vercel
```

### 4. Get Your URL
After deployment, you'll get a URL like:
```
https://your-project.vercel.app
```

### 5. Update `.env`
Replace `your-project` with your actual Vercel project name:
```env
ML_PREDICT_URL = https://your-project.vercel.app/api/predict
```

### 6. Test

**Health check:**
```bash
curl https://your-project.vercel.app/api/health
```

**Prediction:**
```bash
curl -X POST https://your-project.vercel.app/api/predict \
  -H "Content-Type: application/json" \
  -d '{
    "temperature_c": 22.5,
    "humidity_pct": 65,
    "tds_ppm": 1200,
    "ph_level": 6.5
  }'
```

## ⚠️ Limitations

| Limit | Value |
|-------|-------|
| Execution time | 60 seconds |
| Memory per function | 3008 MB |
| Model file size | ~50 MB unzipped |
| Cold start delay | 2-5 seconds first request |

## ✅ Your Model Still Works

Yes! Your model loads and runs on every request:
- CodeIgniter sends data
- Vercel function loads model
- Returns prediction
- User sees result

**No code changes needed in CodeIgniter!**

## 📁 File Structure
```
lettuce/
├── api/
│   ├── predict.py          ← serverless function
│   └── health.py           ← health check
├── python_api/
│   └── harvest_model.joblib   ← your model
├── vercel.json             ← config
└── .env                    ← updated URL
```

## 🆘 Troubleshooting

**Model file not found:**
- Ensure `harvest_model.joblib` is in `python_api/`
- File must be committed to Git
- Check Vercel build logs

**Timeout errors:**
- If predictions take >60 seconds, they'll fail
- Model file might be too large (>50MB)
- Try upgrading Vercel Pro ($20/month) for longer timeouts

**CORS errors:**
- Vercel functions already have CORS enabled
- Should work with your CodeIgniter app

**Cold start delay (2-5 seconds):**
- Normal for serverless
- Only happens after 30 mins of inactivity
- Upgrade to Pro for faster cold starts

## 💡 Tips

✅ Deploy updates: Just push to GitHub, Vercel auto-deploys

✅ Monitor: Check Vercel Dashboard for logs and errors

✅ Scale: Free tier handles 100+ daily predictions easily

## Next Steps

1. Make sure model file is in place
2. Push to GitHub
3. Connect to Vercel
4. Update `.env` with your URL
5. Test predictions
6. Done! 🎉

---

**Project URL:** https://vercel.com/dashboard
