# 🚀 CI/CD Integration for CS423 Assignment

## 📋 GitHub Actions Workflow Overview

Our CS423 API Testing assignment is fully integrated with **GitHub Actions** for continuous integration and automated testing.

### 🔗 Workflow File

- **Location**: `.github/workflows/api-testing.yml`
- **Name**: 🔍 CS423 API Testing
- **Badge**: [![CS423 API Testing](https://github.com/TuanPh1608/practice-software-testing/actions/workflows/api-testing.yml/badge.svg)](https://github.com/TuanPh1608/practice-software-testing/actions/workflows/api-testing.yml)

## 🎯 CI/CD Pipeline Jobs

### Job 1: 🚀 Setup & Deploy Local App

- **Purpose**: Deploy Practice Software Testing application locally
- **Actions**:
  - Start Docker containers
  - Setup database with seed data
  - Perform health checks
  - Validate API endpoints

### Job 2: 🔐 Authentication API Tests

- **Tests**: Basic authentication flow
- **Collection**: `Authentication.postman_collection.json`
- **Techniques**: Domain testing, credential validation

### Job 3: 🏷️ Brands Search API Tests

- **Tests**: 35 comprehensive test cases
- **Collection**: `API1-Brands-Search.postman_collection.json`
- **Techniques**: Domain testing, boundary analysis, security testing

### Job 4: 📂 Categories Search API Tests

- **Tests**: 35 comprehensive test cases
- **Collection**: `API2-Categories-Search.postman_collection.json`
- **Techniques**: Domain testing, boundary analysis, security testing

### Job 5: 📋 Invoice Status API Tests

- **Tests**: 40 comprehensive test cases
- **Collection**: `API3-Invoice-Status.postman_collection.json`
- **Techniques**: State transition testing, authentication, authorization

### Job 6: 📊 Generate CS423 Assignment Report

- **Purpose**: Create comprehensive assignment documentation
- **Outputs**: Consolidated test reports, assignment summary

### Job 7: 📢 PR Comments & Notifications

- **Purpose**: Automated PR feedback and status reporting
- **Features**: Test results summary, success rates, actionable feedback

### Job 8: 🧹 Cleanup

- **Purpose**: Clean up Docker containers and resources

## ⚡ Trigger Conditions

### Automatic Triggers

- **Push to main branch**: Full test suite execution
- **Push to hw7-api-testing branch**: Development testing
- **Pull Request to main**: Quality gate before merge

### Manual Trigger (workflow_dispatch)

- **Test Scope Options**:
  - `all`: Run all API tests (default)
  - `auth`: Authentication tests only
  - `brands`: Brands search tests only
  - `categories`: Categories search tests only
  - `invoices`: Invoice status tests only

### Manual Trigger Example

```bash
# Via GitHub UI: Actions tab > CS423 API Testing > Run workflow
# Select test scope and deploy options
```

## 📊 CI/CD Features

### ✅ Automated Testing

- **110+ test cases** executed automatically
- **Parallel execution** for faster results
- **Comprehensive reporting** with HTML outputs

### ✅ Quality Gates

- **Health checks** before testing
- **Error handling** and retry mechanisms
- **Artifact retention** for 30 days

### ✅ Notifications

- **PR comments** with test results
- **Status badges** in README
- **Detailed failure reporting**

### ✅ Documentation

- **Automated report generation**
- **CS423 assignment documentation**
- **Evidence for academic submission**

## 🔧 Configuration

### Environment Variables

```yaml
env:
  NODE_VERSION: "18"
  REPORTS_RETENTION_DAYS: 30
```

### Secrets Required

- None (all tests run against local deployment)

### Permissions

- **Contents**: read (checkout code)
- **Issues**: write (PR comments)
- **Pull-requests**: write (PR comments)

## 📈 Monitoring & Reports

### Artifacts Generated

1. **authentication-test-reports**: Authentication API test results
2. **brands-test-reports**: Brands search test results
3. **categories-test-reports**: Categories search test results
4. **invoices-test-reports**: Invoice status test results
5. **cs423-final-assignment-report**: Complete assignment documentation

### Report Formats

- **HTML**: Visual reports with charts and graphs
- **JSON**: Machine-readable test results
- **Markdown**: Assignment documentation

## 🎓 CS423 Assignment Integration

### Academic Compliance

- ✅ **CI/CD Requirement**: Fully integrated with GitHub Actions
- ✅ **Automated Execution**: Tests run on every code change
- ✅ **Professional Documentation**: Comprehensive reporting
- ✅ **Quality Assurance**: Multiple validation stages

### Submission Evidence

1. **Workflow Badge**: Shows CI status
2. **Actions History**: Execution logs and results
3. **Artifacts**: Downloadable test reports
4. **PR Comments**: Automated feedback examples

## 🚀 Usage Examples

### Running via Git Push

```bash
git add .
git commit -m "feat: update API tests"
git push origin hw7-api-testing
# ⚡ Automatically triggers CI pipeline
```

### Creating Pull Request

```bash
# Create PR from hw7-api-testing to main
# ⚡ Automatically triggers CI with PR comments
```

### Manual Execution

1. Go to **Actions** tab in GitHub
2. Select **🔍 CS423 API Testing**
3. Click **Run workflow**
4. Choose test scope and options
5. Click **Run workflow** button

## 🔍 Troubleshooting

### Common Issues

1. **Docker startup failures**: Check container logs in workflow
2. **Database connection errors**: Verify seed data setup
3. **API health check timeouts**: Increase wait times if needed

### Debug Information

- All workflows include detailed logging
- Container status and logs are captured
- Health check results are displayed
- Error messages include context

## 📚 Learning Outcomes

This CI/CD integration demonstrates:

1. **Professional Development Practices**

   - Automated testing pipelines
   - Quality gates and validations
   - Documentation automation

2. **Testing Best Practices**

   - Comprehensive test coverage
   - Multiple testing techniques
   - Automated reporting

3. **DevOps Skills**
   - Docker containerization
   - GitHub Actions workflows
   - Artifact management

---

**🎯 Assignment Goal Achieved**: Complete CI/CD integration for CS423 API Testing with professional-grade automation, reporting, and quality assurance.
