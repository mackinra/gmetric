#!/bin/bash

PATH=$PATH:/usr/bin:/usr/local/bin
GMETRIC=gmetric
MAX_LIFETIME=$((3600*24))
COUNT=0

if [ -z "`which $GMETRIC`" ]; then
    echo "Unable to locate gmetric executable"
    exit 1
fi

function gmetric_submit () {
    $GMETRIC --dmax=${MAX_LIFETIME} --type=$1 --name=$2 --value=$3 --units="$4"
    if [ $? -ne 0 ]; then
        exit $?
    fi
    COUNT=$((COUNT+1))
}

function Usage() {
    cat - <<EOF

Usage:
$0 -d directory -p prefix

-d Directory to examine for disk usage
-p Prefix to use on gmetric names

EOF
}

while getopts "d:p:" opt; do
    case "$opt" in
        d)
            path=$OPTARG
            ;;
        p)
            prefix=$OPTARG
            ;;
        *)
            Usage
            exit 1
            ;;
   esac
done

if [ -z "$path" ]; then
    echo "Must specify a directory to examine"
    Usage
    exit 1
fi
if [ ! -d "$path" ]; then
    echo "Unable to locate directory: $path"
    Usage
    exit 1
fi
if [ -z "$prefix" ]; then
    echo "Must specify a prefix to use on ganglia names (e.g., myapp_)"
    Usage
    exit 1
fi

free_disk=`df -k $path|tail -1|awk '{print $4}'`
real_total_disk=`df -k $path|tail -1|awk '{print $2}'`

gmetric_submit uint32 "${prefix}disk_free" "$free_disk" "KB"
gmetric_submit uint32 "${prefix}disk_total" "$real_total_disk" "KB"

echo "$COUNT metrics sent"
