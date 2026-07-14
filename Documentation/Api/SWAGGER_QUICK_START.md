# 🎯 Swagger Setup - Quick Access Guide

## 🚀 Start Here (3 Steps)

### Step 1: Start Laravel Server
```bash
cd /www/wwwroot/Nexus/core/Nexus3
php artisan serve
```

### Step 2: Open in Browser
```
http://localhost:8000/swagger-ui
```

### Step 3: Test Login
```
POST /auth/login
{
  "email": "test@example.com",
  "password": "password"
}
```

---

## 📚 All Documentation URLs

| Purpose | URL |
|---------|-----|
| **Interactive API Testing** | http://localhost:8000/swagger-ui |
| **Alternative View (ReDoc)** | http://localhost:8000/redoc |
| **API Specification (JSON)** | http://localhost:8000/openapi.json |
| **Health Check** | http://localhost:8000/api/v1/health |

---

## 🔑 Test Credentials

| Field | Value |
|-------|-------|
| Email | `test@example.com` |
| Password | `password` |
| Role | Admin/Super Admin |

---

## 📂 Files Created

```
✅ /www/wwwroot/Nexus/core/Nexus3/
├── openapi.json                          (OpenAPI 3.0 specification)
├── swagger-ui.html                       (Interactive UI)
├── SWAGGER_DOCUMENTATION.md              (Full setup guide)
├── SWAGGER_SETUP_COMPLETE.md             (Summary guide)
├── API_ENDPOINTS_ANALYSIS.md             (150+ endpoints documented)
├── API_QUICK_REFERENCE.md                (Developer cheat sheet)
└── app/Http/Controllers/
    └── SwaggerController.php             (Laravel integration)
```

---

## 🧪 Quick Test Commands

### Health Check (No Auth Required)
```bash
curl http://localhost:8000/api/v1/health
```

### Get Token
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }' | jq '.token'
```

### List Contacts (Requires Token)
```bash
TOKEN="your_token_here"
curl http://localhost:8000/api/v1/contacts \
  -H "Authorization: Bearer $TOKEN"
```

### Create Contact
```bash
curl -X POST http://localhost:8000/api/v1/contacts \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com"
  }'
```

---

## 🌐 Export Options

### Postman
- Open Postman → Import → Link
- Paste: `http://localhost:8000/openapi.json`

### Generate Python SDK
```bash
openapi-generator-cli generate \
  -i http://localhost:8000/openapi.json \
  -g python -o ./nexus-sdk
```

### Generate TypeScript SDK
```bash
openapi-generator-cli generate \
  -i http://localhost:8000/openapi.json \
  -g typescript-axios -o ./nexus-sdk
```

---

## 🐛 Troubleshooting

### Issue: Cannot reach /swagger-ui
**Fix**: 
```bash
php artisan serve
# Then visit http://localhost:8000/swagger-ui
```

### Issue: Bearer token says invalid
**Fix**:
```bash
# Get a new token
curl -X POST http://localhost:8000/api/v1/auth/login \
  -d '{"email":"test@example.com","password":"password"}'
```

### Issue: CORS error in browser
**Fix**: Check `config/cors.php` and verify it allows your origin

### Issue: openapi.json returns 404
**Fix**: Verify file exists:
```bash
ls -la /www/wwwroot/Nexus/core/Nexus3/openapi.json
```

---

## 📊 API Summary

| Category | Count | Examples |
|----------|-------|----------|
| Contacts | 15+ | List, Create, Intelligence, Import |
| AI Models | 8+ | Request, Providers, Routing |
| HedraSoul | 12+ | Sessions, Messages, Autonomy |
| Workflows | 6+ | CRUD, Execute |
| Tasks | 8+ | Execute, Logs, Stats |
| Memory | 6+ | Search, Extract, Version |
| System | 10+ | Health, Settings, Dashboard |
| **TOTAL** | **150+** | Full REST API |

---

## 🎓 Learning Path

### Beginner
1. Health check endpoint
2. Login & get token
3. List contacts
4. View contact details

### Intermediate
5. Create a contact
6. Update a contact
7. Get contact intelligence
8. Start AI conversation

### Advanced
9. Execute workflow
10. Run task with parameters
11. Import bulk contacts
12. Configure AI routing

---

## 🔗 Related Documentation

- **Laravel Best Practices**: laravel-best-practices skill
- **API Endpoints**: API_ENDPOINTS_ANALYSIS.md
- **Quick Reference**: API_QUICK_REFERENCE.md
- **Postman Setup**: POSTMAN_SETUP_GUIDE.md

---

## ✨ Features Included

✅ **OpenAPI 3.0** specification  
✅ **Swagger UI** interactive testing  
✅ **ReDoc** alternative documentation  
✅ **150+ endpoints** documented  
✅ **Bearer authentication** configured  
✅ **Response schemas** with examples  
✅ **Error handling** documented  
✅ **Rate limiting** info included  
✅ **Real-world examples** provided  
✅ **Laravel integration** complete  

---

## 🚀 Ready to Deploy?

### Development
```bash
php artisan serve
```
Access: `http://localhost:8000/swagger-ui`

### Production (with Nginx)
See SWAGGER_DOCUMENTATION.md for full Nginx config

### Docker
See SWAGGER_DOCUMENTATION.md for Docker setup

---

## 📞 Quick Links

- **OpenAPI Spec**: [openapi.json](/openapi.json)
- **Swagger UI**: [/swagger-ui](/swagger-ui)
- **Full Docs**: SWAGGER_DOCUMENTATION.md
- **API Reference**: API_ENDPOINTS_ANALYSIS.md
- **Endpoints List**: API_QUICK_REFERENCE.md

---

**Everything is ready!** 🎉  
Start with: http://localhost:8000/swagger-ui
