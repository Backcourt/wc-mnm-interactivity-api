#!/bin/bash
set -e
# Absolute path to this script
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
# Absolute path to plugin root
PLUGIN_DIR="$(dirname "$SCRIPT_DIR")"
# Plugin slug (directory name)
PLUGIN_SLUG="$(basename "$PLUGIN_DIR")"
# Get version from package.json
PLUGIN_VERSION=$(node -p "require('$PLUGIN_DIR/package.json').version")
# Compile directory
COMPILE_DIR="$PLUGIN_DIR/compile"
# Deploy directory
DEPLOY_DIR="$PLUGIN_DIR/deploy"

echo "🧹 Cleaning up old compile..."
rm -rf "$COMPILE_DIR"
mkdir "$COMPILE_DIR"

echo "📁 Creating deploy directory..."
mkdir -p "$DEPLOY_DIR"

cd "$PLUGIN_DIR"

echo "📦 Installing Composer dependencies (with dev)..."
composer install --no-interaction

echo "🛠 Running build scripts..."
npm install
npm run build
composer run makepot

echo "🧬 Copying plugin files to compile directory..."
rsync -a --delete \
  --include='build/***' \
  --include='includes/***' \
  --include='languages/***' \
  --include='packages/***' \
  --include='src/***' \
  --include='templates/***' \
  --include='readme.txt' \
  --include="${PLUGIN_SLUG}.php" \
  --exclude='.bak' \
  --exclude='.gitkeep' \
  --exclude='composer.json' \
  --exclude='*' \
  ./ "$COMPILE_DIR"

echo "🗜 Zipping plugin from compile/ with version $PLUGIN_VERSION..."
cd "$PLUGIN_DIR"
# Rename compile to plugin slug
mv compile "$PLUGIN_SLUG"
# Zip the renamed folder
zip -r "${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip" "$PLUGIN_SLUG"
# Clean up renamed folder
rm -rf "$PLUGIN_SLUG"

echo "📦 Moving zip to deploy folder..."
mv "${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip" "$DEPLOY_DIR/"

echo "✅ Build complete. ZIP created: deploy/${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip"
