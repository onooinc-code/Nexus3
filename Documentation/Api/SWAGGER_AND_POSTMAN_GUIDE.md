# 📚 API Documentation Guide - Swagger & Postman

## 🎯 Complete Setup Confirmation

✅ All documentation files have been organized and moved to:
```
/www/wwwroot/Nexus/core/Nexus3/Documentation/Api/
```

---

## 🚀 Access Points

### **Swagger UI** (Interactive API Documentation)
```
http://localhost:8000/swagger-ui
```

### **Alternative: ReDoc** (Alternative Documentation View)
```
http://localhost:8000/redoc
```

### **API Specification** (Raw JSON)
```
http://localhost:8000/openapi.json
```

---

## 📂 Documentation Files Location

All documentation is now organized in: `Documentation/Api/`

```
Documentation/Api/
├── 📄 openapi.json                         (API Specification)
├── 📄 swagger-ui.html                      (Swagger Interface)
│
├─ 📖 SWAGGER Documentation
│  ├── SWAGGER_QUICK_START.md              (Quick 3-step guide)
│  ├── SWAGGER_DOCUMENTATION.md            (Complete setup guide)
│  ├── SWAGGER_SETUP_COMPLETE.md           (Full overview)
│  ├── SWAGGER_BUILD_SUMMARY.md            (Build summary)
│  └── SWAGGER_FILE_REFERENCE.md           (File reference)
│
├─ 🔗 API Reference
│  ├── API_ENDPOINTS_ANALYSIS.md           (All 150+ endpoints)
│  └── API_QUICK_REFERENCE.md              (Quick lookup)
│
└─ 📮 POSTMAN Documentation
   ├── Nexus_API_Collection.postman_collection.json
   ├── Nexus_Environment.postman_environment.json
   ├── POSTMAN_SETUP_GUIDE.md
   └── POSTMAN_API_DOCUMENTATION.md
```

---

## ⚡ Quick Start (3 Steps)

### Step 1: Start the Server
```bash
cd /www/wwwroot/Nexus/core/Nexus3
php artisan serve
```

### Step 2: Open Swagger UI
```
http://localhost:8000/swagger-ui
```

### Step 3: Get Authentication Token

**In Swagger UI:**
1. Click the **"Authorize"** button (top right)
2. Test **POST /auth/login** endpoint
3. Use credentials:
   - Email: `test@example.com`
   - Password: `password`
4. Copy the token from response
5. In Authorization dialog, paste: `Bearer YOUR_TOKEN_HERE`
6. Click **"Authorize"**

---

## 🧪 How to Use Swagger UI

### **Testing an Endpoint**

1. **Find the endpoint** in the list
2. **Click to expand** it
3. **Click "Try it out"**
4. **Fill in parameters** if needed
5. **Click "Execute"**
6. **View the response** below

### **Example: Get All Contacts**

```
1. Find: GET /contacts
2. Click to expand
3. Click "Try it out"
4. Leave parameters as default
5. Click "Execute"
6. Response shows contact list
```

### **Example: Create a Contact**

```
1. Find: POST /contacts
2. Click to expand
3. Click "Try it out"
4. Modify request body:
   {
     "name": "John Doe",
     "email": "john@example.com",
     "phone": "+1234567890"
   }
5. Click "Execute"
6. Response shows created contact
```

---

## 🔐 Authentication Flow

### **Getting Your Token**

**Request:**
```bash
POST http://localhost:8000/api/v1/auth/login

{
  "email": "test@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com"
  }
}
```

### **Using the Token**

Add to every request header:
```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### **In Swagger UI**

1. Click **"Authorize"** button
2. Paste token in format: `Bearer TOKEN_HERE`
3. Click **"Authorize"**
4. All requests will now include your token

---

## 📊 Available Endpoints

### **150+ Endpoints Documented**

| Category | Endpoints | Examples |
|----------|-----------|----------|
| **Authentication** | 2 | Login, Logout |
| **Contacts** | 15+ | CRUD, Import, Intelligence |
| **AI Models** | 8+ | Request, Providers, Routing |
| **HedraSoul** | 12+ | Sessions, Messages, Autonomy |
| **Workflows** | 6+ | CRUD, Execute |
| **Tasks** | 8+ | Execute, Logs, Stats |
| **Memory** | 6+ | Search, Extract |
| **System** | 10+ | Health, Settings, Dashboard |

---

## 💻 Integration Examples

### **Using cURL**

```bash
# Get token
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}' \
  | jq -r '.token')

# List contacts
curl -X GET http://localhost:8000/api/v1/contacts \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json"

# Create contact
curl -X POST http://localhost:8000/api/v1/contacts \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com"
  }'
```

### **Using Python**

```python
import requests

API_URL = "http://localhost:8000/api/v1"

# Get token
login = requests.post(f"{API_URL}/auth/login", json={
    'email': 'test@example.com',
    'password': 'password'
})
token = login.json()['token']

# Use token
headers = {'Authorization': f'Bearer {token}'}

# Get contacts
contacts = requests.get(f"{API_URL}/contacts", headers=headers)
print(contacts.json())

# Create contact
new_contact = {
    'name': 'Jane Smith',
    'email': 'jane@example.com'
}
response = requests.post(f"{API_URL}/contacts", headers=headers, json=new_contact)
print(response.json())
```

### **Using JavaScript/Node.js**

```javascript
const API_URL = "http://localhost:8000/api/v1";

// Get token
const loginRes = await fetch(`${API_URL}/auth/login`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        email: 'test@example.com',
        password: 'password'
    })
});
const { token } = await loginRes.json();

// Use token
const headers = {'Authorization': `Bearer ${token}`};

// Get contacts
const res = await fetch(`${API_URL}/contacts`, { headers });
const contacts = await res.json();
console.log(contacts);

// Create contact
const createRes = await fetch(`${API_URL}/contacts`, {
    method: 'POST',
    headers: { ...headers, 'Content-Type': 'application/json' },
    body: JSON.stringify({
        name: 'Jane Smith',
        email: 'jane@example.com'
    })
});
const newContact = await createRes.json();
console.log(newContact);
```

---

## 📮 Postman Integration

### **Import Postman Collection**

1. **Open Postman**
2. **Click "Import"**
3. **Choose "Link"**
4. **Paste**: `http://localhost:8000/openapi.json`
5. **Click "Import"**
6. **Collection auto-generates**

### **Alternative: Manual Import**

1. **Download files** from `Documentation/Api/`:
   - `Nexus_API_Collection.postman_collection.json`
   - `Nexus_Environment.postman_environment.json`

2. **In Postman**:
   - Import the collection file
   - Import the environment file
   - Select environment from dropdown

---

## 🎯 Key Endpoints to Test

### **1. Health Check** (No Auth Required)
```
GET /health
```

### **2. Login**
```
POST /auth/login
Body: {
  "email": "test@example.com",
  "password": "password"
}
```

### **3. List Contacts**
```
GET /contacts?page=1&per_page=10
Header: Authorization: Bearer {token}
```

### **4. Create Contact**
```
POST /contacts
Header: Authorization: Bearer {token}
Body: {
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890"
}
```

### **5. Get Contact Intelligence**
```
GET /contacts/{id}/intelligence
Header: Authorization: Bearer {token}
```

### **6. Start AI Conversation**
```
POST /hedrasoul/sessions
Header: Authorization: Bearer {token}
Body: {
  "name": "Customer Analysis",
  "ai_persona": "expert_analyst"
}
```

---

## 📊 Response Examples

### **Successful Contact Creation**
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

### **Contact List Response**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Contact 1",
      "email": "contact1@example.com",
      ...
    },
    {
      "id": 2,
      "name": "Contact 2",
      "email": "contact2@example.com",
      ...
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 250,
    "last_page": 17
  }
}
```

### **Error Response**
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required"]
  }
}
```

---

## 🔧 Troubleshooting

| Issue | Solution |
|-------|----------|
| **Cannot access /swagger-ui** | Ensure `php artisan serve` is running on port 8000 |
| **openapi.json returns 404** | Verify file exists at `Documentation/Api/openapi.json` |
| **Bearer token not working** | Get fresh token from `/auth/login` endpoint |
| **Endpoints return 401** | Ensure Bearer token is correctly formatted in header |
| **CORS errors** | Check `config/cors.php` configuration |
| **Timeout on requests** | Check if Redis and database are running |

---

## 🎓 Learning Path

### **Beginner (Day 1)**
- [ ] Access Swagger UI
- [ ] Get authentication token
- [ ] Test health endpoint
- [ ] List contacts
- [ ] View contact details

### **Intermediate (Day 2-3)**
- [ ] Create a contact
- [ ] Update a contact
- [ ] Get contact intelligence
- [ ] Delete a contact
- [ ] Understand pagination

### **Advanced (Week 2)**
- [ ] Start HedraSoul session
- [ ] Send AI messages
- [ ] Execute workflows
- [ ] Run tasks with parameters
- [ ] Import bulk contacts

---

## 📚 Documentation Files to Read

| File | Read Time | Purpose |
|------|-----------|---------|
| `SWAGGER_QUICK_START.md` | 5 min | Quick 3-step start |
| `SWAGGER_DOCUMENTATION.md` | 20 min | Complete setup guide |
| `API_ENDPOINTS_ANALYSIS.md` | 30 min | Endpoint reference |
| `API_QUICK_REFERENCE.md` | 10 min | Quick lookup |
| `POSTMAN_SETUP_GUIDE.md` | 15 min | Postman integration |

---

## 🔗 Quick Links

### URLs
- **Swagger UI**: http://localhost:8000/swagger-ui
- **ReDoc**: http://localhost:8000/redoc
- **API Spec**: http://localhost:8000/openapi.json
- **Health**: http://localhost:8000/api/v1/health

### Files
- **Location**: `/www/wwwroot/Nexus/core/Nexus3/Documentation/Api/`
- **Open in VS Code**: `code Documentation/Api/`

---

## ✅ Verification Checklist

- [ ] Server running: `php artisan serve`
- [ ] Access Swagger: http://localhost:8000/swagger-ui
- [ ] Can see API endpoints
- [ ] Can click "Authorize"
- [ ] Can test /health endpoint
- [ ] Can login and get token
- [ ] Can set token in Authorization
- [ ] Can test other endpoints

---

## 🚀 Next Steps

1. **Start Server**: `php artisan serve`
2. **Open Swagger**: http://localhost:8000/swagger-ui
3. **Get Token**: Login with test@example.com / password
4. **Test Endpoints**: Try various API calls
5. **Read Documentation**: Review endpoint details
6. **Integrate**: Build your application

---

**Documentation Version**: 1.0.0  
**Framework**: Laravel 13 + OpenAPI 3.0  
**Last Updated**: 2024-07-12  
**Status**: ✅ Complete & Ready
