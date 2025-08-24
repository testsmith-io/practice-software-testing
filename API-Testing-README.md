# 🔍 CS423 API Testing Assignment

## 📋 Overview

This repository contains API testing implementation for CS423 Software Testing course using the Practice Software Testing application.

## 🎯 Objectives

- Test **4 critical APIs** with comprehensive test coverage
- Apply **Domain Testing** and **State Transition Testing** techniques
- Integrate API tests into **CI/CD pipeline**
- Generate detailed **test reports** and documentation
- Deploy and test on **local environment**

## 🧪 APIs Under Test

### 🔐 API 1: Authentication

- **Endpoint**: `POST /users/login`
- **Purpose**: User authentication and token generation
- **Test Cases**: Basic authentication flow, credential validation
- **Collection**: `Authentication.postman_collection.json`

### 🏷️ API 2: Brands Search

- **Endpoint**: `GET /brands/search`
- **Purpose**: Search and filter brand information
- **Test Cases**: 35+ comprehensive test cases
- **Techniques**: Domain testing, boundary analysis, security testing
- **Collection**: `API1-Brands-Search.postman_collection.json`

### 📂 API 3: Categories Search

- **Endpoint**: `GET /categories/search`
- **Purpose**: Search and filter product categories
- **Test Cases**: 35+ comprehensive test cases
- **Techniques**: Domain testing, boundary analysis, security testing
- **Collection**: `API2-Categories-Search.postman_collection.json`

### 📋 API 4: Invoice Status Management

- **Endpoint**: `PUT /invoices/{id}/status`
- **Purpose**: Update invoice status with state transitions
- **Test Cases**: 40+ comprehensive test cases
- **Techniques**: State transition testing, authentication, authorization
- **Collection**: `API3-Invoice-Status.postman_collection.json`

## 🛠️ Testing Techniques Applied

### 1. 🔬 Domain Testing

- **Input Validation**: Valid/invalid data ranges
- **Boundary Value Analysis**: Edge cases and limits
- **Equivalence Partitioning**: Input data classes
- **Error Handling**: Invalid inputs and error responses

### 2. 🔄 State Transition Testing

- **Invoice States**: PENDING → PAID → SHIPPED → DELIVERED
- **Authentication States**: Guest → Authenticated → Authorized
- **Invalid Transitions**: Testing forbidden state changes
- **Idempotency**: Same state transitions

### 3. 🔒 Security Testing

- **XSS Injection**: Cross-site scripting attempts
- **SQL Injection**: Database injection attacks
- **Authentication Bypass**: Token manipulation
- **Authorization**: Access control validation

## 🚀 Local Setup & Deployment

### Prerequisites

- Docker & Docker Compose
- Node.js 18+
- npm or yarn

### 1. Clone Repository

```bash
git clone <repository-url>
cd practice-software-testing
```

### 2. Install Dependencies

```bash
npm install
```

### 3. Deploy Application Locally

```bash
# Start all services with Docker Compose
docker compose up -d

# Wait for services to be ready (about 2 minutes)
# Setup database with seed data
docker compose exec laravel-api php artisan migrate:refresh --seed
```

### 4. Verify Deployment

```bash
# Check API health
curl http://localhost:8091/status

# Check application
curl http://localhost:8080
```

## 🧪 Running API Tests

### Run All Tests

```bash
npm run api:test:all
```

### Run Individual Test Suites

```bash
# Authentication tests
npm run api:test:auth

# Brands search tests
npm run api:test:brands

# Categories search tests
npm run api:test:categories

# Invoice status tests
npm run api:test:invoices
```

### Clean Reports

```bash
npm run api:clean
```

## 📊 Test Reports

Test reports are generated in the `reports/` directory:

- **HTML Reports**: Detailed visual reports with charts and graphs
- **JSON Reports**: Machine-readable test results
- **CLI Output**: Console output during test execution

### Report Files

- `reports/authentication-report.html`
- `reports/brands-report.html`
- `reports/categories-report.html`
- `reports/invoices-report.html`

## 🔄 CI/CD Integration

### GitHub Actions Workflow

The repository includes a comprehensive GitHub Actions workflow (`.github/workflows/api-testing.yml`) that:

1. **🚀 Setup**: Deploy application locally using Docker
2. **🔐 Auth Tests**: Run authentication API tests
3. **🏷️ Brands Tests**: Run brands search API tests
4. **📂 Categories Tests**: Run categories search API tests
5. **📋 Invoices Tests**: Run invoice status API tests
6. **📊 Report**: Generate consolidated CS423 assignment report
7. **🧹 Cleanup**: Stop containers and cleanup resources

### Trigger Workflow

- **Automatic**: On push to `main` or `hw7-api-testing` branches
- **Manual**: Using workflow_dispatch with test scope selection
- **Pull Request**: On PRs to main branch

### Artifacts Generated

- Individual test reports for each API
- Consolidated CS423 assignment report
- All artifacts retained for 30 days

## 📁 Project Structure

```
practice-software-testing/
├── 📄 Authentication.postman_collection.json       # Auth API tests
├── 📄 API1-Brands-Search.postman_collection.json   # Brands API tests (35 cases)
├── 📄 API2-Categories-Search.postman_collection.json # Categories API tests (35 cases)
├── 📄 API3-Invoice-Status.postman_collection.json  # Invoice API tests (40 cases)
├── 🌐 Local-Toolshop.postman_environment.json     # Test environment config
├── 📦 package.json                                 # Dependencies & scripts
├── 🚀 .github/workflows/api-testing.yml           # CI/CD pipeline
├── 🐳 docker-compose.yml                          # Local deployment
├── 📋 README.md                                    # This file
└── 📂 reports/                                     # Generated test reports
```

## 🎓 CS423 Assignment Compliance

### ✅ Requirements Met

- [x] **4 APIs tested** (exceeds minimum 3 requirement)
- [x] **110+ total test cases** (exceeds 30+ per API requirement)
- [x] **Domain Testing** implemented across all APIs
- [x] **State Transition Testing** implemented for invoice status
- [x] **CI/CD Integration** with GitHub Actions
- [x] **Local Deployment** using Docker Compose
- [x] **Comprehensive Documentation** and reporting
- [x] **Security Testing** included in test suites

### 📊 Test Coverage Summary

| API               | Test Cases     | Techniques              | Status       |
| ----------------- | -------------- | ----------------------- | ------------ |
| Authentication    | Basic Flow     | Domain Testing          | ✅ Complete  |
| Brands Search     | 35 cases       | Domain + Security       | ✅ Complete  |
| Categories Search | 35 cases       | Domain + Security       | ✅ Complete  |
| Invoice Status    | 40 cases       | State Transition + Auth | ✅ Complete  |
| **Total**         | **110+ cases** | **All Techniques**      | **✅ Ready** |

## 🐛 Bug Discovery

During testing, the following areas are monitored for potential bugs:

- **Authentication**: Token handling, session management
- **Search APIs**: Input validation, SQL injection vulnerabilities
- **State Transitions**: Invalid status changes, business logic errors
- **Authorization**: Access control bypasses
- **Performance**: Response times, timeout handling

## 🔗 Additional Resources

- [Practice Software Testing Documentation](https://api.practicesoftwaretesting.com/api/documentation)
- [Postman API Testing Guide](https://learning.postman.com/docs/writing-scripts/test-scripts/)
- [Newman CLI Documentation](https://github.com/postmanlabs/newman)
- [Docker Compose Documentation](https://docs.docker.com/compose/)

## 📝 Assignment Submission

For CS423 assignment submission, include:

1. ✅ **Source Code**: This repository with all collections
2. ✅ **Test Reports**: HTML reports from `reports/` directory
3. ✅ **CI/CD Evidence**: GitHub Actions workflow execution screenshots
4. ✅ **Bug Reports**: Any discovered issues during testing
5. ✅ **Documentation**: This README and assignment report

---

**Course**: CS423 - Software Testing  
**Assignment**: API Testing  
**Platform**: Practice Software Testing  
**Framework**: Postman + Newman + GitHub Actions  
**Deployment**: Docker Compose (Local)
