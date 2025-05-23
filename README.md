# Skiff PHP API Documentation

## Base URL
`http://127.0.0.1:8080/skiff_php_api/api/`

## Authentication
- All endpoints except `/auth/*` require a Bearer token in the Authorization header
- Token format: `Bearer {JWT_TOKEN}`

---

## Endpoints

### Authentication

#### Login
```http
POST /auth/login

```
Request Body (JSON):
```json
{
  "email": "user@example.com",
  "password": "secret123"
}
```
Register
```http
POST /auth/register

```
Request Body (JSON):
```json
{
  "email": "new@example.com",
  "name": "username",
  "password": "secret123",
  "role": "user|agent|admin"
}
```
Logout
```http
POST /auth/logout
Headers:
Authorization: Bearer {token}
```

Department
Get Departments
```http
GET /department/getdepartments
Headers:
```
Authorization: Bearer {token}
Create Department
```http
POST /department/create
Headers:

Authorization: Bearer {token}
Content-Type: application/json
```
Request Body (JSON):

```json
{
  "name": "Department Name"
}
```
##Ticket
###Get Tickets
```http
GET /ticket/index
Headers:

Authorization: Bearer {token}
```
###Create Ticket
```http
POST /ticket/create
Headers:

Authorization: Bearer {token}
Content-Type: multipart/form-data
```
Form Data:

title: string

description: string

status: string

department_id: int

user_id: int


file: File upload (optional)

###Update Ticket
```http
PUT /ticket/update/{ticket_id}
Headers:

Authorization: Bearer {token}
Content-Type: application/json
```
###Request Body (JSON):

```json
{
  "title": "Updated Title",
  "description": "Updated description",
  "status": "closed|in_progress",
  "department_id": 1
}
```
###Add Ticket Note
```http
POST /ticket/addNote/{ticket_id}
Headers:

Authorization: Bearer {token}
Content-Type: application/json
```
###Request Body (JSON):

```json
{
  "user_id": 3,
  "note": "Additional information"
}
```
###Response Format
All responses are JSON format:

```json
{
  "success": true|false,
  "data": { ... },
  "error": "Error message (if any)"
}
```
