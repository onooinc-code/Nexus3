/**
 * Tasks Hub OpenAPI Specification Export
 * Nexus Monolith - Dedicated Tasks Hub API Documentation
 */

const tasksHubOpenApiSpec = {
  "openapi": "3.0.3",
  "info": {
    "title": "Nexus Tasks Hub API Documentation",
    "description": "Dedicated API Specification and Swagger Documentation exclusively for the Tasks Hub in Nexus3 Monolith.",
    "version": "1.0.0",
    "contact": {
      "name": "Nexus Development Team",
      "url": "https://n.soulyeg.online/hub/tasks"
    }
  },
  "servers": [
    {
      "url": "https://n.soulyeg.online/api/v1",
      "description": "Nexus Production API v1"
    },
    {
      "url": "http://localhost:8000/api/v1",
      "description": "Local Development Server"
    }
  ],
  "tags": [
    { "name": "Tasks Management", "description": "Core CRUD operations for agent tasks" },
    { "name": "Task Lifecycle & Actions", "description": "State transition controls (execute, pause, resume, cancel, status update, logs)" },
    { "name": "Type-Specific Task Creation", "description": "Endpoints for creating manual, agentic, and system pipeline tasks" },
    { "name": "Tasks Analytics & Stats", "description": "System monitoring, active tasks, queue metrics, and routing performance" },
    { "name": "Sub-tasks Management", "description": "API endpoints for adding, toggling, and deleting sub-tasks within tasks" },
    { "name": "Task Templates", "description": "Template management and spawning parameterized tasks" }
  ],
  "components": {
    "securitySchemes": {
      "bearerAuth": {
        "type": "http",
        "scheme": "bearer",
        "bearerFormat": "Sanctum Token"
      }
    },
    "schemas": {
      "AgentTask": {
        "type": "object",
        "properties": {
          "id": { "type": "integer", "example": 101 },
          "title": { "type": "string", "example": "Data Analysis & Report Sync" },
          "description": { "type": "string", "example": "Process incoming user feedback logs" },
          "type": { "type": "string", "enum": ["manual", "agent", "system"], "example": "agent" },
          "status": { "type": "string", "enum": ["todo", "in-progress", "blocked", "completed", "failed", "cancelled"], "example": "todo" },
          "priority": { "type": "integer", "example": 5 },
          "progress": { "type": "integer", "example": 0 },
          "agent_id": { "type": "integer", "nullable": true, "example": 2 },
          "workflow_id": { "type": "integer", "nullable": true, "example": null },
          "contact_id": { "type": "integer", "nullable": true, "example": null },
          "conversation_id": { "type": "integer", "nullable": true, "example": null },
          "due_date": { "type": "string", "format": "date-time", "nullable": true, "example": "2026-07-25T12:00:00Z" },
          "payload_data": { "type": "object", "nullable": true },
          "result_data": { "type": "object", "nullable": true },
          "metadata": { "type": "object", "nullable": true, "example": { "subtasks": [{ "id": "subtask_1", "title": "Setup database", "completed": true }] } },
          "created_at": { "type": "string", "format": "date-time" },
          "updated_at": { "type": "string", "format": "date-time" }
        }
      },
      "SubTask": {
        "type": "object",
        "properties": {
          "id": { "type": "string", "example": "subtask_1721445000123" },
          "title": { "type": "string", "example": "Design database schema" },
          "completed": { "type": "boolean", "example": false }
        }
      },
      "TaskTemplate": {
        "type": "object",
        "properties": {
          "id": { "type": "integer", "example": 5 },
          "name": { "type": "string", "example": "Daily Backup Pipeline" },
          "task_type": { "type": "string", "example": "system" },
          "title_template": { "type": "string", "example": "Backup DB {db_name}" },
          "payload_template": { "type": "object" },
          "expected_variables": { "type": "array", "items": { "type": "string" }, "example": ["db_name"] },
          "created_at": { "type": "string", "format": "date-time" },
          "updated_at": { "type": "string", "format": "date-time" }
        }
      }
    }
  },
  "security": [{ "bearerAuth": [] }],
  "paths": {
    "/tasks/stats": { "get": { "tags": ["Tasks Analytics & Stats"], "summary": "Get overall tasks statistics" } },
    "/tasks/active": { "get": { "tags": ["Tasks Analytics & Stats"], "summary": "List active tasks" } },
    "/tasks/queue-stats": { "get": { "tags": ["Tasks Analytics & Stats"], "summary": "Get task queue metrics" } },
    "/tasks/routing-stats": { "get": { "tags": ["Tasks Analytics & Stats"], "summary": "Get task routing metrics" } },
    "/tasks/stats/by-type": { "get": { "tags": ["Tasks Analytics & Stats"], "summary": "Get tasks breakdown by type" } },
    "/tasks": {
      "get": { "tags": ["Tasks Management"], "summary": "List all tasks" },
      "post": { "tags": ["Tasks Management"], "summary": "Create a generic task" }
    },
    "/tasks/manual": { "post": { "tags": ["Type-Specific Task Creation"], "summary": "Create a manual task" } },
    "/tasks/agent": { "post": { "tags": ["Type-Specific Task Creation"], "summary": "Create an agentic task" } },
    "/tasks/system": { "post": { "tags": ["Type-Specific Task Creation"], "summary": "Create a system task" } },
    "/tasks/type/{type}": { "get": { "tags": ["Tasks Management"], "summary": "Get tasks by type" } },
    "/tasks/{task}": {
      "get": { "tags": ["Tasks Management"], "summary": "Get task details" },
      "put": { "tags": ["Tasks Management"], "summary": "Update task" },
      "patch": { "tags": ["Tasks Management"], "summary": "Partial update task" },
      "delete": { "tags": ["Tasks Management"], "summary": "Delete task" }
    },
    "/tasks/{task}/execute": { "post": { "tags": ["Task Lifecycle & Actions"], "summary": "Force execute task" } },
    "/tasks/{task}/logs": { "get": { "tags": ["Task Lifecycle & Actions"], "summary": "Get task execution logs" } },
    "/tasks/{task}/status": { "patch": { "tags": ["Task Lifecycle & Actions"], "summary": "Update task status via state machine" } },
    "/tasks/{task}/cancel": { "post": { "tags": ["Task Lifecycle & Actions"], "summary": "Cancel task execution" } },
    "/tasks/{task}/pause": { "post": { "tags": ["Task Lifecycle & Actions"], "summary": "Pause task execution" } },
    "/tasks/{task}/resume": { "post": { "tags": ["Task Lifecycle & Actions"], "summary": "Resume task execution" } },
    "/tasks/{task}/subtasks": { "post": { "tags": ["Sub-tasks Management"], "summary": "Add a sub-task to a task" } },
    "/tasks/{task}/subtasks/{subtask}": {
      "patch": { "tags": ["Sub-tasks Management"], "summary": "Toggle sub-task completion status" },
      "delete": { "tags": ["Sub-tasks Management"], "summary": "Delete a sub-task" }
    },
    "/task-templates": {
      "get": { "tags": ["Task Templates"], "summary": "List task templates" },
      "post": { "tags": ["Task Templates"], "summary": "Create a task template" }
    },
    "/task-templates/{taskTemplate}": {
      "get": { "tags": ["Task Templates"], "summary": "Get task template details" },
      "put": { "tags": ["Task Templates"], "summary": "Update task template" },
      "delete": { "tags": ["Task Templates"], "summary": "Delete task template" }
    },
    "/task-templates/{taskTemplate}/spawn": { "post": { "tags": ["Task Templates"], "summary": "Spawn task from template" } }
  }
};

if (typeof module !== 'undefined' && module.exports) {
  module.exports = tasksHubOpenApiSpec;
}
if (typeof window !== 'undefined') {
  window.tasksHubOpenApiSpec = tasksHubOpenApiSpec;
}
