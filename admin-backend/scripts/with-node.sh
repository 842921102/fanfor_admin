#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
NODE_DIR="$ROOT/.node-v22.12.0"
NODE_BIN="$NODE_DIR/bin/node"
NODE_VERSION="22.12.0"

node_is_usable() {
    local bin="$1"

    if [[ ! -x "$bin" ]]; then
        return 1
    fi

    "$bin" -e '
        const [major, minor] = process.versions.node.split(".").map(Number);
        const ok = major > 22 || (major === 22 && minor >= 12) || (major === 20 && minor >= 19);
        process.exit(ok ? 0 : 1);
    ' >/dev/null 2>&1
}

download_node() {
    local arch tarball url

    arch="$(uname -m)"
    case "$arch" in
        arm64) tarball="node-v${NODE_VERSION}-darwin-arm64.tar.gz" ;;
        x86_64) tarball="node-v${NODE_VERSION}-darwin-x64.tar.gz" ;;
        *)
            echo "with-node: unsupported architecture: $arch" >&2
            exit 1
            ;;
    esac

    url="https://nodejs.org/dist/v${NODE_VERSION}/${tarball}"
    echo "with-node: downloading Node.js v${NODE_VERSION} for ${arch}..."

    mkdir -p "$NODE_DIR"
    curl -fsSL "$url" | tar -xz -C "$NODE_DIR" --strip-components=1
}

if ! node_is_usable "$NODE_BIN"; then
    if node_is_usable "$(command -v node 2>/dev/null || true)"; then
        export PATH="$(dirname "$(command -v node)"):$PATH"
        exec "$@"
    fi

    download_node
fi

export PATH="$NODE_DIR/bin:$PATH"
exec "$@"
