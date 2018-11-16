#!/bin/sh

eval `ssh-agent -s`
ssh-add $1 >/dev/null

echo ""
echo "ssh -o StrictHostKeyChecking=no -t -A -i $1 root@$3 ssh -o StrictHostKeyChecking=no -A root@$2"
echo ""

ssh -o StrictHostKeyChecking=no -t -A -i $1 root@$3 ssh -o StrictHostKeyChecking=no -A root@$2

kill $SSH_AGENT_PID