#!/bin/bash

#PRWODI=$PWD
#BN=$BASENAME
pwdrelative=${PWD##*/}

for file in *.ttl;
    do
    	filename=$(basename $file);
        #extension=${filename##*.};
        graph=${filename%.*};
        #echo $file;
        #echo /usr/lib/fuseki/./s-put --verbose http://localhost:3030/dataset/data http://data-gov.ie/graph/$graph $file;
        /usr/lib/fuseki/./s-put --verbose http://localhost:3030/dataset/data http://data-gov.ie/graph/$graph $file;
    done;

