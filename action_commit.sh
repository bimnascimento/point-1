#!/bin/bash
echo "==>1. commit e push"
git pull && git add . && git commit -m "update" --no-verify && git push