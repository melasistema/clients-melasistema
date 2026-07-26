#! /bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# --- Configuration ---
PRODUCTION_BRANCH="master" # GitHub default production branch

echo "--- Starting Release Process ---"

# 1. Ensure we are on the production branch
echo "Checking out the production branch: $PRODUCTION_BRANCH"
git checkout $PRODUCTION_BRANCH

# 2. Pull latest changes to avoid conflicts
echo "Pulling latest changes from origin..."
git pull origin $PRODUCTION_BRANCH

# 3. Run the release automation script
echo "Running release automation (semantic versioning, tagging, and changelog)..."
# This command bumps the version, creates a new commit, and tags it.
# Extra args are forwarded to standard-version, e.g.:
#   ./release.sh --release-as 1.0.0   (force a specific version — used once to leave 0.x)
#   ./release.sh --dry-run            (preview the bump without writing anything)
npm run release -- "$@"

# 4. Push the new commit and the tag to GitHub
echo "Pushing new release commit and tag to origin..."
git push origin $PRODUCTION_BRANCH
git push --tags

echo "--- Release Process Complete! ---"
echo "A new tag has been pushed to GitHub, which will trigger the CI/CD pipeline."
