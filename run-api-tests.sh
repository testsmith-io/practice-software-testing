#!/bin/bash

# 🔍 CS423 API Testing Script
# This script helps you run API tests locally for the CS423 assignment

set -e

echo "🎓 ===== CS423 API Testing Script ====="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Newman is installed
check_newman() {
    if ! command -v newman &> /dev/null; then
        print_warning "Newman is not installed globally. Installing dependencies..."
        npm install
        return 1
    fi
    return 0
}

# Check if Docker is running
check_docker() {
    if ! docker info &> /dev/null; then
        print_error "Docker is not running. Please start Docker and try again."
        exit 1
    fi
}

# Check if application is running
check_app() {
    print_status "Checking if application is running..."
    if curl -s http://localhost:8091/status &> /dev/null; then
        print_success "Application is running on localhost:8091"
        return 0
    else
        print_warning "Application is not running. Starting it now..."
        return 1
    fi
}

# Start the application
start_app() {
    print_status "Starting Practice Software Testing application..."
    
    export DISABLE_LOGGING=true
    docker compose -f docker-compose.yml up -d --force-recreate
    
    print_status "Waiting for services to start (this may take 2-3 minutes)..."
    sleep 90
    
    print_status "Setting up database with seed data..."
    docker compose exec -T laravel-api php artisan migrate:refresh --seed
    
    print_status "Performing health check..."
    for i in {1..10}; do
        if curl -s http://localhost:8091/status &> /dev/null; then
            print_success "Application is ready!"
            return 0
        fi
        print_status "Waiting... (attempt $i/10)"
        sleep 10
    done
    
    print_error "Application failed to start properly"
    exit 1
}

# Run specific API test
run_test() {
    local test_name=$1
    local collection_file=$2
    local report_name=$3
    
    print_status "Running $test_name tests..."
    
    mkdir -p reports
    
    if check_newman; then
        newman run "$collection_file" \
            -e Local-Toolshop.postman_environment.json \
            --reporters cli,htmlextra \
            --reporter-htmlextra-export "reports/$report_name.html" \
            --reporter-htmlextra-title "$test_name - CS423 Assignment" \
            --timeout 30000 \
            --delay-request 500 \
            --color on
    else
        npm run "api:test:$(echo $report_name | cut -d'-' -f1)"
    fi
    
    if [ $? -eq 0 ]; then
        print_success "$test_name tests completed successfully"
    else
        print_warning "$test_name tests completed with some failures"
    fi
}

# Main menu
show_menu() {
    echo ""
    echo "🔍 Choose what to test:"
    echo "1) 🔐 Authentication API"
    echo "2) 🏷️  Brands Search API (35 test cases)"
    echo "3) 📂 Categories Search API (35 test cases)"
    echo "4) 📋 Invoice Status API (40 test cases)"
    echo "5) 🚀 Run ALL API Tests (110+ test cases)"
    echo "6) 🐳 Start/Restart Application"
    echo "7) 🧹 Clean Reports"
    echo "8) 📊 Open Reports Directory"
    echo "9) ❌ Exit"
    echo ""
    read -p "Enter your choice (1-9): " choice
}

# Process menu choice
process_choice() {
    case $choice in
        1)
            run_test "Authentication" "Authentication.postman_collection.json" "authentication-report"
            ;;
        2)
            run_test "Brands Search" "API1-Brands-Search.postman_collection.json" "brands-report"
            ;;
        3)
            run_test "Categories Search" "API2-Categories-Search.postman_collection.json" "categories-report"
            ;;
        4)
            run_test "Invoice Status" "API3-Invoice-Status.postman_collection.json" "invoices-report"
            ;;
        5)
            print_status "Running ALL API tests - this will take several minutes..."
            run_test "Authentication" "Authentication.postman_collection.json" "authentication-report"
            run_test "Brands Search" "API1-Brands-Search.postman_collection.json" "brands-report"
            run_test "Categories Search" "API2-Categories-Search.postman_collection.json" "categories-report"
            run_test "Invoice Status" "API3-Invoice-Status.postman_collection.json" "invoices-report"
            print_success "All API tests completed! Check reports/ directory for results."
            ;;
        6)
            start_app
            ;;
        7)
            print_status "Cleaning reports directory..."
            rm -rf reports
            mkdir -p reports
            print_success "Reports directory cleaned"
            ;;
        8)
            if [ -d "reports" ]; then
                if command -v explorer &> /dev/null; then
                    explorer reports  # Windows
                elif command -v open &> /dev/null; then
                    open reports      # macOS
                elif command -v xdg-open &> /dev/null; then
                    xdg-open reports  # Linux
                else
                    print_status "Reports are in: $(pwd)/reports"
                    ls -la reports/
                fi
            else
                print_warning "No reports directory found. Run some tests first!"
            fi
            ;;
        9)
            print_success "Thanks for using CS423 API Testing Script!"
            exit 0
            ;;
        *)
            print_error "Invalid choice. Please try again."
            ;;
    esac
}

# Main execution
main() {
    # Check prerequisites
    check_docker
    
    # Check if app is running, start if needed
    if ! check_app; then
        start_app
    fi
    
    # Main loop
    while true; do
        show_menu
        process_choice
        echo ""
        read -p "Press Enter to continue..."
    done
}

# Run main function
main
