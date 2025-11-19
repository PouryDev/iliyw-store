#!/bin/bash

# Script to configure Docker to use Arvan Cloud mirror
# This script sets up the Docker daemon to use Arvan Cloud's Docker Hub mirror

set -e

DAEMON_JSON_PATH="/etc/docker/daemon.json"
PROJECT_DAEMON_JSON="$(dirname "$0")/docker/daemon.json"

echo "🔧 Setting up Arvan Cloud Docker Mirror..."

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then 
    echo "❌ This script must be run as root or with sudo"
    echo "Please run: sudo $0"
    exit 1
fi

# Check if daemon.json already exists
if [ -f "$DAEMON_JSON_PATH" ]; then
    echo "⚠️  /etc/docker/daemon.json already exists"
    echo "Current content:"
    cat "$DAEMON_JSON_PATH"
    echo ""
    read -p "Do you want to backup and replace it? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Aborted."
        exit 1
    fi
    # Backup existing file
    cp "$DAEMON_JSON_PATH" "${DAEMON_JSON_PATH}.backup.$(date +%Y%m%d_%H%M%S)"
    echo "✅ Backup created"
fi

# Create /etc/docker directory if it doesn't exist
mkdir -p /etc/docker

# Copy the daemon.json file
if [ -f "$PROJECT_DAEMON_JSON" ]; then
    cp "$PROJECT_DAEMON_JSON" "$DAEMON_JSON_PATH"
    echo "✅ Copied daemon.json to /etc/docker/"
else
    echo "❌ Project daemon.json not found at $PROJECT_DAEMON_JSON"
    exit 1
fi

# Detect OS and restart Docker accordingly
if [[ "$OSTYPE" == "darwin"* ]]; then
    echo "🍎 Detected macOS"
    echo "Please restart Docker Desktop manually or run:"
    echo "  osascript -e 'quit app \"Docker\"' && open -a Docker"
elif command -v systemctl &> /dev/null; then
    echo "🐧 Detected Linux with systemd"
    echo "🔄 Restarting Docker service..."
    systemctl restart docker
    echo "✅ Docker service restarted"
else
    echo "⚠️  Could not detect how to restart Docker"
    echo "Please restart Docker manually"
fi

echo ""
echo "✅ Docker mirror configuration completed!"
echo ""
echo "To verify, run:"
echo "  docker info | grep -A 5 'Registry Mirrors'"
echo ""

