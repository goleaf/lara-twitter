#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<'EOF'
codex-loop.sh

Runs Codex CLI in a loop with a fixed prompt and model settings.

Usage:
  ./codex-loop.sh [options]

Options:
  --interval SECONDS      Sleep between runs (default: 30)
  --sandbox MODE          Sandbox mode (default: danger-full-access)
  --post-run PATH         Run script after each Codex run
  --once                  Run once then exit
  --max-runs N            Stop after N runs (default: 0 = infinite)
  -h, --help              Show help

Environment:
  CODEX_BIN               Codex binary (default: codex)
  MODEL_NAME              Model name (default: gpt-5.2-codex)
  REASONING_EFFORT        Reasoning effort (default: xhigh)
  PROMPT_TEXT             Prompt override (default: built-in prompt)
  SANDBOX_MODE            Codex sandbox (default: danger-full-access)
  TEXT_VERBOSITY          Text verbosity (default: medium)
  POST_RUN_SCRIPT         Script to run after each Codex run (optional)
EOF
}

CODEX_BIN="${CODEX_BIN:-codex}"
MODEL_NAME="${MODEL_NAME:-gpt-5.2-codex}"
REASONING_EFFORT="${REASONING_EFFORT:-xhigh}"
PROMPT_TEXT="${PROMPT_TEXT:-Large multi-part request (analyze, fix errors, optimize, design, run tests). Planning to scope and sequence work. do not ask any questions, make all works automatically}"
SANDBOX_MODE="${SANDBOX_MODE:-danger-full-access}"
TEXT_VERBOSITY="${TEXT_VERBOSITY:-medium}"
POST_RUN_SCRIPT="${POST_RUN_SCRIPT:-}"

interval_seconds=30
run_once=false
max_runs=0

while [[ $# -gt 0 ]]; do
  case "$1" in
    --interval)
      interval_seconds="${2:-}"; shift 2 ;;
    --sandbox)
      SANDBOX_MODE="${2:-}"; shift 2 ;;
    --post-run)
      POST_RUN_SCRIPT="${2:-}"; shift 2 ;;
    --once)
      run_once=true; shift ;;
    --max-runs)
      max_runs="${2:-}"; shift 2 ;;
    -h|--help)
      usage; exit 0 ;;
    *)
      echo "Unknown option: $1" >&2
      usage
      exit 2
      ;;
  esac
done

if ! command -v "$CODEX_BIN" >/dev/null 2>&1; then
  echo "codex not found in PATH (set CODEX_BIN if needed)" >&2
  exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

runs_done=0

while :; do
  if "$CODEX_BIN" --ask-for-approval never exec \
    --model "$MODEL_NAME" \
    --sandbox "$SANDBOX_MODE" \
    --color never \
    -c model_reasoning_effort="$REASONING_EFFORT" \
    -c text.verbosity="$TEXT_VERBOSITY" \
    - <<<"$PROMPT_TEXT"; then
    :
  else
    exit_code=$?
    echo "codex exited with status $exit_code" >&2
  fi

  if [[ -n "$POST_RUN_SCRIPT" ]]; then
    if [[ ! -e "$POST_RUN_SCRIPT" ]]; then
      echo "post-run script not found: $POST_RUN_SCRIPT" >&2
      exit 1
    fi

    if [[ -x "$POST_RUN_SCRIPT" ]]; then
      "$POST_RUN_SCRIPT"
    else
      bash "$POST_RUN_SCRIPT"
    fi
  fi

  runs_done=$((runs_done + 1))
  if [[ "$max_runs" -gt 0 && "$runs_done" -ge "$max_runs" ]]; then
    exit 0
  fi

  if $run_once; then
    exit 0
  fi

  sleep "$interval_seconds"
done
