# 📚 Swagger API Documentation Setup Guide

## Overview

Complete OpenAPI 3.0 Swagger documentation for the Nexus Platform API with full request/response examples, authentication details, and interactive testing capabilities.

---

## 📦 Files Created

### 1. **openapi.json** (Main API Specification)
- **OpenAPI 3.0.0** format
- **150+ endpoints** documented
- Machine-readable specification
- Compatible with: Swagger UI, ReDoc, SDK generators, Postman, Insomnia

### 2. **swagger-ui.html** (Interactive Documentation)
- Beautiful interactive Swagger UI interface
- Live endpoint testing
- Request/response visualization
- Authentication management
- Modern gradient design

---

## 🚀 How to Use

### Option 1: Local Swagger UI (Recommended)

**Setup:**
```bash
cd /www/wwwroot/Nexus/core/Nexus3

# Serve the files with built-in server
php artisan serve
```

**Access:**
```
http://localhost:8000/swagger-ui.html
```

### Option 2: Using an OpenAPI Route

Add this to your `routes/web.php`:
```php
Route::get('/swagger-ui', function () {
    return file_get_contents(base_path('swagger-ui.html'));
});

Route::get('/openapi.json', function () {
    return response()->json(json_decode(file_get_contents(base_path('openapi.json'))));
});
```

### Option 3: Production Deployment

**With Nginx:**
```nginx
server {
    listen 80;
    server_name api.example.com;

    location / {
        proxy_pass http://localhost:8000;
    }

    location /swagger-ui.html {
        alias /var/www/nexus/swagger-ui.html;
    }

    location /openapi.json {
        alias /var/www/nexus/openapi.json;
    }
}
```

**Access:**
```
https://api.example.com/swagger-ui.html
```

---

## 🔑 Authentication Setup

### In Swagger UI:

1. **Click "Authorize" button** (top right)
2. **Enter your Bearer Token:**
   ```
   Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   ```
3. **Click "Authorize"**
4. **Now all requests will include the token**

### Get Your Token:

```bash
# Using cURL
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }' | jq '.token'
```

---

## 📋 Endpoint Categories Documented

### ✅ Authentication (2 endpoints)
- `POST /auth/login` - User authentication
- `POST /auth/logout` - Revoke token

### ✅ Contacts (15+ endpoints)
- `GET /contacts` - List all contacts
- `POST /contacts` - Create contact
- `GET /contacts/{id}` - Get contact details
- `PUT /contacts/{id}` - Update contact
- `DELETE /contacts/{id}` - Delete contact
- `GET /contacts/{id}/intelligence` - AI insights
- `POST /contacts/import` - Bulk import

### ✅ AI Models (8+ endpoints)
- `POST /ai/request` - Intelligent AI routing
- `GET /ai/providers` - Available providers
- `GET /ai/models` - Model management
- `GET /ai/usage` - Cost analytics

### ✅ HedraSoul (12+ endpoints)
- `GET /hedrasoul/sessions` - List sessions
- `POST /hedrasoul/sessions` - Create session
- `GET /hedrasoul/sessions/{id}/messages` - Get messages
- `POST /hedrasoul/sessions/{id}/messages` - Send message
- `POST /hedrasoul/sessions/{id}/autonomy` - Enable autonomy
- `GET /hedrasoul/memory/search` - Search memory

### ✅ Tasks (8+ endpoints)
- `POST /tasks/{id}/execute` - Execute task
- `GET /tasks/{id}/logs` - View logs
- `GET /tasks/stats` - Task statistics

### ✅ Workflows (6+ endpoints)
- `GET /workflows` - List workflows
- `POST /workflows` - Create workflow
- `POST /workflows/{id}/execute` - Execute

### ✅ Health & System (3+ endpoints)
- `GET /health` - Health check
- `GET /settings` - System settings
- `GET /dashboard/stats` - Dashboard statistics

---

## 💡 Common Use Cases with Examples

### 1. Authenticate and List Contacts

```bash
# Step 1: Get token
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }' | jq -r '.token')

# Step 2: List contacts
curl -X GET "http://localhost:8000/api/v1/contacts?page=1&per_page=10" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json"
```

### 2. Create a Contact and Get AI Intelligence

```bash
# Create contact
CONTACT=$(curl -s -X POST http://localhost:8000/api/v1/contacts \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "company": "Acme Corp"
  }')

CONTACT_ID=$(echo $CONTACT | jq -r '.id')

# Get AI insights
curl -X GET "http://localhost:8000/api/v1/contacts/$CONTACT_ID/intelligence" \
  -H "Authorization: Bearer $TOKEN"
```

### 3. Start AI Conversation (HedraSoul)

```bash
# Create session
SESSION=$(curl -s -X POST http://localhost:8000/api/v1/hedrasoul/sessions \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Customer Analysis",
    "ai_persona": "expert_analyst"
  }')

SESSION_ID=$(echo $SESSION | jq -r '.id')

# Send message and get AI response
curl -X POST "http://localhost:8000/api/v1/hedrasoul/sessions/$SESSION_ID/messages" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Analyze the top sales opportunities in our pipeline",
    "role": "user"
  }'
```

### 4. Intelligent AI Request

```bash
# Auto-select best AI provider based on your needs
curl -X POST http://localhost:8000/api/v1/ai/request \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Generate a personalized email for a VIP customer",
    "provider": "auto",
    "context": {
      "customer_type": "vip",
      "industry": "technology"
    },
    "temperature": 0.7,
    "max_tokens": 500
  }'
```

### 5. Import Contacts

```bash
curl -X POST http://localhost:8000/api/v1/contacts/import \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@contacts.csv" \
  -F "map={\"0\":\"name\",\"1\":\"email\",\"2\":\"phone\"}"
```

---

## 🔐 Response Examples

### Successful Contact Creation
```json
{
  "id": 123,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "company": "Acme Corp",
  "metadata": {},
  "created_at": "2024-07-12T10:30:00Z",
  "updated_at": "2024-07-12T10:30:00Z"
}
```

### Contact Intelligence Response
```json
{
  "summary": "High-value B2B prospect with consistent engagement patterns",
  "insights": [
    "Has visited pricing page 5 times in past 30 days",
    "Engaged with 3 different product demos",
    "Strong interest in enterprise features",
    "Decision maker role indicated by email domain"
  ],
  "sentiment": "positive",
  "recommendations": [
    "Schedule a product walkthrough",
    "Prepare enterprise pricing proposal",
    "Highlight compliance certifications",
    "Follow up with technical documentation"
  ]
}
```

### AI Request Response
```json
{
  "response": "Subject: Exclusive Opportunity for [Customer Name]...",
  "provider": "openai",
  "model": "gpt-4",
  "usage": {
    "prompt_tokens": 256,
    "completion_tokens": 512
  }
}
```

---

## 🛠️ Integration Examples

### Python SDK Usage

```python
import requests

API_URL = "http://localhost:8000/api/v1"
TOKEN = "your_bearer_token"

headers = {
    "Authorization": f"Bearer {TOKEN}",
    "Content-Type": "application/json"
}

# Get contacts
response = requests.get(f"{API_URL}/contacts", headers=headers)
contacts = response.json()

# Create contact
new_contact = {
    "name": "Jane Smith",
    "email": "jane@example.com"
}
response = requests.post(f"{API_URL}/contacts", headers=headers, json=new_contact)
```

### JavaScript/Node.js Usage

```javascript
const API_URL = "http://localhost:8000/api/v1";
const TOKEN = "your_bearer_token";

const headers = {
    "Authorization": `Bearer ${TOKEN}`,
    "Content-Type": "application/json"
};

// Get contacts
fetch(`${API_URL}/contacts`, { headers })
    .then(r => r.json())
    .then(data => console.log(data));

// Create contact
fetch(`${API_URL}/contacts`, {
    method: "POST",
    headers,
    body: JSON.stringify({
        name: "Jane Smith",
        email: "jane@example.com"
    })
})
    .then(r => r.json())
    .then(data => console.log(data));
```

### Postman Collection Import

1. Open Postman
2. Click "Import"
3. Enter: `https://n.soulyeg.online/openapi.json`
4. Postman will auto-generate a collection

---

## 📊 API Metrics

| Metric | Value |
|--------|-------|
| Total Endpoints | 150+ |
| Authenticated Endpoints | 138 (92%) |
| Public Endpoints | 12 (8%) |
| HTTP Methods | GET, POST, PUT, PATCH, DELETE |
| Response Format | JSON |
| Authentication | Bearer Token (JWT) |
| API Version | v1 |

---

## ✅ Testing Endpoints

### Health Check
```bash
curl http://localhost:8000/api/v1/health
```
**No authentication required**

### Public Endpoints
- `GET /health` - API health
- `POST /auth/login` - User login
- `GET /webhooks/{token}` - Webhook receiver

### Rate Limiting
- **Default**: 60 requests/minute per IP
- **Imports**: 5 requests/minute
- **Analysis**: Custom throttle
- **Dashboard**: 60 requests/minute

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| **Token expired** | Get new token with `/auth/login` |
| **401 Unauthorized** | Ensure Bearer token is in Authorization header |
| **422 Validation Error** | Check required fields in request body |
| **404 Not Found** | Verify resource ID exists |
| **429 Too Many Requests** | Wait before retrying; you've hit rate limit |
| **500 Server Error** | Check logs: `php artisan pail` |

---

## 📖 Documentation References

- **OpenAPI Spec**: `/openapi.json`
- **Swagger UI**: `/swagger-ui.html`
- **API Endpoints**: `/API_ENDPOINTS_ANALYSIS.md`
- **Quick Reference**: `/API_QUICK_REFERENCE.md`
- **Postman Collection**: `/Nexus_API_Collection.postman_collection.json`
- **Environment Variables**: `/Nexus_Environment.postman_environment.json`

---

## 🚀 Deployment Checklist

Before deploying to production:

- [ ] Update `APP_URL` in `.env`
- [ ] Change all default passwords
- [ ] Enable HTTPS only
- [ ] Configure CORS properly
- [ ] Set up API rate limiting
- [ ] Enable request logging
- [ ] Configure error monitoring
- [ ] Set up API analytics
- [ ] Document custom endpoints
- [ ] Test all integrations

---

## 📝 Next Steps

1. **Import Swagger into your tools**: Postman, Insomnia, VS Code REST Client
2. **Generate SDKs**: Use OpenAPI Generator for your language
3. **Explore endpoints**: Test in Swagger UI
4. **Build integrations**: Use cURL/SDKs for your app
5. **Monitor usage**: Track API metrics and performance

---

**Version**: 1.0.0  
**Last Updated**: 2024-07-12  
**Maintained By**: Nexus Platform Team
