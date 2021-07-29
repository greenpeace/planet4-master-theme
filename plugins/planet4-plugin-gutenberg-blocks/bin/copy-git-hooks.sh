#!/usr/bin/env bash
install -C -m 755 .githooks/* .git/hooks
echo Copied git hooks
