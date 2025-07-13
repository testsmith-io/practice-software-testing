#!/usr/bin/env bash

set -e

# Configuration
DOCKER_USER="testsmith"
SPRINTS=(
  sprint1
  sprint2
  sprint3
  sprint4
  sprint5
  sprint5-performance
  sprint5-with-bugs
)
COMPONENTS=("UI" "API")

# Flags
SKIP_PUSH=false
DRY_RUN=false
SELECTED_SPRINT=""
TAG=""
HELP=false

# Functions
print_help() {
  echo "Usage: $0 [options]"
  echo ""
  echo "Options:"
  echo "  -h, --help              Show this help message"
  echo "  -s, --sprint <name>     Build only specific sprint (e.g., sprint1, sprint5-performance)"
  echo "  -l, --list              List all available sprints"
  echo "  --tag <version>         Optional Docker tag to apply (e.g., v1.2.0)"
  echo "  --skip-push             Build images but don't push to Docker Hub"
  echo "  --dry-run               Show what would be built without actually building"
  echo ""
  echo "Examples:"
  echo "  $0                      # Build all sprints"
  echo "  $0 -s sprint1           # Build only sprint1"
  echo "  $0 --tag v1.2.0         # Build all with version tag and also push latest"
  exit 0
}

list_sprints() {
  echo "Available sprints:"
  for sprint in "${SPRINTS[@]}"; do
    echo "  - $sprint"
  done
  exit 0
}

build_image() {
  local sprint="$1"
  local component="$2"

  local sprint_lc=$(echo "$sprint" | tr '[:upper:]' '[:lower:]')
  local component_lc=$(echo "$component" | tr '[:upper:]' '[:lower:]')

  local base_image="$DOCKER_USER/practice-software-testing-${sprint_lc}-${component_lc}"
  local version_tag="${base_image}${TAG:+:$TAG}"
  local latest_tag="${base_image}:latest"

  local context_dir="./$sprint/$component"
  local dockerfile="./_docker/${component_lc}.docker"

  if [ ! -d "$context_dir" ]; then
    echo "⚠️  Skipping: Directory not found: $context_dir"
    return
  fi

  echo "📦 Building: $version_tag"
  echo "🔍 Context: $context_dir"
  echo "📝 Dockerfile: $dockerfile"

  if [ "$component_lc" = "api" ]; then
    echo "📁 Copying config files to $context_dir"
    rm -rf "$context_dir/_docker/"
    mkdir "$context_dir/_docker/"
    cp ./_docker/opcache.ini "$context_dir/_docker/" || true
    cp ./_docker/php-ini-overrides.ini "$context_dir/_docker/" || true
  fi

  if [ "$DRY_RUN" = false ]; then
    docker build -t "$version_tag" \
      --target production \
      -f "$dockerfile" "$context_dir"
  else
    echo "💡 Dry run: docker build -t \"$version_tag\" --target production -f \"$dockerfile\" \"$context_dir\""
  fi

  if [ "$component_lc" = "api" ]; then
    echo "🧹 Cleaning up config files from $context_dir"
    rm -rf "$context_dir/_docker/"
  fi

  if [ "$SKIP_PUSH" = false ] && [ "$DRY_RUN" = false ]; then
    echo "📤 Pushing: $version_tag"
    docker push "$version_tag"

    if [ -n "$TAG" ]; then
      echo "🏷  Tagging also as latest: $latest_tag"
      docker tag "$version_tag" "$latest_tag"
      echo "📤 Pushing: $latest_tag"
      docker push "$latest_tag"
    fi
  elif [ "$SKIP_PUSH" = false ]; then
    echo "💡 Dry run: docker push \"$version_tag\""
    [ -n "$TAG" ] && echo "💡 Dry run: docker tag \"$version_tag\" \"$latest_tag\" && docker push \"$latest_tag\""
  fi

  echo ""
}

# Parse arguments
while [[ $# -gt 0 ]]; do
  case "$1" in
    -h|--help)
      HELP=true
      ;;
    -s|--sprint)
      SELECTED_SPRINT="$2"
      shift
      ;;
    -l|--list)
      list_sprints
      ;;
    --tag)
      TAG="$2"
      shift
      ;;
    --skip-push)
      SKIP_PUSH=true
      ;;
    --dry-run)
      DRY_RUN=true
      ;;
    *)
      echo "❌ Unknown option: $1"
      exit 1
      ;;
  esac
  shift
done

$HELP && print_help

if [ -n "$SELECTED_SPRINT" ]; then
  TARGET_SPRINTS=("$SELECTED_SPRINT")
else
  TARGET_SPRINTS=("${SPRINTS[@]}")
fi

for sprint in "${TARGET_SPRINTS[@]}"; do
  for component in "${COMPONENTS[@]}"; do
    build_image "$sprint" "$component"
  done
done
