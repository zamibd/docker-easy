#!/bin/bash

# 📁 কোন ডিরেক্টরি ওয়াচ করবে
WATCH_DIR="./"

# 🔒 আগের বিল্ড হ্যাশ সংরক্ষণ করা ফাইল
HASH_FILE=".last_build_hash"

# 🔍 বর্তমান Dockerfile, docker-compose.yml, *.php, *.env, *.js, *.ts ফাইলের হ্যাশ জেনারেট করা
CURRENT_HASH=$(find "$WATCH_DIR" -type f \( \
    -name 'Dockerfile' -o \
    -name 'docker-compose.yml' -o \
    -name '*.php' -o \
    -name '*.env' -o \
    -name '*.js' -o \
    -name '*.ts' \
    \) -exec md5sum {} \; | sort | md5sum | awk '{ print $1 }')

# 🧠 আগের হ্যাশ লোড করা (যদি থাকে)
LAST_HASH=""
[ -f "$HASH_FILE" ] && LAST_HASH=$(cat "$HASH_FILE")

# 🔁 চেঞ্জ হলে বিল্ড + আপ, না হলে শুধু আপ
if [ "$CURRENT_HASH" != "$LAST_HASH" ]; then
    echo "🔄 Changes detected. Rebuilding..."
    docker compose down
    docker compose up -d --build
    echo "$CURRENT_HASH" > "$HASH_FILE"
    echo "✅ Rebuild complete!"
else
    echo "♻️ No changes. Restarting without rebuild..."
    docker compose down
    docker compose up -d
    echo "✅ Restart complete!"
fi
