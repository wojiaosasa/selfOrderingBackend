#!/usr/bin/env bash
set -euo pipefail

POD_NAME="${POD_NAME:-myapp-pod}"

podman pod ps --filter "name=${POD_NAME}"
podman ps --pod --filter "pod=${POD_NAME}"
