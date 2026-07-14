# Nexus Platform API - Postman Collection & Documentation

Welcome! This package contains everything you need to explore and test the Nexus Platform APIs using Postman.

## 📦 Files Included

### 1. **Nexus_API_Collection.postman_collection.json**
The complete API collection containing 100+ endpoints organized into 15 categories:
- 🔐 Authentication (login, register, token management)
- 👥 Contacts Hub (contact management, intelligence, messaging)
- 🔔 Notifications Hub (templates, sending, broadcasting)
- 💬 Conversations (multi-channel messaging)
- 🤖 Agents Hub (AI agent management and execution)
- ⚙️ Workflows (automation and orchestration)
- 📋 Tasks (task management and tracking)
- 🧠 Memory Hub (memory storage and retrieval)
- 👻 HedraSoul (AI soul management)
- 🧪 AI Models Hub (provider and model management)
- ⚙️ Settings (application configuration)
- 📊 Monitoring (system health and metrics)
- 📝 Logs (logging and error tracking)
- 👤 Profile (user profile management)
- ⚡ WebHooks (webhook management)

### 2. **Nexus_Environment.postman_environment.json**
Pre-configured environment with variables for:
- Base URL
- API version
- Authentication token
- Resource IDs (contact, agent, workflow, etc.)
- Pagination settings
- Test data

### 3. **POSTMAN_API_DOCUMENTATION.md**
Comprehensive API documentation including:
- Overview and authentication guide
- Complete endpoint listing by category
- Common use cases with examples
- Error handling and status codes
- Rate limiting information
- Pagination details
- Filtering and sorting
- Real-time features
- File upload instructions
- Testing tips

### 4. **POSTMAN_SETUP_GUIDE.md**
Step-by-step installation and usage guide:
- How to import collection and environment
- Configuration instructions
- Common tasks walkthrough
- Environment variables reference
- Tips and tricks
- Batch testing
- Debugging guide
- Troubleshooting FAQ

### 5. **README.md** (this file)
Overview of all documentation files

---

## 🚀 Quick Start

### 1. Install Postman
Download from: https://www.postman.com/downloads/

### 2. Import Files
1. Open Postman
2. Click **Import**
3. Import both JSON files:
   - `Nexus_API_Collection.postman_collection.json`
   - `Nexus_Environment.postman_environment.json`

### 3. Authenticate
1. Select the imported environment from dropdown
2. Run the **Login** request to get token
3. Save token to `bearer_token` variable

### 4. Start Testing
1. Browse collections in left sidebar
2. Click any endpoint
3. Click **Send** to test

---

## 📊 API Overview

**Base URL:** `https://n.soulyeg.online/api/v1`

**Authentication:** Bearer Token (Sanctum)

**Total Endpoints:** 100+

**Organized into 15 Hub Categories**

### Core Features
- ✅ Contact management with AI intelligence
- ✅ Real-time notifications and broadcasting
- ✅ Automated workflows
- ✅ AI-powered agents
- ✅ Task management
- ✅ Memory/knowledge base
- ✅ Multi-channel conversations
- ✅ System monitoring
- ✅ Comprehensive logging
- ✅ Multi-provider AI routing

---

## 🎯 Typical Workflows

### Create and Manage Contacts
```
Login → Create Contact → Get Intelligence → Export
```

### Send Notifications
```
Create Template → Send Notification → Track Delivery
```

### Execute Workflows
```
Create Workflow → Execute for Contact → Track Progress
```

### Run AI Agents
```
Create Agent → Run with Input → View Logs
```

### Manage Conversations
```
Create Conversation → Send Messages → View History
```

---

## 📚 Documentation Structure

```
├── POSTMAN_SETUP_GUIDE.md
│   └── How to install and use
├── POSTMAN_API_DOCUMENTATION.md
│   ├── API Reference
│   ├── Authentication
│   ├── All Endpoints (organized by category)
│   ├── Use Cases
│   ├── Error Handling
│   └── Advanced Features
├── Nexus_API_Collection.postman_collection.json
│   ├── 15 Endpoint Categories
│   ├── 100+ Requests
│   ├── Pre-filled Examples
│   └── Descriptions for Each
└── Nexus_Environment.postman_environment.json
    ├── Variable Definitions
    ├── Base Configuration
    └── Test Data
```

---

## 🔑 Key Endpoints by Category

### Authentication
- `POST /login` - Get authentication token
- `POST /register` - Create account
- `POST /logout` - Logout
- `POST /verify-token` - Verify token

### Contacts
- `GET /contacts` - List all contacts
- `POST /contacts` - Create contact
- `GET /contacts/{id}/intelligence` - AI insights
- `GET /contacts/{id}/memory` - Stored memories
- `POST /contacts/import` - Bulk import

### Notifications
- `POST /notifications/send` - Send notification
- `POST /notifications/broadcast` - Real-time broadcast
- `GET /notifications/templates` - List templates

### Workflows
- `POST /workflows/{id}/execute` - Execute workflow
- `GET /workflows/{id}/progress` - Get progress
- `GET /workflows/templates` - Available templates

### Agents
- `POST /agents/{id}/run` - Execute agent
- `GET /agents/{id}/logs` - View execution logs
- `POST /agents/{id}/quarantine` - Safety control

### AI Models
- `GET /ai/providers` - List providers
- `POST /ai/request` - Route request to AI
- `GET /ai/cost/forecast` - Cost prediction

### Memory
- `POST /memories` - Store memory
- `GET /memories/search` - Search memories
- `GET /contacts/{id}/memories` - Contact memories

### HedraSoul
- `GET /hedrasoul/sessions` - AI sessions
- `POST /hedrasoul/sessions/{id}/messages` - Chat
- `GET /hedrasoul/profile` - AI profile

### Monitoring
- `GET /monitoring/health` - System health
- `GET /monitoring/metrics` - Performance metrics
- `GET /dashboard/stats` - Dashboard data

---

## 📖 Documentation Files Guide

### Start Here: POSTMAN_SETUP_GUIDE.md
- Installation steps
- First-time setup
- Common tasks
- Troubleshooting

### Reference: POSTMAN_API_DOCUMENTATION.md
- Complete endpoint list
- Request/response formats
- Error codes
- Use case examples

### Collections: JSON Files
- Ready-to-use requests
- Example data
- Pre-configured auth

---

## 💡 Tips for Success

1. **Read the Setup Guide First**
   - Follow step-by-step instructions
   - Save time on configuration

2. **Use Environment Variables**
   - Simplifies request URLs
   - Easy to switch between servers
   - Secure token management

3. **Explore Request Examples**
   - Each request has example data
   - Modify for your needs
   - View response examples

4. **Test Incrementally**
   - Start with simple endpoints
   - Build up to complex workflows
   - Use saved requests for repeatability

5. **Check API Documentation**
   - POSTMAN_API_DOCUMENTATION.md has details
   - Understand response formats
   - Know error codes

6. **Monitor with Postman**
   - Use Network tab to debug
   - View full request/response
   - Track timing

---

## 🔍 Finding Specific Endpoints

### Search in Postman
1. Press `Cmd+K` or `Ctrl+K`
2. Type endpoint name
3. Click to open

### By Feature
- **Contacts:** 👥 Contacts Hub folder
- **Messaging:** 💬 Conversations folder
- **Notifications:** 🔔 Notifications Hub folder
- **Automation:** ⚙️ Workflows folder
- **AI:** 🤖 Agents Hub or 🧪 AI Models Hub folder

### By Operation Type
- **List:** `GET /resource`
- **Create:** `POST /resource`
- **Update:** `PUT /resource/{id}`
- **Delete:** `DELETE /resource/{id}`

---

## 🔐 Security Best Practices

1. **Keep Token Secret**
   - Never share `bearer_token`
   - Use environment variable
   - Rotate tokens regularly

2. **Use Separate Environments**
   - Development
   - Staging  
   - Production
   - Each with own credentials

3. **Don't Commit Secrets**
   - Add to `.gitignore`
   - Use environment variables
   - Consider Postman vaults

4. **Validate Requests**
   - Check SSL/TLS
   - Verify headers
   - Validate responses

---

## 📞 Support & Resources

### Within This Package
- **Setup Issues:** See POSTMAN_SETUP_GUIDE.md
- **API Questions:** See POSTMAN_API_DOCUMENTATION.md
- **Request Examples:** Browse JSON collection

### External Resources
- **Postman Docs:** https://learning.postman.com
- **API Base:** https://n.soulyeg.online
- **Collection:** Nexus_API_Collection.postman_collection.json

---

## 📋 Checklist

- [ ] Downloaded and installed Postman
- [ ] Imported API Collection
- [ ] Imported Environment
- [ ] Read POSTMAN_SETUP_GUIDE.md
- [ ] Successfully logged in
- [ ] Set bearer_token in environment
- [ ] Tested Health Check endpoint
- [ ] Created first contact
- [ ] Explored other endpoints
- [ ] Bookmarked POSTMAN_API_DOCUMENTATION.md

---

## 🎓 Learning Path

**Beginner:**
1. Import collection and environment
2. Read Setup Guide
3. Test Health Check
4. Create a contact
5. Get contact details

**Intermediate:**
1. List all contacts
2. Create notification template
3. Send notification
4. View notification logs
5. Create workflow

**Advanced:**
1. Execute AI agent
2. Run workflow
3. Search memories
4. Manage AI providers
5. Monitor system metrics

---

## 📝 Version Information

- **API Version:** v1
- **Collection Version:** 1.0.0
- **Base URL:** https://n.soulyeg.online/api/v1
- **Last Updated:** 2024

---

## 📄 License

These API documentation and collection files are provided as-is for use with the Nexus Platform.

---

## 🚀 Ready to Get Started?

1. **First Time?** → Read `POSTMAN_SETUP_GUIDE.md`
2. **Need Details?** → Check `POSTMAN_API_DOCUMENTATION.md`
3. **Have Questions?** → Look in troubleshooting section
4. **Let's Go!** → Import the collection and start testing

Enjoy exploring the Nexus Platform API! 🎉

---

## Files Summary

| File | Purpose | Action |
|------|---------|--------|
| `Nexus_API_Collection.postman_collection.json` | API endpoints | Import to Postman |
| `Nexus_Environment.postman_environment.json` | Variables & config | Import to Postman |
| `POSTMAN_SETUP_GUIDE.md` | Installation guide | Read first |
| `POSTMAN_API_DOCUMENTATION.md` | API reference | Use as needed |
| `README.md` | This overview | You're reading it! |

---

**Happy Testing!** 🎯
