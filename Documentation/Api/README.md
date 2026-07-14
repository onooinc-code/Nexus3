# 🎯 Swagger API Documentation - Quick Access Guide

## ✅ Setup Complete!

All API documentation has been organized in:
```
📁 Documentation/Api/
```

---

## 🚀 **SWAGGER URL**

### **Main URL (Interactive Testing)**
```
http://localhost:8000/swagger-ui
```

### **Alternative Views**
- ReDoc: `http://localhost:8000/redoc`
- Raw JSON: `http://localhost:8000/openapi.json`

---

## 📋 **5 SIMPLE STEPS TO USE SWAGGER**

### **Step 1: Start the Server**
```bash
cd /www/wwwroot/Nexus/core/Nexus3
php artisan serve
```
✅ Server running on: `http://localhost:8000`

---

### **Step 2: Open Swagger UI**
```
Open in browser: http://localhost:8000/swagger-ui
```
You'll see the beautiful Swagger interface with all API endpoints!

---

### **Step 3: Get Authentication Token**

**Option A: Using Swagger UI**
1. Click the **Authorize** button (top right)
2. Find **POST /auth/login** endpoint
3. Click **"Try it out"**
4. Fill in:
   ```json
   {
     "email": "test@example.com",
     "password": "password"
   }
   ```
5. Click **Execute**
6. Copy the `token` from response

**Option B: Using cURL**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }' | jq '.token'
```

---

### **Step 4: Set Authorization**

In Swagger UI:
1. Click **Authorize** button
2. Paste your token in this format:
   ```
   Bearer YOUR_TOKEN_HERE
   ```
3. Click **Authorize**
4. ✅ All endpoints now have your authorization!

---

### **Step 5: Test Any Endpoint**

**Example: Get All Contacts**
1. Find: **GET /contacts**
2. Click to expand
3. Click **"Try it out"**
4. Click **Execute**
5. See the response! 🎉

---

## 🔑 **Default Credentials**

| Field | Value |
|-------|-------|
| Email | `test@example.com` |
| Password | `password` |

---

## 🧪 **Test Endpoints to Try**

```
✅ GET /health                    (No auth needed)
✅ POST /auth/login              (Get token)
✅ GET /contacts                 (List all contacts)
✅ POST /contacts                (Create contact)
✅ GET /contacts/{id}            (Get one contact)
✅ GET /contacts/{id}/intelligence (Get AI insights)
```

---

## 📂 **Documentation Files**

All files are in: `Documentation/Api/`

| File | Purpose |
|------|---------|
| `openapi.json` | API Specification |
| `swagger-ui.html` | UI Interface |
| `SWAGGER_AND_POSTMAN_GUIDE.md` | **Comprehensive guide** 👈 Read this! |
| `API_ENDPOINTS_ANALYSIS.md` | All 150+ endpoints |
| `SWAGGER_QUICK_START.md` | Quick start |
| `Nexus_API_Collection.postman_collection.json` | Postman collection |
| `POSTMAN_SETUP_GUIDE.md` | Postman guide |

---

## 💡 **Quick Commands**

```bash
# Start server
php artisan serve

# Get token with cURL
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Test endpoint with token
curl -X GET http://localhost:8000/api/v1/contacts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Create contact
curl -X POST http://localhost:8000/api/v1/contacts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com"
  }'
```

---

## 📊 **What You Can Do**

✅ **150+ API Endpoints** documented  
✅ **Interactive Testing** - Try endpoints directly  
✅ **Real-time Responses** - See what data returns  
✅ **Request Examples** - Pre-filled sample data  
✅ **Error Scenarios** - Understand error handling  
✅ **Authentication** - Manage Bearer tokens  
✅ **Documentation** - Full descriptions for all endpoints  

---

## 🔐 **Example API Flow**

```
1. Login
   POST /auth/login
   ├─ Email: test@example.com
   ├─ Password: password
   └─ Get: Token

2. Create Contact
   POST /contacts
   ├─ Authorization: Bearer {token}
   ├─ Name: John Doe
   ├─ Email: john@example.com
   └─ Get: Contact created with ID

3. Get AI Intelligence
   GET /contacts/{id}/intelligence
   ├─ Authorization: Bearer {token}
   └─ Get: AI-powered insights about contact

4. Start Conversation
   POST /hedrasoul/sessions
   ├─ Authorization: Bearer {token}
   ├─ Name: Analysis Session
   └─ Get: Session created
```

---

## 🐛 **Troubleshooting**

| Problem | Solution |
|---------|----------|
| **Cannot access /swagger-ui** | Make sure `php artisan serve` is running |
| **404 error** | Server not running on port 8000 |
| **Bearer token invalid** | Get fresh token from /auth/login |
| **401 Unauthorized** | Token not set in Authorize header |
| **CORS error** | Normal for local development, check browser console |

---

## 🎯 **Next Steps**

1. ✅ Start server: `php artisan serve`
2. ✅ Open: http://localhost:8000/swagger-ui
3. ✅ Get token from /auth/login
4. ✅ Set Authorization with token
5. ✅ Test endpoints!

---

## 📚 **Read More**

For complete details, read: `Documentation/Api/SWAGGER_AND_POSTMAN_GUIDE.md`

This includes:
- Detailed usage examples
- Code examples (Python, JavaScript, cURL)
- Postman integration
- All endpoint documentation
- Troubleshooting guide
- Advanced usage

---

## 🚀 **You're All Set!**

**Main URL**: http://localhost:8000/swagger-ui

Start testing your API now! 🎉
