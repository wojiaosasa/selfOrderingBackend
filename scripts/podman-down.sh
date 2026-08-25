#!/usr/bin/env bash
set -euo pipefail

POD_NAME="${POD_NAME:-myapp-pod}"

podman pod rm -f "$POD_NAME"
