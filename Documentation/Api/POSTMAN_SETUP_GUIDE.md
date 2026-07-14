# Postman Setup Guide

## Overview

This guide walks you through importing and using the Nexus Platform API Collection in Postman.

**Files Provided:**
1. `Nexus_API_Collection.postman_collection.json` - Complete API collection with 100+ endpoints
2. `Nexus_Environment.postman_environment.json` - Pre-configured environment variables
3. `POSTMAN_API_DOCUMENTATION.md` - Detailed API documentation

## Installation Steps

### Step 1: Download Postman

If you don't have Postman installed:
- Download from: https://www.postman.com/downloads/
- Install and launch the application

### Step 2: Import Collection

1. Open Postman
2. Click the **Import** button (top left)
3. Select **File** tab
4. Choose `Nexus_API_Collection.postman_collection.json`
5. Click **Import**

You should now see "Nexus Platform API Collection" in your Collections sidebar.

### Step 3: Import Environment

1. Click the gear icon (⚙️) in the top right
2. Select **Environments**
3. Click **Import**
4. Choose `Nexus_Environment.postman_environment.json`
5. Click **Import**

### Step 4: Activate Environment

1. In the top right, find the environment dropdown (currently says "No Environment")
2. Click it and select **Nexus Platform - Local Environment**

## Configuration

### Set Your Authentication Token

Before making requests, you need to authenticate:

1. In the collection, open the **🔐 Authentication** folder
2. Click on **Login** request
3. Update the body with your credentials:
   ```json
   {
     "email": "your-email@example.com",
     "password": "your-password"
   }
   ```
4. Click **Send**
5. Copy the `token` value from the response
6. Go to the **Environments** and set `bearer_token` to your token

📧 البريد: admin@nexus.local
🔑 الكلمة المرور: password123

📧 البريد: demo@nexus.local
🔑 الكلمة المرور: password123

📧 البريد: test@nexus.local
🔑 الكلمة المرور: password123

Alternatively, paste your existing token directly into the environment variable.

### Update Base URL (if needed)

If your API is hosted at a different URL:

1. Click the environment dropdown
2. Click the eye icon next to it to see variables
3. Update `base_url` to your server URL (e.g., `http://localhost:8000`)
4. Click **Save**

## Using the Collection

### Browse Endpoints

1. Expand the collection in the left sidebar
2. Click on any folder to see available endpoints
3. Click on an endpoint to view the request

### Customize Requests

Each request has:
- **Method** - GET, POST, PUT, DELETE, etc.
- **URL** - Built from variables ({{base_url}}/api/{{api_version}}/...)
- **Headers** - Including Authorization with bearer token
- **Body** - Pre-filled with example data (for POST/PUT requests)
- **Params** - Query parameters

Modify as needed for your use case.

### Send Requests

1. Click on any request in the collection
2. Edit the parameters if needed
3. Click the **Send** button
4. View the response in the pane below

### Test Results

Responses show:
- **Status** - HTTP status code (200, 201, 400, etc.)
- **Body** - JSON response data
- **Headers** - Response headers
- **Size** - Response size
- **Time** - Response time in milliseconds

## Common Tasks

### Task 1: Create a Contact

1. Open **👥 Contacts Hub** folder
2. Click **Create Contact**
3. In the **Body** tab, modify the JSON:
   ```json
   {
     "name": "Your Name",
     "email": "your@email.com",
     "phone": "+1234567890",
     "tags": ["vip"]
   }
   ```
4. Click **Send**
5. Copy the `id` from the response

### Task 2: Send a Notification

1. First, create a notification template:
   - Open **🔔 Notifications Hub** > **Create Notification Template**
   - Fill in the template details
   - Send and copy the template ID

2. Then send a notification:
   - Open **🔔 Notifications Hub** > **Send Notification**
   - Set `template_id` to the ID you copied
   - Set `recipient_id` to a contact ID
   - Click **Send**

### Task 3: Execute a Workflow

1. Open **⚙️ Workflows** > **Create Workflow**
2. Define workflow steps and send
3. Copy the workflow ID
4. Open **⚙️ Workflows** > **Execute Workflow**
5. Set `workflow_id` to the ID you copied
6. Set `contact_id` to the contact to run on
7. Click **Send**
8. Monitor progress with **Get Workflow Progress**

### Task 4: Run an AI Agent

1. Open **🤖 Agents Hub** > **Get All Agents**
2. Choose an agent and copy its ID
3. Open **🤖 Agents Hub** > **Run Agent**
4. Update the body with your agent ID and input
5. Click **Send**
6. View results in response

### Task 5: Search Memories

1. Open **🧠 Memory Hub** > **Search Memories**
2. Update the `query` parameter:
   ```
   ?query=customer+preferences
   ```
3. Click **Send**
4. View matching memories in response

## Environment Variables

Pre-configured variables you can use:

| Variable | Purpose | Example |
|----------|---------|---------|
| `base_url` | API server URL | https://n.soulyeg.online |
| `bearer_token` | Authentication token | (Your token here) |
| `contact_id` | Example contact ID | 1 |
| `agent_id` | Example agent ID | 1 |
| `workflow_id` | Example workflow ID | 1 |
| `task_id` | Example task ID | 1 |
| `page` | Pagination page | 1 |
| `per_page` | Results per page | 20 |

Use in requests with double curly braces: `{{variable_name}}`

## Tips & Tricks

### 1. Save Custom Environments

Create separate environments for:
- Local development
- Staging
- Production

Each with its own `base_url` and credentials.

### 2. Use Pre-request Scripts

Automatically set values before requests:

```javascript
// Auto-set timestamp
pm.environment.set("timestamp", new Date().toISOString());

// Generate random contact name
const names = ["Alice", "Bob", "Charlie"];
pm.environment.set("test_contact_name", names[Math.floor(Math.random() * names.length)]);
```

### 3. Chain Requests

Use Post-request scripts to chain requests:

```javascript
// Save ID from response for next request
const response = pm.response.json();
pm.environment.set("contact_id", response.data.id);
```

### 4. Run Collections in Batch

1. Click **Collection Runner** (or use the Runner tab)
2. Select the collection and environment
3. Set the number of iterations
4. Click **Run**

Watch all requests execute with results summary.

### 5. Export Results

After running a collection:
1. Click **Export Results** (in Runner)
2. Choose format (JSON, CSV)
3. Save file for documentation

## Monitoring Network Traffic

Enable Network Inspector to see:
- All HTTP requests
- Request/response headers
- Cookies
- Request timings

Click the **Network** tab at the bottom of Postman.

## Debugging Failed Requests

If a request fails:

1. **Check Status Code:**
   - 401 Unauthorized → Check `bearer_token`
   - 404 Not Found → Check URL and ID parameters
   - 422 Unprocessable Entity → Check request body
   - 500 Server Error → Check server logs

2. **View Response Body:**
   - Error messages often in response
   - Look for `message` or `errors` fields

3. **Verify Headers:**
   - Check Authorization header exists
   - Ensure Content-Type is correct

4. **Check Network:**
   - Open Network Inspector tab
   - View full request/response

5. **Test API Health:**
   - Run **Public - Health Check** (no auth needed)
   - Confirms API is responding

## API Documentation Reference

For detailed endpoint documentation, see:
- `POSTMAN_API_DOCUMENTATION.md` in this folder
- Each endpoint's request has a description

## Common Issues & Solutions

### Issue: 401 Unauthorized

**Solution:**
1. Login again to get fresh token
2. Paste token in environment
3. Verify Authorization header is set to `Bearer {{bearer_token}}`

### Issue: 404 Not Found

**Solution:**
1. Verify the endpoint path is correct
2. Check that ID parameters exist
3. Confirm environment variables are set correctly

### Issue: CORS Error (browser)

**Solution:**
This is normal in browsers. Postman handles it. If you need browser access, check API CORS settings.

### Issue: Connection Refused

**Solution:**
1. Verify `base_url` is correct
2. Check if API server is running
3. Try health check endpoint first

## Support

For issues or questions:
1. Check the documentation: `POSTMAN_API_DOCUMENTATION.md`
2. Review endpoint descriptions in Postman
3. Check API server logs for errors

## Next Steps

1. ✅ Import collection and environment
2. ✅ Set authentication token
3. ✅ Test a simple endpoint (Health Check)
4. ✅ Create your first contact
5. ✅ Explore other endpoints
6. ✅ Automate workflows

Enjoy using the Nexus Platform API!
