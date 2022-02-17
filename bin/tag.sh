#!/bin/bash

# This script will be executed after commit in placed in .git/hooks/post-commit

# Semantic Versioning guideline
# By Jonatan Reginato
#
# Given a version number MAJOR.MINOR.PATCH, increment the:
# MAJOR version when you make incompatible API changes,
# MINOR version when you add functionality in a backwards-compatible manner, and
# PATCH version when you make backwards-compatible bug fixes.
#
# see https://gitversion.net/docs/ and https://semver.org/

echo "Updating application files"
git pull origin production

echo "Starting the taging process based on commit message +semver: xxxxx"

# Get highest tag number across all branches, not just the current branch
# VERSION=$(git describe --abbrev=0 --tags)
VERSION=$(git describe --tags "$(git rev-list --tags --max-count=1)")

if [ -z "$VERSION" ]; then
    NEW_TAG="1.0.0"
    echo "No tag present."
    echo "Creating tag: $NEW_TAG"
    git tag $NEW_TAG -a -m $NEW_TAG
    git push --tag
    echo "Tag created and pushed: $NEW_TAG"
    exit 0
fi

echo "Latest version tag: $VERSION"

# Replace . with space so can split into an array
IFS='.' read -r -a VERSION_BITS <<<"${VERSION}"

# Get number parts and increase last one by 1
VNUM1=${VERSION_BITS[0]}
VNUM2=${VERSION_BITS[1]}
VNUM3=${VERSION_BITS[2]}

# Taken from gitversion
# major-version-bump-message: '\+semver:\s?(breaking|major)'
# minor-version-bump-message: '\+semver:\s?(feature|minor)'
# patch-version-bump-message: '\+semver:\s?(fix|patch)'
# get last commit message and extract the count for "semver: (major|minor|patch)"
COUNT_OF_COMMIT_MSG_HAVE_SEMVER_MAJOR=$(git log -1 --pretty=%B | grep -E -c '\+semver:\s?(breaking|major)')
COUNT_OF_COMMIT_MSG_HAVE_SEMVER_MINOR=$(git log -1 --pretty=%B | grep -E -c '\+semver:\s?(feature|minor)')
COUNT_OF_COMMIT_MSG_HAVE_SEMVER_PATCH=$(git log -1 --pretty=%B | grep -E -c '\+semver:\s?(fix|patch)')

if [ "$COUNT_OF_COMMIT_MSG_HAVE_SEMVER_MAJOR" -gt 0 ]; then
    VNUM1=$((VNUM1 + 1))
fi
if [ "$COUNT_OF_COMMIT_MSG_HAVE_SEMVER_MINOR" -gt 0 ]; then
    VNUM2=$((VNUM2 + 1))
fi
if [ "$COUNT_OF_COMMIT_MSG_HAVE_SEMVER_PATCH" -gt 0 ]; then
    VNUM3=$((VNUM3 + 1))
fi

# Count all commits for a branch
GIT_COMMIT_COUNT=$(git rev-list --count HEAD)
echo "Commit count: $GIT_COMMIT_COUNT"
export BUILD_NUMBER=$GIT_COMMIT_COUNT

# Create new tag
NEW_TAG="$VNUM1.$VNUM2.$VNUM3"

echo "Updating $VERSION to $NEW_TAG"

# Only tag if commit message have version-bump-message as mentioned above
if [ "$COUNT_OF_COMMIT_MSG_HAVE_SEMVER_MAJOR" -gt 0 ] || [ "$COUNT_OF_COMMIT_MSG_HAVE_SEMVER_MINOR" -gt 0 ] || [ "$COUNT_OF_COMMIT_MSG_HAVE_SEMVER_PATCH" -gt 0 ]; then
    echo "Tagged with $NEW_TAG (Ignoring fatal:cannot describe - this means commit is untagged)"
    git tag "$NEW_TAG" -a -m $NEW_TAG
    git push --tag
else
    echo "Already a tag on this commit"
fi
