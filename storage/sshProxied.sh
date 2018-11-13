#!/bin/sh

eval `ssh-agent`;

echo ""
echo "ssh -o StrictHostKeyChecking=no -t -A -i $1 root@$3 ssh -o StrictHostKeyChecking=no -A root@$2"
echo ""

ssh-add $1 >/dev/null

ssh -o StrictHostKeyChecking=no -t -A -i $1 root@$3 ssh -o StrictHostKeyChecking=no -A root@$2
