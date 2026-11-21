#!/usr/bin/env bash
# start | stop | status  — PHP built-in server for the local WordPress made by setup.sh
WP_DIR="${WP_DIR:-/tmp/arezoo-wp}"; PORT="${PORT:-8890}"; LOG="${LOG:-/tmp/arezoo-wp.log}"
pidf="$WP_DIR/.server-$PORT.pid"
up() { curl -s -o /dev/null -w '%{http_code}' "http://127.0.0.1:$PORT/" 2>/dev/null | grep -q '^[23]'; }
case "${1:-start}" in
  start)
    if up; then echo "already serving http://127.0.0.1:$PORT"; exit 0; fi
    [ -f "$WP_DIR/wp-load.php" ] || { echo "no WordPress in $WP_DIR - run setup.sh first"; exit 1; }
    cd "$WP_DIR" || exit 1
    # php must be the direct background job, otherwise $! is a subshell and stop leaves php running
    nohup php -S "127.0.0.1:$PORT" router.php >"$LOG" 2>&1 &
    echo $! >"$pidf"
    timeout 30 bash -c "until curl -s -o /dev/null http://127.0.0.1:$PORT/; do sleep 0.3; done"
    echo "serving http://127.0.0.1:$PORT  (log $LOG)";;
  stop)
    [ -f "$pidf" ] && kill "$(cat "$pidf")" 2>/dev/null; rm -f "$pidf"
    pkill -f "php -S 127.0.0.1:$PORT router.php" 2>/dev/null   # also servers started some other way
    timeout 10 bash -c "while curl -s -o /dev/null http://127.0.0.1:$PORT/; do sleep 0.2; done"
    echo "stopped :$PORT";;
  status)
    up && echo "up http://127.0.0.1:$PORT" || { echo "down :$PORT"; exit 1; };;
esac
