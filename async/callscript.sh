#!/bin/bash

## experimental mode

touch $temp

########
dt=$(date +"%Y-%m-%d %H:%M:%S") 
nt=$(date +%s%N | cut -b1-13);
echo "SH $$ $dt $nt :: Running async script - $2" >> $(dirname $0)/async.log.txt
########

/usr/local/bin/php $1

########
dt=$(date +"%Y-%m-%d %H:%M:%S")
nt=$(date +%s%N | cut -b1-13);
echo "SH $$ $dt $nt :: Finished async script - $2" >> $(dirname $0)/async.log.txt
########

rm $temp

exit 0



#################


temp=$(dirname $0)/running.txt;

max_wait_time=600;

# wait until current async task completes

wait_time=$max_wait_time;
while [ -f "$temp" ] ; do
	
		########
		dt=$(date +"%Y-%m-%d %H:%M:%S") 
		nt=$(date +%s%N | cut -b1-13);
		echo "SH $$ $dt $nt :: Async waiting $wait_time ... " >> $(dirname $0)/async.log.txt
		########
		
		sleep 1;
		wait_time=$((wait_time-1));
		if [  $wait_time -lt 0 ] ; then
			########
			dt=$(date +"%Y-%m-%d %H:%M:%S") 
			nt=$(date +%s%N | cut -b1-13);
			echo "SH $$ $dt $nt :: Waited for $max_wait_time seconds and gave up" >> $(dirname $0)/async.log.txt
			########
			exit 1;
		fi;
done;



if [ -f "$temp" ] ;
then
	########
	dt=$(date +"%Y-%m-%d %H:%M:%S") 
	nt=$(date +%s%N | cut -b1-13);
	echo "SH $$ $dt $nt :: Cancelling since already running... " >> $(dirname $0)/async.log.txt
	########
	
else
	touch $temp
	
	########
	dt=$(date +"%Y-%m-%d %H:%M:%S") 
	nt=$(date +%s%N | cut -b1-13);
	echo "SH $$ $dt $nt :: Running async script $@" >> $(dirname $0)/async.log.txt
	########
	
	/usr/local/bin/php $@
	
	########
	dt=$(date +"%Y-%m-%d %H:%M:%S")
	nt=$(date +%s%N | cut -b1-13);
	echo "SH $$ $dt $nt :: Finished async script $@" >> $(dirname $0)/async.log.txt
	########
	
	rm $temp
fi; 

echo "" >> $out
	
